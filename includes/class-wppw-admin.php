<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GlocalSaino_Wppw_Admin {

	private const OPT_GENERAL  = 'glocalsaino_wppw_general';
	private const OPT_MESSAGES = 'glocalsaino_wppw_messages';
	private const OPT_DESIGN   = 'glocalsaino_wppw_design';

	public function register_menu(): void {
		add_menu_page(
			__( 'GlocalSaino WebPagesPassworded', 'glocalsaino-webpagespassworded' ),
			__( 'WebPagesPassworded', 'glocalsaino-webpagespassworded' ),
			'manage_options',
			'glocalsaino-wppw-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-lock',
			80
		);

		add_submenu_page(
			'glocalsaino-wppw-settings',
			__( 'GlocalSaino WebPagesPassworded', 'glocalsaino-webpagespassworded' ),
			__( 'Settings', 'glocalsaino-webpagespassworded' ),
			'manage_options',
			'glocalsaino-wppw-settings',
			array( $this, 'render_settings_page' )
		);

	}

	public function register_extensions_submenu(): void {
		add_submenu_page(
			'glocalsaino-wppw-settings',
			__( 'Extensions', 'glocalsaino-webpagespassworded' ),
			__( 'Extensions', 'glocalsaino-webpagespassworded' ),
			'manage_options',
			'glocalsaino-wppw-extensions',
			array( $this, 'render_extensions_page' )
		);
	}

	public function register_settings(): void {
		register_setting( 'glocalsaino_wppw_general_group',  self::OPT_GENERAL,  array( 'sanitize_callback' => array( $this, 'sanitize_general'  ) ) );
		register_setting( 'glocalsaino_wppw_messages_group', self::OPT_MESSAGES, array( 'sanitize_callback' => array( $this, 'sanitize_messages' ) ) );
		register_setting( 'glocalsaino_wppw_design_group',   self::OPT_DESIGN,   array( 'sanitize_callback' => array( $this, 'sanitize_design'   ) ) );
	}

	public function sanitize_general( $input ): array {
		return array( 'button_label' => sanitize_text_field( $input['button_label'] ?? '' ) );
	}

	public function sanitize_messages( $input ): array {
		return array(
			'msg_wrong_pw' => sanitize_text_field( $input['msg_wrong_pw'] ?? '' ),
			'msg_lockout'  => sanitize_text_field( $input['msg_lockout']  ?? '' ),
		);
	}

	public function sanitize_design( $input ): array {
		$clean = array();

		foreach ( array( 'input_bg', 'input_text', 'input_border', 'btn_bg', 'btn_text' ) as $key ) {
			$clean[ $key ] = sanitize_hex_color( $input[ $key ] ?? '' ) ?? '';
		}

		$clean['input_size'] = absint( $input['input_size'] ?? 16 );
		$clean['btn_size']   = absint( $input['btn_size']   ?? 16 );
		$clean['field_gap']  = absint( $input['field_gap']  ?? 10 );

		$clean['btn_icon_fa'] = sanitize_text_field( $input['btn_icon_fa'] ?? '' );

		$allowed_pos                = array( 'left', 'right', 'top' );
		$pos                        = $input['btn_icon_position'] ?? 'left';
		$clean['btn_icon_position'] = in_array( $pos, $allowed_pos, true ) ? $pos : 'left';

		return $clean;
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( 'toplevel_page_glocalsaino-wppw-settings' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'glocalsaino-wppw-font-awesome',
			GLOCALSAINO_WPPW_URL . 'assets/vendor/font-awesome/css/all.min.css',
			array(),
			'6.5.1'
		);
		wp_enqueue_style(
			'glocalsaino-wppw-admin',
			GLOCALSAINO_WPPW_URL . 'assets/css/admin.css',
			array( 'wp-color-picker', 'glocalsaino-wppw-font-awesome' ),
			GLOCALSAINO_WPPW_VERSION
		);
		wp_enqueue_script(
			'glocalsaino-wppw-admin',
			GLOCALSAINO_WPPW_URL . 'assets/js/admin.js',
			array( 'wp-color-picker', 'jquery' ),
			GLOCALSAINO_WPPW_VERSION,
			true
		);
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$g = (array) get_option( self::OPT_GENERAL,  array() );
		$m = (array) get_option( self::OPT_MESSAGES, array() );
		$d = (array) get_option( self::OPT_DESIGN,   array() );
		?>
		<div class="wrap wppw-settings-wrap">
			<h1><?php esc_html_e( 'GlocalSaino WebPagesPassworded', 'glocalsaino-webpagespassworded' ); ?></h1>

			<!-- ══ SECTION 1 · General settings ══ -->
			<form method="post" action="options.php">
				<?php settings_fields( 'glocalsaino_wppw_general_group' ); ?>
				<div class="wppw-section">
					<h2><?php esc_html_e( 'General settings', 'glocalsaino-webpagespassworded' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="glocalsaino_wppw_button_label"><?php esc_html_e( 'Button label', 'glocalsaino-webpagespassworded' ); ?></label>
							</th>
							<td>
								<input type="text" id="glocalsaino_wppw_button_label"
									name="<?php echo esc_attr( self::OPT_GENERAL ); ?>[button_label]"
									value="<?php echo esc_attr( $g['button_label'] ?? '' ); ?>"
									class="regular-text"
									placeholder="Enter" />
								<p class="description"><?php esc_html_e( 'Global button text. The shortcode label attribute always takes precedence.', 'glocalsaino-webpagespassworded' ); ?></p>
							</td>
						</tr>
					</table>
					<p><?php esc_html_e( 'Shortcode:', 'glocalsaino-webpagespassworded' ); ?> <code>[glocalsaino_wppw]</code> &nbsp;|&nbsp;
					   <?php esc_html_e( 'With parameters:', 'glocalsaino-webpagespassworded' ); ?> <code>[glocalsaino_wppw label="Enter" id="my-form"]</code></p>
					<?php submit_button( __( 'Save general settings', 'glocalsaino-webpagespassworded' ) ); ?>
				</div>
			</form>

			<!-- ══ SECTION 2 · Error messages ══ -->
			<form method="post" action="options.php">
				<?php settings_fields( 'glocalsaino_wppw_messages_group' ); ?>
				<div class="wppw-section">
					<h2><?php esc_html_e( 'Error messages', 'glocalsaino-webpagespassworded' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="glocalsaino_wppw_msg_wrong_pw"><?php esc_html_e( 'Wrong password', 'glocalsaino-webpagespassworded' ); ?></label>
							</th>
							<td>
								<input type="text" id="glocalsaino_wppw_msg_wrong_pw"
									name="<?php echo esc_attr( self::OPT_MESSAGES ); ?>[msg_wrong_pw]"
									value="<?php echo esc_attr( $m['msg_wrong_pw'] ?? '' ); ?>"
									class="large-text"
									placeholder="<?php esc_attr_e( 'Incorrect password.', 'glocalsaino-webpagespassworded' ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="glocalsaino_wppw_msg_lockout"><?php esc_html_e( 'Too many attempts', 'glocalsaino-webpagespassworded' ); ?></label>
							</th>
							<td>
								<input type="text" id="glocalsaino_wppw_msg_lockout"
									name="<?php echo esc_attr( self::OPT_MESSAGES ); ?>[msg_lockout]"
									value="<?php echo esc_attr( $m['msg_lockout'] ?? '' ); ?>"
									class="large-text"
									placeholder="<?php esc_attr_e( 'Too many failed attempts. Please wait 15 minutes before trying again.', 'glocalsaino-webpagespassworded' ); ?>" />
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save messages', 'glocalsaino-webpagespassworded' ) ); ?>
				</div>
			</form>

			<!-- ══ SECTION 3 · Form design ══ -->
			<form method="post" action="options.php">
				<?php settings_fields( 'glocalsaino_wppw_design_group' ); ?>
				<div class="wppw-section">
					<h2><?php esc_html_e( 'Form design', 'glocalsaino-webpagespassworded' ); ?></h2>

					<h3><?php esc_html_e( 'Password field', 'glocalsaino-webpagespassworded' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Background color', 'glocalsaino-webpagespassworded' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_bg]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['input_bg'] ?? '#ffffff' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Text color', 'glocalsaino-webpagespassworded' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_text]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['input_text'] ?? '#333333' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Border color', 'glocalsaino-webpagespassworded' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_border]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['input_border'] ?? '#cccccc' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="glocalsaino_wppw_input_size"><?php esc_html_e( 'Font size (px)', 'glocalsaino-webpagespassworded' ); ?></label></th>
							<td><input type="number" id="glocalsaino_wppw_input_size"
								name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_size]"
								min="10" max="48"
								value="<?php echo esc_attr( $d['input_size'] ?? 16 ); ?>"
								class="small-text" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="glocalsaino_wppw_field_gap"><?php esc_html_e( 'Gap to button (px)', 'glocalsaino-webpagespassworded' ); ?></label></th>
							<td><input type="number" id="glocalsaino_wppw_field_gap"
								name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[field_gap]"
								min="0" max="100"
								value="<?php echo esc_attr( $d['field_gap'] ?? 10 ); ?>"
								class="small-text" /></td>
						</tr>
					</table>

					<h3><?php esc_html_e( 'Submit button', 'glocalsaino-webpagespassworded' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Background color', 'glocalsaino-webpagespassworded' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_bg]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['btn_bg'] ?? '#0073aa' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Text color', 'glocalsaino-webpagespassworded' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_text]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['btn_text'] ?? '#ffffff' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="glocalsaino_wppw_btn_size"><?php esc_html_e( 'Font size (px)', 'glocalsaino-webpagespassworded' ); ?></label></th>
							<td><input type="number" id="glocalsaino_wppw_btn_size"
								name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_size]"
								min="10" max="48"
								value="<?php echo esc_attr( $d['btn_size'] ?? 16 ); ?>"
								class="small-text" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="glocalsaino_wppw_btn_icon_fa"><?php esc_html_e( 'Icon (Font Awesome)', 'glocalsaino-webpagespassworded' ); ?></label></th>
							<td>
								<?php
								$fa_icons = array(
									'fa-solid fa-lock'             => 'fa-lock',
									'fa-solid fa-key'              => 'fa-key',
									'fa-solid fa-right-to-bracket' => 'fa-right-to-bracket',
									'fa-solid fa-user'             => 'fa-user',
									'fa-solid fa-shield-halved'    => 'fa-shield-halved',
									'fa-solid fa-eye'              => 'fa-eye',
									'fa-solid fa-unlock'           => 'fa-unlock',
									'fa-solid fa-fingerprint'      => 'fa-fingerprint',
									'fa-solid fa-id-card'          => 'fa-id-card',
									'fa-solid fa-circle-check'     => 'fa-circle-check',
									'fa-solid fa-arrow-right'      => 'fa-arrow-right',
									'fa-solid fa-door-open'        => 'fa-door-open',
									'fa-solid fa-circle-user'      => 'fa-circle-user',
									'fa-solid fa-bell'             => 'fa-bell',
									'fa-solid fa-star'             => 'fa-star',
								);
								$current_icon = $d['btn_icon_fa'] ?? '';
								?>
								<div class="wppw-fa-quick-pick">
									<?php foreach ( $fa_icons as $class => $slug ) : ?>
										<button type="button"
											class="wppw-fa-tile<?php echo $current_icon === $class ? ' wppw-fa-tile--active' : ''; ?>"
											data-fa="<?php echo esc_attr( $class ); ?>"
											title="<?php echo esc_attr( $class ); ?>">
											<i class="<?php echo esc_attr( $class ); ?>"></i>
										</button>
									<?php endforeach; ?>
								</div>
								<div class="wppw-fa-custom">
									<input type="text" id="glocalsaino_wppw_btn_icon_fa"
										name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_icon_fa]"
										value="<?php echo esc_attr( $current_icon ); ?>"
										class="regular-text"
										placeholder="fa-solid fa-key" />
									<span class="wppw-fa-live-preview">
										<?php if ( $current_icon ) : ?>
											<i class="<?php echo esc_attr( $current_icon ); ?>"></i>
										<?php endif; ?>
									</span>
								</div>
								<p class="description">
									<?php esc_html_e( 'Click an icon in the grid or type a Font Awesome 6 Free class.', 'glocalsaino-webpagespassworded' ); ?>
									<a href="https://fontawesome.com/search?o=r&m=free" target="_blank" rel="noopener"><?php esc_html_e( 'Browse all icons →', 'glocalsaino-webpagespassworded' ); ?></a>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="glocalsaino_wppw_btn_icon_position"><?php esc_html_e( 'Icon position', 'glocalsaino-webpagespassworded' ); ?></label></th>
							<td>
								<?php $pos = $d['btn_icon_position'] ?? 'left'; ?>
								<select id="glocalsaino_wppw_btn_icon_position"
									name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_icon_position]">
									<option value="left"  <?php selected( $pos, 'left' ); ?>><?php esc_html_e( 'Left of text', 'glocalsaino-webpagespassworded' ); ?></option>
									<option value="right" <?php selected( $pos, 'right' ); ?>><?php esc_html_e( 'Right of text', 'glocalsaino-webpagespassworded' ); ?></option>
									<option value="top"   <?php selected( $pos, 'top' ); ?>><?php esc_html_e( 'Above text', 'glocalsaino-webpagespassworded' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save design', 'glocalsaino-webpagespassworded' ) ); ?>
				</div>
			</form>
		</div>
		<?php
	}

	// Tarjeta compacta reutilizable (icono + nombre + una frase + estado/CTA)
	// — mismo formato en los tres plugins de la familia (GlocalSaino
	// Auctions Displayed by Shortcodes, Layer Map Viewer y este), para que
	// las tres páginas de "Extensions" se vean consistentes entre sí.
	private function render_extension_card( string $name, string $description, bool $active, string $url, string $icon_url = '' ): void {
		?>
		<div style="background:#fff;border:1px solid #ccd0d4;padding:20px;max-width:640px;margin-top:16px;display:flex;gap:16px;align-items:flex-start;">
			<?php if ( $icon_url ) : ?>
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $name ); ?>"
					width="48" height="48" style="width:48px;height:48px;border-radius:8px;flex-shrink:0;">
			<?php endif; ?>
			<div>
				<h2 style="margin-top:0;"><?php echo esc_html( $name ); ?></h2>
				<p><?php echo esc_html( $description ); ?></p>
				<?php if ( $active ) : ?>
					<p><strong><?php esc_html_e( 'Active', 'glocalsaino-webpagespassworded' ); ?></strong></p>
				<?php else : ?>
					<p>
						<a class="button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
							<?php esc_html_e( 'Learn more', 'glocalsaino-webpagespassworded' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function render_extensions_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Extensions', 'glocalsaino-webpagespassworded' ); ?></h1>

			<?php
			$this->render_extension_card(
				__( 'GlocalSaino WPPW Magic Links', 'glocalsaino-webpagespassworded' ),
				__( 'Generate signed links that grant direct access to password-protected pages — no password form, no friction. Control expiry, usage limits, and revoke any link instantly.', 'glocalsaino-webpagespassworded' ),
				class_exists( 'GlocalSaino_Wppw_ML_Core' ),
				'https://glocalsaino.com/wppw-magic-link',
				GLOCALSAINO_WPPW_URL . 'assets/img/icon-wppw-magic-link.png'
			);
			?>

			<h2 style="margin-top:32px;"><?php esc_html_e( 'What are you protecting?', 'glocalsaino-webpagespassworded' ); ?></h2>
			<p style="max-width:760px;font-size:14px;">
				<?php esc_html_e( 'WebPagesPassworded controls who gets in — these two free GlocalSaino plugins give visitors something worth getting in for.', 'glocalsaino-webpagespassworded' ); ?>
			</p>

			<?php
			$this->render_extension_card(
				__( 'GlocalSaino Auctions Displayed by Shortcodes', 'glocalsaino-webpagespassworded' ),
				__( "Run a private, invitation-only auction. Put the auction page behind a password or a Magic Link, so only people you've vetted can even see the bidding.", 'glocalsaino-webpagespassworded' ),
				class_exists( 'GSADS_Settings' ),
				'https://glocalsaino.com/auctionsdisplayedbyshortcodes/',
				GLOCALSAINO_WPPW_URL . 'assets/img/icon-auctions-displayed-by-shortcodes.png'
			);

			$this->render_extension_card(
				__( 'GlocalSaino Layer Map Viewer', 'glocalsaino-webpagespassworded' ),
				__( 'Protect a map with sensitive or unpublished data. Show parcel boundaries, property details, or live-tracked assets only to people who have the password or the link.', 'glocalsaino-webpagespassworded' ),
				function_exists( 'kml_map_tile_dir' ),
				'https://glocalsaino.com/layermapviewer/',
				GLOCALSAINO_WPPW_URL . 'assets/img/icon-glocalsaino-layer-map-viewer.png'
			);
			?>
		</div>
		<?php
	}
}
