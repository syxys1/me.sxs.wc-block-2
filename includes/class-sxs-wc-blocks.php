<?php
/**
 * Main Plugin Class
 *
 * @package SXS_WC_Blocks
 */

defined('ABSPATH') || exit;

class SXS_WC_Blocks {
    /**
     * Singleton instance
     *
     * @var SXS_WC_Blocks
     */
    private static $instance = null;
    
    /**
     * Context manager instance
     *
     * @var SXS_Context_Manager
     */
    private $context_manager;
    
    /**
     * Category manager instance
     *
     * @var SXS_Category_Manager
     */
    private $category_manager;

    /**
     * Block instances
     *
     * @var array
     */
    private $blocks = [];
    
    /**
     * Get singleton instance
     *
     * @return SXS_WC_Blocks
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
        $this->define_constants();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     */
    private function define_constants() {
        if (!defined('SXS_WC_BLOCKS_PLUGIN_PATH')) {
            define('SXS_WC_BLOCKS_PLUGIN_PATH', plugin_dir_path(dirname(__FILE__)));
        }
        if (!defined('SXS_WC_BLOCKS_PLUGIN_URL')) {
            define('SXS_WC_BLOCKS_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
        }
        if (!defined('SXS_WC_BLOCKS_DEBUG')) {
            define('SXS_WC_BLOCKS_DEBUG', true); // Set to true in development
        }
        if (!defined('SXS_CONTEXT_NONE')) {
            define('SXS_CONTEXT_NONE', 0); // No valid context
        }
        if (!defined('SXS_CONTEXT_GUTENBERG')) {
            define('SXS_CONTEXT_GUTENBERG', 1); // In Gutenberg Editor
        }
        if (!defined('SXS_CONTEXT_SHOP')) {
            define('SXS_CONTEXT_SHOP', 2); // On front end shop page
        }
        if (!defined('SXS_CONTEXT_PAGE')) {
            define('SXS_CONTEXT_PAGE', 3); // On front end page
        }
        if (!defined('SXS_IS_ACTIVATION')) {
            define('SXS_IS_ACTIVATION', false);
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load dependencies first
        $this->load_dependencies();
        
        // Initialize managers
        $this->context_manager = SXS_Context_Manager::get_instance();
        $this->category_manager = SXS_Category_Manager::get_instance();

        // Initialize blocks
        $this->init_blocks();
        
        // HPOS Compatibility
        add_action('before_woocommerce_init', [$this, 'declare_hpos_compatibility']);
        
        // Plugin initialization
        //add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
        
        // Block category registration
        add_action('enqueue_block_editor_assets', [$this, 'register_block_category']);
        
        // Plugin initialization
        //add_action('init', [$this, 'register_blocks']);
        
        // Context detection
        add_action('template_redirect', [$this, 'detect_context']);
        
        // Category processing
        add_action('pre_get_posts', [$this, 'process_categories'], 10);
        
        // Query modification
        add_action('pre_get_posts', [$this, 'modify_product_query'], 20);
        
        // Template handling
        add_filter('template_include', [$this, 'handle_template'], 11);
        
        // Style enqueuing
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        
        // Shop page content
        add_action('woocommerce_before_shop_loop', [$this, 'display_shop_content'], 10);
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load utility functions
        require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/sxs-functions.php';
        
        // Load manager classes
        require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/class-sxs-context-manager.php';
        require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/class-sxs-category-manager.php';
           // Load block files
        require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/class-sxs-category-accordion.php';
    }
    
    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', SXS_WC_BLOCKS_PLUGIN_PATH . 'me-sxs-block.php', true);
        }
    }
    
    /**
     * Plugin activation callback
     */
    public static function on_activation() {
        self::check_woocommerce(true);
    }
    
    /**
     * Plugins loaded callback
     */
    public function on_plugins_loaded() {
        sxs_log('Loading required category-accordion.php : ' . (self::check_woocommerce(false) ? 'true' : 'false'), 'DEBUG');
         if (!self::check_woocommerce(false)) {
            return;
        }
        sxs_log('Loading required category-accordion.php : ' . (self::check_woocommerce(false) ? 'true' : 'false'), 'DEBUG');
        // Load block files when WooCommerce is active
        require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/blocks/category-accordion.php';
    }
    
