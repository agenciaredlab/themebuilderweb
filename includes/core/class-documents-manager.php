<?php
/**
 * Documents Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Documents_Manager {

    /**
     * Document Types
     */
    private $document_types = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_document_types();
        add_action('save_post', [$this, 'on_save_post'], 10, 2);
    }

    /**
     * Register Document Types
     */
    private function register_document_types() {
        $this->document_types = [
            'page' => [
                'label' => __('Page', 'theme-builder-pro'),
                'post_types' => ['page'],
                'class' => 'TBP_Document_Page',
            ],
            'post' => [
                'label' => __('Post', 'theme-builder-pro'),
                'post_types' => ['post'],
                'class' => 'TBP_Document_Post',
            ],
            'header' => [
                'label' => __('Header', 'theme-builder-pro'),
                'post_types' => ['tbp_theme_doc'],
                'class' => 'TBP_Document_Header',
            ],
            'footer' => [
                'label' => __('Footer', 'theme-builder-pro'),
                'post_types' => ['tbp_theme_doc'],
                'class' => 'TBP_Document_Footer',
            ],
            'single' => [
                'label' => __('Single', 'theme-builder-pro'),
                'post_types' => ['tbp_theme_doc'],
                'class' => 'TBP_Document_Single',
            ],
            'archive' => [
                'label' => __('Archive', 'theme-builder-pro'),
                'post_types' => ['tbp_theme_doc'],
                'class' => 'TBP_Document_Archive',
            ],
            'search' => [
                'label' => __('Search Results', 'theme-builder-pro'),
                'post_types' => ['tbp_theme_doc'],
                'class' => 'TBP_Document_Search',
            ],
            'error_404' => [
                'label' => __('404 Page', 'theme-builder-pro'),
                'post_types' => ['tbp_theme_doc'],
                'class' => 'TBP_Document_404',
            ],
            'popup' => [
                'label' => __('Popup', 'theme-builder-pro'),
                'post_types' => ['tbp_popup'],
                'class' => 'TBP_Document_Popup',
            ],
            'loop_item' => [
                'label' => __('Loop Item', 'theme-builder-pro'),
                'post_types' => ['tbp_template'],
                'class' => 'TBP_Document_Loop_Item',
            ],
            'section' => [
                'label' => __('Section', 'theme-builder-pro'),
                'post_types' => ['tbp_template'],
                'class' => 'TBP_Document_Section',
            ],
        ];

        $this->document_types = apply_filters('tbp/documents/types', $this->document_types);
    }

    /**
     * Get Document Types
     */
    public function get_document_types() {
        return $this->document_types;
    }

    /**
     * Get Document Type
     */
    public function get_document_type($type) {
        return isset($this->document_types[$type]) ? $this->document_types[$type] : null;
    }

    /**
     * Get Document
     */
    public function get($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return null;
        }

        $document_type = get_post_meta($post_id, '_tbp_document_type', true);

        if (!$document_type) {
            // Determine type from post type
            $document_type = $this->get_type_from_post_type($post->post_type);
        }

        return $this->create_document($post_id, $document_type);
    }

    /**
     * Create Document
     */
    private function create_document($post_id, $type) {
        return new TBP_Document($post_id, $type);
    }

    /**
     * Get Type from Post Type
     */
    private function get_type_from_post_type($post_type) {
        foreach ($this->document_types as $type => $config) {
            if (in_array($post_type, $config['post_types'])) {
                return $type;
            }
        }

        return 'page';
    }

    /**
     * Create New Document
     */
    public function create($args = []) {
        $defaults = [
            'post_type' => 'tbp_template',
            'post_title' => __('New Template', 'theme-builder-pro'),
            'post_status' => 'draft',
            'document_type' => 'page',
        ];

        $args = wp_parse_args($args, $defaults);

        $post_id = wp_insert_post([
            'post_type' => $args['post_type'],
            'post_title' => $args['post_title'],
            'post_status' => $args['post_status'],
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        update_post_meta($post_id, '_tbp_document_type', $args['document_type']);
        update_post_meta($post_id, '_tbp_edit_mode', 'builder');
        update_post_meta($post_id, '_tbp_content', wp_json_encode(['elements' => []]));
        update_post_meta($post_id, '_tbp_settings', wp_json_encode([]));

        return $post_id;
    }

    /**
     * Delete Document
     */
    public function delete($post_id) {
        return wp_delete_post($post_id, true);
    }

    /**
     * Duplicate Document
     */
    public function duplicate($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return new WP_Error('invalid_post', __('Invalid post ID', 'theme-builder-pro'));
        }

        $new_post_id = wp_insert_post([
            'post_type' => $post->post_type,
            'post_title' => $post->post_title . ' ' . __('(Copy)', 'theme-builder-pro'),
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
        ]);

        if (is_wp_error($new_post_id)) {
            return $new_post_id;
        }

        // Copy meta
        $meta_keys = [
            '_tbp_document_type',
            '_tbp_edit_mode',
            '_tbp_content',
            '_tbp_settings',
            '_tbp_css',
            '_tbp_conditions',
        ];

        foreach ($meta_keys as $key) {
            $value = get_post_meta($post_id, $key, true);
            if ($value) {
                update_post_meta($new_post_id, $key, $value);
            }
        }

        // Regenerate element IDs
        $this->regenerate_element_ids($new_post_id);

        return $new_post_id;
    }

    /**
     * Regenerate Element IDs
     */
    private function regenerate_element_ids($post_id) {
        $content = TBP_Utils::get_post_content($post_id);

        if (!empty($content['elements'])) {
            $content['elements'] = $this->regenerate_ids_recursive($content['elements']);
            TBP_Utils::save_post_content($post_id, $content);
        }
    }

    /**
     * Regenerate IDs Recursive
     */
    private function regenerate_ids_recursive($elements) {
        foreach ($elements as &$element) {
            $element['id'] = TBP_Utils::generate_id();

            if (!empty($element['elements'])) {
                $element['elements'] = $this->regenerate_ids_recursive($element['elements']);
            }
        }

        return $elements;
    }

    /**
     * Import Document
     */
    public function import($data) {
        if (!isset($data['content'])) {
            return new WP_Error('invalid_data', __('Invalid import data', 'theme-builder-pro'));
        }

        $post_id = $this->create([
            'post_title' => $data['title'] ?? __('Imported Template', 'theme-builder-pro'),
            'document_type' => $data['document_type'] ?? 'page',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Save content
        TBP_Utils::save_post_content($post_id, $data['content']);

        // Save settings
        if (isset($data['settings'])) {
            TBP_Utils::save_post_settings($post_id, $data['settings']);
        }

        // Regenerate element IDs
        $this->regenerate_element_ids($post_id);

        // Regenerate CSS
        TBP_Assets::instance()->generate_post_css($post_id);

        return $post_id;
    }

    /**
     * Export Document
     */
    public function export($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return null;
        }

        return [
            'title' => $post->post_title,
            'document_type' => get_post_meta($post_id, '_tbp_document_type', true),
            'content' => TBP_Utils::get_post_content($post_id),
            'settings' => TBP_Utils::get_post_settings($post_id),
            'version' => TBP_VERSION,
        ];
    }

    /**
     * On Save Post
     */
    public function on_save_post($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!TBP_Utils::is_built_with_tbp($post_id)) {
            return;
        }

        // Regenerate CSS
        TBP_Assets::instance()->clear_cache($post_id);
    }

    /**
     * Get Recent Documents
     */
    public function get_recent($limit = 10) {
        $post_types = ['page', 'post', 'tbp_template', 'tbp_theme_doc', 'tbp_popup'];

        $posts = get_posts([
            'post_type' => $post_types,
            'posts_per_page' => $limit,
            'meta_key' => '_tbp_edit_mode',
            'meta_value' => 'builder',
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);

        return array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'document_type' => get_post_meta($post->ID, '_tbp_document_type', true),
                'edit_url' => $this->get_edit_url($post->ID),
                'modified' => $post->post_modified,
            ];
        }, $posts);
    }

    /**
     * Get Edit URL
     */
    public function get_edit_url($post_id) {
        return add_query_arg([
            'post' => $post_id,
            'action' => 'tbp_editor',
        ], admin_url('post.php'));
    }

    /**
     * Get Preview URL
     */
    public function get_preview_url($post_id) {
        return add_query_arg([
            'tbp-preview' => '1',
        ], get_permalink($post_id));
    }

    /**
     * Save Document
     */
    public function save($post_id, $data) {
        // Save content
        if (isset($data['content'])) {
            TBP_Utils::save_post_content($post_id, $data['content']);
        }

        // Save settings
        if (isset($data['settings'])) {
            TBP_Utils::save_post_settings($post_id, $data['settings']);
        }

        // Regenerate CSS
        $css = TBP_Assets::instance()->generate_post_css($post_id);
        TBP_Utils::save_post_css($post_id, $css);

        // Update post
        if (isset($data['post_title'])) {
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $data['post_title'],
            ]);
        }

        // Save history state
        TBP_DB::save_history_state($post_id, 'save', $data);

        do_action('tbp/document/saved', $post_id, $data);

        return true;
    }

    /**
     * Autosave Document
     */
    public function autosave($post_id, $data) {
        // Save to autosave meta instead of main content
        update_post_meta($post_id, '_tbp_autosave_content', wp_json_encode($data['content'] ?? []));
        update_post_meta($post_id, '_tbp_autosave_time', time());

        return true;
    }

    /**
     * Restore Autosave
     */
    public function restore_autosave($post_id) {
        $autosave = get_post_meta($post_id, '_tbp_autosave_content', true);

        if ($autosave) {
            TBP_Utils::save_post_content($post_id, json_decode($autosave, true));
            delete_post_meta($post_id, '_tbp_autosave_content');
            delete_post_meta($post_id, '_tbp_autosave_time');

            return true;
        }

        return false;
    }

    /**
     * Check for Autosave
     */
    public function has_autosave($post_id) {
        $autosave_time = get_post_meta($post_id, '_tbp_autosave_time', true);

        if (!$autosave_time) {
            return false;
        }

        $post = get_post($post_id);
        $post_modified = strtotime($post->post_modified);

        return $autosave_time > $post_modified;
    }
}

