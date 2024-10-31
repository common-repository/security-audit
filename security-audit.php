<?php
/**
 * Plugin Name: Security Audit
 * Plugin URI: https://cornershopcreative.com
 * Description: Security Audit for WordPress including PHPSecInfo and WPScan
 * Version: 1.0
 * Author: Cornershop Creative
 * Author URI: https://cornershopcreative.com
 */

defined( 'ABSPATH' ) || die( 'Forbidden' );

// Include the phpsecinfo library.
require_once( 'inc/server-phpsecinfo.php' );

// Include the secaudit class.
require_once( 'inc/class-securityaudit.php' );

// PhpSecInfo class.
require_once( 'PhpSecInfo/PhpSecInfo.php' );

/**
 * Create a custom page in the admin toolbar.
 */
function secaudit_custom_page() {
	add_submenu_page(
		'tools.php',
		'Security Audit',
		'Security Audit',
		'manage_options',
		'security-audit',
		'secaudit_tab_content'
	);
}
add_action( 'admin_menu', 'secaudit_custom_page' );

/**
 * Generate the administration tabs
 * - Homepage (PHPSec Info)
 * - Plugin (WPSecurityAudit Plugin Vulnerability Scanner)
 * - Theme  (WPSecurityAudit Theme Vulnerability Scanner)
 * - Core   (WPSecurityAudit Core Vulnerability Scanner)
 *
 * @param string $current Current tab as found in secaudit_tab_content().
 */
function secaudit_admin_tabs( $current = 'homepage' ) {
	?><h1>Security Audit</h1><?php
	$tabs = array(
		'homepage' => 'PHPSec Info',
		'plugin' => 'Plugin Scanner',
		'theme' => 'Theme Scanner',
		'core' => 'WordPress Core Scanner',
								);
	?>

	<aside class="secaudit-column-2">
		<div class="secaudit-box">
			<h2>Like Security Audit?</h2>
			<ul>
				<li class="share"><a href="#" data-service="facebook">Share it on Facebook »</a></li>
				<li class="share"><a href="#" data-service="twitter">Tweet it »</a></li>
				<li><a href="https://wordpress.org/plugins/security-audit/reviews/#new-post" target="_blank">Review it on WordPress.org »</a></li>
			</ul>
		</div>
	</aside>
	<div class="secaudit-column-1">
		<div class="nav-tab-wrapper">
		<?php
		foreach ( $tabs as $tab => $name ) {
			$class = ( $tab === $current ) ? ' nav-tab-active' : '';
			echo '<a class="nav-tab' . esc_attr( $class ) . '" href="?page=security-audit&tab=' . esc_attr( $tab ) . '">' . esc_html( $name ) . '</a>';
		}
		?>
		</div>
	</div>
	<?php
}

/**
 * Callback to populate page content for custom admin toolbar page.
 */
function secaudit_tab_content() {
	echo '<div class="wrap">';

	// Setup and format tabs.
	$tab = ( isset( $_GET['tab'] ) ) ? sanitize_html_class( wp_unslash( $_GET['tab'] ) ) : 'homepage';
	secaudit_admin_tabs( $tab );

	// Homepage Runs phpsecinfo by default,
	// other pages will utilize the tabs $_GET variable to decide which WPSecurityAudit scan to run.
	if ( 'homepage' === $tab ) {
		run_phpsec_info();
	} else {
		$scan = new WPSecurityAudit( $tab );
	}
	echo '</div>';
}

/**
 * Add our plugin's CSS & JS, but only on our page.
 *
 * @param string $suffix Expected suffix provided from WordPress.
 */
function secaudit_enqueue( $suffix ) {
	if ( 'tools_page_security-audit' === $suffix ) {
		// CSS Styles.
		wp_enqueue_style( 'wp-phpsecinfo', plugins_url( 'css/wp-secaudit.css', __FILE__ ) );

		// JS Ajax.
		wp_enqueue_script( 'secaudit-mainajax-js',   plugins_url( 'js/main.js', __FILE__ ), 'jQuery', '0.2', true );

		// js properties accessed with ajax-phpsec.ajax_url and ajax-phpsec.secuaudit_settings.
		wp_localize_script( 'secaudit-mainajax-js', 'secaudit_vulns', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}
}
add_action( 'admin_enqueue_scripts', 'secaudit_enqueue' );

/**
 * Helper proxy to localize wpvulndb APIs cross-origin request
 */
function secaudit_ajax_request_audit( $type, $item = false ){
	$type = isset( $_GET['type'] ) ? ( $_GET['type'] ) : '';
	$item = isset( $_GET['item'] ) ? ( $_GET['item'] ) : '';

	$api_url = '';

	if ( 'core' === $type ) {
		$api_url = 'https://wpvulndb.com/api/v2/wordpresses/';
	} elseif ( 'theme' === $type ) {
		$api_url = 'https://wpvulndb.com/api/v2/themes/';
	} else {
		$api_url = 'https://wpvulndb.com/api/v2/plugins/';
	}

	if ( $api_url ) {
		$wpsec_vuln_data = false;
		$api_url = esc_url( $api_url . $item );
		$request = wp_remote_get( $api_url );
		$response_code = wp_remote_retrieve_response_code( $request );

		if ( is_wp_error( $request ) || 404 === $response_code ) {
			return false;
		} else {
			$wpsec_vuln_data = wp_remote_retrieve_body( $request );
			// Check for error.
			if ( is_wp_error( $wpsec_vuln_data ) ) {
				return false;
			}
		}

		if ( $wpsec_vuln_data ) {
			header( 'Content-Type: application/json' );
			echo wp_json_encode( $wpsec_vuln_data );
			die();
		} else {
			echo false;
			die();
		}
	}

}
add_action( 'wp_ajax_secaudit_vulns', 'secaudit_ajax_request_audit' );

/**
 * Secaudit Globals
 * - Set up globals here that we'll utilize thorughout.
 */
function secaudit_globals() {
	global $wpdb;
	$secaudit_settings = '';
	wp_die();
}
add_action( 'wp_ajax_secaudit_globals', 'secaudit_globals' );
