<?php

/**
 * Make sure that the Gravity Forms User Registration feed is not asynchronous.
 *
 * https://docs.gravityforms.com/gform_is_feed_asynchronous/
 */
add_filter( 'gform_is_feed_asynchronous', function ( $is_asynchronous, $feed, $entry, $form ) {
    if ( ! $is_asynchronous || rgar( $feed, 'addon_slug' ) !== 'gravityformsuserregistration' ) {
        return $is_asynchronous;
    }
 
    return gf_user_registration()->is_update_feed( $feed ) ? $is_asynchronous : false;
}, 10, 4 );

/**
 * Automatically log in the user after they have been registered by Gravity Forms.
 * 
 * @param int $user_id The ID of the user that was just registered.
 * @param array $feed The configuration of the Gravity Forms feed that triggered
 *                     this function.
 * @param array $entry The data of the form that was just submitted.
 * @param string $password The password that the user entered on the form.
 */
function jh_gravity_registration_autologin( $user_id, $feed, $entry, $password ) {
	if ( !is_user_logged_in() ) {
	   $user = get_userdata( $user_id );
	   wp_signon( array(
		   'user_login' => $user->user_login,
		   'user_password' => $password,
		   'remember' => false
	   ) );
    } else {
		wp_safe_redirect(home_url('/?login-success=1'));
		exit;
	}

}
add_action( 'gform_user_registered', 'jh_gravity_registration_autologin', 10, 4 );