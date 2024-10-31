<?php
/**
 * Installation related functions and actions.
 *
 * @author    QuickCEP
 * @category  Admin
 * @package   WooCommerceQuickCEP/Classes
 * @version   0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WCK_Install' ) ) :

	/**
	 * WCK_Install Class
	 */
	class WCK_Install {
		/**
		 * Get capabilities for WooCommerceQuickCEP - these are assigned to admin/shop manager during installation or reset
		 *
		 * @access public
		 * @return array
		 */
		public static function get_core_capabilities() {
			$capabilities = array();

			$capability_types = array( 'quickcep_shop_cart', );

			foreach ( $capability_types as $capability_type ) {

				$capabilities[ $capability_type ] = array(
					// Post type
					"edit_{$capability_type}",
					"read_{$capability_type}",
					"delete_{$capability_type}",
					"edit_{$capability_type}s",
					"edit_others_{$capability_type}s",
					"publish_{$capability_type}s",
					"read_private_{$capability_type}s",
					"delete_{$capability_type}s",
					"delete_private_{$capability_type}s",
					"delete_published_{$capability_type}s",
					"delete_others_{$capability_type}s",
					"edit_private_{$capability_type}s",
					"edit_published_{$capability_type}s",

					// Terms
					"manage_{$capability_type}_terms",
					"edit_{$capability_type}_terms",
					"delete_{$capability_type}_terms",
					"assign_{$capability_type}_terms"
				);
			}

			return $capabilities;
		}

		/**
		 * woocommerce-QuickCEP_remove_roles function.
		 *
		 * @access public
		 * @return void
		 */
		public static function remove_roles() {
			global $wp_roles;

			if ( class_exists( 'WP_Roles' ) ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}

			if ( is_object( $wp_roles ) ) {

				$capabilities = self::get_core_capabilities();

				foreach ( $capabilities as $cap_group ) {
					foreach ( $cap_group as $cap ) {
						$wp_roles->remove_cap( 'shop_manager', $cap );
						$wp_roles->remove_cap( 'administrator', $cap );
					}
				}
			}
		}

		/**
		 * Called from WCK_Api via the `disable` route. Cleanup QuickCEP plugin data (do not send a webhook since this
		 * request comes from the app) and then deactivate the plugin.
		 */
		public function deactivate_quickcep() {
			$this->cleanup_quickcep( $should_send_webhook = false );
			deactivate_plugins( QUICKCEP_BASENAME );
		}

		/**
		 * Called via register_deactivation_hook. Args aren't accepted, even defaults, through the
		 * hook so we need to wrap it.
		 */
		public function cleanup_quickcep_on_deactivation() {
			$this->cleanup_quickcep();
		}

		/**
		 * Handle cleanup of the plugin.
		 * Delete options and remove WooCommerce webhooks.
		 * Optionally send a webhook to remove the integration in QuickCEP to keep state aligned.
		 *
		 * @param bool $should_send_webhook Whether to send a webhook to QuickCEP to remove the integration.
		 */
		private function cleanup_quickcep( $should_send_webhook = true ) {
			// We can't remove webhooks without WooCommerce. No need to remove the integration app-side.
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				// Remove WooCommerce webhooks
				self::remove_quickcep_webhooks();

				if ( $should_send_webhook ) {
					// Send remove webhook to QuickCEP
					// WCK()->webhook_service->send_remove_webhook();
				}
			}

			// Lastly, delete QuickCEP-related options.
			delete_option( 'quickcep_settings' );
			delete_option( 'woocommerce_quickcep_version' );
			delete_site_transient( 'is_quickcep_plugin_outdated' );
		}

		/**
		 * Remove QuickCEP related webhooks. The only way to identify these are through the delivery url so check for the
		 * Woocommerce webhook path.
		 */
		private static function remove_quickcep_webhooks() {
			$webhook_data_store = WC_Data_Store::load( 'webhook' );
			$webhooks_by_status = $webhook_data_store->get_count_webhooks_by_status();
			// $webhooks_by_status returns an associative array with a count of webhooks in each status.
			$count = array_sum( $webhooks_by_status );

			if ( 0 === $count ) {
				return;
			}

			// We can only get IDs and there's not a way to search by delivery url which is the only way to identify
			// a webhook created by QuickCEP. We'll have to iterate no matter what so might as well get them all.
			$webhook_ids = $webhook_data_store->get_webhooks_ids();

			foreach ( $webhook_ids as $webhook_id ) {
				$webhook = wc_get_webhook( $webhook_id );
				if ( ! $webhook ) {
					continue;
				}

				if ( false !== strpos( $webhook->get_delivery_url(), '/api/webhook/integration/woocommerce' ) ) {
					$webhook_data_store->delete( $webhook );
				}
			}
		}
	}

endif;
