<?php
/**
 * Admin menu registration for MEDBEAFGALLERY Gallery
 *
 * @package MEDBEAFGALLERY_Gallery
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add MEDBEAFGALLERY Gallery admin menu
 */
function medbeafgallery_admin_menu() {
    // Always add main menu item (dashboard handles license check internally)
    add_menu_page(
        esc_html__('Medical Before After Gallery', 'medical-before-after-gallery'),
        esc_html__('Medical Before After Gallery', 'medical-before-after-gallery'),
        'manage_options',
        'medbeafgallery-gallery',
        'medbeafgallery_admin_page',
        'dashicons-format-gallery',
        30
    );

    // Add submenu items
    add_submenu_page(
        'medbeafgallery-gallery',
        esc_html__('Dashboard', 'medical-before-after-gallery'),
        esc_html__('Dashboard', 'medical-before-after-gallery'),
        'manage_options',
        'medbeafgallery-gallery',
        'medbeafgallery_admin_page'
    );

    // All Cases menu item
    add_submenu_page(
        'medbeafgallery-gallery',
        esc_html__('All Cases', 'medical-before-after-gallery'),
        esc_html__('All Cases', 'medical-before-after-gallery'),
        'manage_options',
        'edit.php?post_type=medbeafgallery_case',
        ''
    );

    add_submenu_page(
        'medbeafgallery-gallery',
        esc_html__('Add New Case', 'medical-before-after-gallery'),
        esc_html__('Add New Case', 'medical-before-after-gallery'),
        'manage_options',
        'post-new.php?post_type=medbeafgallery_case',
        ''
    );

    // Check if the taxonomy exists before adding the menu
    if (taxonomy_exists('medbeafgallery_category')) {
        add_submenu_page(
            'medbeafgallery-gallery',
            esc_html__('Categories', 'medical-before-after-gallery'),
            esc_html__('Categories', 'medical-before-after-gallery'),
            'manage_options',
            'edit-tags.php?taxonomy=medbeafgallery_category&post_type=medbeafgallery_case',
            ''
        );
    }

    // Removed Settings menu item
    // Add Pro Features page
    add_submenu_page(
        'medbeafgallery-gallery',
        esc_html__('Pro Features', 'medical-before-after-gallery'),
        esc_html__('Pro Features', 'medical-before-after-gallery'),
        'manage_options',
        'medbeafgallery-gallery-pro-features',
        'medbeafgallery_pro_features_page'
    );
}

/**
 * Fix submenu highlighting for custom taxonomy pages and post listings
 */
function medbeafgallery_fix_submenu_highlighting($parent_file) {
    global $current_screen, $pagenow;

    // Set the parent file to Medical Before After Gallery when on the medbeafgallery_category taxonomy page
    if ($current_screen->taxonomy === 'medbeafgallery_category') {
        $parent_file = 'medbeafgallery-gallery';
    }

    // Set the parent file to Medical Before After Gallery when on the medbeafgallery_case post type listing or editing pages
    if (($pagenow === 'edit.php' || $pagenow === 'post.php' || $pagenow === 'post-new.php')
        && $current_screen->post_type === 'medbeafgallery_case') {
        $parent_file = 'medbeafgallery-gallery';
    }

    return $parent_file;
}
add_filter('parent_file', 'medbeafgallery_fix_submenu_highlighting');

/**
 * Fix the current submenu highlighting
 */
function medbeafgallery_fix_submenu($submenu_file) {
    global $current_screen, $pagenow;

    // Highlight the All Cases submenu when on the case edit screen
    if ($pagenow === 'post.php' && $current_screen->post_type === 'medbeafgallery_case') {
        $submenu_file = 'edit.php?post_type=medbeafgallery_case';
    }

    return $submenu_file;
}
add_filter('submenu_file', 'medbeafgallery_fix_submenu');

/**
 * Make sure admin menus are registered after taxonomies
 */
function medbeafgallery_register_admin_menu() {
    add_action('admin_menu', 'medbeafgallery_admin_menu');
}
add_action('init', 'medbeafgallery_register_admin_menu', 99); // Register after taxonomies (priority 99)

// Remove the default admin_menu hook since we're registering it later
remove_action('admin_menu', 'medbeafgallery_admin_menu');

/**
 * Pro Features page content
 */
