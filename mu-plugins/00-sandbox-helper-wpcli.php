<?php

if ( ! class_exists( 'WP_CLI_Command' ) ) {
	return;
}
class VIP_Go_Sandbox_Helpers_Command extends WP_CLI_Command {
	/**
	 * Gets size of database tables for the current site.
	 *
	 * ## OPTIONS
	 *
	 * [--raw]
	 * : Outputs full size in bytes instead of human readable sizes.
	 *
	 * [--order_by=<Total>]
	 * : Allows custom ordering of the data.
	 * ---
	 * default: Total
	 * options:
	 *   - Table
	 *   - Data Size
	 *   - Index Size
	 *   - Total
	 * ---
	 *
	 * [--order=<asc>]
	 * : Allows custom ordering direction of the data.
	 * ---
	 * default: asc
	 * options:
	 *   - asc
	 *   - desc
	 * ---
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * @subcommand db-size
	 */
	public function db_size( $args, $assoc_args ) {
		global $wpdb;

		$output   = array();
		$db_size  = array();
		$order_by = WP_CLI\Utils\get_flag_value( $assoc_args, 'order_by', 'Total' );
		$order    = WP_CLI\Utils\get_flag_value( $assoc_args, 'order', 'asc' );
		$format   = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );
		$raw      = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'raw', false );

		// Fetch list of tables from database.
		$tables = array_map(
			function( $val ) {
				return $val[0];
			},
			$wpdb->get_results( 'SHOW TABLES;', ARRAY_N ) // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		);

		// Fetch table information from database.
		$report = array_map(
			function( $table ) use ( $wpdb ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				return $wpdb->get_row( "SHOW TABLE STATUS LIKE '$table'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			},
			$tables
		);

		foreach ( $report as $table ) {
			// Keep a running total of sizes.
			$db_size['data']  += $table->Data_length; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$db_size['index'] += $table->Index_length; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Format output for WP-CLI's format_items function.
			$output[] = array(
				'Table'      => $table->Name, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'Data Size'  => $table->Data_length, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'Index Size' => $table->Index_length, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'Total'      => $table->Data_length + $table->Index_length, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			);
		}

		// Sort table data.
		self::sort_table_by( $order_by, $output, $order );

		if ( ! $raw ) {
			// Make data human readable.
			foreach ( array_keys( $output ) as $key ) {
				$output[ $key ]['Data Size']  = self::human_readable_bytes( $output[ $key ]['Data Size'] );
				$output[ $key ]['Index Size'] = self::human_readable_bytes( $output[ $key ]['Index Size'] );
				$output[ $key ]['Total']      = self::human_readable_bytes( $output[ $key ]['Total'] );
			}
		}

		// Output data.
		WP_CLI\Utils\format_items( $format, $output, array( 'Table', 'Data Size', 'Index Size', 'Total' ) );

		// Output totals if we're not piping somewhere.
		if ( ! WP_CLI\Utils\isPiped() ) {
			WP_CLI::success(
				sprintf(
					'Total size of the database for %s is %s. Data: %s; Index: %s',
					home_url(),
					WP_CLI::colorize( '%g' . self::human_readable_bytes( $db_size['data'] + $db_size['index'] ) . '%n' ),
					WP_CLI::colorize( '%g' . self::human_readable_bytes( $db_size['data'] ) . '%n' ),
					WP_CLI::colorize( '%g' . self::human_readable_bytes( $db_size['index'] ) . '%n' )
				)
			);
		}
	}

	/**
	 * Runs a SQL query against the site database.
	 *
	 * ## OPTIONS
	 *
	 * <query>
	 * : SQL Query to run.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * [--dry-run=<true>]
	 * : Performa a dry run
	 *
	 * [--force]
	 * : Force running the SQL command if it contains potential modifications
	 *
	 * @subcommand sql
	 */
	public function sql( $args, $assoc_args ) {
		global $wpdb;

		$sql     = $args[0];
		$format  = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );
		$dry_run = WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', true );
		$force   = WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

		// Just some precautions.
		$deny_words = array(
			'update',
			'delete',
			'drop',
			'insert',
			'create',
			'alter',
			'rename',
			'truncate',
			'replace',
		);

		if ( ! $force ) {
			foreach ( $deny_words as $deny_word ) {
				if ( false !== stripos( $sql, $deny_word ) ) {
					WP_CLI::error( 'Please do not modify the database with this command.  Run with `--force` if this is a false positive.' );
				}
			}
		}

		if ( 'false' !== $dry_run ) {
			WP_CLI::log( WP_CLI::colorize( '%gDRY-RUN%n: `EXPLAIN` of the query is below: (https://mariadb.com/kb/en/explain/)' ) );
			$sql = 'EXPLAIN ' . $sql;
		}

		// Fetch results from database.
		$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB

		// Output data.
		WP_CLI\Utils\format_items( $format, $results, array_keys( $results[0] ) );
	}

	/**
	 * Outputs a human readable string for data.
	 *
	 * @param int $bytes The size of the data in bytes.
	 * @param int $precision The decimal precision requested.  Default to auto.
	 */
	public function human_readable_bytes( $bytes, $precision = null ) {
		// Taken from https://gist.github.com/liunian/9338301#gistcomment-3293173 Thanks!
		$byte_units     = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
		$byte_precision = array( 0, 0, 1, 2, 2, 3, 3, 4, 4 ); // Number of decimal places to round to for each unit.
		$byte_next      = 1024;
		$byte_unite_c   = count( $byte_units );

		for ( $i = 0; ( $bytes / $byte_next ) >= 0.9 && $i < $byte_unite_c; $i++ ) {
			$bytes /= $byte_next;
		}

		return round( $bytes, is_null( $precision ) ? $byte_precision[ $i ] : $precision ) . $byte_units[ $i ];
	}

	/**
	 * Outputs a human readable string for data.
	 *
	 * @param string $field The field to order by.
	 * @param array  $array The decimal precision requested.  Default to auto.
	 * @param string $direction The direction to sort. Ascending ('asc') or descending ('desc').
	 */
	public function sort_table_by( $field, &$array, $direction ) {
		// Taken from https://joshtronic.com/2013/09/23/sorting-associative-array-specific-key/ Thanks!
		usort(
			$array,
			function ( $a, $b ) use ( $field, $direction ) {
				$a = $a[ $field ];
				$b = $b[ $field ];

				if ( $a === $b ) {
					return 0;
				}

				switch ( $direction ) {
					case 'asc':
						return ( $a < $b ) ? -1 : 1;
					case 'desc':
						return ( $a > $b ) ? -1 : 1;
					default:
						return 0;
				}
			}
		);
		return true;
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'vip sh', 'VIP_Go_Sandbox_Helpers_Command' );
}
