<?php
/**
 * Widget Base Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Base {

    protected $name;
    protected $config;
    protected $controls = [];
    protected $content_controls = [];
    protected $style_controls = [];
    protected $advanced_controls = [];

    /**
     * Constructor
     */
    public function __construct($name, $config = []) {
        $this->name = $name;
        $this->config = $config;

        $this->register_controls();
    }

    /**
     * Get Name
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get Title
     */
    public function get_title() {
        return $this->config['title'] ?? $this->name;
    }

    /**
     * Get Icon
     */
    public function get_icon() {
        return $this->config['icon'] ?? 'eicon-widget';
    }

    /**
     * Get Categories
     */
    public function get_categories() {
        return $this->config['categories'] ?? ['basic'];
    }

    /**
     * Get Keywords
     */
    public function get_keywords() {
        return $this->config['keywords'] ?? [];
    }

    /**
     * Register Controls
     */
    protected function register_controls() {
        // Content Controls
        $this->register_content_controls();

        // Style Controls
        $this->register_style_controls();

        // Advanced Controls
        $this->register_advanced_controls();
    }

    /**
     * Register Content Controls
     */
    protected function register_content_controls() {
        // Override in child classes
        $this->content_controls = $this->get_default_content_controls();
    }

    /**
     * Register Style Controls
     */
    protected function register_style_controls() {
        // Override in child classes
        $this->style_controls = $this->get_default_style_controls();
    }

    /**
     * Register Advanced Controls
     */
    protected function register_advanced_controls() {
        $this->advanced_controls = [
            // Layout Section
            'layout_section' => [
                'type' => 'section',
                'label' => __('Layout', 'theme-builder-pro'),
                'tab' => 'advanced',
                'controls' => [
                    'margin' => [
                        'type' => 'dimensions',
                        'label' => __('Margin', 'theme-builder-pro'),
                        'responsive' => true,
                        'allowed_dimensions' => ['top', 'right', 'bottom', 'left'],
                        'size_units' => ['px', 'em', '%'],
                        'selectors' => [
                            '{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ],
                    'padding' => [
                        'type' => 'dimensions',
                        'label' => __('Padding', 'theme-builder-pro'),
                        'responsive' => true,
                        'allowed_dimensions' => ['top', 'right', 'bottom', 'left'],
                        'size_units' => ['px', 'em', '%'],
                        'selectors' => [
                            '{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ],
                    'z_index' => [
                        'type' => 'number',
                        'label' => __('Z-Index', 'theme-builder-pro'),
                        'min' => -9999,
                        'max' => 9999,
                        'selectors' => [
                            '{{WRAPPER}}' => 'z-index: {{VALUE}};',
                        ],
                    ],
                    'css_id' => [
                        'type' => 'text',
                        'label' => __('CSS ID', 'theme-builder-pro'),
                    ],
                    'css_classes' => [
                        'type' => 'text',
                        'label' => __('CSS Classes', 'theme-builder-pro'),
                    ],
                ],
            ],

            // Motion Effects Section
            'motion_effects_section' => [
                'type' => 'section',
                'label' => __('Motion Effects', 'theme-builder-pro'),
                'tab' => 'advanced',
                'controls' => [
                    'entrance_animation' => [
                        'type' => 'select',
                        'label' => __('Entrance Animation', 'theme-builder-pro'),
                        'options' => TBP_Controls_Manager::get_animation_options(),
                    ],
                    'animation_duration' => [
                        'type' => 'select',
                        'label' => __('Animation Duration', 'theme-builder-pro'),
                        'default' => 'normal',
                        'options' => [
                            'slow' => __('Slow', 'theme-builder-pro'),
                            'normal' => __('Normal', 'theme-builder-pro'),
                            'fast' => __('Fast', 'theme-builder-pro'),
                        ],
                        'condition' => [
                            'entrance_animation!' => '',
                        ],
                    ],
                    'animation_delay' => [
                        'type' => 'number',
                        'label' => __('Animation Delay (ms)', 'theme-builder-pro'),
                        'min' => 0,
                        'max' => 5000,
                        'step' => 100,
                        'condition' => [
                            'entrance_animation!' => '',
                        ],
                    ],
                    'hover_animation' => [
                        'type' => 'select',
                        'label' => __('Hover Animation', 'theme-builder-pro'),
                        'options' => TBP_Controls_Manager::get_hover_animation_options(),
                    ],
                ],
            ],

            // Transform Section
            'transform_section' => [
                'type' => 'section',
                'label' => __('Transform', 'theme-builder-pro'),
                'tab' => 'advanced',
                'controls' => [
                    'transform_rotate' => [
                        'type' => 'slider',
                        'label' => __('Rotate', 'theme-builder-pro'),
                        'size_units' => ['deg'],
                        'range' => [
                            'deg' => [
                                'min' => -360,
                                'max' => 360,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}}' => 'transform: rotate({{SIZE}}{{UNIT}});',
                        ],
                    ],
                    'transform_scale' => [
                        'type' => 'slider',
                        'label' => __('Scale', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 2,
                                'step' => 0.1,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}}' => 'transform: scale({{SIZE}});',
                        ],
                    ],
                    'transform_translate_x' => [
                        'type' => 'slider',
                        'label' => __('Offset X', 'theme-builder-pro'),
                        'size_units' => ['px', '%'],
                        'range' => [
                            'px' => [
                                'min' => -500,
                                'max' => 500,
                            ],
                            '%' => [
                                'min' => -100,
                                'max' => 100,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}}' => 'transform: translateX({{SIZE}}{{UNIT}});',
                        ],
                    ],
                    'transform_translate_y' => [
                        'type' => 'slider',
                        'label' => __('Offset Y', 'theme-builder-pro'),
                        'size_units' => ['px', '%'],
                        'range' => [
                            'px' => [
                                'min' => -500,
                                'max' => 500,
                            ],
                            '%' => [
                                'min' => -100,
                                'max' => 100,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}}' => 'transform: translateY({{SIZE}}{{UNIT}});',
                        ],
                    ],
                ],
            ],

            // Background Section
            'background_section' => [
                'type' => 'section',
                'label' => __('Background', 'theme-builder-pro'),
                'tab' => 'advanced',
                'controls' => [
                    'background_type' => [
                        'type' => 'choose',
                        'label' => __('Type', 'theme-builder-pro'),
                        'options' => [
                            'classic' => [
                                'title' => __('Classic', 'theme-builder-pro'),
                                'icon' => 'eicon-paint-brush',
                            ],
                            'gradient' => [
                                'title' => __('Gradient', 'theme-builder-pro'),
                                'icon' => 'eicon-barcode',
                            ],
                        ],
                        'default' => 'classic',
                    ],
                    'background_color' => [
                        'type' => 'color',
                        'label' => __('Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}}' => 'background-color: {{VALUE}};',
                        ],
                        'condition' => [
                            'background_type' => 'classic',
                        ],
                    ],
                    'background_image' => [
                        'type' => 'media',
                        'label' => __('Image', 'theme-builder-pro'),
                        'media_types' => ['image'],
                        'condition' => [
                            'background_type' => 'classic',
                        ],
                    ],
                    'background_size' => [
                        'type' => 'select',
                        'label' => __('Size', 'theme-builder-pro'),
                        'default' => 'cover',
                        'options' => [
                            'auto' => __('Auto', 'theme-builder-pro'),
                            'cover' => __('Cover', 'theme-builder-pro'),
                            'contain' => __('Contain', 'theme-builder-pro'),
                        ],
                        'condition' => [
                            'background_type' => 'classic',
                            'background_image[url]!' => '',
                        ],
                    ],
                    'gradient_color1' => [
                        'type' => 'color',
                        'label' => __('First Color', 'theme-builder-pro'),
                        'default' => '#ffffff',
                        'condition' => [
                            'background_type' => 'gradient',
                        ],
                    ],
                    'gradient_color2' => [
                        'type' => 'color',
                        'label' => __('Second Color', 'theme-builder-pro'),
                        'default' => '#000000',
                        'condition' => [
                            'background_type' => 'gradient',
                        ],
                    ],
                    'gradient_angle' => [
                        'type' => 'slider',
                        'label' => __('Angle', 'theme-builder-pro'),
                        'default' => [
                            'size' => 180,
                            'unit' => 'deg',
                        ],
                        'range' => [
                            'deg' => [
                                'min' => 0,
                                'max' => 360,
                            ],
                        ],
                        'condition' => [
                            'background_type' => 'gradient',
                        ],
                    ],
                ],
            ],

            // Border Section
            'border_section' => [
                'type' => 'section',
                'label' => __('Border', 'theme-builder-pro'),
                'tab' => 'advanced',
                'controls' => [
                    'border_type' => [
                        'type' => 'select',
                        'label' => __('Border Type', 'theme-builder-pro'),
                        'options' => [
                            '' => __('None', 'theme-builder-pro'),
                            'solid' => __('Solid', 'theme-builder-pro'),
                            'dashed' => __('Dashed', 'theme-builder-pro'),
                            'dotted' => __('Dotted', 'theme-builder-pro'),
                            'double' => __('Double', 'theme-builder-pro'),
                            'groove' => __('Groove', 'theme-builder-pro'),
                        ],
                        'selectors' => [
                            '{{WRAPPER}}' => 'border-style: {{VALUE}};',
                        ],
                    ],
                    'border_width' => [
                        'type' => 'dimensions',
                        'label' => __('Border Width', 'theme-builder-pro'),
                        'size_units' => ['px'],
                        'selectors' => [
                            '{{WRAPPER}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                        'condition' => [
                            'border_type!' => '',
                        ],
                    ],
                    'border_color' => [
                        'type' => 'color',
                        'label' => __('Border Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}}' => 'border-color: {{VALUE}};',
                        ],
                        'condition' => [
                            'border_type!' => '',
                        ],
                    ],
                    'border_radius' => [
                        'type' => 'dimensions',
                        'label' => __('Border Radius', 'theme-builder-pro'),
                        'size_units' => ['px', '%'],
                        'selectors' => [
                            '{{WRAPPER}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ],
                    'box_shadow' => [
                        'type' => 'box_shadow',
                        'label' => __('Box Shadow', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}}' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}} {{POSITION}};',
                        ],
                    ],
                ],
            ],

            // Responsive Section
            'responsive_section' => [
                'type' => 'section',
                'label' => __('Responsive', 'theme-builder-pro'),
                'tab' => 'advanced',
                'controls' => [
                    'hide_desktop' => [
                        'type' => 'switcher',
                        'label' => __('Hide on Desktop', 'theme-builder-pro'),
                        'return_value' => 'yes',
                    ],
                    'hide_tablet' => [
                        'type' => 'switcher',
                        'label' => __('Hide on Tablet', 'theme-builder-pro'),
                        'return_value' => 'yes',
                    ],
                    'hide_mobile' => [
                        'type' => 'switcher',
                        'label' => __('Hide on Mobile', 'theme-builder-pro'),
                        'return_value' => 'yes',
                    ],
                ],
            ],

            // Custom CSS Section
            'custom_css_section' => [
                'type' => 'section',
                'label' => __('Custom CSS', 'theme-builder-pro'),
                'tab' => 'advanced',
                'controls' => [
                    'custom_css' => [
                        'type' => 'code',
                        'label' => __('Custom CSS', 'theme-builder-pro'),
                        'language' => 'css',
                        'rows' => 10,
                        'description' => __('Use "selector" to target this element.', 'theme-builder-pro'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get Default Content Controls
     */
    protected function get_default_content_controls() {
        return [];
    }

    /**
     * Get Default Style Controls
     */
    protected function get_default_style_controls() {
        return [];
    }

    /**
     * Get Controls
     */
    public function get_controls() {
        return [
            'content' => $this->content_controls,
            'style' => $this->style_controls,
            'advanced' => $this->advanced_controls,
        ];
    }

    /**
     * Get Default Settings
     */
    public function get_default_settings() {
        $defaults = [];

        foreach ($this->get_controls() as $tab => $sections) {
            foreach ($sections as $section_id => $section) {
                if (isset($section['controls'])) {
                    foreach ($section['controls'] as $control_id => $control) {
                        if (isset($control['default'])) {
                            $defaults[$control_id] = $control['default'];
                        }
                    }
                }
            }
        }

        return $defaults;
    }

    /**
     * Render Element
     */
    public function render_element($element) {
        $id = $element['id'];
        $settings = array_merge($this->get_default_settings(), $element['settings'] ?? []);

        $classes = ['tbp-widget', 'tbp-widget-' . $this->name, 'tbp-element', 'tbp-element-' . $id];

        // Add responsive visibility classes
        if (!empty($settings['hide_desktop'])) {
            $classes[] = 'tbp-hidden-desktop';
        }
        if (!empty($settings['hide_tablet'])) {
            $classes[] = 'tbp-hidden-tablet';
        }
        if (!empty($settings['hide_mobile'])) {
            $classes[] = 'tbp-hidden-mobile';
        }

        // Add animation classes
        if (!empty($settings['entrance_animation'])) {
            $classes[] = 'tbp-animation';
            $classes[] = 'tbp-animation-' . $settings['entrance_animation'];
        }

        if (!empty($settings['hover_animation'])) {
            $classes[] = 'tbp-hover-' . $settings['hover_animation'];
        }

        // Add custom classes
        if (!empty($settings['css_classes'])) {
            $classes[] = $settings['css_classes'];
        }

        $attributes = [
            'class' => implode(' ', $classes),
            'data-id' => $id,
            'data-widget' => $this->name,
        ];

        // Add custom ID
        if (!empty($settings['css_id'])) {
            $attributes['id'] = $settings['css_id'];
        }

        // Animation data attributes
        if (!empty($settings['animation_duration'])) {
            $attributes['data-animation-duration'] = $settings['animation_duration'];
        }
        if (!empty($settings['animation_delay'])) {
            $attributes['data-animation-delay'] = $settings['animation_delay'];
        }

        $html = '<div' . TBP_Utils::render_attributes($attributes) . '>';
        $html .= '<div class="tbp-widget-container">';
        $html .= $this->render($settings);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render Widget
     */
    public function render($settings) {
        // Override in child classes
        return '';
    }

    /**
     * Render Editor Template
     */
    public function render_editor_template() {
        ob_start();
        ?>
        <#
        var settings = data.settings;
        #>
        <div class="tbp-widget-container">
            <?php $this->content_template(); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Content Template (for editor)
     */
    protected function content_template() {
        // Override in child classes
    }

    /**
     * Get Element CSS
     */
    public function get_element_css($id, $settings) {
        $css = '';
        $selector = '.tbp-element-' . $id;

        $controls = $this->get_controls();

        foreach ($controls as $tab => $sections) {
            foreach ($sections as $section_id => $section) {
                if (isset($section['controls'])) {
                    foreach ($section['controls'] as $control_id => $control) {
                        if (isset($control['selectors']) && isset($settings[$control_id])) {
                            $value = $settings[$control_id];

                            foreach ($control['selectors'] as $control_selector => $css_property) {
                                $full_selector = str_replace('{{WRAPPER}}', $selector, $control_selector);
                                $css_value = $this->parse_selector_value($css_property, $value, $control);

                                if ($css_value) {
                                    $css .= $full_selector . ' {' . $css_value . '}';
                                }
                            }
                        }
                    }
                }
            }
        }

        return $css;
    }

    /**
     * Parse Selector Value
     */
    private function parse_selector_value($template, $value, $control) {
        if (empty($value)) {
            return '';
        }

        // Handle dimensions
        if (is_array($value) && isset($value['top'])) {
            $unit = $value['unit'] ?? 'px';
            $template = str_replace('{{TOP}}', $value['top'], $template);
            $template = str_replace('{{RIGHT}}', $value['right'], $template);
            $template = str_replace('{{BOTTOM}}', $value['bottom'], $template);
            $template = str_replace('{{LEFT}}', $value['left'], $template);
            $template = str_replace('{{UNIT}}', $unit, $template);
        }
        // Handle slider
        elseif (is_array($value) && isset($value['size'])) {
            $unit = $value['unit'] ?? 'px';
            $template = str_replace('{{SIZE}}', $value['size'], $template);
            $template = str_replace('{{UNIT}}', $unit, $template);
        }
        // Handle box shadow
        elseif (is_array($value) && isset($value['horizontal'])) {
            $template = str_replace('{{HORIZONTAL}}', $value['horizontal'] ?? 0, $template);
            $template = str_replace('{{VERTICAL}}', $value['vertical'] ?? 0, $template);
            $template = str_replace('{{BLUR}}', $value['blur'] ?? 10, $template);
            $template = str_replace('{{SPREAD}}', $value['spread'] ?? 0, $template);
            $template = str_replace('{{COLOR}}', $value['color'] ?? 'rgba(0,0,0,0.5)', $template);
            $template = str_replace('{{POSITION}}', ($value['position'] ?? '') === 'inset' ? 'inset' : '', $template);
        }
        // Handle simple value
        else {
            $template = str_replace('{{VALUE}}', $value, $template);
        }

        return $template;
    }

    /**
     * Add Control
     */
    protected function add_control($id, $args, $tab = 'content', $section = 'default') {
        if ($tab === 'content') {
            if (!isset($this->content_controls[$section])) {
                $this->content_controls[$section] = ['type' => 'section', 'controls' => []];
            }
            $this->content_controls[$section]['controls'][$id] = $args;
        } elseif ($tab === 'style') {
            if (!isset($this->style_controls[$section])) {
                $this->style_controls[$section] = ['type' => 'section', 'controls' => []];
            }
            $this->style_controls[$section]['controls'][$id] = $args;
        } elseif ($tab === 'advanced') {
            if (!isset($this->advanced_controls[$section])) {
                $this->advanced_controls[$section] = ['type' => 'section', 'controls' => []];
            }
            $this->advanced_controls[$section]['controls'][$id] = $args;
        }
    }

    /**
     * Add Section
     */
    protected function add_section($id, $args, $tab = 'content') {
        $section = array_merge(['type' => 'section', 'controls' => []], $args);

        if ($tab === 'content') {
            $this->content_controls[$id] = $section;
        } elseif ($tab === 'style') {
            $this->style_controls[$id] = $section;
        } elseif ($tab === 'advanced') {
            $this->advanced_controls[$id] = $section;
        }
    }
}
