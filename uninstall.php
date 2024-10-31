<?php
/**
 * WooCommerceQuickCEP Uninstall
 *
 * Uninstalling WooCommerceQuickCEP deletes user roles, options, tables, and pages.
 *
 * @author    QuickCEP
 * @category  Core
 * @package   WooCommerceQuickCEP/Uninstaller
 * @version   0.0.1
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
  exit();

// Remove role capabilities.
include( 'includes/class-wck-install.php' );
WCK_Install::remove_roles();
