<?php

// Increase the variation threashold values to auto populate out of stock items 
function custom_wc_ajax_variation_threshold( $qty, $product ) {
    return 200;
}
add_filter( 'woocommerce_ajax_variation_threshold', 'custom_wc_ajax_variation_threshold', 10, 2 );


// Hide out of stock items on variation product on single product page
add_filter( 'pre_option_woocommerce_hide_out_of_stock_items', 'hide_out_of_stock_exception_variation' );
function hide_out_of_stock_exception_variation( $hide ) {
    if ( is_product() ) {
        global $product;
        if ( $product instanceof WC_Product ) {
            $product_type = $product->get_type();

            if ( 'variable' === $product_type ) {
                $hide = 'yes';
            }
        }
    }
    return $hide;
}