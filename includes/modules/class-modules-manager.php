<?php
/**
 * Modules Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Modules_Manager {

    private $modules = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_modules();
    }

    /**
     * Register Modules
     */
    private function register_modules() {
        $modules = [
            'library' => [
                'label' => __('Template Library', 'theme-builder-pro'),
                'description' => __('Access pre-designed templates and blocks', 'theme-builder-pro'),
                'class' => 'TBP_Module_Library',
                'enabled' => true,
            ],
            'theme-builder' => [
                'label' => __('Theme Builder', 'theme-builder-pro'),
                'description' => __('Build custom headers, footers, and templates', 'theme-builder-pro'),
                'class' => 'TBP_Module_Theme_Builder',
                'enabled' => true,
            ],
            'popup-builder' => [
                'label' => __('Popup Builder', 'theme-builder-pro'),
                'description' => __('Create popups with triggers and conditions', 'theme-builder-pro'),
                'class' => 'TBP_Module_Popup_Builder',
                'enabled' => true,
            ],
            'form-builder' => [
                'label' => __('Form Builder', 'theme-builder-pro'),
                'description' => __('Create contact forms and lead generation', 'theme-builder-pro'),
                'class' => 'TBP_Module_Form_Builder',
                'enabled' => true,
            ],
            'dynamic-content' => [
                'label' => __('Dynamic Content', 'theme-builder-pro'),
                'description' => __('Display dynamic data from posts, ACF, and more', 'theme-builder-pro'),
                'class' => 'TBP_Module_Dynamic_Content',
                'enabled' => true,
            ],
            'global-widget' => [
                'label' => __('Global Widget', 'theme-builder-pro'),
                'description' => __('Create reusable widgets across your site', 'theme-builder-pro'),
                'class' => 'TBP_Module_Global_Widget',
                'enabled' => true,
            ],
            'custom-css' => [
                'label' => __('Custom CSS', 'theme-builder-pro'),
                'description' => __('Add custom CSS to any element', 'theme-builder-pro'),
                'class' => 'TBP_Module_Custom_CSS',
                'enabled' => true,
            ],
            'custom-fonts' => [
                'label' => __('Custom Fonts', 'theme-builder-pro'),
                'description' => __('Upload and use custom fonts', 'theme-builder-pro'),
                'class' => 'TBP_Module_Custom_Fonts',
                'enabled' => true,
            ],
            'role-manager' => [
                'label' => __('Role Manager', 'theme-builder-pro'),
                'description' => __('Control access to the editor by role', 'theme-builder-pro'),
                'class' => 'TBP_Module_Role_Manager',
                'enabled' => true,
            ],
            'motion-effects' => [
                'label' => __('Motion Effects', 'theme-builder-pro'),
                'description' => __('Scrolling effects and mouse parallax', 'theme-builder-pro'),
                'class' => 'TBP_Module_Motion_Effects',
                'enabled' => true,
            ],
            'sticky' => [
                'label' => __('Sticky Elements', 'theme-builder-pro'),
                'description' => __('Make sections and columns sticky', 'theme-builder-pro'),
                'class' => 'TBP_Module_Sticky',
                'enabled' => true,
            ],
            'display-conditions' => [
                'label' => __('Display Conditions', 'theme-builder-pro'),
                'description' => __('Show/hide elements based on conditions', 'theme-builder-pro'),
                'class' => 'TBP_Module_Display_Conditions',
                'enabled' => true,
            ],
            'scroll-snap' => [
                'label' => __('Scroll Snap', 'theme-builder-pro'),
                'description' => __('Full page scroll snapping', 'theme-builder-pro'),
                'class' => 'TBP_Module_Scroll_Snap',
                'enabled' => true,
            ],
            'woocommerce' => [
                'label' => __('WooCommerce Builder', 'theme-builder-pro'),
                'description' => __('Build WooCommerce pages visually', 'theme-builder-pro'),
                'class' => 'TBP_Module_WooCommerce',
                'enabled' => class_exists('WooCommerce'),
            ],
            'acf' => [
                'label' => __('ACF Integration', 'theme-builder-pro'),
                'description' => __('Display ACF fields dynamically', 'theme-builder-pro'),
                'class' => 'TBP_Module_ACF',
                'enabled' => class_exists('ACF'),
            ],
            'loop-builder' => [
                'label' => __('Loop Builder', 'theme-builder-pro'),
                'description' => __('Create custom loop templates', 'theme-builder-pro'),
                'class' => 'TBP_Module_Loop_Builder',
                'enabled' => true,
            ],
            'mega-menu' => [
                'label' => __('Mega Menu', 'theme-builder-pro'),
                'description' => __('Create advanced mega menus', 'theme-builder-pro'),
                'class' => 'TBP_Module_Mega_Menu',
                'enabled' => true,
            ],
            'notes' => [
                'label' => __('Notes & Collaboration', 'theme-builder-pro'),
                'description' => __('Add notes for team collaboration', 'theme-builder-pro'),
                'class' => 'TBP_Module_Notes',
                'enabled' => true,
            ],
        ];

        // Get saved module settings
        $saved_modules = get_option('tbp_modules', []);

        foreach ($modules as $id => $module) {
            // Check if module was explicitly disabled
            if (isset($saved_modules[$id]) && !$saved_modules[$id]) {
                continue;
            }

            // Check if module requirements are met
            if (!$module['enabled']) {
                continue;
            }

            $this->modules[$id] = $module;
        }

        $this->modules = apply_filters('tbp/modules/registered', $this->modules);
    }

    /**
     * Get Modules
     */
    public function get_modules() {
        return $this->modules;
    }

    /**
     * Get Module
     */
    public function get_module($id) {
        return isset($this->modules[$id]) ? $this->modules[$id] : null;
    }

    /**
     * Is Module Active
     */
    public function is_module_active($id) {
        return isset($this->modules[$id]);
    }

    /**
     * Activate Module
     */
    public function activate_module($id) {
        $saved_modules = get_option('tbp_modules', []);
        $saved_modules[$id] = true;
        update_option('tbp_modules', $saved_modules);
    }

    /**
     * Deactivate Module
     */
    public function deactivate_module($id) {
        $saved_modules = get_option('tbp_modules', []);
        $saved_modules[$id] = false;
        update_option('tbp_modules', $saved_modules);
    }

    /**
     * Get Module Categories
     */
    public function get_categories() {
        return [
            'design' => __('Design', 'theme-builder-pro'),
            'building' => __('Building', 'theme-builder-pro'),
            'integrations' => __('Integrations', 'theme-builder-pro'),
            'tools' => __('Tools', 'theme-builder-pro'),
        ];
    }
}

