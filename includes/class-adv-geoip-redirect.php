<?php
/**
 * This file contains the definition of the Adv_Geoip_Redirect class, which
 * is used to begin the plugin's functionality.
 *
 * @package       Adv_Geoip_Redirect
 * @subpackage    Adv_Geoip_Redirect/includes
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

/**
 * The core plugin class.
 *
 * This is used to define admin-specific hooks and public-facing hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since    2.0.0
 */
class Adv_Geoip_Redirect {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since     2.0.0
	 * @access    protected
	 * @var       Adv_Geoip_Redirect_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since     2.0.0
	 * @access    protected
	 * @var       string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since     2.0.0
	 * @access    protected
	 * @var       string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Stores the plugin's redirection default settings.
	 *
	 * This static property holds an array containing the default values redirection settings
	 * for the plugin.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @var       array $redirect_settings The array containing redirection settings.
	 */
	public static $default_settings;

	/**
	 * The name of the plugin's option in the WordPress options table.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @var       string
	 */
	public static $option_name = 'geoipr_redirect_options';

	/**
	 * An array of option field names.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @var       array
	 */
	public static $option_fields = array(
		'redirect_switch',
		'dev_mode',
		'dubug_log',
		'skip_if_bot',
		'skip_if_skipredirect_provided',
		'redirect_for_first_time_visit_only',
		'redirection_type',
		'redirect_rules',
	);

	/**
	 * An array of default values for the plugin options.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @var       array
	 */
	public static $default_option_values = array(
		'false',
		'false',
		'false',
		'false',
		'false',
		'false',
		'302',
		array(),
	);

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function __construct() {
		$this->version     = defined( 'ADV_GEOIP_REDIRECT_PLUGIN_VERSION' ) ? ADV_GEOIP_REDIRECT_PLUGIN_VERSION : '1.0.0';
		$this->plugin_name = 'adv-geoip-redirect';

		self::$default_settings = array_combine( self::$option_fields, self::$default_option_values );

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Adv_Geoip_Redirect_Loader. Orchestrates the hooks of the plugin.
	 * - Adv_Geoip_Redirect_Admin.  Defines all hooks for the admin area.
	 * - Adv_Geoip_Redirect_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since     2.0.0
	 * @access    private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'includes/class-adv-geoip-redirect-loader.php';

		/**
		 * Loads the composer autoload file to include packages.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'vendor/autoload.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'admin/class-adv-geoip-redirect-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'public/class-adv-geoip-redirect-public.php';

		$this->loader = new Adv_Geoip_Redirect_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Adv_Geoip_Redirect_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'plugin_action_links_' . ADV_GEOIP_REDIRECT_PLUGIN_BASENAME, $plugin_admin, 'add_plugin_action_links' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		$this->loader->add_filter( 'admin_notices', $plugin_admin, 'admin_notices' );
		$this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'admin_footer_text' );

		$this->loader->add_filter( 'wp_ajax_geoipr_form_submit', $plugin_admin, 'capture_form_submit' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 */
	private function define_public_hooks() {
		$plugin_public = new Adv_Geoip_Redirect_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded', $plugin_public, 'always_load_this_plugin_first' );

		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect', PHP_INT_MAX );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    Adv_Geoip_Redirect_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieves the value of a specific settings field.
	 *
	 * This method fetches the value of a settings field from the WordPress options database.
	 * It retrieves the entire option group for the given section and then extracts the
	 * value for the specified field.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @param     string $option        The name of the settings field.
	 * @param     string $section       The name of the section this field belongs to. This corresponds
	 *                                  to the option name used in `register_setting()`.
	 * @param     string $default_value Optional. The default value to return if the field's value
	 *                                  is not found in the database. Default is an empty string.
	 * @return    string|mixed          The value of the settings field, or the default value if not found.
	 */
	public static function get_option( $option, $section, $default_value = '' ) {
		$options = get_option( $section ); // Get all options for the section.

		// Check if the option exists within the section's options array.
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ]; // Return the option value.
		}

		return $default_value; // Return the default value if the option is not found.
	}

