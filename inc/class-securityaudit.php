<?php
class WPSecurityAudit {

	function __construct( $type ) {
		// Get WordPress core version global.
		global $wp_version;

		// Gather information from WordPress.
		$this->wp_version = $wp_version;
		$this->type 	    = $type;

		// Gather information about user session.
		$items   = $this->get_items_by_type( $type );
		$this->populate_secaudit_globals( $items );

		// heading based upon type.
		$this->heading    = $this->base_markup( $type );
	}

	 /**
	  * Populate Secaudit Globals
	  * - Gather PHP information that we want to relay back to javascript.
	  */
	public function populate_secaudit_globals( $items ) {
		$secaudit_settings['wpsec_type'] = $this->type;
		$secaudit_settings['wpsec_items'] = $items;

		echo '<script>';
		echo 'var scan_type   = "' . esc_js( $this->type ) . '";';
		echo 'var wpsec_items = ' . wp_json_encode( $items );
		echo '</script>';
	}

	/**
	 * Markup
	 * - Displays the type of scan running and any relevant details before scan is run.
	 * - Sets up base markup that javascript will utilize later to populate.
	 */
	private function base_markup( $type ) {
		echo '<h2>WordPress ' . esc_html( ucfirst( $this->type ) ) . ' Scanner</h2>';
		if ( 'core' === $this->type ) {
			echo 'The WordPress Core  scanner will scan your currently installed WordPress version against a vulnerability database to see if there are any open issues.';
		} else {
			echo 'The ' . esc_html( ucfirst( $this->type ) ) . ' scanner will gather all the ' . esc_html( ucfirst( $this->type ) ) . 's you have installed and check against a vulnerability database to see if there are any open issues.';
		}

		echo '<ul><li>Issues marked with Green are fixed in your current version.</li>' .
		'<li>Issues marked with Yellow may require more investigation to determine if it needs to be addressed.</li>' .
		'<li>Issues marked with Red need to be updated immediately to secure your WordPress site</li></ul>';
		echo '<p><small>Vulnerability data for this scan is provided by <a href="https://wpvulndb.com/" target="_blank">https://wpvulndb.com/</a>.</small></p>';
		?>

		<div id="progress-bar">
			<div id="progress-text">
				<span class="current"></span>
				<span class="details"> of </span>
				<span class="total"></span>

				<span class="percentage"></span>
			</div>

			<div id="current-progress">
			</div>
		</div>

		<div id="vulndata">
		</div>
		<?php
	}

	/**
	 * Get Items by type
	 * - Gather Installed Items by type of scan selected.
	 */
	private function get_items_by_type( $type ) {
		if ( 'core' === $type ) {
			global $wp_version;
			return (object) str_replace( '.', '', $wp_version );
		} elseif ( 'theme' === $type ) {
				$themes = wp_get_themes( );
				return array_keys( $themes );
		} else {
			return get_plugins();
		}
	}
}
