<?php
/**
 * Integrations Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Integrations_Manager {

    /**
     * Integrations
     */
    private $integrations = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_integrations();
        $this->init_integrations();
    }

    /**
     * Register Integrations
     */
    private function register_integrations() {
        $this->integrations = [
            'woocommerce' => [
                'label' => __('WooCommerce', 'theme-builder-pro'),
                'description' => __('Build custom WooCommerce pages', 'theme-builder-pro'),
                'class' => 'TBP_Integration_WooCommerce',
                'condition' => class_exists('WooCommerce'),
            ],
            'acf' => [
                'label' => __('Advanced Custom Fields', 'theme-builder-pro'),
                'description' => __('Display ACF fields dynamically', 'theme-builder-pro'),
                'class' => 'TBP_Integration_ACF',
                'condition' => class_exists('ACF'),
            ],
            'wpml' => [
                'label' => __('WPML', 'theme-builder-pro'),
                'description' => __('Multilingual support', 'theme-builder-pro'),
                'class' => 'TBP_Integration_WPML',
                'condition' => defined('ICL_SITEPRESS_VERSION'),
            ],
            'polylang' => [
                'label' => __('Polylang', 'theme-builder-pro'),
                'description' => __('Multilingual support', 'theme-builder-pro'),
                'class' => 'TBP_Integration_Polylang',
                'condition' => defined('POLYLANG_VERSION'),
            ],
            'yoast' => [
                'label' => __('Yoast SEO', 'theme-builder-pro'),
                'description' => __('SEO breadcrumbs and schema', 'theme-builder-pro'),
                'class' => 'TBP_Integration_Yoast',
                'condition' => defined('WPSEO_VERSION'),
            ],
            'rankmath' => [
                'label' => __('Rank Math', 'theme-builder-pro'),
                'description' => __('SEO breadcrumbs and schema', 'theme-builder-pro'),
                'class' => 'TBP_Integration_RankMath',
                'condition' => class_exists('RankMath'),
            ],
            'cf7' => [
                'label' => __('Contact Form 7', 'theme-builder-pro'),
                'description' => __('Style CF7 forms', 'theme-builder-pro'),
                'class' => 'TBP_Integration_CF7',
                'condition' => defined('WPCF7_VERSION'),
            ],
            'gravityforms' => [
                'label' => __('Gravity Forms', 'theme-builder-pro'),
                'description' => __('Style Gravity Forms', 'theme-builder-pro'),
                'class' => 'TBP_Integration_GravityForms',
                'condition' => class_exists('GFCommon'),
            ],
            'wpforms' => [
                'label' => __('WPForms', 'theme-builder-pro'),
                'description' => __('Style WPForms', 'theme-builder-pro'),
                'class' => 'TBP_Integration_WPForms',
                'condition' => defined('WPFORMS_VERSION'),
            ],
        ];

        $this->integrations = apply_filters('tbp/integrations', $this->integrations);
    }

    /**
     * Initialize Integrations
     */
    private function init_integrations() {
        foreach ($this->integrations as $id => $integration) {
            if ($integration['condition'] && class_exists($integration['class'])) {
                new $integration['class']();
            }
        }
    }

    /**
     * Get Integrations
     */
    public function get_integrations() {
        return $this->integrations;
    }

    /**
     * Get Active Integrations
     */
    public function get_active_integrations() {
        return array_filter($this->integrations, function($integration) {
            return $integration['condition'];
        });
    }

    /**
     * Is Integration Active
     */
    public function is_active($id) {
        return isset($this->integrations[$id]) && $this->integrations[$id]['condition'];
    }
}

/**
 * WooCommerce Integration
 */
class TBP_Integration_WooCommerce {

    public function __construct() {
        add_action('tbp/widgets/register', [$this, 'register_widgets']);
        add_filter('tbp/dynamic_tags', [$this, 'register_dynamic_tags']);
    }

