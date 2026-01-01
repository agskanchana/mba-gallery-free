<?php
/**
 * Admin dashboard page for MEDBEAFGALLERY Gallery
 *
 * @package MEDBEAFGALLERY_Gallery
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files
require_once MEDBEAFGALLERY_PATH . 'admin/dashboard/statistics.php';
require_once MEDBEAFGALLERY_PATH . 'admin/dashboard/help-guide.php';
require_once MEDBEAFGALLERY_PATH . 'admin/dashboard/settings.php'; // Add this line to include settings

/**
 * Display the admin dashboard page
 */
function medbeafgallery_admin_page() {
    // Initialize with safe defaults and error handling
    $stats = array();

    // Try to get statistics safely
    if (function_exists('medbeafgallery_get_dashboard_statistics')) {
        try {
            $stats = medbeafgallery_get_dashboard_statistics();
        } catch (Exception $e) {
            $stats = array();
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                call_user_func('error_log', 'MEDBEAFGALLERY Gallery Dashboard Statistics Error: ' . $e->getMessage());
            }
        }
    }

    // Try to process settings safely
    if (function_exists('medbeafgallery_process_settings')) {
        try {
            medbeafgallery_process_settings();
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                call_user_func('error_log', 'MEDBEAFGALLERY Gallery Settings Processing Error: ' . $e->getMessage());
            }
        }
    }

    // Get current settings
    $settings = get_option('medbeafgallery_settings', array(
        'cropping_enabled' => false,
        'cropping_size' => '300',
        'gallery_primary_color' => '#3498db',
        'category_display_mode' => 'grid'
    ));
    ?>
    <div class="wrap medbeafgallery-admin-wrap">
        <h1><?php esc_html_e('Medical Before After Gallery Dashboard', 'medical-before-after-gallery'); ?></h1>

        <div class="medbeafgallery-admin-header">
            <div class="medbeafgallery-admin-header-info">
                <h2><?php esc_html_e('Before & After Gallery Management', 'medical-before-after-gallery'); ?></h2>
                <p><?php esc_html_e('Manage your before and after gallery cases, categories, and settings.', 'medical-before-after-gallery'); ?></p>
            </div>
            <div class="medbeafgallery-admin-header-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=medbeafgallery_case')); ?>" class="button button-primary">
                    <?php esc_html_e('Add New Case', 'medical-before-after-gallery'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=medbeafgallery_category&post_type=medbeafgallery_case')); ?>" class="button">
                    <?php esc_html_e('Manage Categories', 'medical-before-after-gallery'); ?>
                </a>
            </div>
        </div>



        <div class="medbeafgallery-admin-content">
            <div class="medbeafgallery-admin-main">
                <?php
                // Display statistics boxes (1st)
                medbeafgallery_display_statistics($stats);

                // Display help guide (2nd)
                medbeafgallery_display_help_guide();

                // Display settings form (3rd)
                medbeafgallery_display_settings_form_main($settings);

                // Display pro features (4th)
                medbeafgallery_display_pro_features();
                ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Process settings form submission including cropping settings
 */
function medbeafgallery_process_settings() {
    // Check if form was submitted
    if (isset($_POST['medbeafgallery_save_settings']) && check_admin_referer('medbeafgallery_settings_nonce')) {
        // Get current settings or set defaults
        $settings = get_option('medbeafgallery_settings', array());

        // Explicitly handling checkboxes - they're only included in $_POST when checked

        // Image cropping settings - IMPORTANT: Save correctly
        $settings['cropping_enabled'] = isset($_POST['cropping_enabled']) ? true : false;
        $settings['cropping_size'] = isset($_POST['cropping_size']) ? absint($_POST['cropping_size']) : 800;
        $settings['cropping_size'] = max(300, min(2000, $settings['cropping_size']));

        // Design settings - Support both hex colors and gradients
        $gallery_color = isset($_POST['gallery_primary_color']) ? sanitize_text_field(wp_unslash($_POST['gallery_primary_color'])) : '#3498db';

        // Validate color input - allow hex colors and CSS gradients
        if (strpos($gallery_color, 'gradient') !== false) {
            // For gradients, ensure it's a valid CSS gradient
            $settings['gallery_primary_color'] = $gallery_color;
        } else {
            // For regular colors, ensure it's a valid hex color
            $settings['gallery_primary_color'] = sanitize_hex_color($gallery_color) ?: '#3498db';
        }
        $settings['category_display_mode'] = isset($_POST['category_display_mode']) && in_array($_POST['category_display_mode'], ['grid', 'carousel']) ? sanitize_text_field(wp_unslash($_POST['category_display_mode'])) : 'grid';

        // Add debug lines for cropping settings
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            call_user_func('error_log', 'MEDBEAFGALLERY Gallery: Cropping form submission - enabled: ' . (isset($_POST['cropping_enabled']) ? 'yes' : 'no'));
            call_user_func('error_log', 'MEDBEAFGALLERY Gallery: Final cropping setting saved: ' . ($settings['cropping_enabled'] ? 'enabled' : 'disabled'));
        }

        // Save updated settings
        update_option('medbeafgallery_settings', $settings);

        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            call_user_func('error_log', 'MEDBEAFGALLERY Gallery: Settings saved - Cropping enabled: ' . ($settings['cropping_enabled'] ? 'yes' : 'no'));
        }

        // Add admin notice
        add_action('admin_notices', 'medbeafgallery_settings_saved_notice');
    }
}

