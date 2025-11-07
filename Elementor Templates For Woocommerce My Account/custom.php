<?php

/**
 * Customize WooCommerce My Account menu
 * - Replace default menu items with Elementor templates
 * - Add custom "Refer a Friend" menu item
 * - Remove menu items for non logged in users
 * - Attach Elementor templates to default endpoints
 * @link https://woocommerce.com/document/customize-my-account-page/
 */

add_filter('woocommerce_account_menu_items', 'build_custom_menu', 40);
function build_custom_menu($menu_links)
{
    // Remove menu items for non logged in users
    if (!is_user_logged_in()) {
        unset($menu_links['downloads']);
        unset($menu_links['edit-account']);
        unset($menu_links['customer-logout']);
    }

    // Replace default menu items with Elementor templates
    $menu_links = array(
        'dashboard'         => __('Dashboard', 'woocommerce'),
        'orders'            => __('Orders', 'woocommerce'),
        'downloads'         => __('Downloadable products', 'woocommerce'),
        'edit-account'      => __('Account details', 'woocommerce'),
        'customer-logout'   => __('Logout', 'woocommerce'),
        'refer-friend'      => 'Refer A Friend',
    );

    return $menu_links;
}

/**
 * Attach Elementor templates to default endpoints
 */
add_action('woocommerce_account_dashboard_endpoint', 'endpoint_content_dashboard');
function endpoint_content_dashboard()
{
    echo do_shortcode("[elementor-template id='868']");
}

add_action('woocommerce_account_orders_endpoint', 'endpoint_content_orders');
function endpoint_content_orders()
{
    echo do_shortcode("[elementor-template id='2154']");
}

add_action('woocommerce_account_downloads_endpoint', 'endpoint_content_downloads');
function endpoint_content_downloads()
{
    echo do_shortcode("[elementor-template id='9765']");
}

add_action('woocommerce_account_edit-account_endpoint', 'endpoint_content_edit_account');
function endpoint_content_edit_account()
{
    echo do_shortcode("[elementor-template id='2160']");
}

add_action('woocommerce_account_customer-logout_endpoint', 'endpoint_content_customer_logout');
function endpoint_content_customer_logout()
{
    echo do_shortcode("[elementor-template id='1247']");
}

add_action('woocommerce_account_refer-friend_endpoint', 'endpoint_content_refer_friend');
function endpoint_content_refer_friend()
{
    echo do_shortcode("[elementor-template id='2160']");
}