	/**
	 * Sets the plugin's default option values.
	 *
	 * This static function resets the plugin's settings to their default values by updating
	 * the corresponding option in the WordPress options table.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 */
	public static function reset_plugin_settings() {
		// Update the plugin option with the default settings.
		update_option( self::$option_name, self::$default_settings );
	}

	/**
	 * Updates the plugin's option values.
	 *
	 * This static function updates the plugin's settings to the provided values by updating
	 * the corresponding option in the WordPress options table.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @param     array $options Plugin options array.
	 * @return    bool           True if the value was updated, false otherwise.
	 */
	public static function update_plugin_settings( $options ) {
		// Check if provided $options variable value is an array.
		if ( ! is_array( $options ) ) {
			return false; // Return false if not.
		}

		// Check if all required fields exist.
		foreach ( self::$option_fields as $field ) {
			if ( ! array_key_exists( $field, $options ) ) {
				return false; // Return false if any required field is missing.
			}
		}

		// options only the allowed fields and remove any extra ones.
		$options = array_intersect_key( $options, array_flip( self::$option_fields ) );

		// Update the plugin option with the provided settings.
		return update_option( self::$option_name, self::sanitize_array_recursively( $options ) );
	}

	/**
	 * Retrieves plugin settings from the WordPress options table using the Options API.
	 *
	 * This static function retrieves the plugin settings stored in the WordPress options table
	 * using the option name defined in `self::$option_name`.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @return    array The plugin settings as an array, or default if the option is not set.
	 */
	public static function get_plugin_settings() {
		// Retrieve the plugin settings from the options table.
		return get_option( self::$option_name, self::$default_settings );
	}

	/**
	 * Retrieves an array of labels for checkbox fields in the plugin settings.
	 *
	 * This static function returns an array containing translatable labels for checkbox
	 * fields used in the plugin's settings page. It also includes placeholder values,
	 * which may need to be reviewed and adjusted based on the intended functionality.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @return    array An array of checkbox field labels and placeholder values.
	 */
	public static function get_plugin_settings_chk_fields() {
		return array(
			__( 'Enable Redirection', 'adv-geoip-redirect' ),
			__( 'Enable Development Mode', 'adv-geoip-redirect' ),
			__( 'Write Down Debug Log', 'adv-geoip-redirect' ),
			__( 'Skip Redirect For Bot & Crawlers', 'adv-geoip-redirect' ),
			__( 'Skip Redirect If <code>?skipredirect</code> Parameter Found', 'adv-geoip-redirect' ),
			__( 'Only Redirect If First Time Visit (reset after 24hrs)', 'adv-geoip-redirect' ),
			'false',
			'false',
		);
	}

	/**
	 * Recursively sanitizes each field within an array.
	 *
	 * This static function iterates through all elements of an array, including nested arrays,
	 * and sanitizes each value using WordPress's `sanitize_text_field()` function.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @param     array $original_array The array to be sanitized (passed by reference).
	 * @return    array                 The sanitized array.
	 */
	public static function sanitize_array_recursively( array &$original_array ) {
		if ( ! is_array( $original_array ) ) {
			return $original_array;
		}

		// Recursively walk through the array and sanitize each value.
		array_walk_recursive(
			$original_array,
			function ( &$value ) {
				$value = sanitize_text_field( $value );
			}
		);

		// Return the sanitized array.
		return $original_array;
	}

	/**
	 * Checks if a URL is relative to the WordPress home URL.
	 *
	 * This static function determines if a given URL is relative by checking if it lacks a protocol
	 * and starts with a forward slash.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @param     string $uri The URL to check.
	 * @return    bool        True if the URL is relative, false otherwise.
	 */
	public static function is_url_relative( $uri ) {
		// Check if the URL lacks a protocol (e.g., http:// or https://) and starts with a forward slash.
		return ( strpos( $uri, '://' ) === false && substr( $uri, 0, 1 ) === '/' );
	}

