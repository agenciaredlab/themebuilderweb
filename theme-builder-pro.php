<?php
/**
 * Plugin Name: Theme Builder Pro
 * Plugin URI: https://themebuilderpro.com
 * Description: Professional visual theme builder for WordPress with drag & drop editor, theme builder, popup builder, form builder, and WooCommerce integration.
 * Version: 1.0.0
 * Author: Theme Builder Pro Team
 * Author URI: https://themebuilderpro.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: theme-builder-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin Constants
define('TBP_VERSION', '1.0.0');
define('TBP_PLUGIN_FILE', __FILE__);
define('TBP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TBP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TBP_ASSETS_URL', TBP_PLUGIN_URL . 'assets/');
define('TBP_INCLUDES_DIR', TBP_PLUGIN_DIR . 'includes/');
define('TBP_TEMPLATES_DIR', TBP_PLUGIN_DIR . 'templates/');

/**
 * Main Theme Builder Pro Class
 */
final class Theme_Builder_Pro {

    /**
     * Instance
     */
    private static $instance = null;

    /**
     * Modules Manager
     */
    public $modules_manager;

    /**
     * Widgets Manager
     */
    public $widgets_manager;

    /**
     * Documents Manager
     */
    public $documents_manager;

    /**
     * Dynamic Tags Manager
     */
    public $dynamic_tags;

    /**
     * Theme Builder
     */
    public $theme_builder;

    /**
     * Popup Manager
     */
    public $popup_manager;

    /**
     * Form Manager
     */
    public $form_manager;

    /**
     * Templates Library
     */
    public $templates_library;

    /**
     * Get Instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load Dependencies
     */
    private function load_dependencies() {
        // Core
        require_once TBP_INCLUDES_DIR . 'core/class-autoloader.php';
        require_once TBP_INCLUDES_DIR . 'core/class-utils.php';
        require_once TBP_INCLUDES_DIR . 'core/class-assets.php';
        require_once TBP_INCLUDES_DIR . 'core/class-controls-manager.php';
        require_once TBP_INCLUDES_DIR . 'core/class-schemes-manager.php';
        require_once TBP_INCLUDES_DIR . 'core/class-responsive.php';
        require_once TBP_INCLUDES_DIR . 'core/class-conditions.php';
        require_once TBP_INCLUDES_DIR . 'core/class-db.php';

        // Modules
        require_once TBP_INCLUDES_DIR . 'modules/class-modules-manager.php';

        // Widgets
        require_once TBP_INCLUDES_DIR . 'widgets/class-widgets-manager.php';
        require_once TBP_INCLUDES_DIR . 'widgets/class-widget-base.php';

        // Documents
        require_once TBP_INCLUDES_DIR . 'core/class-documents-manager.php';

        // Dynamic Tags
        require_once TBP_INCLUDES_DIR . 'dynamic/class-dynamic-tags-manager.php';

        // Theme Builder
        require_once TBP_INCLUDES_DIR . 'templates/class-theme-builder.php';

        // Popup
        require_once TBP_INCLUDES_DIR . 'templates/class-popup-manager.php';

        // Templates Library
        require_once TBP_INCLUDES_DIR . 'templates/class-templates-library.php';

        // Forms
        require_once TBP_INCLUDES_DIR . 'forms/class-form-manager.php';

        // API
        require_once TBP_INCLUDES_DIR . 'api/class-rest-api.php';

        // Integrations
        require_once TBP_INCLUDES_DIR . 'integrations/class-integrations-manager.php';

        // Admin
        if (is_admin()) {
            require_once TBP_PLUGIN_DIR . 'admin/class-admin.php';
            require_once TBP_INCLUDES_DIR . 'admin/class-ajax-handlers.php';
        }

        // Theme Compatibility
        require_once TBP_INCLUDES_DIR . 'core/class-theme-compatibility.php';
    }