/**
 * Module Base Class
 */
abstract class TBP_Module_Base {

    protected $id;
    protected $name;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize Module
     */
    abstract protected function init();

    /**
     * Get ID
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get Name
     */
    public function get_name() {
        return $this->name;
    }
}

/**
 * Template Library Module
 */
class TBP_Module_Library extends TBP_Module_Base {

    protected $id = 'library';
    protected $name = 'Template Library';

    protected function init() {
        add_action('tbp/editor/footer', [$this, 'print_templates_modal']);
        add_action('wp_ajax_tbp_get_templates', [$this, 'ajax_get_templates']);
        add_action('wp_ajax_tbp_import_template', [$this, 'ajax_import_template']);
    }

    public function print_templates_modal() {
        ?>
        <div id="tbp-templates-modal" class="tbp-modal" style="display: none;">
            <div class="tbp-modal-content">
                <div class="tbp-modal-header">
                    <h2><?php esc_html_e('Template Library', 'theme-builder-pro'); ?></h2>
                    <button class="tbp-modal-close">&times;</button>
                </div>
                <div class="tbp-modal-body">
                    <div class="tbp-templates-tabs">
                        <button class="tbp-tab active" data-tab="blocks"><?php esc_html_e('Blocks', 'theme-builder-pro'); ?></button>
                        <button class="tbp-tab" data-tab="pages"><?php esc_html_e('Pages', 'theme-builder-pro'); ?></button>
                        <button class="tbp-tab" data-tab="my-templates"><?php esc_html_e('My Templates', 'theme-builder-pro'); ?></button>
                    </div>
                    <div class="tbp-templates-search">
                        <input type="text" placeholder="<?php esc_attr_e('Search templates...', 'theme-builder-pro'); ?>">
                    </div>
                    <div class="tbp-templates-categories"></div>
                    <div class="tbp-templates-grid"></div>
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_get_templates() {
        check_ajax_referer('tbp_editor', 'nonce');

        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'blocks';
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

        $templates = $this->get_templates($type, $category);

        wp_send_json_success($templates);
    }

    public function ajax_import_template() {
        check_ajax_referer('tbp_editor', 'nonce');

        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';

        if (!$template_id) {
            wp_send_json_error(__('Invalid template ID', 'theme-builder-pro'));
        }

        $template_data = $this->get_template_data($template_id);

        if (!$template_data) {
            wp_send_json_error(__('Template not found', 'theme-builder-pro'));
        }

        wp_send_json_success($template_data);
    }

    private function get_templates($type, $category) {
        // Built-in templates
        $templates = $this->get_builtin_templates($type);

        // My templates (saved)
        if ($type === 'my-templates') {
            $templates = $this->get_user_templates();
        }

        // Filter by category
        if ($category) {
            $templates = array_filter($templates, function($template) use ($category) {
                return in_array($category, $template['categories'] ?? []);
            });
        }

        return apply_filters('tbp/templates/list', $templates, $type, $category);
    }

    private function get_builtin_templates($type) {
        $templates_dir = TBP_TEMPLATES_DIR . 'library/' . $type;
        $templates = [];

        if (!is_dir($templates_dir)) {
            return $this->get_default_templates($type);
        }

        $files = glob($templates_dir . '/*.json');

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $templates[] = $data;
            }
        }

