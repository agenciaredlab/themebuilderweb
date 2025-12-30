<?php
/**
 * Admin Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_filter('plugin_action_links_' . plugin_basename(TBP_PLUGIN_FILE), [$this, 'add_plugin_links']);
        add_action('admin_bar_menu', [$this, 'add_admin_bar_menu'], 100);
        add_filter('post_row_actions', [$this, 'add_post_row_actions'], 10, 2);
        add_filter('page_row_actions', [$this, 'add_post_row_actions'], 10, 2);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
    }

    /**
     * Add Admin Menu
     */
    public function add_admin_menu() {
        // Main Menu
        add_menu_page(
            __('Theme Builder Pro', 'theme-builder-pro'),
            __('Theme Builder', 'theme-builder-pro'),
            'edit_posts',
            'theme-builder-pro',
            [$this, 'render_dashboard'],
            'dashicons-layout',
            58
        );

        // Dashboard
        add_submenu_page(
            'theme-builder-pro',
            __('Dashboard', 'theme-builder-pro'),
            __('Dashboard', 'theme-builder-pro'),
            'edit_posts',
            'theme-builder-pro',
            [$this, 'render_dashboard']
        );

        // Theme Builder
        add_submenu_page(
            'theme-builder-pro',
            __('Theme Builder', 'theme-builder-pro'),
            __('Theme Builder', 'theme-builder-pro'),
            'edit_theme_options',
            'tbp-theme-builder',
            [$this, 'render_theme_builder']
        );

        // Templates
        add_submenu_page(
            'theme-builder-pro',
            __('Templates', 'theme-builder-pro'),
            __('Templates', 'theme-builder-pro'),
            'edit_posts',
            'tbp-templates',
            [$this, 'render_templates']
        );

        // Popups
        add_submenu_page(
            'theme-builder-pro',
            __('Popups', 'theme-builder-pro'),
            __('Popups', 'theme-builder-pro'),
            'edit_posts',
            'tbp-popups',
            [$this, 'render_popups']
        );

        // Forms
        add_submenu_page(
            'theme-builder-pro',
            __('Forms', 'theme-builder-pro'),
            __('Forms', 'theme-builder-pro'),
            'edit_posts',
            'tbp-forms',
            [$this, 'render_forms']
        );

        // Submissions
        add_submenu_page(
            'theme-builder-pro',
            __('Submissions', 'theme-builder-pro'),
            __('Submissions', 'theme-builder-pro'),
            'edit_posts',
            'tbp-submissions',
            [$this, 'render_submissions']
        );

        // Global Styles
        add_submenu_page(
            'theme-builder-pro',
            __('Global Styles', 'theme-builder-pro'),
            __('Global Styles', 'theme-builder-pro'),
            'edit_theme_options',
            'tbp-global-styles',
            [$this, 'render_global_styles']
        );

        // Custom Fonts
        add_submenu_page(
            'theme-builder-pro',
            __('Custom Fonts', 'theme-builder-pro'),
            __('Custom Fonts', 'theme-builder-pro'),
            'edit_theme_options',
            'tbp-custom-fonts',
            [$this, 'render_custom_fonts']
        );

        // Settings
        add_submenu_page(
            'theme-builder-pro',
            __('Settings', 'theme-builder-pro'),
            __('Settings', 'theme-builder-pro'),
            'manage_options',
            'tbp-settings',
            [$this, 'render_settings']
        );

        // Tools
        add_submenu_page(
            'theme-builder-pro',
            __('Tools', 'theme-builder-pro'),
            __('Tools', 'theme-builder-pro'),
            'manage_options',
            'tbp-tools',
            [$this, 'render_tools']
        );
    }

    /**
     * Initialize Settings
     */
    public function init_settings() {
        register_setting('tbp_settings', 'tbp_google_fonts');
        register_setting('tbp_settings', 'tbp_enable_animations');
        register_setting('tbp_settings', 'tbp_enable_lazyload');
        register_setting('tbp_settings', 'tbp_container_width');
        register_setting('tbp_settings', 'tbp_breakpoints');
        register_setting('tbp_settings', 'tbp_global_colors');
        register_setting('tbp_settings', 'tbp_global_fonts');
    }

    /**
     * Enqueue Admin Scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Admin styles
        wp_enqueue_style(
            'tbp-admin',
            TBP_ASSETS_URL . 'css/admin.css',
            [],
            TBP_VERSION
        );

        // Only on our pages
        if (strpos($hook, 'tbp') !== false || strpos($hook, 'theme-builder') !== false) {
            wp_enqueue_style('wp-components');
            wp_enqueue_script('wp-api-fetch');

            wp_enqueue_script(
                'tbp-admin',
                TBP_ASSETS_URL . 'js/admin/admin.js',
                ['jquery', 'wp-element', 'wp-components', 'wp-api-fetch'],
                TBP_VERSION,
                true
            );

            wp_localize_script('tbp-admin', 'tbpAdmin', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'restUrl' => rest_url('tbp/v1/'),
                'nonce' => wp_create_nonce('tbp_admin'),
                'restNonce' => wp_create_nonce('wp_rest'),
                'adminUrl' => admin_url(),
                'pluginUrl' => TBP_PLUGIN_URL,
            ]);
        }
    }

    /**
     * Add Plugin Links
     */
    public function add_plugin_links($links) {
        $plugin_links = [
            '<a href="' . admin_url('admin.php?page=tbp-settings') . '">' . __('Settings', 'theme-builder-pro') . '</a>',
        ];

        return array_merge($plugin_links, $links);
    }

    /**
     * Add Admin Bar Menu
     */
    public function add_admin_bar_menu($wp_admin_bar) {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $wp_admin_bar->add_node([
            'id' => 'tbp-edit',
            'title' => '<span class="ab-icon dashicons dashicons-edit"></span>' . __('Edit with Theme Builder', 'theme-builder-pro'),
            'href' => '#',
            'meta' => [
                'class' => 'tbp-admin-bar-edit',
            ],
        ]);

        // Add sub items for singular pages
        if (is_singular() && !is_admin()) {
            $post_id = get_the_ID();

            if ($post_id) {
                $wp_admin_bar->add_node([
                    'id' => 'tbp-edit-page',
                    'parent' => 'tbp-edit',
                    'title' => __('Edit This Page', 'theme-builder-pro'),
                    'href' => $this->get_edit_url($post_id),
                ]);
            }
        }

        // Theme Builder links
        $wp_admin_bar->add_node([
            'id' => 'tbp-theme-builder',
            'parent' => 'tbp-edit',
            'title' => __('Theme Builder', 'theme-builder-pro'),
            'href' => admin_url('admin.php?page=tbp-theme-builder'),
        ]);

        $wp_admin_bar->add_node([
            'id' => 'tbp-popups',
            'parent' => 'tbp-edit',
            'title' => __('Popups', 'theme-builder-pro'),
            'href' => admin_url('admin.php?page=tbp-popups'),
        ]);
    }

    /**
     * Add Post Row Actions
     */
    public function add_post_row_actions($actions, $post) {
        if (!current_user_can('edit_post', $post->ID)) {
            return $actions;
        }

        $edit_url = $this->get_edit_url($post->ID);

        $actions['tbp_edit'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($edit_url),
            __('Edit with Theme Builder', 'theme-builder-pro')
        );

        return $actions;
    }

    /**
     * Get Edit URL
     */
    private function get_edit_url($post_id) {
        return add_query_arg([
            'post' => $post_id,
            'action' => 'tbp_editor',
        ], admin_url('post.php'));
    }

    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        $post_types = get_post_types(['public' => true]);

        foreach ($post_types as $post_type) {
            add_meta_box(
                'tbp_page_settings',
                __('Theme Builder Pro', 'theme-builder-pro'),
                [$this, 'render_page_settings_metabox'],
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Render Page Settings Metabox
     */
    public function render_page_settings_metabox($post) {
        $is_built_with_tbp = TBP_Utils::is_built_with_tbp($post->ID);
        $edit_url = $this->get_edit_url($post->ID);
        ?>
        <div class="tbp-metabox">
            <?php if ($is_built_with_tbp): ?>
                <p class="tbp-status tbp-status-active">
                    <span class="dashicons dashicons-yes"></span>
                    <?php esc_html_e('Built with Theme Builder Pro', 'theme-builder-pro'); ?>
                </p>
            <?php endif; ?>

            <a href="<?php echo esc_url($edit_url); ?>" class="button button-primary button-large tbp-edit-button">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e('Edit with Theme Builder Pro', 'theme-builder-pro'); ?>
            </a>

            <?php if ($is_built_with_tbp): ?>
                <p class="tbp-back-to-editor-note">
                    <a href="<?php echo get_edit_post_link($post->ID); ?>">
                        <?php esc_html_e('Back to WordPress Editor', 'theme-builder-pro'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render Dashboard
     */
    public function render_dashboard() {
        $recent_documents = theme_builder_pro()->documents_manager->get_recent(10);
        ?>
        <div class="wrap tbp-dashboard">
            <h1><?php esc_html_e('Theme Builder Pro', 'theme-builder-pro'); ?></h1>

            <div class="tbp-dashboard-header">
                <div class="tbp-dashboard-welcome">
                    <h2><?php esc_html_e('Welcome to Theme Builder Pro', 'theme-builder-pro'); ?></h2>
                    <p><?php esc_html_e('Create stunning websites with the most powerful visual builder.', 'theme-builder-pro'); ?></p>
                </div>
            </div>

            <div class="tbp-dashboard-grid">
                <!-- Quick Actions -->
                <div class="tbp-dashboard-card">
                    <h3><?php esc_html_e('Quick Actions', 'theme-builder-pro'); ?></h3>
                    <div class="tbp-quick-actions">
                        <a href="<?php echo admin_url('post-new.php?post_type=page&tbp_action=new'); ?>" class="button button-primary">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php esc_html_e('New Page', 'theme-builder-pro'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=tbp-theme-builder'); ?>" class="button">
                            <span class="dashicons dashicons-layout"></span>
                            <?php esc_html_e('Theme Builder', 'theme-builder-pro'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=tbp-popups'); ?>" class="button">
                            <span class="dashicons dashicons-welcome-widgets-menus"></span>
                            <?php esc_html_e('Create Popup', 'theme-builder-pro'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=tbp-templates'); ?>" class="button">
                            <span class="dashicons dashicons-portfolio"></span>
                            <?php esc_html_e('Templates', 'theme-builder-pro'); ?>
                        </a>
                    </div>
                </div>

                <!-- Recent Documents -->
                <div class="tbp-dashboard-card">
                    <h3><?php esc_html_e('Recent Documents', 'theme-builder-pro'); ?></h3>
                    <?php if (!empty($recent_documents)): ?>
                        <ul class="tbp-recent-documents">
                            <?php foreach ($recent_documents as $doc): ?>
                                <li>
                                    <a href="<?php echo esc_url($doc['edit_url']); ?>">
                                        <span class="tbp-doc-title"><?php echo esc_html($doc['title']); ?></span>
                                        <span class="tbp-doc-type"><?php echo esc_html($doc['type']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?php esc_html_e('No documents yet. Start by creating a new page!', 'theme-builder-pro'); ?></p>
                    <?php endif; ?>
                </div>

                <!-- System Info -->
                <div class="tbp-dashboard-card">
                    <h3><?php esc_html_e('System Info', 'theme-builder-pro'); ?></h3>
                    <table class="tbp-system-info">
                        <tr>
                            <td><?php esc_html_e('Version', 'theme-builder-pro'); ?></td>
                            <td><?php echo TBP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('WordPress', 'theme-builder-pro'); ?></td>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('PHP', 'theme-builder-pro'); ?></td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Memory Limit', 'theme-builder-pro'); ?></td>
                            <td><?php echo ini_get('memory_limit'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Theme Builder
     */
    public function render_theme_builder() {
        $locations = theme_builder_pro()->theme_builder->get_locations();
        ?>
        <div class="wrap tbp-theme-builder-page">
            <h1><?php esc_html_e('Theme Builder', 'theme-builder-pro'); ?></h1>
            <p><?php esc_html_e('Create custom templates for every part of your site.', 'theme-builder-pro'); ?></p>

            <div class="tbp-theme-locations">
                <?php foreach ($locations as $location_id => $location): ?>
                    <?php $documents = theme_builder_pro()->theme_builder->get_documents_for_location($location_id); ?>
                    <div class="tbp-location-card">
                        <div class="tbp-location-header">
                            <span class="dashicons <?php echo esc_attr($location['icon'] ?? 'dashicons-layout'); ?>"></span>
                            <h3><?php echo esc_html($location['label']); ?></h3>
                        </div>
                        <p class="tbp-location-description"><?php echo esc_html($location['description']); ?></p>

                        <?php if (!empty($documents)): ?>
                            <ul class="tbp-location-documents">
                                <?php foreach ($documents as $doc): ?>
                                    <li>
                                        <a href="<?php echo esc_url($this->get_edit_url($doc['id'])); ?>">
                                            <?php echo esc_html(get_the_title($doc['id'])); ?>
                                        </a>
                                        <span class="tbp-doc-conditions">
                                            <?php echo count($doc['conditions']); ?> <?php esc_html_e('conditions', 'theme-builder-pro'); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <a href="#" class="button tbp-add-template" data-location="<?php echo esc_attr($location_id); ?>">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php esc_html_e('Add New', 'theme-builder-pro'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render Templates
     */
    public function render_templates() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Templates', 'theme-builder-pro'); ?></h1>
            <div id="tbp-templates-app"></div>
        </div>
        <?php
    }

    /**
     * Render Popups
     */
    public function render_popups() {
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Popups', 'theme-builder-pro'); ?>
                <a href="#" class="page-title-action tbp-add-popup"><?php esc_html_e('Add New', 'theme-builder-pro'); ?></a>
            </h1>
            <div id="tbp-popups-app"></div>
        </div>
        <?php
    }

    /**
     * Render Forms
     */
    public function render_forms() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Forms', 'theme-builder-pro'); ?></h1>
            <p><?php esc_html_e('Manage forms created with the Form widget.', 'theme-builder-pro'); ?></p>
            <div id="tbp-forms-app"></div>
        </div>
        <?php
    }

    /**
     * Render Submissions
     */
    public function render_submissions() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Form Submissions', 'theme-builder-pro'); ?></h1>
            <div id="tbp-submissions-app"></div>
        </div>
        <?php
    }

    /**
     * Render Global Styles
     */
    public function render_global_styles() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Global Styles', 'theme-builder-pro'); ?></h1>
            <p><?php esc_html_e('Define global colors, fonts, and spacing used throughout your site.', 'theme-builder-pro'); ?></p>
            <div id="tbp-global-styles-app"></div>
        </div>
        <?php
    }

    /**
     * Render Custom Fonts
     */
    public function render_custom_fonts() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Custom Fonts', 'theme-builder-pro'); ?></h1>
            <p><?php esc_html_e('Upload and manage custom fonts for your site.', 'theme-builder-pro'); ?></p>
            <div id="tbp-custom-fonts-app"></div>
        </div>
        <?php
    }

    /**
     * Render Settings
     */
    public function render_settings() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Settings', 'theme-builder-pro'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('tbp_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Container Width', 'theme-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="tbp_container_width" value="<?php echo esc_attr(get_option('tbp_container_width', 1140)); ?>" class="small-text" /> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Google Fonts', 'theme-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="tbp_google_fonts" value="1" <?php checked(get_option('tbp_google_fonts', true)); ?> />
                                <?php esc_html_e('Enable Google Fonts', 'theme-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Animations', 'theme-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="tbp_enable_animations" value="1" <?php checked(get_option('tbp_enable_animations', true)); ?> />
                                <?php esc_html_e('Enable entrance animations', 'theme-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Lazy Loading', 'theme-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="tbp_enable_lazyload" value="1" <?php checked(get_option('tbp_enable_lazyload', true)); ?> />
                                <?php esc_html_e('Enable lazy loading for images', 'theme-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render Tools
     */
    public function render_tools() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Tools', 'theme-builder-pro'); ?></h1>

            <div class="tbp-tools-grid">
                <div class="tbp-tool-card">
                    <h3><?php esc_html_e('Regenerate CSS', 'theme-builder-pro'); ?></h3>
                    <p><?php esc_html_e('Regenerate all CSS files for Theme Builder Pro content.', 'theme-builder-pro'); ?></p>
                    <button class="button" id="tbp-regenerate-css"><?php esc_html_e('Regenerate CSS', 'theme-builder-pro'); ?></button>
                </div>

                <div class="tbp-tool-card">
                    <h3><?php esc_html_e('Clear Cache', 'theme-builder-pro'); ?></h3>
                    <p><?php esc_html_e('Clear all cached data.', 'theme-builder-pro'); ?></p>
                    <button class="button" id="tbp-clear-cache"><?php esc_html_e('Clear Cache', 'theme-builder-pro'); ?></button>
                </div>

                <div class="tbp-tool-card">
                    <h3><?php esc_html_e('Export Settings', 'theme-builder-pro'); ?></h3>
                    <p><?php esc_html_e('Export all plugin settings and global styles.', 'theme-builder-pro'); ?></p>
                    <button class="button" id="tbp-export-settings"><?php esc_html_e('Export', 'theme-builder-pro'); ?></button>
                </div>

                <div class="tbp-tool-card">
                    <h3><?php esc_html_e('Import Settings', 'theme-builder-pro'); ?></h3>
                    <p><?php esc_html_e('Import settings from a previously exported file.', 'theme-builder-pro'); ?></p>
                    <input type="file" id="tbp-import-file" accept=".json" />
                    <button class="button" id="tbp-import-settings"><?php esc_html_e('Import', 'theme-builder-pro'); ?></button>
                </div>

                <div class="tbp-tool-card">
                    <h3><?php esc_html_e('Replace URL', 'theme-builder-pro'); ?></h3>
                    <p><?php esc_html_e('Replace URLs in all Theme Builder Pro content.', 'theme-builder-pro'); ?></p>
                    <input type="url" id="tbp-old-url" placeholder="<?php esc_attr_e('Old URL', 'theme-builder-pro'); ?>" />
                    <input type="url" id="tbp-new-url" placeholder="<?php esc_attr_e('New URL', 'theme-builder-pro'); ?>" />
                    <button class="button" id="tbp-replace-url"><?php esc_html_e('Replace', 'theme-builder-pro'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize Admin
new TBP_Admin();
