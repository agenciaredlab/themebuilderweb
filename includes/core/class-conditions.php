<?php
/**
 * Conditions Manager - Display Conditions for Theme Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Conditions {

    private static $conditions = null;

    /**
     * Get All Conditions
     */
    public static function get_conditions() {
        if (self::$conditions === null) {
            self::$conditions = self::init_conditions();
        }

        return self::$conditions;
    }

    /**
     * Initialize Conditions
     */
    private static function init_conditions() {
        $conditions = [
            'general' => [
                'label' => __('General', 'theme-builder-pro'),
                'conditions' => [
                    'entire_site' => [
                        'label' => __('Entire Site', 'theme-builder-pro'),
                        'callback' => '__return_true',
                    ],
                ],
            ],
            'singular' => [
                'label' => __('Singular', 'theme-builder-pro'),
                'conditions' => [
                    'singular' => [
                        'label' => __('All Singular', 'theme-builder-pro'),
                        'callback' => 'is_singular',
                    ],
                    'front_page' => [
                        'label' => __('Front Page', 'theme-builder-pro'),
                        'callback' => 'is_front_page',
                    ],
                    'post' => [
                        'label' => __('Single Post', 'theme-builder-pro'),
                        'callback' => [__CLASS__, 'is_single_post'],
                        'sub_conditions' => 'posts',
                    ],
                    'page' => [
                        'label' => __('Single Page', 'theme-builder-pro'),
                        'callback' => 'is_page',
                        'sub_conditions' => 'pages',
                    ],
                    'attachment' => [
                        'label' => __('Attachment', 'theme-builder-pro'),
                        'callback' => 'is_attachment',
                    ],
                    'error_404' => [
                        'label' => __('404 Page', 'theme-builder-pro'),
                        'callback' => 'is_404',
                    ],
                ],
            ],
            'archive' => [
                'label' => __('Archive', 'theme-builder-pro'),
                'conditions' => [
                    'archive' => [
                        'label' => __('All Archives', 'theme-builder-pro'),
                        'callback' => 'is_archive',
                    ],
                    'author' => [
                        'label' => __('Author Archive', 'theme-builder-pro'),
                        'callback' => 'is_author',
                        'sub_conditions' => 'authors',
                    ],
                    'date' => [
                        'label' => __('Date Archive', 'theme-builder-pro'),
                        'callback' => 'is_date',
                    ],
                    'search' => [
                        'label' => __('Search Results', 'theme-builder-pro'),
                        'callback' => 'is_search',
                    ],
                    'category' => [
                        'label' => __('Category', 'theme-builder-pro'),
                        'callback' => 'is_category',
                        'sub_conditions' => 'categories',
                    ],
                    'tag' => [
                        'label' => __('Tag', 'theme-builder-pro'),
                        'callback' => 'is_tag',
                        'sub_conditions' => 'tags',
                    ],
                ],
            ],
        ];

        // Add custom post types
        $post_types = get_post_types(['public' => true, '_builtin' => false], 'objects');
        foreach ($post_types as $post_type) {
            $conditions['singular']['conditions'][$post_type->name] = [
                'label' => sprintf(__('Single %s', 'theme-builder-pro'), $post_type->labels->singular_name),
                'callback' => [__CLASS__, 'is_singular_post_type'],
                'callback_args' => [$post_type->name],
                'sub_conditions' => 'cpt_' . $post_type->name,
            ];

            if ($post_type->has_archive) {
                $conditions['archive']['conditions'][$post_type->name . '_archive'] = [
                    'label' => sprintf(__('%s Archive', 'theme-builder-pro'), $post_type->labels->name),
                    'callback' => 'is_post_type_archive',
                    'callback_args' => [$post_type->name],
                ];
            }

            // Add taxonomies
            $taxonomies = get_object_taxonomies($post_type->name, 'objects');
            foreach ($taxonomies as $taxonomy) {
                if (!$taxonomy->public) continue;

                $conditions['archive']['conditions'][$taxonomy->name] = [
                    'label' => $taxonomy->labels->singular_name,
                    'callback' => 'is_tax',
                    'callback_args' => [$taxonomy->name],
                    'sub_conditions' => 'tax_' . $taxonomy->name,
                ];
            }
        }

        // WooCommerce conditions
        if (class_exists('WooCommerce')) {
            $conditions['woocommerce'] = [
                'label' => __('WooCommerce', 'theme-builder-pro'),
                'conditions' => [
                    'woo_shop' => [
                        'label' => __('Shop Page', 'theme-builder-pro'),
                        'callback' => 'is_shop',
                    ],
                    'woo_product' => [
                        'label' => __('Single Product', 'theme-builder-pro'),
                        'callback' => 'is_product',
                        'sub_conditions' => 'products',
                    ],
                    'woo_product_category' => [
                        'label' => __('Product Category', 'theme-builder-pro'),
                        'callback' => 'is_product_category',
                        'sub_conditions' => 'product_categories',
                    ],
                    'woo_product_tag' => [
                        'label' => __('Product Tag', 'theme-builder-pro'),
                        'callback' => 'is_product_tag',
                        'sub_conditions' => 'product_tags',
                    ],
                    'woo_cart' => [
                        'label' => __('Cart', 'theme-builder-pro'),
                        'callback' => 'is_cart',
                    ],
                    'woo_checkout' => [
                        'label' => __('Checkout', 'theme-builder-pro'),
                        'callback' => 'is_checkout',
                    ],
                    'woo_account' => [
                        'label' => __('My Account', 'theme-builder-pro'),
                        'callback' => 'is_account_page',
                    ],
                    'woo_thankyou' => [
                        'label' => __('Thank You', 'theme-builder-pro'),
                        'callback' => [__CLASS__, 'is_woo_thankyou'],
                    ],
                ],
            ];
        }

        // User conditions
        $conditions['user'] = [
            'label' => __('User', 'theme-builder-pro'),
            'conditions' => [
                'logged_in' => [
                    'label' => __('Logged In', 'theme-builder-pro'),
                    'callback' => 'is_user_logged_in',
                ],
                'logged_out' => [
                    'label' => __('Logged Out', 'theme-builder-pro'),
                    'callback' => [__CLASS__, 'is_logged_out'],
                ],
                'user_role' => [
                    'label' => __('User Role', 'theme-builder-pro'),
                    'callback' => [__CLASS__, 'check_user_role'],
                    'sub_conditions' => 'roles',
                ],
            ],
        ];

        return apply_filters('tbp/conditions', $conditions);
    }

    /**
     * Check Conditions
     */
    public static function check($conditions, $context = []) {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? '';
            $value = $condition['value'] ?? '';
            $include = $condition['include'] ?? true;

            $result = self::evaluate_condition($type, $value);

            if ($include && $result) {
                return true;
            }

            if (!$include && !$result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate Condition
     */
    private static function evaluate_condition($type, $value) {
        $conditions = self::get_conditions();

        foreach ($conditions as $group => $data) {
            if (isset($data['conditions'][$type])) {
                $condition = $data['conditions'][$type];
                $callback = $condition['callback'];
                $callback_args = $condition['callback_args'] ?? [];

                if (!empty($value)) {
                    $callback_args[] = $value;
                }

                if (is_callable($callback)) {
                    return call_user_func_array($callback, $callback_args);
                }
            }
        }

        return false;
    }

    /**
     * Get Sub Conditions
     */
    public static function get_sub_conditions($type) {
        switch ($type) {
            case 'posts':
                return self::get_posts_list();
            case 'pages':
                return self::get_pages_list();
            case 'categories':
                return self::get_terms_list('category');
            case 'tags':
                return self::get_terms_list('post_tag');
            case 'authors':
                return self::get_authors_list();
            case 'roles':
                return self::get_roles_list();
            case 'products':
                return self::get_posts_list('product');
            case 'product_categories':
                return self::get_terms_list('product_cat');
            case 'product_tags':
                return self::get_terms_list('product_tag');
            default:
                if (strpos($type, 'cpt_') === 0) {
                    $post_type = str_replace('cpt_', '', $type);
                    return self::get_posts_list($post_type);
                }
                if (strpos($type, 'tax_') === 0) {
                    $taxonomy = str_replace('tax_', '', $type);
                    return self::get_terms_list($taxonomy);
                }
                return [];
        }
    }

    /**
     * Get Posts List
     */
    private static function get_posts_list($post_type = 'post') {
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => 100,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $list = [];
        foreach ($posts as $post) {
            $list[$post->ID] = $post->post_title;
        }

        return $list;
    }

    /**
     * Get Pages List
     */
    private static function get_pages_list() {
        $pages = get_pages(['sort_column' => 'post_title']);

        $list = [];
        foreach ($pages as $page) {
            $list[$page->ID] = $page->post_title;
        }

        return $list;
    }

    /**
     * Get Terms List
     */
    private static function get_terms_list($taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        $list = [];
        foreach ($terms as $term) {
            $list[$term->term_id] = $term->name;
        }

        return $list;
    }

    /**
     * Get Authors List
     */
    private static function get_authors_list() {
        $users = get_users(['who' => 'authors']);

        $list = [];
        foreach ($users as $user) {
            $list[$user->ID] = $user->display_name;
        }

        return $list;
    }

    /**
     * Get Roles List
     */
    private static function get_roles_list() {
        global $wp_roles;

        $list = [];
        foreach ($wp_roles->roles as $role => $data) {
            $list[$role] = $data['name'];
        }

        return $list;
    }

    /**
     * Callback: Is Single Post
     */
    public static function is_single_post($post_id = '') {
        if ($post_id) {
            return is_single($post_id);
        }
        return is_single();
    }

    /**
     * Callback: Is Singular Post Type
     */
    public static function is_singular_post_type($post_type, $post_id = '') {
        if ($post_id) {
            return is_singular($post_type) && get_the_ID() == $post_id;
        }
        return is_singular($post_type);
    }

    /**
     * Callback: Is Logged Out
     */
    public static function is_logged_out() {
        return !is_user_logged_in();
    }

    /**
     * Callback: Check User Role
     */
    public static function check_user_role($role) {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();
        return in_array($role, (array) $user->roles);
    }

    /**
     * Callback: Is WooCommerce Thank You Page
     */
    public static function is_woo_thankyou() {
        if (!function_exists('is_wc_endpoint_url')) {
            return false;
        }
        return is_wc_endpoint_url('order-received');
    }

    /**
     * Get Conditions Config for Editor
     */
    public static function get_conditions_config() {
        $conditions = self::get_conditions();
        $config = [];

        foreach ($conditions as $group => $data) {
            $group_conditions = [];

            foreach ($data['conditions'] as $key => $condition) {
                $group_conditions[$key] = [
                    'label' => $condition['label'],
                    'sub_conditions' => isset($condition['sub_conditions']) ? $condition['sub_conditions'] : false,
                ];
            }

            $config[$group] = [
                'label' => $data['label'],
                'conditions' => $group_conditions,
            ];
        }

        return $config;
    }

    /**
     * Get Document Conditions
     */
    public static function get_document_conditions($post_id) {
        return get_post_meta($post_id, '_tbp_conditions', true) ?: [];
    }

    /**
     * Save Document Conditions
     */
    public static function save_document_conditions($post_id, $conditions) {
        return update_post_meta($post_id, '_tbp_conditions', $conditions);
    }

    /**
     * Find Matching Documents
     */
    public static function find_matching_documents($document_type = '') {
        $args = [
            'post_type' => 'tbp_theme_doc',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];

        if ($document_type) {
            $args['meta_query'] = [
                [
                    'key' => '_tbp_document_type',
                    'value' => $document_type,
                ],
            ];
        }

        $documents = get_posts($args);
        $matching = [];

        foreach ($documents as $document) {
            $conditions = self::get_document_conditions($document->ID);

            if (self::check($conditions)) {
                $priority = get_post_meta($document->ID, '_tbp_priority', true) ?: 0;
                $matching[] = [
                    'id' => $document->ID,
                    'priority' => $priority,
                ];
            }
        }

        // Sort by priority (higher first)
        usort($matching, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return $matching;
    }

    /**
     * Get Active Document
     */
    public static function get_active_document($document_type) {
        $matching = self::find_matching_documents($document_type);

        if (!empty($matching)) {
            return $matching[0]['id'];
        }

        return null;
    }
}
