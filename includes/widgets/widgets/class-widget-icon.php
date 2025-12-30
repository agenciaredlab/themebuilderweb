<?php
/**
 * Icon Widget
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Icon extends TBP_Widget_Base {

    public function get_name() {
        return 'icon';
    }

    public function get_title() {
        return __('Icon', 'theme-builder-pro');
    }

    public function get_icon() {
        return 'dashicons-star-filled';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_keywords() {
        return ['icon', 'font awesome', 'symbol'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section('section_icon', [
            'label' => __('Icon', 'theme-builder-pro'),
            'tab' => 'content',
        ]);

        $this->add_control('icon', [
            'label' => __('Icon', 'theme-builder-pro'),
            'type' => 'icon',
            'default' => 'fas fa-star',
        ]);

        $this->add_control('view', [
            'label' => __('View', 'theme-builder-pro'),
            'type' => 'select',
            'default' => 'default',
            'options' => [
                'default' => __('Default', 'theme-builder-pro'),
                'stacked' => __('Stacked', 'theme-builder-pro'),
                'framed' => __('Framed', 'theme-builder-pro'),
            ],
        ]);

        $this->add_control('shape', [
            'label' => __('Shape', 'theme-builder-pro'),
            'type' => 'select',
            'default' => 'circle',
            'options' => [
                'circle' => __('Circle', 'theme-builder-pro'),
                'square' => __('Square', 'theme-builder-pro'),
            ],
            'condition' => [
                'view!' => 'default',
            ],
        ]);

        $this->add_control('link', [
            'label' => __('Link', 'theme-builder-pro'),
            'type' => 'url',
            'placeholder' => 'https://your-link.com',
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

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section('section_style', [
            'label' => __('Icon', 'theme-builder-pro'),
            'tab' => 'style',
        ]);

        $this->add_control('primary_color', [
            'label' => __('Primary Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '#6366f1',
        ]);

        $this->add_control('secondary_color', [
            'label' => __('Secondary Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '#ffffff',
            'condition' => [
                'view!' => 'default',
            ],
        ]);

        $this->add_control('size', [
            'label' => __('Size', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 50,
            'min' => 10,
            'max' => 300,
        ]);

        $this->add_control('padding', [
            'label' => __('Padding', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 25,
            'min' => 0,
            'max' => 100,
            'condition' => [
                'view!' => 'default',
            ],
        ]);

        $this->add_control('border_width', [
            'label' => __('Border Width', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 3,
            'min' => 1,
            'max' => 20,
            'condition' => [
                'view' => 'framed',
            ],
        ]);

        $this->add_control('rotate', [
            'label' => __('Rotate', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 0,
            'min' => 0,
            'max' => 360,
        ]);

        $this->end_controls_section();

        // Hover Section
        $this->start_controls_section('section_hover', [
            'label' => __('Hover', 'theme-builder-pro'),
            'tab' => 'style',
        ]);

        $this->add_control('hover_primary_color', [
            'label' => __('Primary Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '',
        ]);

        $this->add_control('hover_secondary_color', [
            'label' => __('Secondary Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '',
            'condition' => [
                'view!' => 'default',
            ],
        ]);

        $this->add_control('hover_animation', [
            'label' => __('Hover Animation', 'theme-builder-pro'),
            'type' => 'select',
            'default' => '',
            'options' => [
                '' => __('None', 'theme-builder-pro'),
                'grow' => __('Grow', 'theme-builder-pro'),
                'shrink' => __('Shrink', 'theme-builder-pro'),
                'pulse' => __('Pulse', 'theme-builder-pro'),
                'rotate' => __('Rotate', 'theme-builder-pro'),
                'bounce' => __('Bounce', 'theme-builder-pro'),
                'float' => __('Float', 'theme-builder-pro'),
                'wobble' => __('Wobble', 'theme-builder-pro'),
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $icon = $settings['icon'];
        $view = $settings['view'];
        $shape = isset($settings['shape']) ? $settings['shape'] : 'circle';

        $classes = ['tbp-icon-wrapper'];
        $classes[] = 'tbp-icon-view-' . $view;

        if ($view !== 'default') {
            $classes[] = 'tbp-icon-shape-' . $shape;
        }

        if (!empty($settings['hover_animation'])) {
            $classes[] = 'tbp-hover-' . $settings['hover_animation'];
        }

        // Build styles
        $wrapper_styles = [];
        $wrapper_styles[] = 'text-align: ' . $settings['align'];

        $icon_styles = [];
        $icon_styles[] = 'font-size: ' . $settings['size'] . 'px';

        if (!empty($settings['rotate'])) {
            $icon_styles[] = 'transform: rotate(' . $settings['rotate'] . 'deg)';
        }

        // Colors based on view
        if ($view === 'default') {
            $icon_styles[] = 'color: ' . $settings['primary_color'];
        } elseif ($view === 'stacked') {
            $icon_styles[] = 'color: ' . $settings['secondary_color'];
            $icon_styles[] = 'background-color: ' . $settings['primary_color'];
            $icon_styles[] = 'padding: ' . $settings['padding'] . 'px';
            if ($shape === 'circle') {
                $icon_styles[] = 'border-radius: 50%';
            }
        } elseif ($view === 'framed') {
            $icon_styles[] = 'color: ' . $settings['primary_color'];
            $icon_styles[] = 'border: ' . $settings['border_width'] . 'px solid ' . $settings['primary_color'];
            $icon_styles[] = 'padding: ' . $settings['padding'] . 'px';
            if ($shape === 'circle') {
                $icon_styles[] = 'border-radius: 50%';
            }
        }

        $has_link = !empty($settings['link']['url']);

        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" style="<?php echo esc_attr(implode('; ', $wrapper_styles)); ?>">
            <?php if ($has_link): ?>
                <a href="<?php echo esc_url($settings['link']['url']); ?>"
                   <?php if (!empty($settings['link']['is_external'])): ?>target="_blank"<?php endif; ?>
                   <?php if (!empty($settings['link']['nofollow'])): ?>rel="nofollow"<?php endif; ?>>
            <?php endif; ?>

            <span class="tbp-icon" style="<?php echo esc_attr(implode('; ', $icon_styles)); ?>">
                <i class="<?php echo esc_attr($icon); ?>"></i>
            </span>

            <?php if ($has_link): ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}
