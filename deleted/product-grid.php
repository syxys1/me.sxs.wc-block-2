<?php
/**
 * Product Grid Block
 */

if (!defined('ABSPATH')) {
    exit;
}

function sxs_render_product_grid($attributes) {
    global $post;
    
    // Context detection
    $is_editor = (defined('REST_REQUEST') && REST_REQUEST) || is_admin();
    
    if ($is_editor) {
        return '<div class="sxs-product-grid-placeholder">Product Grid Preview</div>';
    }

    // Utiliser la requête WooCommerce existante si on est sur la page shop
    if (is_shop()) {
        global $wp_query;
        $products = $wp_query;
    } else {
        // Sinon créer une nouvelle requête
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $attributes['perPage'] ?? 12,
            'orderby' => $attributes['orderby'] ?? 'date',
            'order' => $attributes['order'] ?? 'DESC'
        ];

        if (!empty($attributes['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $attributes['category']
                ]
            ];
        }
        $products = new WP_Query($args);
    }
    ob_start();
    
    if ($products->have_posts()) {
        echo '<div class="sxs-product-grid" style="--columns: '.esc_attr($attributes['columns']).'">';
        while ($products->have_posts()) {
            $products->the_post();
            wc_get_template_part('content', 'product');
        }
        echo '</div>';
    }
    
    wp_reset_postdata();
    return ob_get_clean();
}
