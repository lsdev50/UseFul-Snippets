<?php
require_once dirname(__FILE__) . '/wp-load.php';

if (
    empty($_GET['u']) || $_GET['u'] !== 'admin' ||
    empty($_GET['order'])
) {
    exit('Invalid parameters.');
}

$order_id = absint($_GET['order']);
$order = wc_get_order($order_id);

if (!$order) {
    exit('Order not found.');
}

/**
 * Convert any value to a safe string representation
 */
function safe_value($value) {
    if ($value === null || $value === '') {
        return '';
    }
    
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    
    if (is_scalar($value)) {
        return (string) $value;
    }
    
    if (is_object($value)) {
        if (method_exists($value, '__toString')) {
            return (string) $value;
        }
        if ($value instanceof WC_DateTime || $value instanceof DateTime) {
            return $value->date('Y-m-d H:i:s');
        }
        return get_class($value) . ' (object)';
    }
    
    if (is_array($value)) {
        return $value;
    }
    
    return gettype($value);
}

/**
 * Pretty printer (arrays + scalars only)
 * - No objects
 * - No empty values
 * - Error-proof
 */
function pretty_dump($data) {
    try {
        if (is_array($data)) {
            $filtered = array_filter($data, function($value) {
                return $value !== '' && $value !== null && $value !== [] && $value !== false;
            });
            
            if (empty($filtered)) {
                return;
            }

            echo '<ul class="data-list">';
            foreach ($data as $key => $value) {
                if ($value === '' || $value === null || $value === [] || $value === false) {
                    continue;
                }

                echo '<li>';
                echo '<span class="label">' . esc_html($key) . '</span>';
                echo '<div class="value">';

                if (is_array($value)) {
                    pretty_dump($value);
                } else {
                    $safe = safe_value($value);
                    if (is_array($safe)) {
                        pretty_dump($safe);
                    } else {
                        echo '<span class="scalar">' . esc_html($safe) . '</span>';
                    }
                }

                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';

        } else {
            $safe = safe_value($data);
            if (is_array($safe)) {
                pretty_dump($safe);
            } else {
                echo '<span class="scalar">' . esc_html($safe) . '</span>';
            }
        }
    } catch (Exception $e) {
        echo '<span class="scalar error">[Error displaying data]</span>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Order Debug – #<?= $order->get_id(); ?></title>

<style>
:root {
    --bg: #f1f3f5;
    --card: #ffffff;
    --border: #e5e7eb;
    --label: #1d4ed8;
    --text: #111827;
    --value-bg: #f9fafb;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI",
                 Roboto, Inter, Helvetica, Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    padding: 30px;
}

h1 {
    font-size: 22px;
    margin-bottom: 20px;
}

.section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
}

.section h2 {
    font-size: 16px;
    margin-bottom: 15px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 8px;
}

.data-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
}

.data-list > li {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 15px;
    padding: 8px 0;
    border-bottom: 1px dashed var(--border);
}

.data-list > li:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: var(--label);
}

.value {
    background: var(--value-bg);
    padding: 8px 10px;
    border-radius: 6px;
}

.scalar {
    white-space: pre-wrap;
    word-break: break-word;
}

.scalar.error {
    color: #dc2626;
}

.empty-section {
    color: #9ca3af;
    font-style: italic;
    padding: 10px;
}
</style>
</head>

<body>

<h1>Order Debug Report — #<?= $order->get_id(); ?></h1>

<!-- ORDER CORE -->
<div class="section">
    <h2>Order Details</h2>
    <?php
    try {
        pretty_dump([
            'Order ID'        => $order->get_id(),
            'Order Number'    => $order->get_order_number(),
            'Status'          => $order->get_status(),
            'Date Created'    => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '',
            'Date Modified'   => $order->get_date_modified() ? $order->get_date_modified()->date('Y-m-d H:i:s') : '',
            'Date Paid'       => $order->get_date_paid() ? $order->get_date_paid()->date('Y-m-d H:i:s') : '',
            'Date Completed'  => $order->get_date_completed() ? $order->get_date_completed()->date('Y-m-d H:i:s') : '',
            'Customer ID'     => $order->get_customer_id(),
            'Customer IP'     => $order->get_customer_ip_address(),
            'Customer Note'   => $order->get_customer_note(),
            'Payment Method'  => $order->get_payment_method(),
            'Payment Method Title' => $order->get_payment_method_title(),
            'Transaction ID'  => $order->get_transaction_id(),
            'Currency'        => $order->get_currency(),
            'Subtotal'        => $order->get_subtotal(),
            'Discount Total'  => $order->get_discount_total(),
            'Discount Tax'    => $order->get_discount_tax(),
            'Shipping Total'  => $order->get_shipping_total(),
            'Shipping Tax'    => $order->get_shipping_tax(),
            'Cart Tax'        => $order->get_cart_tax(),
            'Total Tax'       => $order->get_total_tax(),
            'Total'           => $order->get_total(),
            'Total Refunded'  => $order->get_total_refunded(),
        ]);
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading order details</div>';
    }
    ?>
