<?php
ini_set( 'xdebug.cli_color', 1 );

if ( ! function_exists( 'vip_dump' ) ) {
	function vip_dump( $var = null ) {
		$prefix = '';
		if ( $microtime ) {
			$prefix = sprintf( '[%s] ', microtime() );
		}
		ob_start();
		var_dump( $var );
		$out1 = ob_get_contents();
		ob_end_clean();
		error_log( $out1 );
	}
}


