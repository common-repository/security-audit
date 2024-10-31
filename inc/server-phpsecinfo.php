<?php
/**
 * PHPSecInfo
 * Formatted content from default PHPSecInfo.php
 */
function run_phpsec_info() {
	echo '<h2>' . __( 'PHP Security Audit', 'security-audit' ) . '</h2>';

	echo '<p>These checks are performed on the configuration of PHP your server is running. Functionality for this scan is provided by <a href="' . esc_url( PHPSECINFO_URL ) . '" target="_blank">PHPSecInfo v' . __( PHPSECINFO_VERSION ) . '</a>.</p>';
	echo "<p>To address these issues, you'll need to edit your .htaccess file to change some PHP settings and/or contact your sysadmin/host and request that they update the PHP configuration accordingly.</p>";

	// Instantiate the class.
	$psi = new PhpSecInfo();

	if ( $psi ) {
		// Load and run all tests.
		$psi->loadAndRun();

		// Show the Stats.
		$psi->_outputRenderStatsTable();
		echo '<hr/>';

		// Display any tests that weren't run.
		$psi->_outputRenderNotRunTable();
		echo '<hr/>';

		// Display test results.
		echo '<h2>Test Results</h2>';

		// Create tables with information.
		foreach ( $psi->test_results as $group_name => $group_results ) {
			$psi->_outputRenderTable( $group_name, $group_results );
			echo '<hr/>';
		}
	} else {
		echo 'An error occurred running phpsecinfo';
	} // End if().
}
