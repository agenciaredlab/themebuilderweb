<?php
/**
 * Theme Builder Pro - Archive Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$template_id = TBP_Theme_Builder::get_template_id('archive');

?>

<main id="tbp-main" class="tbp-main tbp-archive">

    <?php if ($template_id): ?>
        <?php
        // Use custom TBP template
        $content = get_post_meta($template_id, '_tbp_content', true);
        if ($content && is_array($content)) {
            echo '<div class="tbp-archive-content">';
            tbp_render_elements($content);
            echo '</div>';
        }
        ?>
    <?php else: ?>
        <!-- Fallback archive template -->
        <div class="tbp-container">

            <header class="tbp-archive-header">
                <?php
                the_archive_title('<h1 class="tbp-archive-title">', '</h1>');
                the_archive_description('<div class="tbp-archive-description">', '</div>');
                ?>
            </header>

            <?php if (have_posts()): ?>

                <div class="tbp-archive-grid">
                    <?php while (have_posts()): the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class('tbp-archive-item'); ?>>

                            <?php if (has_post_thumbnail()): ?>
                                <a href="<?php the_permalink(); ?>" class="tbp-archive-thumbnail">
                                    <?php the_post_thumbnail('medium_large'); ?>
                                </a>
                            <?php endif; ?>

                            <div class="tbp-archive-item-content">
                                <div class="tbp-archive-meta">
                                    <span class="tbp-archive-category">
                                        <?php
                                        $categories = get_the_category();
                                        if (!empty($categories)) {
                                            echo esc_html($categories[0]->name);
                                        }
                                        ?>
                                    </span>
                                    <span class="tbp-archive-date">
                                        <?php echo get_the_date(); ?>
                                    </span>
                                </div>

                                <h2 class="tbp-archive-item-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

                                <div class="tbp-archive-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>

                                <a href="<?php the_permalink(); ?>" class="tbp-archive-read-more">
                                    <?php esc_html_e('Read More', 'theme-builder-pro'); ?>
                                    <span>&rarr;</span>
                                </a>
                            </div>

                        </article>

                    <?php endwhile; ?>
                </div>

                <?php
                // Pagination
                the_posts_pagination([
                    'mid_size' => 2,
                    'prev_text' => '<span>&larr;</span> ' . esc_html__('Previous', 'theme-builder-pro'),
                    'next_text' => esc_html__('Next', 'theme-builder-pro') . ' <span>&rarr;</span>',
                    'class' => 'tbp-pagination',
                ]);
                ?>

            <?php else: ?>

                <div class="tbp-archive-empty">
                    <h2><?php esc_html_e('No posts found', 'theme-builder-pro'); ?></h2>
                    <p><?php esc_html_e('It seems we can\'t find what you\'re looking for.', 'theme-builder-pro'); ?></p>
                </div>

            <?php endif; ?>

        </div>
    <?php endif; ?>

</main>

<?php
get_footer();
