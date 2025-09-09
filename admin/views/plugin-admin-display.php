<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since         2.0.0
 * @package       Adv_Geoip_Redirect
 * @subpackage    Adv_Geoip_Redirect/admin/views
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$visitor_ip = Adv_Geoip_Redirect::get_visitor_ip();

// if result is found then redirect.
try {
	// This creates the Reader object.
	$reader          = new \GeoIp2\Database\Reader( ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'public/geoip-db/GeoLite2-Country.mmdb' );
	$visitor_geo     = $reader->country( $visitor_ip );
	$visitor_country = $visitor_geo->country->name;
} catch ( Exception $e ) {
	$visitor_country = 'N/A';
}

Adv_Geoip_Redirect_Admin::show_upgrade_notice_bar();

?>
<div class="wrap">
	<h2>
		<?php esc_html_e( 'GeoIP Redirect Settings', 'adv-geoip-redirect' ); ?>
		<button type="submit" class="button button-secondary" id="geoipr_add_new_btn">
			<?php esc_html_e( 'Add New Redirect Rule', 'adv-geoip-redirect' ); ?>
		</button>
	</h2>
	<div class="notice geoipr_message"><p></p></div><br>
	<form action="" method="post" id="geoipr_settings_form">
		<div id="geoipr_settings_fields">
		<?php
		foreach ( Adv_Geoip_Redirect::get_plugin_settings_chk_fields() as $index => $label ) :
			if ( 'false' !== $label ) :
				$chk_id = Adv_Geoip_Redirect::$option_fields[ $index ];
				?>
					<div class="form-group row">
						<div class="col-sm-3" style="line-height: 35px;">
							<?php echo wp_kses_post( $label ); ?>
						</div>
						<div class="col-sm-9">
							<div class="form-check">
								<div class="geoipr_chk-slider">
									<input type="checkbox" class="geoipr_chk-slider-checkbox" id="<?php echo esc_attr( $chk_id ); ?>" <?php checked( Adv_Geoip_Redirect::get_option( $chk_id, Adv_Geoip_Redirect::$option_name, 'false' ), 'true' ); ?>>
									<label class="geoipr_chk-slider-label" for="<?php echo esc_attr( $chk_id ); ?>">
										<span class="geoipr_chk-slider-inner"></span><span class="geoipr_chk-slider-circle"></span>
									</label>
								</div>
							</div>
						</div>
					</div>
				<?php
			endif;
		endforeach;
		?>
			<div class="form-group row">
				<div class="col-sm-3" style="line-height: 35px;">
					<?php esc_html_e( 'Redirection Type', 'adv-geoip-redirect' ); ?>
				</div>
				<div class="col-sm-9">
					<div class="form-check">
						<select class="form-control" id="redirection_type">
							<option value="301" <?php selected( Adv_Geoip_Redirect::get_option( 'redirection_type', Adv_Geoip_Redirect::$option_name, '302' ), '301' ); ?>><?php esc_html_e( '301 Moved Permanently', 'adv-geoip-redirect' ); ?></option>
							<option value="302" <?php selected( Adv_Geoip_Redirect::get_option( 'redirection_type', Adv_Geoip_Redirect::$option_name, '302' ), '302' ); ?>><?php esc_html_e( '302 Moved Temporarily', 'adv-geoip-redirect' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-sm-3" style="line-height: 35px;">
					<?php esc_html_e( 'Your IP Information', 'adv-geoip-redirect' ); ?>
				</div>
				<div class="col-sm-9">
					<div class="form-check">
						<code class="geoipr_ip_info"><?php esc_html_e( 'Address: ', 'adv-geoip-redirect' ); ?><?php echo esc_html( $visitor_ip ); ?></code>
						<code class="geoipr_ip_info"><?php esc_html_e( 'Country: ', 'adv-geoip-redirect' ); ?><?php echo esc_html( $visitor_country ); ?></code>
					</div>
				</div>
			</div>

			<?php
				/**
				 * Renders the admin settings list for the Advanced GeoIP Redirect plugin.
				 *
				 * This function is hooked to the 'adv_geoip_redirect_admin_settings_list' action. It is
				 * responsible for displaying the main settings page in the WordPress admin area,
				 * including a title and a description, and then calling the necessary functions
				 * to render the actual settings fields.
				 *
				 * @since    2.0.5
				 */
				do_action( 'adv_geoip_redirect_admin_settings_list' );
			?>
		</div>

		<!-- Here Goes Rules Set Markup -->
		<h3 class="redirect_rules_heading" <?php echo ! empty( Adv_Geoip_Redirect::get_option( 'redirect_rules', Adv_Geoip_Redirect::$option_name, array() ) ) ? 'style="display: block;"' : 'style="display: none;"'; ?>>
			<?php esc_html_e( 'Redirect Rules', 'adv-geoip-redirect' ); ?>
		</h3>

		<div id="geoipr_rules_group"></div>
		<div class="geoipr_action_container">
			<button type="button" class="button button-primary" id="geoipr_submit_btn"><?php esc_html_e( 'Save Changes', 'adv-geoip-redirect' ); ?></button>
			<?php wp_nonce_field( 'geoipr_settings_form', '_wpnonce_geoipr_settings_form' ); ?>
			<button type="submit" class="button button-secondary" id="geoipr_reset_btn" name="geoipr_reset_btn" value="1"><?php esc_html_e( 'Reset Settings', 'adv-geoip-redirect' ); ?></button>
		</div>
	</form>

	<br>
	<div class="metabox-holder">
		<?php
			/**
			 * Executes an action hook before the metaboxes are rendered on the Advanced GeoIP Redirect settings page.
			 *
			 * This function provides a hook for other plugins or themes to add content, scripts, or
			 * styles before the main settings metaboxes are displayed. This is useful for
			 * adding custom notices, introductory text, or other elements that should
			 * appear at the top of the settings page.
			 *
			 * @since    2.0.5
			 */
			do_action( 'adv_geoip_redirect_admin_before_metaboxes' );
		?>
		<div class="postbox">
			<h3><span><?php esc_html_e( 'Debug Log Viewer', 'adv-geoip-redirect' ); ?></span></h3>
			<div class="inside">
				<textarea id="geoipr_settings_debug_log" name="geoipr_settings_debug_log" class="large-text code" readonly rows="5"><?php echo esc_textarea( Adv_Geoip_Redirect::read_debug_log() ); ?></textarea>
				<form action="" method="post" id="geoipr_settings_debug_log_clear_form" enctype="multipart/form-data">
					<?php wp_nonce_field( 'geoipr_settings_debug_log_clear_form', '_wpnonce_geoipr_settings_debug_log_clear_form' ); ?>
					<?php submit_button( __( 'Clear Debug Log', 'adv-geoip-redirect' ), 'secondary', 'geoipr_debug_log_clear_action', false ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<div class="postbox">
			<h3><span><?php esc_html_e( 'Export Settings', 'adv-geoip-redirect' ); ?></span></h3>
			<div class="inside">
				<p><?php esc_html_e( 'Export the currently saved plugin settings. This allows you to easily import the configuration into another site.', 'adv-geoip-redirect' ); ?></p>
				<form action="" method="post" id="geoipr_settings_export_form">
					<?php wp_nonce_field( 'geoipr_settings_export_form', '_wpnonce_geoipr_settings_export_form' ); ?>
					<?php submit_button( __( 'Export', 'adv-geoip-redirect' ), 'secondary', 'geoipr_export_action', false ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<div class="postbox">
			<h3><span><?php esc_html_e( 'Import Settings', 'adv-geoip-redirect' ); ?></span></h3>
			<div class="inside">
				<p><?php esc_html_e( 'Import the plugin settings. Use above form button (on another site maybe) to generate the import file to use in here', 'adv-geoip-redirect' ); ?></p>
				<form action="" method="post" id="geoipr_settings_import_form" enctype="multipart/form-data">
					<p>
						<input type="file" name="import_file"/>
					</p>
					<?php wp_nonce_field( 'geoipr_settings_import_form', '_wpnonce_geoipr_settings_import_form' ); ?>
					<?php submit_button( __( 'Import', 'adv-geoip-redirect' ), 'secondary', 'geoipr_import_action', false ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
			/**
			 * Executes an action hook after the metaboxes are rendered on the Advanced GeoIP Redirect settings page.
			 *
			 * This function provides a hook for other plugins or themes to add custom content,
			 * scripts, or styles after the main settings metaboxes have been displayed.
			 *
			 * @since    2.0.5
			 */
			do_action( 'adv_geoip_redirect_admin_after_metaboxes' );
		?>
	</div><!-- .metabox-holder -->
	<script type="text/template" id="tmpl-redirect-rules-set">
		<div class="input-group mb-3 geoipr_rules_group_container">
			<div class="input-group-prepend dropdown">
				<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?php esc_html_e( 'Settings', 'adv-geoip-redirect' ); ?></button>
				<div class="dropdown-menu">
					<label for="pass_url_parameter_{{data.pass_url_parameter_id}}">
						<input class="dropdown-item geoipr_pass_url_parameter" type="checkbox" id="pass_url_parameter_{{data.pass_url_parameter_id}}" <# if ( data.PassParameter == 'true' ) { #> checked=checked <# } #>>
						<?php esc_html_e( 'Pass URL Parameters Forward', 'adv-geoip-redirect' ); ?>
					</label>
					<div role="separator" class="dropdown-divider"></div>
					<label for="ignore_url_parameter_{{data.ignore_url_parameter_id}}">
						<input class="dropdown-item geoipr_ignore_url_parameter" type="checkbox" id="ignore_url_parameter_{{data.ignore_url_parameter_id}}" <# if ( data.IgnoreParameter == 'true' ) { #> checked=checked <# } #>>
						<?php esc_html_e( 'Ignore URL Parameters When Check Against', 'adv-geoip-redirect' ); ?>
					</label>
				</div>
			</div>
			<div class="input-group-prepend">
				<span class="input-group-text"><?php esc_html_e( 'Redirect if user is', 'adv-geoip-redirect' ); ?></span>
			</div>
			<select class="form-control geoipr_user_from_chk_condition">
				<option value="from" <# if ( data.FromChkCondition == 'from' ) { #> selected=selected <# } #>><?php esc_html_e( 'From', 'adv-geoip-redirect' ); ?></option>
				<option value="not_from" <# if ( data.FromChkCondition == 'not_from' ) { #> selected=selected <# } #>><?php esc_html_e( 'Not From', 'adv-geoip-redirect' ); ?></option>
			</select>
			<select class="form-control geoipr_countries_list" multiple="multiple">
				<?php foreach ( Adv_Geoip_Redirect::get_countries() as $code => $country ) : ?>
					<option <# if ( ( data.countryField.indexOf( "<?php echo esc_attr( $code ); ?>" ) != -1 ) ) { #> selected=selected <# } #> value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $country ); ?></option>
				<?php endforeach; ?>
			</select>
			<div class="input-group-append">
				<span class="input-group-text"><?php esc_html_e( 'To', 'adv-geoip-redirect' ); ?></span>
			</div>
			<input type="text" class="form-control geoipr_target_url" value="{{data.TargetURLField}}" placeholder="<?php esc_html_e( 'Enter Redirect URL...', 'adv-geoip-redirect' ); ?>">
			<div class="input-group-append">
				<span class="input-group-text"><?php esc_html_e( 'When Visits', 'adv-geoip-redirect' ); ?></span>
			</div>
			<input type="text" class="form-control geoipr_visited_url" value="{{data.VisitedURLField}}" placeholder="<?php esc_html_e( 'Enter Visited URL...', 'adv-geoip-redirect' ); ?>">
			<span class="dashicons dashicons-trash geoipr_delete"></span>
		</div>
	</script>
</div>

<style type="text/css">
	.geoipr_chk-slider-inner:before {
		content: '<?php esc_html_e( 'ENABLED', 'adv-geoip-redirect' ); ?>';
	}
	.geoipr_chk-slider-inner:after {
		content: '<?php esc_html_e( 'DISABLED', 'adv-geoip-redirect' ); ?>';
	}
</style>
