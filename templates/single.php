<?php
/**
 * Theme Builder Pro - Single Post Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$template_id = TBP_Theme_Builder::get_template_id('single');

?>

<main id="tbp-main" class="tbp-main tbp-single">

    <?php if ($template_id): ?>
        <?php
        // Use custom TBP template
        $content = get_post_meta($template_id, '_tbp_content', true);
        if ($content && is_array($content)) {
            while (have_posts()) {
                the_post();
                echo '<article id="post-' . get_the_ID() . '" class="' . implode(' ', get_post_class('tbp-single-article')) . '">';
                tbp_render_elements($content);
                echo '</article>';
            }
        }
        ?>
    <?php else: ?>
        <!-- Fallback single template -->
        <div class="tbp-container">
            <?php while (have_posts()): the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('tbp-single-article tbp-single-fallback'); ?>>

                    <?php if (has_post_thumbnail()): ?>
                        <div class="tbp-single-thumbnail">
                            <?php the_post_thumbnail('large'); ?>
                        </div>
                    <?php endif; ?>

                    <header class="tbp-single-header">
                        <div class="tbp-single-meta">
                            <span class="tbp-single-category">
                                <?php the_category(', '); ?>
                            </span>
                            <span class="tbp-single-date">
                                <?php echo get_the_date(); ?>
                            </span>
                        </div>

                        <h1 class="tbp-single-title"><?php the_title(); ?></h1>

                        <div class="tbp-single-author">
                            <?php echo get_avatar(get_the_author_meta('ID'), 48); ?>
                            <div class="tbp-single-author-info">
                                <span class="tbp-single-author-name">
                                    <?php the_author(); ?>
                                </span>
                                <span class="tbp-single-reading-time">
                                    <?php echo tbp_get_reading_time(get_the_content()); ?>
                                </span>
                            </div>
                        </div>
                    </header>

                    <div class="tbp-single-content">
                        <?php
                        the_content();

                        wp_link_pages([
                            'before' => '<div class="tbp-page-links">' . esc_html__('Pages:', 'theme-builder-pro'),
                            'after' => '</div>',
                        ]);
                        ?>
                    </div>

                    <footer class="tbp-single-footer">
                        <?php if (has_tag()): ?>
                            <div class="tbp-single-tags">
                                <?php the_tags('<span class="tbp-tag-label">' . esc_html__('Tags:', 'theme-builder-pro') . '</span> ', ', '); ?>
                            </div>
                        <?php endif; ?>

                        <div class="tbp-single-share">
                            <span class="tbp-share-label"><?php esc_html_e('Share:', 'theme-builder-pro'); ?></span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener" class="tbp-share-facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener" class="tbp-share-twitter">
                                <i class="fab fa-x-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener" class="tbp-share-linkedin">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </footer>

                    <?php
                    // Author bio
                    if (get_the_author_meta('description')):
                    ?>
                        <div class="tbp-author-bio">
                            <?php echo get_avatar(get_the_author_meta('ID'), 96); ?>
                            <div class="tbp-author-bio-content">
                                <h3 class="tbp-author-bio-name"><?php the_author(); ?></h3>
                                <p class="tbp-author-bio-description"><?php the_author_meta('description'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Post navigation
                    the_post_navigation([
                        'prev_text' => '<span class="tbp-nav-subtitle">' . esc_html__('Previous', 'theme-builder-pro') . '</span><span class="tbp-nav-title">%title</span>',
                        'next_text' => '<span class="tbp-nav-subtitle">' . esc_html__('Next', 'theme-builder-pro') . '</span><span class="tbp-nav-title">%title</span>',
                        'class' => 'tbp-post-navigation',
                    ]);
                    ?>

                    <?php
                    // Comments
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

/**
 * Calculate reading time
 */
function tbp_get_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200);
    return sprintf(
        _n('%d min read', '%d min read', $reading_time, 'theme-builder-pro'),
        $reading_time
    );
}
