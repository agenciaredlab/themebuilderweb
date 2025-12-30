<?php
/**
 * Theme Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Theme_Builder {

    /**
     * Locations
     */
    private $locations = [];

    /**
     * Active Documents
     */
    private $active_documents = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_locations();
        $this->init_hooks();
    }

    /**
     * Register Locations
     */
    private function register_locations() {
        $this->locations = [
            'header' => [
                'label' => __('Header', 'theme-builder-pro'),
                'icon' => 'eicon-header',
                'description' => __('Design your site header', 'theme-builder-pro'),
                'multiple' => true,
                'hook' => 'get_header',
            ],
            'footer' => [
                'label' => __('Footer', 'theme-builder-pro'),
                'icon' => 'eicon-footer',
                'description' => __('Design your site footer', 'theme-builder-pro'),
                'multiple' => true,
                'hook' => 'get_footer',
            ],
            'single' => [
                'label' => __('Single Post', 'theme-builder-pro'),
                'icon' => 'eicon-single-post',
                'description' => __('Design single post templates', 'theme-builder-pro'),
                'multiple' => true,
            ],
            'single-page' => [
                'label' => __('Single Page', 'theme-builder-pro'),
                'icon' => 'eicon-single-page',
                'description' => __('Design single page templates', 'theme-builder-pro'),
                'multiple' => true,
            ],
            'archive' => [
                'label' => __('Archive', 'theme-builder-pro'),
                'icon' => 'eicon-archive',
                'description' => __('Design archive templates', 'theme-builder-pro'),
                'multiple' => true,
            ],
            'search' => [
                'label' => __('Search Results', 'theme-builder-pro'),
                'icon' => 'eicon-search',
                'description' => __('Design search results template', 'theme-builder-pro'),
                'multiple' => false,
            ],
            'error-404' => [
                'label' => __('404 Page', 'theme-builder-pro'),
                'icon' => 'eicon-error-404',
                'description' => __('Design 404 error page', 'theme-builder-pro'),
                'multiple' => false,
            ],
        ];

        // WooCommerce locations
        if (class_exists('WooCommerce')) {
            $this->locations['product'] = [
                'label' => __('Single Product', 'theme-builder-pro'),
                'icon' => 'eicon-single-product',
                'description' => __('Design single product template', 'theme-builder-pro'),
                'multiple' => true,
            ];

            $this->locations['product-archive'] = [
                'label' => __('Product Archive', 'theme-builder-pro'),
                'icon' => 'eicon-products',
                'description' => __('Design shop and product category pages', 'theme-builder-pro'),
                'multiple' => true,
            ];

            $this->locations['cart'] = [
                'label' => __('Cart', 'theme-builder-pro'),
                'icon' => 'eicon-cart',
                'description' => __('Design cart page', 'theme-builder-pro'),
                'multiple' => false,
            ];

            $this->locations['checkout'] = [
                'label' => __('Checkout', 'theme-builder-pro'),
                'icon' => 'eicon-checkout',
                'description' => __('Design checkout page', 'theme-builder-pro'),
                'multiple' => false,
            ];

            $this->locations['my-account'] = [
                'label' => __('My Account', 'theme-builder-pro'),
                'icon' => 'eicon-lock-user',
                'description' => __('Design my account pages', 'theme-builder-pro'),
                'multiple' => false,
            ];

            $this->locations['thankyou'] = [
                'label' => __('Thank You', 'theme-builder-pro'),
                'icon' => 'eicon-check-circle',
                'description' => __('Design order confirmation page', 'theme-builder-pro'),
                'multiple' => false,
            ];
        }

        $this->locations = apply_filters('tbp/theme_builder/locations', $this->locations);
    }

    /**
     * Initialize Hooks
     */
    private function init_hooks() {
        // Header replacement
        add_action('get_header', [$this, 'maybe_replace_header'], 999);

        // Footer replacement
        add_action('get_footer', [$this, 'maybe_replace_footer'], 999);

        // Template redirects
        add_filter('template_include', [$this, 'template_include'], 999);

        // Canvas template
        add_action('tbp/template/canvas/before_content', [$this, 'render_header']);
        add_action('tbp/template/canvas/after_content', [$this, 'render_footer']);
    }

    /**
     * Get Locations
     */
    public function get_locations() {
        return $this->locations;
    }

    /**
     * Get Location
     */
    public function get_location($location) {
        return isset($this->locations[$location]) ? $this->locations[$location] : null;
    }

    /**
     * Get Active Document
     */
    public function get_active_document($location) {
        if (!isset($this->active_documents[$location])) {
            $this->active_documents[$location] = TBP_Conditions::get_active_document($location);
        }

        return $this->active_documents[$location];
    }

    /**
     * Maybe Replace Header
     */
    public function maybe_replace_header($name) {
        $header_id = $this->get_active_document('header');

        if (!$header_id) {
            return;
        }

        // Load our header template
        $this->load_template('header', $header_id);

        // Prevent default header
        $templates = [];
        if ($name) {
            $templates[] = "header-{$name}.php";
        }
        $templates[] = 'header.php';

        // Remove the default header
        remove_all_actions('wp_head');
        wp_head();

        exit;
    }

    /**
     * Maybe Replace Footer
     */
    public function maybe_replace_footer($name) {
        $footer_id = $this->get_active_document('footer');

        if (!$footer_id) {
            return;
        }

        // Load our footer template
        $this->load_template('footer', $footer_id);

        // Prevent default footer
        wp_footer();
        exit;
    }

    /**
     * Template Include
     */
    public function template_include($template) {
        // Check for single templates
        if (is_singular()) {
            $post_type = get_post_type();
            $location = $post_type === 'page' ? 'single-page' : 'single';

            // Check for WooCommerce product
            if ($post_type === 'product' && class_exists('WooCommerce')) {
                $location = 'product';
            }

            $template_id = $this->get_active_document($location);

            if ($template_id) {
                return $this->get_canvas_template($template_id, $location);
            }
        }

        // Check for archive templates
        if (is_archive() || is_home()) {
            $location = 'archive';

            // Check for WooCommerce archive
            if (class_exists('WooCommerce') && (is_shop() || is_product_category() || is_product_tag())) {
                $location = 'product-archive';
            }

            $template_id = $this->get_active_document($location);

            if ($template_id) {
                return $this->get_canvas_template($template_id, $location);
            }
        }

        // Check for search
        if (is_search()) {
            $template_id = $this->get_active_document('search');

            if ($template_id) {
                return $this->get_canvas_template($template_id, 'search');
            }
        }

        // Check for 404
        if (is_404()) {
            $template_id = $this->get_active_document('error-404');

            if ($template_id) {
                return $this->get_canvas_template($template_id, 'error-404');
            }
        }

        // WooCommerce pages
        if (class_exists('WooCommerce')) {
            if (is_cart()) {
                $template_id = $this->get_active_document('cart');
                if ($template_id) {
                    return $this->get_canvas_template($template_id, 'cart');
                }
            }

            if (is_checkout()) {
                $template_id = $this->get_active_document('checkout');
                if ($template_id) {
                    return $this->get_canvas_template($template_id, 'checkout');
                }
            }

            if (is_account_page()) {
                $template_id = $this->get_active_document('my-account');
                if ($template_id) {
                    return $this->get_canvas_template($template_id, 'my-account');
                }
            }
        }

        return $template;
    }

    /**
     * Get Canvas Template
     */
    private function get_canvas_template($template_id, $location) {
        // Store for later use
        $GLOBALS['tbp_template_id'] = $template_id;
        $GLOBALS['tbp_template_location'] = $location;

        // Return our canvas template
        return TBP_TEMPLATES_DIR . 'theme-builder/canvas.php';
    }

    /**
     * Load Template
     */
    private function load_template($type, $template_id) {
        $document = theme_builder_pro()->documents_manager->get($template_id);

        if (!$document) {
            return;
        }

        // Enqueue styles
        $css = $document->get_css();
        if ($css) {
            echo '<style id="tbp-' . $type . '-css">' . $css . '</style>';
        }

        // Render content
        echo $document->render();
    }

    /**
     * Render Header
     */
    public function render_header() {
        $header_id = $this->get_active_document('header');

        if ($header_id) {
            $this->load_template('header', $header_id);
        }
    }

    /**
     * Render Footer
     */
    public function render_footer() {
        $footer_id = $this->get_active_document('footer');

        if ($footer_id) {
            $this->load_template('footer', $footer_id);
        }
    }

    /**
     * Render Content
     */
    public function render_content($template_id) {
        $document = theme_builder_pro()->documents_manager->get($template_id);

        if (!$document) {
            return;
        }

        // Enqueue styles
        $css = $document->get_css();
        if ($css) {
            echo '<style id="tbp-content-css">' . $css . '</style>';
        }

        // Render content
        echo $document->render();
    }

    /**
     * Get Documents for Location
     */
    public function get_documents_for_location($location) {
        $posts = get_posts([
            'post_type' => 'tbp_theme_doc',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_tbp_document_type',
                    'value' => $location,
                ],
            ],
        ]);

        return array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'conditions' => TBP_Conditions::get_document_conditions($post->ID),
                'priority' => get_post_meta($post->ID, '_tbp_priority', true) ?: 0,
            ];
        }, $posts);
    }

    /**
     * Create Theme Document
     */
    public function create_document($location, $title = '') {
        if (!$title) {
            $location_data = $this->get_location($location);
            $title = $location_data ? $location_data['label'] : ucfirst($location);
        }

        $post_id = wp_insert_post([
            'post_type' => 'tbp_theme_doc',
            'post_title' => $title,
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        update_post_meta($post_id, '_tbp_document_type', $location);
        update_post_meta($post_id, '_tbp_edit_mode', 'builder');
        update_post_meta($post_id, '_tbp_content', wp_json_encode(['elements' => []]));

        // Set default conditions
        $default_conditions = $this->get_default_conditions($location);
        TBP_Conditions::save_document_conditions($post_id, $default_conditions);

        return $post_id;
    }

    /**
     * Get Default Conditions
     */
    private function get_default_conditions($location) {
        switch ($location) {
            case 'header':
            case 'footer':
                return [
                    [
                        'type' => 'entire_site',
                        'include' => true,
                    ],
                ];

            case 'single':
                return [
                    [
                        'type' => 'post',
                        'include' => true,
                    ],
                ];

            case 'single-page':
                return [
                    [
                        'type' => 'page',
                        'include' => true,
                    ],
                ];

            case 'archive':
                return [
                    [
                        'type' => 'archive',
                        'include' => true,
                    ],
                ];

            case 'search':
                return [
                    [
                        'type' => 'search',
                        'include' => true,
                    ],
                ];

            case 'error-404':
                return [
                    [
                        'type' => 'error_404',
                        'include' => true,
                    ],
                ];

            case 'product':
                return [
                    [
                        'type' => 'woo_product',
                        'include' => true,
                    ],
                ];

            case 'product-archive':
                return [
                    [
                        'type' => 'woo_shop',
                        'include' => true,
                    ],
                    [
                        'type' => 'woo_product_category',
                        'include' => true,
                    ],
                ];

            default:
                return [];
        }
    }

    /**
     * Get Preview URL
     */
    public function get_preview_url($location) {
        switch ($location) {
            case 'header':
            case 'footer':
                return home_url('/');

            case 'single':
                $posts = get_posts(['numberposts' => 1]);
                return !empty($posts) ? get_permalink($posts[0]) : home_url('/');

            case 'single-page':
                $pages = get_pages(['number' => 1]);
                return !empty($pages) ? get_permalink($pages[0]) : home_url('/');

            case 'archive':
                $categories = get_categories(['number' => 1]);
                return !empty($categories) ? get_category_link($categories[0]) : home_url('/');

            case 'search':
                return home_url('/?s=test');

            case 'error-404':
                return home_url('/this-page-does-not-exist-404');

            case 'product':
                if (class_exists('WooCommerce')) {
                    $products = wc_get_products(['limit' => 1]);
                    return !empty($products) ? get_permalink($products[0]->get_id()) : home_url('/');
                }
                return home_url('/');

            case 'product-archive':
                if (class_exists('WooCommerce')) {
                    return wc_get_page_permalink('shop');
                }
                return home_url('/');

            case 'cart':
                if (class_exists('WooCommerce')) {
                    return wc_get_cart_url();
                }
                return home_url('/');

            case 'checkout':
                if (class_exists('WooCommerce')) {
                    return wc_get_checkout_url();
                }
                return home_url('/');

            default:
                return home_url('/');
        }
    }
}