/**
 * Display admin notice when settings are saved
 */
function medbeafgallery_settings_saved_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e('Gallery settings saved successfully!', 'medical-before-after-gallery'); ?></p>
    </div>
    <?php
}

/**
 * Display the settings form with added watermarking section
 */
function medbeafgallery_display_settings_form_main($settings) {
    // Add default values if not set
    $settings = wp_parse_args($settings, array(
        // Default cropping settings
        'cropping_enabled' => false,
        'cropping_size' => '300',
        // Default design settings
        'gallery_primary_color' => '#3498db',
        'category_display_mode' => 'grid'
    ));
    ?>
    <div class="medbeafgallery-admin-box medbeafgallery-settings-form">
        <h2><?php esc_html_e('Gallery Settings', 'medical-before-after-gallery'); ?></h2>

        <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=medbeafgallery-gallery')); ?>">
            <?php wp_nonce_field('medbeafgallery_settings_nonce'); ?>

            <!-- Updated Cropping Section -->
            <h3><?php esc_html_e('Image Cropping', 'medical-before-after-gallery'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Cropping', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <label for="cropping_enabled">
                            <input type="checkbox" name="cropping_enabled" id="cropping_enabled" value="1" <?php checked(!empty($settings['cropping_enabled'])); ?>>
                            <?php esc_html_e('Enable square image cropping for before/after images', 'medical-before-after-gallery'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('This will provide a cropping interface when uploading images.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Cropping Size', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="number" name="cropping_size" id="cropping_size" min="300" max="2000" step="50" value="<?php echo esc_attr($settings['cropping_size']); ?>" class="small-text"> px
                        <p class="description"><?php esc_html_e('Output size for cropped images (width = height for square aspect ratio). Minimum 300px, maximum 2000px.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
            </table>

            <hr>

            <!-- New Design Section -->
            <h3><?php esc_html_e('Design', 'medical-before-after-gallery'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="gallery_primary_color"><?php esc_html_e('Gallery Color', 'medical-before-after-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="gallery_primary_color" id="gallery_primary_color"
                               value="<?php echo esc_attr($settings['gallery_primary_color'] ?? '#3498db'); ?>"
                               class="medbeafgallery-color-picker regular-text">
                        <div class="medbeafgallery-color-presets">
                            <p><strong>Quick Colors:</strong></p>
                            <div class="medbeafgallery-preset-colors">
                                <button type="button" class="medbeafgallery-preset-color" data-color="#3498db" style="background: #3498db" title="Blue"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="#e74c3c" style="background: #e74c3c" title="Red"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="#2ecc71" style="background: #2ecc71" title="Green"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="#f39c12" style="background: #f39c12" title="Orange"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="#9b59b6" style="background: #9b59b6" title="Purple"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="#1abc9c" style="background: #1abc9c" title="Teal"></button>
                            </div>
                            <p><strong>Gradients:</strong></p>
                            <div class="medbeafgallery-preset-gradients">
                                <button type="button" class="medbeafgallery-preset-color" data-color="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)" title="Purple Blue"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" title="Pink Red"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" title="Blue Cyan"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)" title="Green Teal"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="linear-gradient(135deg, #fa709a 0%, #fee140 100%)" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%)" title="Pink Yellow"></button>
                                <button type="button" class="medbeafgallery-preset-color" data-color="linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)" title="Mint Pink"></button>
                            </div>
                        </div>
                        <p class="description">
                            <?php esc_html_e('Choose the main color for your gallery. Supports solid colors (hex codes) and gradients. This affects buttons, headings, and other elements throughout the gallery.', 'medical-before-after-gallery'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="category_display_mode"><?php esc_html_e('Category Display', 'medical-before-after-gallery'); ?></label>
                    </th>
                    <td>
                        <select name="category_display_mode" id="category_display_mode" class="regular-text">
                            <option value="grid" <?php selected($settings['category_display_mode'] ?? 'grid', 'grid'); ?>><?php esc_html_e('Grid Layout', 'medical-before-after-gallery'); ?></option>
                            <option value="carousel" <?php selected($settings['category_display_mode'] ?? 'grid', 'carousel'); ?>><?php esc_html_e('Carousel with Navigation', 'medical-before-after-gallery'); ?></option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Choose how categories are displayed. Grid shows all categories at once, while Carousel displays them with navigation arrows when there are many items.', 'medical-before-after-gallery'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="medbeafgallery_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'medical-before-after-gallery'); ?>">
            </p>
        </form>
    </div>


    <?php
    // Add watermark refresh button
    // medbeafgallery_add_watermark_refresh_button();
}



/**
 * Diagnostic function to help troubleshoot dashboard issues
 */
function medbeafgallery_dashboard_diagnostic() {
    $diagnostics = array();

    // Check if required functions exist
    $diagnostics['functions'] = array(
        'medbeafgallery_is_premium_active' => function_exists('medbeafgallery_is_premium_active'),
        'medbeafgallery_get_dashboard_statistics' => function_exists('medbeafgallery_get_dashboard_statistics'),
        'medbeafgallery_process_settings' => function_exists('medbeafgallery_process_settings')
    );

    // Check license status
    if (function_exists('medbeafgallery_is_premium_active')) {
        $diagnostics['license_active'] = medbeafgallery_is_premium_active();
    }

    return $diagnostics;
}

/**
 * Display pro features (locked in free version)
 */
function medbeafgallery_display_pro_features() {
    ?>
    <!-- Category Carousel Shortcode Generator (PRO) -->
    <div class="medbeafgallery-admin-box medbeafgallery-pro-feature-box">
        <div class="medbeafgallery-pro-header">
            <h2><?php esc_html_e('Category Carousel Shortcode Generator', 'medical-before-after-gallery'); ?></h2>
            <span class="medbeafgallery-pro-badge">
                <span class="dashicons dashicons-lock"></span> PRO
            </span>
        </div>
        <p class="medbeafgallery-pro-description"><?php esc_html_e('Generate shortcodes for category carousel display.', 'medical-before-after-gallery'); ?></p>
        
        <div class="medbeafgallery-pro-overlay">
            <div class="medbeafgallery-pro-overlay-content">
                <span class="dashicons dashicons-lock"></span>
                <p><?php esc_html_e('This feature is available in PRO version', 'medical-before-after-gallery'); ?></p>
                <a href="https://medicalbeforeaftergallery.com/pricing/" target="_blank" class="button button-primary">
                    <?php esc_html_e('Upgrade to PRO', 'medical-before-after-gallery'); ?>
                </a>
            </div>
        </div>

        <div class="medbeafgallery-pro-content">
            <h3><?php esc_html_e('Category Carousel', 'medical-before-after-gallery'); ?></h3>
            <p><?php esc_html_e('Display cases from a specific category in a carousel format.', 'medical-before-after-gallery'); ?></p>

            <table class="form-table medbeafgallery-pro-disabled">
                <tr>
                    <th scope="row"><?php esc_html_e('Category', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <select disabled>
                            <option><?php esc_html_e('Select a category', 'medical-before-after-gallery'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Select which category to display in the carousel.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Items Per View', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <select disabled>
                            <option>3</option>
                        </select>
                        <p class="description"><?php esc_html_e('Number of items visible at once in the carousel.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Autoplay', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" disabled>
                        <?php esc_html_e('Enable autoplay', 'medical-before-after-gallery'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Autoplay Speed', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="number" value="3000" disabled class="small-text">
                        <p class="description"><?php esc_html_e('Autoplay speed in milliseconds.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Show Navigation', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" checked disabled>
                        <?php esc_html_e('Show navigation arrows', 'medical-before-after-gallery'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Show Dots', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" checked disabled>
                        <?php esc_html_e('Show pagination dots', 'medical-before-after-gallery'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Loop', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" checked disabled>
                        <?php esc_html_e('Enable infinite loop', 'medical-before-after-gallery'); ?>
                    </td>
                </tr>
            </table>

            <div class="medbeafgallery-shortcode-output">
                <h4><?php esc_html_e('Generated Shortcode:', 'medical-before-after-gallery'); ?></h4>
                <div class="medbeafgallery-shortcode-display">
                    <code>[mba_category_carousel]</code>
                    <button class="button" disabled><?php esc_html_e('Copy', 'medical-before-after-gallery'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Watermarking (PRO) -->
    <div class="medbeafgallery-admin-box medbeafgallery-pro-feature-box">
        <div class="medbeafgallery-pro-header">
            <h2><?php esc_html_e('Watermarking', 'medical-before-after-gallery'); ?></h2>
            <span class="medbeafgallery-pro-badge">
                <span class="dashicons dashicons-lock"></span> PRO
            </span>
        </div>

        <div class="medbeafgallery-pro-overlay">
            <div class="medbeafgallery-pro-overlay-content">
                <span class="dashicons dashicons-lock"></span>
                <p><?php esc_html_e('This feature is available in PRO version', 'medical-before-after-gallery'); ?></p>
                <a href="https://medicalbeforeaftergallery.com/pricing/" target="_blank" class="button button-primary">
                    <?php esc_html_e('Upgrade to PRO', 'medical-before-after-gallery'); ?>
                </a>
            </div>
        </div>

        <div class="medbeafgallery-pro-content">
            <div class="notice notice-success inline">
                <p><?php esc_html_e('GD library is available.', 'medical-before-after-gallery'); ?></p>
            </div>

            <table class="form-table medbeafgallery-pro-disabled">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Watermarking', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" disabled>
                        <?php esc_html_e('Apply watermark to all gallery images', 'medical-before-after-gallery'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Watermark Type', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="watermark_type_disabled" checked disabled> <?php esc_html_e('Text', 'medical-before-after-gallery'); ?>
                        </label>
                        <label style="margin-left: 15px;">
                            <input type="radio" name="watermark_type_disabled" disabled> <?php esc_html_e('Image', 'medical-before-after-gallery'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Text', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="text" class="regular-text" placeholder="<?php esc_attr_e('© Your Clinic Name', 'medical-before-after-gallery'); ?>" disabled>
                        <p class="description"><?php esc_html_e('Text to display as watermark, for example: © Your Clinic Name', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Font Size', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="number" value="24" class="small-text" disabled> px
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Color', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="text" value="#ffffff" class="medbeafgallery-color-field" disabled>
                        <button class="button" disabled><?php esc_html_e('Select Color', 'medical-before-after-gallery'); ?></button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Opacity', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="range" min="0" max="100" value="50" disabled>
                        <span>50%</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Position', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <select disabled>
                            <option><?php esc_html_e('Bottom Right', 'medical-before-after-gallery'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Additional Display Options (PRO) -->
    <div class="medbeafgallery-admin-box medbeafgallery-pro-feature-box">
        <div class="medbeafgallery-pro-header">
            <h2><?php esc_html_e('Advanced Display Options', 'medical-before-after-gallery'); ?></h2>
            <span class="medbeafgallery-pro-badge">
                <span class="dashicons dashicons-lock"></span> PRO
            </span>
        </div>

        <div class="medbeafgallery-pro-overlay">
            <div class="medbeafgallery-pro-overlay-content">
                <span class="dashicons dashicons-lock"></span>
                <p><?php esc_html_e('This feature is available in PRO version', 'medical-before-after-gallery'); ?></p>
                <a href="https://medicalbeforeaftergallery.com/pricing/" target="_blank" class="button button-primary">
                    <?php esc_html_e('Upgrade to PRO', 'medical-before-after-gallery'); ?>
                </a>
            </div>
        </div>

        <div class="medbeafgallery-pro-content">
            <table class="form-table medbeafgallery-pro-disabled">
                <tr>
                    <th scope="row"><?php esc_html_e('Category Display', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <select disabled>
                            <option><?php esc_html_e('Grid Layout', 'medical-before-after-gallery'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Choose how categories are displayed. Grid shows all categories at once, while Carousel displays them with navigation arrows when there are many items.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Before/After Display', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" disabled>
                        <?php esc_html_e('Display before and after images side by side', 'medical-before-after-gallery'); ?>
                        <p class="description"><?php esc_html_e('When enabled, before and after images will be displayed side by side in the modal instead of using the split view slider. The split view, before view, and after view controls will be hidden.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Hide Case Details', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" disabled>
                        <?php esc_html_e('Hide case details from modal', 'medical-before-after-gallery'); ?>
                        <p class="description"><?php esc_html_e('When enabled, the description and details tabs will be hidden from the modal view.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Hide Sidebar', 'medical-before-after-gallery'); ?></th>
                    <td>
                        <input type="checkbox" disabled>
                        <?php esc_html_e('Hide sidebar with filters', 'medical-before-after-gallery'); ?>
                        <p class="description"><?php esc_html_e('When enabled, the sidebar with gender, age, and other filters will be hidden from the gallery view.', 'medical-before-after-gallery'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}