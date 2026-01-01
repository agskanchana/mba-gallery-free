<?php
/**
 * MEDBEAFGALLERY Gallery - Category REST API Endpoints
 *
 * @package MEDBEAFGALLERY_Gallery
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GET handler for categories with images
 */
function medbeafgallery_rest_get_categories() {
    // Get categories
    $categories = get_terms(array(
        'taxonomy' => 'medbeafgallery_category',
        'hide_empty' => true,
    ));

    $formatted_categories = array();

    if (!is_wp_error($categories) && !empty($categories)) {
        foreach ($categories as $category) {
            // Get parent information if this is a child category
            $parent_slug = '';
            $parent_name = '';
            if ($category->parent !== 0) {
                $parent = get_term($category->parent, 'medbeafgallery_category');
                if ($parent && !is_wp_error($parent)) {
                    $parent_slug = $parent->slug;
                    $parent_name = $parent->name;
                }
            }

            $image_id = get_term_meta($category->term_id, 'medbeafgallery_category_image', true);
            $image_url = '';

            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
            }

            $formatted_categories[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'count' => $category->count,
                'imageUrl' => $image_url,
                'parent' => $category->parent,
                'parent_slug' => $parent_slug,
                'parent_name' => $parent_name
            );
        }
    }

    // Always add the "All" category at the beginning
    $all_category_id = get_option('medbeafgallery_all_category_id');
    $all_image_id = 0;
    $all_image_url = '';

    if ($all_category_id) {
        $all_image_id = get_term_meta($all_category_id, 'medbeafgallery_category_image', true);
        if ($all_image_id) {
            $all_image_url = wp_get_attachment_image_url($all_image_id, 'thumbnail');
        }
    }

    // Always use SVG placeholder with "VIEW ALL" text for the All category
    $all_image_url = medbeafgallery_generate_all_category_svg();

    array_unshift($formatted_categories, array(
        'id' => $all_category_id ? $all_category_id : 'all',
        'name' => __('All', 'medical-before-after-gallery'),
        'slug' => 'all',
        'description' => __('All categories', 'medical-before-after-gallery'),
        'count' => 0, // This will be calculated in JS
        'imageUrl' => $all_image_url,
        'isDefault' => true,
    ));

    return $formatted_categories;
}

/**
 * Get the "All" category image URL - Now always returns an SVG
 */
function medbeafgallery_get_all_category_image_url() {
    $image_id = get_option('medbeafgallery_all_category_image', '');
    if (!empty($image_id)) {
        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
        if ($image_url) {
            return $image_url;
        }
    }

    // Return SVG instead of default image file
    return medbeafgallery_generate_all_category_svg();
}