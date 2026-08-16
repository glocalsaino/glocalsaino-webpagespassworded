<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WebPagesPW_Styles {

	public function enqueue_frontend_assets(): void {
		if ( ! is_singular() ) {
			return;
		}

		$s = glocalsaino_wppw_get_settings();

		// Always enqueue a lightweight base stylesheet so we have a handle
		// to attach dynamic inline styles via wp_add_inline_style().
		wp_enqueue_style(
			'glocalsaino-wppw-frontend',
			WPPW_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			WPPW_VERSION
		);

		if ( ! empty( $s['btn_icon_fa'] ) ) {
			wp_enqueue_style(
				'glocalsaino-wppw-font-awesome',
				WPPW_PLUGIN_URL . 'assets/vendor/font-awesome/css/all.min.css',
				array(),
				'6.5.1'
			);
		}

		$css = $this->build_css( $s );
		if ( ! empty( $css ) ) {
			wp_add_inline_style( 'glocalsaino-wppw-frontend', $css );
		}
	}

	private function build_css( array $s ): string {
		$input_rules = '';

		if ( ! empty( $s['input_bg'] ) ) {
			$input_rules .= 'background-color:' . sanitize_hex_color( $s['input_bg'] ) . '!important;';
		}
		if ( ! empty( $s['input_text'] ) ) {
			$input_rules .= 'color:' . sanitize_hex_color( $s['input_text'] ) . '!important;';
		}
		if ( ! empty( $s['input_border'] ) ) {
			$input_rules .= 'border-color:' . sanitize_hex_color( $s['input_border'] ) . '!important;';
		}
		if ( ! empty( $s['input_size'] ) ) {
			$input_rules .= 'font-size:' . absint( $s['input_size'] ) . 'px!important;';
		}
		if ( isset( $s['field_gap'] ) && '' !== $s['field_gap'] ) {
			$input_rules .= 'margin-bottom:' . absint( $s['field_gap'] ) . 'px!important;';
		}

		$btn_rules = '';

		if ( ! empty( $s['btn_bg'] ) ) {
			$btn_rules .= 'background-color:' . sanitize_hex_color( $s['btn_bg'] ) . '!important;';
		}
		if ( ! empty( $s['btn_text'] ) ) {
			$btn_rules .= 'color:' . sanitize_hex_color( $s['btn_text'] ) . '!important;';
		}
		if ( ! empty( $s['btn_size'] ) ) {
			$btn_rules .= 'font-size:' . absint( $s['btn_size'] ) . 'px!important;';
		}
		if ( ! empty( $s['btn_icon_fa'] ) ) {
			$position = $s['btn_icon_position'] ?? 'left';
			if ( 'top' === $position ) {
				$btn_rules .= 'display:inline-flex!important;flex-direction:column!important;align-items:center!important;gap:4px!important;';
			} else {
				$btn_rules .= 'display:inline-flex!important;align-items:center!important;gap:6px!important;';
			}
		}

		$css = '';

		if ( $input_rules ) {
			$css .= "body .glocalsaino-wppw-form-wrapper input[type=\"password\"]{{$input_rules}}\n";
		}
		if ( $btn_rules ) {
			$css .= "body .glocalsaino-wppw-form-wrapper button[type=\"submit\"]{{$btn_rules}}\n";
		}

		return $css;
	}
}