    /**
     * Initialize Hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('init', [$this, 'register_post_types']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        register_activation_hook(TBP_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(TBP_PLUGIN_FILE, [$this, 'deactivate']);
    }

    /**
     * Initialize
     */
    public function init() {
        // Load textdomain
        load_plugin_textdomain('theme-builder-pro', false, dirname(plugin_basename(TBP_PLUGIN_FILE)) . '/languages');

        // Initialize managers
        $this->modules_manager = new TBP_Modules_Manager();
        $this->widgets_manager = new TBP_Widgets_Manager();
        $this->documents_manager = new TBP_Documents_Manager();
        $this->dynamic_tags = new TBP_Dynamic_Tags_Manager();
        $this->theme_builder = new TBP_Theme_Builder();
        $this->popup_manager = new TBP_Popup_Manager();
        $this->form_manager = new TBP_Form_Manager();
        $this->templates_library = new TBP_Templates_Library();

        // Initialize REST API
        new TBP_REST_API();

        // Initialize integrations
        new TBP_Integrations_Manager();

        do_action('tbp/init');
    }

    /**
     * Register Custom Post Types
     */
    public function register_post_types() {
        // Templates
        register_post_type('tbp_template', [
            'labels' => [
                'name' => __('Templates', 'theme-builder-pro'),
                'singular_name' => __('Template', 'theme-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'author', 'custom-fields'],
            'show_in_rest' => true,
        ]);

        // Theme Builder Documents (Header, Footer, etc.)
        register_post_type('tbp_theme_doc', [
            'labels' => [
                'name' => __('Theme Documents', 'theme-builder-pro'),
                'singular_name' => __('Theme Document', 'theme-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'author', 'custom-fields'],
            'show_in_rest' => true,
        ]);

        // Popups
        register_post_type('tbp_popup', [
            'labels' => [
                'name' => __('Popups', 'theme-builder-pro'),
                'singular_name' => __('Popup', 'theme-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'author', 'custom-fields'],
            'show_in_rest' => true,
        ]);

        // Forms
        register_post_type('tbp_form', [
            'labels' => [
                'name' => __('Forms', 'theme-builder-pro'),
                'singular_name' => __('Form', 'theme-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'author', 'custom-fields'],
            'show_in_rest' => true,
        ]);

        // Form Submissions
        register_post_type('tbp_submission', [
            'labels' => [
                'name' => __('Submissions', 'theme-builder-pro'),
                'singular_name' => __('Submission', 'theme-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'custom-fields'],
            'show_in_rest' => true,
        ]);

        // Global Widgets
        register_post_type('tbp_global_widget', [
            'labels' => [
                'name' => __('Global Widgets', 'theme-builder-pro'),
                'singular_name' => __('Global Widget', 'theme-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'author', 'custom-fields'],
            'show_in_rest' => true,
        ]);
    }

    /**
     * Enqueue Frontend Assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'tbp-frontend',
            TBP_ASSETS_URL . 'css/frontend.css',
            [],
            TBP_VERSION
        );

        wp_enqueue_script(
            'tbp-frontend',
            TBP_ASSETS_URL . 'js/frontend/frontend.js',
            ['jquery'],
            TBP_VERSION,
            true
        );

        wp_localize_script('tbp-frontend', 'tbpFrontend', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('tbp/v1/'),
            'nonce' => wp_create_nonce('tbp_frontend'),
        ]);
    }

    /**
     * Enqueue Admin Assets
     */
    public function enqueue_admin_assets($hook) {
        if (!$this->is_editor_page()) {
            return;
        }

        // Editor Styles
        wp_enqueue_style(
            'tbp-editor',
            TBP_ASSETS_URL . 'css/editor.css',
            [],
            TBP_VERSION
        );

        // Editor Scripts
        wp_enqueue_script(
            'tbp-editor',
            TBP_ASSETS_URL . 'js/editor/editor.js',
            ['jquery', 'wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch'],
            TBP_VERSION,
            true
        );

        wp_localize_script('tbp-editor', 'tbpEditor', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('tbp/v1/'),
            'nonce' => wp_create_nonce('tbp_editor'),
            'postId' => get_the_ID(),
            'settings' => $this->get_editor_settings(),
            'widgets' => $this->widgets_manager->get_widget_types(),
            'controls' => TBP_Controls_Manager::get_controls(),
            'schemes' => TBP_Schemes_Manager::get_schemes(),
            'dynamicTags' => $this->dynamic_tags->get_tags(),
            'breakpoints' => TBP_Responsive::get_breakpoints(),
            'i18n' => $this->get_i18n_strings(),
        ]);
    }

    /**
     * Check if Editor Page
     */
    private function is_editor_page() {
        global $pagenow;

        if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
            return true;
        }

        if (isset($_GET['page']) && strpos($_GET['page'], 'theme-builder-pro') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get Editor Settings
     */
    private function get_editor_settings() {
        return apply_filters('tbp/editor/settings', [
            'defaultColors' => [
                '#000000', '#ffffff', '#f44336', '#e91e63', '#9c27b0',
                '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4',
                '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b',
                '#ffc107', '#ff9800', '#ff5722', '#795548', '#607d8b',
            ],
            'defaultFonts' => [
                'Arial', 'Helvetica', 'Georgia', 'Times New Roman',
                'Verdana', 'Roboto', 'Open Sans', 'Lato', 'Montserrat',
                'Poppins', 'Raleway', 'Oswald', 'Source Sans Pro',
            ],
            'gridColumns' => 12,
            'containerWidth' => 1140,
            'sectionGap' => 20,
            'columnGap' => 20,
        ]);
    }

    /**
     * Get i18n Strings
     */
    private function get_i18n_strings() {
        return [
            'save' => __('Save', 'theme-builder-pro'),
            'publish' => __('Publish', 'theme-builder-pro'),
            'preview' => __('Preview', 'theme-builder-pro'),
            'undo' => __('Undo', 'theme-builder-pro'),
            'redo' => __('Redo', 'theme-builder-pro'),
            'addSection' => __('Add Section', 'theme-builder-pro'),
            'addWidget' => __('Add Widget', 'theme-builder-pro'),
            'settings' => __('Settings', 'theme-builder-pro'),
            'style' => __('Style', 'theme-builder-pro'),
            'advanced' => __('Advanced', 'theme-builder-pro'),
            'responsive' => __('Responsive', 'theme-builder-pro'),
            'desktop' => __('Desktop', 'theme-builder-pro'),
            'tablet' => __('Tablet', 'theme-builder-pro'),
            'mobile' => __('Mobile', 'theme-builder-pro'),
            'delete' => __('Delete', 'theme-builder-pro'),
            'duplicate' => __('Duplicate', 'theme-builder-pro'),
            'copy' => __('Copy', 'theme-builder-pro'),
            'paste' => __('Paste', 'theme-builder-pro'),
            'pasteStyle' => __('Paste Style', 'theme-builder-pro'),
            'resetStyle' => __('Reset Style', 'theme-builder-pro'),
            'navigator' => __('Navigator', 'theme-builder-pro'),
            'history' => __('History', 'theme-builder-pro'),
            'globalColors' => __('Global Colors', 'theme-builder-pro'),
            'globalFonts' => __('Global Fonts', 'theme-builder-pro'),
        ];
    }

    /**
     * Plugin Activation
     */
    public function activate() {
        // Create database tables
        TBP_DB::create_tables();

        // Set default options
        $this->set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin Deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Set Default Options
     */
    private function set_default_options() {
        $defaults = [
            'tbp_global_colors' => [
                'primary' => '#0073aa',
                'secondary' => '#23282d',
                'text' => '#333333',
                'accent' => '#00a0d2',
            ],
            'tbp_global_fonts' => [
                'primary' => 'Roboto',
                'secondary' => 'Open Sans',
                'accent' => 'Montserrat',
            ],
            'tbp_breakpoints' => [
                'desktop' => 1200,
                'tablet' => 992,
                'tablet_portrait' => 768,
                'mobile' => 576,
            ],
            'tbp_container_width' => 1140,
            'tbp_enable_animations' => true,
            'tbp_enable_lazyload' => true,
            'tbp_google_fonts' => true,
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }
}

/**
 * Get Theme Builder Pro Instance
 */
function theme_builder_pro() {
    return Theme_Builder_Pro::instance();
}

// Initialize
theme_builder_pro();
