<?php
/**
 * Plugin Name:       GlocalSaino WebPagesPassworded
 * Plugin URI:        https://glocalsaino.com
 * Description:       Single access page for groups of password-protected child pages. Place [glocalsaino_wppw] on a parent page and visitors are redirected to the matching child page after entering the correct password.
 * Version:           4.4.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Glocal Saino
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       glocalsaino-webpagespassworded
 *
 * Based on Smart Passworded Pages (v2.0.0) by Brian Layman
 * Copyright 2015 Brian Layman — https://thecodecave.com
 * Licensed under GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'glocalsaino_wppw_fs' ) ) {
	glocalsaino_wppw_fs()->set_basename( true, __FILE__ );
} else {

	define( 'GLOCALSAINO_WPPW_VERSION',        '4.4.0' );
	define( 'GLOCALSAINO_WPPW_DIR',     plugin_dir_path( __FILE__ ) );
	define( 'GLOCALSAINO_WPPW_URL',     plugin_dir_url( __FILE__ ) );
	define( 'GLOCALSAINO_WPPW_COOKIE_SECONDS', 864000 );
	define( 'GLOCALSAINO_WPPW_MAX_ATTEMPTS',   5 );
	define( 'GLOCALSAINO_WPPW_LOCKOUT_SECONDS', 900 );

	if ( ! function_exists( 'glocalsaino_wppw_fs' ) ) {
		require_once GLOCALSAINO_WPPW_DIR . 'freemius-init.php';
	}

	require_once GLOCALSAINO_WPPW_DIR . 'includes/class-wppw-core.php';
	require_once GLOCALSAINO_WPPW_DIR . 'includes/class-wppw-admin.php';
	require_once GLOCALSAINO_WPPW_DIR . 'includes/class-wppw-styles.php';

	$wppw_core   = new GlocalSaino_Wppw_Core();
	$wppw_admin  = new GlocalSaino_Wppw_Admin();
	$wppw_styles = new GlocalSaino_Wppw_Styles();

	add_action( 'init',                  array( $wppw_core,   'process_form' ) );
	add_shortcode( 'glocalsaino_wppw',   array( $wppw_core,   'wppw_shortcode' ) );

	add_action( 'admin_menu',            array( $wppw_admin,  'register_menu' ) );
	add_action( 'admin_init',            array( $wppw_admin,  'register_settings' ) );
	add_action( 'admin_enqueue_scripts', array( $wppw_admin,  'enqueue_admin_assets' ) );

	add_action( 'wp_enqueue_scripts',    array( $wppw_styles, 'enqueue_frontend_assets' ) );
}
