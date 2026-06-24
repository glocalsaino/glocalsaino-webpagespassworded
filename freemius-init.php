<?php
/**
 * Freemius initialization.
 *
 * Pasos para activar:
 * 1. Regístrate en https://freemius.com y crea un nuevo plugin.
 * 2. Descarga el SDK de Freemius desde tu dashboard y colócalo en /freemius/.
 * 3. Sustituye FREEMIUS_PLUGIN_ID y FREEMIUS_PUBLIC_KEY por los valores de tu dashboard.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wppw_fs' ) ) {

	function wppw_fs(): ?object {
		global $wppw_fs;

		if ( ! isset( $wppw_fs ) ) {
			$sdk = WPPW_PLUGIN_DIR . 'freemius/start.php';

			if ( ! file_exists( $sdk ) ) {
				$wppw_fs = null;
				return null;
			}

			require_once $sdk;

			$wppw_fs = fs_dynamic_init( [
				'id'             => 'FREEMIUS_PLUGIN_ID',  // ← reemplazar tras registrar en Freemius
				'slug'           => 'wp-webpagespassworded',
				'type'           => 'plugin',
				'public_key'     => 'FREEMIUS_PUBLIC_KEY', // ← reemplazar tras registrar en Freemius
				'is_premium'     => true,
				'has_addons'     => false,
				'has_paid_plans' => true,
				'menu'           => [
					'slug'   => 'wppw-settings',
					'parent' => [
						'slug' => 'options-general.php',
					],
				],
			] );
		}

		return $wppw_fs;
	}

	wppw_fs();
	do_action( 'wppw_fs_loaded' );
}

/**
 * Returns true if the current site can use premium features.
 * During development, define WPPW_FORCE_PREMIUM in wp-config.php to bypass the license check.
 */
function wppw_is_premium(): bool {
	if ( defined( 'WPPW_FORCE_PREMIUM' ) && WPPW_FORCE_PREMIUM ) {
		return true;
	}
	$fs = wppw_fs();
	if ( null === $fs ) {
		return false;
	}
	return $fs->can_use_premium_code();
}

/**
 * Returns the Freemius upgrade URL, or a fallback when the SDK is not yet installed.
 */
function wppw_get_upgrade_url(): string {
	$fs = wppw_fs();
	if ( null === $fs ) {
		return 'https://glocalsaino.com';
	}
	return (string) $fs->get_upgrade_url();
}
