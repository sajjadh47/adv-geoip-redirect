<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      2.0.0
 * @package    Adv_Geoip_Redirect
 * @subpackage Adv_Geoip_Redirect/includes
 * @author     Sajjad Hossain Sagor <sagorh672@gmail.com>
 */
class Adv_Geoip_Redirect_i18n
{
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    2.0.0
	 * @access   public
	 */
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain(
			'adv-geoip-redirect',
			false,
			dirname( ADV_GEOIP_REDIRECT_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
