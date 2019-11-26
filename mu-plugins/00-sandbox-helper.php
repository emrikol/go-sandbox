<?php
ini_set( 'xdebug.cli_color', 1 );
error_reporting(E_ALL & ~E_NOTICE);

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