/**
 * Document Class
 */
class TBP_Document {

    protected $post_id;
    protected $type;
    protected $post;
    protected $content;
    protected $settings;

    public function __construct($post_id, $type = 'page') {
        $this->post_id = $post_id;
        $this->type = $type;
        $this->post = get_post($post_id);
    }

    public function get_id() {
        return $this->post_id;
    }

    public function get_type() {
        return $this->type;
    }

    public function get_post() {
        return $this->post;
    }

    public function get_title() {
        return $this->post ? $this->post->post_title : '';
    }

    public function get_content() {
        if ($this->content === null) {
            $this->content = TBP_Utils::get_post_content($this->post_id);
        }
        return $this->content;
    }

    public function get_settings() {
        if ($this->settings === null) {
            $this->settings = TBP_Utils::get_post_settings($this->post_id);
        }
        return $this->settings;
    }

    public function get_elements() {
        $content = $this->get_content();
        return isset($content['elements']) ? $content['elements'] : [];
    }

    public function get_css() {
        return TBP_Utils::get_post_css($this->post_id);
    }

    public function render() {
        $elements = $this->get_elements();

        if (empty($elements)) {
            return '';
        }

        ob_start();

        echo '<div class="tbp-document tbp-document-' . esc_attr($this->type) . '" data-id="' . esc_attr($this->post_id) . '">';

        foreach ($elements as $element) {
            echo $this->render_element($element);
        }

        echo '</div>';

        return ob_get_clean();
    }

