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

	public function render_extensions_page(): void {
		$banner_url = GLOCALSAINO_WPPW_URL . 'assets/img/magic-links-banner.png';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Extensions', 'glocalsaino-webpagespassworded' ); ?></h1>

			<img src="<?php echo esc_url( $banner_url ); ?>"
				alt="<?php esc_attr_e( 'GlocalSaino WPPW Magic Links', 'glocalsaino-webpagespassworded' ); ?>"
				style="max-width:772px;width:100%;height:auto;display:block;margin:16px 0 24px;" />

			<p style="font-size:14px;max-width:760px;">
				<?php esc_html_e( 'GlocalSaino WPPW Magic Links lets you generate signed links that grant direct access to password-protected pages — no password form, no friction. Control expiry, usage limits, and revoke any link instantly.', 'glocalsaino-webpagespassworded' ); ?>
			</p>

			<ol style="max-width:760px;font-size:14px;line-height:1.8;">
				<li><strong><?php esc_html_e( 'Send access, not passwords.', 'glocalsaino-webpagespassworded' ); ?></strong>
					<?php esc_html_e( 'Stop copying and pasting passwords into emails. Generate a single link and share it — your client clicks it and lands directly on the protected page.', 'glocalsaino-webpagespassworded' ); ?></li>
				<li><strong><?php esc_html_e( 'Control who gets in and for how long.', 'glocalsaino-webpagespassworded' ); ?></strong>
					<?php esc_html_e( 'Set an expiry date or a maximum number of uses on each link. When a project ends or a client\'s access period is over, the link stops working on its own.', 'glocalsaino-webpagespassworded' ); ?></li>
				<li><strong><?php esc_html_e( 'Protect your passwords without exposing them.', 'glocalsaino-webpagespassworded' ); ?></strong>
					<?php esc_html_e( 'The real page password never appears in the URL. You can share access with dozens of individual people, revoke any one of them instantly, and the underlying password remains unchanged for everyone else.', 'glocalsaino-webpagespassworded' ); ?></li>
			</ol>

			<hr style="max-width:760px;margin:24px 0;" />

			<p style="font-size:14px;max-width:760px;">
				<?php esc_html_e( 'Managing password-protected content should not mean managing passwords. GlocalSaino WPPW Magic Links removes that friction entirely.', 'glocalsaino-webpagespassworded' ); ?>
			</p>

			<h2><?php esc_html_e( 'Share access without sharing passwords', 'glocalsaino-webpagespassworded' ); ?></h2>
			<p style="max-width:760px;font-size:14px;">
				<?php esc_html_e( 'When you protect a page with a password, sharing that password is the weakest link in your workflow. Anyone who receives it can forward it, anyone can save it, and changing it means notifying everyone all over again. Magic links break that cycle. Each link is a unique, signed token that grants access to one specific page. The real password stays private — only the token travels in the URL.', 'glocalsaino-webpagespassworded' ); ?>
			</p>

			<h2><?php esc_html_e( 'Access that expires on your terms', 'glocalsaino-webpagespassworded' ); ?></h2>
			<p style="max-width:760px;font-size:14px;">
				<?php esc_html_e( 'Every magic link you generate can have its own rules. Set it to expire after one day, one week, or one month. Limit it to a single use or a fixed number of visits. When the link expires or runs out of uses, access stops automatically — no manual intervention required. If you want the visitor to land somewhere specific when a link no longer works, add a fallback URL and they will be redirected gracefully instead of hitting a dead end.', 'glocalsaino-webpagespassworded' ); ?>
			</p>

			<h2><?php esc_html_e( 'A clear dashboard, full control', 'glocalsaino-webpagespassworded' ); ?></h2>
			<p style="max-width:760px;font-size:14px;">
				<?php esc_html_e( 'The Magic Links panel shows every link you have generated: the target page, the label you assigned, the expiry date, the number of uses, and the current status — active, expired, or exhausted. Revoking a link takes one click and takes effect immediately.', 'glocalsaino-webpagespassworded' ); ?>
			</p>

			<p style="margin-top:24px;">
				<a href="https://glocalsaino.com/wppw-magic-link" target="_blank" rel="noopener noreferrer" class="button button-primary button-large">
					<?php esc_html_e( 'Learn more and buy →', 'glocalsaino-webpagespassworded' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
