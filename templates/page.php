<?php
/**
 * Theme Builder Pro - Page Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$template_id = TBP_Theme_Builder::get_template_id('page');

?>

<main id="tbp-main" class="tbp-main tbp-page">

    <?php if ($template_id): ?>
        <?php
        // Use custom TBP template
        $content = get_post_meta($template_id, '_tbp_content', true);
        if ($content && is_array($content)) {
            while (have_posts()) {
                the_post();
                echo '<div class="tbp-page-content">';
                tbp_render_elements($content);
                echo '</div>';
            }
        }
        ?>
    <?php else: ?>
        <!-- Fallback page template -->
        <div class="tbp-container">
            <?php while (have_posts()): the_post(); ?>

                <article id="page-<?php the_ID(); ?>" <?php post_class('tbp-page-article'); ?>>

                    <?php if (has_post_thumbnail()): ?>
                        <div class="tbp-page-thumbnail">
                            <?php the_post_thumbnail('full'); ?>
                        </div>
                    <?php endif; ?>

                    <header class="tbp-page-header">
                        <h1 class="tbp-page-title"><?php the_title(); ?></h1>
                    </header>

                    <div class="tbp-page-content entry-content">
                        <?php
                        the_content();

                        wp_link_pages([
                            'before' => '<div class="tbp-page-links">' . esc_html__('Pages:', 'theme-builder-pro'),
                            'after' => '</div>',
                        ]);
                        ?>
                    </div>

                    <?php
                    // Comments on pages (if enabled)
                    if (comments_open() || get_comments_number()) {
                        comments_template();
                    }
                    ?>

                </article>

            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</main>

<?php
get_footer();