	/**
	 * Generates plugin settings as JSON data and exports it to the browser as a JSON file.
	 *
	 * This static function retrieves the plugin settings, encodes them as JSON, and sends
	 * them to the browser as a downloadable JSON file.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 */
	public static function export_settings() {
		// Retrieve plugin settings.
		$settings = self::get_plugin_settings();

		// Prevent the script from being interrupted if the user closes the browser.
		ignore_user_abort( true );

		// Set no-cache headers to prevent caching.
		nocache_headers();

		// Set the content type to JSON.
		header( 'Content-Type: application/json; charset=utf-8' );

		// Set the content disposition to attachment, prompting the user to download the file.
		header( 'Content-Disposition: attachment; filename=geoipr-settings-export-' . gmdate( 'm-d-Y' ) . '.json' );

		// Set the Expires header to 0 to prevent caching.
		header( 'Expires: 0' );

		// Output the JSON encoded settings.
		echo wp_json_encode( $settings );

		// Exit to prevent further script execution.
		exit;
	}

	/**
	 * Imports plugin settings from a JSON file.
	 *
	 * This static function imports plugin settings from a specified JSON file.
	 * It decodes the JSON content, converts it to an array, and updates the plugin's
	 * settings option in the WordPress database.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @param     string $import_file The path to the JSON file containing the settings.
	 * @return    bool                True if the settings were successfully imported, false otherwise.
	 */
	public static function import_settings( $import_file ) {
		try {
			global $wp_filesystem;

			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();

			$file_contents = $wp_filesystem->get_contents( $import_file );

			// Retrieve the settings from the file and convert the JSON object to an array.
			$settings = (array) json_decode( $file_contents, true );

			// Update the plugin settings option.
			return self::update_plugin_settings( $settings );
		} catch ( Exception $e ) {
			// Return false if an exception occurs during the process.
			return false;
		}
	}

