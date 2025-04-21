<?php
/**
 * Utility functions for SXS WC Blocks
 *
 * @package SXS_WC_Blocks
 */

defined('ABSPATH') || exit;

/**
 * Conditional logging function
 *
 * @param string $message The message to log
 * @param string $level The log level (DEBUG, INFO, WARNING, ERROR)
 */
function sxs_log($message, $level = 'DEBUG') {
    if (!defined('SXS_WC_BLOCKS_DEBUG') || !SXS_WC_BLOCKS_DEBUG) {
        return;
    }

    $allowed_levels = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];
    if (!in_array($level, $allowed_levels)) {
        $level = 'DEBUG';
    }

    $log_dir = SXS_WC_BLOCKS_PLUGIN_PATH . 'log/';
    $log_file = $log_dir . 'me-sxs-block.log';

    // Check if log directory exists, create if not
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }

    // Check if directory is writable
    if (!is_writable($log_dir)) {
        error_log("SXS Debug: Log directory is not writable.");
        return;
    }

    // Check if file is writable
    if (file_exists($log_file) && !is_writable($log_file)) {
        error_log("SXS Debug: Cannot write to log file $log_file.");
        return;
    }

    // Get call context
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = isset($backtrace[1]['function']) ? $backtrace[1]['function'] : 'global scope';

    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] [$level] [$caller] $message\n";

    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Check if content contains specific blocks
 *
 * @param string $content The content to check
 * @param string|array $block_types Block types to look for
 * @return bool
 */
function sxs_has_block_in_content($content, $block_types = '*') {
    if (empty($content)) {
        sxs_log('Empty content in block detection', 'DEBUG');
        return false;
    }

    sxs_log('Block types to search: ' . (is_array($block_types) ? implode(',', $block_types) : $block_types), 'DEBUG');

    $blocks = parse_blocks($content);
    $found = false;

    $search_blocks = function($block) use (&$search_blocks, &$found, $block_types) {
        if (empty($block['blockName'])) {
            return;
        }
        if ($block_types === '*' && strpos($block['blockName'], 'me-sxs-block/') === 0) {
            $found = true;
            sxs_log("Found block - " . $block['blockName'], 'DEBUG');
            return;
        }
        if (is_array($block_types) ? in_array($block['blockName'], $block_types) : $block['blockName'] === $block_types) {
            $found = true;
            sxs_log("Found specific block - " . $block['blockName'], 'DEBUG');
            return;
        }
        if (!empty($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $inner_block) {
                if ($found) break;
                $search_blocks($inner_block);
            }
        }
    };

    foreach ($blocks as $block) {
        if ($found) break;
        $search_blocks($block);
    }
    
    return $found;
}
