<?php
/**
 * Responsive Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Responsive {

    /**
     * Device Constants
     */
    const DEVICE_DESKTOP = 'desktop';
    const DEVICE_LAPTOP = 'laptop';
    const DEVICE_TABLET = 'tablet';
    const DEVICE_TABLET_PORTRAIT = 'tablet_portrait';
    const DEVICE_MOBILE = 'mobile';
    const DEVICE_MOBILE_PORTRAIT = 'mobile_portrait';

    /**
     * Get Breakpoints
     */
    public static function get_breakpoints() {
        $saved_breakpoints = get_option('tbp_breakpoints', []);

        $defaults = [
            'widescreen' => [
                'label' => __('Widescreen', 'theme-builder-pro'),
                'value' => 2400,
                'default_value' => 2400,
                'direction' => 'min',
                'is_enabled' => false,
            ],
            'desktop' => [
                'label' => __('Desktop', 'theme-builder-pro'),
                'value' => 1200,
                'default_value' => 1200,
                'direction' => 'max',
                'is_enabled' => true,
            ],
            'laptop' => [
                'label' => __('Laptop', 'theme-builder-pro'),
                'value' => 1024,
                'default_value' => 1024,
                'direction' => 'max',
                'is_enabled' => false,
            ],
            'tablet' => [
                'label' => __('Tablet', 'theme-builder-pro'),
                'value' => 992,
                'default_value' => 992,
                'direction' => 'max',
                'is_enabled' => true,
            ],
            'tablet_portrait' => [
                'label' => __('Tablet Portrait', 'theme-builder-pro'),
                'value' => 768,
                'default_value' => 768,
                'direction' => 'max',
                'is_enabled' => false,
            ],
            'mobile' => [
                'label' => __('Mobile', 'theme-builder-pro'),
                'value' => 576,
                'default_value' => 576,
                'direction' => 'max',
                'is_enabled' => true,
            ],
            'mobile_portrait' => [
                'label' => __('Mobile Portrait', 'theme-builder-pro'),
                'value' => 375,
                'default_value' => 375,
                'direction' => 'max',
                'is_enabled' => false,
            ],
        ];

        // Merge saved breakpoints
        foreach ($saved_breakpoints as $key => $value) {
            if (isset($defaults[$key])) {
                if (is_array($value)) {
                    $defaults[$key] = array_merge($defaults[$key], $value);
                } else {
                    $defaults[$key]['value'] = $value;
                }
            }
        }

        return apply_filters('tbp/breakpoints', $defaults);
    }

    /**
     * Get Breakpoint
     */
    public static function get_breakpoint($device) {
        $breakpoints = self::get_breakpoints();

        if (isset($breakpoints[$device])) {
            return $breakpoints[$device]['value'];
        }

        return null;
    }

    /**
     * Get Active Breakpoints
     */
    public static function get_active_breakpoints() {
        $breakpoints = self::get_breakpoints();
        $active = [];

        foreach ($breakpoints as $key => $breakpoint) {
            if (!empty($breakpoint['is_enabled'])) {
                $active[$key] = $breakpoint;
            }
        }

        return $active;
    }

    /**
     * Update Breakpoint
     */
    public static function update_breakpoint($device, $value) {
        $saved_breakpoints = get_option('tbp_breakpoints', []);
        $saved_breakpoints[$device] = $value;
        update_option('tbp_breakpoints', $saved_breakpoints);
    }

    /**
     * Get Device Suffix
     */
    public static function get_device_suffix($device) {
        if ($device === self::DEVICE_DESKTOP) {
            return '';
        }

        return '_' . $device;
    }

    /**
     * Get Responsive Setting
     */
    public static function get_responsive_setting($settings, $key, $device = null) {
        if (!$device) {
            $device = self::get_current_device();
        }

        $suffix = self::get_device_suffix($device);

        // Try device-specific value
        if (isset($settings[$key . $suffix]) && $settings[$key . $suffix] !== '') {
            return $settings[$key . $suffix];
        }

        // Fallback to desktop
        if (isset($settings[$key]) && $settings[$key] !== '') {
            return $settings[$key];
        }

        return null;
    }

    /**
     * Get Current Device
     */
    public static function get_current_device() {
        if (!class_exists('Mobile_Detect')) {
            return self::DEVICE_DESKTOP;
        }

        $detect = new Mobile_Detect();

        if ($detect->isMobile()) {
            return self::DEVICE_MOBILE;
        }

        if ($detect->isTablet()) {
            return self::DEVICE_TABLET;
        }

        return self::DEVICE_DESKTOP;
    }

    /**
     * Get Media Query
     */
    public static function get_media_query($device) {
        $breakpoints = self::get_breakpoints();

        if (!isset($breakpoints[$device])) {
            return '';
        }

        $breakpoint = $breakpoints[$device];
        $direction = $breakpoint['direction'] ?? 'max';

        return "@media ({$direction}-width: {$breakpoint['value']}px)";
    }

    /**
     * Generate Responsive CSS
     */
    public static function generate_responsive_css($selector, $property, $values) {
        $css = '';
        $breakpoints = self::get_active_breakpoints();

        // Desktop (no media query)
        if (isset($values['desktop']) && $values['desktop'] !== '') {
            $css .= "{$selector} { {$property}: {$values['desktop']}; }";
        }

        // Other breakpoints
        foreach ($breakpoints as $device => $breakpoint) {
            if ($device === 'desktop') continue;

            $suffix = '_' . $device;
            if (isset($values[$device]) && $values[$device] !== '') {
                $media_query = self::get_media_query($device);
                $css .= "{$media_query} { {$selector} { {$property}: {$values[$device]}; } }";
            }
        }

        return $css;
    }

    /**
     * Get Preview Dimensions
     */
    public static function get_preview_dimensions($device) {
        $dimensions = [
            'widescreen' => ['width' => 2560, 'height' => 1440],
            'desktop' => ['width' => 1920, 'height' => 1080],
            'laptop' => ['width' => 1366, 'height' => 768],
            'tablet' => ['width' => 1024, 'height' => 768],
            'tablet_portrait' => ['width' => 768, 'height' => 1024],
            'mobile' => ['width' => 414, 'height' => 896],
            'mobile_portrait' => ['width' => 375, 'height' => 667],
        ];

        return isset($dimensions[$device]) ? $dimensions[$device] : $dimensions['desktop'];
    }

    /**
     * Has Responsive Value
     */
    public static function has_responsive_value($settings, $key) {
        $breakpoints = self::get_active_breakpoints();

        foreach ($breakpoints as $device => $breakpoint) {
            $suffix = self::get_device_suffix($device);
            if (isset($settings[$key . $suffix]) && $settings[$key . $suffix] !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get All Responsive Values
     */
    public static function get_all_responsive_values($settings, $key) {
        $values = [];
        $breakpoints = self::get_active_breakpoints();

        foreach ($breakpoints as $device => $breakpoint) {
            $suffix = self::get_device_suffix($device);
            $setting_key = $key . $suffix;

            if (isset($settings[$setting_key]) && $settings[$setting_key] !== '') {
                $values[$device] = $settings[$setting_key];
            }
        }

        return $values;
    }

    /**
     * Get Container Width
     */
    public static function get_container_width($device = 'desktop') {
        $widths = [
            'widescreen' => 1400,
            'desktop' => get_option('tbp_container_width', 1140),
            'laptop' => 960,
            'tablet' => 720,
            'tablet_portrait' => 540,
            'mobile' => '100%',
            'mobile_portrait' => '100%',
        ];

        return isset($widths[$device]) ? $widths[$device] : $widths['desktop'];
    }

    /**
     * Get Responsive Controls Config
     */
    public static function get_responsive_controls_config() {
        $active_breakpoints = self::get_active_breakpoints();
        $config = [];

        foreach ($active_breakpoints as $device => $breakpoint) {
            $config[$device] = [
                'label' => $breakpoint['label'],
                'value' => $breakpoint['value'],
                'icon' => self::get_device_icon($device),
            ];
        }

        return $config;
    }

    /**
     * Get Device Icon
     */
    public static function get_device_icon($device) {
        $icons = [
            'widescreen' => 'dashicons-desktop',
            'desktop' => 'dashicons-desktop',
            'laptop' => 'dashicons-laptop',
            'tablet' => 'dashicons-tablet',
            'tablet_portrait' => 'dashicons-tablet',
            'mobile' => 'dashicons-smartphone',
            'mobile_portrait' => 'dashicons-smartphone',
        ];

        return isset($icons[$device]) ? $icons[$device] : 'dashicons-desktop';
    }

    /**
     * Print Preview Styles
     */
    public static function print_preview_styles($device) {
        $dimensions = self::get_preview_dimensions($device);
        $width = $dimensions['width'];

        echo '<style id="tbp-preview-device-styles">';
        echo '.tbp-preview-wrapper { max-width: ' . $width . 'px; margin: 0 auto; }';
        echo '</style>';
    }
}
