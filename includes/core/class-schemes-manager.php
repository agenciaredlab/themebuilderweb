<?php
/**
 * Schemes Manager - Global Styles
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Schemes_Manager {

    private static $schemes = null;

    /**
     * Scheme Types
     */
    const COLOR = 'color';
    const TYPOGRAPHY = 'typography';

    /**
     * Get All Schemes
     */
    public static function get_schemes() {
        if (self::$schemes === null) {
            self::$schemes = [
                self::COLOR => self::get_color_scheme(),
                self::TYPOGRAPHY => self::get_typography_scheme(),
            ];
        }

        return self::$schemes;
    }

    /**
     * Get Color Scheme
     */
    public static function get_color_scheme() {
        $saved_colors = get_option('tbp_global_colors', []);

        $defaults = [
            'primary' => [
                'title' => __('Primary', 'theme-builder-pro'),
                'value' => '#0073aa',
            ],
            'secondary' => [
                'title' => __('Secondary', 'theme-builder-pro'),
                'value' => '#23282d',
            ],
            'text' => [
                'title' => __('Text', 'theme-builder-pro'),
                'value' => '#333333',
            ],
            'accent' => [
                'title' => __('Accent', 'theme-builder-pro'),
                'value' => '#00a0d2',
            ],
            'background' => [
                'title' => __('Background', 'theme-builder-pro'),
                'value' => '#ffffff',
            ],
            'background_secondary' => [
                'title' => __('Background Secondary', 'theme-builder-pro'),
                'value' => '#f5f5f5',
            ],
            'border' => [
                'title' => __('Border', 'theme-builder-pro'),
                'value' => '#e0e0e0',
            ],
            'success' => [
                'title' => __('Success', 'theme-builder-pro'),
                'value' => '#4caf50',
            ],
            'warning' => [
                'title' => __('Warning', 'theme-builder-pro'),
                'value' => '#ff9800',
            ],
            'danger' => [
                'title' => __('Danger', 'theme-builder-pro'),
                'value' => '#f44336',
            ],
            'info' => [
                'title' => __('Info', 'theme-builder-pro'),
                'value' => '#2196f3',
            ],
        ];

        // Merge saved colors
        foreach ($defaults as $key => $color) {
            if (isset($saved_colors[$key])) {
                $defaults[$key]['value'] = $saved_colors[$key];
            }
        }

        // Add custom colors
        $custom_colors = get_option('tbp_custom_colors', []);
        foreach ($custom_colors as $key => $color) {
            $defaults['custom_' . $key] = [
                'title' => $color['title'] ?? __('Custom Color', 'theme-builder-pro'),
                'value' => $color['value'] ?? '#000000',
            ];
        }

        return apply_filters('tbp/schemes/colors', $defaults);
    }

    /**
     * Get Typography Scheme
     */
    public static function get_typography_scheme() {
        $saved_typography = get_option('tbp_global_typography', []);

        $defaults = [
            'primary' => [
                'title' => __('Primary Heading', 'theme-builder-pro'),
                'value' => [
                    'font_family' => 'Roboto',
                    'font_weight' => '600',
                ],
            ],
            'secondary' => [
                'title' => __('Secondary Heading', 'theme-builder-pro'),
                'value' => [
                    'font_family' => 'Roboto',
                    'font_weight' => '500',
                ],
            ],
            'text' => [
                'title' => __('Body Text', 'theme-builder-pro'),
                'value' => [
                    'font_family' => 'Open Sans',
                    'font_weight' => '400',
                ],
            ],
            'accent' => [
                'title' => __('Accent Text', 'theme-builder-pro'),
                'value' => [
                    'font_family' => 'Montserrat',
                    'font_weight' => '500',
                ],
            ],
        ];

        // Merge saved typography
        foreach ($defaults as $key => $typography) {
            if (isset($saved_typography[$key])) {
                $defaults[$key]['value'] = array_merge($defaults[$key]['value'], $saved_typography[$key]);
            }
        }

        return apply_filters('tbp/schemes/typography', $defaults);
    }

    /**
     * Get Scheme Value
     */
    public static function get_scheme_value($type, $key) {
        $schemes = self::get_schemes();

        if (isset($schemes[$type][$key])) {
            return $schemes[$type][$key]['value'];
        }

        return null;
    }

    /**
     * Update Scheme Value
     */
    public static function update_scheme_value($type, $key, $value) {
        $option_key = $type === self::COLOR ? 'tbp_global_colors' : 'tbp_global_typography';
        $saved = get_option($option_key, []);

        $saved[$key] = $value;

        update_option($option_key, $saved);
        self::$schemes = null; // Reset cache

        do_action('tbp/schemes/updated', $type, $key, $value);

        return true;
    }

    /**
     * Get CSS Variables
     */
    public static function get_css_variables() {
        $css = ':root {';

        // Colors
        $colors = self::get_color_scheme();
        foreach ($colors as $key => $color) {
            $css .= '--tbp-color-' . str_replace('_', '-', $key) . ': ' . $color['value'] . ';';
        }

        // Typography
        $typography = self::get_typography_scheme();
        foreach ($typography as $key => $typo) {
            if (isset($typo['value']['font_family'])) {
                $css .= '--tbp-font-' . str_replace('_', '-', $key) . ': "' . $typo['value']['font_family'] . '", sans-serif;';
            }
        }

        // Spacing
        $spacing = self::get_spacing_scheme();
        foreach ($spacing as $key => $value) {
            $css .= '--tbp-spacing-' . $key . ': ' . $value . ';';
        }

        $css .= '}';

        return $css;
    }

    /**
     * Get Spacing Scheme
     */
    public static function get_spacing_scheme() {
        return apply_filters('tbp/schemes/spacing', [
            'xs' => '5px',
            'sm' => '10px',
            'md' => '20px',
            'lg' => '40px',
            'xl' => '60px',
            'xxl' => '100px',
        ]);
    }

    /**
     * Get Size Scheme
     */
    public static function get_size_scheme() {
        return apply_filters('tbp/schemes/size', [
            'container-width' => get_option('tbp_container_width', 1140) . 'px',
            'container-width-wide' => '1400px',
            'container-width-full' => '100%',
            'section-padding' => '60px',
            'column-gap' => '20px',
        ]);
    }

    /**
     * Add Custom Color
     */
    public static function add_custom_color($title, $value) {
        $custom_colors = get_option('tbp_custom_colors', []);
        $key = sanitize_key($title) . '_' . count($custom_colors);

        $custom_colors[$key] = [
            'title' => $title,
            'value' => $value,
        ];

        update_option('tbp_custom_colors', $custom_colors);
        self::$schemes = null;

        return $key;
    }

    /**
     * Remove Custom Color
     */
    public static function remove_custom_color($key) {
        $custom_colors = get_option('tbp_custom_colors', []);

        if (isset($custom_colors[$key])) {
            unset($custom_colors[$key]);
            update_option('tbp_custom_colors', $custom_colors);
            self::$schemes = null;
            return true;
        }

        return false;
    }

    /**
     * Export Schemes
     */
    public static function export_schemes() {
        return [
            'colors' => get_option('tbp_global_colors', []),
            'custom_colors' => get_option('tbp_custom_colors', []),
            'typography' => get_option('tbp_global_typography', []),
        ];
    }

    /**
     * Import Schemes
     */
    public static function import_schemes($data) {
        if (isset($data['colors'])) {
            update_option('tbp_global_colors', $data['colors']);
        }

        if (isset($data['custom_colors'])) {
            update_option('tbp_custom_colors', $data['custom_colors']);
        }

        if (isset($data['typography'])) {
            update_option('tbp_global_typography', $data['typography']);
        }

        self::$schemes = null;

        return true;
    }

    /**
     * Print Global Styles
     */
    public static function print_global_styles() {
        echo '<style id="tbp-global-styles">';
        echo self::get_css_variables();
        echo '</style>';
    }
}

// Print global styles in header
add_action('wp_head', ['TBP_Schemes_Manager', 'print_global_styles'], 1);