    public function register_widgets($manager) {
        // Product widgets
        $manager->register_widget('product-title', [
            'title' => __('Product Title', 'theme-builder-pro'),
            'icon' => 'eicon-product-title',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Title',
        ]);

        $manager->register_widget('product-price', [
            'title' => __('Product Price', 'theme-builder-pro'),
            'icon' => 'eicon-product-price',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Price',
        ]);

        $manager->register_widget('product-images', [
            'title' => __('Product Images', 'theme-builder-pro'),
            'icon' => 'eicon-product-images',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Images',
        ]);

        $manager->register_widget('product-add-to-cart', [
            'title' => __('Add to Cart', 'theme-builder-pro'),
            'icon' => 'eicon-product-add-to-cart',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Add_To_Cart',
        ]);

        $manager->register_widget('product-rating', [
            'title' => __('Product Rating', 'theme-builder-pro'),
            'icon' => 'eicon-product-rating',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Rating',
        ]);

        $manager->register_widget('product-stock', [
            'title' => __('Product Stock', 'theme-builder-pro'),
            'icon' => 'eicon-product-stock',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Stock',
        ]);

        $manager->register_widget('product-meta', [
            'title' => __('Product Meta', 'theme-builder-pro'),
            'icon' => 'eicon-product-meta',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Meta',
        ]);

        $manager->register_widget('product-content', [
            'title' => __('Product Content', 'theme-builder-pro'),
            'icon' => 'eicon-product-content',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Content',
        ]);

        $manager->register_widget('product-short-description', [
            'title' => __('Short Description', 'theme-builder-pro'),
            'icon' => 'eicon-product-description',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Short_Description',
        ]);

        $manager->register_widget('product-data-tabs', [
            'title' => __('Product Data Tabs', 'theme-builder-pro'),
            'icon' => 'eicon-product-tabs',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Data_Tabs',
        ]);

        $manager->register_widget('product-additional-info', [
            'title' => __('Additional Information', 'theme-builder-pro'),
            'icon' => 'eicon-product-info',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Additional_Info',
        ]);

        $manager->register_widget('product-related', [
            'title' => __('Related Products', 'theme-builder-pro'),
            'icon' => 'eicon-product-related',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Related',
        ]);

        $manager->register_widget('product-upsells', [
            'title' => __('Upsells', 'theme-builder-pro'),
            'icon' => 'eicon-product-upsell',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Upsells',
        ]);

        // Archive widgets
        $manager->register_widget('products', [
            'title' => __('Products', 'theme-builder-pro'),
            'icon' => 'eicon-products',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Products',
        ]);

        $manager->register_widget('product-categories', [
            'title' => __('Product Categories', 'theme-builder-pro'),
            'icon' => 'eicon-product-categories',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Product_Categories',
        ]);

        $manager->register_widget('archive-products', [
            'title' => __('Archive Products', 'theme-builder-pro'),
            'icon' => 'eicon-archive-products',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Archive_Products',
        ]);

        $manager->register_widget('archive-description', [
            'title' => __('Archive Description', 'theme-builder-pro'),
            'icon' => 'eicon-archive-description',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Archive_Description',
        ]);

        // Cart widgets
        $manager->register_widget('cart', [
            'title' => __('Cart', 'theme-builder-pro'),
            'icon' => 'eicon-cart',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Cart',
        ]);

        $manager->register_widget('cart-totals', [
            'title' => __('Cart Totals', 'theme-builder-pro'),
            'icon' => 'eicon-cart-totals',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Cart_Totals',
        ]);

        $manager->register_widget('menu-cart', [
            'title' => __('Menu Cart', 'theme-builder-pro'),
            'icon' => 'eicon-cart-medium',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Menu_Cart',
        ]);

        // Checkout widgets
        $manager->register_widget('checkout', [
            'title' => __('Checkout', 'theme-builder-pro'),
            'icon' => 'eicon-checkout',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Checkout',
        ]);

        // My Account widgets
        $manager->register_widget('my-account', [
            'title' => __('My Account', 'theme-builder-pro'),
            'icon' => 'eicon-my-account',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_My_Account',
        ]);

        // Purchase Summary
        $manager->register_widget('purchase-summary', [
            'title' => __('Purchase Summary', 'theme-builder-pro'),
            'icon' => 'eicon-purchase-summary',
            'categories' => ['woocommerce'],
            'class' => 'TBP_Widget_Purchase_Summary',
        ]);
    }

