<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- glocalsaino_wppw_fs and $glocalsaino_wppw_fs are the plugin's Freemius integration function and global; glocalsaino_wppw_fs_loaded is the corresponding action.
if ( ! function_exists( 'glocalsaino_wppw_fs' ) ) {

	function glocalsaino_wppw_fs() {
		global $glocalsaino_wppw_fs;

		if ( ! isset( $glocalsaino_wppw_fs ) ) {
			$sdk = GLOCALSAINO_WPPW_DIR . 'vendor/autoload.php';

			if ( ! file_exists( $sdk ) ) {
				$glocalsaino_wppw_fs = null;
				return null;
			}

			require_once $sdk;

			$glocalsaino_wppw_fs = fs_dynamic_init( array(
				'id'                  => '36911',
				'slug'                => 'glocalsaino-webpagespassworded',
				'type'                => 'plugin',
				'public_key'          => 'pk_114bfda53c60f4de7cafd3382ffe7',
				// Plugin 100% gratuito. Freemius se usa únicamente para la
				// infraestructura de add-ons futuros. Nada está bloqueado
				// tras una licencia, en cumplimiento de las normas de WP.org.
				'is_premium'          => false,
				'has_premium_version' => false,
				'has_addons'          => false,
				'has_paid_plans'      => false,
				'is_org_compliant'    => true,
				'menu'                => array(
					'slug'    => 'glocalsaino-wppw-settings',
					'pricing' => false,
				),
			) );
		}

		return $glocalsaino_wppw_fs;
	}

	glocalsaino_wppw_fs();
	do_action( 'glocalsaino_wppw_fs_loaded' );
}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals
