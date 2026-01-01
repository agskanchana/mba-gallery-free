<?php
/**
 * Plugin Name: Medical Before After Gallery
 * Plugin URI: https://medicalbeforeaftergallery.com/
 * Description: Professional before-after image gallery plugin with filtering and categories for healthcare professionals. Free version with core features.
 * Version: 1.3.0
 * Author: Medical Before After Gallery
 * Text Domain: medical-before-after-gallery
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 *
 * @package Medical_Before_After_Gallery
 * @version 1.3.0
 * @author Medical Before After Gallery
 * @copyright Copyright (c) 2024, Medical Before After Gallery
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}


// Define security constants
define('MEDBEAFGALLERY_SECURE', true);

/**
 * Check plugin compatibility and requirements
 */
function medbeafgallery_check_requirements() {
    $requirements = array(
        'php_version' => '7.4',
        'wp_version' => '5.0',
        'mysql_version' => '5.6'
    );

    $errors = array();

    // Check PHP version
    if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
        $errors[] = sprintf(
            /* translators: %1$s: required PHP version, %2$s: current PHP version */
            __('Medical Before After Gallery requires PHP version %1$s or higher. You are running version %2$s.', 'medical-before-after-gallery'),
            $requirements['php_version'],
            PHP_VERSION
        );
    }

    // Check WordPress version
    if (version_compare(get_bloginfo('version'), $requirements['wp_version'], '<')) {
        $errors[] = sprintf(
            /* translators: %1$s: required WordPress version, %2$s: current WordPress version */
            __('Medical Before After Gallery requires WordPress version %1$s or higher. You are running version %2$s.', 'medical-before-after-gallery'),
            $requirements['wp_version'],
            get_bloginfo('version')
        );
    }

    if (!empty($errors)) {
        add_action('admin_notices', function() use ($errors) {
            ?>
            <div class="notice notice-error">
                <p><strong><?php esc_html_e('Medical Before After Gallery cannot be activated:', 'medical-before-after-gallery'); ?></strong></p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        });

        return false;
    }

    return true;
}

// Run compatibility check early
if (!medbeafgallery_check_requirements()) {
    return; // Stop loading if requirements not met
}

/**
 * Uninstall cleanup function
 */
function medbeafgallery_uninstall_cleanup() {
    // Remove plugin options
    delete_option('medbeafgallery_settings');
    delete_option('medbeafgallery_version');
    delete_option('medbeafgallery_db_version');

    // Remove user meta
    delete_metadata('user', 0, 'medbeafgallery_warning_acknowledged', '', true);

    // Remove transients
    delete_transient('medbeafgallery_library_warning');
    delete_transient('medbeafgallery_free_to_pro_migration');

    // Get and remove all custom post type data
    $posts = get_posts(array(
        'post_type' => 'medbeafgallery_case',
        'numberposts' => -1,
        'post_status' => 'any'
    ));

    foreach ($posts as $post) {
        // Delete all meta data first
        $meta_keys = get_post_meta($post->ID);
        foreach ($meta_keys as $key => $value) {
            delete_post_meta($post->ID, $key);
        }

        // Delete the post
        wp_delete_post($post->ID, true);
    }

    // Remove custom taxonomy terms
    $taxonomies = array('medbeafgallery_category');

    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ));

        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomy);
        }
    }

    // Clear any cached data
    wp_cache_flush();

    // Log cleanup completion
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        call_user_func('error_log', 'Medical Before After Gallery: Uninstall cleanup completed');
    }
}

/**
 * Check if all features are enabled (always true for free version)
 */
function medbeafgallery_is_premium_active() {
    return true;
}

/**
 * Get maximum allowed cases (unlimited for free version)
 */
function medbeafgallery_get_max_cases() {
    return -1; // unlimited
}

/**
 * Check if advanced filters are enabled (always true for free version)
 */
function medbeafgallery_advanced_filters_enabled() {
    return true;
}







// Define plugin constants
define('MEDBEAFGALLERY_VERSION', '1.3.0');
define('MEDBEAFGALLERY_DB_VERSION', '1.0.0');
define('MEDBEAFGALLERY_PATH', plugin_dir_path(__FILE__));
define('MEDBEAFGALLERY_URL', plugin_dir_url(__FILE__));
define('MEDBEAFGALLERY_BASENAME', plugin_basename(__FILE__));

/**
 * Database version check and upgrade
 */
function medbeafgallery_check_db_version() {
    $installed_version = get_option('medbeafgallery_db_version', '0.0.0');

    if (version_compare($installed_version, MEDBEAFGALLERY_DB_VERSION, '<')) {
        medbeafgallery_upgrade_database($installed_version);
        update_option('medbeafgallery_db_version', MEDBEAFGALLERY_DB_VERSION);
    }
}
add_action('plugins_loaded', 'medbeafgallery_check_db_version');

/**
 * Handle database upgrades
 */
function medbeafgallery_upgrade_database($from_version) {
    // Example upgrade routines
    if (version_compare($from_version, '1.0.0', '<')) {
        // Upgrade to version 1.0.0
        // Add any database schema changes here
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            call_user_func('error_log', 'Medical Before After Gallery: Database upgraded to version 1.0.0');
        }
    }
}

if (!function_exists('medbeafgallery_enqueue_scripts')) {
// Enqueue scripts and styles
function medbeafgallery_enqueue_scripts() {
    // Only enqueue on pages that need it
    if (!is_admin() && (has_shortcode(get_post()->post_content ?? '', 'medical-before-after-gallery') || is_singular('medbeafgallery_case'))) {
        // Register and enqueue CSS
        wp_register_style('medbeafgallery-css', MEDBEAFGALLERY_URL . 'assets/css/gallery.css', array(), MEDBEAFGALLERY_VERSION);
        wp_enqueue_style('medbeafgallery-css');

        // Register and enqueue JavaScript
        wp_register_script('medbeafgallery-js', MEDBEAFGALLERY_URL . 'assets/js/gallery.js', array('jquery'), MEDBEAFGALLERY_VERSION, true);

        // Create localization data with enhanced security
        $gallery_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medical-before-after-gallery_nonce'),
            'rest_url' => rest_url('medical-before-after-gallery/v1/'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'plugin_url' => MEDBEAFGALLERY_URL,
            'is_admin' => current_user_can('manage_options'),
            'strings' => array(
                'loading' => __('Loading...', 'medical-before-after-gallery'),
                'error' => __('An error occurred. Please try again.', 'medical-before-after-gallery'),
                'no_results' => __('No results found.', 'medical-before-after-gallery'),
            )
        );

        // Localize script with data
        wp_localize_script('medbeafgallery-js', 'medbeafgalleryData', $gallery_data);

        // Enqueue script
        wp_enqueue_script('medbeafgallery-js');
    }
}
add_action('wp_enqueue_scripts', 'medbeafgallery_enqueue_scripts');
}

/**
 * Enhanced security: Add nonce verification for AJAX calls
 */
function medbeafgallery_verify_ajax_nonce() {
    $action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';

    if (strpos($action, 'medical-before-after-gallery') !== false) {
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'medical-before-after-gallery_nonce')) {
            wp_die(esc_html__('Security check failed', 'medical-before-after-gallery'), esc_html__('Security Error', 'medical-before-after-gallery'), array('response' => 403));
        }
    }
}
add_action('wp_ajax_nopriv_medbeafgallery_get_cases', 'medbeafgallery_verify_ajax_nonce', 1);
add_action('wp_ajax_medbeafgallery_get_cases', 'medbeafgallery_verify_ajax_nonce', 1);

/**
 * Log errors for debugging (only in debug mode)
 */
function medbeafgallery_log_error($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $log_message = '[Medical Before After Gallery] ' . $message;
        if ($data) {
            $log_message .= ' Data: ' . wp_json_encode($data);
        }
        call_user_func('error_log', $log_message);
    }
}

/**
 * Handle plugin errors gracefully
 */
function medbeafgallery_handle_error($error_message, $context = '') {
    // Log the error
    medbeafgallery_log_error($error_message, $context);

    // Return user-friendly message
    if (current_user_can('manage_options')) {
        /* translators: %s: error message */
        return sprintf(esc_html__('Medical Before After Gallery Error: %s', 'medical-before-after-gallery'), esc_html($error_message));
    } else {
        return esc_html__('Gallery temporarily unavailable. Please try again later.', 'medical-before-after-gallery');
    }
}

/**
 * Performance optimization: Cache gallery data
 */
function medbeafgallery_get_cached_data($cache_key, $callback, $expiration = 3600) {
    $cached_data = get_transient($cache_key);

    if (false === $cached_data) {
        $cached_data = call_user_func($callback);
        set_transient($cache_key, $cached_data, $expiration);
    }

    return $cached_data;
}

/**
 * Clear gallery cache when posts are updated
 */
function medbeafgallery_clear_cache($post_id) {
    if (get_post_type($post_id) === 'medbeafgallery_case') {
        delete_transient('medbeafgallery_cases_cache');
        delete_transient('medbeafgallery_categories_cache');
    }
}
add_action('save_post', 'medbeafgallery_clear_cache');
add_action('delete_post', 'medbeafgallery_clear_cache');



