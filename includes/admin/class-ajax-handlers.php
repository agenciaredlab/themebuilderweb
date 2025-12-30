<?php
/**
 * AJAX Handlers for Admin
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Ajax_Handlers {

    public function __construct() {
        // Templates
        add_action('wp_ajax_tbp_get_templates', [$this, 'get_templates']);
        add_action('wp_ajax_tbp_create_template', [$this, 'create_template']);
        add_action('wp_ajax_tbp_delete_template', [$this, 'delete_template']);
        add_action('wp_ajax_tbp_duplicate_template', [$this, 'duplicate_template']);

        // Popups
        add_action('wp_ajax_tbp_get_popups', [$this, 'get_popups']);
        add_action('wp_ajax_tbp_create_popup', [$this, 'create_popup']);
        add_action('wp_ajax_tbp_delete_popup', [$this, 'delete_popup']);
        add_action('wp_ajax_tbp_update_popup_status', [$this, 'update_popup_status']);

        // Global Styles
        add_action('wp_ajax_tbp_get_global_styles', [$this, 'get_global_styles']);
        add_action('wp_ajax_tbp_save_global_styles', [$this, 'save_global_styles']);

        // Forms
        add_action('wp_ajax_tbp_get_forms', [$this, 'get_forms']);
        add_action('wp_ajax_tbp_get_submissions', [$this, 'get_submissions']);
        add_action('wp_ajax_tbp_delete_submission', [$this, 'delete_submission']);
        add_action('wp_ajax_tbp_export_submissions', [$this, 'export_submissions']);

        // Theme Builder
        add_action('wp_ajax_tbp_get_theme_locations', [$this, 'get_theme_locations']);
        add_action('wp_ajax_tbp_assign_template', [$this, 'assign_template']);

        // Custom Fonts
        add_action('wp_ajax_tbp_get_fonts', [$this, 'get_fonts']);
        add_action('wp_ajax_tbp_upload_font', [$this, 'upload_font']);
        add_action('wp_ajax_tbp_delete_font', [$this, 'delete_font']);

        // Tools
        add_action('wp_ajax_tbp_regenerate_css', [$this, 'regenerate_css']);
        add_action('wp_ajax_tbp_clear_cache', [$this, 'clear_cache']);
        add_action('wp_ajax_tbp_export_settings', [$this, 'export_settings']);
        add_action('wp_ajax_tbp_import_settings', [$this, 'import_settings']);
        add_action('wp_ajax_tbp_replace_urls', [$this, 'replace_urls']);
    }

    private function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbp_admin')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            exit;
        }
    }

    private function check_permission($capability = 'edit_posts') {
        if (!current_user_can($capability)) {
            wp_send_json_error(['message' => 'Permission denied']);
            exit;
        }
    }

    // =============================================
    // TEMPLATES
    // =============================================

    public function get_templates() {
        $this->verify_nonce();
        $this->check_permission();

        $templates = get_posts([
            'post_type' => 'tbp_template',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        $data = [];
        foreach ($templates as $template) {
            $type = get_post_meta($template->ID, '_tbp_template_type', true) ?: 'page';
            $thumbnail = get_the_post_thumbnail_url($template->ID, 'medium');

            $data[] = [
                'id' => $template->ID,
                'title' => $template->post_title,
                'type' => $type,
                'status' => $template->post_status,
                'date' => get_the_date('M j, Y', $template->ID),
                'modified' => get_the_modified_date('M j, Y', $template->ID),
                'thumbnail' => $thumbnail ?: '',
                'edit_url' => add_query_arg(['post' => $template->ID, 'action' => 'tbp_editor'], admin_url('post.php'))
            ];
        }

        wp_send_json_success($data);
    }

    public function create_template() {
        $this->verify_nonce();
        $this->check_permission();

        $name = sanitize_text_field($_POST['name'] ?? 'Untitled');
        $type = sanitize_key($_POST['type'] ?? 'page');

        $post_id = wp_insert_post([
            'post_title' => $name,
            'post_type' => 'tbp_template',
            'post_status' => 'draft',
            'meta_input' => [
                '_tbp_template_type' => $type,
                '_tbp_content' => [],
                '_tbp_settings' => []
            ]
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }

        wp_send_json_success([
            'id' => $post_id,
            'edit_url' => add_query_arg(['post' => $post_id, 'action' => 'tbp_editor'], admin_url('post.php'))
        ]);
    }

    public function delete_template() {
        $this->verify_nonce();
        $this->check_permission('delete_posts');

        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            wp_send_json_error(['message' => 'Invalid template ID']);
        }

        wp_delete_post($id, true);
        wp_send_json_success(['message' => 'Template deleted']);
    }

    public function duplicate_template() {
        $this->verify_nonce();
        $this->check_permission();

        $id = intval($_POST['id'] ?? 0);
        $original = get_post($id);

        if (!$original) {
            wp_send_json_error(['message' => 'Template not found']);
        }

        $new_id = wp_insert_post([
            'post_title' => $original->post_title . ' (Copy)',
            'post_type' => 'tbp_template',
            'post_status' => 'draft'
        ]);

        // Copy meta
        $meta_keys = ['_tbp_template_type', '_tbp_content', '_tbp_settings', '_tbp_conditions'];
        foreach ($meta_keys as $key) {
            $value = get_post_meta($id, $key, true);
            if ($value) {
                update_post_meta($new_id, $key, $value);
            }
        }

        wp_send_json_success(['id' => $new_id, 'message' => 'Template duplicated']);
    }

    // =============================================
    // POPUPS
    // =============================================

    public function get_popups() {
        $this->verify_nonce();
        $this->check_permission();

        $popups = get_posts([
            'post_type' => 'tbp_popup',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        $data = [];
        foreach ($popups as $popup) {
            $settings = get_post_meta($popup->ID, '_tbp_popup_settings', true) ?: [];

            $data[] = [
                'id' => $popup->ID,
                'title' => $popup->post_title,
                'status' => $popup->post_status,
                'trigger' => $settings['trigger'] ?? 'on_load',
                'position' => $settings['position'] ?? 'center',
                'views' => intval(get_post_meta($popup->ID, '_tbp_popup_views', true)),
                'conversions' => intval(get_post_meta($popup->ID, '_tbp_popup_conversions', true)),
                'date' => get_the_date('M j, Y', $popup->ID)
            ];
        }

        wp_send_json_success($data);
    }

    public function create_popup() {
        $this->verify_nonce();
        $this->check_permission();

        $name = sanitize_text_field($_POST['name'] ?? 'Untitled Popup');
        $template = sanitize_key($_POST['template'] ?? 'blank');
        $trigger = sanitize_key($_POST['trigger'] ?? 'on_load');
        $position = sanitize_key($_POST['position'] ?? 'center');

        // Get template content
        $content = $this->get_popup_template_content($template);

        $post_id = wp_insert_post([
            'post_title' => $name,
            'post_type' => 'tbp_popup',
            'post_status' => 'draft',
            'meta_input' => [
                '_tbp_content' => $content,
                '_tbp_popup_settings' => [
                    'trigger' => $trigger,
                    'position' => $position,
                    'animation' => 'fadeIn',
                    'close_button' => true,
                    'overlay' => true,
                    'overlay_close' => true,
                    'esc_close' => true
                ]
            ]
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }

        wp_send_json_success([
            'id' => $post_id,
            'edit_url' => add_query_arg(['post' => $post_id, 'action' => 'tbp_editor'], admin_url('post.php'))
        ]);
    }

    private function get_popup_template_content($template) {
        $templates = [
            'newsletter' => [
                [
                    'type' => 'section',
                    'settings' => ['padding' => '40px', 'background' => '#ffffff'],
                    'elements' => [
                        ['type' => 'heading', 'settings' => ['text' => 'Subscribe to Our Newsletter', 'tag' => 'h2', 'align' => 'center']],
                        ['type' => 'text', 'settings' => ['text' => 'Get the latest updates delivered to your inbox.', 'align' => 'center']],
                        ['type' => 'form', 'settings' => ['fields' => [['type' => 'email', 'label' => 'Email', 'required' => true]], 'button_text' => 'Subscribe']]
                    ]
                ]
            ],
            'discount' => [
                [
                    'type' => 'section',
                    'settings' => ['padding' => '40px', 'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                    'elements' => [
                        ['type' => 'heading', 'settings' => ['text' => '20% OFF', 'tag' => 'h1', 'align' => 'center', 'color' => '#ffffff']],
                        ['type' => 'text', 'settings' => ['text' => 'Use code SAVE20 at checkout', 'align' => 'center', 'color' => '#ffffff']],
                        ['type' => 'button', 'settings' => ['text' => 'Shop Now', 'align' => 'center', 'style' => 'filled', 'background' => '#ffffff', 'color' => '#667eea']]
                    ]
                ]
            ],
            'announcement' => [
                [
                    'type' => 'section',
                    'settings' => ['padding' => '30px', 'background' => '#1e293b'],
                    'elements' => [
                        ['type' => 'heading', 'settings' => ['text' => 'Important Announcement', 'tag' => 'h3', 'align' => 'center', 'color' => '#ffffff']],
                        ['type' => 'text', 'settings' => ['text' => 'Your announcement message goes here.', 'align' => 'center', 'color' => '#94a3b8']],
                        ['type' => 'button', 'settings' => ['text' => 'Learn More', 'align' => 'center']]
                    ]
                ]
            ],
            'contact' => [
                [
                    'type' => 'section',
                    'settings' => ['padding' => '40px'],
                    'elements' => [
                        ['type' => 'heading', 'settings' => ['text' => 'Get in Touch', 'tag' => 'h2', 'align' => 'center']],
                        ['type' => 'form', 'settings' => [
                            'fields' => [
                                ['type' => 'text', 'label' => 'Name', 'required' => true],
                                ['type' => 'email', 'label' => 'Email', 'required' => true],
                                ['type' => 'textarea', 'label' => 'Message', 'required' => true]
                            ],
                            'button_text' => 'Send Message'
                        ]]
                    ]
                ]
            ],
            'exit-intent' => [
                [
                    'type' => 'section',
                    'settings' => ['padding' => '40px', 'background' => '#ffffff'],
                    'elements' => [
                        ['type' => 'heading', 'settings' => ['text' => 'Wait! Before You Go...', 'tag' => 'h2', 'align' => 'center']],
                        ['type' => 'text', 'settings' => ['text' => 'Get 10% off your first order when you sign up.', 'align' => 'center']],
                        ['type' => 'form', 'settings' => ['fields' => [['type' => 'email', 'label' => 'Email', 'required' => true]], 'button_text' => 'Get My Discount']]
                    ]
                ]
            ]
        ];

        return $templates[$template] ?? [];
    }

    public function delete_popup() {
        $this->verify_nonce();
        $this->check_permission('delete_posts');

        $id = intval($_POST['id'] ?? 0);
        wp_delete_post($id, true);
        wp_send_json_success(['message' => 'Popup deleted']);
    }

    public function update_popup_status() {
        $this->verify_nonce();
        $this->check_permission();

        $id = intval($_POST['id'] ?? 0);
        $status = sanitize_key($_POST['status'] ?? 'draft');

        wp_update_post([
            'ID' => $id,
            'post_status' => $status
        ]);

        wp_send_json_success(['message' => 'Status updated']);
    }

    // =============================================
    // GLOBAL STYLES
    // =============================================

    public function get_global_styles() {
        $this->verify_nonce();
        $this->check_permission();

        $styles = get_option('tbp_global_styles', []);
        wp_send_json_success($styles);
    }

    public function save_global_styles() {
        $this->verify_nonce();
        $this->check_permission('edit_theme_options');

        $styles = isset($_POST['styles']) ? json_decode(stripslashes($_POST['styles']), true) : [];

        if (empty($styles)) {
            wp_send_json_error(['message' => 'Invalid styles data']);
        }

        update_option('tbp_global_styles', $styles);

        // Generate CSS
        $this->generate_global_css($styles);

        wp_send_json_success(['message' => 'Styles saved']);
    }

    private function generate_global_css($styles) {
        $css = ":root {\n";

        // Colors
        if (!empty($styles['colors'])) {
            foreach ($styles['colors'] as $key => $value) {
                $css .= "  --tbp-color-" . $this->slugify($key) . ": {$value};\n";
            }
        }

        // Typography
        if (!empty($styles['typography'])) {
            if (!empty($styles['typography']['primaryFont'])) {
                $css .= "  --tbp-font-primary: '{$styles['typography']['primaryFont']}', sans-serif;\n";
            }
            if (!empty($styles['typography']['secondaryFont'])) {
                $css .= "  --tbp-font-secondary: '{$styles['typography']['secondaryFont']}', sans-serif;\n";
            }
            if (!empty($styles['typography']['baseSize'])) {
                $css .= "  --tbp-font-size-base: {$styles['typography']['baseSize']}px;\n";
            }
        }

        // Spacing
        if (!empty($styles['spacing'])) {
            if (!empty($styles['spacing']['unit'])) {
                $unit = $styles['spacing']['unit'];
                $css .= "  --tbp-spacing-xs: {$unit}px;\n";
                $css .= "  --tbp-spacing-sm: " . ($unit * 2) . "px;\n";
                $css .= "  --tbp-spacing-md: " . ($unit * 3) . "px;\n";
                $css .= "  --tbp-spacing-lg: " . ($unit * 4) . "px;\n";
                $css .= "  --tbp-spacing-xl: " . ($unit * 6) . "px;\n";
            }
            if (!empty($styles['spacing']['containerWidth'])) {
                $css .= "  --tbp-container-width: {$styles['spacing']['containerWidth']}px;\n";
            }
        }

        // Buttons
        if (!empty($styles['buttons'])) {
            if (isset($styles['buttons']['borderRadius'])) {
                $css .= "  --tbp-btn-radius: {$styles['buttons']['borderRadius']}px;\n";
            }
        }

        $css .= "}\n";

        // Save to file
        $upload_dir = wp_upload_dir();
        $css_dir = $upload_dir['basedir'] . '/tbp-css';

        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }

        file_put_contents($css_dir . '/global-styles.css', $css);
    }

    private function slugify($text) {
        return strtolower(preg_replace('/([A-Z])/', '-$1', $text));
    }

    // =============================================
    // FORMS
    // =============================================

    public function get_forms() {
        $this->verify_nonce();
        $this->check_permission();

        global $wpdb;
        $table = $wpdb->prefix . 'tbp_forms';

        $forms = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");

        $data = [];
        foreach ($forms as $form) {
            $fields = json_decode($form->fields, true) ?: [];
            $submissions = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}tbp_submissions WHERE form_id = %d",
                $form->id
            ));

            $data[] = [
                'id' => $form->id,
                'name' => $form->name,
                'fields' => count($fields),
                'submissions' => intval($submissions),
                'created' => $form->created_at
            ];
        }

        wp_send_json_success($data);
    }

    public function get_submissions() {
        $this->verify_nonce();
        $this->check_permission();

        global $wpdb;
        $table = $wpdb->prefix . 'tbp_submissions';
        $forms_table = $wpdb->prefix . 'tbp_forms';

        $submissions = $wpdb->get_results("
            SELECT s.*, f.name as form_name
            FROM {$table} s
            LEFT JOIN {$forms_table} f ON s.form_id = f.id
            ORDER BY s.created_at DESC
            LIMIT 100
        ");

        $data = [];
        foreach ($submissions as $sub) {
            $data[] = [
                'id' => $sub->id,
                'form_id' => $sub->form_id,
                'form_name' => $sub->form_name ?: 'Unknown Form',
                'data' => json_decode($sub->data, true) ?: [],
                'date' => date('M j, Y H:i', strtotime($sub->created_at))
            ];
        }

        wp_send_json_success($data);
    }

    public function delete_submission() {
        $this->verify_nonce();
        $this->check_permission('delete_posts');

        global $wpdb;
        $id = intval($_POST['id'] ?? 0);

        $wpdb->delete($wpdb->prefix . 'tbp_submissions', ['id' => $id], ['%d']);
        wp_send_json_success(['message' => 'Submission deleted']);
    }

    public function export_submissions() {
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'tbp_admin')) {
            wp_die('Invalid nonce');
        }

        if (!current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }

        global $wpdb;
        $submissions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tbp_submissions ORDER BY created_at DESC");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="form-submissions-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, ['ID', 'Form ID', 'Data', 'Date']);

        foreach ($submissions as $sub) {
            fputcsv($output, [
                $sub->id,
                $sub->form_id,
                $sub->data,
                $sub->created_at
            ]);
        }

        fclose($output);
        exit;
    }

    // =============================================
    // THEME BUILDER
    // =============================================

    public function get_theme_locations() {
        $this->verify_nonce();
        $this->check_permission();

        $locations = [
            ['id' => 'header', 'name' => 'Header', 'template' => null],
            ['id' => 'footer', 'name' => 'Footer', 'template' => null],
            ['id' => 'single', 'name' => 'Single Post', 'template' => null],
            ['id' => 'page', 'name' => 'Page', 'template' => null],
            ['id' => 'archive', 'name' => 'Archive', 'template' => null],
            ['id' => 'search', 'name' => 'Search Results', 'template' => null],
            ['id' => '404', 'name' => '404 Page', 'template' => null],
            ['id' => 'product', 'name' => 'Single Product', 'template' => null],
            ['id' => 'product_archive', 'name' => 'Shop Page', 'template' => null]
        ];

        // Get assigned templates
        $assignments = get_option('tbp_template_assignments', []);

        foreach ($locations as &$location) {
            if (!empty($assignments[$location['id']])) {
                $template_id = $assignments[$location['id']];
                $template = get_post($template_id);

                if ($template) {
                    $location['template'] = [
                        'id' => $template_id,
                        'title' => $template->post_title
                    ];
                }
            }
        }

        wp_send_json_success($locations);
    }

    public function assign_template() {
        $this->verify_nonce();
        $this->check_permission('edit_theme_options');

        $location = sanitize_key($_POST['location'] ?? '');
        $template_id = intval($_POST['template_id'] ?? 0);

        if (!$location) {
            wp_send_json_error(['message' => 'Invalid location']);
        }

        $assignments = get_option('tbp_template_assignments', []);
        $assignments[$location] = $template_id;
        update_option('tbp_template_assignments', $assignments);

        wp_send_json_success(['message' => 'Template assigned']);
    }

    // =============================================
    // CUSTOM FONTS
    // =============================================

    public function get_fonts() {
        $this->verify_nonce();
        $this->check_permission();

        $fonts = get_option('tbp_custom_fonts', []);
        wp_send_json_success($fonts);
    }

    public function upload_font() {
        $this->verify_nonce();
        $this->check_permission('edit_theme_options');

        if (empty($_FILES['font'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $allowed_types = ['font/woff', 'font/woff2', 'application/font-woff', 'application/font-woff2', 'font/ttf', 'application/x-font-ttf'];
        $file = $_FILES['font'];

        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(['message' => 'Invalid font file type']);
        }

        $upload = wp_handle_upload($file, ['test_form' => false]);

        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        $name = sanitize_text_field($_POST['font_name'] ?? pathinfo($file['name'], PATHINFO_FILENAME));
        $weight = sanitize_text_field($_POST['font_weight'] ?? '400');
        $style = sanitize_text_field($_POST['font_style'] ?? 'normal');

        $fonts = get_option('tbp_custom_fonts', []);
        $fonts[] = [
            'id' => uniqid('font_'),
            'name' => $name,
            'weight' => $weight,
            'style' => $style,
            'url' => $upload['url'],
            'file' => $upload['file']
        ];

        update_option('tbp_custom_fonts', $fonts);
        $this->generate_fonts_css($fonts);

        wp_send_json_success(['message' => 'Font uploaded', 'fonts' => $fonts]);
    }

    public function delete_font() {
        $this->verify_nonce();
        $this->check_permission('edit_theme_options');

        $id = sanitize_text_field($_POST['id'] ?? '');
        $fonts = get_option('tbp_custom_fonts', []);

        foreach ($fonts as $key => $font) {
            if ($font['id'] === $id) {
                if (!empty($font['file']) && file_exists($font['file'])) {
                    unlink($font['file']);
                }
                unset($fonts[$key]);
                break;
            }
        }

        $fonts = array_values($fonts);
        update_option('tbp_custom_fonts', $fonts);
        $this->generate_fonts_css($fonts);

        wp_send_json_success(['message' => 'Font deleted']);
    }

    private function generate_fonts_css($fonts) {
        $css = '';

        foreach ($fonts as $font) {
            $css .= "@font-face {\n";
            $css .= "  font-family: '{$font['name']}';\n";
            $css .= "  src: url('{$font['url']}');\n";
            $css .= "  font-weight: {$font['weight']};\n";
            $css .= "  font-style: {$font['style']};\n";
            $css .= "  font-display: swap;\n";
            $css .= "}\n\n";
        }

        $upload_dir = wp_upload_dir();
        $css_dir = $upload_dir['basedir'] . '/tbp-css';

        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }

        file_put_contents($css_dir . '/custom-fonts.css', $css);
    }

    // =============================================
    // TOOLS
    // =============================================

    public function regenerate_css() {
        $this->verify_nonce();
        $this->check_permission('manage_options');

        // Get all TBP content
        $posts = get_posts([
            'post_type' => ['tbp_template', 'tbp_popup', 'page', 'post'],
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_tbp_content',
                    'compare' => 'EXISTS'
                ]
            ]
        ]);

        $count = 0;
        foreach ($posts as $post) {
            $content = get_post_meta($post->ID, '_tbp_content', true);
            if ($content) {
                theme_builder_pro()->assets->generate_css($post->ID, $content);
                $count++;
            }
        }

        // Regenerate global styles
        $styles = get_option('tbp_global_styles', []);
        if (!empty($styles)) {
            $this->generate_global_css($styles);
        }

        wp_send_json_success(['message' => "Regenerated CSS for {$count} documents"]);
    }

    public function clear_cache() {
        $this->verify_nonce();
        $this->check_permission('manage_options');

        // Clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_tbp_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_tbp_%'");

        // Clear CSS cache
        $upload_dir = wp_upload_dir();
        $css_dir = $upload_dir['basedir'] . '/tbp-css';

        if (is_dir($css_dir)) {
            $files = glob($css_dir . '/*.css');
            foreach ($files as $file) {
                unlink($file);
            }
        }

        wp_send_json_success(['message' => 'Cache cleared']);
    }

    public function export_settings() {
        $this->verify_nonce();
        $this->check_permission('manage_options');

        $data = [
            'version' => TBP_VERSION,
            'settings' => [
                'container_width' => get_option('tbp_container_width'),
                'google_fonts' => get_option('tbp_google_fonts'),
                'animations' => get_option('tbp_enable_animations'),
                'lazyload' => get_option('tbp_enable_lazyload'),
                'breakpoints' => get_option('tbp_breakpoints')
            ],
            'global_styles' => get_option('tbp_global_styles'),
            'template_assignments' => get_option('tbp_template_assignments')
        ];

        wp_send_json_success($data);
    }

    public function import_settings() {
        $this->verify_nonce();
        $this->check_permission('manage_options');

        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : null;

        if (!$data) {
            wp_send_json_error(['message' => 'Invalid import data']);
        }

        // Import settings
        if (!empty($data['settings'])) {
            foreach ($data['settings'] as $key => $value) {
                update_option('tbp_' . $key, $value);
            }
        }

        // Import global styles
        if (!empty($data['global_styles'])) {
            update_option('tbp_global_styles', $data['global_styles']);
            $this->generate_global_css($data['global_styles']);
        }

        // Import template assignments
        if (!empty($data['template_assignments'])) {
            update_option('tbp_template_assignments', $data['template_assignments']);
        }

        wp_send_json_success(['message' => 'Settings imported']);
    }

    public function replace_urls() {
        $this->verify_nonce();
        $this->check_permission('manage_options');

        $old_url = esc_url_raw($_POST['old_url'] ?? '');
        $new_url = esc_url_raw($_POST['new_url'] ?? '');

        if (!$old_url || !$new_url) {
            wp_send_json_error(['message' => 'Invalid URLs']);
        }

        global $wpdb;

        // Replace in post meta
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key LIKE '_tbp_%'",
            $old_url,
            $new_url
        ));

        // Replace in options
        $options = ['tbp_global_styles', 'tbp_custom_fonts'];
        foreach ($options as $option) {
            $value = get_option($option);
            if ($value) {
                $value = json_decode(str_replace($old_url, $new_url, json_encode($value)), true);
                update_option($option, $value);
            }
        }

        wp_send_json_success(['message' => 'URLs replaced']);
    }
}

new TBP_Ajax_Handlers();
