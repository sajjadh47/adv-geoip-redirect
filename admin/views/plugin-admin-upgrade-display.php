<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since         2.0.5
 * @package       Adv_Geoip_Redirect
 * @subpackage    Adv_Geoip_Redirect/admin/views
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?>
<div class="wrap adv-upgrade-to-pro-container">
	<p><strong><?php esc_html_e( 'Advanced GeoIP Redirect Pro', 'adv-geoip-redirect' ); ?></strong> <?php esc_html_e( 'gives you the power to create smarter, faster, and more reliable redirects with advanced geolocation technology, cache-bypass compatibility, and actionable visitor analytics — all built seamlessly into WordPress.', 'adv-geoip-redirect' ); ?></p>

	<p><?php esc_html_e( 'With the', 'adv-geoip-redirect' ); ?> <strong><?php esc_html_e( 'MaxMind GeoIP2 Database', 'adv-geoip-redirect' ); ?></strong> <?php esc_html_e( 'and our optimized engine, you’ll get unmatched accuracy and performance, even on heavily cached websites. Plus, with the Pro debug log viewer and analytics dashboard, you’ll always know exactly how your redirects are performing.', 'adv-geoip-redirect' ); ?></p>

	<h3>
		<?php esc_html_e( 'Why Upgrade?', 'adv-geoip-redirect' ); ?>
	</h3>
	<ul class="ul-disc">
		<li><strong><?php esc_html_e( 'Bypass cache issues:', 'adv-geoip-redirect' ); ?></strong> <?php esc_html_e( 'Works flawlessly with 25+ popular caching and hosting platforms.', 'adv-geoip-redirect' ); ?></li>
		<li><strong><?php esc_html_e( 'Unmatched accuracy:', 'adv-geoip-redirect' ); ?></strong> <?php esc_html_e( 'MaxMind GeoIP2 for country-level precision.', 'adv-geoip-redirect' ); ?></li>
		<li><strong><?php esc_html_e( 'See everything:', 'adv-geoip-redirect' ); ?></strong> <?php esc_html_e( 'Debug log viewer with filters, sorting, and timestamps.', 'adv-geoip-redirect' ); ?></li>
		<li><strong><?php esc_html_e( 'Make decisions with data:', 'adv-geoip-redirect' ); ?></strong> <?php esc_html_e( 'Analytics dashboard with charts for countries, URLs, IPs, and peak hours.', 'adv-geoip-redirect' ); ?></li>
		<li><strong><?php esc_html_e( 'Peace of mind:', 'adv-geoip-redirect' ); ?></strong> <?php esc_html_e( 'Priority support + continuous updates.', 'adv-geoip-redirect' ); ?></li>
	</ul>

	<h3><?php esc_html_e( 'Free vs Pro Comparison', 'adv-geoip-redirect' ); ?></h3>
	<table class="widefat striped adv-upgrade-comparison">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Feature', 'adv-geoip-redirect' ); ?></th>
				<th><?php esc_html_e( 'Free', 'adv-geoip-redirect' ); ?></th>
				<th><?php esc_html_e( 'Pro', 'adv-geoip-redirect' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Basic country-based redirects', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '✅', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '✅', 'adv-geoip-redirect' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Works with caching plugins', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '❌', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '✅ (25+ supported)', 'adv-geoip-redirect' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'GeoIP database accuracy', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( 'Basic (Lite)', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( 'MaxMind GeoIP2', 'adv-geoip-redirect' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Debug log (basic)', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( 'Minimal', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( 'Tabular log viewer with filters', 'adv-geoip-redirect' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Analytics dashboard', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '❌', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '✅ Charts by country, URL, IP, hour', 'adv-geoip-redirect' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Advanced rule options', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '❌', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '✅ Pass/ignore URL parameters', 'adv-geoip-redirect' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Developer hooks & filters', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( 'Limited', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( '✅ Extensive', 'adv-geoip-redirect' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Support & updates', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( 'Community only', 'adv-geoip-redirect' ); ?></td>
				<td><?php esc_html_e( 'Priority Pro support', 'adv-geoip-redirect' ); ?></td>
			</tr>
		</tbody>
	</table>

	<h3>
		<?php esc_html_e( 'Supported Cache Plugins', 'adv-geoip-redirect' ); ?>
	</h3>
	<p><?php esc_html_e( 'Pro is fully compatible with the most popular caching plugins and hosting optimizations, including:', 'adv-geoip-redirect' ); ?></p>
	<ul class="ul-disc">
		<li><?php esc_html_e( 'WP Rocket', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'LiteSpeed Cache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'W3 Total Cache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'WP-Optimize', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'WP Super Cache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'Super Page Cache (Cloudflare)', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'WP Fastest Cache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'Redis Object Cache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'Breeze', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'NitroPack', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'Docket Cache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'SpeedyCache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'Cache Enabler', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( 'Kinsta / WPEngine / SiteGround / Pantheon hosting cache', 'adv-geoip-redirect' ); ?></li>
		<li><?php esc_html_e( '…and many more', 'adv-geoip-redirect' ); ?></li>
	</ul>

	<p class="adv-upgrade-cta">
		<a href="<?php echo esc_url( Adv_Geoip_Redirect_Admin::$upgrade_link ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'Upgrade To Pro Now →', 'adv-geoip-redirect' ); ?>
		</a>
	</p>
</div>