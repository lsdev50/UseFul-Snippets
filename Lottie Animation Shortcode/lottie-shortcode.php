<?php

function render_lottie_animation_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'json' => '',
            'width' => '300px',
            'height' => '300px',
            'hover_selector' => '.lottie-animation-container',
            'container_class' => 'lottie-animation-container',
        ),
        $atts
    );

    if (empty($atts['json'])) {
        return '<p style="color:red;">Please provide a valid JSON file URL.</p>';
    }

    // Enqueue the Lottie script only once on the page
    if (!wp_script_is('lottie-player', 'enqueued')) {
        wp_enqueue_script(
            'lottie-player',
            'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.1/lottie.min.js',
            array(),
            '5.10.1',
            true
        );
    }

    $unique_id = 'lottie-container-' . uniqid();

    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['container_class']); ?>" id="<?php echo esc_attr($unique_id); ?>" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;" data-json="<?php echo esc_url($atts['json']); ?>" data-hover-selector="<?php echo esc_attr($atts['hover_selector']); ?>"></div>
    <?php
    return ob_get_clean();
}

add_shortcode('lottie', 'render_lottie_animation_shortcode');
