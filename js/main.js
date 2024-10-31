jQuery(document).ready(function($){

	// Make sure sharing function is triggered.
	secaudit_Sharing( jQuery );

	// set defualt variables
	var current     = 0;
	var total_items = Object.keys( wpsec_items ).length;
	var request     = '';

	$.each(wpsec_items, function(i, val) {
		// Build API request URL
		requestURL = secaudit_vulns.ajax_url;

		if ( scan_type == "plugin" ) {
			request =  requestURL + '?type=' + scan_type + '&item=' + val.TextDomain;
		} else {
			request =  requestURL + '?type=' + scan_type + '&item=' + val;
		}
		wpscan_get_vulnerabilities( request, val );
	});


	// ajax local proxy to gather vulnerability list
	function wpscan_get_vulnerabilities( requestURL, val ) {
		// send the request, gather the results
		$.ajax( {
			url:      requestURL,
			type:     'GET',
			dataType: 'json',
			data : {
				action: 'secaudit_vulns',
			},

			success: function(data) {
				current++;                   // Increment Counter
				updateScanCount( current );  // Update Count
				writeVulnData( data, val );  // Write Data to Screen
			},

			error: function() {
				$("#vulndata").append( '<br/>Error requesting infromation about ' + val );
			},
		});
	}

	/**
	 *  write out the vulnerability data to the display.
	 */
	function writeVulnData( data, val ) {
		// check to make sure we have data, bad data (404s or errors) are returned as 0/false
		if ( data ){
			data = JSON.parse(data);

			// Installed information
			var installed_title = val.Title;
			var installed_ver   = val.Version;

			// Scan information
			var title 				  = Object.keys(data);
			var version 			  = data[title]['latest_version'];
			var last_updated 	  = data[title]['last_updated'];
			var latest_version  = data[title]['latest_version'];
			var popular 			  = data[title]['popular'];
			var vulnerabilities = data[title]['vulnerabilities'];
			var vuln_list 		  = '';

			// loop through each vulnerability and display the information
			$.each(vulnerabilities, function() {

				vulntitle = this['title'];
				vulntype  = this['vuln_type'];

				if ( this['fixed_in'] > installed_ver ) {
						vuln_list += '<div class="value-warn">';
						vuln_list += '<h3>This exploit is currently active in your installation</h3>';
						vuln_list += '<p>Update this ' + scan_type + ' to fix this issue</p>';
				} else {
						vuln_list += '<div class="value-okay">';
				}

				vuln_list += vulntype + ': <b>' + vulntitle + '</b>';

				if ( this['references']['url'] ) {
					urls = this['references']['url'];
					$.each(urls, function(){
						// TODO: Better Detail
						vuln_list += '<br/><a href="' + this + '" target="_blank">More Information</a>';
					})
				}

				vuln_list += '</div>';
				vuln_list += '<hr/>';
			});

			output = '<hr>';
			if ( installed_title && installed_ver ) {
				output += '<h3>' + installed_title + ' ' + installed_ver + '</h3>';
			} else if ( title ) {
				output += '<h3>' + title + '</h3>';
			}

			output += vulnerabilities.length + ' vulnerabilities found';

			if ( vulnerabilities.length == 0 ){
				output += '<table class="results value-okay"><tbody>';
			} else {
				output += '<table class="results"><tbody>';
			}

			if ( last_updated ) {
				output += table_row( 'Last Updated',   last_updated );
			}
			if ( latest_version ) {
				output += table_row( 'Latest Version', latest_version );
			}
			if ( popular ) {
				output += table_row( 'Popular', popular );
			}
			if ( vuln_list ){
				output += '<tr><td class="label">Vulnerabilities</td><td class="value">' +vuln_list+ '</td></tr>';
			} else {
				output += '<tr><td class="label">Vulnerabilities</td><td class="value"> None Found! </td></tr>';
			}

			output += '</tbody></table>';
		} else {
			output = '<hr>';

			// if an object was returned, we need to obtain the Plugin Name differently
			if( jQuery.type(val) == 'object'){
				output += '<h3>' + val.Name + '</h3>';
			} else {
				output += '<h3>' + val + '</h3>';
			}

			output += '<table class="results value-notice"><tbody>';
			output += table_row( 'No Data Returned', 'This means there isn\'t a history of the ' + scan_type + ' in the vulnerability database. <br/>The reason may be that the ' + scan_type + ' isn\'t popular, or you\'re using a custom ' + scan_type + '.');
			output += '</tbody></table>';
		}

		$("#vulndata").append(output);
	}

	/**
	 * Keep a count of how many items have been scanned so we can display the progress
	 */
	function updateScanCount( current ) {
		var progress = ( current / total_items ) * 100;

		// Spans if we want to display X of X
		$('span.current').text( current );
		$('span.total').text( total_items );

		// Spans for Percentages
		$('span.percentage').show();
		$('span.percentage').text( Math.round( progress ) + '%' );

		// Update loader to represent percentage
		$('#current-progress').width( progress + "%" );

		// Hide the bar when complete
		if ( progress >= 100 ) {
			$('#progress-bar').hide();
		}
	}

	/**
	 * Formatting function for table rows
	 */
	function table_row( label, value ) {
		if ( value != 'NULL' || value != null || value != 'undefined' || value != undefined ) {
			return '<tr><td class="label">' + label + '</td><td class="value">'+ value +'</td></tr>';
		}
	}

	/**
	 * Sharing tools
	 */
	function secaudit_Sharing( $ ) {

		var sharer = {
			// Initialize the singleton
			init: function() {
				this.buttons = $('.share a');
				if ( this.buttons.length === 0 ) {
					// Abort if no buttons
					return;
				}

				this.buttons.on( 'click', $.proxy( this, 'onClick' ) );
			},

			// Get the url, title, and description of the page
			// Cache the data after the first get
			getPageData: function( e ) {
				if ( !this._data ) {
					this._data = {};
					this._data.title       = 'I\'ve found Security Audit to be a useful plugin for securing my #WordPress website -- check it out!';
					this._data.url         = 'https://wordpress.org/plugins-wp/security-audit/';
					this._data.description = 'Security Audit is a great WordPress plugin to help you find and eliminate security issues from your WordPress site.';
					this._data.target = e;
				}
				return this._data;
			},

			// Event handler for the share buttons
			onClick: function( event ) {
				var service = $(event.target).data('service');
				if ( this[ 'do_' + service ] ) {
					this[ 'do_' + service ]( this.getPageData( event.target ) );
				}
				return false;
			},

			// Handle the Twitter service
			do_twitter: function( data ) {
				var url = 'https://twitter.com/intent/tweet?' + $.param({
					original_referer: document.title,
					text: $(data.target).data('tweet') || data.title,
					url: data.url
				});
				if ( $('.en_social_buttons .en_twitter a').length ) {
					url = $.trim( $('.en_social_buttons .en_twitter a').attr('href') );
				}
				this.popup({
					url: url,
					name: 'twitter_share'
				});
			},

			// Handle the Facebook service
			do_facebook: function( data ) {
				var url = 'https://www.facebook.com/sharer/sharer.php?' + $.param({
					u: data.url
				});
				if ( $('.en_social_buttons .en_facebook a').length ) {
					url = $.trim( $('.en_social_buttons .en_facebook a').attr('href') );
				}
				this.popup({
					url: url,
					name: 'facebook_share'
				});
			},

			// Handle the email service
			do_email: function( data ) {
				var url = 'mailto:?subject=' + data.title + '&body=' + data.description + ': \n' + data.url;
				window.location.href = url.replace('/\+/g',' ');
			},

			// Handle Tumblr
			do_tumblr: function ( data ) {
				var url = 'https://www.tumblr.com/widgets/share/tool?' + $.param({
					canonicalUrl: data.url,
					title: data.title,
					caption: data.caption,
					posttype: 'link'
				});
				this.popup({
					url: url,
					name: 'tumblr_share'
				});
			},

			// Handle the Google+ service
			do_googleplus: function( data ) {
				var url = 'https://plus.google.com/share?' + $.param({
					url: data.url
				});
				this.popup({
					url: url,
					name: 'googleplus_share'
				});
			},

			do_gplus: function ( data ) {
				this.do_googleplus( data );
			},

			// Handle the LinkedIn service
			do_linkedin: function( data ) {
				var url = 'http://www.linkedin.com/shareArticle?' + $.param({
					mini: 'true',
					url: data.url,
					title: data.title,
					summary: data.description
					// source: data.siteName
				});
				this.popup({
					url: url,
					name: 'linkedin_share'
				});
			},

			// Create and open a popup
			popup: function( data ) {
				if ( !data.url ) {
					return;
				}

				$.extend( data, {
					name: '_blank',
					height: 600,
					width: 845,
					menubar: 'no',
					status: 'no',
					toolbar: 'no',
					resizable: 'yes',
					left: Math.floor(screen.width/2 - 845/2),
					top: Math.floor(screen.height/2 - 600/2)
				});

				var i,
					specNames = 'height width menubar status toolbar resizable left top'.split( ' ' ),
					specs = [];

				for( i = 0; i < specNames.length; ++i ) {
					specs.push( specNames[i] + '=' + data[specNames[i]] );
				}
				return window.open( data.url, data.name, specs.join(',') );
			}
		};

		sharer.init();
	}
});