    protected function render_element($element) {
        $type = $element['type'] ?? 'widget';
        $widget_type = $element['widgetType'] ?? '';

        if ($type === 'section') {
            return $this->render_section($element);
        }

        if ($type === 'column') {
            return $this->render_column($element);
        }

        if ($type === 'widget' && $widget_type) {
            return $this->render_widget($element);
        }

        return '';
    }

    protected function render_section($element) {
        $id = $element['id'];
        $settings = $element['settings'] ?? [];
        $elements = $element['elements'] ?? [];

        $classes = ['tbp-section', 'tbp-element', 'tbp-element-' . $id];

        if (!empty($settings['layout'])) {
            $classes[] = 'tbp-section-' . $settings['layout'];
        }

        if (!empty($settings['stretch_section'])) {
            $classes[] = 'tbp-section-stretched';
        }

        if (!empty($settings['css_classes'])) {
            $classes[] = $settings['css_classes'];
        }

        $html = '<section class="' . esc_attr(implode(' ', $classes)) . '" data-id="' . esc_attr($id) . '">';

        // Background overlay
        if (!empty($settings['background_overlay_color'])) {
            $html .= '<div class="tbp-background-overlay"></div>';
        }

        // Shape divider top
        if (!empty($settings['shape_divider_top'])) {
            $html .= $this->render_shape_divider('top', $settings);
        }

        $html .= '<div class="tbp-container">';
        $html .= '<div class="tbp-row">';

        foreach ($elements as $child) {
            $html .= $this->render_element($child);
        }

        $html .= '</div>';
        $html .= '</div>';

        // Shape divider bottom
        if (!empty($settings['shape_divider_bottom'])) {
            $html .= $this->render_shape_divider('bottom', $settings);
        }

        $html .= '</section>';

        return $html;
    }

