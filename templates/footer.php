<?php
/**
 * Theme Builder Pro - Footer Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

$footer_id = TBP_Theme_Builder::get_template_id('footer');
$footer_settings = $footer_id ? get_post_meta($footer_id, '_tbp_document_settings', true) : [];

$classes = ['tbp-footer'];
if (isset($footer_settings['sticky']) && $footer_settings['sticky']) {
    $classes[] = 'tbp-footer-sticky';
}

?>
<footer id="tbp-footer" class="<?php echo esc_attr(implode(' ', $classes)); ?>">

    <?php if ($footer_id): ?>
        <div class="tbp-footer-inner">
            <?php
            $content = get_post_meta($footer_id, '_tbp_content', true);
            if ($content && is_array($content)) {
                echo '<div class="tbp-footer-content">';
                tbp_render_elements($content);
                echo '</div>';
            }
            ?>
        </div>
    <?php else: ?>
        <!-- Fallback footer when no custom footer is set -->
        <div class="tbp-footer-fallback">
            <div class="tbp-container">
                <div class="tbp-footer-widgets">
                    <?php if (is_active_sidebar('footer-1')): ?>
                        <div class="tbp-footer-widget-area">
                            <?php dynamic_sidebar('footer-1'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-2')): ?>
                        <div class="tbp-footer-widget-area">
                            <?php dynamic_sidebar('footer-2'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-3')): ?>
                        <div class="tbp-footer-widget-area">
                            <?php dynamic_sidebar('footer-3'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-4')): ?>
                        <div class="tbp-footer-widget-area">
                            <?php dynamic_sidebar('footer-4'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tbp-footer-bottom">
                    <div class="tbp-footer-copyright">
                        <?php
                        printf(
                            esc_html__('© %1$s %2$s. All rights reserved.', 'theme-builder-pro'),
                            date('Y'),
                            get_bloginfo('name')
                        );
                        ?>
                    </div>

                    <?php if (has_nav_menu('footer')): ?>
                        <nav class="tbp-footer-nav">
                            <?php
                            wp_nav_menu([
                                'theme_location' => 'footer',
                                'menu_class' => 'tbp-footer-menu',
                                'container' => false,
                                'depth' => 1,
                            ]);
                            ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</footer>
