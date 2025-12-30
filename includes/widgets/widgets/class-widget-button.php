<?php
/**
 * Button Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Button extends TBP_Widget_Base {

    protected function register_content_controls() {
        $this->content_controls = [
            'button_section' => [
                'type' => 'section',
                'label' => __('Button', 'theme-builder-pro'),
                'controls' => [
                    'button_type' => [
                        'type' => 'select',
                        'label' => __('Type', 'theme-builder-pro'),
                        'default' => '',
                        'options' => [
                            '' => __('Default', 'theme-builder-pro'),
                            'info' => __('Info', 'theme-builder-pro'),
                            'success' => __('Success', 'theme-builder-pro'),
                            'warning' => __('Warning', 'theme-builder-pro'),
                            'danger' => __('Danger', 'theme-builder-pro'),
                        ],
                    ],
                    'text' => [
                        'type' => 'text',
                        'label' => __('Text', 'theme-builder-pro'),
                        'default' => __('Click Here', 'theme-builder-pro'),
                        'dynamic' => true,
                    ],
                    'link' => [
                        'type' => 'url',
                        'label' => __('Link', 'theme-builder-pro'),
                        'default' => [
                            'url' => '#',
                        ],
                        'dynamic' => true,
                    ],
                    'size' => [
                        'type' => 'select',
                        'label' => __('Size', 'theme-builder-pro'),
                        'default' => 'md',
                        'options' => [
                            'xs' => __('Extra Small', 'theme-builder-pro'),
                            'sm' => __('Small', 'theme-builder-pro'),
                            'md' => __('Medium', 'theme-builder-pro'),
                            'lg' => __('Large', 'theme-builder-pro'),
                            'xl' => __('Extra Large', 'theme-builder-pro'),
                        ],
                    ],
                    'icon' => [
                        'type' => 'icon',
                        'label' => __('Icon', 'theme-builder-pro'),
                    ],
                    'icon_position' => [
                        'type' => 'choose',
                        'label' => __('Icon Position', 'theme-builder-pro'),
                        'default' => 'left',
                        'options' => [
                            'left' => [
                                'title' => __('Left', 'theme-builder-pro'),
                                'icon' => 'eicon-h-align-left',
                            ],
                            'right' => [
                                'title' => __('Right', 'theme-builder-pro'),
                                'icon' => 'eicon-h-align-right',
                            ],
                        ],
                        'condition' => [
                            'icon[value]!' => '',
                        ],
                    ],
                    'icon_spacing' => [
                        'type' => 'slider',
                        'label' => __('Icon Spacing', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 50,
                            ],
                        ],
                        'default' => [
                            'size' => 8,
                            'unit' => 'px',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button-icon-left .tbp-button-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                            '{{WRAPPER}} .tbp-button-icon-right .tbp-button-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
                        ],
                        'condition' => [
                            'icon[value]!' => '',
                        ],
                    ],
                    'align' => [
                        'type' => 'choose',
                        'label' => __('Alignment', 'theme-builder-pro'),
                        'options' => [
                            'left' => [
                                'title' => __('Left', 'theme-builder-pro'),
                                'icon' => 'eicon-text-align-left',
                            ],
                            'center' => [
                                'title' => __('Center', 'theme-builder-pro'),
                                'icon' => 'eicon-text-align-center',
                            ],
                            'right' => [
                                'title' => __('Right', 'theme-builder-pro'),
                                'icon' => 'eicon-text-align-right',
                            ],
                            'justify' => [
                                'title' => __('Justify', 'theme-builder-pro'),
                                'icon' => 'eicon-text-align-justify',
                            ],
                        ],
                        'default' => '',
                        'responsive' => true,
                    ],
                    'button_id' => [
                        'type' => 'text',
                        'label' => __('Button ID', 'theme-builder-pro'),
                        'description' => __('Add a custom ID for the button.', 'theme-builder-pro'),
                    ],
                ],
            ],
        ];
    }

    protected function register_style_controls() {
        $this->style_controls = [
            'button_style_section' => [
                'type' => 'section',
                'label' => __('Button', 'theme-builder-pro'),
                'controls' => [
                    'typography_font_family' => [
                        'type' => 'font',
                        'label' => __('Font Family', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'font-family: "{{VALUE}}", sans-serif;',
                        ],
                    ],
                    'typography_font_size' => [
                        'type' => 'slider',
                        'label' => __('Font Size', 'theme-builder-pro'),
                        'size_units' => ['px', 'em', 'rem'],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'font-size: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'typography_font_weight' => [
                        'type' => 'select',
                        'label' => __('Font Weight', 'theme-builder-pro'),
                        'options' => TBP_Controls_Manager::get_font_weight_options(),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'font-weight: {{VALUE}};',
                        ],
                    ],
                    'text_color' => [
                        'type' => 'color',
                        'label' => __('Text Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'color: {{VALUE}};',
                        ],
                    ],
                    'background_color' => [
                        'type' => 'color',
                        'label' => __('Background Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'background-color: {{VALUE}};',
                        ],
                    ],
                    'border_type' => [
                        'type' => 'select',
                        'label' => __('Border Type', 'theme-builder-pro'),
                        'options' => [
                            '' => __('None', 'theme-builder-pro'),
                            'solid' => __('Solid', 'theme-builder-pro'),
                            'dashed' => __('Dashed', 'theme-builder-pro'),
                            'dotted' => __('Dotted', 'theme-builder-pro'),
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'border-style: {{VALUE}};',
                        ],
                    ],
                    'border_width' => [
                        'type' => 'dimensions',
                        'label' => __('Border Width', 'theme-builder-pro'),
                        'size_units' => ['px'],
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                        'condition' => [
                            'border_type!' => '',
                        ],
                    ],
                    'border_color' => [
                        'type' => 'color',
                        'label' => __('Border Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'border-color: {{VALUE}};',
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
                            '{{WRAPPER}} .tbp-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ],
                    'box_shadow' => [
                        'type' => 'box_shadow',
                        'label' => __('Box Shadow', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}};',
                        ],
                    ],
                    'padding' => [
                        'type' => 'dimensions',
                        'label' => __('Padding', 'theme-builder-pro'),
                        'size_units' => ['px', 'em', '%'],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ],
                ],
            ],
            'hover_section' => [
                'type' => 'section',
                'label' => __('Hover', 'theme-builder-pro'),
                'controls' => [
                    'hover_text_color' => [
                        'type' => 'color',
                        'label' => __('Text Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button:hover' => 'color: {{VALUE}};',
                        ],
                    ],
                    'hover_background_color' => [
                        'type' => 'color',
                        'label' => __('Background Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button:hover' => 'background-color: {{VALUE}};',
                        ],
                    ],
                    'hover_border_color' => [
                        'type' => 'color',
                        'label' => __('Border Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button:hover' => 'border-color: {{VALUE}};',
                        ],
                    ],
                    'hover_animation' => [
                        'type' => 'select',
                        'label' => __('Hover Animation', 'theme-builder-pro'),
                        'options' => TBP_Controls_Manager::get_hover_animation_options(),
                    ],
                    'transition_duration' => [
                        'type' => 'slider',
                        'label' => __('Transition Duration', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 1000,
                                'step' => 50,
                            ],
                        ],
                        'default' => [
                            'size' => 300,
                            'unit' => 'px',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .tbp-button' => 'transition-duration: {{SIZE}}ms;',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function render($settings) {
        $text = $settings['text'] ?? __('Click Here', 'theme-builder-pro');
        $link = $settings['link'] ?? ['url' => '#'];
        $size = $settings['size'] ?? 'md';
        $button_type = $settings['button_type'] ?? '';
        $icon = $settings['icon'] ?? [];
        $icon_position = $settings['icon_position'] ?? 'left';
        $button_id = $settings['button_id'] ?? '';
        $align = $settings['align'] ?? '';
        $hover_animation = $settings['hover_animation'] ?? '';

        $classes = ['tbp-button', 'tbp-button-' . $size];

        if ($button_type) {
            $classes[] = 'tbp-button-' . $button_type;
        }

        if (!empty($icon['value'])) {
            $classes[] = 'tbp-button-icon-' . $icon_position;
        }

        if ($hover_animation) {
            $classes[] = 'tbp-hover-' . $hover_animation;
        }

        $wrapper_classes = ['tbp-button-wrapper'];
        if ($align) {
            $wrapper_classes[] = 'tbp-align-' . $align;
        }

        $target = !empty($link['is_external']) ? ' target="_blank"' : '';
        $nofollow = !empty($link['nofollow']) ? ' rel="nofollow"' : '';
        $id_attr = $button_id ? ' id="' . esc_attr($button_id) . '"' : '';

        $html = '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '">';
        $html .= '<a href="' . esc_url($link['url'] ?? '#') . '" class="' . esc_attr(implode(' ', $classes)) . '"' . $target . $nofollow . $id_attr . '>';

        if (!empty($icon['value']) && $icon_position === 'left') {
            $html .= '<span class="tbp-button-icon"><i class="' . esc_attr($icon['value']) . '"></i></span>';
        }

        $html .= '<span class="tbp-button-text">' . esc_html($text) . '</span>';

        if (!empty($icon['value']) && $icon_position === 'right') {
            $html .= '<span class="tbp-button-icon"><i class="' . esc_attr($icon['value']) . '"></i></span>';
        }

        $html .= '</a>';
        $html .= '</div>';

        return $html;
    }
}
