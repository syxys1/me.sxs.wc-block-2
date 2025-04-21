<?php
/**
 * Context Manager Class
 *
 * Handles the detection and management of different execution contexts.
 *
 * @package SXS_WC_Blocks
 */

defined('ABSPATH') || exit;

class SXS_Context_Manager {
    /**
     * Singleton instance
     *
     * @var SXS_Context_Manager
     */
    private static $instance = null;
    
    /**
     * Current context
     *
     * @var int
     */
    private $current_context = 0; // Default to NONE
    
    /**
     * Get singleton instance
     *
     * @return SXS_Context_Manager
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
        // Initialize with NONE context
        $this->current_context = SXS_CONTEXT_NONE;
    }
    
    /**
     * Get current context
     *
     * @return int
     */
    public function get_context() {
        return $this->current_context;
    }
    
    /**
     * Set current context
     *
     * @param int $context
     */
    public function set_context($context) {
        $this->current_context = $context;
    }
    
    /**
     * Detect and set the current context
     *
     * @return bool True if a valid context was detected
     */
    public function detect_context() {
        sxs_log('Entering valid context verification.', 'DEBUG');
        
        // Vérification 4 : Sommes-nous sur une page WooCommerce ou une page normale ?
        global $post;

        if (is_shop()) {
            $shop_page_id = wc_get_page_id('shop');
            if ($shop_page_id > 0) {
                $shop_page = get_post($shop_page_id);
                if ($shop_page && has_blocks($shop_page->post_content)) {
                    if (sxs_has_block_in_content($shop_page->post_content, '*')) {
                        sxs_log('Contexte valide : page boutique avec blocs SXS.', 'DEBUG');
                        $this->set_context(SXS_CONTEXT_SHOP);   
                        return true;
                    }
                }
            }
        } elseif (is_page() && $post) {
            if (has_blocks($post->post_content)) {
                if (sxs_has_block_in_content($post->post_content, '*')) {
                    sxs_log('Contexte valide : page normale avec blocs SXS.', 'DEBUG');
                    $this->set_context(SXS_CONTEXT_PAGE);
                    return true;
                }
            }
        } elseif (is_admin()) {
            // Inclure get_current_screen si nécessaire
            if (!function_exists('get_current_screen')) {
                require_once ABSPATH . 'wp-admin/includes/screen.php';
            }

            $current_screen = get_current_screen();
            if ($current_screen && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) {
                sxs_log('Contexte valide : éditeur de blocs.', 'DEBUG');
                $this->set_context(SXS_CONTEXT_GUTENBERG);
                return true;
            }
        }

        // Si aucune condition n'est satisfaite, retourner false
        sxs_log('Contexte invalide : aucune condition satisfaite.', 'DEBUG');
        $this->set_context(SXS_CONTEXT_NONE);
        return false;
    }
    
    /**
     * Check if current context matches a specific context
     *
     * @param int $context_to_check
     * @return bool
     */
    public function is_context($context_to_check) {
        return $this->current_context === $context_to_check;
    }
    
    /**
     * Check if current context is valid (not NONE)
     *
     * @return bool
     */
    public function has_valid_context() {
        return $this->current_context !== SXS_CONTEXT_NONE;
    }
}