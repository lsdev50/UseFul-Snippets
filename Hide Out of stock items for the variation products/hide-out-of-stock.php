<?php

// Add this code to your theme's functions.php file
// Hide out of stock items on single product page for variable products 
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

add_filter( 'pre_option_woocommerce_hide_out_of_stock_items', 'hide_out_of_stock_exception_variation' );