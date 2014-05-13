<?php

/**
 * Log tables for Authentication Module
 *
 * @package    iThemes-Security
 * @subpackage Authentication
 * @since      4.0
 */
final class ITSEC_Logger_All_Logs extends ITSEC_WP_List_Table {

	function __construct() {

		parent::__construct(
		      array(
			      'singular' => 'itsec_raw_log_item',
			      'plural'   => 'itsec_raw_log_items',
			      'ajax'     => true
		      )
		);

	}

	/**
	 * Define type column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_time( $item ) {

		return $item['time'];

	}

	/**
	 * Define function column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_function( $item ) {

		return $item['function'];

	}

	/**
	 * Define priority column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_priority( $item ) {

		return $item['priority'];

	}

	/**
	 * Define host column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_host( $item ) {

		$r = array();
		if ( ! is_array( $item['host'] ) ) {
			$item['host'] = array( $item['host'] );
		}
		foreach ( $item['host'] as $host ) {
			$r[] = '<a href="http://ip-adress.com/ip_tracer/' . filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) . '" target="_blank">' . filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) . '</a>';
		}
		$return = implode( '<br />', $r );

		return $return;

	}

	/**
	 * Define username column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_user( $item ) {

		if ( $item['user_id'] != 0 ) {
			return '<a href="/wp-admin/user-edit.php?user_id=' . $item['user_id'] . '" target="_blank">' . $item['user'] . '</a>';
		} else {
			return $item['user'];
		}

	}

	/**
	 * Define url column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_url( $item ) {

		return $item['url'];

	}

	/**
	 * Define referrer column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_referrer( $item ) {

		return $item['referrer'];

	}

	/**
	 * Define data column
	 *
	 * @param array $item array of row data
	 *
	 * @return string formatted output
	 *
	 **/
	function column_data( $item ) {

		global $itsec_logger;

		$raw_data = maybe_unserialize( $item['data'] );

		if ( is_array( $raw_data ) && sizeof( $raw_data ) > 0 ) {

			$data = $itsec_logger->print_array( $raw_data, true );

		} elseif ( ! is_array( $raw_data ) ) {

			$data = sanitize_text_field( $raw_data );

		} else {

			$data = '';

		}

		if ( strlen( $data ) > 1 ) {

			$content = '<div class="itsec-all-log-dialog" id="itsec-log-all-row-' . $item['id'] . '" style="display:none;">';
			$content .= $data;
			$content .= '</div>';

			$content .= '<a href="itsec-log-all-row-' . $item['id'] . '" class="dialog">' . __( 'Details', 'it-l10n-better-wp-security' ) . '</a>';

			return $content;

		} else {

			return '';

		}

	}

	/**
	 * Define Columns
	 *
	 * @return array array of column titles
	 */
	public function get_columns() {

		return array(
			'function' => __( 'Function', 'it-l10n-better-wp-security' ),
			'priority' => __( 'Priority', 'it-l10n-better-wp-security' ),
			'time'     => __( 'Time', 'it-l10n-better-wp-security' ),
			'host'     => __( 'Host', 'it-l10n-better-wp-security' ),
			'user'     => __( 'User', 'it-l10n-better-wp-security' ),
			'url'      => __( 'URL', 'it-l10n-better-wp-security' ),
			'referrer' => __( 'Referrer', 'it-l10n-better-wp-security' ),
			'data'     => __( 'Data', 'it-l10n-better-wp-security' ),
		);

	}

	/**
	 * Prepare data for table
	 *
	 * @return void
	 */
	public function prepare_items() {

		global $itsec_logger;

		$columns               = $this->get_columns();
		$hidden                = array();
		$this->_column_headers = array( $columns, $hidden, false );

		$items = $itsec_logger->get_events( 'all' );

		$table_data = array();

		$count = 0;

		foreach ( $items as $item ) { //loop through and group 404s

			$table_data[$count]['id']       = $count;
			$table_data[$count]['function'] = sanitize_text_field( $item['log_function'] );
			$table_data[$count]['priority'] = sanitize_text_field( $item['log_priority'] );
			$table_data[$count]['time']     = sanitize_text_field( $item['log_date'] );
			$table_data[$count]['host']     = sanitize_text_field( $item['log_host'] );
			$table_data[$count]['user']     = sanitize_text_field( $item['log_username'] );
			$table_data[$count]['user_id']  = sanitize_text_field( $item['log_user'] );
			$table_data[$count]['url']      = sanitize_text_field( $item['log_url'] );
			$table_data[$count]['referrer'] = sanitize_text_field( $item['log_referrer'] );
			$table_data[$count]['data']     = sanitize_text_field( $item['log_data'] );

			$count ++;

		}

		usort( $table_data, array( $this, 'sortrows' ) );

		$per_page     = 50; //20 items per page
		$current_page = $this->get_pagenum();
		$total_items  = count( $table_data );

		$table_data = array_slice( $table_data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $table_data;

		$this->set_pagination_args(
		     array(
			     'total_items' => $total_items,
			     'per_page'    => $per_page,
			     'total_pages' => ceil( $total_items / $per_page )
		     )
		);

	}

	/**
	 * Sorts rows by count in descending order
	 *
	 * @param array $a first array to compare
	 * @param array $b second array to compare
	 *
	 * @return int comparison result
	 */
	function sortrows( $a, $b ) {

		// If no sort, default to count
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? esc_attr( $_GET['orderby'] ) : 'time';

		// If no order, default to desc
		$order = ( ! empty( $_GET['order'] ) ) ? esc_attr( $_GET['order'] ) : 'desc';

		// Determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );

		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : - $result;

	}

}