<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WebPagesPW_Admin {

	// Three independent WP options — one per section.
	const OPT_GENERAL  = 'wppw_general';
	const OPT_MESSAGES = 'wppw_messages';
	const OPT_DESIGN   = 'wppw_design';

	public function register_menu(): void {
		add_options_page(
			__( 'WebPagesPassworded', 'wppw' ),
			__( 'WebPagesPassworded', 'wppw' ),
			'manage_options',
			'wppw-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings(): void {
		register_setting( 'wppw_general_group',  self::OPT_GENERAL,  [ 'sanitize_callback' => [ $this, 'sanitize_general'  ] ] );
		register_setting( 'wppw_messages_group', self::OPT_MESSAGES, [ 'sanitize_callback' => [ $this, 'sanitize_messages' ] ] );
		register_setting( 'wppw_design_group',   self::OPT_DESIGN,   [ 'sanitize_callback' => [ $this, 'sanitize_design'   ] ] );
	}

	public function sanitize_general( $input ): array {
		return [ 'button_label' => sanitize_text_field( $input['button_label'] ?? '' ) ];
	}

	public function sanitize_messages( $input ): array {
		if ( ! wppw_is_premium() ) {
			return (array) get_option( self::OPT_MESSAGES, [] );
		}
		return [
			'msg_wrong_pw' => sanitize_text_field( $input['msg_wrong_pw'] ?? '' ),
			'msg_lockout'  => sanitize_text_field( $input['msg_lockout']  ?? '' ),
		];
	}

	public function sanitize_design( $input ): array {
		if ( ! wppw_is_premium() ) {
			return (array) get_option( self::OPT_DESIGN, [] );
		}
		$clean = [];

		foreach ( [ 'input_bg', 'input_text', 'input_border', 'btn_bg', 'btn_text' ] as $key ) {
			$clean[ $key ] = sanitize_hex_color( $input[ $key ] ?? '' ) ?? '';
		}

		$clean['input_size'] = absint( $input['input_size'] ?? 16 );
		$clean['btn_size']   = absint( $input['btn_size']   ?? 16 );
		$clean['field_gap']  = absint( $input['field_gap']  ?? 10 );

		$clean['btn_icon_fa'] = sanitize_text_field( $input['btn_icon_fa'] ?? '' );

		$allowed_pos              = [ 'left', 'right', 'top' ];
		$pos                      = $input['btn_icon_position'] ?? 'left';
		$clean['btn_icon_position'] = in_array( $pos, $allowed_pos, true ) ? $pos : 'left';

		return $clean;
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( 'settings_page_wppw-settings' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'wppw-font-awesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
			[],
			'6.5.1'
		);
		wp_enqueue_style(
			'wppw-admin',
			WPPW_PLUGIN_URL . 'assets/css/admin.css',
			[ 'wp-color-picker', 'wppw-font-awesome' ],
			WPPW_VERSION
		);
		wp_enqueue_script(
			'wppw-admin',
			WPPW_PLUGIN_URL . 'assets/js/admin.js',
			[ 'wp-color-picker', 'jquery' ],
			WPPW_VERSION,
			true
		);
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$g          = (array) get_option( self::OPT_GENERAL,  [] );
		$m          = (array) get_option( self::OPT_MESSAGES, [] );
		$d          = (array) get_option( self::OPT_DESIGN,   [] );
		$is_premium = wppw_is_premium();
		$locked     = $is_premium ? '' : ' wppw-locked';
		?>
		<div class="wrap wppw-settings-wrap">
			<h1><?php esc_html_e( 'WebPagesPassworded', 'wppw' ); ?></h1>

			<?php if ( ! $is_premium ) : ?>
				<div class="notice notice-info wppw-notice-test">
					<p>
						<?php esc_html_e( 'Para probar las funciones premium localmente, añade esta línea a tu wp-config.php:', 'wppw' ); ?>
						<code>define( 'WPPW_FORCE_PREMIUM', true );</code>
					</p>
				</div>
			<?php endif; ?>

			<!-- ══════════════════════════════════════
			     SECCIÓN 1 · Ajustes generales (free)
			     ══════════════════════════════════════ -->
			<form method="post" action="options.php">
				<?php settings_fields( 'wppw_general_group' ); ?>
				<div class="wppw-section">
					<h2><?php esc_html_e( 'Ajustes generales', 'wppw' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="wppw_button_label"><?php esc_html_e( 'Texto del botón', 'wppw' ); ?></label>
							</th>
							<td>
								<input type="text" id="wppw_button_label"
									name="<?php echo esc_attr( self::OPT_GENERAL ); ?>[button_label]"
									value="<?php echo esc_attr( $g['button_label'] ?? '' ); ?>"
									class="regular-text"
									placeholder="Enter" />
								<p class="description"><?php esc_html_e( 'Texto global del botón. El atributo label del shortcode siempre tiene prioridad.', 'wppw' ); ?></p>
							</td>
						</tr>
					</table>
					<p><?php esc_html_e( 'Shortcode:', 'wppw' ); ?> <code>[wppw]</code> &nbsp;|&nbsp;
					   <?php esc_html_e( 'Con parámetros:', 'wppw' ); ?> <code>[wppw label="Acceder" id="mi-formulario"]</code></p>
					<?php submit_button( __( 'Guardar ajustes generales', 'wppw' ) ); ?>
				</div>
			</form>

			<!-- ══════════════════════════════════════
			     SECCIÓN 2 · Mensajes de error (premium)
			     ══════════════════════════════════════ -->
			<form method="post" action="options.php">
				<?php settings_fields( 'wppw_messages_group' ); ?>
				<div class="wppw-section<?php echo esc_attr( $locked ); ?>">
					<?php $this->premium_badge( $is_premium ); ?>
					<h2><?php esc_html_e( 'Mensajes de error', 'wppw' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="wppw_msg_wrong_pw"><?php esc_html_e( 'Contraseña incorrecta', 'wppw' ); ?></label>
							</th>
							<td>
								<input type="text" id="wppw_msg_wrong_pw"
									name="<?php echo esc_attr( self::OPT_MESSAGES ); ?>[msg_wrong_pw]"
									value="<?php echo esc_attr( $m['msg_wrong_pw'] ?? '' ); ?>"
									class="large-text"
									placeholder="<?php esc_attr_e( 'La contraseña es incorrecta.', 'wppw' ); ?>"
									<?php disabled( ! $is_premium ); ?> />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="wppw_msg_lockout"><?php esc_html_e( 'Demasiados intentos', 'wppw' ); ?></label>
							</th>
							<td>
								<input type="text" id="wppw_msg_lockout"
									name="<?php echo esc_attr( self::OPT_MESSAGES ); ?>[msg_lockout]"
									value="<?php echo esc_attr( $m['msg_lockout'] ?? '' ); ?>"
									class="large-text"
									placeholder="<?php esc_attr_e( 'Demasiados intentos fallidos. Espera 15 minutos antes de intentarlo de nuevo.', 'wppw' ); ?>"
									<?php disabled( ! $is_premium ); ?> />
							</td>
						</tr>
					</table>
					<?php if ( $is_premium ) : ?>
						<?php submit_button( __( 'Guardar mensajes', 'wppw' ) ); ?>
					<?php else : ?>
						<p><a href="<?php echo esc_url( wppw_get_upgrade_url() ); ?>" class="button wppw-upgrade-btn">&#11088; <?php esc_html_e( 'Actualizar a Premium', 'wppw' ); ?></a></p>
					<?php endif; ?>
				</div>
			</form>

			<!-- ══════════════════════════════════════
			     SECCIÓN 3 · Diseño del formulario (premium)
			     ══════════════════════════════════════ -->
			<form method="post" action="options.php">
				<?php settings_fields( 'wppw_design_group' ); ?>
				<div class="wppw-section<?php echo esc_attr( $locked ); ?>">
					<?php $this->premium_badge( $is_premium ); ?>
					<h2><?php esc_html_e( 'Diseño del formulario', 'wppw' ); ?></h2>

					<h3><?php esc_html_e( 'Campo de contraseña', 'wppw' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Color de fondo', 'wppw' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_bg]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['input_bg'] ?? '#ffffff' ); ?>"
								<?php disabled( ! $is_premium ); ?> /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Color de texto', 'wppw' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_text]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['input_text'] ?? '#333333' ); ?>"
								<?php disabled( ! $is_premium ); ?> /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Color del borde', 'wppw' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_border]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['input_border'] ?? '#cccccc' ); ?>"
								<?php disabled( ! $is_premium ); ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label for="wppw_input_size"><?php esc_html_e( 'Tamaño de fuente (px)', 'wppw' ); ?></label></th>
							<td><input type="number" id="wppw_input_size"
								name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[input_size]"
								min="10" max="48"
								value="<?php echo esc_attr( $d['input_size'] ?? 16 ); ?>"
								class="small-text" <?php disabled( ! $is_premium ); ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label for="wppw_field_gap"><?php esc_html_e( 'Espacio hasta el botón (px)', 'wppw' ); ?></label></th>
							<td><input type="number" id="wppw_field_gap"
								name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[field_gap]"
								min="0" max="100"
								value="<?php echo esc_attr( $d['field_gap'] ?? 10 ); ?>"
								class="small-text" <?php disabled( ! $is_premium ); ?> /></td>
						</tr>
					</table>

					<h3><?php esc_html_e( 'Botón de envío', 'wppw' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Color de fondo', 'wppw' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_bg]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['btn_bg'] ?? '#0073aa' ); ?>"
								<?php disabled( ! $is_premium ); ?> /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Color de texto', 'wppw' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_text]"
								class="wppw-color-picker"
								value="<?php echo esc_attr( $d['btn_text'] ?? '#ffffff' ); ?>"
								<?php disabled( ! $is_premium ); ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label for="wppw_btn_size"><?php esc_html_e( 'Tamaño de fuente (px)', 'wppw' ); ?></label></th>
							<td><input type="number" id="wppw_btn_size"
								name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_size]"
								min="10" max="48"
								value="<?php echo esc_attr( $d['btn_size'] ?? 16 ); ?>"
								class="small-text" <?php disabled( ! $is_premium ); ?> /></td>
						</tr>
						<tr>
							<th scope="row"><label for="wppw_btn_icon_fa"><?php esc_html_e( 'Icono (Font Awesome)', 'wppw' ); ?></label></th>
							<td>
								<?php
								$fa_icons = [
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
								];
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
									<input type="text" id="wppw_btn_icon_fa"
										name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_icon_fa]"
										value="<?php echo esc_attr( $current_icon ); ?>"
										class="regular-text"
										placeholder="fa-solid fa-key"
										<?php disabled( ! $is_premium ); ?> />
									<span class="wppw-fa-live-preview">
										<?php if ( $current_icon ) : ?>
											<i class="<?php echo esc_attr( $current_icon ); ?>"></i>
										<?php endif; ?>
									</span>
								</div>
								<p class="description">
									<?php esc_html_e( 'Haz clic en un icono de la cuadrícula o escribe la clase de Font Awesome 6 Free.', 'wppw' ); ?>
									<a href="https://fontawesome.com/search?o=r&m=free" target="_blank" rel="noopener"><?php esc_html_e( 'Ver todos los iconos →', 'wppw' ); ?></a>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="wppw_btn_icon_position"><?php esc_html_e( 'Posición del icono', 'wppw' ); ?></label></th>
							<td>
								<?php $pos = $d['btn_icon_position'] ?? 'left'; ?>
								<select id="wppw_btn_icon_position"
									name="<?php echo esc_attr( self::OPT_DESIGN ); ?>[btn_icon_position]"
									<?php disabled( ! $is_premium ); ?>>
									<option value="left"  <?php selected( $pos, 'left' ); ?>><?php esc_html_e( 'Izquierda del texto', 'wppw' ); ?></option>
									<option value="right" <?php selected( $pos, 'right' ); ?>><?php esc_html_e( 'Derecha del texto', 'wppw' ); ?></option>
									<option value="top"   <?php selected( $pos, 'top' ); ?>><?php esc_html_e( 'Encima del texto', 'wppw' ); ?></option>
								</select>
							</td>
						</tr>
					</table>

					<?php if ( $is_premium ) : ?>
						<?php submit_button( __( 'Guardar diseño', 'wppw' ) ); ?>
					<?php else : ?>
						<p><a href="<?php echo esc_url( wppw_get_upgrade_url() ); ?>" class="button wppw-upgrade-btn">&#11088; <?php esc_html_e( 'Actualizar a Premium', 'wppw' ); ?></a></p>
					<?php endif; ?>
				</div>
			</form>

		</div>
		<?php
	}

	private function premium_badge( bool $is_premium ): void {
		if ( $is_premium ) {
			return;
		}
		?>
		<div class="wppw-premium-badge">&#11088; <?php esc_html_e( 'Premium', 'wppw' ); ?></div>
		<?php
	}
}
