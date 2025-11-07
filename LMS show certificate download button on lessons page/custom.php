<?php

/**
 * LMS show certificate download button on lessons page
 * requires LearnDash, LearnDash Certificates and LearnDash Certificate Addon
 *
 * @since 1.0.0
 * @package LMS show certificate download button on lessons page
 *
 * @param array $atts Shortcode attributes
 * @return string certificate download button HTML
 */

function lms_show_certificate_button_shortcode($atts){
    $atts = shortcode_atts([
        'label'       => 'Download Certificate',
        'extra_label' => '',
        'extra_url'   => '',
    ], $atts, 'lms_show_certificate_button');

    // check if LearnDash is active
    if(!function_exists('learndash_get_course_id')){ return ''; }

    // check if user is logged in
    if(!is_user_logged_in()){ return ''; }

    $user_id = get_current_user_id();
    $course_id = learndash_get_course_id(get_the_ID());
    if(!$course_id){ return ''; }

    // check if LearnDash Certificates and LearnDash Certificate Addon are active
    if(!function_exists('ld_certificate_display')){
        // show message if Addon is not active
        return '<p class="lms-show-certificate-button-error">'.__('LearnDash Certificate Addon is not active. Please activate it to use this shortcode.', 'lms-show-certificate-button').'</p>';
    }

    $certificate_button = do_shortcode('[ld_certificate course_id="'.$course_id.'" user_id="'.$user_id.'" label="'.esc_attr($atts['label']).'"]');
    if(empty(trim($certificate_button))){ return ''; }

    $output = $certificate_button;
    if(!empty($atts['extra_url']) && !empty($atts['extra_label'])){
        $output .= ' <a href="'.esc_url($atts['extra_url']).'" class="ld-extra-btn">'.esc_html($atts['extra_label']).'</a>';
    }
    return $output;
}
add_shortcode('ld_certificate_dynamic','my_dynamic_ld_certificate_shortcode');
