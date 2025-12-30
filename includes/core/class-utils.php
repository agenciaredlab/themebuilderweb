<?php
/**
 * Utility Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Utils {

    /**
     * Generate Unique ID
     */
    public static function generate_id($length = 7) {
        return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', $length)), 0, $length);
    }

    /**
     * Get Post Content
     */
    public static function get_post_content($post_id) {
        $content = get_post_meta($post_id, '_tbp_content', true);
        return $content ? json_decode($content, true) : [];
    }

    /**
     * Save Post Content
     */
    public static function save_post_content($post_id, $content) {
        return update_post_meta($post_id, '_tbp_content', wp_json_encode($content));
    }

    /**
     * Get Post Settings
     */
    public static function get_post_settings($post_id) {
        $settings = get_post_meta($post_id, '_tbp_settings', true);
        return $settings ? json_decode($settings, true) : [];
    }

    /**
     * Save Post Settings
     */
    public static function save_post_settings($post_id, $settings) {
        return update_post_meta($post_id, '_tbp_settings', wp_json_encode($settings));
    }

    /**
     * Get Page CSS
     */
    public static function get_post_css($post_id) {
        return get_post_meta($post_id, '_tbp_css', true) ?: '';
    }

    /**
     * Save Page CSS
     */
    public static function save_post_css($post_id, $css) {
        return update_post_meta($post_id, '_tbp_css', $css);
    }

    /**
     * Sanitize Settings
     */
    public static function sanitize_settings($settings) {
        $sanitized = [];

        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitize_settings($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                $sanitized[$key] = floatval($value);
            } elseif (is_bool($value)) {
                $sanitized[$key] = (bool) $value;
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Parse CSS Value
     */
    public static function parse_css_value($value) {
        if (empty($value)) {
            return '';
        }

        if (is_numeric($value)) {
            return $value . 'px';
        }

        return $value;
    }

    /**
     * Build CSS Rule
     */
    public static function build_css_rule($selector, $properties) {
        if (empty($properties)) {
            return '';
        }

        $css = $selector . ' {';

        foreach ($properties as $property => $value) {
            if (!empty($value) || $value === 0 || $value === '0') {
                $css .= $property . ': ' . $value . ';';
            }
        }

        $css .= '}';

        return $css;
    }

    /**
     * Build Responsive CSS
     */
    public static function build_responsive_css($base_selector, $settings, $property_mapping) {
        $css = '';
        $breakpoints = TBP_Responsive::get_breakpoints();

        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            $device_css = '';
            $suffix = $device === 'desktop' ? '' : '_' . $device;

            foreach ($property_mapping as $setting_key => $css_property) {
                $key = $setting_key . $suffix;
                if (isset($settings[$key]) && $settings[$key] !== '') {
                    $value = self::parse_css_value($settings[$key]);
                    $device_css .= $css_property . ': ' . $value . ';';
                }
            }

            if ($device_css) {
                if ($device === 'desktop') {
                    $css .= $base_selector . ' {' . $device_css . '}';
                } else {
                    $breakpoint = $device === 'tablet' ? $breakpoints['tablet'] : $breakpoints['mobile'];
                    $css .= '@media (max-width: ' . $breakpoint . 'px) {';
                    $css .= $base_selector . ' {' . $device_css . '}';
                    $css .= '}';
                }
            }
        }

        return $css;
    }

    /**
     * Get Placeholder Image
     */
    public static function get_placeholder_image($size = 'medium') {
        $sizes = [
            'small' => [150, 150],
            'medium' => [300, 200],
            'large' => [600, 400],
            'full' => [1200, 800],
        ];

        $dimensions = isset($sizes[$size]) ? $sizes[$size] : $sizes['medium'];

        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="' . $dimensions[0] . '" height="' . $dimensions[1] . '" viewBox="0 0 ' . $dimensions[0] . ' ' . $dimensions[1] . '">
                <rect fill="#f0f0f0" width="100%" height="100%"/>
                <text fill="#999" font-family="sans-serif" font-size="20" x="50%" y="50%" text-anchor="middle" dy=".3em">Image</text>
            </svg>'
        );
    }

    /**
     * Minify CSS
     */
    public static function minify_css($css) {
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/\s*}\s*/', '}', $css);
        $css = preg_replace('/\s*;\s*/', ';', $css);
        $css = preg_replace('/\s*:\s*/', ':', $css);
        $css = preg_replace('/\s*,\s*/', ',', $css);
        $css = str_replace(';}', '}', $css);
        return trim($css);
    }

    /**
     * Get Google Fonts URL
     */
    public static function get_google_fonts_url($fonts) {
        if (empty($fonts)) {
            return '';
        }

        $font_families = [];

        foreach ($fonts as $font) {
            $family = str_replace(' ', '+', $font['family']);
            $weights = isset($font['weights']) ? implode(',', $font['weights']) : '400,500,600,700';
            $font_families[] = $family . ':' . $weights;
        }

        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $font_families) . '&display=swap';
    }

    /**
     * Render Attributes
     */
    public static function render_attributes($attributes) {
        $html = '';

        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            $html .= ' ' . $key . '="' . esc_attr($value) . '"';
        }

        return $html;
    }

    /**
     * Get Widget Icon
     */
    public static function get_widget_icon($icon) {
        $icons = [
            'heading' => 'dashicons-heading',
            'text' => 'dashicons-editor-textcolor',
            'image' => 'dashicons-format-image',
            'video' => 'dashicons-video-alt3',
            'button' => 'dashicons-button',
            'icon' => 'dashicons-star-filled',
            'divider' => 'dashicons-minus',
            'spacer' => 'dashicons-arrow-up-alt2',
            'map' => 'dashicons-location',
            'social' => 'dashicons-share',
            'form' => 'dashicons-feedback',
            'gallery' => 'dashicons-format-gallery',
            'slider' => 'dashicons-images-alt2',
            'tabs' => 'dashicons-excerpt-view',
            'accordion' => 'dashicons-list-view',
            'toggle' => 'dashicons-arrow-down-alt2',
            'counter' => 'dashicons-performance',
            'progress' => 'dashicons-chart-bar',
            'testimonial' => 'dashicons-format-quote',
            'pricing' => 'dashicons-money-alt',
            'countdown' => 'dashicons-clock',
            'html' => 'dashicons-editor-code',
            'shortcode' => 'dashicons-shortcode',
            'menu' => 'dashicons-menu',
            'search' => 'dashicons-search',
            'sidebar' => 'dashicons-welcome-widgets-menus',
            'posts' => 'dashicons-admin-post',
            'products' => 'dashicons-cart',
            'loop' => 'dashicons-update',
        ];

        return isset($icons[$icon]) ? $icons[$icon] : 'dashicons-admin-generic';
    }

    /**
     * Has Dynamic Content
     */
    public static function has_dynamic_content($content) {
        return preg_match('/\{\{([^}]+)\}\}/', $content);
    }

    /**
     * Parse Dynamic Content
     */
    public static function parse_dynamic_content($content, $post_id = null) {
        if (!self::has_dynamic_content($content)) {
            return $content;
        }

        return preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($post_id) {
            return theme_builder_pro()->dynamic_tags->render_tag($matches[1], $post_id);
        }, $content);
    }

    /**
     * Is Preview Mode
     */
    public static function is_preview() {
        return isset($_GET['tbp-preview']) || isset($_GET['tbp-editor']);
    }

    /**
     * Is Editor Mode
     */
    public static function is_editor() {
        return isset($_GET['tbp-editor']) || (is_admin() && isset($_GET['action']) && $_GET['action'] === 'tbp_editor');
    }

    /**
     * Get Current Post ID
     */
    public static function get_current_post_id() {
        $post_id = get_the_ID();

        if (isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
        } elseif (isset($_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
        }

        return $post_id;
    }

    /**
     * Is Built with Theme Builder Pro
     */
    public static function is_built_with_tbp($post_id = null) {
        if (!$post_id) {
            $post_id = self::get_current_post_id();
        }

        return (bool) get_post_meta($post_id, '_tbp_edit_mode', true);
    }

    /**
     * Format Bytes
     */
    public static function format_bytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
