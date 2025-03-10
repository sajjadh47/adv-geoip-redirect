<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, other methods and
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Adv_Geoip_Redirect
 * @subpackage Adv_Geoip_Redirect/public
 * @author     Sajjad Hossain Sagor <sagorh672@gmail.com>
 */
class Adv_Geoip_Redirect_Public
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name     The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    		The current version of this plugin.
	 */
	private $version;

	/**
	 * Stores debug log messages.
	 *
	 * @since    2.0.0
	 * @access 	 public
	 * @var 	 string 	$debug_logs 	The debug log messages.
	 */
	public $debug_logs;

	/**
	 * Stores the current visitor's IP address.
	 *
	 * @since    2.0.0
	 * @access 	 public
	 * @var 	 string|null $VisitorIP 	The current visitor's IP address, or null if not available.
	 */
	public $VisitorIP;

	/**
	 * Stores the current visitor's country ISO code.
	 *
	 * @since    2.0.0
	 * @access 	 public
	 * @var 	 string|null $VisitorCountry The current visitor's country ISO code, or null if not available.
	 */
	public $VisitorCountry;

	/**
	 * Stores the redirection type.
	 *
	 * @since    2.0.0
	 * @access 	 public
	 * @var 	 string|null $RedirectType The redirection type, or null if not available.
	 */
	public $RedirectType;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @access   public
	 * @param    string    $plugin_name   	The name of the plugin.
	 * @param    string    $version   		The version of this plugin.
	 */
	public function __construct( $plugin_name, $version )
	{
		$this->plugin_name 	= $plugin_name;
		
		$this->version 		= $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style( $this->plugin_name, ADV_GEOIP_REDIRECT_PLUGIN_URL . 'public/css/public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( $this->plugin_name, ADV_GEOIP_REDIRECT_PLUGIN_URL . 'public/js/public.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'ADV_GEOIP_REDIRECT', array(
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
		) );
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
	public function always_load_this_plugin_first()
	{
		// Get the plugin's basename.
		$geoipr_plugin 				= ADV_GEOIP_REDIRECT_PLUGIN_BASENAME;

		// Get the array of active plugins.
		$active_plugins 			= get_option( 'active_plugins' );

		// Check if the plugin is in the active plugins list.
		$geoipr_plugin_name 		= array_search( $geoipr_plugin, $active_plugins );

		if ( $geoipr_plugin_name !== false ) // Use strict comparison to avoid issues with index 0
		{
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
	 * @since   2.0.0
	 * @access 	public
	 * @return 	void
	 */
	public function template_redirect()
	{
		// if loading admin side don't run redirection logic
		if ( is_admin() || defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) ) return;

		// now check if redirect is enabled
		if ( Adv_Geoip_Redirect_Util::get_option( 'redirect_switch' ) !== 'true' ) return;

		$redirect_rules 								= Adv_Geoip_Redirect_Util::get_option( 'redirect_rules' );

		// and last now check if any rules is set
		if ( empty( $redirect_rules ) ) return;

		// if development mode enabled then only redirect logged in admin users
		if ( Adv_Geoip_Redirect_Util::get_option( 'dev_mode' ) == 'true' )
		{
			if ( ! current_user_can( 'administrator' ) ) return;
		}

		$this->VisitorIP 								= Adv_Geoip_Redirect_Util::get_visitor_ip();

		// visitor ip is not valid
		if ( $this->VisitorIP == '' )
		{	
			$this->debug_logs 							= implode( "\t", array(
				gmdate( 'Y-m-d H:i:s' ),
				'Visitor IP ' . $this->VisitorIP,
				__( 'Redirection terminated. Invalid Visitor IP Found!' ),
			) );
		}
		// don't continue if ?skipredirect is in the url
		else if ( Adv_Geoip_Redirect_Util::get_option( 'skip_if_skipredirect_provided' ) == 'true' && isset( $_GET['skipredirect'] ) )
		{
			$this->debug_logs 							= implode( "\t", array(
				gmdate( 'Y-m-d H:i:s' ),
				'Visitor IP ' . $this->VisitorIP,
				__( 'Redirection terminated. ?skipredirect URL Parameter Found!', 'adv-geoip-redirect' ),
			) );
		}
		// don't continue if request is from a bot/web crawler & option to skip is enabled..
		else if ( Adv_Geoip_Redirect_Util::get_option( 'skip_if_bot' ) == 'true' && Adv_Geoip_Redirect_Util::is_bot() )
		{
			$this->debug_logs 							= implode( "\t", array(
				gmdate( 'Y-m-d H:i:s' ),
				'Visitor IP ' . $this->VisitorIP,
				__( 'Redirection terminated. Bot/Web Crawler Detected!', 'adv-geoip-redirect' ),
			) );
		}
		else
		{
			// set redirect type
			$this->RedirectType 						= Adv_Geoip_Redirect_Util::get_option( 'redirection_type' );

			// if result is found then redirect
			try
			{
				// This creates the Reader object, 
				$reader 								= new \GeoIp2\Database\Reader( ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'public/geoip-db/GeoLite2-Country.mmdb' );
				
				$VisitorGeo 							= $reader->country( $this->VisitorIP );

				$this->VisitorCountry 					= $VisitorGeo->country->isoCode;

				// go over through all rules
				foreach ( $redirect_rules as $rule_set )
				{
					// convert object to array
					$rule_set 							= (array)$rule_set;

					// get current visited url
					$current_url 						= Adv_Geoip_Redirect_Util::get_current_url( $rule_set['IgnoreParameter'] );
					
					// don't continue if redirect to & visited url is same
					if ( esc_url( $rule_set['TargetURLField'] ) == esc_url( $rule_set['VisitedURLField'] ) || $current_url == esc_url( $rule_set['TargetURLField'] ) )
					{
						$this->debug_logs 				= implode( "\t", array(
							gmdate( 'Y-m-d H:i:s' ),
							'Visitor IP ' . $this->VisitorIP,
							__( 'Redirection terminated. Same page redirection! Aborted Redirection to avoid infinite redirect loop!', 'adv-geoip-redirect' ),
						) );

						continue;
					}

					// if url is relative add home_url()
					if ( Adv_Geoip_Redirect_Util::is_url_relative( $rule_set['TargetURLField'] ) )
					{
						$rule_set['TargetURLField'] 	= home_url( esc_url( $rule_set['TargetURLField'] ) );
					}

					if ( Adv_Geoip_Redirect_Util::is_url_relative( $rule_set['VisitedURLField'] ) )
					{
						$rule_set['VisitedURLField'] 	= home_url( esc_url( $rule_set['VisitedURLField'] ) );
					}

					// default check if visitor from country
					$FromChkCondition 					= in_array( $this->VisitorCountry, $rule_set['countryField'] );
					
					// check for visitor country condition for the following rule
					if ( $rule_set['FromChkCondition'] == 'not_from' )
					{
						$FromChkCondition 				= ! in_array( $this->VisitorCountry, $rule_set['countryField'] );
					}
					
					// check if user is from following country
					if ( $FromChkCondition )
					{
						// check if redirect first visit only enabled
						if ( Adv_Geoip_Redirect_Util::get_option( 'redirect_for_first_time_visit_only' ) == 'true' )
						{
							if ( isset( $_COOKIE[sha1( $current_url )] ) )
							{
								$this->debug_logs 		= implode( "\t", array(
									gmdate( 'Y-m-d H:i:s' ),
									'Visitor IP ' . $this->VisitorIP,
									__( 'Redirection terminated. User Already Visited The Page!', 'adv-geoip-redirect' ),
								) );

								continue;
							}
						}

						$VisitedURLField 				= str_replace( [ '?' ], [ '\?' ], $rule_set['VisitedURLField'] );

						// check if VisitedURLField has any url parameter
						$URLParams 						= explode( '?', $rule_set['VisitedURLField'] );

						// if it has Params remove them to go forward
						if ( count( $URLParams ) > 1 )
						{
							$param 						= $URLParams[1];

							$VisitedURLField 			= $URLParams[0] . '[\?|&].*' . $param;
							
							$_SERVER['QUERY_STRING'] 	= preg_replace( "#$param&?#i", '', $_SERVER['QUERY_STRING'] );
						}

						// don't continue if it's a WooCommerce ajax or Job Manager ajax request
						if ( preg_match( '/jm-ajax/', $current_url ) || preg_match( '/wp-content/', $current_url ) || preg_match( '/wc-ajax/', $current_url ) ) continue;

						// now check if user is visiting the set url
						if ( preg_match( "#^$VisitedURLField$#i", $current_url, $matches ) )
						{
							// remove the first value which is basically not needed
							array_shift( $matches );
							
							$redirect_to 				= esc_url( $rule_set['TargetURLField'] );

							if ( strpos( $redirect_to, '(.*)' ) !== false && count( $matches ) )
							{
								$regex_count 			= 0;

								$redirect_to 			= preg_replace_callback( "#\(\.\*\)#", function( $match ) use ( &$regex_count, $matches )
								{
									return $matches[$regex_count++];

								}, $redirect_to );
							}

							// if pass query enabled then add it to url
							if ( $rule_set['PassParameter'] == 'true' && ! empty( $_SERVER['QUERY_STRING'] ) )
							{
								$QueryStringDivider 	= ( strpos( $redirect_to, '?' ) === false )  ? '?' : '&';
								
								$redirect_to 			= $redirect_to . $QueryStringDivider . $_SERVER['QUERY_STRING'];
							}

							// don't continue if redirect to & visited url is same
							if ( $redirect_to == $current_url )
							{
								$this->debug_logs 		= implode( "\t", array(
									gmdate( 'Y-m-d H:i:s' ),
									'Visitor IP ' . $this->VisitorIP,
									__( 'Redirection terminated. Same page redirection! Aborted Redirection to avoid infinite redirect loop!', 'adv-geoip-redirect' ),
								) );

								continue;
							}

							setcookie( sha1( $current_url ), time(), strtotime( '+24 hours' ) );

							// if everything is fine then redirect user to destined url
							if ( wp_redirect( $redirect_to, $this->RedirectType ) )
							{
								$this->debug_logs 		= implode( "\t", array(
									gmdate( 'Y-m-d H:i:s' ),
									'Visitor IP ' . $this->VisitorIP,
									__( 'Redirection succeeded! To ', 'adv-geoip-redirect' ) . $redirect_to . ' From ' . $current_url
								) );
								
								Adv_Geoip_Redirect_Util::write_down_debug_log( $this->debug_logs );
								
								exit();
							}
						
						} //url matching end
					
					} //country matching end
				
				} //endforeach
			}
			catch ( Exception $e )
			{
				$this->debug_logs 						= implode( "\t", array(
					gmdate( 'Y-m-d H:i:s' ),
					'Visitor IP ' . $this->VisitorIP,
					__( 'Redirection terminated. Unable to detect visitor country!', 'adv-geoip-redirect' ),
				) );
			}
		}

		Adv_Geoip_Redirect_Util::write_down_debug_log( $this->debug_logs );
	}
}
