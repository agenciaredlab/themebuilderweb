<?php
/**
 * Popup Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Popup_Manager {

    /**
     * Active Popups
     */
    private $active_popups = [];

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_footer', [$this, 'render_popups']);
        add_action('wp_ajax_tbp_popup_submit', [$this, 'handle_form_submit']);
        add_action('wp_ajax_nopriv_tbp_popup_submit', [$this, 'handle_form_submit']);
    }

    /**
     * Get Active Popups
     */
    public function get_active_popups() {
        if (!empty($this->active_popups)) {
            return $this->active_popups;
        }

        $popups = get_posts([
            'post_type' => 'tbp_popup',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);

        foreach ($popups as $popup) {
            $conditions = TBP_Conditions::get_document_conditions($popup->ID);

            if (TBP_Conditions::check($conditions)) {
                $settings = TBP_Utils::get_post_settings($popup->ID);

                // Check timing/frequency conditions
                if ($this->check_display_rules($popup->ID, $settings)) {
                    $this->active_popups[] = $popup->ID;
                }
            }
        }

        return $this->active_popups;
    }

    /**
     * Check Display Rules
     */
    private function check_display_rules($popup_id, $settings) {
        // Check if popup was already shown in this session
        $frequency = $settings['frequency'] ?? 'always';

        if ($frequency !== 'always') {
            $cookie_name = 'tbp_popup_' . $popup_id;

            if (isset($_COOKIE[$cookie_name])) {
                $last_shown = intval($_COOKIE[$cookie_name]);

                switch ($frequency) {
                    case 'once':
                        return false;

                    case 'once_per_session':
                        return false;

                    case 'once_per_day':
                        if (time() - $last_shown < DAY_IN_SECONDS) {
                            return false;
                        }
                        break;

                    case 'once_per_week':
                        if (time() - $last_shown < WEEK_IN_SECONDS) {
                            return false;
                        }
                        break;
                }
            }
        }

        // Check date range
        $start_date = $settings['start_date'] ?? '';
        $end_date = $settings['end_date'] ?? '';

        if ($start_date && strtotime($start_date) > time()) {
            return false;
        }

        if ($end_date && strtotime($end_date) < time()) {
            return false;
        }

        return true;
    }

    /**
     * Render Popups
     */
    public function render_popups() {
        $popups = $this->get_active_popups();

        if (empty($popups)) {
            return;
        }

        foreach ($popups as $popup_id) {
            $this->render_popup($popup_id);
        }

        // Enqueue popup scripts
        $this->enqueue_popup_scripts();
    }

    /**
     * Render Single Popup
     */
    private function render_popup($popup_id) {
        $document = theme_builder_pro()->documents_manager->get($popup_id);

        if (!$document) {
            return;
        }

        $settings = TBP_Utils::get_post_settings($popup_id);
        $trigger = $settings['trigger'] ?? 'on_load';
        $trigger_settings = $this->get_trigger_settings($settings);
        $animation = $settings['animation'] ?? 'fadeIn';
        $animation_duration = $settings['animation_duration'] ?? 300;
        $position = $settings['position'] ?? 'center center';
        $overlay = $settings['overlay'] ?? 'yes';
        $close_button = $settings['close_button'] ?? 'yes';
        $prevent_scroll = $settings['prevent_scroll'] ?? 'yes';
        $close_on_overlay = $settings['close_on_overlay'] ?? 'yes';
        $close_on_esc = $settings['close_on_esc'] ?? 'yes';

        $popup_classes = [
            'tbp-popup',
            'tbp-popup-' . $popup_id,
            'tbp-popup-position-' . str_replace(' ', '-', $position),
            'tbp-popup-animation-' . $animation,
        ];

        if (!empty($settings['entrance_animation'])) {
            $popup_classes[] = 'tbp-popup-entrance-' . $settings['entrance_animation'];
        }

        $popup_data = [
            'id' => $popup_id,
            'trigger' => $trigger,
            'triggerSettings' => $trigger_settings,
            'animation' => $animation,
            'animationDuration' => $animation_duration,
            'closeOnOverlay' => $close_on_overlay === 'yes',
            'closeOnEsc' => $close_on_esc === 'yes',
            'preventScroll' => $prevent_scroll === 'yes',
        ];

        // CSS
        $css = $document->get_css();
        if ($css) {
            echo '<style id="tbp-popup-css-' . $popup_id . '">' . $css . '</style>';
        }

        // Popup HTML
        ?>
        <div class="<?php echo esc_attr(implode(' ', $popup_classes)); ?>"
             data-popup="<?php echo esc_attr(wp_json_encode($popup_data)); ?>"
             style="display: none;">

            <?php if ($overlay === 'yes'): ?>
                <div class="tbp-popup-overlay"
                     style="background-color: <?php echo esc_attr($settings['overlay_color'] ?? 'rgba(0,0,0,0.8)'); ?>;">
                </div>
            <?php endif; ?>

            <div class="tbp-popup-container"
                 style="max-width: <?php echo esc_attr($settings['width'] ?? '600'); ?>px;
                        max-height: <?php echo esc_attr($settings['height'] ?? '80vh'); ?>;">

                <?php if ($close_button === 'yes'): ?>
                    <button class="tbp-popup-close" aria-label="<?php esc_attr_e('Close', 'theme-builder-pro'); ?>">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                    </button>
                <?php endif; ?>

                <div class="tbp-popup-content">
                    <?php echo $document->render(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get Trigger Settings
     */
    private function get_trigger_settings($settings) {
        $trigger = $settings['trigger'] ?? 'on_load';
        $trigger_settings = [];

        switch ($trigger) {
            case 'on_load':
                $trigger_settings['delay'] = intval($settings['trigger_delay'] ?? 0) * 1000;
                break;

            case 'on_scroll':
                $trigger_settings['direction'] = $settings['scroll_direction'] ?? 'down';
                $trigger_settings['distance'] = intval($settings['scroll_distance'] ?? 50);
                break;

            case 'on_scroll_element':
                $trigger_settings['selector'] = $settings['scroll_element'] ?? '';
                break;

            case 'on_click':
                $trigger_settings['selector'] = $settings['click_selector'] ?? '';
                $trigger_settings['times'] = intval($settings['click_times'] ?? 1);
                break;

            case 'exit_intent':
                $trigger_settings['sensitivity'] = intval($settings['exit_sensitivity'] ?? 20);
                break;

            case 'inactivity':
                $trigger_settings['time'] = intval($settings['inactivity_time'] ?? 30) * 1000;
                break;

            case 'after_x_pages':
                $trigger_settings['pages'] = intval($settings['page_count'] ?? 2);
                break;
        }

        return $trigger_settings;
    }

    /**
     * Enqueue Popup Scripts
     */
    private function enqueue_popup_scripts() {
        wp_enqueue_script(
            'tbp-popup',
            TBP_ASSETS_URL . 'js/frontend/popup.js',
            ['jquery'],
            TBP_VERSION,
            true
        );

        wp_localize_script('tbp-popup', 'tbpPopup', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_popup'),
            'trackingEnabled' => true,
        ]);
    }

    /**
     * Handle Form Submit
     */
    public function handle_form_submit() {
        check_ajax_referer('tbp_popup', 'nonce');

        $popup_id = isset($_POST['popup_id']) ? intval($_POST['popup_id']) : 0;
        $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : [];

        if (!$popup_id) {
            wp_send_json_error(__('Invalid popup', 'theme-builder-pro'));
        }

        // Process form
        $result = theme_builder_pro()->form_manager->process_submission([
            'form_id' => $popup_id,
            'fields' => $form_data,
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Track conversion
        TBP_DB::track_popup_event($popup_id, 'conversion');

        $settings = TBP_Utils::get_post_settings($popup_id);
        $success_message = $settings['success_message'] ?? __('Thank you for your submission!', 'theme-builder-pro');
        $redirect_url = $settings['redirect_url'] ?? '';

        wp_send_json_success([
            'message' => $success_message,
            'redirect' => $redirect_url,
        ]);
    }

    /**
     * Create Popup
     */
    public function create_popup($title = '') {
        if (!$title) {
            $title = __('New Popup', 'theme-builder-pro');
        }

        $post_id = wp_insert_post([
            'post_type' => 'tbp_popup',
            'post_title' => $title,
            'post_status' => 'draft',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        update_post_meta($post_id, '_tbp_edit_mode', 'builder');
        update_post_meta($post_id, '_tbp_content', wp_json_encode(['elements' => []]));

        // Default settings
        $default_settings = [
            'trigger' => 'on_load',
            'trigger_delay' => 2,
            'position' => 'center center',
            'animation' => 'fadeIn',
            'animation_duration' => 300,
            'overlay' => 'yes',
            'overlay_color' => 'rgba(0,0,0,0.8)',
            'close_button' => 'yes',
            'close_on_overlay' => 'yes',
            'close_on_esc' => 'yes',
            'prevent_scroll' => 'yes',
            'width' => 600,
            'height' => '80vh',
            'frequency' => 'once_per_session',
        ];

        TBP_Utils::save_post_settings($post_id, $default_settings);

        // Default conditions (show on all pages)
        TBP_Conditions::save_document_conditions($post_id, [
            [
                'type' => 'entire_site',
                'include' => true,
            ],
        ]);

        return $post_id;
    }

    /**
     * Get Popup Analytics
     */
    public function get_analytics($popup_id, $date_from = '', $date_to = '') {
        $analytics = TBP_DB::get_popup_analytics($popup_id, $date_from, $date_to);

        if (!$analytics) {
            return [
                'views' => 0,
                'closes' => 0,
                'conversions' => 0,
                'conversion_rate' => 0,
            ];
        }

        $conversion_rate = $analytics->views > 0
            ? round(($analytics->conversions / $analytics->views) * 100, 2)
            : 0;

        return [
            'views' => intval($analytics->views),
            'closes' => intval($analytics->closes),
            'conversions' => intval($analytics->conversions),
            'conversion_rate' => $conversion_rate,
        ];
    }

    /**
     * Get Trigger Options
     */
    public function get_trigger_options() {
        return [
            'on_load' => __('On Page Load', 'theme-builder-pro'),
            'on_scroll' => __('On Scroll', 'theme-builder-pro'),
            'on_scroll_element' => __('Scroll To Element', 'theme-builder-pro'),
            'on_click' => __('On Click', 'theme-builder-pro'),
            'exit_intent' => __('Exit Intent', 'theme-builder-pro'),
            'inactivity' => __('After Inactivity', 'theme-builder-pro'),
            'after_x_pages' => __('After X Page Views', 'theme-builder-pro'),
        ];
    }

    /**
     * Get Position Options
     */
    public function get_position_options() {
        return [
            'center center' => __('Center', 'theme-builder-pro'),
            'top center' => __('Top', 'theme-builder-pro'),
            'top left' => __('Top Left', 'theme-builder-pro'),
            'top right' => __('Top Right', 'theme-builder-pro'),
            'bottom center' => __('Bottom', 'theme-builder-pro'),
            'bottom left' => __('Bottom Left', 'theme-builder-pro'),
            'bottom right' => __('Bottom Right', 'theme-builder-pro'),
            'left center' => __('Center Left', 'theme-builder-pro'),
            'right center' => __('Center Right', 'theme-builder-pro'),
        ];
    }

    /**
     * Get Animation Options
     */
    public function get_animation_options() {
        return [
            'fadeIn' => __('Fade In', 'theme-builder-pro'),
            'slideInUp' => __('Slide In Up', 'theme-builder-pro'),
            'slideInDown' => __('Slide In Down', 'theme-builder-pro'),
            'slideInLeft' => __('Slide In Left', 'theme-builder-pro'),
            'slideInRight' => __('Slide In Right', 'theme-builder-pro'),
            'zoomIn' => __('Zoom In', 'theme-builder-pro'),
            'bounceIn' => __('Bounce In', 'theme-builder-pro'),
            'flipInX' => __('Flip In X', 'theme-builder-pro'),
            'flipInY' => __('Flip In Y', 'theme-builder-pro'),
            'rotateIn' => __('Rotate In', 'theme-builder-pro'),
        ];
    }

    /**
     * Get Frequency Options
     */
    public function get_frequency_options() {
        return [
            'always' => __('Always', 'theme-builder-pro'),
            'once' => __('Once', 'theme-builder-pro'),
            'once_per_session' => __('Once Per Session', 'theme-builder-pro'),
            'once_per_day' => __('Once Per Day', 'theme-builder-pro'),
            'once_per_week' => __('Once Per Week', 'theme-builder-pro'),
        ];
    }
}
