<?php
if (!defined('ABSPATH')) exit;

class SXS_Product_Display {
    private static $instance = null;
    private $log_file;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->log_file = plugin_dir_path(__FILE__) . '../log/display_debug.log';
    }

    public function get_categories_hierarchy() {
        $parent_categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'parent' => 0,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);

        $categories_data = [];
        foreach ($parent_categories as $category) {
            $subcategories = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => $category->term_id,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);

            $categories_data[] = [
                'parent' => $category,
                'children' => $subcategories
            ];
        }

        return $categories_data;
    }

    public function render_accordion_display() {
        $categories = $this->get_categories_hierarchy();
        ob_start();
        
        foreach ($categories as $category_data) {
            $category = $category_data['parent'];
            $category_ids = [$category->term_id];
            
            foreach ($category_data['children'] as $subcat) {
                $category_ids[] = $subcat->term_id;
            }
            ?>
            <div class="sxs-category-accordion">
                <button class="accordion-toggle" aria-expanded="false">
                    <?php echo esc_html($category->name); ?>
                </button>
                <div class="accordion-content" hidden>
                    <?php
                    woocommerce_product_loop_start();
                    while (have_posts()) {
                        the_post();
                        if (has_term($category_ids, 'product_cat')) {
                            wc_get_template_part('content', 'product');
                        }
                    }
                    woocommerce_product_loop_end();
                    rewind_posts();
                    ?>
                </div>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }

    public function log_display_data($message) {
        if (WP_DEBUG) {
            file_put_contents(
                $this->log_file,
                date('[Y-m-d H:i:s] ') . $message . "\n",
                FILE_APPEND
            );
        }
    }
}
