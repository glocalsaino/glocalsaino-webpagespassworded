<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GlocalSaino_Wppw_Core {

	public function wppw_shortcode( $atts ): string {
		global $post;

		$settings = glocalsaino_wppw_get_settings();

		$default_label = ! empty( $settings['button_label'] )
			? $settings['button_label']
			: __( 'Enter', 'glocalsaino-webpagespassworded' );

		$atts = shortcode_atts(
			array(
				'label'  => $default_label,
				'id'     => 'glocalsaino-wppw-login',
				'parent' => $post->ID,
			),
			$atts
		);

		$form_id = esc_attr( $atts['id'] );
		$label   = esc_html( $atts['label'] );
		$parent  = (int) $atts['parent'];

		$msg_wrong_pw = ! empty( $settings['msg_wrong_pw'] )
			? esc_html( $settings['msg_wrong_pw'] )
			: esc_html__( 'Incorrect password.', 'glocalsaino-webpagespassworded' );

		$msg_lockout = ! empty( $settings['msg_lockout'] )
			? esc_html( $settings['msg_lockout'] )
			: esc_html__( 'Too many failed attempts. Please wait 15 minutes before trying again.', 'glocalsaino-webpagespassworded' );

		$button_inner = $label;
		if ( ! empty( $settings['btn_icon_fa'] ) ) {
			$icon_class = esc_attr( $settings['btn_icon_fa'] );
			$icon_pos   = $settings['btn_icon_position'] ?? 'left';
			$icon_tag   = "<i class=\"{$icon_class}\" aria-hidden=\"true\"></i>";
			$label_span = "<span class=\"glocalsaino-wppw-btn-label\">{$label}</span>";

			if ( 'top' === $icon_pos ) {
				$button_inner = "{$icon_tag}{$label_span}";
			} elseif ( 'right' === $icon_pos ) {
				$button_inner = "{$label_span}{$icon_tag}";
			} else {
				$button_inner = "{$icon_tag}{$label_span}";
			}
		}

		$permalink = esc_url( get_permalink() );
		$nonce     = wp_create_nonce( 'glocalsaino_wppw_page' );

		$result  = "<div class=\"glocalsaino-wppw-form-wrapper\">\n";
		$result .= "<form id=\"{$form_id}\" method=\"post\" action=\"{$permalink}\">\n";

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- these GET flags are set internally by process_form(), not by user input.
		if ( isset( $_GET['glocalsaino_wppw_locked'] ) ) {
			$result .= "<p class=\"glocalsaino-wppw-error\">{$msg_lockout}</p>\n";
		} elseif ( isset( $_GET['glocalsaino_wppw_wrongpw'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$result .= "<p class=\"glocalsaino-wppw-error\">{$msg_wrong_pw}</p>\n";
		}

		$result .= "\t<input class=\"requiredField\" type=\"password\" name=\"glocalsaino_wppw_password\" id=\"glocalsaino-wppw-password\" value=\"\" />\n";
		$result .= "\t<input type=\"hidden\" name=\"glocalsaino_wppw_parent\" value=\"" . esc_attr( (string) $parent ) . "\" />\n";
		$result .= "\t<input type=\"hidden\" name=\"glocalsaino_wppw_nonce\" value=\"" . esc_attr( $nonce ) . "\" />\n";
		$result .= "\t<button type=\"submit\">{$button_inner}</button>\n";
		$result .= "</form>\n";
		$result .= "</div>\n";

		return $result;
	}

	private function get_client_ip(): string {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REMOTE_ADDR is a server value, not user input; sanitize_text_field strips valid IPv6 chars.
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}

	private function ip_transient_key( string $prefix ): string {
		return $prefix . md5( $this->get_client_ip() );
	}

	private function is_locked_out(): bool {
		return (bool) get_transient( $this->ip_transient_key( 'glocalsaino_wppw_lock_' ) );
	}

	private function record_failure(): void {
		$key      = $this->ip_transient_key( 'glocalsaino_wppw_fail_' );
		$attempts = (int) get_transient( $key ) + 1;
		set_transient( $key, $attempts, GLOCALSAINO_WPPW_LOCKOUT_SECONDS );

		if ( $attempts >= GLOCALSAINO_WPPW_MAX_ATTEMPTS ) {
			set_transient( $this->ip_transient_key( 'glocalsaino_wppw_lock_' ), 1, GLOCALSAINO_WPPW_LOCKOUT_SECONDS );
		}
	}

	private function clear_rate_limit(): void {
		delete_transient( $this->ip_transient_key( 'glocalsaino_wppw_fail_' ) );
		delete_transient( $this->ip_transient_key( 'glocalsaino_wppw_lock_' ) );
	}

	public function pw_redirect( string $perma, string $password ): void {
		global $wp_hasher;

		$cookiePW = wp_unslash( $password );

		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}

		$cookiePW = $wp_hasher->HashPassword( $cookiePW );
		$secure   = ( 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) );

		setcookie(
			'wp-postpass_' . COOKIEHASH,
			$cookiePW,
			array(
				'expires'  => time() + GLOCALSAINO_WPPW_COOKIE_SECONDS,
				'path'     => COOKIEPATH,
				'domain'   => COOKIE_DOMAIN,
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Strict',
			)
		);

		wp_safe_redirect( $perma );
		exit();
	}

	public function process_form(): void {
		if ( ! isset( $_POST['glocalsaino_wppw_password'], $_POST['glocalsaino_wppw_parent'], $_POST['glocalsaino_wppw_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glocalsaino_wppw_nonce'] ) ), 'glocalsaino_wppw_page' ) ) {
			return;
		}

		if ( $this->is_locked_out() ) {
			$_GET['glocalsaino_wppw_locked'] = true;
			return;
		}

		$parentForm   = (int) $_POST['glocalsaino_wppw_parent'];
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords cannot be sanitized without corrupting special characters.
		$postPassword = wp_unslash( (string) $_POST['glocalsaino_wppw_password'] );

		$args = array(
			'sort_order'   => 'DESC',
			'sort_column'  => 'post_date',
			'hierarchical' => 1,
			'child_of'     => $parentForm,
			'parent'       => $parentForm,
			'post_type'    => 'page',
			'post_status'  => 'publish',
		);

		if ( function_exists( 'pause_exclude_pages' ) ) {
			pause_exclude_pages();
		}

		$myPages = get_pages( $args );

		if ( function_exists( 'resume_exclude_pages' ) ) {
			resume_exclude_pages();
		}

		foreach ( $myPages as $page ) {
			if ( $page->post_password === $postPassword ) {
				$this->clear_rate_limit();
				$this->pw_redirect( get_permalink( $page->ID ), $postPassword );
			}
		}

		$this->record_failure();
		$_GET['glocalsaino_wppw_wrongpw'] = true;
	}
}

function glocalsaino_wppw_get_settings(): array {
	return array_merge(
		(array) get_option( 'glocalsaino_wppw_general',  array() ),
		(array) get_option( 'glocalsaino_wppw_messages', array() ),
		(array) get_option( 'glocalsaino_wppw_design',   array() )
	);
}
