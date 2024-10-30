<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\AbandonedCartOverview;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Automation_Record_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Abandoned_Cart_Records_List
 *
 * @package CleverReach\WooCommerce\ViewModel\AbandonedCartOverview
 */
class Abandoned_Cart_Records_List extends WP_List_Table {


	/**
	 * Automation Record Service
	 *
	 * @var Automation_Record_Service
	 */
	private $automation_record_service;

	/** Class constructor */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Abandoned Cart Record', 'cleverreach-wc' ),
				'plural'   => __( 'Abandoned Cart Records', 'cleverreach-wc' ),
				'ajax'     => true,
			)
		);
	}

	/**
	 * Retrieve AC Records data from the database
	 *
	 * @param int $per_page Number of items per page.
	 * @param int $page_number Page number.
	 *
	 * @return AutomationRecord[]
	 */
	public function get_ac_records( $per_page = 5, $page_number = 1 ) {
		$order_by = HTTP_Helper::get_param( 'orderby' );
		$order    = HTTP_Helper::get_param( 'order' );

		return $this->get_automation_record_service()->get_records(
			$this->get_query_filters(),
			$per_page,
			$page_number,
			$order_by,
			$order
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @param mixed[]|null $filters Query filters.
	 *
	 * @return int
	 */
	public function record_count( $filters ) {
		$filters = null !== $filters ? $filters : $this->get_query_filters();

		return $this->get_automation_record_service()->count( $filters );
	}

	/**
	 * Text displayed when no AC Record data is available
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'There are no stored abandoned cart records', 'cleverreach-wc' );
	}

	/**
	 * Method for scheduled time column.
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_scheduled_time( $item ) {
		$offset         = get_option( 'gmt_offset' );
		$offset_string  = (float) $offset > 0 ? '+' . $offset : (string) $offset;
		$scheduled_time = $item->getScheduledTime();

		if ( null !== $scheduled_time ) {
			$scheduled_time = $scheduled_time->modify( "{$offset_string} hour" )->format( 'F j, Y H:i a' );
		} else {
			$scheduled_time = '-';
		}

		return '<div>' . esc_html( $scheduled_time ) . '</div>';
	}

	/**
	 * Method for sent time column.
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_sent_time( $item ) {
		$offset        = get_option( 'gmt_offset' );
		$offset_string = (float) $offset > 0 ? '+' . $offset : (string) $offset;
		$sent_time     = $item->getSentTime();

		if ( null !== $sent_time ) {
			$sent_time = $sent_time->modify( "{$offset_string} hour" )->format( 'F j, Y H:i a' );
		} else {
			$sent_time = '-';
		}

		return '<div>' . esc_html( $sent_time ) . '</div>';
	}

	/**
	 * Method for total column.
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_total( $item ) {
		$total = $item->getAmount();

		$total = $total ? get_woocommerce_currency_symbol() . $total : '-';

		return '<div>' . esc_html( $total ) . '</div>';
	}

	/**
	 * Method for email column.
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_customer_email( $item ) {
		return '<div>' . esc_html( $item->getEmail() ) . '</div>';
	}

	/**
	 * Method for error message column.
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_error_message( $item ) {
		$error_message = $item->getErrorMessage() ? $item->getErrorMessage() : '-';

		return '<div>' . esc_html( $error_message ) . '</div>';
	}

	/**
	 * Method for recovery status column.
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_recovery_status( $item ) {
		$recovery_status = $item->getIsRecovered();

		if ( ! $recovery_status ) {
			return '<mark class="order-status status-canceled" title="' . esc_attr( __( 'Not recovered', 'cleverreach-wc' ) ) . '">
						<span>
						' . esc_html( __( 'Not recovered', 'cleverreach-wc' ) ) . '</span>
					</mark>';
		}

		return '<mark class="order-status status-processing" title="' . esc_attr( __( 'Recovered', 'cleverreach-wc' ) ) . '">
					<span>
					' . esc_html( __( 'Recovered', 'cleverreach-wc' ) ) . '</span>
				</mark>';
	}

	/**
	 * Method for recovery email status column.
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_recovery_email_status( $item ) {
		switch ( $item->getStatus() ) {
			case RecoveryEmailStatus::SENT:
				return '<mark class="order-status status-processing" title="' . esc_attr( __( 'Sent', 'cleverreach-wc' ) ) . '">
							<span>
							' . esc_html( __( 'Sent', 'cleverreach-wc' ) ) . '</span>
						</mark>';
			case RecoveryEmailStatus::PENDING:
				return '<mark class="order-status status-on-hold" title="' . esc_attr( __( 'Pending', 'cleverreach-wc' ) ) . '">
							<span>
							' . esc_html( __( 'Pending', 'cleverreach-wc' ) ) . '</span>
						</mark>';
			case RecoveryEmailStatus::SENDING:
				return '<mark class="order-status status-completed" title="' . esc_attr( __( 'Sending', 'cleverreach-wc' ) ) . '">
							<span>
							' . esc_html( __( 'Sending', 'cleverreach-wc' ) ) . '</span>
						</mark>';
			case RecoveryEmailStatus::NOT_SENT:
				return '<mark class="order-status status-canceled" title="' . esc_attr( __( 'Not sent', 'cleverreach-wc' ) ) . '">
							<span>
							' . esc_html( __( 'Not sent', 'cleverreach-wc' ) ) . '</span>
						</mark>';
			default:
				return '<mark class="order-status" title="' . esc_attr( __( $item->getStatus() ) ) // phpcs:ignore
						. '">
							<span>
							' .
				       esc_html( __( $item->getStatus() ) ) // phpcs:ignore
						. '</span> 
						</mark>';
		}
	}

	/**
	 * Method for actions column
	 *
	 * @param AutomationRecord $item Automation Record.
	 *
	 * @return string
	 */
	public function column_actions( $item ) {
		$buttons      = '<p>';
		$email_status = $item->getStatus();

		if ( RecoveryEmailStatus::PENDING === $email_status || RecoveryEmailStatus::NOT_SENT === $email_status ) {
			$buttons .= '<a class="button wc-action-button wc-action-button-complete complete" href="javascript:;" 
						   data-record_id="' . esc_attr( $item->getId() ) . '" 
						   title="' . esc_attr( __( 'Send recovery email now', 'cleverreach-wc' ) ) . '">' .
						esc_html( __( 'Send recovery email now', 'cleverreach-wc' ) ) . '</a>';
		}

		if ( RecoveryEmailStatus::SENDING !== $email_status ) {
			$buttons .= '<a class="button wc-action-button wc-action-button-delete delete" href="javascript:;" 
						   data-record_id="' . esc_attr( $item->getId() ) . '" 
						   title="' . esc_attr( __( 'Delete record', 'cleverreach-wc' ) ) . '">' .
						esc_html( __( 'Delete record', 'cleverreach-wc' ) ) . '</a>';
		}

		return $buttons . '</p>';
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param AutomationRecord $item Automation Record.
	 * @param string           $column_name Column name.
	 *
	 * @return string|float|bool
	 */
	public function column_default( $item, $column_name ) {
		$offset        = get_option( 'gmt_offset' );
		$offset_string = (float) $offset > 0 ? '+' . $offset : (string) $offset;

		switch ( $column_name ) {
			case 'scheduled_time':
				return $item->getScheduledTime() ?
					$item->getScheduledTime()->modify( "{$offset_string} hour" )->format( 'F j, Y H:i a' ) : '-';
			case 'sent_time':
				return $item->getSentTime() ?
					$item->getSentTime()->modify( "{$offset_string} hour" )->format( 'F j, Y H:i a' ) : '-';
			case 'total':
				return $item->getAmount();
			case 'customer_email':
				return $item->getEmail();
			case 'recovery_email_status':
				return $item->getStatus();
			case 'recovery_status':
				return $item->getIsRecovered();
			case 'error_message':
				return $item->getErrorMessage();
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
		}
	}

	/**
	 * Associative array of columns
	 *
	 * @return array<string,string|null>
	 */
	public function get_columns() {
		return array(
			'scheduled_time'        => __( 'Scheduled time', 'cleverreach-wc' ),
			'sent_time'             => __( 'Sent time', 'cleverreach-wc' ),
			'total'                 => __( 'Total', 'cleverreach-wc' ),
			'customer_email'        => __( 'Customer email', 'cleverreach-wc' ),
			'recovery_email_status' => __( 'Recovery email status', 'cleverreach-wc' ),
			'recovery_status'       => __( 'Recovery status', 'cleverreach-wc' ),
			'error_message'         => __( 'Message', 'cleverreach-wc' ),
			'actions'               => __( 'Actions', 'cleverreach-wc' ),
		);
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array<string,mixed[]>
	 */
	public function get_sortable_columns() {
		return array(
			'scheduled_time' => array( 'scheduledTime', true ),
			'sent_time'      => array( 'sentTime', true ),
		);
	}

	/**
	 * Renders Abandoned Cart Records table.
	 *
	 * @return void
	 */
	public function render_table() {
		$send_now = $this->get_config()['send_now'];
		$delete   = $this->get_config()['delete'];

		echo '<form method="post" id="ac_records">
				<input id="cr-ac-send-now" type="hidden" value="' .
			esc_attr( __( $send_now, 'cleverreach-wc' ) ) // phpcs:ignore
			. '"> 
				<input id="cr-ac-delete" type="hidden" value="' .
			esc_attr( __( $delete, 'cleverreach-wc' ) ) . '">'; // phpcs:ignore

		$this->search_box( __( 'Search', 'cleverreach-wc' ), 'ac_records_search' );

		$this->views();
		$this->display();

		echo '</form>';
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string $which Position of table nav.
	 *
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$from = HTTP_Helper::get_param( 'scheduledTimeFrom' ) ? HTTP_Helper::get_param( 'scheduledTimeFrom' ) : '';
			$to   = HTTP_Helper::get_param( 'scheduledTimeTo' ) ? HTTP_Helper::get_param( 'scheduledTimeTo' ) : '';

			echo '<input type="text" name="scheduledTimeFrom" placeholder="' .
				esc_attr( __( 'Scheduled Date From', 'cleverreach-wc' ) ) .
				'" value="' . esc_attr( $from ) . '" />
				<input type="text" name="scheduledTimeTo" placeholder="' .
				esc_attr( __( 'Scheduled Date To', 'cleverreach-wc' ) ) .
				'" value="' . esc_attr( $to ) . '" />';

			submit_button( __( 'Filter', 'cleverreach-wc' ), 'primary', '', false, array( 'id' => 'filter-submit' ) );
		}
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 *
	 * @return mixed[]
	 */
	public function prepare_items() {
		$this->get_column_info();
		$this->_column_headers[0] = $this->get_columns();

		$per_page     = $this->get_items_per_page( Automation_Record_Service::PAGINATION_META_KEY );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count( null );

		$this->items = $this->get_ac_records( $per_page, $current_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		return $this->_column_headers;
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @return int
	 */
	public function get_number_of_items_per_page() {
		return $this->get_items_per_page( Automation_Record_Service::PAGINATION_META_KEY );
	}

	/**
	 * Renders  email status
	 *
	 * @return array<string,string>
	 */
	protected function get_views() {
		$base_url = add_query_arg( array( 'tab' => 'cr-abandoned-cart' ), menu_page_url( 'wc-reports', false ) );

		$all_url      = add_query_arg( array(), $base_url );
		$sent_url     = add_query_arg( array( 'status' => RecoveryEmailStatus::SENT ), $base_url );
		$sending_url  = add_query_arg( array( 'status' => RecoveryEmailStatus::SENDING ), $base_url );
		$pending_url  = add_query_arg( array( 'status' => RecoveryEmailStatus::PENDING ), $base_url );
		$not_sent_url = add_query_arg( array( 'status' => RecoveryEmailStatus::NOT_SENT ), $base_url );

		return array(
			'all'      => '<a href="' . esc_url( $all_url ) . '">' . esc_html( __( 'All', 'cleverreach-wc' ) )
							. '</a>' . esc_html( '(' . $this->record_count( array() ) . ')' ),
			'sent'     => '<a href="' . esc_url( $sent_url ) . '">' . esc_html( __( 'Sent', 'cleverreach-wc' ) )
							. '</a>' . esc_html( '(' . $this->record_count( array( 'status' => RecoveryEmailStatus::SENT ) ) . ')' ),
			'sending'  => '<a href="' . esc_url( $sending_url ) . '">' . esc_html( __( 'Sending', 'cleverreach-wc' ) )
							. '</a>' . esc_html( '(' . $this->record_count( array( 'status' => RecoveryEmailStatus::SENDING ) ) . ')' ),
			'pending'  => '<a href="' . esc_url( $pending_url ) . '">' . esc_html( __( 'Pending', 'cleverreach-wc' ) )
							. '</a>' . esc_html( '(' . $this->record_count( array( 'status' => RecoveryEmailStatus::PENDING ) ) ) . ')',
			'not_sent' => '<a href="' . esc_url( $not_sent_url ) . '">' . esc_html( __( 'Not sent', 'cleverreach-wc' ) )
							. '</a>' . esc_html( '(' . $this->record_count( array( 'status' => RecoveryEmailStatus::NOT_SENT ) ) . ')' ),
		);
	}

	/**
	 * Retrieves Automation Record Service
	 *
	 * @return Automation_Record_Service
	 */
	private function get_automation_record_service() {
		if ( null === $this->automation_record_service ) {
			$this->automation_record_service = new Automation_Record_Service();
		}

		return $this->automation_record_service;
	}

	/**
	 * Retrieves filters from request.
	 *
	 * @return array<string,string>
	 */
	private function get_query_filters() {
		$filters = array();

		$email_filter = HTTP_Helper::get_param( 's' );
		if ( ! empty( $email_filter ) ) {
			$filters['email'] = $email_filter;
		}

		$status_filter = HTTP_Helper::get_param( 'status' );
		if ( ! empty( $status_filter ) ) {
			$filters['status'] = $status_filter;
		}

		$scheduled_time_from = HTTP_Helper::get_param( 'scheduledTimeFrom' );
		if ( ! empty( $scheduled_time_from ) ) {
			$filters['scheduledTimeFrom'] = $scheduled_time_from;
		}

		$scheduled_time_to = HTTP_Helper::get_param( 'scheduledTimeTo' );
		if ( ! empty( $scheduled_time_to ) ) {
			$filters['scheduledTimeTo'] = $scheduled_time_to;
		}

		return $filters;
	}

	/**
	 * Retrieves urls for ajax requests.
	 *
	 * @return array<string,string>
	 */
	public function get_config() {
		return array(
			'send_now'                 => Shop_Helper::get_controller_url( 'Abandoned_Cart_Overview', 'force_send' ),
			'delete'                   => Shop_Helper::get_controller_url( 'Abandoned_Cart_Overview', 'delete' ),
			'update_per_page_settings' =>
				Shop_Helper::get_controller_url(
					'Abandoned_Cart_Overview_Options',
					'save_pagination'
				),
		);
	}
}
