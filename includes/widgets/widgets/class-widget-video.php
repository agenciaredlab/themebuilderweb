<?php
/**
 * Video Widget
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Video extends TBP_Widget_Base {

    public function get_name() {
        return 'video';
    }

    public function get_title() {
        return __('Video', 'theme-builder-pro');
    }

    public function get_icon() {
        return 'dashicons-video-alt3';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_keywords() {
        return ['video', 'youtube', 'vimeo', 'player', 'embed', 'media'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section('section_video', [
            'label' => __('Video', 'theme-builder-pro'),
            'tab' => 'content',
        ]);

        $this->add_control('video_type', [
            'label' => __('Source', 'theme-builder-pro'),
            'type' => 'select',
            'default' => 'youtube',
            'options' => [
                'youtube' => 'YouTube',
                'vimeo' => 'Vimeo',
                'self_hosted' => __('Self Hosted', 'theme-builder-pro'),
            ],
        ]);

        $this->add_control('youtube_url', [
            'label' => __('YouTube URL', 'theme-builder-pro'),
            'type' => 'text',
            'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
            'placeholder' => 'https://www.youtube.com/watch?v=...',
            'condition' => [
                'video_type' => 'youtube',
            ],
        ]);

        $this->add_control('vimeo_url', [
            'label' => __('Vimeo URL', 'theme-builder-pro'),
            'type' => 'text',
            'default' => 'https://vimeo.com/235215203',
            'placeholder' => 'https://vimeo.com/...',
            'condition' => [
                'video_type' => 'vimeo',
            ],
        ]);

        $this->add_control('hosted_url', [
            'label' => __('Video File', 'theme-builder-pro'),
            'type' => 'media',
            'media_type' => 'video',
            'condition' => [
                'video_type' => 'self_hosted',
            ],
        ]);

        $this->add_control('external_url', [
            'label' => __('External URL', 'theme-builder-pro'),
            'type' => 'text',
            'condition' => [
                'video_type' => 'self_hosted',
            ],
        ]);

        $this->end_controls_section();

        // Video Options
        $this->start_controls_section('section_video_options', [
            'label' => __('Video Options', 'theme-builder-pro'),
            'tab' => 'content',
        ]);

        $this->add_control('autoplay', [
            'label' => __('Autoplay', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
        ]);

        $this->add_control('mute', [
            'label' => __('Mute', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
        ]);

        $this->add_control('loop', [
            'label' => __('Loop', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
        ]);

        $this->add_control('controls', [
            'label' => __('Player Controls', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => true,
        ]);

        $this->add_control('modest_branding', [
            'label' => __('Modest Branding', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
            'description' => __('Hide YouTube logo (YouTube only)', 'theme-builder-pro'),
            'condition' => [
                'video_type' => 'youtube',
            ],
        ]);

        $this->add_control('privacy_mode', [
            'label' => __('Privacy Mode', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
            'description' => __('Enable enhanced privacy mode (YouTube only)', 'theme-builder-pro'),
            'condition' => [
                'video_type' => 'youtube',
            ],
        ]);

        $this->add_control('start_time', [
            'label' => __('Start Time (seconds)', 'theme-builder-pro'),
            'type' => 'number',
            'default' => 0,
            'min' => 0,
        ]);

        $this->add_control('end_time', [
            'label' => __('End Time (seconds)', 'theme-builder-pro'),
            'type' => 'number',
            'default' => 0,
            'min' => 0,
            'condition' => [
                'video_type' => 'youtube',
            ],
        ]);

        $this->end_controls_section();

        // Image Overlay
        $this->start_controls_section('section_image_overlay', [
            'label' => __('Image Overlay', 'theme-builder-pro'),
            'tab' => 'content',
        ]);

        $this->add_control('show_image_overlay', [
            'label' => __('Image Overlay', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
        ]);

        $this->add_control('image_overlay', [
            'label' => __('Image', 'theme-builder-pro'),
            'type' => 'media',
            'condition' => [
                'show_image_overlay' => true,
            ],
        ]);

        $this->add_control('lazy_load', [
            'label' => __('Lazy Load', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => true,
            'description' => __('Load video only when play is clicked', 'theme-builder-pro'),
            'condition' => [
                'show_image_overlay' => true,
            ],
        ]);

        $this->add_control('play_icon', [
            'label' => __('Play Icon', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => true,
            'condition' => [
                'show_image_overlay' => true,
            ],
        ]);

        $this->add_control('lightbox', [
            'label' => __('Lightbox', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
            'condition' => [
                'show_image_overlay' => true,
            ],
        ]);

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section('section_video_style', [
            'label' => __('Video', 'theme-builder-pro'),
            'tab' => 'style',
        ]);

        $this->add_control('aspect_ratio', [
            'label' => __('Aspect Ratio', 'theme-builder-pro'),
            'type' => 'select',
            'default' => '169',
            'options' => [
                '169' => '16:9',
                '219' => '21:9',
                '43' => '4:3',
                '32' => '3:2',
                '11' => '1:1',
            ],
        ]);

        $this->add_control('border_radius', [
            'label' => __('Border Radius', 'theme-builder-pro'),
            'type' => 'dimensions',
            'default' => [
                'top' => 0,
                'right' => 0,
                'bottom' => 0,
                'left' => 0,
            ],
            'selectors' => [
                '{{WRAPPER}} .tbp-video-wrapper' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px; overflow: hidden;',
            ],
        ]);

        $this->add_control('box_shadow', [
            'label' => __('Box Shadow', 'theme-builder-pro'),
            'type' => 'box_shadow',
            'selectors' => [
                '{{WRAPPER}} .tbp-video-wrapper' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}};',
            ],
        ]);

        $this->end_controls_section();

        // Play Icon Style
        $this->start_controls_section('section_play_icon_style', [
            'label' => __('Play Icon', 'theme-builder-pro'),
            'tab' => 'style',
            'condition' => [
                'show_image_overlay' => true,
                'play_icon' => true,
            ],
        ]);

        $this->add_control('play_icon_color', [
            'label' => __('Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '#ffffff',
        ]);

        $this->add_control('play_icon_size', [
            'label' => __('Size', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 80,
            'min' => 20,
            'max' => 200,
        ]);

        $this->add_control('play_icon_background', [
            'label' => __('Background Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => 'rgba(0, 0, 0, 0.6)',
        ]);

        $this->end_controls_section();
    }

    private function get_video_id($url, $type) {
        if ($type === 'youtube') {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
            return isset($matches[1]) ? $matches[1] : '';
        } elseif ($type === 'vimeo') {
            preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches);
            return isset($matches[1]) ? $matches[1] : '';
        }
        return '';
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $video_type = $settings['video_type'];
        $show_overlay = !empty($settings['show_image_overlay']);
        $lazy_load = $show_overlay && !empty($settings['lazy_load']);

        // Build video URL
        $video_url = '';
        $video_id = '';

        if ($video_type === 'youtube') {
            $video_url = $settings['youtube_url'];
            $video_id = $this->get_video_id($video_url, 'youtube');
        } elseif ($video_type === 'vimeo') {
            $video_url = $settings['vimeo_url'];
            $video_id = $this->get_video_id($video_url, 'vimeo');
        } else {
            $video_url = !empty($settings['hosted_url']['url']) ? $settings['hosted_url']['url'] : $settings['external_url'];
        }

        $aspect_ratios = [
            '169' => '56.25',
            '219' => '42.85',
            '43' => '75',
            '32' => '66.66',
            '11' => '100',
        ];
        $aspect_ratio = isset($aspect_ratios[$settings['aspect_ratio']]) ? $aspect_ratios[$settings['aspect_ratio']] : '56.25';

        $wrapper_class = 'tbp-video-wrapper';
        if ($lazy_load) {
            $wrapper_class .= ' tbp-video-lazy';
        }

        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>"
             data-video-type="<?php echo esc_attr($video_type); ?>"
             data-video-id="<?php echo esc_attr($video_id); ?>"
             <?php if ($lazy_load): ?>data-lazy="true"<?php endif; ?>
             style="padding-bottom: <?php echo esc_attr($aspect_ratio); ?>%;">

            <?php if ($show_overlay && !empty($settings['image_overlay']['url'])): ?>
                <div class="tbp-video-thumbnail">
                    <img src="<?php echo esc_url($settings['image_overlay']['url']); ?>" alt="">
                </div>

                <?php if (!empty($settings['play_icon'])): ?>
                    <button class="tbp-video-play"
                            style="color: <?php echo esc_attr($settings['play_icon_color']); ?>;
                                   font-size: <?php echo esc_attr($settings['play_icon_size']); ?>px;
                                   background: <?php echo esc_attr($settings['play_icon_background']); ?>;">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="1em" height="1em">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                <?php endif; ?>

            <?php elseif (!$lazy_load): ?>
                <?php $this->render_video($video_type, $video_id, $video_url, $settings); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_video($type, $id, $url, $settings) {
        $params = [];

        if ($type === 'youtube') {
            $domain = !empty($settings['privacy_mode']) ? 'www.youtube-nocookie.com' : 'www.youtube.com';

            if (!empty($settings['autoplay'])) $params['autoplay'] = 1;
            if (!empty($settings['mute'])) $params['mute'] = 1;
            if (!empty($settings['loop'])) {
                $params['loop'] = 1;
                $params['playlist'] = $id;
            }
            if (empty($settings['controls'])) $params['controls'] = 0;
            if (!empty($settings['modest_branding'])) $params['modestbranding'] = 1;
            if (!empty($settings['start_time'])) $params['start'] = $settings['start_time'];
            if (!empty($settings['end_time'])) $params['end'] = $settings['end_time'];

            $params['rel'] = 0;

            $src = 'https://' . $domain . '/embed/' . $id . '?' . http_build_query($params);

            echo '<iframe class="tbp-video-iframe" src="' . esc_url($src) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

        } elseif ($type === 'vimeo') {
            if (!empty($settings['autoplay'])) $params['autoplay'] = 1;
            if (!empty($settings['mute'])) $params['muted'] = 1;
            if (!empty($settings['loop'])) $params['loop'] = 1;

            $src = 'https://player.vimeo.com/video/' . $id . '?' . http_build_query($params);

            echo '<iframe class="tbp-video-iframe" src="' . esc_url($src) . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';

        } else {
            $attrs = [];
            if (!empty($settings['controls'])) $attrs[] = 'controls';
            if (!empty($settings['autoplay'])) $attrs[] = 'autoplay';
            if (!empty($settings['mute'])) $attrs[] = 'muted';
            if (!empty($settings['loop'])) $attrs[] = 'loop';

            echo '<video class="tbp-video-player" src="' . esc_url($url) . '" ' . implode(' ', $attrs) . '></video>';
        }
    }
}
