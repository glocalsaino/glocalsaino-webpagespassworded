<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login links authenticate a WordPress user automatically via a signed,
 * time-limited, revocable token — no password required or exposed.
 */
class WebPagesPW_LoginLinks {

	const OPTION_KEY = 'wppw_login_links';

	/**
	 * Checks the wppw_login query var on every front-end request and, if valid,
	 * logs in the linked user and redirects to the configured destination.
	 */
	public function maybe_grant_access(): void {
		if ( is_admin() || ! wppw_is_premium() || empty( $_GET['wppw_login'] ) ) {
			return;
		}

		$token = sanitize_text_field( wp_unslash( $_GET['wppw_login'] ) );
		$links = $this->get_links();

		if ( ! isset( $links[ $token ] ) ) {
			return;
		}

		$link = $links[ $token ];

		if ( $this->is_expired( $link ) || $this->is_exhausted( $link ) ) {
			return;
		}

		$user = get_user_by( 'id', (int) $link['user_id'] );
		if ( ! $user instanceof WP_User ) {
			return;
		}

		$links[ $token ]['uses']++;
		update_option( self::OPTION_KEY, $links );

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, false );
		do_action( 'wp_login', $user->user_login, $user );

		$redirect_to = ! empty( $link['redirect_to'] ) ? $link['redirect_to'] : home_url( '/' );
		wp_safe_redirect( $redirect_to );
		exit;
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

	public function get_link_url( string $token ): string {
		return add_query_arg( 'wppw_login', $token, home_url( '/' ) );
	}

	public function get_users(): array {
		return get_users( [
			'orderby' => 'display_name',
			'order'   => 'ASC',
		] );
	}

	public function handle_create(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'wppw' ) );
		}
		check_admin_referer( 'wppw_login_create' );

		if ( ! wppw_is_premium() ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url( 'options-general.php?page=wppw-settings' ) );
			exit;
		}

		$user_id = absint( $_POST['user_id'] ?? 0 );
		$user    = get_user_by( 'id', $user_id );

		if ( ! $user instanceof WP_User ) {
			wp_safe_redirect( add_query_arg( 'wppw_login_error', '1', wp_get_referer() ) );
			exit;
		}

		$expires_in  = absint( $_POST['expires_in'] ?? 0 );
		$max_uses    = absint( $_POST['max_uses'] ?? 1 );
		$label       = sanitize_text_field( $_POST['label'] ?? '' );
		$redirect_to = esc_url_raw( $_POST['redirect_to'] ?? '' );

		$token = bin2hex( random_bytes( 32 ) );
		$links = $this->get_links();

		$links[ $token ] = [
			'user_id'     => $user_id,
			'label'       => $label,
			'redirect_to' => $redirect_to,
			'created'     => time(),
			'expires'     => $expires_in ? time() + $expires_in : 0,
			'max_uses'    => $max_uses,
			'uses'        => 0,
		];

		update_option( self::OPTION_KEY, $links );

		wp_safe_redirect( add_query_arg( 'wppw_login_created', '1', wp_get_referer() ) );
		exit;
	}

	public function handle_revoke(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'wppw' ) );
		}
		check_admin_referer( 'wppw_login_revoke' );

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
