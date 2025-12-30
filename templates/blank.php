<?php
/**
 * Theme Builder Pro - Blank Template
 * Full-width template without header/footer (for landing pages, etc.)
 *
 * Template Name: TBP Blank
 * Template Post Type: page, post, tbp_template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
$document_settings = get_post_meta($post_id, '_tbp_document_settings', true);
$page_settings = is_array($document_settings) ? $document_settings : [];

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class('tbp-blank-template'); ?>>

<?php wp_body_open(); ?>

<main id="tbp-main" class="tbp-main tbp-blank-main">
    <?php
    while (have_posts()) {
        the_post();

        $tbp_content = get_post_meta($post_id, '_tbp_content', true);

        if ($tbp_content && is_array($tbp_content)) {
            echo '<div class="tbp-document tbp-blank-content">';
            tbp_render_elements($tbp_content);
            echo '</div>';
        } else {
            echo '<div class="tbp-blank-fallback">';
            the_content();
            echo '</div>';
        }
    }
    ?>
</main>

<?php wp_footer(); ?>

</body>
</html>
