<?php
/**
 * Heading Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Heading extends TBP_Widget_Base {

    protected function register_content_controls() {
        $this->content_controls = [
            'content_section' => [
                'type' => 'section',
                'label' => __('Content', 'theme-builder-pro'),
                'controls' => [
                    'title' => [
                        'type' => 'textarea',
                        'label' => __('Title', 'theme-builder-pro'),
                        'default' => __('Add Your Heading Text Here', 'theme-builder-pro'),
                        'placeholder' => __('Enter your title', 'theme-builder-pro'),
                        'dynamic' => true,
                    ],
                    'link' => [
                        'type' => 'url',
                        'label' => __('Link', 'theme-builder-pro'),
                        'dynamic' => true,
                    ],
                    'header_size' => [
                        'type' => 'select',
                        'label' => __('HTML Tag', 'theme-builder-pro'),
                        'options' => [
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6',
                            'div' => 'div',
                            'span' => 'span',
                            'p' => 'p',
                        ],
                        'default' => 'h2',
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
                        'selectors' => [
                            '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function register_style_controls() {
        $this->style_controls = [
            'title_style_section' => [
                'type' => 'section',
                'label' => __('Title', 'theme-builder-pro'),
                'controls' => [
                    'title_color' => [
                        'type' => 'color',
                        'label' => __('Text Color', 'theme-builder-pro'),
                        'global' => true,
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'color: {{VALUE}};',
                        ],
                    ],
                    'typography_font_family' => [
                        'type' => 'font',
                        'label' => __('Font Family', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'font-family: "{{VALUE}}", sans-serif;',
                        ],
                    ],
                    'typography_font_size' => [
                        'type' => 'slider',
                        'label' => __('Font Size', 'theme-builder-pro'),
                        'size_units' => ['px', 'em', 'rem', 'vw'],
                        'range' => [
                            'px' => [
                                'min' => 1,
                                'max' => 200,
                            ],
                            'vw' => [
                                'min' => 0.1,
                                'max' => 10,
                                'step' => 0.1,
                            ],
                        ],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'font-size: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'typography_font_weight' => [
                        'type' => 'select',
                        'label' => __('Font Weight', 'theme-builder-pro'),
                        'options' => TBP_Controls_Manager::get_font_weight_options(),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'font-weight: {{VALUE}};',
                        ],
                    ],
                    'typography_text_transform' => [
                        'type' => 'select',
                        'label' => __('Text Transform', 'theme-builder-pro'),
                        'options' => TBP_Controls_Manager::get_text_transform_options(),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'text-transform: {{VALUE}};',
                        ],
                    ],
                    'typography_line_height' => [
                        'type' => 'slider',
                        'label' => __('Line Height', 'theme-builder-pro'),
                        'size_units' => ['px', 'em'],
                        'range' => [
                            'px' => [
                                'min' => 1,
                                'max' => 200,
                            ],
                            'em' => [
                                'min' => 0.1,
                                'max' => 10,
                                'step' => 0.1,
                            ],
                        ],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'line-height: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'typography_letter_spacing' => [
                        'type' => 'slider',
                        'label' => __('Letter Spacing', 'theme-builder-pro'),
                        'size_units' => ['px', 'em'],
                        'range' => [
                            'px' => [
                                'min' => -5,
                                'max' => 20,
                            ],
                            'em' => [
                                'min' => -0.5,
                                'max' => 2,
                                'step' => 0.1,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'letter-spacing: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'text_stroke_width' => [
                        'type' => 'slider',
                        'label' => __('Text Stroke Width', 'theme-builder-pro'),
                        'size_units' => ['px'],
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 10,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'text_stroke_color' => [
                        'type' => 'color',
                        'label' => __('Text Stroke Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => '-webkit-text-stroke-color: {{VALUE}};',
                        ],
                        'condition' => [
                            'text_stroke_width[size]!' => '',
                        ],
                    ],
                    'text_shadow' => [
                        'type' => 'text_shadow',
                        'label' => __('Text Shadow', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'text-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{COLOR}};',
                        ],
                    ],
                    'blend_mode' => [
                        'type' => 'select',
                        'label' => __('Blend Mode', 'theme-builder-pro'),
                        'options' => [
                            '' => __('Normal', 'theme-builder-pro'),
                            'multiply' => 'Multiply',
                            'screen' => 'Screen',
                            'overlay' => 'Overlay',
                            'darken' => 'Darken',
                            'lighten' => 'Lighten',
                            'color-dodge' => 'Color Dodge',
                            'saturation' => 'Saturation',
                            'color' => 'Color',
                            'difference' => 'Difference',
                            'exclusion' => 'Exclusion',
                            'hue' => 'Hue',
                            'luminosity' => 'Luminosity',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .tbp-heading-title' => 'mix-blend-mode: {{VALUE}};',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function render($settings) {
        $title = $settings['title'] ?? '';
        $tag = $settings['header_size'] ?? 'h2';
        $link = $settings['link'] ?? [];

        if (empty($title)) {
            return '';
        }

        // Parse dynamic content
        $title = TBP_Utils::parse_dynamic_content($title);

        $html = '';

        if (!empty($link['url'])) {
            $target = !empty($link['is_external']) ? ' target="_blank"' : '';
            $nofollow = !empty($link['nofollow']) ? ' rel="nofollow"' : '';
            $html .= '<a href="' . esc_url($link['url']) . '"' . $target . $nofollow . '>';
        }

        $html .= sprintf(
            '<%1$s class="tbp-heading-title">%2$s</%1$s>',
            esc_attr($tag),
            wp_kses_post($title)
        );

        if (!empty($link['url'])) {
            $html .= '</a>';
        }

        return $html;
    }

    protected function content_template() {
        ?>
        <#
        var title = settings.title;
        var headerSize = settings.header_size || 'h2';
        var link = settings.link || {};

        if (title) {
            if (link.url) {
                #>
                <a href="{{ link.url }}">
                <#
            }
            #>
            <{{{ headerSize }}} class="tbp-heading-title">{{{ title }}}</{{{ headerSize }}}>
            <#
            if (link.url) {
                #>
                </a>
                <#
            }
        }
        #>
        <?php
    }
}