    public function register_dynamic_tags($tags) {
        // WooCommerce tags are already registered in Dynamic Tags Manager
        return $tags;
    }
}

/**
 * ACF Integration
 */
class TBP_Integration_ACF {

    public function __construct() {
        add_filter('tbp/dynamic_tags', [$this, 'register_dynamic_tags']);
    }

    public function register_dynamic_tags($tags) {
        $tags['acf_text'] = [
            'label' => __('ACF Text', 'theme-builder-pro'),
            'group' => 'acf',
            'categories' => ['text'],
            'callback' => [$this, 'render_text_field'],
            'controls' => [
                'field' => [
                    'type' => 'text',
                    'label' => __('Field Name', 'theme-builder-pro'),
                ],
            ],
        ];

        $tags['acf_image'] = [
            'label' => __('ACF Image', 'theme-builder-pro'),
            'group' => 'acf',
            'categories' => ['image'],
            'callback' => [$this, 'render_image_field'],
            'controls' => [
                'field' => [
                    'type' => 'text',
                    'label' => __('Field Name', 'theme-builder-pro'),
                ],
                'size' => [
                    'type' => 'select',
                    'label' => __('Size', 'theme-builder-pro'),
                    'options' => [
                        'thumbnail' => 'Thumbnail',
                        'medium' => 'Medium',
                        'large' => 'Large',
                        'full' => 'Full',
                    ],
                    'default' => 'full',
                ],
            ],
        ];

        $tags['acf_url'] = [
            'label' => __('ACF URL', 'theme-builder-pro'),
            'group' => 'acf',
            'categories' => ['url'],
            'callback' => [$this, 'render_url_field'],
            'controls' => [
                'field' => [
                    'type' => 'text',
                    'label' => __('Field Name', 'theme-builder-pro'),
                ],
            ],
        ];

        $tags['acf_gallery'] = [
            'label' => __('ACF Gallery', 'theme-builder-pro'),
            'group' => 'acf',
            'categories' => ['gallery'],
            'callback' => [$this, 'render_gallery_field'],
            'controls' => [
                'field' => [
                    'type' => 'text',
                    'label' => __('Field Name', 'theme-builder-pro'),
                ],
            ],
        ];

        $tags['acf_repeater'] = [
            'label' => __('ACF Repeater', 'theme-builder-pro'),
            'group' => 'acf',
            'categories' => ['repeater'],
            'callback' => [$this, 'render_repeater_field'],
            'controls' => [
                'field' => [
                    'type' => 'text',
                    'label' => __('Field Name', 'theme-builder-pro'),
                ],
            ],
        ];

        return $tags;
    }

    public function render_text_field($post_id, $settings) {
        $field = $settings['field'] ?? '';
        if (!$field || !function_exists('get_field')) {
            return '';
        }
        return get_field($field, $post_id);
    }

    public function render_image_field($post_id, $settings) {
        $field = $settings['field'] ?? '';
        $size = $settings['size'] ?? 'full';

        if (!$field || !function_exists('get_field')) {
            return '';
        }

        $image = get_field($field, $post_id);

        if (is_array($image)) {
            return isset($image['sizes'][$size]) ? $image['sizes'][$size] : $image['url'];
        }

        if (is_numeric($image)) {
            return wp_get_attachment_image_url($image, $size);
        }

        return $image;
    }

    public function render_url_field($post_id, $settings) {
        $field = $settings['field'] ?? '';
        if (!$field || !function_exists('get_field')) {
            return '';
        }

        $value = get_field($field, $post_id);

        if (is_array($value)) {
            return $value['url'] ?? '';
        }

        return $value;
    }