    protected function render_column($element) {
        $id = $element['id'];
        $settings = $element['settings'] ?? [];
        $elements = $element['elements'] ?? [];

        $classes = ['tbp-column', 'tbp-element', 'tbp-element-' . $id];

        $width = $settings['column_width'] ?? 100;
        $classes[] = 'tbp-col-' . intval($width);

        if (!empty($settings['css_classes'])) {
            $classes[] = $settings['css_classes'];
        }

        $html = '<div class="' . esc_attr(implode(' ', $classes)) . '" data-id="' . esc_attr($id) . '">';
        $html .= '<div class="tbp-column-wrap">';

        foreach ($elements as $child) {
            $html .= $this->render_element($child);
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function render_widget($element) {
        $widget_type = $element['widgetType'];
        $widget = theme_builder_pro()->widgets_manager->get_widget($widget_type);

        if (!$widget) {
            return '';
        }

        return $widget->render_element($element);
    }

    protected function render_shape_divider($position, $settings) {
        $shape = $settings['shape_divider_' . $position] ?? '';
        $color = $settings['shape_divider_' . $position . '_color'] ?? '#ffffff';
        $height = $settings['shape_divider_' . $position . '_height'] ?? 100;
        $flip = !empty($settings['shape_divider_' . $position . '_flip']);

        $shapes = [
            'waves' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path></svg>',
            'waves-brush' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path></svg>',
            'triangle' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M598.97 114.72L0 0 0 120 1200 120 1200 0 598.97 114.72z"></path></svg>',
            'arrow' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M649.97 0L550.03 0 599.91 54.12 649.97 0z"></path></svg>',
            'split' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0V7.23C0,65.52,268.63,112.77,600,112.77S1200,65.52,1200,7.23V0Z"></path></svg>',
            'book' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M602.45,3.86h0S572.9,116.24,281.94,120H923C googl632,116.24,googl602.45,3.86,googl602.45,3.86Z"></path></svg>',
            'tilt' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M1200 120L0 16.48 0 0 1200 0 1200 120z"></path></svg>',
        ];

        if (!isset($shapes[$shape])) {
            return '';
        }

        $style = 'fill: ' . esc_attr($color) . '; height: ' . intval($height) . 'px;';

        $html = '<div class="tbp-shape-divider tbp-shape-divider-' . esc_attr($position);
        if ($flip) {
            $html .= ' tbp-shape-divider-flip';
        }
        $html .= '" style="' . $style . '">';
        $html .= $shapes[$shape];
        $html .= '</div>';

        return $html;
    }
}
