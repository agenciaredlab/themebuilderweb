<?php
/**
 * Assets Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Assets {

    private static $instance = null;
    private $styles = [];
    private $scripts = [];
    private $inline_css = [];
    private $google_fonts = [];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles'], 999);
        add_action('wp_footer', [$this, 'print_inline_css'], 999);
        add_action('wp_head', [$this, 'preload_fonts'], 5);
    }

    /**
     * Register Style
     */
    public function register_style($handle, $src, $deps = [], $ver = TBP_VERSION) {
        $this->styles[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
        ];
    }

    /**
     * Enqueue Style
     */
    public function enqueue_style($handle) {
        if (isset($this->styles[$handle])) {
            $style = $this->styles[$handle];
            wp_enqueue_style($handle, $style['src'], $style['deps'], $style['ver']);
        }
    }

    /**
     * Register Script
     */
    public function register_script($handle, $src, $deps = [], $ver = TBP_VERSION, $in_footer = true) {
        $this->scripts[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer,
        ];
    }

    /**
     * Enqueue Script
     */
    public function enqueue_script($handle) {
        if (isset($this->scripts[$handle])) {
            $script = $this->scripts[$handle];
            wp_enqueue_script($handle, $script['src'], $script['deps'], $script['ver'], $script['in_footer']);
        }
    }

    /**
     * Add Inline CSS
     */
    public function add_inline_css($css, $post_id = 0) {
        $this->inline_css[$post_id] = isset($this->inline_css[$post_id]) ? $this->inline_css[$post_id] . $css : $css;
    }

    /**
     * Add Google Font
     */
    public function add_google_font($family, $weights = ['400', '500', '600', '700']) {
        if (!isset($this->google_fonts[$family])) {
            $this->google_fonts[$family] = [];
        }

        $this->google_fonts[$family] = array_unique(array_merge($this->google_fonts[$family], $weights));
    }

    /**
     * Enqueue Styles
     */
    public function enqueue_styles() {
        // Enqueue Google Fonts
        if (!empty($this->google_fonts) && get_option('tbp_google_fonts', true)) {
            $fonts = [];
            foreach ($this->google_fonts as $family => $weights) {
                $fonts[] = [
                    'family' => $family,
                    'weights' => $weights,
                ];
            }

            $google_fonts_url = TBP_Utils::get_google_fonts_url($fonts);
            if ($google_fonts_url) {
                wp_enqueue_style('tbp-google-fonts', $google_fonts_url, [], null);
            }
        }

        // Enqueue Widget Styles
        wp_enqueue_style('tbp-widgets', TBP_ASSETS_URL . 'css/widgets.css', [], TBP_VERSION);

        // Enqueue Animations
        if (get_option('tbp_enable_animations', true)) {
            wp_enqueue_style('tbp-animations', TBP_ASSETS_URL . 'css/animations.css', [], TBP_VERSION);
        }
    }

    /**
     * Preload Fonts
     */
    public function preload_fonts() {
        $fonts_to_preload = apply_filters('tbp/fonts/preload', []);

        foreach ($fonts_to_preload as $font_url) {
            echo '<link rel="preload" href="' . esc_url($font_url) . '" as="font" type="font/woff2" crossorigin>';
        }
    }

    /**
     * Print Inline CSS
     */
    public function print_inline_css() {
        if (empty($this->inline_css)) {
            return;
        }

        $css = implode('', $this->inline_css);
        $css = TBP_Utils::minify_css($css);

        if (!empty($css)) {
            echo '<style id="tbp-inline-css">' . $css . '</style>';
        }
    }

    /**
     * Get Post CSS
     */
    public function get_post_css($post_id) {
        $css = get_post_meta($post_id, '_tbp_css', true);

        if (!$css) {
            $css = $this->generate_post_css($post_id);
            update_post_meta($post_id, '_tbp_css', $css);
        }

        return $css;
    }

    /**
     * Generate Post CSS
     */
    public function generate_post_css($post_id) {
        $content = TBP_Utils::get_post_content($post_id);
        $settings = TBP_Utils::get_post_settings($post_id);

        $css = '';

        // Page Settings CSS
        $css .= $this->generate_page_css($settings);

        // Elements CSS
        if (!empty($content['elements'])) {
            $css .= $this->generate_elements_css($content['elements']);
        }

        return TBP_Utils::minify_css($css);
    }

    /**
     * Generate Page CSS
     */
    private function generate_page_css($settings) {
        $css = '';

        // Page background
        if (!empty($settings['page_background_color'])) {
            $css .= 'body.tbp-page { background-color: ' . $settings['page_background_color'] . '; }';
        }

        if (!empty($settings['page_background_image'])) {
            $css .= 'body.tbp-page { background-image: url(' . $settings['page_background_image'] . '); ';
            $css .= 'background-size: ' . ($settings['page_background_size'] ?? 'cover') . '; ';
            $css .= 'background-position: ' . ($settings['page_background_position'] ?? 'center center') . '; ';
            $css .= 'background-repeat: ' . ($settings['page_background_repeat'] ?? 'no-repeat') . '; }';
        }

        // Content width
        if (!empty($settings['content_width'])) {
            $css .= '.tbp-section-boxed .tbp-container { max-width: ' . $settings['content_width'] . 'px; }';
        }

        return $css;
    }

    /**
     * Generate Elements CSS
     */
    public function generate_elements_css($elements) {
        $css = '';

        foreach ($elements as $element) {
            $css .= $this->generate_element_css($element);

            if (!empty($element['elements'])) {
                $css .= $this->generate_elements_css($element['elements']);
            }
        }

        return $css;
    }

    /**
     * Generate Element CSS
     */
    private function generate_element_css($element) {
        $css = '';
        $id = $element['id'];
        $settings = $element['settings'] ?? [];
        $type = $element['type'] ?? 'widget';
        $widget_type = $element['widgetType'] ?? '';

        $selector = '.tbp-element-' . $id;

        // Background
        $css .= $this->generate_background_css($selector, $settings);

        // Border
        $css .= $this->generate_border_css($selector, $settings);

        // Typography
        $css .= $this->generate_typography_css($selector, $settings);

        // Spacing (margin/padding)
        $css .= $this->generate_spacing_css($selector, $settings);

        // Size
        $css .= $this->generate_size_css($selector, $settings, $type);

        // Position
        $css .= $this->generate_position_css($selector, $settings);

        // Custom CSS
        if (!empty($settings['custom_css'])) {
            $css .= str_replace('selector', $selector, $settings['custom_css']);
        }

        // Widget specific CSS
        if ($widget_type && theme_builder_pro()->widgets_manager) {
            $widget = theme_builder_pro()->widgets_manager->get_widget($widget_type);
            if ($widget) {
                $css .= $widget->get_element_css($id, $settings);
            }
        }

        return $css;
    }

    /**
     * Generate Background CSS
     */
    private function generate_background_css($selector, $settings) {
        $css = '';
        $properties = [];

        foreach (['', '_tablet', '_mobile'] as $suffix) {
            $device_props = [];

            // Background Type
            $bg_type = $settings['background_type' . $suffix] ?? ($suffix ? null : 'classic');

            if ($bg_type === 'classic') {
                // Color
                if (!empty($settings['background_color' . $suffix])) {
                    $device_props['background-color'] = $settings['background_color' . $suffix];
                }

                // Image
                if (!empty($settings['background_image' . $suffix]['url'])) {
                    $device_props['background-image'] = 'url(' . $settings['background_image' . $suffix]['url'] . ')';
                    $device_props['background-size'] = $settings['background_size' . $suffix] ?? 'cover';
                    $device_props['background-position'] = $settings['background_position' . $suffix] ?? 'center center';
                    $device_props['background-repeat'] = $settings['background_repeat' . $suffix] ?? 'no-repeat';
                    $device_props['background-attachment'] = $settings['background_attachment' . $suffix] ?? 'scroll';
                }
            } elseif ($bg_type === 'gradient') {
                // Gradient
                $gradient = $this->build_gradient($settings, $suffix);
                if ($gradient) {
                    $device_props['background-image'] = $gradient;
                }
            }

            if (!empty($device_props)) {
                if ($suffix === '') {
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                } else {
                    $breakpoint = TBP_Responsive::get_breakpoint($suffix === '_tablet' ? 'tablet' : 'mobile');
                    $css .= '@media (max-width: ' . $breakpoint . 'px) {';
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                    $css .= '}';
                }
            }
        }

        // Background Overlay
        if (!empty($settings['background_overlay_color'])) {
            $css .= $selector . '::before { content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0; ';
            $css .= 'background-color: ' . $settings['background_overlay_color'] . '; ';
            $css .= 'opacity: ' . ($settings['background_overlay_opacity'] ?? 0.5) . '; ';
            $css .= 'pointer-events: none; z-index: 0; }';
        }

        return $css;
    }

    /**
     * Build Gradient
     */
    private function build_gradient($settings, $suffix = '') {
        $type = $settings['gradient_type' . $suffix] ?? 'linear';
        $color1 = $settings['gradient_color1' . $suffix] ?? '#ffffff';
        $color2 = $settings['gradient_color2' . $suffix] ?? '#000000';
        $location1 = $settings['gradient_location1' . $suffix] ?? 0;
        $location2 = $settings['gradient_location2' . $suffix] ?? 100;

        if ($type === 'linear') {
            $angle = $settings['gradient_angle' . $suffix] ?? 180;
            return "linear-gradient({$angle}deg, {$color1} {$location1}%, {$color2} {$location2}%)";
        } else {
            $position = $settings['gradient_position' . $suffix] ?? 'center center';
            return "radial-gradient(at {$position}, {$color1} {$location1}%, {$color2} {$location2}%)";
        }
    }

    /**
     * Generate Border CSS
     */
    private function generate_border_css($selector, $settings) {
        $css = '';

        foreach (['', '_tablet', '_mobile'] as $suffix) {
            $device_props = [];

            // Border Type
            $border_type = $settings['border_type' . $suffix] ?? '';
            if ($border_type && $border_type !== 'none') {
                $device_props['border-style'] = $border_type;

                // Border Width
                $border_width = $settings['border_width' . $suffix] ?? [];
                if (!empty($border_width)) {
                    if (isset($border_width['top'])) $device_props['border-top-width'] = $border_width['top'] . ($border_width['unit'] ?? 'px');
                    if (isset($border_width['right'])) $device_props['border-right-width'] = $border_width['right'] . ($border_width['unit'] ?? 'px');
                    if (isset($border_width['bottom'])) $device_props['border-bottom-width'] = $border_width['bottom'] . ($border_width['unit'] ?? 'px');
                    if (isset($border_width['left'])) $device_props['border-left-width'] = $border_width['left'] . ($border_width['unit'] ?? 'px');
                }

                // Border Color
                if (!empty($settings['border_color' . $suffix])) {
                    $device_props['border-color'] = $settings['border_color' . $suffix];
                }
            }

            // Border Radius
            $border_radius = $settings['border_radius' . $suffix] ?? [];
            if (!empty($border_radius)) {
                $unit = $border_radius['unit'] ?? 'px';
                if (isset($border_radius['top'])) $device_props['border-top-left-radius'] = $border_radius['top'] . $unit;
                if (isset($border_radius['right'])) $device_props['border-top-right-radius'] = $border_radius['right'] . $unit;
                if (isset($border_radius['bottom'])) $device_props['border-bottom-right-radius'] = $border_radius['bottom'] . $unit;
                if (isset($border_radius['left'])) $device_props['border-bottom-left-radius'] = $border_radius['left'] . $unit;
            }

            // Box Shadow
            if (!empty($settings['box_shadow' . $suffix])) {
                $shadow = $settings['box_shadow' . $suffix];
                $device_props['box-shadow'] = $this->build_box_shadow($shadow);
            }

            if (!empty($device_props)) {
                if ($suffix === '') {
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                } else {
                    $breakpoint = TBP_Responsive::get_breakpoint($suffix === '_tablet' ? 'tablet' : 'mobile');
                    $css .= '@media (max-width: ' . $breakpoint . 'px) {';
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                    $css .= '}';
                }
            }
        }

        return $css;
    }

    /**
     * Build Box Shadow
     */
    private function build_box_shadow($shadow) {
        $h = $shadow['horizontal'] ?? 0;
        $v = $shadow['vertical'] ?? 0;
        $blur = $shadow['blur'] ?? 10;
        $spread = $shadow['spread'] ?? 0;
        $color = $shadow['color'] ?? 'rgba(0,0,0,0.3)';
        $position = !empty($shadow['position']) && $shadow['position'] === 'inset' ? 'inset ' : '';

        return "{$position}{$h}px {$v}px {$blur}px {$spread}px {$color}";
    }

    /**
     * Generate Typography CSS
     */
    private function generate_typography_css($selector, $settings) {
        $css = '';

        foreach (['', '_tablet', '_mobile'] as $suffix) {
            $device_props = [];

            if (!empty($settings['typography_font_family' . $suffix])) {
                $font_family = $settings['typography_font_family' . $suffix];
                $device_props['font-family'] = '"' . $font_family . '", sans-serif';
                $this->add_google_font($font_family);
            }

            if (!empty($settings['typography_font_size' . $suffix])) {
                $size = $settings['typography_font_size' . $suffix];
                $device_props['font-size'] = is_array($size) ? $size['size'] . ($size['unit'] ?? 'px') : $size . 'px';
            }

            if (!empty($settings['typography_font_weight' . $suffix])) {
                $device_props['font-weight'] = $settings['typography_font_weight' . $suffix];
            }

            if (!empty($settings['typography_line_height' . $suffix])) {
                $lh = $settings['typography_line_height' . $suffix];
                $device_props['line-height'] = is_array($lh) ? $lh['size'] . ($lh['unit'] ?? '') : $lh;
            }

            if (!empty($settings['typography_letter_spacing' . $suffix])) {
                $ls = $settings['typography_letter_spacing' . $suffix];
                $device_props['letter-spacing'] = is_array($ls) ? $ls['size'] . ($ls['unit'] ?? 'px') : $ls . 'px';
            }

            if (!empty($settings['typography_text_transform' . $suffix])) {
                $device_props['text-transform'] = $settings['typography_text_transform' . $suffix];
            }

            if (!empty($settings['typography_font_style' . $suffix])) {
                $device_props['font-style'] = $settings['typography_font_style' . $suffix];
            }

            if (!empty($settings['typography_text_decoration' . $suffix])) {
                $device_props['text-decoration'] = $settings['typography_text_decoration' . $suffix];
            }

            if (!empty($device_props)) {
                if ($suffix === '') {
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                } else {
                    $breakpoint = TBP_Responsive::get_breakpoint($suffix === '_tablet' ? 'tablet' : 'mobile');
                    $css .= '@media (max-width: ' . $breakpoint . 'px) {';
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                    $css .= '}';
                }
            }
        }

        return $css;
    }

    /**
     * Generate Spacing CSS
     */
    private function generate_spacing_css($selector, $settings) {
        $css = '';

        foreach (['', '_tablet', '_mobile'] as $suffix) {
            $device_props = [];

            // Margin
            $margin = $settings['margin' . $suffix] ?? [];
            if (!empty($margin)) {
                $unit = $margin['unit'] ?? 'px';
                if (isset($margin['top']) && $margin['top'] !== '') $device_props['margin-top'] = $margin['top'] . $unit;
                if (isset($margin['right']) && $margin['right'] !== '') $device_props['margin-right'] = $margin['right'] . $unit;
                if (isset($margin['bottom']) && $margin['bottom'] !== '') $device_props['margin-bottom'] = $margin['bottom'] . $unit;
                if (isset($margin['left']) && $margin['left'] !== '') $device_props['margin-left'] = $margin['left'] . $unit;
            }

            // Padding
            $padding = $settings['padding' . $suffix] ?? [];
            if (!empty($padding)) {
                $unit = $padding['unit'] ?? 'px';
                if (isset($padding['top']) && $padding['top'] !== '') $device_props['padding-top'] = $padding['top'] . $unit;
                if (isset($padding['right']) && $padding['right'] !== '') $device_props['padding-right'] = $padding['right'] . $unit;
                if (isset($padding['bottom']) && $padding['bottom'] !== '') $device_props['padding-bottom'] = $padding['bottom'] . $unit;
                if (isset($padding['left']) && $padding['left'] !== '') $device_props['padding-left'] = $padding['left'] . $unit;
            }

            if (!empty($device_props)) {
                if ($suffix === '') {
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                } else {
                    $breakpoint = TBP_Responsive::get_breakpoint($suffix === '_tablet' ? 'tablet' : 'mobile');
                    $css .= '@media (max-width: ' . $breakpoint . 'px) {';
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                    $css .= '}';
                }
            }
        }

        return $css;
    }

    /**
     * Generate Size CSS
     */
    private function generate_size_css($selector, $settings, $type) {
        $css = '';

        foreach (['', '_tablet', '_mobile'] as $suffix) {
            $device_props = [];

            // Width
            if (!empty($settings['width' . $suffix])) {
                $width = $settings['width' . $suffix];
                $device_props['width'] = is_array($width) ? $width['size'] . ($width['unit'] ?? 'px') : $width . 'px';
            }

            // Min Width
            if (!empty($settings['min_width' . $suffix])) {
                $min_width = $settings['min_width' . $suffix];
                $device_props['min-width'] = is_array($min_width) ? $min_width['size'] . ($min_width['unit'] ?? 'px') : $min_width . 'px';
            }

            // Max Width
            if (!empty($settings['max_width' . $suffix])) {
                $max_width = $settings['max_width' . $suffix];
                $device_props['max-width'] = is_array($max_width) ? $max_width['size'] . ($max_width['unit'] ?? 'px') : $max_width . 'px';
            }

            // Height
            if (!empty($settings['height' . $suffix])) {
                $height = $settings['height' . $suffix];
                $device_props['height'] = is_array($height) ? $height['size'] . ($height['unit'] ?? 'px') : $height . 'px';
            }

            // Min Height
            if (!empty($settings['min_height' . $suffix])) {
                $min_height = $settings['min_height' . $suffix];
                $device_props['min-height'] = is_array($min_height) ? $min_height['size'] . ($min_height['unit'] ?? 'px') : $min_height . 'px';
            }

            // Column Width (for sections)
            if ($type === 'column' && !empty($settings['column_width' . $suffix])) {
                $device_props['width'] = $settings['column_width' . $suffix] . '%';
            }

            if (!empty($device_props)) {
                if ($suffix === '') {
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                } else {
                    $breakpoint = TBP_Responsive::get_breakpoint($suffix === '_tablet' ? 'tablet' : 'mobile');
                    $css .= '@media (max-width: ' . $breakpoint . 'px) {';
                    $css .= TBP_Utils::build_css_rule($selector, $device_props);
                    $css .= '}';
                }
            }
        }

        return $css;
    }

    /**
     * Generate Position CSS
     */
    private function generate_position_css($selector, $settings) {
        $css = '';
        $props = [];

        // Z-Index
        if (isset($settings['z_index']) && $settings['z_index'] !== '') {
            $props['z-index'] = $settings['z_index'];
        }

        // CSS Classes
        if (!empty($settings['css_classes'])) {
            // Handled in HTML rendering
        }

        // Overflow
        if (!empty($settings['overflow'])) {
            $props['overflow'] = $settings['overflow'];
        }

        // Object Fit (for images)
        if (!empty($settings['object_fit'])) {
            $props['object-fit'] = $settings['object_fit'];
        }

        // Opacity
        if (isset($settings['opacity']) && $settings['opacity'] !== '' && $settings['opacity'] !== 1) {
            $props['opacity'] = $settings['opacity'];
        }

        // CSS Filters
        $filters = [];
        if (!empty($settings['filter_blur'])) $filters[] = 'blur(' . $settings['filter_blur'] . 'px)';
        if (!empty($settings['filter_brightness'])) $filters[] = 'brightness(' . $settings['filter_brightness'] . '%)';
        if (!empty($settings['filter_contrast'])) $filters[] = 'contrast(' . $settings['filter_contrast'] . '%)';
        if (!empty($settings['filter_saturate'])) $filters[] = 'saturate(' . $settings['filter_saturate'] . '%)';
        if (!empty($settings['filter_hue'])) $filters[] = 'hue-rotate(' . $settings['filter_hue'] . 'deg)';

        if (!empty($filters)) {
            $props['filter'] = implode(' ', $filters);
        }

        // Transform
        $transforms = [];
        if (!empty($settings['transform_rotate'])) $transforms[] = 'rotate(' . $settings['transform_rotate'] . 'deg)';
        if (!empty($settings['transform_scale'])) $transforms[] = 'scale(' . $settings['transform_scale'] . ')';
        if (!empty($settings['transform_skew_x'])) $transforms[] = 'skewX(' . $settings['transform_skew_x'] . 'deg)';
        if (!empty($settings['transform_skew_y'])) $transforms[] = 'skewY(' . $settings['transform_skew_y'] . 'deg)';
        if (!empty($settings['transform_translate_x'])) $transforms[] = 'translateX(' . $settings['transform_translate_x'] . 'px)';
        if (!empty($settings['transform_translate_y'])) $transforms[] = 'translateY(' . $settings['transform_translate_y'] . 'px)';

        if (!empty($transforms)) {
            $props['transform'] = implode(' ', $transforms);
        }

        // Transition
        if (!empty($settings['transition_duration'])) {
            $property = $settings['transition_property'] ?? 'all';
            $duration = $settings['transition_duration'] . 'ms';
            $timing = $settings['transition_timing'] ?? 'ease';
            $delay = !empty($settings['transition_delay']) ? $settings['transition_delay'] . 'ms' : '0ms';
            $props['transition'] = "{$property} {$duration} {$timing} {$delay}";
        }

        if (!empty($props)) {
            $css .= TBP_Utils::build_css_rule($selector, $props);
        }

        // Hover State
        $hover_props = [];

        if (!empty($settings['hover_opacity'])) {
            $hover_props['opacity'] = $settings['hover_opacity'];
        }

        if (!empty($settings['hover_transform_scale'])) {
            $hover_props['transform'] = 'scale(' . $settings['hover_transform_scale'] . ')';
        }

        if (!empty($settings['hover_box_shadow'])) {
            $hover_props['box-shadow'] = $this->build_box_shadow($settings['hover_box_shadow']);
        }

        if (!empty($hover_props)) {
            $css .= TBP_Utils::build_css_rule($selector . ':hover', $hover_props);
        }

        return $css;
    }

    /**
     * Clear Cache
     */
    public function clear_cache($post_id = null) {
        if ($post_id) {
            delete_post_meta($post_id, '_tbp_css');
        } else {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_tbp_css'");
        }

        // Clear any external caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        do_action('tbp/cache/cleared', $post_id);
    }
}

// Initialize
TBP_Assets::instance();
