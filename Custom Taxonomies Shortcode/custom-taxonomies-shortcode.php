<?php

function custom_taxonomy_categories_shortcode($atts) { ?>
    <style>
        .custom-taxonomy-list { display: flex; flex-wrap: wrap; gap: 15px; justify-content: flex-start; }
        .custom-taxonomy-list ul { display: contents; padding: 0; margin: 0; list-style: none; }
        .custom-taxonomy-list li a:hover { transform: translateY(-4px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08); }
        .custom-taxonomy-list a { text-decoration: none; color: #333; font-size: 16px; display: block; padding: 1.2rem; border-radius: 5px; border: 1px solid #dddddd; text-align: center; transition: transform 0.2s ease, box-shadow 0.2s ease; min-width: 150px; }
        .custom-taxonomy-list img.category-image { width: auto; max-height: 70px }
    </style>
    
    <?php
    $atts = shortcode_atts(array(
        'taxonomy' => 'category',
        'exclude'  => '',
		'hide_empty' => '',
    ), $atts, 'custom_categories');

    $exclude_ids = array_filter(array_map('intval', explode(',', $atts['exclude'])));

    $terms = get_terms(array(
        'taxonomy'   => $atts['taxonomy'],
        'hide_empty' => $atts['hide_empty'],
        'exclude'    => $exclude_ids,
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return '<p>No categories found.</p>';
    }

    $output = '<div class="custom-taxonomy-list"><ul>';

    foreach ($terms as $term) {
        $image_id = get_term_meta($term->term_id, 'image', true);
        $term_link = get_term_link($term);

        if (!is_wp_error($term_link)) {
            $output .= '<li class="category-box"><a href="' . esc_url($term_link) . '">';

            if ($image_id) {
                $image_html = wp_get_attachment_image($image_id, 'medium', false, array('class' => 'category-image'));
                $output .= '<div class="category-image-wrap">' . $image_html . '</div>';
            }

            $output .= '<div class="category-title">' . esc_html($term->name) . '</div></a></li>';
        }
    }

    $output .= '</ul></div>';

    return $output;
}
add_shortcode('custom_categories', 'custom_taxonomy_categories_shortcode');