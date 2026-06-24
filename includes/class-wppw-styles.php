<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WebPagesPW_Styles {

	/**
	 * Enqueues Font Awesome via wp_enqueue_scripts (fires before wp_head prints styles).
	 * No shortcode detection here — if premium + icon is configured, we load it on
	 * every singular page to avoid false negatives with page builders.
	 */
	public function enqueue_frontend_assets(): void {
		if ( ! wppw_is_premium() || ! is_singular() ) {
			return;
		}
		$s = wppw_get_settings();
		if ( ! empty( $s['btn_icon_fa'] ) ) {
			wp_enqueue_style(
				'wppw-font-awesome',
				'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
				[],
				'6.5.1'
			);
		}
	}

	/**
	 * Outputs inline CSS in <head> on every singular page when premium is active.
	 * Skipping shortcode detection avoids false negatives with Elementor and other
	 * page builders that store content outside post_content.
	 */
	public function output_form_styles(): void {
		if ( ! wppw_is_premium() || ! is_singular() ) {
			return;
		}

		$css = $this->build_css( wppw_get_settings() );

		if ( empty( $css ) ) {
			return;
		}

		echo "<style id=\"wppw-form-styles\">\n{$css}\n</style>\n";
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
			$css .= "body .wppw-form-wrapper input[type=\"password\"]{{$input_rules}}\n";
		}
		if ( $btn_rules ) {
			$css .= "body .wppw-form-wrapper button[type=\"submit\"]{{$btn_rules}}\n";
		}

		return $css;
	}
}
