<?php
/**
 * Plugin Name:       Vetra Dashboard
 * Plugin URI:        https://vetra.local/
 * Description:       A modern, fully-featured user dashboard and account panel for WordPress. Independent from WooCommerce. Includes tickets, notifications, polls, attachments, banking, wallet, SMS OTP auth and more.
 * Version:           2.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Vetra
 * Author URI:        https://vetra.local/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vetra-dashboard
 * Domain Path:       /languages
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

define( 'VTD_VERSION', '2.1.0' );
define( 'VTD_FILE', __FILE__ );
define( 'VTD_PATH', plugin_dir_path( __FILE__ ) );
define( 'VTD_URL', plugin_dir_url( __FILE__ ) );
define( 'VTD_BASENAME', plugin_basename( __FILE__ ) );
define( 'VTD_INCLUDES', VTD_PATH . 'includes/' );
define( 'VTD_MODULES', VTD_PATH . 'modules/' );
define( 'VTD_ADMIN', VTD_PATH . 'admin/' );
define( 'VTD_TEMPLATES', VTD_PATH . 'templates/' );
define( 'VTD_ASSETS', VTD_URL . 'assets/' );
define( 'VTD_DB_VERSION', '1.1.0' );

if ( ! defined( 'VTD_OPTION_KEY' ) ) {
	define( 'VTD_OPTION_KEY', 'vetra_settings' );
}

require_once VTD_INCLUDES . 'functions.php';
require_once VTD_INCLUDES . 'class-vtd-options.php';
require_once VTD_INCLUDES . 'class-vtd-modules.php';
require_once VTD_INCLUDES . 'class-vtd-social.php';
require_once VTD_INCLUDES . 'class-vtd-changes.php';
require_once VTD_INCLUDES . 'class-vtd-db.php';
require_once VTD_INCLUDES . 'class-vtd-roles.php';
require_once VTD_INCLUDES . 'class-vtd-install.php';
require_once VTD_INCLUDES . 'class-vtd-assets.php';
require_once VTD_INCLUDES . 'class-vtd-templates.php';
require_once VTD_INCLUDES . 'class-vtd-router.php';
require_once VTD_INCLUDES . 'class-vtd-shortcodes.php';
require_once VTD_INCLUDES . 'class-vtd-ajax.php';
require_once VTD_INCLUDES . 'class-vtd-email.php';
require_once VTD_INCLUDES . 'class-vtd-i18n.php';
require_once VTD_INCLUDES . 'class-vtd-plugin.php';

require_once VTD_MODULES . 'sms/class-vtd-sms.php';
require_once VTD_MODULES . 'auth/class-vtd-auth.php';
require_once VTD_MODULES . 'profile/class-vtd-profile.php';
require_once VTD_MODULES . 'dashboard/class-vtd-dashboard.php';
require_once VTD_MODULES . 'tickets/class-vtd-tickets.php';
require_once VTD_MODULES . 'notifications/class-vtd-notifications.php';
require_once VTD_MODULES . 'polls/class-vtd-polls.php';
require_once VTD_MODULES . 'attachments/class-vtd-attachments.php';
require_once VTD_MODULES . 'banking/class-vtd-banking.php';
require_once VTD_MODULES . 'wallet/class-vtd-wallet.php';
require_once VTD_MODULES . 'comments/class-vtd-comments.php';

if ( is_admin() ) {
	require_once VTD_ADMIN . 'class-vtd-admin.php';
	require_once VTD_ADMIN . 'class-vtd-settings.php';
	require_once VTD_ADMIN . 'class-vtd-list-tables.php';
}

register_activation_hook( __FILE__, array( 'VTD_Install', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'VTD_Install', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'VTD_Plugin', 'instance' ), 5 );
