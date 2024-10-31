<?php
class WPQuickCEPAdmin {

    function __construct() {
        if ( ! is_admin() ) {
            return;
        }

        add_action( 'plugins_loaded', array( $this, 'setup_admin' ) );
    }

    public function setup_admin()
    {
        if ( ! function_exists( 'add_menu_page' )) {
            return;
        }

        add_action( 'admin_menu', array( $this, 'add_settings_oauth' ) );
    }

    /**
     * Create QuickCEP plugin menu tab in left navigation panel.
     *
     */
    public function add_menu_page( $function )
    {
        add_menu_page( 'QuickCEP', 'QuickCEP', 'manage_options', 'quickcep_settings', array( $this, $function ), QUICKCEP_URL . 'img/LOGO.svg' );
    }

    /**
     * Add QuickCEP menu tab for new authentication process.
     */
    public function add_settings_oauth()
    {
        $this->add_menu_page( 'settings_oauth' );
    }


    /**
     * Settings page content for new authentication process.
     */
    public function settings_oauth()
    {
        include_once( QUICKCEP_PATH . '/includes/admin/partials/wc_v1_auth_settings.html' );
    }

}
?>
