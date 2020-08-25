<?php

class VIP_Go_Sandbox_Helpers_Command extends WP_CLI_Command {
	/**
	 * Get total size of database for the site in MB
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp vip sh db-size
	 *
	 * @param array $args
	 * @param array $assoc_args
	 * @subcommand db-size
	 */
	public function db_size( $args, $assoc_args ) {
		global $wpdb;
		$report = array_map(
			function( $table ) use ( $wpdb ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				return $wpdb->get_row( "SHOW TABLE STATUS LIKE '$table'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			},
			$wpdb->tables( 'blog' )
		);

		$total_used = array_reduce(
			$report,
			function( $total, $table ) {
				$total['data']  += (int) $table->Data_length; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$total['index'] += (int) $table->Index_length; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				return $total;
			},
			array(
				'data'  => 0,
				'index' => 0,
			)
		);

		$db_size = array_map(
			function( $v ) {
				return $v / pow( 1024, 3 );
			},
			$total_used
		);

		WP_CLI::success(
			sprintf(
				'Total size of the database for %s (blog_id %s) is %s GB. Data: %s GB; Index: %s GB',
				home_url(),
				get_current_blog_id(),
				WP_CLI::colorize( '%g' . round( $db_size['data'] + $db_size['index'], 3 ) . '%n' ),
				WP_CLI::colorize( '%g' . round( $db_size['data'], 3 ) . '%n' ),
				WP_CLI::colorize( '%g' . round( $db_size['index'], 3 ) . '%n' )
			)
		);
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'vip sh', 'VIP_Go_Sandbox_Helpers_Command' );
}
