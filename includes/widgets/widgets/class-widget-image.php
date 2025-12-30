<?php
/**
 * Image Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Image extends TBP_Widget_Base {

    protected function register_content_controls() {
        $this->content_controls = [
            'image_section' => [
                'type' => 'section',
                'label' => __('Image', 'theme-builder-pro'),
                'controls' => [
                    'image' => [
                        'type' => 'media',
                        'label' => __('Choose Image', 'theme-builder-pro'),
                        'default' => [
                            'url' => TBP_Utils::get_placeholder_image('medium'),
                        ],
                        'dynamic' => true,
                    ],
                    'image_size' => [
                        'type' => 'select',
                        'label' => __('Image Size', 'theme-builder-pro'),
                        'default' => 'large',
                        'options' => [
                            'thumbnail' => __('Thumbnail', 'theme-builder-pro'),
                            'medium' => __('Medium', 'theme-builder-pro'),
                            'medium_large' => __('Medium Large', 'theme-builder-pro'),
                            'large' => __('Large', 'theme-builder-pro'),
                            'full' => __('Full', 'theme-builder-pro'),
                            'custom' => __('Custom', 'theme-builder-pro'),
                        ],
                    ],
                    'custom_dimension' => [
                        'type' => 'image_dimensions',
                        'label' => __('Custom Dimension', 'theme-builder-pro'),
                        'condition' => [
                            'image_size' => 'custom',
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
                        ],
                        'default' => '',
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                        ],
                    ],
                    'caption_source' => [
                        'type' => 'select',
                        'label' => __('Caption', 'theme-builder-pro'),
                        'default' => 'none',
                        'options' => [
                            'none' => __('None', 'theme-builder-pro'),
                            'attachment' => __('Attachment Caption', 'theme-builder-pro'),
                            'custom' => __('Custom Caption', 'theme-builder-pro'),
                        ],
                    ],
                    'caption' => [
                        'type' => 'text',
                        'label' => __('Custom Caption', 'theme-builder-pro'),
                        'dynamic' => true,
                        'condition' => [
                            'caption_source' => 'custom',
                        ],
                    ],
                    'link_to' => [
                        'type' => 'select',
                        'label' => __('Link', 'theme-builder-pro'),
                        'default' => 'none',
                        'options' => [
                            'none' => __('None', 'theme-builder-pro'),
                            'file' => __('Media File', 'theme-builder-pro'),
                            'custom' => __('Custom URL', 'theme-builder-pro'),
                        ],
                    ],
                    'link' => [
                        'type' => 'url',
                        'label' => __('Link', 'theme-builder-pro'),
                        'dynamic' => true,
                        'condition' => [
                            'link_to' => 'custom',
                        ],
                    ],
                    'open_lightbox' => [
                        'type' => 'switcher',
                        'label' => __('Open Lightbox', 'theme-builder-pro'),
                        'default' => 'yes',
                        'condition' => [
                            'link_to' => 'file',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function register_style_controls() {
        $this->style_controls = [
            'image_style_section' => [
                'type' => 'section',
                'label' => __('Image', 'theme-builder-pro'),
                'controls' => [
                    'width' => [
                        'type' => 'slider',
                        'label' => __('Width', 'theme-builder-pro'),
                        'size_units' => ['px', '%', 'vw'],
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 1000,
                            ],
                            '%' => [
                                'min' => 0,
                                'max' => 100,
                            ],
                        ],
                        'default' => [
                            'unit' => '%',
                        ],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} img' => 'width: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'max_width' => [
                        'type' => 'slider',
                        'label' => __('Max Width', 'theme-builder-pro'),
                        'size_units' => ['px', '%', 'vw'],
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 1000,
                            ],
                            '%' => [
                                'min' => 0,
                                'max' => 100,
                            ],
                        ],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} img' => 'max-width: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'height' => [
                        'type' => 'slider',
                        'label' => __('Height', 'theme-builder-pro'),
                        'size_units' => ['px', 'vh'],
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 1000,
                            ],
                        ],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} img' => 'height: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                    'object_fit' => [
                        'type' => 'select',
                        'label' => __('Object Fit', 'theme-builder-pro'),
                        'options' => [
                            '' => __('Default', 'theme-builder-pro'),
                            'fill' => __('Fill', 'theme-builder-pro'),
                            'cover' => __('Cover', 'theme-builder-pro'),
                            'contain' => __('Contain', 'theme-builder-pro'),
                        ],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'object-fit: {{VALUE}};',
                        ],
                    ],
                    'opacity' => [
                        'type' => 'slider',
                        'label' => __('Opacity', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 1,
                                'step' => 0.1,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'opacity: {{SIZE}};',
                        ],
                    ],
                    'css_filters_heading' => [
                        'type' => 'heading',
                        'label' => __('CSS Filters', 'theme-builder-pro'),
                    ],
                    'filter_blur' => [
                        'type' => 'slider',
                        'label' => __('Blur', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'filter: blur({{SIZE}}px);',
                        ],
                    ],
                    'filter_brightness' => [
                        'type' => 'slider',
                        'label' => __('Brightness', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 200,
                            ],
                        ],
                        'default' => [
                            'size' => 100,
                        ],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'filter: brightness({{SIZE}}%);',
                        ],
                    ],
                    'filter_contrast' => [
                        'type' => 'slider',
                        'label' => __('Contrast', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 200,
                            ],
                        ],
                        'default' => [
                            'size' => 100,
                        ],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'filter: contrast({{SIZE}}%);',
                        ],
                    ],
                    'filter_saturation' => [
                        'type' => 'slider',
                        'label' => __('Saturation', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 200,
                            ],
                        ],
                        'default' => [
                            'size' => 100,
                        ],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'filter: saturate({{SIZE}}%);',
                        ],
                    ],
                    'border_radius' => [
                        'type' => 'dimensions',
                        'label' => __('Border Radius', 'theme-builder-pro'),
                        'size_units' => ['px', '%'],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ],
                    'box_shadow' => [
                        'type' => 'box_shadow',
                        'label' => __('Box Shadow', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} img' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}};',
                        ],
                    ],
                ],
            ],
            'hover_section' => [
                'type' => 'section',
                'label' => __('Hover', 'theme-builder-pro'),
                'controls' => [
                    'hover_opacity' => [
                        'type' => 'slider',
                        'label' => __('Opacity', 'theme-builder-pro'),
                        'range' => [
                            'px' => [
                                'min' => 0,
                                'max' => 1,
                                'step' => 0.1,
                            ],
                        ],
                        'selectors' => [
                            '{{WRAPPER}}:hover img' => 'opacity: {{SIZE}};',
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
                                'max' => 3000,
                                'step' => 100,
                            ],
                        ],
                        'default' => [
                            'size' => 300,
                        ],
                        'selectors' => [
                            '{{WRAPPER}} img' => 'transition-duration: {{SIZE}}ms;',
                        ],
                    ],
                ],
            ],
            'caption_style_section' => [
                'type' => 'section',
                'label' => __('Caption', 'theme-builder-pro'),
                'controls' => [
                    'caption_align' => [
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
                        'selectors' => [
                            '{{WRAPPER}} .tbp-image-caption' => 'text-align: {{VALUE}};',
                        ],
                    ],
                    'caption_color' => [
                        'type' => 'color',
                        'label' => __('Text Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-image-caption' => 'color: {{VALUE}};',
                        ],
                    ],
                    'caption_background_color' => [
                        'type' => 'color',
                        'label' => __('Background Color', 'theme-builder-pro'),
                        'selectors' => [
                            '{{WRAPPER}} .tbp-image-caption' => 'background-color: {{VALUE}};',
                        ],
                    ],
                    'caption_typography_font_size' => [
                        'type' => 'slider',
                        'label' => __('Font Size', 'theme-builder-pro'),
                        'size_units' => ['px', 'em'],
                        'responsive' => true,
                        'selectors' => [
                            '{{WRAPPER}} .tbp-image-caption' => 'font-size: {{SIZE}}{{UNIT}};',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function render($settings) {
        $image = $settings['image'] ?? [];
        $image_size = $settings['image_size'] ?? 'large';
        $link_to = $settings['link_to'] ?? 'none';
        $link = $settings['link'] ?? [];
        $caption_source = $settings['caption_source'] ?? 'none';
        $caption = $settings['caption'] ?? '';
        $open_lightbox = $settings['open_lightbox'] ?? 'yes';
        $hover_animation = $settings['hover_animation'] ?? '';

        if (empty($image['url']) && empty($image['id'])) {
            return '';
        }

        // Get image URL
        $image_url = $image['url'];
        if (!empty($image['id'])) {
            $image_src = wp_get_attachment_image_src($image['id'], $image_size);
            if ($image_src) {
                $image_url = $image_src[0];
            }
        }

        // Get caption
        $caption_text = '';
        if ($caption_source === 'attachment' && !empty($image['id'])) {
            $caption_text = wp_get_attachment_caption($image['id']);
        } elseif ($caption_source === 'custom' && $caption) {
            $caption_text = $caption;
        }

        // Build link
        $link_url = '';
        $link_attrs = '';
        if ($link_to === 'file') {
            $link_url = $image['url'];
            if ($open_lightbox === 'yes') {
                $link_attrs = ' data-lightbox="tbp-lightbox"';
            }
        } elseif ($link_to === 'custom' && !empty($link['url'])) {
            $link_url = $link['url'];
            if (!empty($link['is_external'])) {
                $link_attrs .= ' target="_blank"';
            }
            if (!empty($link['nofollow'])) {
                $link_attrs .= ' rel="nofollow"';
            }
        }

        // Build classes
        $img_classes = ['tbp-image'];
        if ($hover_animation) {
            $img_classes[] = 'tbp-hover-' . $hover_animation;
        }

        // Build HTML
        $html = '<figure class="tbp-image-wrapper">';

        if ($link_url) {
            $html .= '<a href="' . esc_url($link_url) . '"' . $link_attrs . '>';
        }

        $html .= '<img src="' . esc_url($image_url) . '" class="' . esc_attr(implode(' ', $img_classes)) . '"';

        if (!empty($image['id'])) {
            $alt = get_post_meta($image['id'], '_wp_attachment_image_alt', true);
            $html .= ' alt="' . esc_attr($alt) . '"';
        }

        $html .= ' />';

        if ($link_url) {
            $html .= '</a>';
        }

        if ($caption_text) {
            $html .= '<figcaption class="tbp-image-caption">' . esc_html($caption_text) . '</figcaption>';
        }

        $html .= '</figure>';

        return $html;
    }
}
