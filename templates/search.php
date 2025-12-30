<?php
/**
 * Theme Builder Pro - Search Results Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$template_id = TBP_Theme_Builder::get_template_id('search');

?>

<main id="tbp-main" class="tbp-main tbp-search-results">

    <?php if ($template_id): ?>
        <?php
        // Use custom TBP template
        $content = get_post_meta($template_id, '_tbp_content', true);
        if ($content && is_array($content)) {
            echo '<div class="tbp-search-content">';
            tbp_render_elements($content);
            echo '</div>';
        }
        ?>
    <?php else: ?>
        <!-- Fallback search template -->
        <div class="tbp-container">

            <header class="tbp-search-header">
                <h1 class="tbp-search-title">
                    <?php
                    printf(
                        esc_html__('Search Results for: %s', 'theme-builder-pro'),
                        '<span>' . get_search_query() . '</span>'
                    );
                    ?>
                </h1>

                <div class="tbp-search-form-container">
                    <?php get_search_form(); ?>
                </div>

                <?php if (have_posts()): ?>
                    <p class="tbp-search-count">
                        <?php
                        global $wp_query;
                        printf(
                            esc_html(_n('%d result found', '%d results found', $wp_query->found_posts, 'theme-builder-pro')),
                            $wp_query->found_posts
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </header>

            <?php if (have_posts()): ?>

                <div class="tbp-search-results-grid">
                    <?php while (have_posts()): the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class('tbp-search-item'); ?>>

                            <?php if (has_post_thumbnail()): ?>
                                <a href="<?php the_permalink(); ?>" class="tbp-search-thumbnail">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            <?php endif; ?>

                            <div class="tbp-search-item-content">
                                <span class="tbp-search-item-type">
                                    <?php echo get_post_type_object(get_post_type())->labels->singular_name; ?>
                                </span>

                                <h2 class="tbp-search-item-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <div class="tbp-search-excerpt">
                                    <?php
                                    $excerpt = get_the_excerpt();
                                    $search_query = get_search_query();
                                    // Highlight search term
                                    $excerpt = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<mark>$1</mark>', $excerpt);
                                    echo wp_kses_post($excerpt);
                                    ?>
                                </div>

                                <div class="tbp-search-item-meta">
                                    <span class="tbp-search-date">
                                        <?php echo get_the_date(); ?>
                                    </span>
                                    <?php if (get_post_type() === 'post'): ?>
                                        <span class="tbp-search-author">
                                            <?php echo get_the_author(); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
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

                <div class="tbp-search-empty">
                    <div class="tbp-search-empty-icon">
                        <svg viewBox="0 0 24 24" width="80" height="80" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                            <path d="M8 8l6 6M14 8l-6 6" stroke-width="2"/>
                        </svg>
                    </div>

                    <h2><?php esc_html_e('No results found', 'theme-builder-pro'); ?></h2>
                    <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'theme-builder-pro'); ?></p>

                    <div class="tbp-search-suggestions">
                        <h3><?php esc_html_e('Suggestions:', 'theme-builder-pro'); ?></h3>
                        <ul>
                            <li><?php esc_html_e('Check your spelling', 'theme-builder-pro'); ?></li>
                            <li><?php esc_html_e('Try more general keywords', 'theme-builder-pro'); ?></li>
                            <li><?php esc_html_e('Try different keywords', 'theme-builder-pro'); ?></li>
                        </ul>
                    </div>

                    <a href="<?php echo esc_url(home_url('/')); ?>" class="tbp-button tbp-button-primary">
                        <?php esc_html_e('Go to Homepage', 'theme-builder-pro'); ?>
                    </a>
                </div>

            <?php endif; ?>

        </div>
    <?php endif; ?>

</main>

<?php
get_footer();
