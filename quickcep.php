<?php
/*
Plugin Name: QuickCEP
Plugin URI: https://app.quickcep.com/panel/login
Description: Quickcep- Chatbots、Live Chat、Email marketing、Popup and  CDP（ Customer Data Platform）
Version: 1.1.0
Author: QuickCEP
Author URI: https://www.quickcep.com/home
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'is_plugin_inactive' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( ! class_exists( 'WooCommerceQuickCEP' ) ) :

	/**
	 * Main WooCommerceQuickCEP Class
	 *
	 * @class WooCommerceQuickCEP
	 * @version 0.0.1
	 */
	final class WooCommerceQuickCEP {

		/**
		 * @var WooCommerceQuickCEP The single instance of the class
		 * @since 2.0.0
		 */
		protected static $_instance = null;

		/**
		 * @var WCK_Install $installer Responsible for install/uninstall logic.
		 */
		public $installer;

		/**
		 * @var WPQuickCEPAdmin $admin Handles plugin's admin page content and functionality.
		 */
		public $admin;

		/**
		 * Main WooCommerceQuickCEP Instance
		 *
		 * Ensures only one instance of WooCommerceQuickCEP is loaded or can be loaded.
		 *
		 * @return WooCommerceQuickCEP - Main instance
		 * @see WCK()
		 * @since 2.0.0
		 * @static
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-QuickCEP' ), '0.9' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-QuickCEP' ), '0.9' );
		}

		/**
		 * WooCommerceQuickCEP Constructor.
		 * @access public
		 * @return WooCommerceQuickCEP
		 */
		public function __construct() {
			// Auto-load classes on demand
			if ( function_exists( "__autoload" ) ) {
				spl_autoload_register( "__autoload" );
			}

			spl_autoload_register( array( $this, 'autoload' ) );

			$this->define_constants();

			// Include required files
			$this->includes();

			// Init API
			$this->installer = new WCK_Install();
			$this->admin     = new WPQuickCEPAdmin();

			// Hooks
			add_action( 'init', array( $this, 'init' ), 0 );
			// $this->define_admin_hooks();
			// add_action( 'init', array( $this, 'include_template_functions' ) );

			// Loaded action
			// do_action( 'woocommerce_quickcep_loaded' );
		}

		/**
		 * Auto-load in-accessible properties on demand.
		 *
		 * @param mixed $key
		 *
		 * @return mixed
		 */
		public function __get( $key ) {
			if ( method_exists( $this, $key ) ) {
				return $this->$key();
			}

			return false;
		}

		/**
		 * Auto-load WC classes on demand to reduce memory consumption.
		 *
		 * @param mixed $class
		 *
		 * @return void
		 */
		public function autoload( $class ) {
			$path  = null;
			$class = strtolower( $class );
			$file  = 'class-' . str_replace( '_', '-', $class ) . '.php';

			if ( $path && is_readable( $path . $file ) ) {
				include_once( $path . $file );

				return;
			}

			// Fallback
			if ( strpos( $class, 'wck_' ) === 0 ) {
				$path = $this->plugin_path() . '/includes/';
			}

			if ( $path && is_readable( $path . $file ) ) {
				include_once( $path . $file );

				return;
			}
		}

		// Define WC Constants

		private function define_constants() {
			define( 'QUICKCEP_PLUGIN_FILE', __FILE__ );
		}

		// Include required core files used in admin and on the frontend. Only include wck-core if WooCommerce
		// plugin is activated. Always include analytics.
		private function includes() {
			include_once( 'includes/class-wck-install.php' );
			include_once( 'inc/quickcep-admin.php' );
		}

		private function define_admin_hooks() {
			// Add admin styles.
			// add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_styles' ) );
		}

		/**
		 * Function used to Init WooCommerce Template Functions - This makes them pluggable by plugins and themes.
		 */
		// public function include_template_functions() {
		//   include_once( 'includes/wc-template-functions.php' );
		// }

		/**
		 * Init WooCommerceQuickCEP when WordPress Initialises.
		 */
		public function init() {
			// Init action
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		/*public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}*/

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	}

endif;

/**
 * Returns the main instance of WCK to prevent the need to use globals.
 *
 * @return WooCommerceQuickCEP
 * @since  0.9
 */

if ( ! function_exists( 'WCK' ) ) {
	function WCK() {
		return WooCommerceQuickCEP::instance();
	}
}

// Global for backwards compatibility.
$GLOBALS['woocommerce-QuickCEP'] = WCK();

// load the wordpress tracking and widgets

// Makes sure the plugin is defined before trying to use it

$url = plugins_url();

if ( ! function_exists( 'is_plugin_inactive' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( is_plugin_inactive( 'wordpress-quickcep-master/QuickCEP.php' ) ) {
	//plugin is not activated

	$my_plugin_file = __FILE__;

	if ( isset( $plugin ) ) {
		$my_plugin_file = $plugin;
	} else if ( isset( $mu_plugin ) ) {
		$my_plugin_file = $mu_plugin;
	} else if ( isset( $network_plugin ) ) {
		$my_plugin_file = $network_plugin;
	}


//
// CONSTANTS
// ------------------------------------------
	if ( ! defined( 'QUICKCEP_URL' ) ) {
		define( 'QUICKCEP_URL', plugin_dir_url( $my_plugin_file ) );
	}
	if ( ! defined( 'QUICKCEP_PATH' ) ) {
		define( 'QUICKCEP_PATH', __DIR__ . '/' );
	}
	if ( ! defined( 'QUICKCEP_BASENAME' ) ) {
		define( 'QUICKCEP_BASENAME', plugin_basename( $my_plugin_file ) );
	}

	// Handle deactivation
	register_deactivation_hook( __FILE__, array( WCK()->installer, 'cleanup_quickcep_on_deactivation' ) );
}