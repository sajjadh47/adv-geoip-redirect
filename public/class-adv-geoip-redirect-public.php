<?php
/**
 * This file contains the definition of the Adv_Geoip_Redirect_Public class, which
 * is used to load the plugin's public-facing functionality.
 *
 * @package       Adv_Geoip_Redirect
 * @subpackage    Adv_Geoip_Redirect/public
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version and other methods.
 *
 * @since    2.0.0
 */
class Adv_Geoip_Redirect_Public {
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
	 * Stores debug log messages.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @var       string $debug_logs The debug log messages.
	 */
	public $debug_logs;

	/**
	 * Stores the current visitor's IP address.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @var       string|null $visitor_ip The current visitor's IP address, or null if not available.
	 */
	public $visitor_ip;

	/**
	 * Stores the current visitor's country ISO code.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @var       string|null $visitor_country The current visitor's country ISO code, or null if not available.
	 */
	public $visitor_country;

	/**
	 * Stores the redirection type.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @var       string|null $redirect_type The redirection type, or null if not available.
	 */
	public $redirect_type;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     string $plugin_name The name of the plugin.
	 * @param     string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Ensures this plugin is loaded first by adjusting the 'active_plugins' option.
	 *
	 * This function modifies the 'active_plugins' option in WordPress to ensure that this
	 * plugin is loaded before all other plugins. This is useful when the plugin's functionality
	 * relies on being loaded first to work correctly.
	 *
	 * @since    2.0.0
	 * @access public
	 */
	public function always_load_this_plugin_first() {
		// Get the plugin's basename.
		$geoipr_plugin = ADV_GEOIP_REDIRECT_PLUGIN_BASENAME;

		// Get the array of active plugins.
		$active_plugins = get_option( 'active_plugins' );

		// Check if the plugin is in the active plugins list.
		$geoipr_plugin_name = array_search( $geoipr_plugin, $active_plugins, true );

		// Use strict comparison to avoid issues with index 0.
		if ( false !== $geoipr_plugin_name ) {
			// Remove the plugin from its current position.
			array_splice( $active_plugins, $geoipr_plugin_name, 1 );

			// Add the plugin to the beginning of the array.
			array_unshift( $active_plugins, $geoipr_plugin );

			// Update the 'active_plugins' option.
			update_option( 'active_plugins', $active_plugins );
		}
	}

