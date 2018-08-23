<?php
/*
Plugin Name: Debug and Trace
Plugin URI: http://serverpress.com/plugins/debug-trace
Description: Forces WP_DEBUG = true and enables cross-platform/language trace statement in PHP and JavaScript. Use the Trace window for debugging.
Author: Stephen Carnam, Dave Jesch
Version: 2.1.1
Text Domain: debug-trace
Author URI: http://steveorevo.com/
*/

if ( FALSE === stripos( __DIR__, 'ds-plugins' ) ) {
	// detect if not in the ds-plugins folder
	if ( is_admin() )
		add_action( 'admin_notices', 'debug_trace_install_message' );
	return;		// do not initialize the rest of the plugin
}

/**
 * Display admin notification to install plugin in correct directory
 */
function debug_trace_install_message()
{
	if ( 'Darwin' === PHP_OS )
		$correct_dir = '/Applications/XAMPP/ds-plugins/';		// mac directory
	else
		$correct_dir = 'C:\\xampplite\\ds-plugins\\';			// Windows directory

	echo '<div class="notice notice-error">',
		'<p>',
		sprintf( __('<b>Notice:</b> The Debug and Trace plugin needs to be installed in Desktop Server\'s ds-plugins directory.<br/>Please install in %1$sdebug-trace', 'debug-trace' ),
			$correct_dir),
		'</p>',
		'</div>';
}
