<?php
/**
 * Theme Builder Pro - Product Archive Template
 *
 * @package Theme_Builder_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header('shop');

$template_id = TBP_Theme_Builder::get_template_id('product_archive');

?>

<main id="tbp-main" class="tbp-main tbp-woocommerce tbp-product-archive">

    <?php if ($template_id): ?>
        <?php
        // Use custom TBP template
        $content = get_post_meta($template_id, '_tbp_content', true);
        if ($content && is_array($content)) {
            echo '<div class="tbp-shop-content">';
            tbp_render_elements($content);
            echo '</div>';
        }
        ?>
    <?php else: ?>
        <?php
        // Default WooCommerce archive
        if (woocommerce_product_loop()) {

            do_action('woocommerce_before_shop_loop');

            woocommerce_product_loop_start();

            if (wc_get_loop_prop('total')) {
                while (have_posts()) {
                    the_post();
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                }
            }

            woocommerce_product_loop_end();

            do_action('woocommerce_after_shop_loop');

        } else {
            do_action('woocommerce_no_products_found');
        }
        ?>
    <?php endif; ?>

</main>

<?php
get_footer('shop');
