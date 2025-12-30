<?php
/**
 * Dynamic Tags Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Dynamic_Tags_Manager {

    private $tags = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_tags();
    }

    /**
     * Register Tags
     */
    private function register_tags() {
        // Post Tags
        $this->register_tag('post_title', [
            'label' => __('Post Title', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text'],
            'callback' => [$this, 'get_post_title'],
        ]);

        $this->register_tag('post_excerpt', [
            'label' => __('Post Excerpt', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text'],
            'callback' => [$this, 'get_post_excerpt'],
        ]);

        $this->register_tag('post_content', [
            'label' => __('Post Content', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text'],
            'callback' => [$this, 'get_post_content'],
        ]);

        $this->register_tag('post_date', [
            'label' => __('Post Date', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text'],
            'callback' => [$this, 'get_post_date'],
            'controls' => [
                'format' => [
                    'type' => 'text',
                    'label' => __('Date Format', 'theme-builder-pro'),
                    'default' => get_option('date_format'),
                ],
            ],
        ]);

        $this->register_tag('post_time', [
            'label' => __('Post Time', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text'],
            'callback' => [$this, 'get_post_time'],
            'controls' => [
                'format' => [
                    'type' => 'text',
                    'label' => __('Time Format', 'theme-builder-pro'),
                    'default' => get_option('time_format'),
                ],
            ],
        ]);

        $this->register_tag('post_id', [
            'label' => __('Post ID', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text', 'number'],
            'callback' => [$this, 'get_post_id'],
        ]);

        $this->register_tag('post_url', [
            'label' => __('Post URL', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['url'],
            'callback' => [$this, 'get_post_url'],
        ]);

        $this->register_tag('featured_image', [
            'label' => __('Featured Image', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['image'],
            'callback' => [$this, 'get_featured_image'],
            'controls' => [
                'size' => [
                    'type' => 'select',
                    'label' => __('Image Size', 'theme-builder-pro'),
                    'options' => [
                        'thumbnail' => __('Thumbnail', 'theme-builder-pro'),
                        'medium' => __('Medium', 'theme-builder-pro'),
                        'large' => __('Large', 'theme-builder-pro'),
                        'full' => __('Full', 'theme-builder-pro'),
                    ],
                    'default' => 'full',
                ],
            ],
        ]);

        $this->register_tag('post_terms', [
            'label' => __('Post Terms', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text'],
            'callback' => [$this, 'get_post_terms'],
            'controls' => [
                'taxonomy' => [
                    'type' => 'select',
                    'label' => __('Taxonomy', 'theme-builder-pro'),
                    'options' => $this->get_taxonomies_options(),
                    'default' => 'category',
                ],
                'separator' => [
                    'type' => 'text',
                    'label' => __('Separator', 'theme-builder-pro'),
                    'default' => ', ',
                ],
                'link' => [
                    'type' => 'switcher',
                    'label' => __('Link', 'theme-builder-pro'),
                    'default' => 'yes',
                ],
            ],
        ]);

        $this->register_tag('post_meta', [
            'label' => __('Post Meta', 'theme-builder-pro'),
            'group' => 'post',
            'categories' => ['text'],
            'callback' => [$this, 'get_post_meta'],
            'controls' => [
                'key' => [
                    'type' => 'text',
                    'label' => __('Meta Key', 'theme-builder-pro'),
                ],
            ],
        ]);

        // Author Tags
        $this->register_tag('author_name', [
            'label' => __('Author Name', 'theme-builder-pro'),
            'group' => 'author',
            'categories' => ['text'],
            'callback' => [$this, 'get_author_name'],
        ]);

        $this->register_tag('author_bio', [
            'label' => __('Author Bio', 'theme-builder-pro'),
            'group' => 'author',
            'categories' => ['text'],
            'callback' => [$this, 'get_author_bio'],
        ]);

        $this->register_tag('author_avatar', [
            'label' => __('Author Avatar', 'theme-builder-pro'),
            'group' => 'author',
            'categories' => ['image'],
            'callback' => [$this, 'get_author_avatar'],
            'controls' => [
                'size' => [
                    'type' => 'number',
                    'label' => __('Size', 'theme-builder-pro'),
                    'default' => 96,
                ],
            ],
        ]);

        $this->register_tag('author_url', [
            'label' => __('Author URL', 'theme-builder-pro'),
            'group' => 'author',
            'categories' => ['url'],
            'callback' => [$this, 'get_author_url'],
        ]);

        $this->register_tag('author_email', [
            'label' => __('Author Email', 'theme-builder-pro'),
            'group' => 'author',
            'categories' => ['text'],
            'callback' => [$this, 'get_author_email'],
        ]);

        // Site Tags
        $this->register_tag('site_title', [
            'label' => __('Site Title', 'theme-builder-pro'),
            'group' => 'site',
            'categories' => ['text'],
            'callback' => [$this, 'get_site_title'],
        ]);

        $this->register_tag('site_tagline', [
            'label' => __('Site Tagline', 'theme-builder-pro'),
            'group' => 'site',
            'categories' => ['text'],
            'callback' => [$this, 'get_site_tagline'],
        ]);

        $this->register_tag('site_url', [
            'label' => __('Site URL', 'theme-builder-pro'),
            'group' => 'site',
            'categories' => ['url'],
            'callback' => [$this, 'get_site_url'],
        ]);

        $this->register_tag('site_logo', [
            'label' => __('Site Logo', 'theme-builder-pro'),
            'group' => 'site',
            'categories' => ['image'],
            'callback' => [$this, 'get_site_logo'],
        ]);

        $this->register_tag('current_date', [
            'label' => __('Current Date', 'theme-builder-pro'),
            'group' => 'site',
            'categories' => ['text'],
            'callback' => [$this, 'get_current_date'],
            'controls' => [
                'format' => [
                    'type' => 'text',
                    'label' => __('Date Format', 'theme-builder-pro'),
                    'default' => get_option('date_format'),
                ],
            ],
        ]);

        $this->register_tag('current_year', [
            'label' => __('Current Year', 'theme-builder-pro'),
            'group' => 'site',
            'categories' => ['text'],
            'callback' => [$this, 'get_current_year'],
        ]);

        // Archive Tags
        $this->register_tag('archive_title', [
            'label' => __('Archive Title', 'theme-builder-pro'),
            'group' => 'archive',
            'categories' => ['text'],
            'callback' => [$this, 'get_archive_title'],
        ]);

        $this->register_tag('archive_description', [
            'label' => __('Archive Description', 'theme-builder-pro'),
            'group' => 'archive',
            'categories' => ['text'],
            'callback' => [$this, 'get_archive_description'],
        ]);

        // User Tags
        $this->register_tag('user_name', [
            'label' => __('User Display Name', 'theme-builder-pro'),
            'group' => 'user',
            'categories' => ['text'],
            'callback' => [$this, 'get_user_name'],
        ]);

        $this->register_tag('user_email', [
            'label' => __('User Email', 'theme-builder-pro'),
            'group' => 'user',
            'categories' => ['text'],
            'callback' => [$this, 'get_user_email'],
        ]);

        $this->register_tag('user_avatar', [
            'label' => __('User Avatar', 'theme-builder-pro'),
            'group' => 'user',
            'categories' => ['image'],
            'callback' => [$this, 'get_user_avatar'],
        ]);

        // Request Tags
        $this->register_tag('request_param', [
            'label' => __('Request Parameter', 'theme-builder-pro'),
            'group' => 'request',
            'categories' => ['text'],
            'callback' => [$this, 'get_request_param'],
            'controls' => [
                'param' => [
                    'type' => 'text',
                    'label' => __('Parameter Name', 'theme-builder-pro'),
                ],
                'type' => [
                    'type' => 'select',
                    'label' => __('Type', 'theme-builder-pro'),
                    'options' => [
                        'get' => 'GET',
                        'post' => 'POST',
                    ],
                    'default' => 'get',
                ],
            ],
        ]);

        $this->register_tag('shortcode', [
            'label' => __('Shortcode', 'theme-builder-pro'),
            'group' => 'site',
            'categories' => ['text'],
            'callback' => [$this, 'render_shortcode'],
            'controls' => [
                'shortcode' => [
                    'type' => 'text',
                    'label' => __('Shortcode', 'theme-builder-pro'),
                ],
            ],
        ]);

        // ACF Integration
        if (class_exists('ACF')) {
            $this->register_tag('acf_field', [
                'label' => __('ACF Field', 'theme-builder-pro'),
                'group' => 'acf',
                'categories' => ['text', 'image', 'url'],
                'callback' => [$this, 'get_acf_field'],
                'controls' => [
                    'field' => [
                        'type' => 'text',
                        'label' => __('Field Name', 'theme-builder-pro'),
                    ],
                ],
            ]);
        }

        // WooCommerce Integration
        if (class_exists('WooCommerce')) {
            $this->register_woocommerce_tags();
        }

        $this->tags = apply_filters('tbp/dynamic_tags', $this->tags);
    }

    /**
     * Register WooCommerce Tags
     */
    private function register_woocommerce_tags() {
        $this->register_tag('product_price', [
            'label' => __('Product Price', 'theme-builder-pro'),
            'group' => 'woocommerce',
            'categories' => ['text'],
            'callback' => [$this, 'get_product_price'],
        ]);

        $this->register_tag('product_rating', [
            'label' => __('Product Rating', 'theme-builder-pro'),
            'group' => 'woocommerce',
            'categories' => ['text', 'number'],
            'callback' => [$this, 'get_product_rating'],
        ]);

        $this->register_tag('product_stock', [
            'label' => __('Product Stock', 'theme-builder-pro'),
            'group' => 'woocommerce',
            'categories' => ['text'],
            'callback' => [$this, 'get_product_stock'],
        ]);

        $this->register_tag('product_sku', [
            'label' => __('Product SKU', 'theme-builder-pro'),
            'group' => 'woocommerce',
            'categories' => ['text'],
            'callback' => [$this, 'get_product_sku'],
        ]);

        $this->register_tag('product_gallery', [
            'label' => __('Product Gallery', 'theme-builder-pro'),
            'group' => 'woocommerce',
            'categories' => ['gallery'],
            'callback' => [$this, 'get_product_gallery'],
        ]);

        $this->register_tag('cart_total', [
            'label' => __('Cart Total', 'theme-builder-pro'),
            'group' => 'woocommerce',
            'categories' => ['text'],
            'callback' => [$this, 'get_cart_total'],
        ]);

        $this->register_tag('cart_count', [
            'label' => __('Cart Count', 'theme-builder-pro'),
            'group' => 'woocommerce',
            'categories' => ['text', 'number'],
            'callback' => [$this, 'get_cart_count'],
        ]);
    }

    /**
     * Register Tag
     */
    public function register_tag($name, $config) {
        $this->tags[$name] = $config;
    }

    /**
     * Get Tags
     */
    public function get_tags() {
        return $this->tags;
    }

    /**
     * Get Tags Config (for editor)
     */
    public function get_tags_config() {
        $config = [];
        $groups = $this->get_groups();

        foreach ($groups as $group_id => $group_label) {
            $config[$group_id] = [
                'label' => $group_label,
                'tags' => [],
            ];
        }

        foreach ($this->tags as $name => $tag) {
            $group = $tag['group'] ?? 'site';
            $config[$group]['tags'][$name] = [
                'label' => $tag['label'],
                'categories' => $tag['categories'] ?? ['text'],
                'controls' => $tag['controls'] ?? [],
            ];
        }

        return $config;
    }

    /**
     * Get Groups
     */
    public function get_groups() {
        $groups = [
            'post' => __('Post', 'theme-builder-pro'),
            'author' => __('Author', 'theme-builder-pro'),
            'site' => __('Site', 'theme-builder-pro'),
            'archive' => __('Archive', 'theme-builder-pro'),
            'user' => __('User', 'theme-builder-pro'),
            'request' => __('Request', 'theme-builder-pro'),
        ];

        if (class_exists('ACF')) {
            $groups['acf'] = __('ACF', 'theme-builder-pro');
        }

        if (class_exists('WooCommerce')) {
            $groups['woocommerce'] = __('WooCommerce', 'theme-builder-pro');
        }

        return apply_filters('tbp/dynamic_tags/groups', $groups);
    }

    /**
     * Render Tag
     */
    public function render_tag($tag_string, $post_id = null, $settings = []) {
        // Parse tag string (format: tag_name or tag_name:setting1=value1,setting2=value2)
        $parts = explode(':', $tag_string, 2);
        $tag_name = $parts[0];

        if (isset($parts[1])) {
            $settings_pairs = explode(',', $parts[1]);
            foreach ($settings_pairs as $pair) {
                $kv = explode('=', $pair, 2);
                if (count($kv) === 2) {
                    $settings[$kv[0]] = $kv[1];
                }
            }
        }

        if (!isset($this->tags[$tag_name])) {
            return '';
        }

        $tag = $this->tags[$tag_name];
        $callback = $tag['callback'] ?? null;

        if (!is_callable($callback)) {
            return '';
        }

        return call_user_func($callback, $post_id, $settings);
    }

    /**
     * Get Taxonomies Options
     */
    private function get_taxonomies_options() {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $options = [];

        foreach ($taxonomies as $taxonomy) {
            $options[$taxonomy->name] = $taxonomy->label;
        }

        return $options;
    }

    // Tag Callbacks

    public function get_post_title($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        return get_the_title($post_id);
    }

    public function get_post_excerpt($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        return get_the_excerpt($post_id);
    }

    public function get_post_content($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $post = get_post($post_id);
        return $post ? apply_filters('the_content', $post->post_content) : '';
    }

    public function get_post_date($post_id = null, $settings = []) {
        $post_id = $post_id ?: get_the_ID();
        $format = $settings['format'] ?? get_option('date_format');
        return get_the_date($format, $post_id);
    }

    public function get_post_time($post_id = null, $settings = []) {
        $post_id = $post_id ?: get_the_ID();
        $format = $settings['format'] ?? get_option('time_format');
        return get_the_time($format, $post_id);
    }

    public function get_post_id($post_id = null) {
        return $post_id ?: get_the_ID();
    }

    public function get_post_url($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        return get_permalink($post_id);
    }

    public function get_featured_image($post_id = null, $settings = []) {
        $post_id = $post_id ?: get_the_ID();
        $size = $settings['size'] ?? 'full';
        return get_the_post_thumbnail_url($post_id, $size);
    }

    public function get_post_terms($post_id = null, $settings = []) {
        $post_id = $post_id ?: get_the_ID();
        $taxonomy = $settings['taxonomy'] ?? 'category';
        $separator = $settings['separator'] ?? ', ';
        $link = $settings['link'] ?? 'yes';

        $terms = get_the_terms($post_id, $taxonomy);

        if (!$terms || is_wp_error($terms)) {
            return '';
        }

        $term_list = [];
        foreach ($terms as $term) {
            if ($link === 'yes') {
                $term_list[] = '<a href="' . get_term_link($term) . '">' . esc_html($term->name) . '</a>';
            } else {
                $term_list[] = esc_html($term->name);
            }
        }

        return implode($separator, $term_list);
    }

    public function get_post_meta($post_id = null, $settings = []) {
        $post_id = $post_id ?: get_the_ID();
        $key = $settings['key'] ?? '';

        if (!$key) {
            return '';
        }

        return get_post_meta($post_id, $key, true);
    }

    public function get_author_name($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        return get_the_author_meta('display_name', get_post_field('post_author', $post_id));
    }

    public function get_author_bio($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        return get_the_author_meta('description', get_post_field('post_author', $post_id));
    }

    public function get_author_avatar($post_id = null, $settings = []) {
        $post_id = $post_id ?: get_the_ID();
        $size = $settings['size'] ?? 96;
        return get_avatar_url(get_post_field('post_author', $post_id), ['size' => $size]);
    }

    public function get_author_url($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        return get_author_posts_url(get_post_field('post_author', $post_id));
    }

    public function get_author_email($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        return get_the_author_meta('email', get_post_field('post_author', $post_id));
    }

    public function get_site_title() {
        return get_bloginfo('name');
    }

    public function get_site_tagline() {
        return get_bloginfo('description');
    }

    public function get_site_url() {
        return home_url('/');
    }

    public function get_site_logo() {
        $logo_id = get_theme_mod('custom_logo');
        return $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    }

    public function get_current_date($post_id = null, $settings = []) {
        $format = $settings['format'] ?? get_option('date_format');
        return date_i18n($format);
    }

    public function get_current_year() {
        return date('Y');
    }

    public function get_archive_title() {
        return get_the_archive_title();
    }

    public function get_archive_description() {
        return get_the_archive_description();
    }

    public function get_user_name() {
        $user = wp_get_current_user();
        return $user->ID ? $user->display_name : '';
    }

    public function get_user_email() {
        $user = wp_get_current_user();
        return $user->ID ? $user->user_email : '';
    }

    public function get_user_avatar($post_id = null, $settings = []) {
        $user = wp_get_current_user();
        $size = $settings['size'] ?? 96;
        return $user->ID ? get_avatar_url($user->ID, ['size' => $size]) : '';
    }

    public function get_request_param($post_id = null, $settings = []) {
        $param = $settings['param'] ?? '';
        $type = $settings['type'] ?? 'get';

        if (!$param) {
            return '';
        }

        if ($type === 'post') {
            return isset($_POST[$param]) ? sanitize_text_field($_POST[$param]) : '';
        }

        return isset($_GET[$param]) ? sanitize_text_field($_GET[$param]) : '';
    }

    public function render_shortcode($post_id = null, $settings = []) {
        $shortcode = $settings['shortcode'] ?? '';
        return $shortcode ? do_shortcode($shortcode) : '';
    }

    public function get_acf_field($post_id = null, $settings = []) {
        if (!function_exists('get_field')) {
            return '';
        }

        $post_id = $post_id ?: get_the_ID();
        $field = $settings['field'] ?? '';

        if (!$field) {
            return '';
        }

        return get_field($field, $post_id);
    }

    // WooCommerce Callbacks
    public function get_product_price($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $product = wc_get_product($post_id);
        return $product ? $product->get_price_html() : '';
    }

    public function get_product_rating($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $product = wc_get_product($post_id);
        return $product ? $product->get_average_rating() : '';
    }

    public function get_product_stock($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $product = wc_get_product($post_id);
        return $product ? $product->get_stock_status() : '';
    }

    public function get_product_sku($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $product = wc_get_product($post_id);
        return $product ? $product->get_sku() : '';
    }

    public function get_product_gallery($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $product = wc_get_product($post_id);

        if (!$product) {
            return [];
        }

        $gallery_ids = $product->get_gallery_image_ids();
        $images = [];

        foreach ($gallery_ids as $id) {
            $images[] = [
                'id' => $id,
                'url' => wp_get_attachment_url($id),
            ];
        }

        return $images;
    }

    public function get_cart_total() {
        if (!WC()->cart) {
            return '';
        }
        return WC()->cart->get_cart_total();
    }

    public function get_cart_count() {
        if (!WC()->cart) {
            return 0;
        }
        return WC()->cart->get_cart_contents_count();
    }
}
