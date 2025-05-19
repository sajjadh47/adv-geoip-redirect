<?php
/**
 * This file contains the definition of the Adv_Geoip_Redirect_I18n class, which
 * is used to load the plugin's internationalization.
 *
 * @package       Adv_Geoip_Redirect
 * @subpackage    Adv_Geoip_Redirect/includes
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since    2.0.0
 */
class Adv_Geoip_Redirect_I18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'adv-geoip-redirect',
			false,
			dirname( ADV_GEOIP_REDIRECT_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
