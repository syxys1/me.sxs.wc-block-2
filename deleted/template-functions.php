<?php
defined('ABSPATH') || exit;

/**
 * Override WooCommerce templates with our custom versions
 */
function sxs_wc_override_template($template, $template_name, $template_path) {
    $plugin_template_path = SXS_WC_BLOCKS_PLUGIN_PATH . 'templates/';
    
    // Only override specific templates
    $templates_to_override = array(
        'archive-product.php'
    );
    
    if (in_array($template_name, $templates_to_override)) {
        $template = $plugin_template_path . $template_name;
    }
    
    return $template;
}

/**
 * Register template settings
 */
function sxs_register_template_settings() {
    register_setting('sxs_wc_blocks_options', 'sxs_wc_blocks_options');
    
    add_settings_section(
        'sxs_wc_blocks_templates_section',
        __('Template Settings', 'sxs-wc-blocks'),
        'sxs_templates_section_callback',
        'sxs-wc-blocks'
    );
    
    add_settings_field(
        'override_shop_template',
        __('Override Shop Template', 'sxs-wc-blocks'),
        'sxs_override_shop_template_callback',
        'sxs-wc-blocks',
        'sxs_wc_blocks_templates_section'
    );
}

function sxs_templates_section_callback() {
    echo '<p>' . __('Configure how templates are handled.', 'sxs-wc-blocks') . '</p>';
}

function sxs_override_shop_template_callback() {
    $options = get_option('sxs_wc_blocks_options');
    $checked = isset($options['override_shop_template']) ? 'checked' : '';
    
    echo '<input type="checkbox" id="override_shop_template" name="sxs_wc_blocks_options[override_shop_template]" ' . $checked . ' />';
    echo '<label for="override_shop_template">' . __('Use custom shop template', 'sxs-wc-blocks') . '</label>';
}

function sxs_should_override_template() {
    $options = get_option('sxs_wc_blocks_options');
    return isset($options['override_shop_template']) && $options['override_shop_template'];
}

function sxs_wc_locate_template($template, $template_name, $template_path) {
    if (!sxs_should_override_template()) {
        return $template;
    }
    
    return sxs_wc_override_template($template, $template_name, $template_path);
}

function sxs_get_archive_product_template() {
    return SXS_WC_BLOCKS_PLUGIN_PATH . 'templates/archive-product.php';
}

function sxs_register_block_template() {
    if (!sxs_should_override_template()) {
        return;
    }
    
    $template_path = sxs_get_archive_product_template();
    if (!file_exists($template_path)) {
        return;
    }
    
    $template_content = file_get_contents($template_path);
    
    wp_register_block_template(
        'sxs-wc-blocks/archive-product',
        array(
            'title' => __('SXS Archive Product', 'sxs-wc-blocks'),
            'content' => $template_content,
        )
    );
}

function sxs_filter_block_templates($templates) {
    if (!sxs_should_override_template()) {
        return $templates;
    }
    
    foreach ($templates as $key => $template) {
        if ($template->slug === 'archive-product') {
            unset($templates[$key]);
            break;
        }
    }
    
    return $templates;
}

/**
 * Remove default WooCommerce template actions to avoid duplication
 */
function sxs_remove_default_woocommerce_actions() {
    // Only on shop/archive pages
    if (!is_product_category() && !is_product_tag() && !is_shop()) {
        return;
    }
    
    // Remove archive description
    remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
    remove_action('woocommerce_archive_description', 'woocommerce_product_archive_description', 10);
    
    // Remove shop loop elements
    remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    
    // Remove the main shop loop completely
    remove_action('woocommerce_shop_loop', 'WC_Template_Loader::shop_loop', 10);
    
    // Remove no products found message
    remove_action('woocommerce_no_products_found', 'wc_no_products_found', 10);
    
    // Remove pagination
    remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
}
add_action('wp', 'sxs_remove_default_woocommerce_actions', 20);

function sxs_remove_default_woocommerce_actions2() {
    // Uniquement pour les pages de produits
    if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    
    // Actions à supprimer
    $actions_to_remove = array(
        'woocommerce_before_main_content' => array(
            array('woocommerce_output_content_wrapper', 10),
            array('woocommerce_breadcrumb', 20)
        ),
        'woocommerce_after_main_content' => array(
            array('woocommerce_output_content_wrapper_end', 10)
        ),
        'woocommerce_sidebar' => array(
            array('woocommerce_get_sidebar', 10)
        )
    );
    
    // Suppression des actions
    foreach ($actions_to_remove as $hook => $callbacks) {
        foreach ($callbacks as $callback) {
            remove_action($hook, $callback[0], $callback[1]);
        }
    }
}

// Add required hooks
add_filter('woocommerce_locate_template', 'sxs_wc_locate_template', 20, 3);
add_action('admin_init', 'sxs_register_template_settings');
add_action('init', 'sxs_register_block_template');
add_filter('get_block_templates', 'sxs_filter_block_templates', 10, 1);
add_action('wp', 'sxs_remove_default_woocommerce_actions', 20);