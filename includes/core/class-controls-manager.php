<?php
/**
 * Controls Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Controls_Manager {

    private static $controls = null;

    /**
     * Control Types Constants
     */
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const WYSIWYG = 'wysiwyg';
    const NUMBER = 'number';
    const SLIDER = 'slider';
    const SELECT = 'select';
    const SELECT2 = 'select2';
    const SWITCHER = 'switcher';
    const CHOOSE = 'choose';
    const COLOR = 'color';
    const MEDIA = 'media';
    const GALLERY = 'gallery';
    const URL = 'url';
    const ICON = 'icon';
    const FONT = 'font';
    const DATE_TIME = 'date_time';
    const CODE = 'code';
    const HIDDEN = 'hidden';
    const HEADING = 'heading';
    const DIVIDER = 'divider';
    const RAW_HTML = 'raw_html';
    const BUTTON = 'button';
    const POPOVER = 'popover';
    const TABS = 'tabs';
    const TAB = 'tab';
    const SECTION = 'section';
    const REPEATER = 'repeater';
    const DIMENSIONS = 'dimensions';
    const BOX_SHADOW = 'box_shadow';
    const TEXT_SHADOW = 'text_shadow';
    const BORDER = 'border';
    const TYPOGRAPHY = 'typography';
    const BACKGROUND = 'background';
    const ANIMATION = 'animation';
    const ENTRANCE_ANIMATION = 'entrance_animation';
    const HOVER_ANIMATION = 'hover_animation';
    const IMAGE_DIMENSIONS = 'image_dimensions';
    const STRUCTURE = 'structure';
    const EXIT_ANIMATION = 'exit_animation';
    const DEPRECATED_NOTICE = 'deprecated_notice';

    /**
     * Get Controls
     */
    public static function get_controls() {
        if (self::$controls === null) {
            self::$controls = self::init_controls();
        }

        return self::$controls;
    }

    /**
     * Initialize Controls
     */
    private static function init_controls() {
        return apply_filters('tbp/controls', [
            self::TEXT => [
                'type' => 'text',
                'label' => __('Text', 'theme-builder-pro'),
                'default' => '',
            ],
            self::TEXTAREA => [
                'type' => 'textarea',
                'label' => __('Textarea', 'theme-builder-pro'),
                'default' => '',
                'rows' => 5,
            ],
            self::WYSIWYG => [
                'type' => 'wysiwyg',
                'label' => __('WYSIWYG Editor', 'theme-builder-pro'),
                'default' => '',
            ],
            self::NUMBER => [
                'type' => 'number',
                'label' => __('Number', 'theme-builder-pro'),
                'default' => 0,
                'min' => 0,
                'max' => 100,
                'step' => 1,
            ],
            self::SLIDER => [
                'type' => 'slider',
                'label' => __('Slider', 'theme-builder-pro'),
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'size_units' => ['px', 'em', 'rem', '%', 'vw', 'vh'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'vw' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
            ],
            self::SELECT => [
                'type' => 'select',
                'label' => __('Select', 'theme-builder-pro'),
                'default' => '',
                'options' => [],
            ],
            self::SELECT2 => [
                'type' => 'select2',
                'label' => __('Select2', 'theme-builder-pro'),
                'default' => '',
                'options' => [],
                'multiple' => false,
            ],
            self::SWITCHER => [
                'type' => 'switcher',
                'label' => __('Switcher', 'theme-builder-pro'),
                'default' => '',
                'label_on' => __('Yes', 'theme-builder-pro'),
                'label_off' => __('No', 'theme-builder-pro'),
                'return_value' => 'yes',
            ],
            self::CHOOSE => [
                'type' => 'choose',
                'label' => __('Choose', 'theme-builder-pro'),
                'default' => '',
                'options' => [],
                'toggle' => true,
            ],
            self::COLOR => [
                'type' => 'color',
                'label' => __('Color', 'theme-builder-pro'),
                'default' => '',
                'alpha' => true,
                'global' => true,
            ],
            self::MEDIA => [
                'type' => 'media',
                'label' => __('Media', 'theme-builder-pro'),
                'default' => [
                    'url' => '',
                    'id' => '',
                ],
                'media_types' => ['image'],
            ],
            self::GALLERY => [
                'type' => 'gallery',
                'label' => __('Gallery', 'theme-builder-pro'),
                'default' => [],
            ],
            self::URL => [
                'type' => 'url',
                'label' => __('URL', 'theme-builder-pro'),
                'default' => [
                    'url' => '',
                    'is_external' => false,
                    'nofollow' => false,
                    'custom_attributes' => '',
                ],
                'show_external' => true,
                'show_nofollow' => true,
            ],
            self::ICON => [
                'type' => 'icon',
                'label' => __('Icon', 'theme-builder-pro'),
                'default' => [
                    'value' => '',
                    'library' => 'fa-solid',
                ],
                'include' => [],
                'exclude' => [],
            ],
            self::FONT => [
                'type' => 'font',
                'label' => __('Font', 'theme-builder-pro'),
                'default' => '',
                'groups' => [
                    'system' => __('System Fonts', 'theme-builder-pro'),
                    'google' => __('Google Fonts', 'theme-builder-pro'),
                ],
            ],
            self::DATE_TIME => [
                'type' => 'date_time',
                'label' => __('Date Time', 'theme-builder-pro'),
                'default' => '',
                'picker_options' => [],
            ],
            self::CODE => [
                'type' => 'code',
                'label' => __('Code', 'theme-builder-pro'),
                'default' => '',
                'language' => 'html',
                'rows' => 10,
            ],
            self::HIDDEN => [
                'type' => 'hidden',
                'default' => '',
            ],
            self::HEADING => [
                'type' => 'heading',
                'label' => '',
                'separator' => 'before',
            ],
            self::DIVIDER => [
                'type' => 'divider',
            ],
            self::RAW_HTML => [
                'type' => 'raw_html',
                'raw' => '',
            ],
            self::BUTTON => [
                'type' => 'button',
                'text' => __('Click', 'theme-builder-pro'),
                'button_type' => 'default',
                'event' => '',
            ],
            self::DIMENSIONS => [
                'type' => 'dimensions',
                'label' => __('Dimensions', 'theme-builder-pro'),
                'default' => [
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'size_units' => ['px', 'em', '%'],
                'allowed_dimensions' => ['top', 'right', 'bottom', 'left'],
            ],
            self::BOX_SHADOW => [
                'type' => 'box_shadow',
                'label' => __('Box Shadow', 'theme-builder-pro'),
                'default' => [
                    'horizontal' => 0,
                    'vertical' => 0,
                    'blur' => 10,
                    'spread' => 0,
                    'color' => 'rgba(0, 0, 0, 0.5)',
                    'position' => 'outline',
                ],
            ],
            self::TEXT_SHADOW => [
                'type' => 'text_shadow',
                'label' => __('Text Shadow', 'theme-builder-pro'),
                'default' => [
                    'horizontal' => 0,
                    'vertical' => 0,
                    'blur' => 10,
                    'color' => 'rgba(0, 0, 0, 0.5)',
                ],
            ],
            self::BORDER => [
                'type' => 'border',
                'label' => __('Border', 'theme-builder-pro'),
                'default' => [
                    'border_type' => 'none',
                    'width' => [
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'unit' => 'px',
                        'isLinked' => true,
                    ],
                    'color' => '',
                ],
                'selector' => '',
            ],
            self::TYPOGRAPHY => [
                'type' => 'typography',
                'label' => __('Typography', 'theme-builder-pro'),
                'default' => [],
                'selector' => '',
                'global' => true,
                'fields_options' => [
                    'font_family' => [],
                    'font_size' => [],
                    'font_weight' => [],
                    'line_height' => [],
                    'letter_spacing' => [],
                    'text_transform' => [],
                    'font_style' => [],
                    'text_decoration' => [],
                ],
            ],
            self::BACKGROUND => [
                'type' => 'background',
                'label' => __('Background', 'theme-builder-pro'),
                'default' => [],
                'selector' => '',
                'types' => ['classic', 'gradient', 'video', 'slideshow'],
            ],
            self::ANIMATION => [
                'type' => 'animation',
                'label' => __('Animation', 'theme-builder-pro'),
                'default' => '',
            ],
            self::ENTRANCE_ANIMATION => [
                'type' => 'entrance_animation',
                'label' => __('Entrance Animation', 'theme-builder-pro'),
                'default' => '',
            ],
            self::HOVER_ANIMATION => [
                'type' => 'hover_animation',
                'label' => __('Hover Animation', 'theme-builder-pro'),
                'default' => '',
            ],
            self::IMAGE_DIMENSIONS => [
                'type' => 'image_dimensions',
                'label' => __('Image Size', 'theme-builder-pro'),
                'default' => [
                    'width' => '',
                    'height' => '',
                ],
            ],
            self::STRUCTURE => [
                'type' => 'structure',
                'label' => __('Structure', 'theme-builder-pro'),
                'default' => '1',
            ],
            self::REPEATER => [
                'type' => 'repeater',
                'label' => __('Repeater', 'theme-builder-pro'),
                'default' => [],
                'fields' => [],
                'title_field' => '',
            ],
        ]);
    }

    /**
     * Get Control Config
     */
    public static function get_control($type) {
        $controls = self::get_controls();
        return isset($controls[$type]) ? $controls[$type] : null;
    }

    /**
     * Render Control
     */
    public static function render_control($control, $value = null) {
        $type = $control['type'] ?? 'text';
        $name = $control['name'] ?? '';
        $label = $control['label'] ?? '';
        $default = $control['default'] ?? '';
        $value = $value !== null ? $value : $default;
        $description = $control['description'] ?? '';
        $responsive = $control['responsive'] ?? false;
        $condition = $control['condition'] ?? [];

        $html = '<div class="tbp-control tbp-control-' . esc_attr($type) . '" data-name="' . esc_attr($name) . '"';

        if (!empty($condition)) {
            $html .= ' data-condition="' . esc_attr(wp_json_encode($condition)) . '"';
        }

        if ($responsive) {
            $html .= ' data-responsive="true"';
        }

        $html .= '>';

        if ($label) {
            $html .= '<label class="tbp-control-label">' . esc_html($label);
            if ($responsive) {
                $html .= '<span class="tbp-responsive-switcher"></span>';
            }
            $html .= '</label>';
        }

        $html .= '<div class="tbp-control-input">';
        $html .= self::render_control_input($control, $value);
        $html .= '</div>';

        if ($description) {
            $html .= '<div class="tbp-control-description">' . esc_html($description) . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render Control Input
     */
    private static function render_control_input($control, $value) {
        $type = $control['type'] ?? 'text';
        $name = $control['name'] ?? '';

        switch ($type) {
            case self::TEXT:
                return '<input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="tbp-control-text" />';

            case self::TEXTAREA:
                $rows = $control['rows'] ?? 5;
                return '<textarea name="' . esc_attr($name) . '" rows="' . $rows . '" class="tbp-control-textarea">' . esc_textarea($value) . '</textarea>';

            case self::NUMBER:
                $min = $control['min'] ?? '';
                $max = $control['max'] ?? '';
                $step = $control['step'] ?? 1;
                return '<input type="number" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" min="' . $min . '" max="' . $max . '" step="' . $step . '" class="tbp-control-number" />';

            case self::SELECT:
                $options = $control['options'] ?? [];
                $html = '<select name="' . esc_attr($name) . '" class="tbp-control-select">';
                foreach ($options as $key => $label) {
                    $selected = selected($value, $key, false);
                    $html .= '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($label) . '</option>';
                }
                $html .= '</select>';
                return $html;

            case self::SWITCHER:
                $label_on = $control['label_on'] ?? __('Yes', 'theme-builder-pro');
                $label_off = $control['label_off'] ?? __('No', 'theme-builder-pro');
                $return_value = $control['return_value'] ?? 'yes';
                $checked = $value === $return_value ? 'checked' : '';
                return '<label class="tbp-control-switcher">
                    <input type="checkbox" name="' . esc_attr($name) . '" value="' . esc_attr($return_value) . '" ' . $checked . ' />
                    <span class="tbp-switcher-slider" data-on="' . esc_attr($label_on) . '" data-off="' . esc_attr($label_off) . '"></span>
                </label>';

            case self::COLOR:
                return '<input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="tbp-control-color" data-alpha="' . ($control['alpha'] ?? true) . '" />';

            case self::MEDIA:
                $url = is_array($value) ? ($value['url'] ?? '') : $value;
                $id = is_array($value) ? ($value['id'] ?? '') : 0;
                return '<div class="tbp-control-media" data-id="' . esc_attr($id) . '">
                    <div class="tbp-media-preview">' . ($url ? '<img src="' . esc_url($url) . '" />' : '') . '</div>
                    <input type="hidden" name="' . esc_attr($name) . '[url]" value="' . esc_attr($url) . '" />
                    <input type="hidden" name="' . esc_attr($name) . '[id]" value="' . esc_attr($id) . '" />
                    <button type="button" class="tbp-media-select">' . __('Choose Image', 'theme-builder-pro') . '</button>
                    <button type="button" class="tbp-media-remove">' . __('Remove', 'theme-builder-pro') . '</button>
                </div>';

            case self::SLIDER:
                $size_units = $control['size_units'] ?? ['px'];
                $range = $control['range'] ?? [];
                $size = is_array($value) ? ($value['size'] ?? 0) : $value;
                $unit = is_array($value) ? ($value['unit'] ?? 'px') : 'px';

                $html = '<div class="tbp-control-slider" data-range="' . esc_attr(wp_json_encode($range)) . '">';
                $html .= '<input type="range" class="tbp-slider-input" value="' . esc_attr($size) . '" />';
                $html .= '<input type="number" name="' . esc_attr($name) . '[size]" value="' . esc_attr($size) . '" class="tbp-slider-number" />';
                if (count($size_units) > 1) {
                    $html .= '<select name="' . esc_attr($name) . '[unit]" class="tbp-slider-unit">';
                    foreach ($size_units as $u) {
                        $html .= '<option value="' . esc_attr($u) . '"' . selected($unit, $u, false) . '>' . esc_html($u) . '</option>';
                    }
                    $html .= '</select>';
                } else {
                    $html .= '<span class="tbp-slider-unit-label">' . esc_html($size_units[0]) . '</span>';
                    $html .= '<input type="hidden" name="' . esc_attr($name) . '[unit]" value="' . esc_attr($size_units[0]) . '" />';
                }
                $html .= '</div>';
                return $html;

            case self::DIMENSIONS:
                $size_units = $control['size_units'] ?? ['px', 'em', '%'];
                $defaults = [
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                    'unit' => 'px',
                    'isLinked' => true,
                ];
                $value = is_array($value) ? array_merge($defaults, $value) : $defaults;

                $html = '<div class="tbp-control-dimensions">';
                $html .= '<div class="tbp-dimensions-inputs">';
                foreach (['top', 'right', 'bottom', 'left'] as $pos) {
                    $html .= '<div class="tbp-dimension">';
                    $html .= '<input type="number" name="' . esc_attr($name) . '[' . $pos . ']" value="' . esc_attr($value[$pos]) . '" placeholder="-" />';
                    $html .= '<label>' . ucfirst($pos) . '</label>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                $html .= '<button type="button" class="tbp-dimensions-link' . ($value['isLinked'] ? ' linked' : '') . '" data-linked="' . ($value['isLinked'] ? 'true' : 'false') . '"><span class="dashicons dashicons-admin-links"></span></button>';
                $html .= '<input type="hidden" name="' . esc_attr($name) . '[isLinked]" value="' . ($value['isLinked'] ? '1' : '0') . '" />';
                $html .= '<select name="' . esc_attr($name) . '[unit]" class="tbp-dimensions-unit">';
                foreach ($size_units as $u) {
                    $html .= '<option value="' . esc_attr($u) . '"' . selected($value['unit'], $u, false) . '>' . esc_html($u) . '</option>';
                }
                $html .= '</select>';
                $html .= '</div>';
                return $html;

            case self::CHOOSE:
                $options = $control['options'] ?? [];
                $toggle = $control['toggle'] ?? true;
                $html = '<div class="tbp-control-choose" data-toggle="' . ($toggle ? 'true' : 'false') . '">';
                foreach ($options as $key => $option) {
                    $checked = $value === $key ? 'checked' : '';
                    $html .= '<label class="tbp-choose-option">';
                    $html .= '<input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($key) . '" ' . $checked . ' />';
                    $html .= '<span class="tbp-choose-label" title="' . esc_attr($option['title'] ?? '') . '">';
                    $html .= '<span class="' . esc_attr($option['icon'] ?? '') . '"></span>';
                    $html .= '</span>';
                    $html .= '</label>';
                }
                $html .= '</div>';
                return $html;

            case self::HEADING:
                return '<h3 class="tbp-control-heading">' . esc_html($control['label'] ?? '') . '</h3>';

            case self::DIVIDER:
                return '<hr class="tbp-control-divider" />';

            case self::RAW_HTML:
                return $control['raw'] ?? '';

            default:
                return '<input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="tbp-control-text" />';
        }
    }

    /**
     * Get Font Weight Options
     */
    public static function get_font_weight_options() {
        return [
            '100' => __('100 (Thin)', 'theme-builder-pro'),
            '200' => __('200 (Extra Light)', 'theme-builder-pro'),
            '300' => __('300 (Light)', 'theme-builder-pro'),
            '400' => __('400 (Normal)', 'theme-builder-pro'),
            '500' => __('500 (Medium)', 'theme-builder-pro'),
            '600' => __('600 (Semi Bold)', 'theme-builder-pro'),
            '700' => __('700 (Bold)', 'theme-builder-pro'),
            '800' => __('800 (Extra Bold)', 'theme-builder-pro'),
            '900' => __('900 (Black)', 'theme-builder-pro'),
        ];
    }

    /**
     * Get Text Transform Options
     */
    public static function get_text_transform_options() {
        return [
            '' => __('Default', 'theme-builder-pro'),
            'uppercase' => __('Uppercase', 'theme-builder-pro'),
            'lowercase' => __('Lowercase', 'theme-builder-pro'),
            'capitalize' => __('Capitalize', 'theme-builder-pro'),
            'none' => __('None', 'theme-builder-pro'),
        ];
    }

    /**
     * Get Animation Options
     */
    public static function get_animation_options() {
        return [
            '' => __('None', 'theme-builder-pro'),
            'Fading' => [
                'fadeIn' => 'Fade In',
                'fadeInUp' => 'Fade In Up',
                'fadeInDown' => 'Fade In Down',
                'fadeInLeft' => 'Fade In Left',
                'fadeInRight' => 'Fade In Right',
            ],
            'Zooming' => [
                'zoomIn' => 'Zoom In',
                'zoomInUp' => 'Zoom In Up',
                'zoomInDown' => 'Zoom In Down',
                'zoomInLeft' => 'Zoom In Left',
                'zoomInRight' => 'Zoom In Right',
            ],
            'Bouncing' => [
                'bounceIn' => 'Bounce In',
                'bounceInUp' => 'Bounce In Up',
                'bounceInDown' => 'Bounce In Down',
                'bounceInLeft' => 'Bounce In Left',
                'bounceInRight' => 'Bounce In Right',
            ],
            'Sliding' => [
                'slideInUp' => 'Slide In Up',
                'slideInDown' => 'Slide In Down',
                'slideInLeft' => 'Slide In Left',
                'slideInRight' => 'Slide In Right',
            ],
            'Rotating' => [
                'rotateIn' => 'Rotate In',
                'rotateInUpLeft' => 'Rotate In Up Left',
                'rotateInUpRight' => 'Rotate In Up Right',
                'rotateInDownLeft' => 'Rotate In Down Left',
                'rotateInDownRight' => 'Rotate In Down Right',
            ],
            'Attention Seekers' => [
                'bounce' => 'Bounce',
                'flash' => 'Flash',
                'pulse' => 'Pulse',
                'rubberBand' => 'Rubber Band',
                'shake' => 'Shake',
                'swing' => 'Swing',
                'tada' => 'Tada',
                'wobble' => 'Wobble',
                'jello' => 'Jello',
            ],
        ];
    }

    /**
     * Get Hover Animation Options
     */
    public static function get_hover_animation_options() {
        return [
            '' => __('None', 'theme-builder-pro'),
            'grow' => 'Grow',
            'shrink' => 'Shrink',
            'pulse' => 'Pulse',
            'pulse-grow' => 'Pulse Grow',
            'pulse-shrink' => 'Pulse Shrink',
            'push' => 'Push',
            'pop' => 'Pop',
            'bounce-in' => 'Bounce In',
            'bounce-out' => 'Bounce Out',
            'rotate' => 'Rotate',
            'grow-rotate' => 'Grow Rotate',
            'float' => 'Float',
            'sink' => 'Sink',
            'bob' => 'Bob',
            'hang' => 'Hang',
            'skew' => 'Skew',
            'skew-forward' => 'Skew Forward',
            'skew-backward' => 'Skew Backward',
            'wobble-horizontal' => 'Wobble Horizontal',
            'wobble-vertical' => 'Wobble Vertical',
            'wobble-to-bottom-right' => 'Wobble To Bottom Right',
            'wobble-to-top-right' => 'Wobble To Top Right',
            'wobble-top' => 'Wobble Top',
            'wobble-bottom' => 'Wobble Bottom',
            'wobble-skew' => 'Wobble Skew',
            'buzz' => 'Buzz',
            'buzz-out' => 'Buzz Out',
        ];
    }
}