    /**
     * Check if WooCommerce is active
     *
     * @param bool $is_activation Whether this is during plugin activation
     * @return bool
     */
    public static function check_woocommerce($is_activation = false) {
        sxs_log('Checking WooCommerce with is_activation: ' . ($is_activation ? 'true' : 'false'), 'DEBUG');
        
        if (!class_exists('WooCommerce')) {
            sxs_log('WooCommerce is not active.', 'DEBUG');
            
            if (!function_exists('deactivate_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            
            // Deactivate plugin if WooCommerce is not active
            deactivate_plugins(plugin_basename(SXS_WC_BLOCKS_PLUGIN_PATH . 'me-sxs-block.php'));
            
            if ($is_activation) {
                wp_die(
                    __('SXS WC Blocks requires WooCommerce to function. Please activate WooCommerce before activating this plugin.', 'sxs-wc-blocks'),
                    __('Error: WooCommerce required', 'sxs-wc-blocks'),
                    ['back_link' => true]
                );
            } else {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error"><p>';
                    _e('SXS WC Blocks has been deactivated because WooCommerce is no longer active. Please reactivate WooCommerce to use this plugin.', 'sxs-wc-blocks');
                    echo '</p></div>';
                });
            }
            
            return false;
        }
        
        sxs_log('WooCommerce is active.', 'DEBUG');
        return true;
    }
    
    /**
     * Register block category
     */
    public function register_block_category() {
        sxs_log('Registering block category.', 'DEBUG');
        
        if (version_compare(get_bloginfo('version'), '5.8', '>=')) {
            add_filter('block_categories_all', [$this, 'add_block_category'], 10, 2);
        } else {
            add_filter('block_categories', [$this, 'add_block_category'], 10, 2);
        }
    }
    
    /**
     * Add block category
     *
     * @param array $categories
     * @return array
     */
    public function add_block_category($categories) {
        return array_merge($categories, [
            [
                'slug' => 'me-sxs-category',
                'title' => __('SXS WC Blocks', 'me-sxs-block'),
                'icon' => 'category',
            ],
        ]);
    }
    
    /**
     * Initialize plugin
     */
    public function init_plugin() {
        sxs_log('Plugin initialization started.', 'DEBUG');
        
        // Register blocks
        $this->register_blocks();
        
        sxs_log('Plugin initialization completed.', 'DEBUG');
    }

    /**
     * Initialize blocks
     */
    private function init_blocks() {
        // Initialize Category Accordion block
        $this->blocks['category_accordion'] = SXS_Category_Accordion_Block::get_instance();
        
        // Add other blocks here as needed
    }
    
    /**
     * Register blocks
     */
    public function register_blocks() {
        sxs_log('Registering blocks.', 'DEBUG');
        
        $blocks = [
            'me-sxs-block/category-accordion' => [
                'editor_script'   => 'sxs-category-accordion-editor-script',
                'editor_style'    => 'sxs-category-accordion-editor-style',
                'style'           => 'sxs-category-accordion-style',
                'render_callback' => 'sxs_category_accordion_render',
                'attributes'      => [
                    'excludeCategories' => [
                        'type'    => 'array',
                        'default' => [],
                    ],
                ],
            ],
        ];
        
        foreach ($blocks as $name => $args) {
            // Register styles and scripts for each block
            $block_slug = str_replace('me-sxs-block/', '', $name);
            
            // Editor styles
            $src_dir = file_exists(SXS_WC_BLOCKS_PLUGIN_PATH . "build/{$block_slug}-editor.css") 
                ? "build/" : "css/";
            if (file_exists(SXS_WC_BLOCKS_PLUGIN_PATH . $src_dir . "{$block_slug}-editor.css")) {
                wp_register_style(
                    "sxs-{$block_slug}-editor-style",
                    SXS_WC_BLOCKS_PLUGIN_URL . $src_dir . "{$block_slug}-editor.css",
                    ['wp-edit-blocks'],
                    filemtime(SXS_WC_BLOCKS_PLUGIN_PATH . $src_dir . "{$block_slug}-editor.css")
                );
            }
            
            // Frontend styles
            $src_dir = file_exists(SXS_WC_BLOCKS_PLUGIN_PATH . "build/{$block_slug}.css") 
                ? "build/" : "css/";
            if (file_exists(SXS_WC_BLOCKS_PLUGIN_PATH . $src_dir . "{$block_slug}.css")) {
                wp_register_style(
                    "sxs-{$block_slug}-style",
                    SXS_WC_BLOCKS_PLUGIN_URL . $src_dir . "{$block_slug}.css",
                    [],
                    filemtime(SXS_WC_BLOCKS_PLUGIN_PATH . $src_dir . "{$block_slug}.css")
                );
            }
            
            // Editor script
            $src_dir = file_exists(SXS_WC_BLOCKS_PLUGIN_PATH . "build/{$block_slug}-editor.js") 
                ? "build/" : "js/";
            if (file_exists(SXS_WC_BLOCKS_PLUGIN_PATH . $src_dir . "{$block_slug}-editor.js")) {
                wp_register_script(
                    "sxs-{$block_slug}-editor-script",
                    SXS_WC_BLOCKS_PLUGIN_URL . $src_dir . "{$block_slug}-editor.js",
                    ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                    filemtime(SXS_WC_BLOCKS_PLUGIN_PATH . $src_dir . "{$block_slug}-editor.js"),
                    true
                );
            }
            
            // Register the block
            register_block_type($name, $args);
            // Dans la méthode register_blocks de SXS_WC_Blocks
            sxs_log('Block registered: ' . $name, 'INFO');
            sxs_log('Block render callback: ' . (is_callable($args['render_callback']) ? 'is callable' : 'not callable'), 'INFO');

        }
    }
    
