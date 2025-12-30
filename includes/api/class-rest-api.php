<?php
/**
 * REST API
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_REST_API {

    /**
     * Namespace
     */
    const NAMESPACE = 'tbp/v1';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register Routes
     */
    public function register_routes() {
        // Documents
        register_rest_route(self::NAMESPACE, '/documents', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_documents'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/documents/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_document'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'save_document'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete_document'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/documents/(?P<id>\d+)/autosave', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'autosave_document'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/documents/(?P<id>\d+)/duplicate', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'duplicate_document'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Templates
        register_rest_route(self::NAMESPACE, '/templates', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_templates'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_template'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/templates/import', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'import_template'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/templates/(?P<id>\d+)/export', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'export_template'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Theme Builder
        register_rest_route(self::NAMESPACE, '/theme-builder', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_theme_locations'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/theme-builder/conditions', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_conditions'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/theme-builder/sub-conditions/(?P<type>[a-zA-Z0-9_-]+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_sub_conditions'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Global Styles
        register_rest_route(self::NAMESPACE, '/global-styles', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_global_styles'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'save_global_styles'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Dynamic Tags
        register_rest_route(self::NAMESPACE, '/dynamic-tags', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_dynamic_tags'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/dynamic-tags/render', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'render_dynamic_tag'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Widgets
        register_rest_route(self::NAMESPACE, '/widgets', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_widgets'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // History
        register_rest_route(self::NAMESPACE, '/history/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_history'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'clear_history'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/history/(?P<id>\d+)/restore/(?P<state_id>\d+)', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'restore_history_state'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Forms
        register_rest_route(self::NAMESPACE, '/forms/submit', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'submit_form'],
                'permission_callback' => '__return_true',
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/forms/(?P<id>\d+)/submissions', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_form_submissions'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Popups
        register_rest_route(self::NAMESPACE, '/popups/track', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'track_popup_event'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // Media
        register_rest_route(self::NAMESPACE, '/media', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_media'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Posts (for dynamic content)
        register_rest_route(self::NAMESPACE, '/posts', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_posts_list'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);

        // Settings
        register_rest_route(self::NAMESPACE, '/settings', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_settings'],
                'permission_callback' => [$this, 'admin_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'save_settings'],
                'permission_callback' => [$this, 'admin_permission'],
            ],
        ]);

        // Render
        register_rest_route(self::NAMESPACE, '/render', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'render_element'],
                'permission_callback' => [$this, 'edit_permission'],
            ],
        ]);
    }

    /**
     * Permission Callbacks
     */
    public function edit_permission() {
        return current_user_can('edit_posts');
    }

    public function admin_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Get Documents
     */
    public function get_documents($request) {
        $documents = theme_builder_pro()->documents_manager->get_recent(50);
        return rest_ensure_response($documents);
    }

    /**
     * Get Document
     */
    public function get_document($request) {
        $id = $request->get_param('id');
        $document = theme_builder_pro()->documents_manager->get($id);

        if (!$document) {
            return new WP_Error('not_found', __('Document not found', 'theme-builder-pro'), ['status' => 404]);
        }

        return rest_ensure_response([
            'id' => $document->get_id(),
            'title' => $document->get_title(),
            'type' => $document->get_type(),
            'content' => $document->get_content(),
            'settings' => $document->get_settings(),
            'css' => $document->get_css(),
        ]);
    }

    /**
     * Save Document
     */
    public function save_document($request) {
        $id = $request->get_param('id');
        $data = $request->get_json_params();

        $result = theme_builder_pro()->documents_manager->save($id, $data);

        if (!$result) {
            return new WP_Error('save_failed', __('Failed to save document', 'theme-builder-pro'), ['status' => 500]);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => __('Document saved successfully', 'theme-builder-pro'),
        ]);
    }

    /**
     * Autosave Document
     */
    public function autosave_document($request) {
        $id = $request->get_param('id');
        $data = $request->get_json_params();

        $result = theme_builder_pro()->documents_manager->autosave($id, $data);

        return rest_ensure_response([
            'success' => $result,
        ]);
    }

    /**
     * Delete Document
     */
    public function delete_document($request) {
        $id = $request->get_param('id');
        $result = theme_builder_pro()->documents_manager->delete($id);

        return rest_ensure_response([
            'success' => (bool) $result,
        ]);
    }

    /**
     * Duplicate Document
     */
    public function duplicate_document($request) {
        $id = $request->get_param('id');
        $new_id = theme_builder_pro()->documents_manager->duplicate($id);

        if (is_wp_error($new_id)) {
            return $new_id;
        }

        return rest_ensure_response([
            'success' => true,
            'id' => $new_id,
        ]);
    }

    /**
     * Get Templates
     */
    public function get_templates($request) {
        $type = $request->get_param('type') ?: 'blocks';
        $category = $request->get_param('category') ?: '';

        // Get from library module
        $templates = [];

        return rest_ensure_response($templates);
    }

    /**
     * Create Template
     */
    public function create_template($request) {
        $data = $request->get_json_params();

        $post_id = theme_builder_pro()->documents_manager->create([
            'post_title' => $data['title'] ?? __('New Template', 'theme-builder-pro'),
            'document_type' => $data['type'] ?? 'page',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        if (isset($data['content'])) {
            TBP_Utils::save_post_content($post_id, $data['content']);
        }

        return rest_ensure_response([
            'success' => true,
            'id' => $post_id,
        ]);
    }

    /**
     * Import Template
     */
    public function import_template($request) {
        $data = $request->get_json_params();

        $post_id = theme_builder_pro()->documents_manager->import($data);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        return rest_ensure_response([
            'success' => true,
            'id' => $post_id,
        ]);
    }

    /**
     * Export Template
     */
    public function export_template($request) {
        $id = $request->get_param('id');
        $data = theme_builder_pro()->documents_manager->export($id);

        if (!$data) {
            return new WP_Error('not_found', __('Template not found', 'theme-builder-pro'), ['status' => 404]);
        }

        return rest_ensure_response($data);
    }

    /**
     * Get Theme Locations
     */
    public function get_theme_locations($request) {
        return rest_ensure_response(theme_builder_pro()->theme_builder->get_locations());
    }

    /**
     * Get Conditions
     */
    public function get_conditions($request) {
        return rest_ensure_response(TBP_Conditions::get_conditions_config());
    }

    /**
     * Get Sub Conditions
     */
    public function get_sub_conditions($request) {
        $type = $request->get_param('type');
        return rest_ensure_response(TBP_Conditions::get_sub_conditions($type));
    }

    /**
     * Get Global Styles
     */
    public function get_global_styles($request) {
        return rest_ensure_response(TBP_Schemes_Manager::export_schemes());
    }

    /**
     * Save Global Styles
     */
    public function save_global_styles($request) {
        $data = $request->get_json_params();
        TBP_Schemes_Manager::import_schemes($data);

        return rest_ensure_response([
            'success' => true,
        ]);
    }

    /**
     * Get Dynamic Tags
     */
    public function get_dynamic_tags($request) {
        return rest_ensure_response(theme_builder_pro()->dynamic_tags->get_tags_config());
    }

    /**
     * Render Dynamic Tag
     */
    public function render_dynamic_tag($request) {
        $data = $request->get_json_params();
        $tag = $data['tag'] ?? '';
        $settings = $data['settings'] ?? [];
        $post_id = $data['post_id'] ?? 0;

        $result = theme_builder_pro()->dynamic_tags->render_tag($tag, $post_id, $settings);

        return rest_ensure_response([
            'value' => $result,
        ]);
    }

    /**
     * Get Widgets
     */
    public function get_widgets($request) {
        return rest_ensure_response([
            'widgets' => theme_builder_pro()->widgets_manager->get_widget_types(),
            'categories' => theme_builder_pro()->widgets_manager->get_categories(),
        ]);
    }

    /**
     * Get History
     */
    public function get_history($request) {
        $id = $request->get_param('id');
        $states = TBP_DB::get_history_states($id);

        return rest_ensure_response($states);
    }

    /**
     * Clear History
     */
    public function clear_history($request) {
        $id = $request->get_param('id');
        TBP_DB::clear_history($id);

        return rest_ensure_response([
            'success' => true,
        ]);
    }

    /**
     * Restore History State
     */
    public function restore_history_state($request) {
        $id = $request->get_param('id');
        $state_id = $request->get_param('state_id');

        $states = TBP_DB::get_history_states($id);
        $state_data = null;

        foreach ($states as $state) {
            if ($state->id == $state_id) {
                $state_data = $state->data;
                break;
            }
        }

        if (!$state_data) {
            return new WP_Error('not_found', __('State not found', 'theme-builder-pro'), ['status' => 404]);
        }

        TBP_Utils::save_post_content($id, $state_data);

        return rest_ensure_response([
            'success' => true,
            'content' => $state_data,
        ]);
    }

    /**
     * Submit Form
     */
    public function submit_form($request) {
        $data = $request->get_json_params();

        $result = theme_builder_pro()->form_manager->process_submission($data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'success' => true,
            'message' => $result['message'] ?? __('Form submitted successfully', 'theme-builder-pro'),
        ]);
    }

    /**
     * Get Form Submissions
     */
    public function get_form_submissions($request) {
        $form_id = $request->get_param('id');
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;

        $submissions = TBP_DB::get_form_submissions($form_id, [
            'page' => $page,
            'per_page' => $per_page,
        ]);

        $total = TBP_DB::count_form_submissions($form_id);

        return rest_ensure_response([
            'items' => $submissions,
            'total' => $total,
            'pages' => ceil($total / $per_page),
        ]);
    }

    /**
     * Track Popup Event
     */
    public function track_popup_event($request) {
        $data = $request->get_json_params();

        TBP_DB::track_popup_event(
            $data['popup_id'] ?? 0,
            $data['event_type'] ?? 'view'
        );

        return rest_ensure_response([
            'success' => true,
        ]);
    }

    /**
     * Get Media
     */
    public function get_media($request) {
        $search = $request->get_param('search') ?: '';
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 40;

        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_mime_type' => 'image',
        ];

        if ($search) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        $items = [];

        foreach ($query->posts as $attachment) {
            $items[] = [
                'id' => $attachment->ID,
                'url' => wp_get_attachment_url($attachment->ID),
                'thumbnail' => wp_get_attachment_image_url($attachment->ID, 'thumbnail'),
                'title' => $attachment->post_title,
                'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            ];
        }

        return rest_ensure_response([
            'items' => $items,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ]);
    }

    /**
     * Get Posts List
     */
    public function get_posts_list($request) {
        $post_type = $request->get_param('post_type') ?: 'post';
        $search = $request->get_param('search') ?: '';

        $args = [
            'post_type' => $post_type,
            'posts_per_page' => 50,
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        if ($search) {
            $args['s'] = $search;
        }

        $posts = get_posts($args);
        $items = [];

        foreach ($posts as $post) {
            $items[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
            ];
        }

        return rest_ensure_response($items);
    }

    /**
     * Get Settings
     */
    public function get_settings($request) {
        return rest_ensure_response([
            'global_colors' => get_option('tbp_global_colors', []),
            'global_fonts' => get_option('tbp_global_fonts', []),
            'breakpoints' => get_option('tbp_breakpoints', []),
            'container_width' => get_option('tbp_container_width', 1140),
            'enable_animations' => get_option('tbp_enable_animations', true),
            'enable_lazyload' => get_option('tbp_enable_lazyload', true),
            'google_fonts' => get_option('tbp_google_fonts', true),
        ]);
    }

    /**
     * Save Settings
     */
    public function save_settings($request) {
        $data = $request->get_json_params();

        $settings_keys = [
            'global_colors',
            'global_fonts',
            'breakpoints',
            'container_width',
            'enable_animations',
            'enable_lazyload',
            'google_fonts',
        ];

        foreach ($settings_keys as $key) {
            if (isset($data[$key])) {
                update_option('tbp_' . $key, $data[$key]);
            }
        }

        return rest_ensure_response([
            'success' => true,
        ]);
    }

    /**
     * Render Element
     */
    public function render_element($request) {
        $data = $request->get_json_params();
        $element = $data['element'] ?? [];
        $post_id = $data['post_id'] ?? 0;

        if (empty($element)) {
            return new WP_Error('invalid_element', __('Invalid element data', 'theme-builder-pro'));
        }

        $document = new TBP_Document($post_id);
        $html = $document->render_element($element);

        return rest_ensure_response([
            'html' => $html,
        ]);
    }
}
