<?php

// Add "Buy Now" button
add_action('woocommerce_after_add_to_cart_quantity', 'add_buy_now_button');
function add_buy_now_button() {
    global $product;

    if (!$product || !$product->is_in_stock()) {
        return;
    }

    $product_id = $product->get_id();
    ?>
    <button type="button" class="buy-now-button button alt" data-product-id="<?php echo esc_attr($product_id); ?>">
        Buy Now
    </button>
    <script>
        jQuery(document).ready(function($) {
            $('.buy-now-button').on('click', function() {
                var productId = $(this).data('product-id');
                var form = $(this).closest('form.cart'); // Form element for variation products
                var formData = form.serialize(); // Serialize all form data, including variations

                var redirectUrl = '';
                <?php if (is_user_logged_in()) : ?>
                    redirectUrl = "<?php echo esc_url(wc_get_checkout_url()); ?>";
                <?php else : ?>
                    redirectUrl = "<?php echo esc_url(wc_get_cart_url()); ?>";
                <?php endif; ?>

                // Add product to cart and redirect to the appropriate page
                $.ajax({
                    url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
                    type: "POST",
                    data: {
                        action: 'add_to_cart_and_redirect',
                        form_data: formData // Pass serialized form data
                    },
                    success: function() {
                        window.location.href = redirectUrl;
                    }
                });
            });
        });
    </script>
    <?php
}

// AJAX handler to add product to cart
add_action('wp_ajax_add_to_cart_and_redirect', 'handle_add_to_cart_and_redirect');
add_action('wp_ajax_nopriv_add_to_cart_and_redirect', 'handle_add_to_cart_and_redirect');
function handle_add_to_cart_and_redirect() {
    parse_str($_POST['form_data'], $form_data);

    $product_id = intval($form_data['add-to-cart']);
    $quantity = isset($form_data['quantity']) ? intval($form_data['quantity']) : 1;
    $variation_id = isset($form_data['variation_id']) ? intval($form_data['variation_id']) : 0;
    $variations = array();

    // Collect variation attributes if available
    foreach ($form_data as $key => $value) {
        if (strpos($key, 'attribute_') === 0) {
            $variations[$key] = wc_clean($value);
        }
    }

    if ($product_id) {
        WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations);
    }

    wp_die();
}