    public function render_gallery_field($post_id, $settings) {
        $field = $settings['field'] ?? '';
        if (!$field || !function_exists('get_field')) {
            return [];
        }

        $images = get_field($field, $post_id);

        if (!is_array($images)) {
            return [];
        }

        return array_map(function($image) {
            return [
                'id' => $image['ID'] ?? 0,
                'url' => $image['url'] ?? '',
            ];
        }, $images);
    }

    public function render_repeater_field($post_id, $settings) {
        $field = $settings['field'] ?? '';
        if (!$field || !function_exists('get_field')) {
            return [];
        }

        return get_field($field, $post_id) ?: [];
    }
}

/**
 * WPML Integration
 */
class TBP_Integration_WPML {

    public function __construct() {
        add_filter('tbp/content/before_save', [$this, 'translate_content']);
    }

    public function translate_content($content) {
        // Handle content translation
        return $content;
    }
}

/**
 * Polylang Integration
 */
class TBP_Integration_Polylang {

    public function __construct() {
        add_filter('tbp/template/conditions', [$this, 'add_language_conditions']);
    }

    public function add_language_conditions($conditions) {
        if (!function_exists('pll_languages_list')) {
            return $conditions;
        }

        $languages = pll_languages_list(['fields' => 'name']);

        $conditions['polylang'] = [
            'label' => __('Language', 'theme-builder-pro'),
            'conditions' => [],
        ];

        foreach ($languages as $lang) {
            $conditions['polylang']['conditions']['lang_' . $lang] = [
                'label' => $lang,
                'callback' => function() use ($lang) {
                    return pll_current_language('name') === $lang;
                },
            ];
        }

        return $conditions;
    }
}

/**
 * Yoast SEO Integration
 */
class TBP_Integration_Yoast {

    public function __construct() {
        add_action('tbp/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($manager) {
        $manager->register_widget('yoast-breadcrumbs', [
            'title' => __('Yoast Breadcrumbs', 'theme-builder-pro'),
            'icon' => 'eicon-yoast',
            'categories' => ['site'],
            'class' => 'TBP_Widget_Yoast_Breadcrumbs',
        ]);
    }
}

/**
 * Rank Math Integration
 */
class TBP_Integration_RankMath {

    public function __construct() {
        add_action('tbp/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($manager) {
        $manager->register_widget('rankmath-breadcrumbs', [
            'title' => __('Rank Math Breadcrumbs', 'theme-builder-pro'),
            'icon' => 'eicon-site-search',
            'categories' => ['site'],
            'class' => 'TBP_Widget_RankMath_Breadcrumbs',
        ]);
    }
}

/**
 * Contact Form 7 Integration
 */
class TBP_Integration_CF7 {

    public function __construct() {
        add_action('tbp/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($manager) {
        $manager->register_widget('cf7-form', [
            'title' => __('Contact Form 7', 'theme-builder-pro'),
            'icon' => 'eicon-form-horizontal',
            'categories' => ['form'],
            'class' => 'TBP_Widget_CF7_Form',
        ]);
    }
}

/**
 * Gravity Forms Integration
 */
class TBP_Integration_GravityForms {

    public function __construct() {
        add_action('tbp/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($manager) {
        $manager->register_widget('gravity-form', [
            'title' => __('Gravity Forms', 'theme-builder-pro'),
            'icon' => 'eicon-form-horizontal',
            'categories' => ['form'],
            'class' => 'TBP_Widget_Gravity_Form',
        ]);
    }
}

/**
 * WPForms Integration
 */
class TBP_Integration_WPForms {

    public function __construct() {
        add_action('tbp/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($manager) {
        $manager->register_widget('wpforms', [
            'title' => __('WPForms', 'theme-builder-pro'),
            'icon' => 'eicon-form-horizontal',
            'categories' => ['form'],
            'class' => 'TBP_Widget_WPForms',
        ]);
    }
}