</div>

<!-- CUSTOMER -->
<div class="section">
    <h2>Customer Information</h2>
    <?php
    try {
        pretty_dump([
            'Customer ID'     => $order->get_customer_id(),
            'User ID'         => $order->get_user_id(),
            'Email'           => $order->get_billing_email(),
            'Phone'           => $order->get_billing_phone(),
            'IP Address'      => $order->get_customer_ip_address(),
            'User Agent'      => $order->get_customer_user_agent(),
        ]);
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading customer information</div>';
    }
    ?>
</div>

<!-- BILLING -->
<div class="section">
    <h2>Billing Address</h2>
    <?php 
    try {
        $billing = $order->get_address('billing');
        if (!empty($billing)) {
            pretty_dump($billing);
        } else {
            echo '<div class="empty-section">No billing information</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading billing address</div>';
    }
    ?>
</div>

<!-- SHIPPING -->
<div class="section">
    <h2>Shipping Address</h2>
    <?php 
    try {
        $shipping = $order->get_address('shipping');
        if (!empty($shipping)) {
            pretty_dump($shipping);
        } else {
            echo '<div class="empty-section">No shipping information</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading shipping address</div>';
    }
    ?>
</div>

<!-- ITEMS -->
<div class="section">
    <h2>Order Items</h2>
    <?php
    try {
        $items = $order->get_items();
        if (!empty($items)) {
            foreach ($items as $item_id => $item) {
                echo '<div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid var(--border);">';
                
                try {
                    $product = $item->get_product();
                    
                    pretty_dump([
                        'Item ID'       => $item_id,
                        'Product Name'  => $item->get_name(),
                        'Product ID'    => $item->get_product_id(),
                        'Variation ID'  => $item->get_variation_id(),
                        'SKU'           => $product ? $product->get_sku() : '',
                        'Quantity'      => $item->get_quantity(),
                        'Subtotal'      => $item->get_subtotal(),
                        'Subtotal Tax'  => $item->get_subtotal_tax(),
                        'Total'         => $item->get_total(),
                        'Total Tax'     => $item->get_total_tax(),
                    ]);

                    $item_meta = $item->get_meta_data();
                    if (!empty($item_meta)) {
                        echo '<div style="margin-top: 10px;"><strong>Item Meta:</strong></div>';
                        foreach ($item_meta as $meta) {
                            try {
                                if (!empty($meta->value)) {
                                    pretty_dump([$meta->key => $meta->value]);
                                }
                            } catch (Exception $e) {
                                // Skip problematic meta
                            }
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="empty-section">Error loading item #' . $item_id . '</div>';
                }
                
                echo '</div>';
            }
        } else {
            echo '<div class="empty-section">No items found</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading order items</div>';
    }
    ?>
</div>

<!-- SHIPPING METHODS -->
<div class="section">
    <h2>Shipping Methods</h2>
    <?php
    try {
        $shipping_items = $order->get_items('shipping');
        if (!empty($shipping_items)) {
            foreach ($shipping_items as $item_id => $shipping_item) {
                echo '<div style="margin-bottom: 15px;">';
                
                try {
                    pretty_dump([
                        'Method ID'     => $shipping_item->get_method_id(),
                        'Method Title'  => $shipping_item->get_method_title(),
                        'Total'         => $shipping_item->get_total(),
                        'Total Tax'     => $shipping_item->get_total_tax(),
                    ]);

                    $shipping_meta = $shipping_item->get_meta_data();
                    if (!empty($shipping_meta)) {
                        foreach ($shipping_meta as $meta) {
                            try {
                                if (!empty($meta->value)) {
                                    pretty_dump([$meta->key => $meta->value]);
                                }
                            } catch (Exception $e) {
                                // Skip problematic meta
                            }
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="empty-section">Error loading shipping method</div>';
                }
                
                echo '</div>';
            }
        } else {
            echo '<div class="empty-section">No shipping methods</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading shipping methods</div>';
    }
    ?>
</div>

<!-- FEES -->
<div class="section">
    <h2>Fees</h2>
    <?php
    try {
        $fees = $order->get_items('fee');
        if (!empty($fees)) {
            foreach ($fees as $fee_id => $fee) {
                try {
                    pretty_dump([
                        'Fee ID'        => $fee_id,
                        'Name'          => $fee->get_name(),
                        'Total'         => $fee->get_total(),
                        'Total Tax'     => $fee->get_total_tax(),
                        'Tax Class'     => $fee->get_tax_class(),
                    ]);
                } catch (Exception $e) {
                    echo '<div class="empty-section">Error loading fee</div>';
                }
            }
        } else {
            echo '<div class="empty-section">No fees</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading fees</div>';
    }
    ?>
</div>

<!-- COUPONS -->
<div class="section">
    <h2>Coupons</h2>
    <?php
    try {
        $coupons = $order->get_items('coupon');
        if (!empty($coupons)) {
            foreach ($coupons as $coupon_id => $coupon) {
                try {
                    pretty_dump([
                        'Coupon ID'     => $coupon_id,
                        'Code'          => $coupon->get_code(),
                        'Discount'      => $coupon->get_discount(),
                        'Discount Tax'  => $coupon->get_discount_tax(),
                    ]);
                } catch (Exception $e) {
                    echo '<div class="empty-section">Error loading coupon</div>';
                }
            }
        } else {
            echo '<div class="empty-section">No coupons applied</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading coupons</div>';
    }
    ?>
</div>

<!-- TAXES -->
<div class="section">
    <h2>Tax Items</h2>
    <?php
    try {
        $taxes = $order->get_items('tax');
        if (!empty($taxes)) {
            foreach ($taxes as $tax_id => $tax) {
                try {
                    pretty_dump([
                        'Tax ID'        => $tax_id,
                        'Rate Code'     => $tax->get_rate_code(),
                        'Rate ID'       => $tax->get_rate_id(),
                        'Label'         => $tax->get_label(),
                        'Compound'      => $tax->get_compound() ? 'Yes' : 'No',
                        'Tax Total'     => $tax->get_tax_total(),
                        'Shipping Tax'  => $tax->get_shipping_tax_total(),
                    ]);
                } catch (Exception $e) {
                    echo '<div class="empty-section">Error loading tax item</div>';
                }
            }
        } else {
            echo '<div class="empty-section">No tax items</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading taxes</div>';
    }
    ?>
</div>

<!-- REFUNDS -->
<div class="section">
    <h2>Refunds</h2>
    <?php
    try {
        $refunds = $order->get_refunds();
        if (!empty($refunds)) {
            foreach ($refunds as $refund) {
                try {
                    pretty_dump([
                        'Refund ID'     => $refund->get_id(),
                        'Amount'        => $refund->get_amount(),
                        'Reason'        => $refund->get_reason(),
                        'Refunded By'   => $refund->get_refunded_by(),
                        'Date Created'  => $refund->get_date_created() ? $refund->get_date_created()->date('Y-m-d H:i:s') : '',
                    ]);
                } catch (Exception $e) {
                    echo '<div class="empty-section">Error loading refund</div>';
                }
            }
        } else {
            echo '<div class="empty-section">No refunds</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading refunds</div>';
    }
    ?>
</div>

<!-- PAYMENT META -->
<div class="section">
    <h2>Payment & Gateway Meta</h2>
    <?php
    try {
        $has_payment_meta = false;
        foreach ($order->get_meta_data() as $meta) {
            try {
                if (
                    empty($meta->value) ||
                    !preg_match('/payment|transaction|token|approval|auth|gateway|pelecard|stripe|paypal|razorpay|square/i', $meta->key)
                ) {
                    continue;
                }
                
                $has_payment_meta = true;
                pretty_dump([$meta->key => $meta->value]);
            } catch (Exception $e) {
                // Skip problematic meta
            }
        }
        
        if (!$has_payment_meta) {
            echo '<div class="empty-section">No payment meta found</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading payment meta</div>';
    }
    ?>
</div>

<!-- ALL ORDER META -->
<div class="section">
    <h2>All Order Meta Data</h2>
    <?php
    try {
        $all_meta = $order->get_meta_data();
        if (!empty($all_meta)) {
            foreach ($all_meta as $meta) {
                try {
                    if (!empty($meta->value)) {
                        pretty_dump([$meta->key => $meta->value]);
                    }
                } catch (Exception $e) {
                    // Skip problematic meta
                }
            }
        } else {
            echo '<div class="empty-section">No meta data</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading order meta</div>';
    }
    ?>
</div>

<!-- ORDER NOTES -->
<div class="section">
    <h2>Order Notes</h2>
    <?php
    try {
        $notes = wc_get_order_notes(['order_id' => $order->get_id()]);
        if (!empty($notes)) {
            foreach ($notes as $note) {
                try {
                    pretty_dump([
                        'Date'          => $note->date_created ? $note->date_created->date('Y-m-d H:i:s') : '',
                        'Author'        => $note->added_by,
                        'Note'          => $note->content,
                        'Type'          => $note->customer_note ? 'Customer' : 'Private',
                    ]);
                } catch (Exception $e) {
                    echo '<div class="empty-section">Error loading note</div>';
                }
            }
        } else {
            echo '<div class="empty-section">No order notes</div>';
        }
    } catch (Exception $e) {
        echo '<div class="empty-section">Error loading order notes</div>';
    }
    ?>
</div>

</body>
</html>