<?php
// Bind early and suppress initial redefine notice, gets set back to E_ALL in WP's load.php
error_reporting( E_ALL & ~E_NOTICE );
if ( 'cli' !== PHP_SAPI && isset($_REQUEST["XDEBUG_SESSION_START"]) === FALSE) {
	define( 'WP_DEBUG', TRUE );
}

// Proxy our message over to trace window/avoid cross-domain trust issues
if ( isset( $_GET['m']) ) {
	trace( $_GET['m'], TRUE );
}

// Our native trace function for PHP
function trace( $msg, $j = FALSE )
{
	if ( ! is_string( $msg ) && FALSE === $j ) {
		$msg = '(' . gettype( $msg ) . ') ' . var_export( $msg, TRUE );
	} else {
		if ( FALSE === $j ) {
			$msg = '(' . gettype( $msg ) . ') ' . $msg;
		}
	}
	$h = @fopen( 'http://127.0.0.1:8189/trace?m=' . substr( rawurlencode( $msg ), 0, 2000 ), 'r' );
	if ( FALSE !== $h ) {
		fclose( $h );
	}
}

// Brute force hook wp_head event to code inject our JavaScript trace function.
global $wp_filter;
$wp_filter['login_enqueue_scripts'][0]['ds_js_trace1'] = array( 'function' => 'ds_js_trace', 'accepted_args' => 0 );
$wp_filter['admin_enqueue_scripts'][0]['ds_js_trace2'] = array( 'function' => 'ds_js_trace', 'accepted_args' => 0 );
$wp_filter['wp_head'][0]['ds_js_trace3'] = array( 'function' => 'ds_js_trace', 'accepted_args' => 0 );

// Do it for our localhost development too
global $ds_runtime;
$ds_runtime->add_action( 'append_tools_menu', 'ds_append_menu' );
function ds_append_menu()
{
	echo '<li><a href="#" data-ref="http://localhost/ds-plugins/debug-trace/ds-launch-trace.php" class="ds-trace-menu">Start Debug/Trace Window</a></li>';
}

/**
 * Callback for including jQuery in header #5
 */
$ds_runtime->add_action( 'ds_head', 'ds_trace_head' );
function ds_trace_head()
{
	echo '<script src="http://localhost/js/jquery.min.js" data-source="ds-debug"></script>';
}

$ds_runtime->add_action( 'ds_footer', 'ds_trace_footer' );
function ds_trace_footer()
{
?>
<script type="text/javascript">
(function($) {
	$('a.ds-trace-menu').on('click', function(e) {
		e.preventDefault();
		$.post($(this).attr('data-ref'));
	});
})(jQuery);
</script>
<?php
}


$ds_runtime->add_action( 'ds_head', 'ds_js_trace' );
function ds_js_trace()
{
	?>
	<script>
		function trace(msg) {
			function var_dump(arr, level) {
				var dumped_text = "";
				if (!level)
					level = 0;

				//The padding given at the beginning of the line.
				var level_padding = "";
				for ( var j = 0; j < level + 1; j++ )
					level_padding += "    ";

				if ( 'object' === typeof(arr) ) { //Array/Hashes/Objects
					for ( var item in arr ) {
						var value = arr[item];

						if ( 'object' === typeof(value) ) { //If it is an array,
							dumped_text += level_padding + "'" + item + "' ...\n";
							dumped_text += var_dump(value, level + 1);
						} else {
							dumped_text += level_padding + "'" + item + "' => " + "(" + typeof(value) + ") \"" + value + "\"\n";
						}
					}
				} else { //Stings/Chars/Numbers etc.
					dumped_text = "(" + typeof(arr) + ") " + arr;
					return dumped_text;
				}
				if ( 0 === level ) {
					return '(object)' + String.fromCharCode(10) + dumped_text;
				} else {
					return dumped_text;
				}
			}
			trace.trace_queue.push(var_dump(msg));
		}
		trace.trace_queue = []; // Don't pollute global namespace

		// Try to keep order of operations by transmitting via queue
		setInterval(function() {
			if (0 === trace.trace_queue.length )
				return;
			var msg = trace.trace_queue.shift();

			// Transmit message via XHR
			var xmlhttp;
			if ( window.XMLHttpRequest ) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			} else {
				// code for IE6, IE5
				xmlhttp = new ActiveXObject( "Microsoft.XMLHTTP" );
			}
			var addy = window.location.toString();
			var url = trace.getLeftMost( addy, "//" ) + "//";
			url += trace.getLeftMost( trace.delLeftMost( addy + "/", "//"), "/") + "/ds-plugins/debug-trace/debug-trace.php";
			xmlhttp.open( "GET", url + "?m=" + encodeURIComponent( msg.toString().substring( 0, 2000 ) ), true );
			xmlhttp.send();
		}, 50);

		// Utility parsing functions in trace namespace
		trace.delLeftMost = function( sSource, sFind ) {
			for ( var i = 0; i < sSource.length; i = i + 1 ) {
				var f = sSource.indexOf(sFind, i);
				if ( -1 !== f ) {
					return sSource.substr( f + sFind.length, sSource.length );
					break;
				}
			}
			return sSource;
		};
		trace.getLeftMost = function( sSource, sFind ) {
			for ( var i = 0; i < sSource.length; i = i + 1 ) {
				var f = sSource.indexOf( sFind, i );
				if ( -1 !== f ) {
					return sSource.substr( 0, f );
					break;
				}
			}
			return sSource;
		};
	</script>
<?php
}
