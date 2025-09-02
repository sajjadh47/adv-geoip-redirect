<?php
/**
 * This file contains the definition of the Adv_Geoip_Redirect_Admin class, which
 * is used to load the plugin's admin-specific functionality.
 *
 * @package       Adv_Geoip_Redirect
 * @subpackage    Adv_Geoip_Redirect/admin
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version and other methods.
 *
 * @since    2.0.0
 */
class Adv_Geoip_Redirect_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 * @var       string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 * @var       string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * An array to store admin notices.
	 *
	 * This private property holds an array of notices to be displayed in the WordPress
	 * admin area. Each notice is an associative array containing the 'class' and 'message'
	 * keys.
	 *
	 * @since     2.0.0
	 * @access    private
	 * @var       array $notices An array of admin notices.
	 */
	private $notices = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     string $plugin_name The name of this plugin.
	 * @param     string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function enqueue_styles() {
		$current_screen = get_current_screen();

		// check if current page is plugin settings page.
		if ( 'toplevel_page_adv-geoip-redirect' === $current_screen->id ) {
			wp_enqueue_style( $this->plugin_name, ADV_GEOIP_REDIRECT_PLUGIN_URL . 'admin/css/admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function enqueue_scripts() {
		$current_screen = get_current_screen();

		// check if current page is plugin settings page.
		if ( 'toplevel_page_adv-geoip-redirect' === $current_screen->id ) {
			wp_enqueue_script( $this->plugin_name, ADV_GEOIP_REDIRECT_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery', 'wp-util', 'jquery-ui-sortable' ), $this->version, false );

			wp_localize_script(
				$this->plugin_name,
				'AdvGeoipRedirect',
				array(
					'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
					'btnSavingText'            => __( 'Saving... Please Wait', 'adv-geoip-redirect' ),
					'confirnDeleteMsg'         => __( 'Do You Really Want To Delete This Redirect Rule?', 'adv-geoip-redirect' ),
					'confirnResetMsg'          => __( 'Do You Really Want To Reset All Redirect Rules? Please Make a backup using the Export Tool below to restore again!', 'adv-geoip-redirect' ),
					'confirnDebugLogClearMsg'  => __( 'Do You Really Want To Clear The Debug Log?', 'adv-geoip-redirect' ),
					'redirectRules'            => Adv_Geoip_Redirect::get_option( 'redirect_rules', Adv_Geoip_Redirect::$option_name, array() ),
				)
			);
		}
	}

	/**
	 * Adds a settings link to the plugin's action links on the plugin list table.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     array $links The existing array of plugin action links.
	 * @return    array $links The updated array of plugin action links, including the settings link.
	 */
	public function add_plugin_action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=adv-geoip-redirect' ) ), __( 'Settings', 'adv-geoip-redirect' ) );

		return $links;
	}

	/**
	 * Adds the plugin settings page to the WordPress dashboard menu.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'GeoIP Redirect', 'adv-geoip-redirect' ),
			__( 'GeoIP Redirect', 'adv-geoip-redirect' ),
			'manage_options',
			'adv-geoip-redirect',
			array( $this, 'menu_page' ),
			'dashicons-admin-site-alt3'
		);
	}

	/**
	 * Renders the plugin menu page content.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function menu_page() {
		require ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'admin/views/plugin-admin-display.php';
	}

	/**
	 * Initializes admin-specific functionality.
	 *
	 * This function is hooked to the 'admin_init' action and is used to perform
	 * various administrative tasks, such as registering settings, enqueuing scripts,
	 * or adding admin notices.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function admin_init() {
		if ( isset( $_POST['geoipr_reset_btn'] ) && '1' === $_POST['geoipr_reset_btn'] ) {
			if ( ! isset( $_POST['_wpnonce_geoipr_settings_form'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_geoipr_settings_form'] ) ), 'geoipr_settings_form' ) ) {
				$this->notices = array(
					'class'   => 'notice notice-warning',
					'message' => __( 'Sorry, your nonce did not verify.', 'adv-geoip-redirect' ),
				);
			} elseif ( current_user_can( 'manage_options' ) ) {
				Adv_Geoip_Redirect::reset_plugin_settings();

				$this->notices[] = array(
					'class'   => 'notice notice-success',
					'message' => __( 'Filters Reset Successfully', 'adv-geoip-redirect' ),
				);
			}
		}

		if ( isset( $_POST['geoipr_debug_log_clear_action'] ) && current_user_can( 'manage_options' ) ) {
			if ( ! isset( $_POST['_wpnonce_geoipr_settings_debug_log_clear_form'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_geoipr_settings_debug_log_clear_form'] ) ), 'geoipr_settings_debug_log_clear_form' ) ) {
				$this->notices[] = array(
					'class'   => 'notice notice-warning',
					'message' => __( 'Sorry, your nonce did not verify.', 'adv-geoip-redirect' ),
				);
			} else {
				Adv_Geoip_Redirect::clear_debug_log();

				$this->notices[] = array(
					'class'   => 'notice notice-success',
					'message' => __( 'Debug log cleared Successfully', 'adv-geoip-redirect' ),
				);
			}
		}

		if ( isset( $_POST['geoipr_export_action'] ) && current_user_can( 'manage_options' ) ) {
			if ( ! isset( $_POST['_wpnonce_geoipr_settings_export_form'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_geoipr_settings_export_form'] ) ), 'geoipr_settings_export_form' ) ) {
				$this->notices[] = array(
					'class'   => 'notice notice-warning',
					'message' => __( 'Sorry, your nonce did not verify.', 'adv-geoip-redirect' ),
				);
			} else {
				Adv_Geoip_Redirect::export_settings();
			}
		}

		if ( isset( $_POST['geoipr_import_action'] ) && current_user_can( 'manage_options' ) ) {
			if ( ! isset( $_POST['_wpnonce_geoipr_settings_import_form'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_geoipr_settings_import_form'] ) ), 'geoipr_settings_import_form' ) ) {
				$this->notices[] = array(
					'class'   => 'notice notice-warning',
					'message' => __( 'Sorry, your nonce did not verify.', 'adv-geoip-redirect' ),
				);
			} elseif ( isset( $_FILES['import_file']['tmp_name'] ) ) {
				$import_file = sanitize_text_field( $_FILES['import_file']['tmp_name'] );

				if ( empty( $import_file ) ) {
					$this->notices[] = array(
						'class'   => 'notice notice-warning',
						'message' => __( 'Please upload a file to import.', 'adv-geoip-redirect' ),
					);

					return;
				}

				if ( ! isset( $_FILES['import_file']['name'] ) ) {
					$this->notices[] = array(
						'class'   => 'notice notice-warning',
						'message' => __( 'Please upload a file to import.', 'adv-geoip-redirect' ),
					);

					return;
				}

				$file_name = sanitize_text_field( $_FILES['import_file']['name'] );

				// get file extension.
				$ext = explode( '.', $file_name );

				$extension = end( $ext );

				if ( 'json' !== $extension ) {
					$this->notices[] = array(
						'class'   => 'notice notice-warning',
						'message' => __( 'Please upload a valid .json file.', 'adv-geoip-redirect' ),
					);

					return;
				}

				if ( Adv_Geoip_Redirect::import_settings( $import_file ) ) {
					$this->notices[] = array(
						'class'   => 'notice notice-success',
						'message' => __( 'Settings Imported Successfully', 'adv-geoip-redirect' ),
					);
				} else {
					$this->notices[] = array(
						'class'   => 'notice notice-warning',
						'message' => __( 'Corrupted Import File! Please Try Again!', 'adv-geoip-redirect' ),
					);
				}
			}
		}
	}

	/**
	 * Displays admin notices based on stored messages.
	 *
	 * This function iterates through the stored notices array and displays them
	 * as formatted HTML messages in the WordPress admin area.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function admin_notices() {
		// Check if there are any notices to display.
		if ( ! empty( $this->notices ) ) {
			// Iterate through each notice.
			foreach ( $this->notices as $notice ) {
				// Output the formatted notice.
				printf(
					'<div class="%1$s"><p>%2$s</p></div>',
					esc_attr( $notice['class'] ), // Sanitize the class attribute.
					esc_html( $notice['message'] ) // Sanitize the message content.
				);
			}
		}
	}

	/**
	 * Adds footer text referencing the MaxMind GeoLite2 database on the plugin's settings page.
	 *
	 * This function appends a message to the admin footer text on the GeoIP Redirect plugin's
	 * settings page, acknowledging the use of the MaxMind GeoLite2 database and providing
	 * a link to MaxMind's website.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     string $text The existing admin footer text.
	 * @return    string       The modified admin footer text.
	 */
	public function admin_footer_text( $text ) {
		$current_scrn = get_current_screen();

		// Check if the current screen is the plugin's settings page.
		if ( 'toplevel_page_adv-geoip-redirect' !== $current_scrn->id ) {
			// Return the original text if it's not the correct page.
			return $text;
		}

		return __( "The GeoIP Redirect Plugin is using GeoLite2 db provided by MaxMind. Please visit https://www.maxmind.com for more information.\n", 'adv-geoip-redirect' );
	}

	/**
	 * Saves plugin settings fields from the submitted form data.
	 *
	 * This function processes and saves the plugin settings fields submitted through
	 * the admin settings page form. It validates the nonce, sanitizes the input, and
	 * updates the settings in the WordPress options table.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function capture_form_submit() {
		// Verify the nonce to ensure the request is legitimate.
		if ( ! isset( $_POST['_wpnonce_geoipr_settings_form'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_geoipr_settings_form'] ) ), 'geoipr_settings_form' ) ) {
			// Nonce verification failed. Send an error response.
			$response = array(
				'status'  => 'error',
				'message' => __( 'Sorry, your nonce did not verify.', 'adv-geoip-redirect' ),
			);
		} else {
			// Nonce verification successful. Process the form fields.
			// Ensure 'redirect_rules' is always set.
			if ( ! isset( $_POST['redirect_rules'] ) ) {
				$_POST['redirect_rules'] = array();
			}

			// Update the plugin settings option, data validation will be checked before updating option.
			if ( Adv_Geoip_Redirect::update_plugin_settings( $_POST ) ) {
				// Send a success response.
				$response = array(
					'status'  => 'success',
					'message' => __( 'Settings Updated Successfully!', 'adv-geoip-redirect' ),
				);
			} else {
				$response = array(
					'status'  => 'error',
					'message' => __( 'Invalid Request! Please Try Again!', 'adv-geoip-redirect' ),
				);
			}
		}

		// Send the JSON response and exit.
		wp_send_json( $response );
	}
}
