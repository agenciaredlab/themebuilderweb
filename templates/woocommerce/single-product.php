<?php
/**
 * Theme Builder Pro - Single Product Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header('shop');

$template_id = TBP_Theme_Builder::get_template_id('product');

?>

<main id="tbp-main" class="tbp-main tbp-woocommerce tbp-single-product">

    <?php if ($template_id): ?>
        <?php
        // Use custom TBP template
        $content = get_post_meta($template_id, '_tbp_content', true);
        if ($content && is_array($content)) {
            while (have_posts()) {
                the_post();
                wc_get_template_part('content', 'single-product-tbp');
                // Or render TBP elements
                echo '<div class="tbp-product-content">';
                tbp_render_elements($content);
                echo '</div>';
            }
        }
        ?>
    <?php else: ?>
        <?php
        // Default WooCommerce template
        while (have_posts()) {
            the_post();
            wc_get_template_part('content', 'single-product');
        }
        ?>
    <?php endif; ?>

</main>

<?php
get_footer('shop');
