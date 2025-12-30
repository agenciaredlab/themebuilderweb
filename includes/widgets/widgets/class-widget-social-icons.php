<?php
/**
 * Social Icons Widget
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Social_Icons extends TBP_Widget_Base {

    public function get_name() {
        return 'social-icons';
    }

    public function get_title() {
        return __('Social Icons', 'theme-builder-pro');
    }

    public function get_icon() {
        return 'dashicons-share';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_keywords() {
        return ['social', 'icons', 'facebook', 'twitter', 'instagram', 'linkedin', 'share'];
    }

    private function get_social_platforms() {
        return [
            'facebook' => [
                'title' => 'Facebook',
                'icon' => 'fab fa-facebook-f',
                'color' => '#1877f2',
            ],
            'twitter' => [
                'title' => 'Twitter / X',
                'icon' => 'fab fa-x-twitter',
                'color' => '#000000',
            ],
            'instagram' => [
                'title' => 'Instagram',
                'icon' => 'fab fa-instagram',
                'color' => '#e4405f',
            ],
            'linkedin' => [
                'title' => 'LinkedIn',
                'icon' => 'fab fa-linkedin-in',
                'color' => '#0a66c2',
            ],
            'youtube' => [
                'title' => 'YouTube',
                'icon' => 'fab fa-youtube',
                'color' => '#ff0000',
            ],
            'tiktok' => [
                'title' => 'TikTok',
                'icon' => 'fab fa-tiktok',
                'color' => '#000000',
            ],
            'pinterest' => [
                'title' => 'Pinterest',
                'icon' => 'fab fa-pinterest-p',
                'color' => '#bd081c',
            ],
            'snapchat' => [
                'title' => 'Snapchat',
                'icon' => 'fab fa-snapchat-ghost',
                'color' => '#fffc00',
            ],
            'whatsapp' => [
                'title' => 'WhatsApp',
                'icon' => 'fab fa-whatsapp',
                'color' => '#25d366',
            ],
            'telegram' => [
                'title' => 'Telegram',
                'icon' => 'fab fa-telegram-plane',
                'color' => '#0088cc',
            ],
            'discord' => [
                'title' => 'Discord',
                'icon' => 'fab fa-discord',
                'color' => '#5865f2',
            ],
            'github' => [
                'title' => 'GitHub',
                'icon' => 'fab fa-github',
                'color' => '#333333',
            ],
            'dribbble' => [
                'title' => 'Dribbble',
                'icon' => 'fab fa-dribbble',
                'color' => '#ea4c89',
            ],
            'behance' => [
                'title' => 'Behance',
                'icon' => 'fab fa-behance',
                'color' => '#1769ff',
            ],
            'medium' => [
                'title' => 'Medium',
                'icon' => 'fab fa-medium-m',
                'color' => '#000000',
            ],
            'reddit' => [
                'title' => 'Reddit',
                'icon' => 'fab fa-reddit-alien',
                'color' => '#ff4500',
            ],
            'twitch' => [
                'title' => 'Twitch',
                'icon' => 'fab fa-twitch',
                'color' => '#9146ff',
            ],
            'spotify' => [
                'title' => 'Spotify',
                'icon' => 'fab fa-spotify',
                'color' => '#1db954',
            ],
            'soundcloud' => [
                'title' => 'SoundCloud',
                'icon' => 'fab fa-soundcloud',
                'color' => '#ff5500',
            ],
            'email' => [
                'title' => 'Email',
                'icon' => 'fas fa-envelope',
                'color' => '#ea4335',
            ],
            'phone' => [
                'title' => 'Phone',
                'icon' => 'fas fa-phone',
                'color' => '#25d366',
            ],
            'website' => [
                'title' => 'Website',
                'icon' => 'fas fa-globe',
                'color' => '#6366f1',
            ],
            'rss' => [
                'title' => 'RSS',
                'icon' => 'fas fa-rss',
                'color' => '#f26522',
            ],
        ];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section('section_social_icons', [
            'label' => __('Social Icons', 'theme-builder-pro'),
            'tab' => 'content',
        ]);

        $this->add_control('social_icons', [
            'label' => __('Social Icons', 'theme-builder-pro'),
            'type' => 'repeater',
            'default' => [
                ['platform' => 'facebook', 'url' => '#'],
                ['platform' => 'twitter', 'url' => '#'],
                ['platform' => 'instagram', 'url' => '#'],
            ],
            'fields' => [
                [
                    'name' => 'platform',
                    'label' => __('Platform', 'theme-builder-pro'),
                    'type' => 'select',
                    'options' => array_combine(
                        array_keys($this->get_social_platforms()),
                        array_column($this->get_social_platforms(), 'title')
                    ),
                    'default' => 'facebook',
                ],
                [
                    'name' => 'url',
                    'label' => __('Link', 'theme-builder-pro'),
                    'type' => 'url',
                    'default' => '#',
                ],
                [
                    'name' => 'custom_icon',
                    'label' => __('Custom Icon', 'theme-builder-pro'),
                    'type' => 'icon',
                ],
                [
                    'name' => 'custom_color',
                    'label' => __('Custom Color', 'theme-builder-pro'),
                    'type' => 'color',
                ],
            ],
            'title_field' => '{{{ platform }}}',
        ]);

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section('section_style', [
            'label' => __('Icon', 'theme-builder-pro'),
            'tab' => 'style',
        ]);

        $this->add_control('shape', [
            'label' => __('Shape', 'theme-builder-pro'),
            'type' => 'select',
            'default' => 'rounded',
            'options' => [
                'rounded' => __('Rounded', 'theme-builder-pro'),
                'square' => __('Square', 'theme-builder-pro'),
                'circle' => __('Circle', 'theme-builder-pro'),
            ],
        ]);

        $this->add_control('color_style', [
            'label' => __('Color', 'theme-builder-pro'),
            'type' => 'select',
            'default' => 'official',
            'options' => [
                'official' => __('Official Colors', 'theme-builder-pro'),
                'custom' => __('Custom', 'theme-builder-pro'),
            ],
        ]);

        $this->add_control('custom_icon_color', [
            'label' => __('Icon Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '#ffffff',
            'condition' => [
                'color_style' => 'custom',
            ],
        ]);

        $this->add_control('custom_bg_color', [
            'label' => __('Background Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '#6366f1',
            'condition' => [
                'color_style' => 'custom',
            ],
        ]);

        $this->add_control('icon_size', [
            'label' => __('Size', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 20,
            'min' => 10,
            'max' => 100,
        ]);

        $this->add_control('icon_padding', [
            'label' => __('Padding', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 12,
            'min' => 0,
            'max' => 50,
        ]);

        $this->add_control('icon_spacing', [
            'label' => __('Spacing', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 10,
            'min' => 0,
            'max' => 50,
        ]);

        $this->add_control('border_radius', [
            'label' => __('Border Radius', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 4,
            'min' => 0,
            'max' => 50,
            'condition' => [
                'shape' => 'rounded',
            ],
        ]);

        $this->end_controls_section();

        // Layout Section
        $this->start_controls_section('section_layout', [
            'label' => __('Layout', 'theme-builder-pro'),
            'tab' => 'style',
        ]);

        $this->add_control('align', [
            'label' => __('Alignment', 'theme-builder-pro'),
            'type' => 'choose',
            'options' => [
                'left' => ['title' => __('Left', 'theme-builder-pro'), 'icon' => 'dashicons-editor-alignleft'],
                'center' => ['title' => __('Center', 'theme-builder-pro'), 'icon' => 'dashicons-editor-aligncenter'],
                'right' => ['title' => __('Right', 'theme-builder-pro'), 'icon' => 'dashicons-editor-alignright'],
            ],
            'default' => 'center',
        ]);

        $this->add_control('direction', [
            'label' => __('Direction', 'theme-builder-pro'),
            'type' => 'select',
            'default' => 'horizontal',
            'options' => [
                'horizontal' => __('Horizontal', 'theme-builder-pro'),
                'vertical' => __('Vertical', 'theme-builder-pro'),
            ],
        ]);

        $this->end_controls_section();

        // Hover Section
        $this->start_controls_section('section_hover', [
            'label' => __('Hover', 'theme-builder-pro'),
            'tab' => 'style',
        ]);

        $this->add_control('hover_animation', [
            'label' => __('Animation', 'theme-builder-pro'),
            'type' => 'select',
            'default' => 'grow',
            'options' => [
                '' => __('None', 'theme-builder-pro'),
                'grow' => __('Grow', 'theme-builder-pro'),
                'shrink' => __('Shrink', 'theme-builder-pro'),
                'float' => __('Float', 'theme-builder-pro'),
                'sink' => __('Sink', 'theme-builder-pro'),
                'rotate' => __('Rotate', 'theme-builder-pro'),
                'pulse' => __('Pulse', 'theme-builder-pro'),
            ],
        ]);

        $this->add_control('hover_icon_color', [
            'label' => __('Icon Color', 'theme-builder-pro'),
            'type' => 'color',
        ]);

        $this->add_control('hover_bg_color', [
            'label' => __('Background Color', 'theme-builder-pro'),
            'type' => 'color',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $platforms = $this->get_social_platforms();

        $wrapper_classes = ['tbp-social-icons'];
        $wrapper_classes[] = 'tbp-social-' . $settings['shape'];
        $wrapper_classes[] = 'tbp-social-' . $settings['direction'];

        if (!empty($settings['hover_animation'])) {
            $wrapper_classes[] = 'tbp-social-hover-' . $settings['hover_animation'];
        }

        $wrapper_styles = [
            'justify-content: ' . ($settings['align'] === 'left' ? 'flex-start' : ($settings['align'] === 'right' ? 'flex-end' : 'center')),
            'gap: ' . $settings['icon_spacing'] . 'px',
        ];

        if ($settings['direction'] === 'vertical') {
            $wrapper_styles[] = 'flex-direction: column';
            $wrapper_styles[] = 'align-items: ' . ($settings['align'] === 'left' ? 'flex-start' : ($settings['align'] === 'right' ? 'flex-end' : 'center'));
        }

        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>"
             style="<?php echo esc_attr(implode('; ', $wrapper_styles)); ?>">

            <?php foreach ($settings['social_icons'] as $index => $item):
                $platform = $item['platform'];
                $platform_data = isset($platforms[$platform]) ? $platforms[$platform] : $platforms['website'];

                // Icon
                $icon = !empty($item['custom_icon']) ? $item['custom_icon'] : $platform_data['icon'];

                // Colors
                if ($settings['color_style'] === 'official') {
                    $bg_color = !empty($item['custom_color']) ? $item['custom_color'] : $platform_data['color'];
                    $icon_color = '#ffffff';
                } else {
                    $bg_color = $settings['custom_bg_color'];
                    $icon_color = $settings['custom_icon_color'];
                }

                // Border radius
                $border_radius = '0';
                if ($settings['shape'] === 'circle') {
                    $border_radius = '50%';
                } elseif ($settings['shape'] === 'rounded') {
                    $border_radius = $settings['border_radius'] . 'px';
                }

                $icon_styles = [
                    'font-size: ' . $settings['icon_size'] . 'px',
                    'padding: ' . $settings['icon_padding'] . 'px',
                    'background-color: ' . $bg_color,
                    'color: ' . $icon_color,
                    'border-radius: ' . $border_radius,
                ];

                $url = $item['url'];
                if ($platform === 'email' && strpos($url, 'mailto:') !== 0 && strpos($url, '#') !== 0) {
                    $url = 'mailto:' . $url;
                } elseif ($platform === 'phone' && strpos($url, 'tel:') !== 0 && strpos($url, '#') !== 0) {
                    $url = 'tel:' . preg_replace('/[^0-9+]/', '', $url);
                }
                ?>

                <a href="<?php echo esc_url($url); ?>"
                   class="tbp-social-icon tbp-social-<?php echo esc_attr($platform); ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="<?php echo esc_attr($platform_data['title']); ?>"
                   style="<?php echo esc_attr(implode('; ', $icon_styles)); ?>">
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                </a>

            <?php endforeach; ?>
        </div>
        <?php
    }
}