	/**
	 * Initializes the redirection logic by hooking into the 'template_redirect' action.
	 *
	 * This function hooks into the 'template_redirect' action, which is triggered before
	 * WordPress determines which template to load. It initiates the redirection process
	 * based on the defined rules and visitor's geolocation.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function template_redirect() {
		// if loading admin side don't run redirection logic.
		if ( is_admin() || defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) || defined( 'REST_REQUEST' ) ) {
			return;
		}

		// now check if redirect is enabled.
		if ( 'true' !== Adv_Geoip_Redirect::get_option( 'redirect_switch', Adv_Geoip_Redirect::$option_name, 'false' ) ) {
			return;
		}

		$redirect_rules = Adv_Geoip_Redirect::get_option( 'redirect_rules', Adv_Geoip_Redirect::$option_name, array() );

		// and now check if any rules is set.
		if ( empty( $redirect_rules ) ) {
			return;
		}

		// if development mode enabled then only redirect logged in admin users.
		if ( 'true' === Adv_Geoip_Redirect::get_option( 'dev_mode', Adv_Geoip_Redirect::$option_name, 'false' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
		}

		$this->visitor_ip = Adv_Geoip_Redirect::get_visitor_ip();

		// visitor ip is not valid.
		if ( empty( $this->visitor_ip ) ) {
			$this->debug_logs = implode(
				"\t",
				array(
					gmdate( 'Y-m-d H:i:s' ),
					'Visitor IP ' . $this->visitor_ip,
					__( 'Redirection terminated. Invalid Visitor IP Found!', 'adv-geoip-redirect' ),
				)
			);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( 'true' === Adv_Geoip_Redirect::get_option( 'skip_if_skipredirect_provided', Adv_Geoip_Redirect::$option_name, 'false' ) && isset( $_GET['skipredirect'] ) ) {
			// don't continue if ?skipredirect is in the url.
			$this->debug_logs = implode(
				"\t",
				array(
					gmdate( 'Y-m-d H:i:s' ),
					'Visitor IP ' . $this->visitor_ip,
					__( 'Redirection terminated. ?skipredirect URL Parameter Found!', 'adv-geoip-redirect' ),
				)
			);
		} elseif ( 'true' === Adv_Geoip_Redirect::get_option( 'skip_if_bot', Adv_Geoip_Redirect::$option_name, 'false' ) && Adv_Geoip_Redirect::is_bot() ) {
			// don't continue if request is from a bot/web crawler & option to skip is enabled.
			$this->debug_logs = implode(
				"\t",
				array(
					gmdate( 'Y-m-d H:i:s' ),
					'Visitor IP ' . $this->visitor_ip,
					__( 'Redirection terminated. Bot/Web Crawler Detected!', 'adv-geoip-redirect' ),
				)
			);
		} else {
			// set redirect type.
			$this->redirect_type = Adv_Geoip_Redirect::get_option( 'redirection_type', Adv_Geoip_Redirect::$option_name, '302' );

			// if result is found then redirect.
			try {
				// This creates the Reader object.
				$reader                = new \GeoIp2\Database\Reader( ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'public/geoip-db/GeoLite2-Country.mmdb' );
				$visitor_geo           = $reader->country( $this->visitor_ip );
				$this->visitor_country = $visitor_geo->country->isoCode;

				// go over through all rules.
				foreach ( $redirect_rules as $rule_set ) {
					// convert object to array.
					$rule_set    = (array) $rule_set;
					$target_url  = esc_url( trailingslashit( $rule_set['TargetURLField'] ) );
					$visited_url = esc_url( trailingslashit( $rule_set['VisitedURLField'] ) );

					// get current visited url.
					$current_url = Adv_Geoip_Redirect::get_current_url( $rule_set['IgnoreParameter'] );

					// don't continue if redirect to & visited url is same.
					if ( $target_url === $visited_url || $target_url === $current_url ) {
						$this->debug_logs = implode(
							"\t",
							array(
								gmdate( 'Y-m-d H:i:s' ),
								'Visitor IP ' . $this->visitor_ip,
								__( 'Redirection terminated. Same page redirection! Aborted Redirection to avoid infinite redirect loop!', 'adv-geoip-redirect' ),
							)
						);

						continue;
					}

					// if url is relative add home_url().
					if ( Adv_Geoip_Redirect::is_url_relative( $target_url ) ) {
						$rule_set['TargetURLField'] = trailingslashit( home_url( $target_url ) );
					}

					if ( Adv_Geoip_Redirect::is_url_relative( $rule_set['VisitedURLField'] ) ) {
						$rule_set['VisitedURLField'] = trailingslashit( home_url( $visited_url ) );
					}

					// default check if visitor from country.
					$from_chk_condition = in_array( $this->visitor_country, $rule_set['countryField'], true );

					// check for visitor country condition for the following rule.
					if ( 'not_from' === $rule_set['FromChkCondition'] ) {
						$from_chk_condition = ! in_array( $this->visitor_country, $rule_set['countryField'], true );
					}

					// check if user is from following country.
					if ( $from_chk_condition ) {
						// check if redirect first visit only enabled.
						if ( 'true' === Adv_Geoip_Redirect::get_option( 'redirect_for_first_time_visit_only', Adv_Geoip_Redirect::$option_name, 'false' ) ) {
							if ( isset( $_COOKIE[ sha1( $current_url ) ] ) ) {
								$this->debug_logs = implode(
									"\t",
									array(
										gmdate( 'Y-m-d H:i:s' ),
										'Visitor IP ' . $this->visitor_ip,
										__( 'Redirection terminated. User Already Visited The Page!', 'adv-geoip-redirect' ),
									)
								);

								continue;
							}
						}

						$visited_url_field = str_replace( array( '?' ), array( '\?' ), $visited_url );

						// check if VisitedURLField has any url parameter.
						$url_params = explode( '?', $visited_url );

						// if it has Params remove them to go forward.
						if ( count( $url_params ) > 1 ) {
							$param                   = $url_params[1];
							$visited_url_field       = $url_params[0] . '[\?|&].*' . $param;
							$_SERVER['QUERY_STRING'] = isset( $_SERVER['QUERY_STRING'] ) ? preg_replace( "#$param&?#i", '', sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) ) : '';
						}

						// don't continue if it's a WooCommerce ajax or Job Manager ajax request.
						if ( preg_match( '/jm-ajax/', $current_url ) || preg_match( '/wp-content/', $current_url ) || preg_match( '/wc-ajax/', $current_url ) ) {
							continue;
						}

						// now check if user is visiting the set url.
						if ( preg_match( "#^$visited_url_field$#i", $current_url, $matches ) ) {
							// remove the first value which is basically not needed.
							array_shift( $matches );

							$redirect_to = $target_url;

							if ( strpos( $redirect_to, '(.*)' ) !== false && count( $matches ) ) {
								$regex_count = 0;
								$redirect_to = preg_replace_callback(
									'#\(\.\*\)#',
									// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
									function ( $_match ) use ( &$regex_count, $matches ) {
										return $matches[ $regex_count++ ];
									},
									$redirect_to
								);
							}

							// if pass query enabled then add it to url.
							if ( 'true' === $rule_set['PassParameter'] && ! empty( $_SERVER['QUERY_STRING'] ) ) {
								$query_string_divider = ( strpos( $redirect_to, '?' ) === false ) ? '?' : '&';
								$redirect_to          = $redirect_to . $query_string_divider . sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );
							}

							// don't continue if redirect to & visited url is same.
							if ( $redirect_to === $current_url ) {
								$this->debug_logs = implode(
									"\t",
									array(
										gmdate( 'Y-m-d H:i:s' ),
										'Visitor IP ' . $this->visitor_ip,
										__( 'Redirection terminated. Same page redirection! Aborted Redirection to avoid infinite redirect loop!', 'adv-geoip-redirect' ),
									)
								);

								continue;
							}

							setcookie( sha1( $current_url ), time(), strtotime( '+24 hours' ) );

							// if everything is fine then redirect user to destined url.
							// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
							if ( wp_redirect( $redirect_to, $this->redirect_type ) ) {
								$this->debug_logs = implode(
									"\t",
									array(
										gmdate( 'Y-m-d H:i:s' ),
										'Visitor IP ' . $this->visitor_ip,
										__( 'Redirection succeeded! To ', 'adv-geoip-redirect' ) . $redirect_to . ' From ' . $current_url,
									)
								);

								Adv_Geoip_Redirect::write_down_debug_log( $this->debug_logs );
								exit();
							}
						} //url matching end.
					} //country matching end.
				} //endforeach.
			} catch ( Exception $e ) {
				$this->debug_logs = implode(
					"\t",
					array(
						gmdate( 'Y-m-d H:i:s' ),
						'Visitor IP ' . $this->visitor_ip,
						__( 'Redirection terminated. Unable to detect visitor country!', 'adv-geoip-redirect' ),
					)
				);
			}
		}

		Adv_Geoip_Redirect::write_down_debug_log( $this->debug_logs );
	}
}
