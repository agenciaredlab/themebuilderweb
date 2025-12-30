<?php
/**
 * Text Editor Widget
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Widget_Text_Editor extends TBP_Widget_Base {

    public function get_name() {
        return 'text-editor';
    }

    public function get_title() {
        return __('Text Editor', 'theme-builder-pro');
    }

    public function get_icon() {
        return 'dashicons-editor-paragraph';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_keywords() {
        return ['text', 'editor', 'paragraph', 'content', 'wysiwyg'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section('section_content', [
            'label' => __('Content', 'theme-builder-pro'),
            'tab' => 'content',
        ]);

        $this->add_control('content', [
            'label' => __('Content', 'theme-builder-pro'),
            'type' => 'wysiwyg',
            'default' => '<p>' . __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'theme-builder-pro') . '</p>',
            'description' => __('Enter your content here', 'theme-builder-pro'),
        ]);

        $this->add_control('drop_cap', [
            'label' => __('Drop Cap', 'theme-builder-pro'),
            'type' => 'switcher',
            'default' => false,
            'description' => __('Enable drop cap for the first letter', 'theme-builder-pro'),
        ]);

        $this->add_control('columns', [
            'label' => __('Columns', 'theme-builder-pro'),
            'type' => 'select',
            'default' => '1',
            'options' => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
            ],
        ]);

        $this->add_control('column_gap', [
            'label' => __('Column Gap', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 20,
            'min' => 0,
            'max' => 100,
            'condition' => [
                'columns!' => '1',
            ],
        ]);

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section('section_style', [
            'label' => __('Style', 'theme-builder-pro'),
            'tab' => 'style',
        ]);

        $this->add_control('text_align', [
            'label' => __('Alignment', 'theme-builder-pro'),
            'type' => 'choose',
            'options' => [
                'left' => ['title' => __('Left', 'theme-builder-pro'), 'icon' => 'dashicons-editor-alignleft'],
                'center' => ['title' => __('Center', 'theme-builder-pro'), 'icon' => 'dashicons-editor-aligncenter'],
                'right' => ['title' => __('Right', 'theme-builder-pro'), 'icon' => 'dashicons-editor-alignright'],
                'justify' => ['title' => __('Justify', 'theme-builder-pro'), 'icon' => 'dashicons-editor-justify'],
            ],
            'default' => 'left',
        ]);

        $this->add_control('text_color', [
            'label' => __('Text Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '',
            'selectors' => [
                '{{WRAPPER}} .tbp-text-editor' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('typography', [
            'label' => __('Typography', 'theme-builder-pro'),
            'type' => 'typography',
            'selector' => '{{WRAPPER}} .tbp-text-editor',
        ]);

        $this->end_controls_section();

        // Drop Cap Style
        $this->start_controls_section('section_drop_cap_style', [
            'label' => __('Drop Cap', 'theme-builder-pro'),
            'tab' => 'style',
            'condition' => [
                'drop_cap' => true,
            ],
        ]);

        $this->add_control('drop_cap_color', [
            'label' => __('Color', 'theme-builder-pro'),
            'type' => 'color',
            'default' => '',
            'selectors' => [
                '{{WRAPPER}} .tbp-text-editor p:first-child::first-letter' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('drop_cap_size', [
            'label' => __('Size', 'theme-builder-pro'),
            'type' => 'slider',
            'default' => 50,
            'min' => 20,
            'max' => 200,
            'selectors' => [
                '{{WRAPPER}} .tbp-text-editor p:first-child::first-letter' => 'font-size: {{VALUE}}px;',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $classes = ['tbp-text-editor'];

        if (!empty($settings['drop_cap'])) {
            $classes[] = 'tbp-drop-cap';
        }

        $styles = [];
        if (!empty($settings['text_align'])) {
            $styles[] = 'text-align: ' . $settings['text_align'];
        }
        if (!empty($settings['columns']) && $settings['columns'] > 1) {
            $styles[] = 'column-count: ' . $settings['columns'];
            if (!empty($settings['column_gap'])) {
                $styles[] = 'column-gap: ' . $settings['column_gap'] . 'px';
            }
        }

        $style_attr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';

        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>"<?php echo $style_attr; ?>>
            <?php echo wp_kses_post($settings['content']); ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var classes = ['tbp-text-editor'];
        if (settings.drop_cap) {
            classes.push('tbp-drop-cap');
        }

        var styles = [];
        if (settings.text_align) {
            styles.push('text-align: ' + settings.text_align);
        }
        if (settings.columns > 1) {
            styles.push('column-count: ' + settings.columns);
            if (settings.column_gap) {
                styles.push('column-gap: ' + settings.column_gap + 'px');
            }
        }
        #>
        <div class="{{{ classes.join(' ') }}}" style="{{{ styles.join('; ') }}}">
            {{{ settings.content }}}
        </div>
        <?php
    }
}
