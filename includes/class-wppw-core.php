<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WebPagesPW_Core {

	public function wppw_shortcode( $atts ): string {
		global $post;

		$settings = wppw_get_settings();

		// Global label from settings (free) takes precedence over shortcode default, but shortcode attr overrides all.
		$default_label = ! empty( $settings['button_label'] )
			? $settings['button_label']
			: __( 'Enter', 'webpagespassworded' );

		$atts = shortcode_atts(
			[
				'label'  => $default_label,
				'id'     => 'wppwLogin',
				'parent' => $post->ID,
			],
			$atts
		);

		$form_id = esc_attr( $atts['id'] );
		$label   = esc_html( $atts['label'] );
		$parent  = (int) $atts['parent'];

		$msg_wrong_pw = esc_html__( 'La contraseña es incorrecta.', 'webpagespassworded' );
		$msg_lockout  = esc_html__( 'Demasiados intentos fallidos. Espera 15 minutos antes de intentarlo de nuevo.', 'webpagespassworded' );

		if ( web_fs() && web_fs()->can_use_premium_code__premium_only() ) {
			if ( ! empty( $settings['msg_wrong_pw'] ) ) {
				$msg_wrong_pw = esc_html( $settings['msg_wrong_pw'] );
			}
			if ( ! empty( $settings['msg_lockout'] ) ) {
				$msg_lockout = esc_html( $settings['msg_lockout'] );
			}
		}

		// Premium: Font Awesome icon with configurable position.
		$button_inner = $label;
		if ( web_fs() && web_fs()->can_use_premium_code__premium_only() ) {
			if ( ! empty( $settings['btn_icon_fa'] ) ) {
				$icon_class = esc_attr( $settings['btn_icon_fa'] );
				$icon_pos   = $settings['btn_icon_position'] ?? 'left';
				$icon_tag   = "<i class=\"{$icon_class}\" aria-hidden=\"true\"></i>";
				$label_span = "<span class=\"wppw-btn-label\">{$label}</span>";

				if ( 'top' === $icon_pos ) {
					$button_inner = "{$icon_tag}{$label_span}";
				} elseif ( 'right' === $icon_pos ) {
					$button_inner = "{$label_span}{$icon_tag}";
				} else {
					$button_inner = "{$icon_tag}{$label_span}";
				}
			}
		}

		$permalink = esc_url( get_permalink() );
		$nonce     = wp_create_nonce( 'wppwPage' );

		$result  = "<div class=\"wppw-form-wrapper\">\n";
		$result .= "<form id=\"{$form_id}\" method=\"post\" action=\"{$permalink}\">\n";

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- these GET flags are set internally by process_form(), not by user input.
		if ( isset( $_GET['wppwlocked'] ) ) {
			$result .= "<p id=\"wppwError\">{$msg_lockout}</p>\n";
		} elseif ( isset( $_GET['wrongpw'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$result .= "<p id=\"wppwError\">{$msg_wrong_pw}</p>\n";
		}

		$result .= "\t<input class=\"requiredField\" type=\"password\" name=\"wppwPassword\" id=\"wppwPassword\" value=\"\" />\n";
		$result .= "\t<input type=\"hidden\" name=\"wppwParent\" value=\"{$parent}\" />\n";
		$result .= "\t<input type=\"hidden\" name=\"wppwPage_nonce\" value=\"{$nonce}\" />\n";
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
		return (bool) get_transient( $this->ip_transient_key( 'wppw_lock_' ) );
	}

	private function record_failure(): void {
		$key      = $this->ip_transient_key( 'wppw_fail_' );
		$attempts = (int) get_transient( $key ) + 1;
		set_transient( $key, $attempts, WPPW_LOCKOUT_SECONDS );

		if ( $attempts >= WPPW_MAX_ATTEMPTS ) {
			set_transient( $this->ip_transient_key( 'wppw_lock_' ), 1, WPPW_LOCKOUT_SECONDS );
		}
	}

	private function clear_rate_limit(): void {
		delete_transient( $this->ip_transient_key( 'wppw_fail_' ) );
		delete_transient( $this->ip_transient_key( 'wppw_lock_' ) );
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
			[
				'expires'  => time() + SECONDS_TO_STORE_PW,
				'path'     => COOKIEPATH,
				'domain'   => COOKIE_DOMAIN,
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Strict',
			]
		);

		wp_safe_redirect( $perma );
		exit();
	}

	public function process_form(): void {
		if ( ! isset( $_POST['wppwPassword'], $_POST['wppwParent'], $_POST['wppwPage_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wppwPage_nonce'] ) ), 'wppwPage' ) ) {
			return;
		}

		if ( $this->is_locked_out() ) {
			$_GET['wppwlocked'] = true;
			return;
		}

		$parentForm   = (int) $_POST['wppwParent'];
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords cannot be sanitized without corrupting special characters.
		$postPassword = wp_unslash( (string) $_POST['wppwPassword'] );

		$args = [
			'sort_order'   => 'DESC',
			'sort_column'  => 'post_date',
			'hierarchical' => 1,
			'child_of'     => $parentForm,
			'parent'       => $parentForm,
			'post_type'    => 'page',
			'post_status'  => 'publish',
		];

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

		// Password submitted but no match found — increment failure counter and signal the form.
		$this->record_failure();
		$_GET['wrongpw'] = true;
	}
}

/**
 * Returns all plugin settings merged from the three independent option keys.
 */
function wppw_get_settings(): array {
	return array_merge(
		(array) get_option( 'wppw_general',  [] ),
		(array) get_option( 'wppw_messages', [] ),
		(array) get_option( 'wppw_design',   [] )
	);
}
