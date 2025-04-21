<?php
/**
 * Enhanced Category Accordion Template
 * Version: 2.1 (using Product Display class)
 */

defined('ABSPATH') || exit;

// Handle shop page content
$original_post = $GLOBALS['post'];
$shop_page_id = wc_get_page_id('shop');
$shop_page = get_post($shop_page_id);
setup_postdata($shop_page);
the_content();
$GLOBALS['post'] = $original_post;
wp_reset_postdata();

get_header('shop');
do_action('woocommerce_before_main_content');

if (woocommerce_product_loop()) {
    $product_display = SXS_Product_Display::get_instance();
    $product_display->log_display_data("=== Template B Started ===");
    
    do_action('woocommerce_before_shop_loop');
    
    // Render accordion display
    echo $product_display->render_accordion_display();
    
    do_action('woocommerce_after_shop_loop');
    $product_display->log_display_data("=== Template B Completed ===");
} else {
    do_action('woocommerce_no_products_found');
}

do_action('woocommerce_after_main_content');
do_action('woocommerce_sidebar');
get_footer('shop');
