<?php
/**
 * Plugin Name: SXS WC Blocks
 * Description: SXS Custom WooCommerce blocks
 * Version: 1.0.3
 * Author: Sylvain Plante
 * License: GPL-3.0-or-later
 * Text Domain: sxs-wc-block
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * WC requires at least: 5.0.0
 * WC tested up to: 8.6.0
 * WC HPOS Compatible: yes
 */

defined('ABSPATH') || exit;

// Define plugin constants
if (!defined('SXS_WC_BLOCKS_PLUGIN_PATH')) {
    define('SXS_WC_BLOCKS_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('SXS_WC_BLOCKS_PLUGIN_URL')) {
    define('SXS_WC_BLOCKS_PLUGIN_URL', plugin_dir_url(__FILE__));
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

// Load the main plugin class
require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/sxs-functions.php';
require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/class-sxs-context-manager.php';
require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/class-sxs-category-manager.php';
require_once SXS_WC_BLOCKS_PLUGIN_PATH . 'includes/class-sxs-wc-blocks.php';

// Register activation hook
register_activation_hook(__FILE__, ['SXS_WC_Blocks', 'on_activation']);

// Initialize the plugin
function sxs_wc_blocks_init() {
    return SXS_WC_Blocks::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'sxs_wc_blocks_init');