<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.0
 * @package    Adv_Geoip_Redirect
 * @subpackage Adv_Geoip_Redirect/includes
 * @author     Sajjad Hossain Sagor <sagorh672@gmail.com>
 */
class Adv_Geoip_Redirect
{
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      Adv_Geoip_Redirect_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The settings array for redirection configurations.
	 *
	 * This variable holds an array containing the redirection settings for the plugin.
	 * It is used to store and manage various redirection rules and options.
	 *
	 * @since    2.0.0
	 * @access   public
	 * @var      array		$settings 	The array containing redirection settings.
	 */
	public $settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function __construct()
	{
		if ( defined( 'ADV_GEOIP_REDIRECT_VERSION' ) )
		{
			$this->version = ADV_GEOIP_REDIRECT_VERSION;
		}
		else
		{
			$this->version = '2.0.0';
		}
		
		$this->plugin_name = 'adv-geoip-redirect';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Adv_Geoip_Redirect_Loader. Orchestrates the hooks of the plugin.
	 * - Adv_Geoip_Redirect_i18n. Defines internationalization functionality.
	 * - Adv_Geoip_Redirect_Admin. Defines all hooks for the admin area.
	 * - Adv_Geoip_Redirect_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'includes/class-plugin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'includes/class-plugin-i18n.php';

		/**
		 * Loads the utility class containing helpful functions for the plugin.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'includes/class-plugin-util.php';

		/**
		 * Loads the composer autoload file to include packages.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'vendor/autoload.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'admin/class-plugin-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once ADV_GEOIP_REDIRECT_PLUGIN_PATH . 'public/class-plugin-public.php';

		$this->loader = new Adv_Geoip_Redirect_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Adv_Geoip_Redirect_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Adv_Geoip_Redirect_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Adv_Geoip_Redirect_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'admin_footer_text' );
		$this->loader->add_filter( 'admin_notices', $plugin_admin, 'admin_notices' );
		$this->loader->add_filter( 'wp_ajax_geoipr_form_submit', $plugin_admin, 'capture_form_submit' );

		$this->loader->add_action( 'plugin_action_links_' . ADV_GEOIP_REDIRECT_PLUGIN_BASENAME, $plugin_admin, 'add_plugin_action_links' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Adv_Geoip_Redirect_Public( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'plugins_loaded', $plugin_public, 'always_load_this_plugin_first' );
		
		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect', PHP_INT_MAX );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    Adv_Geoip_Redirect_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