	/**
	 * Retrieves the full URL of the currently visited page.
	 *
	 * This static function constructs the full URL of the current page, including the protocol,
	 * host, port, and URI. It optionally ignores URL parameters (query strings).
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @param     string $ignore_url_parameter Optional. Whether to ignore URL parameters. Defaults to 'false'.
	 * @return    string The full URL of the current page.
	 */
	public static function get_current_url( $ignore_url_parameter = 'false' ) {
		$s = &$_SERVER;

		// Determine if SSL is enabled.
		$ssl_enabled = ( ! empty( $s['HTTPS'] ) && 'on' === $s['HTTPS'] );

		// Determine the server protocol.
		$server_protocol = strtolower( $s['SERVER_PROTOCOL'] );

		// Construct the protocol (http or https).
		$protocol = substr( $server_protocol, 0, strpos( $server_protocol, '/' ) ) . ( $ssl_enabled ? 's' : '' );

		// Determine the server port.
		$port = $s['SERVER_PORT'];

		// Add the port to the URL if it's not the default (80 or 443).
		$port = ( ( ! $ssl_enabled && '80' === $port ) || ( $ssl_enabled && '443' === $port ) ) ? '' : ':' . $port;

		// Determine the host.
		$host = isset( $s['HTTP_X_FORWARDED_HOST'] ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		$host = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;

		// Construct the full URI.
		$uri = $protocol . '://' . $host . $s['REQUEST_URI'];

		// Remove URL parameters if requested.
		if ( 'true' === $ignore_url_parameter ) {
			$segments = explode( '?', $uri, 2 );
			$uri      = $segments[0];
		}

		// Return the URL, ensuring a trailing slash.
		return rtrim( $uri, '/' ) . '/';
	}

	/**
	 * Retrieves the client's IP address.
	 *
	 * This function attempts to determine the client's IP address by checking various
	 * server variables. It handles cases where the client might be behind a proxy or load balancer.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @return    string|null The client's IP address, or null if it cannot be determined.
	 */
	public static function get_visitor_ip() {
		$ipaddress = '';

		// If website is hosted behind CloudFlare protection.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( isset( $_SERVER['X-Real-IP'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['X-Real-IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// validate ip address.
		if ( filter_var( $ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return $ipaddress;
		}

		return $ipaddress;
	}

	/**
	 * Checks if the current request is from a bot or search engine crawler.
	 *
	 * This static function uses a regular expression to analyze the user agent string
	 * and determine if the request is likely from a bot or crawler.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @return    bool True if the request is from a bot, false otherwise.
	 */
	public static function is_bot() {
		// Get the user agent string.
		$user_agent = self::get_user_agent();

		// Check if the user agent string is not empty and matches a bot pattern.
		if ( ! empty( $user_agent ) && preg_match( '/baidu|bingbot|facebookexternalhit|googlebot|-google|ia_archiver|msnbot|naverbot|pingdom|seznambot|slurp|teoma|twitter|yandex|yeti|linkedinbot|pinterest/i', $user_agent ) ) {
			// Return true if it's a bot.
			return true;
		}

		// Return false if it's not a bot.
		return false;
	}

	/**
	 * Retrieves the visitor's browser user agent string.
	 *
	 * This static function retrieves the user agent string from the HTTP_USER_AGENT server variable.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @return    string|null The user agent string, or null if it's not set.
	 */
	public static function get_user_agent() {
		return ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null;
	}

	/**
	 * Writes debug logs to the debug.log file.
	 *
	 * This static function appends debug log messages to the debug.log file if debugging is enabled
	 * in the plugin settings.
	 *
	 * @since    2.0.0
	 * @static
	 * @access   public
	 * @param    string $debug_logs The debug log message to write.
	 */
	public static function write_down_debug_log( $debug_logs = '' ) {
		// Check if settings are valid and debugging is enabled.
		if ( 'true' === self::get_option( 'dubug_log', self::$option_name, 'false' ) ) {
			// Check if debug logs are not empty.
			if ( ! empty( $debug_logs ) ) {
				// Append debug logs to the file.
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'debug.log', 'Debug Log : ' . $debug_logs . "\n", FILE_APPEND );
			}
		}
	}

	/**
	 * Reads and returns the contents of the debug log file.
	 *
	 * This static function reads the contents of the debug log file (debug.log) if debugging is enabled
	 * in the plugin settings.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @return    string|null The contents of the debug log file, or an empty string if debugging is disabled or the log file doesn't exist.
	 */
	public static function read_debug_log() {
		// Check if settings are valid and debugging is enabled.
		if ( 'true' === self::get_option( 'dubug_log', self::$option_name, 'false' ) ) {
			// Construct the debug log file path.
			$log_file_path = ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'debug.log';

			global $wp_filesystem;

			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();

			// Check if the debug log file exists.
			if ( $wp_filesystem->exists( $log_file_path ) ) {
				// Read and return the contents of the log file.
				return $wp_filesystem->get_contents( $log_file_path );
			}
		}

		// Return an empty string if debugging is disabled or the log file doesn't exist.
		return '';
	}

	/**
	 * Retrieves a list of countries with their ISO codes and names.
	 *
	 * This static function returns an associative array containing a list of countries,
	 * where the keys are the ISO 3166-1 alpha-2 country codes and the values are
	 * the corresponding country names.
	 *
	 * @since     2.0.0
	 * @static
	 * @access    public
	 * @return    array An associative array of countries (ISO code => country name).
	 */
	public static function get_countries() {
		$countries = array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia, Plurinational State of',
			'BQ' => 'Bonaire, Sint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'CV' => 'Cabo Verde',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, The Democratic Republic of The',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => "Cote D'ivoire",
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curacao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and Mcdonald Islands',
			'VA' => 'Holy See',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran, Islamic Republic of',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea, Democratic People\'s Republic of',
			'KR' => 'Korea, Republic of',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia, The Former Yugoslav Republic of',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia, Federated States of',
			'MD' => 'Moldova, Republic of',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestine, State of',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena, Ascension and Tristan Da Cunha',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin (French Part)',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and The Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SX' => 'Sint Maarten (Dutch Part)',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and The South Sandwich Islands',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SZ' => 'Eswatini',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan, Province of China',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania, United Republic of',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela, Bolivarian Republic of',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);

		/**
		 * Filters the list of countries.
		 *
		 * This filter allows you to modify the plugin provided list of countries.
		 * You can use this filter to add/remove/edit the list.
		 *
		 * @since     2.0.0
		 * @param     array $countries Default list of countries.
		 * @return    array $countries Modified list of countries.
		 */
		return apply_filters( 'adv_geoip_redirect_countries', $countries );
	}
}
