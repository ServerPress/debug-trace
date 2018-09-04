<?php
ds_launch_trace();


/**
 * Launch the native Debug/Trace application
 */
function ds_launch_trace()
{
	if ( ! defined( 'DS_OS_DARWIN' ) ) {
		// OS-specific defines
		define( 'DS_OS_DARWIN', 'Darwin' === PHP_OS );
		define( 'DS_OS_WINDOWS', !DS_OS_DARWIN && FALSE !== strcasecmp('win', PHP_OS ) );
		define( 'DS_OS_LINUX', FALSE === DS_OS_DARWIN && FALSE === DS_OS_WINDOWS );
	}

	$ds_dir = getenv( 'DS_INSTALL' );
	if ( empty( $ds_dir ) ) {
		if ( DS_OS_DARWIN ) {
			$ds_dir = '/Applications/XAMPP/';
		} else if ( DS_OS_WINDOWS ) {
			$ds_dir = 'c:\\xampplite\\';
		}
	}

	if ( DS_OS_WINDOWS ) {
		// Windows
		$cmd = $ds_dir . 'Trace.exe';
	} else if ( DS_OS_DARWIN ) {
		// Macintosh
		$cmd = 'open ' . $ds_dir . 'Trace.app';	
	}
trace('launch: ' . var_export($cmd, TRUE));

	if ( !empty( $cmd ) ) {
		global $ds_runtime;
		$ds_runtime->do_action( 'pre_ds_launch_trace' );
		shell_exec( $cmd );
	}
}
