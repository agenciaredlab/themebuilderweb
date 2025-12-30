<?php
/**
 * Theme Builder Pro - 404 Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$template_id = TBP_Theme_Builder::get_template_id('404');

?>

<main id="tbp-main" class="tbp-main tbp-404">

    <?php if ($template_id): ?>
        <?php
        // Use custom TBP template
        $content = get_post_meta($template_id, '_tbp_content', true);
        if ($content && is_array($content)) {
            echo '<div class="tbp-404-content">';
            tbp_render_elements($content);
            echo '</div>';
        }
        ?>
    <?php else: ?>
        <!-- Fallback 404 template -->
        <div class="tbp-container">
            <div class="tbp-404-fallback">

                <div class="tbp-404-icon">
                    <svg viewBox="0 0 24 24" width="120" height="120" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M8 15s1.5-2 4-2 4 2 4 2"/>
                        <line x1="9" y1="9" x2="9.01" y2="9" stroke-width="3" stroke-linecap="round"/>
                        <line x1="15" y1="9" x2="15.01" y2="9" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>

                <h1 class="tbp-404-title">
                    <?php esc_html_e('404', 'theme-builder-pro'); ?>
                </h1>

                <h2 class="tbp-404-subtitle">
                    <?php esc_html_e('Page Not Found', 'theme-builder-pro'); ?>
                </h2>

                <p class="tbp-404-description">
                    <?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'theme-builder-pro'); ?>
                </p>

                <div class="tbp-404-search">
                    <?php get_search_form(); ?>
                </div>

                <div class="tbp-404-actions">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="tbp-button tbp-button-primary">
                        <?php esc_html_e('Go to Homepage', 'theme-builder-pro'); ?>
                    </a>
                    <button onclick="history.back()" class="tbp-button tbp-button-secondary">
                        <?php esc_html_e('Go Back', 'theme-builder-pro'); ?>
                    </button>
                </div>

                <?php
                // Show recent posts
                $recent_posts = wp_get_recent_posts([
                    'numberposts' => 4,
                    'post_status' => 'publish',
                ]);

                if ($recent_posts):
                ?>
                    <div class="tbp-404-recent">
                        <h3><?php esc_html_e('Recent Posts', 'theme-builder-pro'); ?></h3>
                        <ul class="tbp-404-recent-list">
                            <?php foreach ($recent_posts as $post): ?>
                                <li>
                                    <a href="<?php echo get_permalink($post['ID']); ?>">
                                        <?php echo esc_html($post['post_title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php
                    wp_reset_postdata();
                endif;
                ?>

            </div>
        </div>
    <?php endif; ?>

</main>

<?php
get_footer();
