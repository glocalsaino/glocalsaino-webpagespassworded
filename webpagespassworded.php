<?php
/**
 * Plugin Name:       WebPagesPassworded
 * Plugin URI:        https://glocalsaino.com
 * Description:       Página de acceso centralizada para páginas hijo protegidas con contraseña. El shortcode [wppw] muestra un formulario; al introducir la contraseña correcta, el visitante es redirigido a la página hijo que la tenga asignada.
 * Version:           4.3.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            RafaMM-GlocalSaino
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       webpagespassworded
 *
 * Based on Smart Passworded Pages (v2.0.0) by Brian Layman
 * Copyright 2015 Brian Layman — https://thecodecave.com
 * Licensed under GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'web_fs' ) ) {
	web_fs()->set_basename( true, __FILE__ );
} else {

	define( 'WPPW_VERSION',         '4.3.0' );
	define( 'WPPW_PLUGIN_DIR',      plugin_dir_path( __FILE__ ) );
	define( 'WPPW_PLUGIN_URL',      plugin_dir_url( __FILE__ ) );
	define( 'SECONDS_TO_STORE_PW',  864000 ); // 10 days
	define( 'WPPW_MAX_ATTEMPTS',    5       );
	define( 'WPPW_LOCKOUT_SECONDS', 900     ); // 15 minutes

	if ( ! function_exists( 'web_fs' ) ) {
		require_once WPPW_PLUGIN_DIR . 'freemius-init.php';
	}

	require_once WPPW_PLUGIN_DIR . 'includes/class-wppw-core.php';
	require_once WPPW_PLUGIN_DIR . 'includes/class-wppw-admin.php';
	require_once WPPW_PLUGIN_DIR . 'includes/class-wppw-styles.php';
	require_once WPPW_PLUGIN_DIR . 'includes/class-wppw-magic-links.php';

	$wppw_core   = new WebPagesPW_Core();
	$wppw_admin  = new WebPagesPW_Admin();
	$wppw_styles = new WebPagesPW_Styles();
	$wppw_magic  = new WebPagesPW_MagicLinks();

	add_action( 'init',                  array( $wppw_core,  'process_form' ) );
	add_action( 'init',                  array( $wppw_magic, 'maybe_grant_access' ) );
	add_shortcode( 'wppw',               array( $wppw_core,  'wppw_shortcode' ) );

	add_action( 'admin_menu',            array( $wppw_admin, 'register_menu' ) );
	add_action( 'admin_init',            array( $wppw_admin, 'register_settings' ) );
	add_action( 'admin_enqueue_scripts', array( $wppw_admin, 'enqueue_admin_assets' ) );

	add_action( 'admin_post_wppw_magic_create', array( $wppw_magic, 'handle_create' ) );
	add_action( 'admin_post_wppw_magic_revoke', array( $wppw_magic, 'handle_revoke' ) );

	add_action( 'wp_enqueue_scripts',    array( $wppw_styles, 'enqueue_frontend_assets' ) );
	add_action( 'wp_head',               array( $wppw_styles, 'output_form_styles' ) );

}