        return $templates;
    }

    private function get_default_templates($type) {
        // Return default template structure
        $templates = [];

        if ($type === 'blocks') {
            $templates = [
                [
                    'id' => 'hero-1',
                    'title' => __('Hero Section 1', 'theme-builder-pro'),
                    'thumbnail' => TBP_ASSETS_URL . 'images/templates/hero-1.jpg',
                    'categories' => ['hero'],
                    'content' => [],
                ],
                [
                    'id' => 'features-1',
                    'title' => __('Features Grid', 'theme-builder-pro'),
                    'thumbnail' => TBP_ASSETS_URL . 'images/templates/features-1.jpg',
                    'categories' => ['features'],
                    'content' => [],
                ],
                [
                    'id' => 'testimonials-1',
                    'title' => __('Testimonials Carousel', 'theme-builder-pro'),
                    'thumbnail' => TBP_ASSETS_URL . 'images/templates/testimonials-1.jpg',
                    'categories' => ['testimonials'],
                    'content' => [],
                ],
                [
                    'id' => 'pricing-1',
                    'title' => __('Pricing Table', 'theme-builder-pro'),
                    'thumbnail' => TBP_ASSETS_URL . 'images/templates/pricing-1.jpg',
                    'categories' => ['pricing'],
                    'content' => [],
                ],
                [
                    'id' => 'cta-1',
                    'title' => __('Call to Action', 'theme-builder-pro'),
                    'thumbnail' => TBP_ASSETS_URL . 'images/templates/cta-1.jpg',
                    'categories' => ['cta'],
                    'content' => [],
                ],
                [
                    'id' => 'contact-1',
                    'title' => __('Contact Section', 'theme-builder-pro'),
                    'thumbnail' => TBP_ASSETS_URL . 'images/templates/contact-1.jpg',
                    'categories' => ['contact'],
                    'content' => [],
                ],
            ];
        }

        return $templates;
    }

    private function get_user_templates() {
        $posts = get_posts([
            'post_type' => 'tbp_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_tbp_template_type',
                    'value' => 'saved',
                ],
            ],
        ]);

        $templates = [];

        foreach ($posts as $post) {
            $content = TBP_Utils::get_post_content($post->ID);
            $thumbnail = get_the_post_thumbnail_url($post->ID, 'medium');

            $templates[] = [
                'id' => 'user_' . $post->ID,
                'title' => $post->post_title,
                'thumbnail' => $thumbnail ?: TBP_ASSETS_URL . 'images/templates/placeholder.jpg',
                'categories' => [],
                'content' => $content,
            ];
        }

        return $templates;
    }

    private function get_template_data($template_id) {
        // Check if user template
        if (strpos($template_id, 'user_') === 0) {
            $post_id = intval(str_replace('user_', '', $template_id));
            return TBP_Utils::get_post_content($post_id);
        }

        // Get from built-in templates
        $templates = $this->get_builtin_templates('blocks');
        $templates = array_merge($templates, $this->get_builtin_templates('pages'));

        foreach ($templates as $template) {
            if ($template['id'] === $template_id) {
                return $template['content'];
            }
        }

        return null;
    }

    public function save_user_template($title, $content) {
        $post_id = wp_insert_post([
            'post_type' => 'tbp_template',
            'post_title' => $title,
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        update_post_meta($post_id, '_tbp_template_type', 'saved');
        TBP_Utils::save_post_content($post_id, $content);

        return $post_id;
    }
}