function medbeafgallery_pro_features_page() {
    ?>
    <div class="wrap medbeafgallery-pro-features">
        <h1><?php esc_html_e('Medical Before After Gallery - Pro Features', 'medical-before-after-gallery'); ?></h1>

        <div class="medbeafgallery-pro-hero">
            <div class="hero-content">
                <h2><?php esc_html_e('Unlock the Full Potential of Your Gallery', 'medical-before-after-gallery'); ?></h2>
                <p><?php esc_html_e('Take your medical gallery to the next level with advanced features designed for professional practices.', 'medical-before-after-gallery'); ?></p>

                <div class="hero-buttons">
                    <a href="https://demo.medicalbeforeaftergallery.com/" target="_blank" class="button button-secondary button-large demo-btn">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('View Live Demo', 'medical-before-after-gallery'); ?>
                    </a>
                    <a href="https://medicalbeforeaftergallery.com/" target="_blank" class="button button-primary button-large get-pro-btn">
                        <span class="dashicons dashicons-star-filled"></span>
                        <?php esc_html_e('Get Pro Version', 'medical-before-after-gallery'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="medbeafgallery-features-comparison">
            <h3><?php esc_html_e('Free vs Pro Features', 'medical-before-after-gallery'); ?></h3>
            <p class="subtitle"><?php esc_html_e('Choose the version that works best for your practice', 'medical-before-after-gallery'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="feature-name"><?php esc_html_e('Feature', 'medical-before-after-gallery'); ?></th>
                        <th class="free-version"><?php esc_html_e('Free Version', 'medical-before-after-gallery'); ?></th>
                        <th class="pro-version"><?php esc_html_e('Pro Version', 'medical-before-after-gallery'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e('Basic Before-After Gallery', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Category Filtering', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Image Cropping', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Responsive Design', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Before-After View Switching', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Modal Case Navigation', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Category Navigation', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr class="pro-feature">
                        <td><?php esc_html_e('Multiple Before-After Pairs', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-dismiss feature-not-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr class="pro-feature">
                        <td><?php esc_html_e('Advanced Filtering (Age, Gender, Procedure)', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-dismiss feature-not-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr class="pro-feature">
                        <td><?php esc_html_e('Watermarking Capabilities', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-dismiss feature-not-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr class="pro-feature">
                        <td><?php esc_html_e('Additional Images Carousel', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-dismiss feature-not-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr class="pro-feature">
                        <td><?php esc_html_e('Sensitive Content Warning', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-dismiss feature-not-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr class="pro-feature">
                        <td><?php esc_html_e('Detailed Before-After Case Information', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-dismiss feature-not-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                    <tr class="pro-feature">
                        <td><?php esc_html_e('Premium Support', 'medical-before-after-gallery'); ?></td>
                        <td><span class="dashicons dashicons-dismiss feature-not-included"></span></td>
                        <td><span class="dashicons dashicons-yes-alt feature-included"></span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="medbeafgallery-pro-highlights">
            <h3><?php esc_html_e('Pro Features Highlights', 'medical-before-after-gallery'); ?></h3>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-format-image"></span>
                    </div>
                    <h4><?php esc_html_e('Interactive Before-After Slider', 'medical-before-after-gallery'); ?></h4>
                    <p><?php esc_html_e('Engage visitors with an intuitive slider that reveals the transformation process.', 'medical-before-after-gallery'); ?></p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-grid-view"></span>
                    </div>
                    <h4><?php esc_html_e('Responsive Gallery Grid', 'medical-before-after-gallery'); ?></h4>
                    <p><?php esc_html_e('Beautifully displays your cases in a responsive grid that works on all devices.', 'medical-before-after-gallery'); ?></p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-filter"></span>
                    </div>
                    <h4><?php esc_html_e('Advanced Filtering', 'medical-before-after-gallery'); ?></h4>
                    <p><?php esc_html_e('Allow visitors to filter by procedure type, category, age, and more.', 'medical-before-after-gallery'); ?></p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-shield"></span>
                    </div>
                    <h4><?php esc_html_e('Content Warning', 'medical-before-after-gallery'); ?></h4>
                    <p><?php esc_html_e('Protect sensitive content with customizable content warnings and blurring.', 'medical-before-after-gallery'); ?></p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-editor-code"></span>
                    </div>
                    <h4><?php esc_html_e('Image Cropping Tool', 'medical-before-after-gallery'); ?></h4>
                    <p><?php esc_html_e('Ensure consistent image sizes with the built-in cropping functionality.', 'medical-before-after-gallery'); ?></p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-admin-customizer"></span>
                    </div>
                    <h4><?php esc_html_e('Image Watermarking', 'medical-before-after-gallery'); ?></h4>
                    <p><?php esc_html_e('Protect your valuable images with customizable watermarks (Pro feature).', 'medical-before-after-gallery'); ?></p>
                </div>
            </div>
        </div>

        <div class="medbeafgallery-cta-section">
            <h3><?php esc_html_e('Ready to Upgrade?', 'medical-before-after-gallery'); ?></h3>
            <p><?php esc_html_e('Join hundreds of medical professionals who trust our plugin to showcase their work.', 'medical-before-after-gallery'); ?></p>

            <div class="cta-buttons">
                <a href="https://demo.medicalbeforeaftergallery.com/" target="_blank" class="button button-secondary button-hero demo-btn">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php esc_html_e('View Live Demo', 'medical-before-after-gallery'); ?>
                </a>
                <a href="https://medicalbeforeaftergallery.com/" target="_blank" class="button button-primary button-hero get-pro-btn">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php esc_html_e('Get Pro Version', 'medical-before-after-gallery'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
}