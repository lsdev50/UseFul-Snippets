<?php

/**
 * Redirect non-logged-in users and users without the 'administrator' or 'customer' role
 * from the Dashboard page to the login page.
 */
function ls_template_redirect() {
    if (is_page('dashboard')) {
        if (! is_user_logged_in() || (! current_user_can('administrator') && ! current_user_can('customer'))) {
            wp_redirect(home_url('/login'));
            exit;
        }
    }
}
add_action('template_redirect', 'ls_template_redirect');


/**
 * Register custom endpoints for the User Registration "My Account" section.
 * Reference: https://docs.wpuserregistration.com/
 */
function ls_add_custom_dashboard_endpoint() {
    add_rewrite_endpoint('listing', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('coupons', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('forms', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('entity', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('entity-add', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('asset', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('asset-add', EP_ROOT | EP_PAGES);
}
add_action('init', 'ls_add_custom_dashboard_endpoint');


/**
 * Load template files for each custom endpoint.
 */
function ls_entity_endpoint_content() {
    get_template_part('user-registration/myaccount/listing-endpoint');
}
add_action('user_registration_account_listing_endpoint', 'ls_entity_endpoint_content');

function ls_entity_add_endpoint_content() {
    get_template_part('user-registration/myaccount/entity-add-endpoint');
}
add_action('user_registration_account_entity-add_endpoint', 'ls_entity_add_endpoint_content');

function ls_asset_endpoint_content() {
    get_template_part('user-registration/myaccount/asset-endpoint');
}
add_action('user_registration_account_asset_endpoint', 'ls_asset_endpoint_content');

function ls_asset_add_endpoint_content() {
    get_template_part('user-registration/myaccount/asset-add-endpoint');
}
add_action('user_registration_account_asset-add_endpoint', 'ls_asset_add_endpoint_content');


/**
 * Add custom menu items inside the User Registration My Account menu.
 */
function ls_add_custom_items_after_first($items) {
    $custom_items = array(
        'entity'              => __('Entity', 'astra-child'),
        'asset'               => __('Asset', 'astra-child'),
        'corporate-services'  => __('Corporate Services', 'astra-child'),
        'get-help'            => __('Get Help', 'astra-child'),
    );

    $first_item      = array_slice($items, 0, 1, true);
    $remaining_items = array_slice($items, 1, null, true);
    $items           = $first_item + $custom_items + $remaining_items;

    return $items;
}
add_filter('user_registration_account_menu_items', 'ls_add_custom_items_after_first', 20);
