<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- web_fs, $web_fs and web_fs_loaded are required by the Freemius SDK and cannot be renamed.
if ( ! function_exists( 'web_fs' ) ) {

	function web_fs() {
		global $web_fs;

		if ( ! isset( $web_fs ) ) {
			$sdk = WPPW_PLUGIN_DIR . 'vendor/autoload.php';

			if ( ! file_exists( $sdk ) ) {
				$web_fs = null;
				return null;
			}

			require_once $sdk;

			$web_fs = fs_dynamic_init( array(
				'id'                  => '36911',
				'slug'                => 'webpagespassworded',
				'type'                => 'plugin',
				'public_key'          => 'pk_114bfda53c60f4de7cafd3382ffe7',
				'is_premium'          => true,
				'premium_suffix'      => 'Premium',
				'has_premium_version' => true,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'is_org_compliant'    => true,
				// Remove this line from the WP.org free version before submitting.
				'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
				'trial'               => array(
					'days'               => 7,
					'is_require_payment' => false,
				),
				'menu'                => array(
					'slug' => 'wppw-settings',
				),
			) );
		}

		return $web_fs;
	}

	web_fs();
	do_action( 'web_fs_loaded' );
}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals

// Internal aliases used throughout the plugin.

function wppw_fs(): ?object {
	return web_fs();
}

function wppw_is_premium(): bool {
	$fs = web_fs();
	return $fs instanceof Freemius && $fs->can_use_premium_code();
}

function wppw_get_upgrade_url(): string {
	$fs = web_fs();
	if ( $fs instanceof Freemius ) {
		return (string) $fs->get_upgrade_url();
	}
	return 'https://glocalsaino.com';
}
