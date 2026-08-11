<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Magic links grant direct access to a password-protected page via a signed,
 * revocable token instead of exposing the real password in a URL.
 */
class WebPagesPW_MagicLinks {

	private const OPTION_KEY = 'wppw_magic_links';

	/**
	 * Checks the wppw_magic query var on every front-end request and, if valid,
	 * sets the WordPress post-password cookie and redirects to the clean permalink.
	 */
	public function maybe_grant_access(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- token itself is the cryptographic verification.
		if ( empty( $_GET['wppw_magic'] ) ) {
			return;
		}

		if ( web_fs() && web_fs()->can_use_premium_code__premium_only() ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- token itself is the cryptographic verification.
			$token = sanitize_text_field( wp_unslash( $_GET['wppw_magic'] ) );
			$links = $this->get_links();

			if ( ! isset( $links[ $token ] ) ) {
				return;
			}

			$link = $links[ $token ];

			if ( $this->is_expired( $link ) || $this->is_exhausted( $link ) ) {
				if ( ! empty( $link['fallback_url'] ) ) {
					// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- fallback_url may be external; saved via esc_url_raw() on creation.
				wp_redirect( $link['fallback_url'] );
					exit;
				}
				return;
			}

			$page = get_post( $link['page_id'] );

			if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status || empty( $page->post_password ) ) {
				return;
			}

			$links[ $token ]['uses']++;
			update_option( self::OPTION_KEY, $links );

			( new WebPagesPW_Core() )->pw_redirect( get_permalink( $page->ID ), $page->post_password );
		}
	}

	public function get_links(): array {
		return (array) get_option( self::OPTION_KEY, [] );
	}

	public function is_expired( array $link ): bool {
		return ! empty( $link['expires'] ) && time() > $link['expires'];
	}

	public function is_exhausted( array $link ): bool {
		return ! empty( $link['max_uses'] ) && $link['uses'] >= $link['max_uses'];
	}

	public function get_link_url( string $token, int $page_id ): string {
		$permalink = get_permalink( $page_id );
		return $permalink ? add_query_arg( 'wppw_magic', $token, $permalink ) : '';
	}

	/**
	 * Returns all published pages currently protected with a password.
	 */
	public function get_protected_pages(): array {
		return get_posts( [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'has_password'   => true,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
	}

	public function handle_create(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'webpagespassworded' ) );
		}
		check_admin_referer( 'wppw_magic_create' );

		if ( web_fs() && web_fs()->can_use_premium_code__premium_only() ) {
			$page_id = absint( $_POST['page_id'] ?? 0 );
			$page    = get_post( $page_id );

			if ( ! $page instanceof WP_Post || empty( $page->post_password ) ) {
				wp_safe_redirect( add_query_arg( 'wppw_magic_error', '1', wp_get_referer() ) );
				exit;
			}

			$expires_in   = absint( $_POST['expires_in'] ?? 0 );
			$max_uses     = absint( $_POST['max_uses'] ?? 0 );
			$label        = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
			$fallback_url = esc_url_raw( wp_unslash( $_POST['fallback_url'] ?? '' ) );

			$token = bin2hex( random_bytes( 32 ) );
			$links = $this->get_links();

			$links[ $token ] = [
				'page_id'      => $page_id,
				'label'        => $label,
				'fallback_url' => $fallback_url,
				'created'      => time(),
				'expires'      => $expires_in ? time() + $expires_in : 0,
				'max_uses'     => $max_uses,
				'uses'         => 0,
			];

			update_option( self::OPTION_KEY, $links );

			wp_safe_redirect( add_query_arg( 'wppw_magic_created', '1', wp_get_referer() ) );
			exit;
		}

		wp_safe_redirect( wp_get_referer() ?: admin_url( 'options-general.php?page=wppw-settings' ) );
		exit;
	}

	public function handle_revoke(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'webpagespassworded' ) );
		}
		check_admin_referer( 'wppw_magic_revoke' );

		$token = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
		$links = $this->get_links();

		if ( isset( $links[ $token ] ) ) {
			unset( $links[ $token ] );
			update_option( self::OPTION_KEY, $links );
		}

		wp_safe_redirect( wp_get_referer() ?: admin_url( 'options-general.php?page=wppw-settings' ) );
		exit;
	}
}
