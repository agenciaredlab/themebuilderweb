<?php
/**
 * Theme Builder Pro - Header Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

$header_id = TBP_Theme_Builder::get_template_id('header');
$header_settings = $header_id ? get_post_meta($header_id, '_tbp_document_settings', true) : [];

// Header options
$sticky = isset($header_settings['sticky']) && $header_settings['sticky'];
$transparent = isset($header_settings['transparent']) && $header_settings['transparent'];
$overlay = isset($header_settings['overlay']) && $header_settings['overlay'];

$classes = ['tbp-header'];
if ($sticky) $classes[] = 'tbp-header-sticky';
if ($transparent) $classes[] = 'tbp-header-transparent';
if ($overlay) $classes[] = 'tbp-header-overlay';

?>
<header id="tbp-header" class="<?php echo esc_attr(implode(' ', $classes)); ?>"
        <?php if ($sticky): ?>data-sticky="true"<?php endif; ?>
        <?php if (isset($header_settings['sticky_behavior'])): ?>
            data-sticky-behavior="<?php echo esc_attr($header_settings['sticky_behavior']); ?>"
        <?php endif; ?>>

    <?php if ($header_id): ?>
        <div class="tbp-header-inner">
            <?php
            $content = get_post_meta($header_id, '_tbp_content', true);
            if ($content && is_array($content)) {
                echo '<div class="tbp-header-content">';
                tbp_render_elements($content);
                echo '</div>';
            }
            ?>
        </div>
    <?php else: ?>
        <!-- Fallback header when no custom header is set -->
        <div class="tbp-header-fallback">
            <div class="tbp-container">
                <div class="tbp-header-row">
                    <div class="tbp-header-logo">
                        <?php if (has_custom_logo()): ?>
                            <?php the_custom_logo(); ?>
                        <?php else: ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="tbp-site-title">
                                <?php bloginfo('name'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <nav class="tbp-header-nav">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'primary',
                            'menu_class' => 'tbp-nav-menu',
                            'container' => false,
                            'fallback_cb' => false,
                        ]);
                        ?>
                    </nav>

                    <button class="tbp-mobile-toggle" aria-label="<?php esc_attr_e('Toggle Menu', 'theme-builder-pro'); ?>">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</header>

<?php if ($sticky && !$overlay): ?>
    <div class="tbp-header-placeholder"></div>
<?php endif; ?>