    /**
     * Detect context
     */
    public function detect_context() {
        $this->context_manager->detect_context();
        sxs_log('Context detected: ' . $this->context_manager->get_context(), 'DEBUG');
    }
    
    /**
     * Process categories
     *
     * @param WP_Query $query
     */
    public function process_categories($query) {
            // Seulement traiter la requête principale et seulement sur la page shop
        if (!$query->is_main_query() || !is_shop()) {
            return;
        }
        if ($this->context_manager->is_context(SXS_CONTEXT_SHOP)) {
            $this->category_manager->process_categories();
        }
    }
    
    /**
     * Modify product query
     *
     * @param WP_Query $query
     */
    public function modify_product_query($query) {
            // Seulement traiter la requête principale et seulement sur la page shop
        if (!$query->is_main_query() || !is_shop()) {
            return;
        }
        if ($this->context_manager->is_context(SXS_CONTEXT_SHOP)) {
            sxs_log('Modifying query.', 'DEBUG');
            
            // Only proceed if it's the main query
            if ($query->is_main_query() && is_shop()) {
                // Configure hierarchical sorting
                $query->set('orderby', [
                    'menu_order' => 'ASC',
                    'tax_product_cat' => 'ASC', 
                    'title' => 'ASC'
                ]);

                // Configure category hierarchy
                $tax_query = [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => get_queried_object_id(),
                    'include_children' => true
                ];

                $query->set('tax_query', $tax_query);
            }
        }
    }
    
    /**
     * Handle template
     *
     * @param string $template
     * @return string
     */
    public function handle_template($template) {
        if ($this->context_manager->is_context(SXS_CONTEXT_SHOP)) {
            sxs_log('Template: template-a', 'DEBUG');
            return $this->get_template('template-a');
        }
        
        return $template;
    }
    

    /**
     * Get template
     *
     * @param string $template_name
     * @param string $default
     * @return string
     */
    public function get_template($template_name, $default = 'template-a') {
        $template_file = SXS_WC_BLOCKS_PLUGIN_PATH . "templates/{$template_name}.php";
        
        if (file_exists($template_file)) {
            sxs_log("Template file included: {$template_file}", 'DEBUG');
            return $template_file;
        } else {
            sxs_log("Template file missing: {$template_file}", 'WARNING');
            return '';
        }
    }
    
    /**
     * Enqueue styles
     */
    public function enqueue_styles() {
        if ($this->context_manager->is_context(SXS_CONTEXT_SHOP)) {
            sxs_log('Enqueuing styles.', 'DEBUG');
            wp_enqueue_style(
                'sxs-category-accordion-style',
                SXS_WC_BLOCKS_PLUGIN_URL . 'css/category-accordion.css',
                [],
                filemtime(SXS_WC_BLOCKS_PLUGIN_PATH . 'css/category-accordion.css')
            );
        }
    }
    
    /**
     * Display shop content
     */
    public function display_shop_content() {
        // Get Shop page ID
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id && $shop_post = get_post($shop_page_id)) {
            // Check if Shop page has content
            if (!empty($shop_post->post_content)) {
                echo '<div class="shop-page-content">';
                echo apply_filters('the_content', $shop_post->post_content);
                echo '</div>';
            }
        }
    }
}
