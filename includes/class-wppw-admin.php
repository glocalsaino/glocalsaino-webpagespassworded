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
			WPPW_PLUGIN_URL . 'assets/vendor/font-awesome/css/all.min.css',
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

			<!-- ══════════════════════════════════════
			     SECCIÓN 4 · Enlaces mágicos (premium)
			     ══════════════════════════════════════ -->
			<?php $this->render_magic_links_section( $is_premium, $locked ); ?>

		</div>
		<?php
	}

	private function render_magic_links_section( bool $is_premium, string $locked ): void {
		$magic           = new WebPagesPW_MagicLinks();
		$protected_pages = $magic->get_protected_pages();
		$links           = $magic->get_links();
		?>
		<div class="wppw-section<?php echo esc_attr( $locked ); ?>">
			<?php $this->premium_badge( $is_premium ); ?>
			<h2><?php esc_html_e( 'Enlaces mágicos', 'wppw' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Genera enlaces que dan acceso directo a una página protegida sin pedir la contraseña. Útiles para compartir con un cliente o colaborador concreto.', 'wppw' ); ?></p>

			<?php if ( isset( $_GET['wppw_magic_created'] ) ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'Enlace generado correctamente.', 'wppw' ); ?></p></div>
			<?php elseif ( isset( $_GET['wppw_magic_error'] ) ) : ?>
				<div class="notice notice-error inline"><p><?php esc_html_e( 'Selecciona una página que tenga contraseña configurada.', 'wppw' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wppw_magic_create" />
				<?php wp_nonce_field( 'wppw_magic_create' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="wppw_magic_page"><?php esc_html_e( 'Página protegida', 'wppw' ); ?></label></th>
						<td>
							<select id="wppw_magic_page" name="page_id" <?php disabled( ! $is_premium ); ?>>
								<?php foreach ( $protected_pages as $p ) : ?>
									<option value="<?php echo esc_attr( $p->ID ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php if ( empty( $protected_pages ) ) : ?>
								<p class="description"><?php esc_html_e( 'No hay páginas protegidas con contraseña todavía.', 'wppw' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wppw_magic_label"><?php esc_html_e( 'Etiqueta (opcional)', 'wppw' ); ?></label></th>
						<td>
							<input type="text" id="wppw_magic_label" name="label" class="regular-text"
								placeholder="<?php esc_attr_e( 'Ej. Cliente Pérez', 'wppw' ); ?>"
								<?php disabled( ! $is_premium ); ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wppw_magic_expires"><?php esc_html_e( 'Caducidad', 'wppw' ); ?></label></th>
						<td>
							<select id="wppw_magic_expires" name="expires_in" <?php disabled( ! $is_premium ); ?>>
								<option value="0"><?php esc_html_e( 'Nunca', 'wppw' ); ?></option>
								<option value="86400"><?php esc_html_e( '1 día', 'wppw' ); ?></option>
								<option value="604800" selected><?php esc_html_e( '7 días', 'wppw' ); ?></option>
								<option value="2592000"><?php esc_html_e( '30 días', 'wppw' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wppw_magic_uses"><?php esc_html_e( 'Usos máximos', 'wppw' ); ?></label></th>
						<td>
							<select id="wppw_magic_uses" name="max_uses" <?php disabled( ! $is_premium ); ?>>
								<option value="0"><?php esc_html_e( 'Ilimitado', 'wppw' ); ?></option>
								<option value="1"><?php esc_html_e( '1 uso', 'wppw' ); ?></option>
								<option value="5"><?php esc_html_e( '5 usos', 'wppw' ); ?></option>
								<option value="10"><?php esc_html_e( '10 usos', 'wppw' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wppw_magic_fallback"><?php esc_html_e( 'URL si el enlace ya no funciona', 'wppw' ); ?></label></th>
						<td>
							<input type="url" id="wppw_magic_fallback" name="fallback_url" class="regular-text"
								placeholder="https://ejemplo.com/acceso-caducado"
								<?php disabled( ! $is_premium ); ?> />
							<p class="description"><?php esc_html_e( 'Opcional. Si el enlace está caducado o agotado, el visitante será redirigido a esta URL en lugar de ver un error.', 'wppw' ); ?></p>
						</td>
					</tr>
				</table>
				<?php if ( $is_premium ) : ?>
					<?php submit_button( __( 'Generar enlace', 'wppw' ) ); ?>
				<?php else : ?>
					<p><a href="<?php echo esc_url( wppw_get_upgrade_url() ); ?>" class="button wppw-upgrade-btn">&#11088; <?php esc_html_e( 'Actualizar a Premium', 'wppw' ); ?></a></p>
				<?php endif; ?>
			</form>

			<?php if ( $is_premium && ! empty( $links ) ) : ?>
				<h3><?php esc_html_e( 'Enlaces existentes', 'wppw' ); ?></h3>
				<table class="widefat striped wppw-magic-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Página', 'wppw' ); ?></th>
							<th><?php esc_html_e( 'Etiqueta', 'wppw' ); ?></th>
							<th><?php esc_html_e( 'Enlace', 'wppw' ); ?></th>
							<th><?php esc_html_e( 'Caduca', 'wppw' ); ?></th>
							<th><?php esc_html_e( 'Usos', 'wppw' ); ?></th>
							<th><?php esc_html_e( 'Estado', 'wppw' ); ?></th>
							<th><?php esc_html_e( 'Acciones', 'wppw' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $links as $token => $link ) :
							$page_title = get_the_title( $link['page_id'] );
							$page_title = $page_title ? $page_title : ( '#' . $link['page_id'] );
							$link_url   = $magic->get_link_url( $token, $link['page_id'] );
							$expired    = $magic->is_expired( $link );
							$exhausted  = $magic->is_exhausted( $link );
							$uses_label = $link['uses'] . ( $link['max_uses'] ? ' / ' . $link['max_uses'] : '' );
							?>
							<tr>
								<td><?php echo esc_html( $page_title ); ?></td>
								<td>
								<?php echo esc_html( $link['label'] ?: '—' ); ?>
								<?php if ( ! empty( $link['fallback_url'] ) ) : ?>
									<br><small class="description"><?php esc_html_e( 'Fallback:', 'wppw' ); ?> <a href="<?php echo esc_url( $link['fallback_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $link['fallback_url'] ); ?></a></small>
								<?php endif; ?>
							</td>
								<td>
									<input type="text" readonly class="wppw-magic-url-field" value="<?php echo esc_url( $link_url ); ?>" onclick="this.select();" />
									<button type="button" class="button wppw-copy-link" data-url="<?php echo esc_url( $link_url ); ?>"><?php esc_html_e( 'Copiar', 'wppw' ); ?></button>
								</td>
								<td><?php echo $link['expires'] ? esc_html( date_i18n( 'd/m/Y H:i', $link['expires'] ) ) : esc_html__( 'Nunca', 'wppw' ); ?></td>
								<td><?php echo esc_html( $uses_label ); ?></td>
								<td>
									<?php if ( $expired ) : ?>
										<span class="wppw-status wppw-status--expired"><?php esc_html_e( 'Caducado', 'wppw' ); ?></span>
									<?php elseif ( $exhausted ) : ?>
										<span class="wppw-status wppw-status--exhausted"><?php esc_html_e( 'Agotado', 'wppw' ); ?></span>
									<?php else : ?>
										<span class="wppw-status wppw-status--active"><?php esc_html_e( 'Activo', 'wppw' ); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
										<input type="hidden" name="action" value="wppw_magic_revoke" />
										<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>" />
										<?php wp_nonce_field( 'wppw_magic_revoke' ); ?>
										<button type="submit" class="button-link-delete"
											onclick="return confirm('<?php echo esc_js( __( '¿Revocar este enlace? Dejará de funcionar inmediatamente.', 'wppw' ) ); ?>');">
											<?php esc_html_e( 'Revocar', 'wppw' ); ?>
										</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
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
