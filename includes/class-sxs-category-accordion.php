<?php
/**
 * Category Accordion Block
 *
 * @package SXS_WC_Blocks
 */

defined('ABSPATH') || exit;

/**
 * Class SXS_Category_Accordion_Block
 * 
 * Handles the registration and rendering of the Category Accordion block.
 */
class SXS_Category_Accordion_Block {
    /**
     * Singleton instance
     *
     * @var SXS_Category_Accordion_Block
     */
    private static $instance = null;
    
    /**
     * Block name
     *
     * @var string
     */
    private $block_name = 'me-sxs-block/category-accordion';
    
    /**
     * Get singleton instance
     *
     * @return SXS_Category_Accordion_Block
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Register the block
        add_action('init', [$this, 'register_block']);
        
        // Register editor scripts
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_scripts']);
        
        // Modify the_content hook to avoid multiple calls
        remove_action('the_content', 'sxs_wc_block_log_content');
        add_action('the_content', [$this, 'log_content'], 1);
    }
    
    /**
     * Register the block
     */
    public function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }
        
        register_block_type($this->block_name, [
            'editor_script'   => 'sxs-category-accordion-editor-script',
            'editor_style'    => 'sxs-category-accordion-editor-style',
            'style'           => 'sxs-category-accordion-style',
            'render_callback' => [$this, 'render'],
            'attributes'      => [
                'excludeCategories' => [
                    'type'    => 'array',
                    'default' => [],
                ],
                'order' => [
                    'type'    => 'string',
                    'default' => 'DESC',
                ],
                'orderBy' => [
                    'type'    => 'string',
                    'default' => 'date',
                ],
                'columns' => [
                    'type'    => 'number',
                    'default' => 4,
                ],
                'title' => [
                    'type'    => 'string',
                    'default' => 'Product Collection',
                ],
                'showSubcategories' => [
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'titleFontSize' => [
                    'type'    => 'number',
                    'default' => 32,
                ],
                'titleFontColor' => [
                    'type'    => 'string',
                    'default' => '#000000',
                ],
                'separatorColor' => [
                    'type'    => 'string',
                    'default' => '#dddddd',
                ],
                'separatorThickness' => [
                    'type'    => 'number',
                    'default' => 1,
                ],
                'showPrice' => [
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'showAddToCart' => [
                    'type'    => 'boolean',
                    'default' => true,
                ],
                'productFontSize' => [
                    'type'    => 'number',
                    'default' => 16,
                ],
                'productMargin' => [
                    'type'    => 'number',
                    'default' => 10,
                ],
                'productBorderColor' => [
                    'type'    => 'string',
                    'default' => '#eeeeee',
                ],
                'productBorderStyle' => [
                    'type'    => 'string',
                    'default' => 'solid',
                ],
                'accordionTitleFontSize' => [
                    'type'    => 'number',
                    'default' => 32,
                ],
                'accordionTitleFontColor' => [
                    'type'    => 'string',
                    'default' => '#000000',
                ],
                'accordionCaretColor' => [
                    'type'    => 'string',
                    'default' => '#000000',
                ],
                'accordionCaretImage' => [
                    'type'    => 'string',
                    'default' => '',
                ],
            ],
        ]);
    }
    
    /**
     * Register and enqueue editor-specific scripts
     */
    public function enqueue_editor_scripts() {
        wp_register_script(
            'sxs-wc-block-editor-helper',
            SXS_WC_BLOCKS_PLUGIN_URL . 'src/editor-helper.js',
            ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'],
            filemtime(SXS_WC_BLOCKS_PLUGIN_PATH . 'src/editor-helper.js')
        );
        wp_enqueue_script('sxs-wc-block-editor-helper');
    }
    
    /**
     * Render the block with the given attributes
     * 
     * @param array $attributes Block attributes
     * @return string Rendered HTML
     */
    public static function render_block($attributes = []) {
        return self::get_instance()->render($attributes);
    }

    /**
     * Log content rendering information
     *
     * @param string $content The content being rendered
     * @return string The unmodified content
     */
    public function log_content($content) {
        static $already_logged = false;
        
        if (!$already_logged) {
            sxs_log("WC Block content rendering started\n" . 
                "Context: " . (is_admin() ? 'admin' : 'frontend') . "\n" .
                "URL: " . $_SERVER['REQUEST_URI'] . "\n" .
                "Content: " . print_r($content, true), 'DEBUG');
            $already_logged = true;
        }
        
        return $content;
    }
    
    /**
     * Render the block
     *
     * @param array $attributes Block attributes
     * @return string Rendered block HTML
     */
    public function render($attributes) {
        sxs_log("Entering rendering block function" . "\n" .
            "attributes" . print_r($attributes, true), 'DEBUG');
        
        // Improve context detection
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
        sxs_log(" - Saving global variables." . "\n", 'DEBUG');

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

        // Add CSS variables
        $sxs_plugin_css = $this->generate_css_variables([
            'title_font_size' => $title_font_size,
            'title_font_color' => $title_font_color,
            'separator_color' => $separator_color,
            'separator_thickness' => $separator_thickness,
            'product_font_size' => $product_font_size,
            'product_margin' => $product_margin,
            'product_border_color' => $product_border_color,
            'product_border_style' => $product_border_style,
            'accordion_title_font_size' => $accordion_title_font_size,
            'accordion_title_font_color' => $accordion_title_font_color,
            'accordion_caret_color' => $accordion_caret_color,
            'accordion_caret_image' => $accordion_caret_image,
        ]);
        
        sxs_log(" - accordionCaretImage Parameter : " . 
            print_r($accordion_caret_image, true) . "\n" .
            print_r($sxs_plugin_css, true), 'DEBUG');

        // Use the current query
        $products_query = $wp_query;
        
        // Log query information
        sxs_log("Found Posts: " . $products_query->found_posts, 'DEBUG');
        sxs_log("SQL Query: " . $products_query->request, 'DEBUG');
        
        // Log product IDs if found
        if ($products_query->have_posts()) {
            $product_ids = array();
            foreach ($products_query->posts as $post) {
                $product_ids[] = $post->ID;
            }
            sxs_log("Product IDs: " . implode(', ', $product_ids), 'DEBUG');
        } else {
            sxs_log("No products found.\n", 'DEBUG');
        }
        
        // Organize products by category and subcategory
        $products_by_category = $this->organize_products_by_category($products_query);
        
        // Start output buffering
        ob_start();
        
        // Display content
        ?>
        <div class="wp-block-me-sxs-wc-block">
            <?php echo $sxs_plugin_css; ?>
            <div class="content-wrapper">
                <div class="product-categories">
                <?php 
                // Render products using the organized structure
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

        // Get the content
        $block_content = ob_get_clean();

        // IMPORTANT: Restore all global variables before returning
        sxs_log(" - Restore global variables" . "\n", 'DEBUG');
        $post = $original_post;
        $wp_query = $original_wp_query;
        $wp_the_query = $original_wp_the_query;
        
        return $block_content;
    }
    
    /**
     * Generate CSS variables for the block
     *
     * @param array $options CSS options
     * @return string CSS variables
     */
    private function generate_css_variables($options) {
        return sprintf(
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
            esc_attr($options['title_font_size']),
            esc_attr($options['title_font_color']),
            esc_attr($options['separator_color']),
            esc_attr($options['separator_thickness']),
            esc_attr($options['product_font_size']),
            esc_attr($options['product_margin']),
            esc_attr($options['product_border_color']),
            esc_attr($options['product_border_style']),
            esc_attr($options['accordion_title_font_size']),
            esc_attr($options['accordion_title_font_color']),
            esc_attr($options['accordion_caret_color']),
            esc_attr($options['separator_color']),
            $options['accordion_caret_image'] ? 'url("' . esc_url($options['accordion_caret_image']) . '")' : 'none'
        );
    }
    
    /**
     * Organize products by category and subcategory
     *
     * @param WP_Query $products_query The products query
     * @return array Organized products
     */
    private function organize_products_by_category($products_query) {
        $products_by_category = array();
        sxs_log(" - Organize products by category and subcategory", 'DEBUG');

        if ($products_query->have_posts()) {
            sxs_log(" - while (products_query->have_posts()) ", 'DEBUG');
                
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
            }
            
            // Log summary of products by category
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
        
        // Reset post data after the loop
        wp_reset_postdata();
        
        return $products_by_category;
    }
}

// Initialize the block
function sxs_category_accordion_init() {
    return SXS_Category_Accordion_Block::get_instance();
}

// Register the block
add_action('init', 'sxs_category_accordion_init', 20);

// Legacy function for backward compatibility
function sxs_category_accordion_render($attributes) {
    return SXS_Category_Accordion_Block::get_instance()->render($attributes);
}

        