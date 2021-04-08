<?php
ini_set( 'xdebug.cli_color', 1 );
error_reporting( E_ERROR | E_WARNING | E_PARSE ); // Disable notices in logs.

foreach( array(
	__DIR__ . '/sandbox-wp-debugger/sandbox-wp-debugger.php',
	__DIR__ . '/wp-toolbar/wp-toolbar.php',
) as $mu_plugin_file ) {
	if ( file_exists( $mu_plugin_file ) ) {
		require_once $mu_plugin_file;
	} else {
		error_log( 'SANDBOX-HELPER: Missing File ' . $mu_plugin_file );
	}
}

if ( ! function_exists( 'vip_dump' ) ) {
	function vip_dump( $var = null ) {
		$old_setting = ini_get( 'html_errors' );
		ini_set( 'html_errors', false );
		ini_set( 'xdebug.cli_color', 2 );

		ob_start();
		var_dump( $var );
		$out1 = ob_get_contents();
		ob_end_clean();
		error_log( $out1 );

		ini_set( 'xdebug.cli_color', 1 );
		ini_set( 'html_errors', $old_setting );
	}
}

// Add Sandbox WP Debugger support for internal REST API requests.
add_action( 'rest_pre_dispatch', function( $result, $server, $request ) {
	if ( function_exists( 'swpd_log' ) && defined( 'SWPD_REST_DEBUG' ) ) {
		swpd_log( 'rest_do_request', $request->get_route(), $request->get_params(), [], true );
	}
}, 10, 3 );