// Register uninstall hook
register_uninstall_hook(__FILE__, 'medbeafgallery_uninstall_cleanup');

// Register activation hook
function medbeafgallery_activate() {
    // Create custom post type
    medbeafgallery_register_post_types();

    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'medbeafgallery_activate');

// Register deactivation hook
function medbeafgallery_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'medbeafgallery_deactivate');

/**
 * Check for required image libraries on plugin activation
 */
function medbeafgallery_check_libraries_on_activation() {
    $image_libraries = medbeafgallery_check_image_libraries();

    if (!$image_libraries['has_required_library']) {
        set_transient('medbeafgallery_library_warning', true, 60 * 60 * 24); // 1 day notice
    }
}
register_activation_hook(__FILE__, 'medbeafgallery_check_libraries_on_activation');

/**
 * Display admin notice if libraries are missing
 */
function medbeafgallery_library_warning_notice() {
    if (get_transient('medbeafgallery_library_warning')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php esc_html_e('Medical Before After Gallery Warning:', 'medical-before-after-gallery'); ?></strong>
                <?php esc_html_e('Your server configuration may limit some functionality. Please contact your hosting provider if you experience issues.', 'medical-before-after-gallery'); ?>
            </p>
        </div>
        <?php
        delete_transient('medbeafgallery_library_warning');
    }
}
add_action('admin_notices', 'medbeafgallery_library_warning_notice');

/**
 * Plugin health check - run diagnostics
 */
function medbeafgallery_health_check() {
    $health_status = array(
        'php_version' => version_compare(PHP_VERSION, '7.4', '>='),
        'wp_version' => version_compare(get_bloginfo('version'), '5.0', '>='),
        'image_library' => function_exists('gd_info') || extension_loaded('imagick'),
        'memory_limit' => wp_convert_hr_to_bytes(ini_get('memory_limit')) >= 134217728, // 128MB
        'upload_dir_writable' => wp_is_writable(wp_upload_dir()['basedir']),
    );

    return $health_status;
}

/**
 * Display health check results in admin
 */
function medbeafgallery_admin_health_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $screen = get_current_screen();
    if ($screen->id !== 'toplevel_page_medbeafgallery') {
        return;
    }

    $health = medbeafgallery_health_check();
    $has_issues = array_search(false, $health, true) !== false;

    if ($has_issues) {
        ?>
        <div class="notice notice-warning">
            <h3><?php esc_html_e('Medical Before After Gallery Health Check', 'medical-before-after-gallery'); ?></h3>
            <ul>
                <?php if (!$health['php_version']): ?>
                    <li><?php esc_html_e('⚠️ PHP version should be 7.4 or higher for optimal performance', 'medical-before-after-gallery'); ?></li>
                <?php endif; ?>
                <?php if (!$health['wp_version']): ?>
                    <li><?php esc_html_e('⚠️ WordPress version should be 5.0 or higher', 'medical-before-after-gallery'); ?></li>
                <?php endif; ?>
                <?php if (!$health['image_library']): ?>
                    <li><?php esc_html_e('⚠️ No image processing library detected.', 'medical-before-after-gallery'); ?></li>
                <?php endif; ?>
                <?php if (!$health['memory_limit']): ?>
                    <li><?php esc_html_e('⚠️ Memory limit may be too low for processing large images', 'medical-before-after-gallery'); ?></li>
                <?php endif; ?>
                <?php if (!$health['upload_dir_writable']): ?>
                    <li><?php esc_html_e('⚠️ Upload directory is not writable', 'medical-before-after-gallery'); ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php
    }
}
add_action('admin_notices', 'medbeafgallery_admin_health_notice');

// Include required files
require_once MEDBEAFGALLERY_PATH . 'includes/utilities.php';
require_once MEDBEAFGALLERY_PATH . 'includes/post-types.php';
require_once MEDBEAFGALLERY_PATH . 'includes/shortcodes.php';
require_once MEDBEAFGALLERY_PATH . 'includes/admin-functions.php';
require_once MEDBEAFGALLERY_PATH . 'includes/rest.php';
require_once MEDBEAFGALLERY_PATH . 'includes/ajax-handlers.php';

// Include admin files
if (is_admin()) {
    require_once MEDBEAFGALLERY_PATH . 'admin/metaboxes.php';
}

// Call this on plugin activation
register_activation_hook(__FILE__, 'medbeafgallery_create_default_categories');
