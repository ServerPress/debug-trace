<?php
ds_launch_trace();


/**
 * Launch the native Debug/Trace application
 */
function ds_launch_trace()
{
	global $ds_runtime;
	if ( 'Darwin' !== PHP_OS ) {
		// Windows
		$cmd = dirname( $ds_runtime->ds_plugins_dir ) . '/Trace.exe';
	} else {
		// Macintosh
		$cmd = dirname( $ds_runtime->ds_plugins_dir ) . '/Trace';
	}

	if ( !empty( $cmd ) )
		exec( $cmd );

	$ds_runtime->do_action( 'pre_ds_launch_trace' );
	exec( $cmd );
}
