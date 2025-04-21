<?php
/**
 * Category Manager Class
 *
 * Handles the processing and organization of product categories.
 *
 * @package SXS_WC_Blocks
 */

defined('ABSPATH') || exit;

class SXS_Category_Manager {
    /**
     * Singleton instance
     *
     * @var SXS_Category_Manager
     */
    private static $instance = null;
    
    /**
     * Category tree
     *
     * @var array
     */
    private $tree = [];
    
    /**
     * Category index
     *
     * @var array
     */
    private $index = [];
    
    /**
     * Categories by level
     *
     * @var array
     */
    private $categories_level = [];
    
    /**
     * Get singleton instance
     *
     * @return SXS_Category_Manager
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
        // Initialize empty tree with root
        $this->tree = [
            0 => [
                'category' => (object) ['term_id' => 0, 'name' => 'Racine', 'parent' => 0],
                'categories' => [],
            ],
        ];
        
        $this->categories_level[0][] = 0; // Root category
    }
    
    /**
     * Get the category tree
     *
     * @return array
     */
    public function get_tree() {
        return $this->tree;
    }
    
    /**
     * Process and organize categories
     */
    public function process_categories() {
        sxs_log('Get and sort categories.', 'DEBUG');
        $categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'DESC',
        ]);
        
        if (is_wp_error($categories)) {
            sxs_log('Error getting categories: ' . $categories->get_error_message(), 'ERROR');
            return;
        }
        
        sxs_log('Non hierarchical sorted categories: ' . print_r($categories, true), 'DEBUG');
        
        // Build index for the tree
        $this->build_index($this->tree, $this->index);
        
        // Assign categories to the tree
        $this->assign_categories_to_tree($this->tree, $categories, $this->index, $this->categories_level);
        
        sxs_log('Sorted categories: ' . print_r($this->tree, true), 'DEBUG');
    }
    
    /**
     * Build an index of categories for quick access
     *
     * @param array $tree
     * @param array $index
     */
    private function build_index(&$tree, &$index) {
        foreach ($tree as $term_id => &$node) {
            $index[$term_id] = &$node; // Create direct reference to the node
            if (!empty($node['categories'])) {
                $this->build_index($node['categories'], $index); // Recursive indexing
            }
        }
    }
    
    /**
     * Assign categories to the tree structure
     *
     * @param array $tree
     * @param array $categories
     * @param array $index
     * @param array $categories_level
     * @param int $tree_level
     */
    private function assign_categories_to_tree(&$tree, &$categories, &$index, &$categories_level, $tree_level = 0) {
        foreach ($categories as $key => $category) {
            if (isset($index[$category->parent])) {
                $parent_node = &$index[$category->parent];
                $parent_node['categories'][$category->term_id] = [
                    'category' => $category,
                    'categories' => []
                ];

                // Add to next level
                $categories_level[$tree_level + 1][] = $category->term_id;

                // Add this category to the index
                $index[$category->term_id] = &$parent_node['categories'][$category->term_id];

                // Remove processed category
                unset($categories[$key]);
            }
        }

        // Check if there are remaining categories to process
        if (!empty($categories)) {
            $tree_level++;
            $this->assign_categories_to_tree($tree, $categories, $index, $categories_level, $tree_level);
        }
    }
}