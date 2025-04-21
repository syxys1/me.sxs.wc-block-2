<?php
/**
 * Category Accordion Block
 * Rendering function only - registration is handled in me-sxs-block.php
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render callback for the block.
 *
 * @param array $attributes Block attributes.
 * @return string Block HTML.
 */
function sxs_category_accordion_render($attributes) {
    sxs_log("Entering rendering block function" . "\n" .
        "attributes" . print_r($attributes, true), 'DEBUG');
    
    // Améliorer la détection du contexte
    $is_editor = (defined('REST_REQUEST') && REST_REQUEST) || 
                 is_admin() ||
                 (defined('IFRAME_REQUEST') && IFRAME_REQUEST) ||
                 (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'wp-admin') !== false);

    sxs_log("Context check: \n" . 
        "REST_REQUEST: " . (defined('REST_REQUEST') ? 'true' : 'false') . "\n" .
        "is_admin: " . (is_admin() ? 'true' : 'false') . "\n" .
        "IFRAME_REQUEST: " . (defined('IFRAME_REQUEST') ? 'true' : 'false') . "\n" .
        "REFERER: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'none'), 'DEBUG');

    if ($is_editor) {
        return;
   }
    
    // Save the global post to restore it later
    global $post, $wp_query, $wp_the_query;
    $original_post = $post;
    $original_wp_query = $wp_query;
    $original_wp_the_query = $wp_the_query;
    sxs_log(" - Saving global variables." .
        "\n",'DEBUG');

    // Set defaults for all attributes
    $attributes = wp_parse_args($attributes, [
        'excludeCategories' => [],
        'order' => 'DESC',
        'orderBy' => 'date',
        'columns' => 4,
        'title' => 'Product Collection',
        'showSubcategories' => true,
        'titleFontSize' => 32,
        'titleFontColor' => '#000000',
        'separatorColor' => '#dddddd',
        'separatorThickness' => 1,
        'showPrice' => true,
        'showAddToCart' => true,
        'productFontSize' => 16,
        'productMargin' => 10,
        'productBorderColor' => '#eeeeee',
        'productBorderStyle' => 'solid',
        'accordionTitleFontSize' => 32,
        'accordionTitleFontColor' => '#000000',
        'accordionCaretColor' => '#000000',
        'accordionCaretImage' => '',
    ]);
    
    // Extract variables from attributes
    $exclude_categories = $attributes['excludeCategories'];
    $order = $attributes['order'];
    $show_subcategories = $attributes['showSubcategories'];
    $title_font_size = $attributes['titleFontSize'];
    $title_font_color = $attributes['titleFontColor'];
    $separator_color = $attributes['separatorColor'];
    $separator_thickness = $attributes['separatorThickness'];
    $show_price = $attributes['showPrice'];
    $show_add_to_cart = $attributes['showAddToCart'];
    $product_font_size = $attributes['productFontSize'];
    $product_margin = $attributes['productMargin'];
    $product_border_color = $attributes['productBorderColor'];
    $product_border_style = $attributes['productBorderStyle'];
    $accordion_title_font_size = $attributes['accordionTitleFontSize'];
    $accordion_title_font_color = $attributes['accordionTitleFontColor'];
    $accordion_caret_color = $attributes['accordionCaretColor'];
    $accordion_caret_image = $attributes['accordionCaretImage'];

     // Ajouter les variables CSS
     $sxs_plugin_css = sprintf(
        '<style>
            .wp-block-me-sxs-wc-block {
                --title-font-size: %spx;
                --title-font-color: %s;
                --separator-color: %s;
                --separator-thickness: %spx;
                --product-font-size: %spx;
                --product-margin: %spx;
                --product-border-color: %s;
                --product-border-style: %s;
                --accordion-title-font-size: %spx;
                --accordion-title-font-color: %s;
                --accordion-caret-color: %s;
                --accordion-title-separator-color: %s; /* Trait sous la catégorie */
                --accordion-caret-image: %s;
            }
        </style>',
        esc_attr($title_font_size),
        esc_attr($title_font_color),
        esc_attr($separator_color),
        esc_attr($separator_thickness),
        esc_attr($product_font_size),
        esc_attr($product_margin),
        esc_attr($product_border_color),
        esc_attr($product_border_style),
        esc_attr($accordion_title_font_size),
        esc_attr($accordion_title_font_color),
        esc_attr($accordion_caret_color),
        esc_attr($separator_color),
        $accordion_caret_image ? 'url("' . esc_url($accordion_caret_image) . '")' : 'none',
    );
    sxs_log(" - accordionCaretImage Parameter : " . 
        print_r($accordion_caret_image, true) . "\n" .
        print_r($sxs_plugin_css, true),'DEBUG');

    // // Continue with the normal frontend rendering
    // $args = array(
    //     'post_type' => 'product',
    //     'posts_per_page' => -1,
    //     'post_status' => 'publish',
    //     'orderby' => $attributes['orderBy'],
    //     'order' => $order,
    //     'tax_query' => array(
    //         array(
    //             'taxonomy' => 'product_cat',
    //             'field' => 'term_id',
    //             'operator' => 'NOT IN',
    //             'terms' => $exclude_categories
    //         )
    //     )
    // );

    // Journaliser les arguments de la requête
    //sxs_log("Query Arguments: " . print_r($args, true), 'DEBUG');

    //$products_query = new WP_Query($args);
    $products_query = $wp_query;
    // Journaliser les résultats de la requête
    sxs_log("Found Posts: " . $products_query->found_posts, 'DEBUG');
    sxs_log("SQL Query: " . $products_query->request, 'DEBUG');
    
    // Optionnel: journaliser les IDs des produits trouvés
    if ($products_query->have_posts()) {
        $product_ids = array();
        foreach ($products_query->posts as $post) {
            $product_ids[] = $post->ID;
        }
        sxs_log("Product IDs: " . implode(', ', $product_ids),'DEBUG');
    } else {
        sxs_log("No products found.\n",'DEBUG');
    }
    
    // NEW CODE: Organize products by category and subcategory
    $products_by_category = array();
    sxs_log(" - Organize products by category and subcategory",'DEBUG');

    if ($products_query->have_posts()) {
        sxs_log(" - while (products_query->have_posts()) ",'DEBUG');
            
        while ($products_query->have_posts()) {
            $products_query->the_post();
            global $product;
            sxs_log(" - products_query->the_posts() : ", 'DEBUG');
            
            // Get product categories
            $terms = get_the_terms($product->get_id(), 'product_cat');
            sxs_log(" - Get this product categories terms: " . print_r($terms, true), 'DEBUG');
                
            if (!$terms) continue;
            
            // Find main category and subcategory
            $main_cat = null;
            $sub_cat = null;
            foreach ($terms as $term) {
                if ($term->parent == 0) {
                    $main_cat = $term;
                } else {
                    $sub_cat = $term;
                }
            }
            
            if ($main_cat) {
                // Create category structure if it doesn't exist
                if (!isset($products_by_category[$main_cat->term_id])) {
                    $products_by_category[$main_cat->term_id] = array(
                        'category' => $main_cat,
                        'subcategories' => array(),
                        'products' => array()
                    );
                }
                
                // Add to subcategory if applicable
                if ($sub_cat) {
                    if (!isset($products_by_category[$main_cat->term_id]['subcategories'][$sub_cat->term_id])) {
                        $products_by_category[$main_cat->term_id]['subcategories'][$sub_cat->term_id] = array(
                            'category' => $sub_cat,
                            'products' => array()
                        );
                    }
                    $products_by_category[$main_cat->term_id]['subcategories'][$sub_cat->term_id]['products'][] = $product;
                } else {
                    // Add to main category if no subcategory
                    $products_by_category[$main_cat->term_id]['products'][] = $product;
                }
            }
            $summary = [];
            foreach ($products_by_category as $main_cat_id => $main_cat_data) {
                $summary[$main_cat_data['category']->name] = [
                    'products' => array_map(function($product) {
                        return $product->get_id();
                    }, $main_cat_data['products']),
                    'subcategories' => []
                ];
                foreach ($main_cat_data['subcategories'] as $sub_cat_id => $sub_cat_data) {
                    $summary[$main_cat_data['category']->name]['subcategories'][$sub_cat_data['category']->name] = array_map(function($product) {
                        return $product->get_id();
                    }, $sub_cat_data['products']);
                }
            }
            sxs_log('Products by category summary: ' . print_r($summary, true), 'DEBUG');
        }
    }
    
    // Reset post data after the loop
    wp_reset_postdata();
    
    // Démarrer la mise en mémoire tampon
    ob_start();
    
    // Afficher le contenu
    ?>
    <div class="wp-block-me-sxs-wc-block">
        <?php echo $sxs_plugin_css; ?>
        <div class="content-wrapper">
            <div class="product-categories">
            <?php 
            // NEW CODE: Render products using the organized structure
            if (!empty($products_by_category)) {
                foreach ($products_by_category as $main_cat_id => $main_cat_data) {
                    $main_cat = $main_cat_data['category'];
                    ?>
                    <div class="accordion-title" data-accordion-target="<?php echo esc_attr($main_cat->term_id); ?>">
                        <span class="accordion-caret"></span>
                        <span><?php echo esc_html($main_cat->name); ?></span>
                    </div>
                    <div class="accordion-content" id="accordion-content-<?php echo esc_attr($main_cat->term_id); ?>">
                        <div class="product-grid" style="grid-template-columns: repeat(<?php echo esc_attr($attributes['columns']); ?>, 1fr);">
                            
                            <?php
                            // First render products directly in the main category
                            foreach ($main_cat_data['products'] as $product) {
                                setup_postdata($GLOBALS['post'] =& $product->post);
                                wc_get_template_part('content', 'product');
                                ?>
                                <!-- <div class="product-item">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium'); ?>
                                        <h5><?php the_title(); ?></h5>
                                        <?php if ($show_price) : ?>
                                            <p><?php echo $product->get_price_html(); ?></p>
                                        <?php endif; ?>
                                        <?php if ($show_add_to_cart) : ?>
                                            <button><?php _e('Add to Cart', 'sxs-wc-block'); ?></button>
                                        <?php endif; ?>
                                    </a>
                                </div> -->
                                <?php
                            }
                            
                            // Then render subcategories and their products
                            foreach ($main_cat_data['subcategories'] as $sub_cat_id => $sub_cat_data) {
                                $sub_cat = $sub_cat_data['category'];
                                
                                if ($show_subcategories) : ?>
                                    <div class="category-title" style="grid-column: 1 / -1;">
                                        <h3><?php echo esc_html($sub_cat->name); ?></h3>
                                    </div>
                                <?php endif;
                                
                                foreach ($sub_cat_data['products'] as $product) {
                                    $post = get_post($product->get_id());
                                    setup_postdata($post);
                                    wc_get_template_part('content', 'product');
                                    //setup_postdata($GLOBALS['post'] =& $product->post);
                                    ?>
                                    <!-- <div class="product-item">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium'); ?>
                                            <h5><?php the_title(); ?></h5>
                                            <?php if ($show_price) : ?>
                                                <p><?php echo $product->get_price_html(); ?></p>
                                            <?php endif; ?>
                                            <?php if ($show_add_to_cart) : ?>
                                                <button><?php _e('Add to Cart', 'sxs-wc-block'); ?></button>
                                            <?php endif; ?>
                                        </a>
                                    </div> -->
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
            }
            wp_reset_postdata();
            ?>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.accordion-title').forEach((title) => {
            title.addEventListener('click', () => {
                const targetId = title.getAttribute('data-accordion-target');
                const content = document.getElementById(`accordion-content-${targetId}`);
                const caret = title.querySelector('.accordion-caret');

                if (content.classList.contains('open')) {
                    content.classList.remove('open');
                    caret.style.transform = 'rotate(0deg)';
                } else {
                    content.classList.add('open');
                    caret.style.transform = 'rotate(90deg)';
                }
            });
        });
    </script>
    <?php

    // Récupérer le contenu
    $block_content = ob_get_clean();

    // IMPORTANT: Restore all global variables before returning
    sxs_log(" - Restore global variables" . 
        "\n",'DEBUG');
    $post = $original_post;
    $wp_query = $original_wp_query;
    $wp_the_query = $original_wp_the_query;
    
    return $block_content;
}

// Modify the_content hook to avoid multiple calls
remove_action('the_content', 'sxs_wc_block_log_content');
add_action('the_content', 'sxs_wc_block_log_content', 1);

/**
 * Log content rendering information.
 *
 * @param string $content The content being rendered.
 * @return string The unmodified content.
 */
function sxs_wc_block_log_content($content) {
    static $already_logged = false;
    
    if (!$already_logged) {
        sxs_log("WC Block content rendering started\n" . 
            "Context: " . (is_admin() ? 'admin' : 'frontend') . "\n" .
            "URL: " . $_SERVER['REQUEST_URI'] . "\n" .
            "Content: " . print_r($content, true),'DEBUG');
        $already_logged = true;
    }
    
    return $content;
}

/**
 * Register and enqueue editor-specific scripts.
 */
function sxs_wc_block_enqueue_editor_scripts() {
    wp_register_script(
        'sxs-wc-block-editor-helper',
        SXS_WC_BLOCK_URL . 'src/editor-helper.js',
        array('wp-blocks', 'wp-dom-ready', 'wp-edit-post'),
        filemtime(SXS_WC_BLOCK_DIR . 'src/editor-helper.js')
    );
    wp_enqueue_script('sxs-wc-block-editor-helper');
}
add_action('enqueue_block_editor_assets', 'sxs_wc_block_enqueue_editor_scripts');

