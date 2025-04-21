<?php
/**
 * Standard WooCommerce Template
 * Version: 2.1 (using Product Display class)
 */

defined('ABSPATH') || exit;

//$product_display = SXS_Product_Display::get_instance();
//$product_display->log_display_data("=== Template A Started ===");

get_header('shop');
do_action('woocommerce_before_main_content');

if (woocommerce_product_loop()) {
    do_action('woocommerce_before_shop_loop');
    
    woocommerce_product_loop_start();
    
    // while (have_posts()) {
    //     the_post();
    //     
    //     //do_action('woocommerce_shop_loop');
    //     wc_get_template_part('content', 'product');
    // }
    
    woocommerce_product_loop_end();
    do_action('woocommerce_after_shop_loop');
} else {
    do_action('woocommerce_no_products_found');
}

//$product_display->log_display_data("=== Template A Completed ===");
do_action('woocommerce_after_main_content');
do_action('woocommerce_sidebar');
get_footer('shop');
