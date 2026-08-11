<?php
/**
 * Freemius SDK initialization.
 *
 * Pasos para activar Freemius:
 * 1. Crea una cuenta en https://freemius.com y añade un nuevo plugin.
 * 2. En el dashboard copia el Plugin ID (número) y la Public Key (pk_…).
 * 3. Sustituye los valores marcados con TODO más abajo.
 * 4. Descarga el SDK desde el dashboard, descomprímelo y coloca la carpeta
 *    resultante en /freemius/ dentro del directorio del plugin.
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
				'id'             => '0000',                            // TODO: Plugin ID de Freemius
				'slug'           => 'webpagespassworded',
				'type'           => 'plugin',
				'public_key'     => 'pk_00000000000000000000000000000', // TODO: Public Key de Freemius
				'is_premium'     => false,
				'has_addons'     => false,
				'has_paid_plans' => true,
				'menu'           => [
					'slug'    => 'wppw-settings',
					'contact' => false,
					'support' => false,
					'parent'  => [
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

function wppw_is_premium(): bool {
	$fs = wppw_fs();
	return $fs instanceof Freemius && $fs->can_use_premium_code();
}

function wppw_get_upgrade_url(): string {
	$fs = wppw_fs();
	if ( $fs instanceof Freemius ) {
		return (string) $fs->get_upgrade_url();
	}
	return 'https://glocalsaino.com';
}
