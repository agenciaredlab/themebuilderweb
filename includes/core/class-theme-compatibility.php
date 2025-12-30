<?php
/**
 * Theme Compatibility Layer
 * Ensures TBP works with any WordPress theme
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Theme_Compatibility {

    private $theme_slug;
    private $theme;

    private $supported_themes = [
        'astra',
        'generatepress',
        'oceanwp',
        'kadence',
        'blocksy',
        'neve',
        'storefront',
        'hello-elementor',
        'twentytwentyfour',
        'twentytwentythree',
        'twentytwentytwo',
        'twentytwentyone',
        'divi',
        'avada',
        'enfold',
    ];

    public function __construct() {
        $this->theme = wp_get_theme();
        $this->theme_slug = sanitize_title(get_template());
        $this->init();
    }

    public function init() {
        add_action('after_setup_theme', [$this, 'setup_theme_support'], 100);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_compatibility_styles'], 999);
        add_filter('body_class', [$this, 'add_body_classes']);
        add_filter('template_include', [$this, 'template_include'], 999);
        add_action('get_header', [$this, 'override_header'], 999);
        add_action('get_footer', [$this, 'override_footer'], 999);

        $this->load_theme_compatibility();

        if ($this->is_block_theme()) {
            $this->init_block_theme_compatibility();
        }

        add_action('wp_head', [$this, 'add_compatibility_styles'], 999);
        add_action('after_setup_theme', [$this, 'set_content_width'], 0);
        add_action('widgets_init', [$this, 'register_widget_areas']);
        add_action('after_setup_theme', [$this, 'register_nav_menus']);

        if (class_exists('WooCommerce')) {
            $this->init_woocommerce_compatibility();
        }
    }

    public function setup_theme_support() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo');
        add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
        add_theme_support('customize-selective-refresh-widgets');
        add_theme_support('responsive-embeds');
        add_theme_support('align-wide');
        add_theme_support('wp-block-styles');
        add_theme_support('editor-styles');
    }

    private function is_block_theme() {
        return function_exists('wp_is_block_theme') && wp_is_block_theme();
    }

    private function init_block_theme_compatibility() {
        add_filter('block_template_hierarchy', [$this, 'filter_block_template_hierarchy'], 999);
    }

    public function filter_block_template_hierarchy($templates) {
        if ($this->has_tbp_template()) {
            return [];
        }
        return $templates;
    }

    private function has_tbp_template() {
        $location = $this->get_current_location();
        return !empty(TBP_Theme_Builder::get_template_id($location));
    }

    private function get_current_location() {
        if (is_singular('product')) return 'product';
        if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) return 'product_archive';
        if (is_404()) return '404';
        if (is_search()) return 'search';
        if (is_singular('post')) return 'single';
        if (is_page()) return 'page';
        if (is_archive() || is_home()) return 'archive';
        return 'page';
    }

    private function load_theme_compatibility() {
        $method = 'compat_' . str_replace('-', '_', $this->theme_slug);
        if (method_exists($this, $method)) {
            call_user_func([$this, $method]);
        }
    }

    public function template_include($template) {
        $location = $this->get_current_location();
        $tbp_template_id = TBP_Theme_Builder::get_template_id($location);

        if ($tbp_template_id) {
            $tbp_template = TBP_PLUGIN_DIR . 'templates/' . $location . '.php';
            if (file_exists($tbp_template)) {
                return $tbp_template;
            }
        }
        return $template;
    }

    public function override_header($name) {
        if (TBP_Theme_Builder::get_template_id('header')) {
            require TBP_PLUGIN_DIR . 'templates/header.php';
            remove_all_actions('get_header');
        }
    }

    public function override_footer($name) {
        if (TBP_Theme_Builder::get_template_id('footer')) {
            require TBP_PLUGIN_DIR . 'templates/footer.php';
            remove_all_actions('get_footer');
        }
    }

    public function add_body_classes($classes) {
        $classes[] = 'tbp-active';
        $classes[] = 'tbp-theme-' . $this->theme_slug;

        if (TBP_Theme_Builder::get_template_id('header')) {
            $classes[] = 'tbp-header-active';
        }
        if (TBP_Theme_Builder::get_template_id('footer')) {
            $classes[] = 'tbp-footer-active';
        }
        return $classes;
    }

    public function enqueue_compatibility_styles() {
        wp_add_inline_style('tbp-frontend', $this->get_compatibility_css());
    }

    private function get_compatibility_css() {
        return '
            .tbp-header-active .site-header,
            .tbp-header-active #masthead,
            .tbp-header-active #site-header,
            .tbp-header-active header.header,
            .tbp-header-active .ast-header,
            .tbp-header-active #ast-desktop-header,
            .tbp-header-active .main-header {
                display: none !important;
            }

            .tbp-footer-active .site-footer,
            .tbp-footer-active #colophon,
            .tbp-footer-active #site-footer,
            .tbp-footer-active footer.footer,
            .tbp-footer-active .ast-footer,
            .tbp-footer-active .main-footer {
                display: none !important;
            }

            .tbp-document { max-width: 100%; }
            .tbp-container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
            .tbp-element * { box-sizing: border-box; }
            .tbp-element img { max-width: 100%; height: auto; }
            .tbp-header { position: relative; z-index: 999; }
            .tbp-popup-overlay { z-index: 99999; }
        ';
    }

    public function add_compatibility_styles() {
        echo '<style id="tbp-critical">.tbp-header{opacity:1}.tbp-loading{opacity:0}.tbp-loaded{opacity:1}</style>';
    }

    public function set_content_width() {
        global $content_width;
        if (!isset($content_width)) $content_width = 1200;
    }

    public function register_widget_areas() {
        for ($i = 1; $i <= 4; $i++) {
            register_sidebar([
                'name' => sprintf(__('Footer %d', 'theme-builder-pro'), $i),
                'id' => 'footer-' . $i,
                'before_widget' => '<div id="%1$s" class="widget tbp-widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h4 class="widget-title">',
                'after_title' => '</h4>',
            ]);
        }

        register_sidebar([
            'name' => __('TBP Sidebar', 'theme-builder-pro'),
            'id' => 'tbp-sidebar',
            'before_widget' => '<div id="%1$s" class="widget tbp-widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ]);
    }

    public function register_nav_menus() {
        register_nav_menus([
            'tbp-primary' => __('TBP Primary Menu', 'theme-builder-pro'),
            'tbp-footer' => __('TBP Footer Menu', 'theme-builder-pro'),
            'tbp-mobile' => __('TBP Mobile Menu', 'theme-builder-pro'),
        ]);
    }

    private function init_woocommerce_compatibility() {
        add_action('after_setup_theme', function() {
            add_theme_support('woocommerce');
            add_theme_support('wc-product-gallery-zoom');
            add_theme_support('wc-product-gallery-lightbox');
            add_theme_support('wc-product-gallery-slider');
        });
    }

    private function compat_astra() {
        add_action('wp', function() {
            if (TBP_Theme_Builder::get_template_id('header')) {
                remove_action('astra_header', 'astra_header_markup');
            }
            if (TBP_Theme_Builder::get_template_id('footer')) {
                remove_action('astra_footer', 'astra_footer_markup');
            }
        });
    }

    private function compat_generatepress() {
        add_action('wp', function() {
            if (TBP_Theme_Builder::get_template_id('header')) {
                remove_action('generate_header', 'generate_construct_header');
            }
            if (TBP_Theme_Builder::get_template_id('footer')) {
                remove_action('generate_footer', 'generate_construct_footer');
            }
        });
    }

    private function compat_oceanwp() {
        add_action('wp', function() {
            if (TBP_Theme_Builder::get_template_id('header')) {
                remove_action('ocean_header', 'ocean_header_template');
            }
            if (TBP_Theme_Builder::get_template_id('footer')) {
                remove_action('ocean_footer', 'oceanwp_footer_template');
            }
        });
    }

    private function compat_kadence() {
        add_action('wp', function() {
            if (TBP_Theme_Builder::get_template_id('header')) {
                remove_action('kadence_header', 'Kadence\header_markup');
            }
            if (TBP_Theme_Builder::get_template_id('footer')) {
                remove_action('kadence_footer', 'Kadence\footer_markup');
            }
        });
    }

    private function compat_storefront() {
        add_action('wp', function() {
            if (TBP_Theme_Builder::get_template_id('header')) {
                remove_all_actions('storefront_header');
            }
            if (TBP_Theme_Builder::get_template_id('footer')) {
                remove_all_actions('storefront_footer');
            }
        });
    }

    private function compat_hello_elementor() {
        add_filter('hello_elementor_header_footer', function($show) {
            if (TBP_Theme_Builder::get_template_id('header') || TBP_Theme_Builder::get_template_id('footer')) {
                return false;
            }
            return $show;
        });
    }

    private function compat_divi() {
        add_action('wp', function() {
            if (TBP_Theme_Builder::get_template_id('header')) {
                add_filter('et_header_template', '__return_false');
            }
            if (TBP_Theme_Builder::get_template_id('footer')) {
                add_filter('et_footer_template', '__return_false');
            }
        });
    }

    private function compat_blocksy() {
        add_action('wp', function() {
            if (TBP_Theme_Builder::get_template_id('header')) {
                add_filter('blocksy:header:has_header', '__return_false');
            }
            if (TBP_Theme_Builder::get_template_id('footer')) {
                add_filter('blocksy:footer:has_footer', '__return_false');
            }
        });
    }

    public function get_theme_info() {
        return [
            'name' => $this->theme->get('Name'),
            'slug' => $this->theme_slug,
            'version' => $this->theme->get('Version'),
            'is_block_theme' => $this->is_block_theme(),
            'is_supported' => in_array($this->theme_slug, $this->supported_themes),
        ];
    }
}

new TBP_Theme_Compatibility();
