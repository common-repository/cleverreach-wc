<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Contracts\Automation_Record_Service_Interface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use DateTime;
use Exception;

/**
 * Class Automation_Record_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation
 */
class Automation_Record_Service extends AutomationRecordService implements Automation_Record_Service_Interface {


	const PAGINATION_META_KEY = 'ac_per_page';

	/**
	 * Retrieves Automation Records.
	 *
	 * @param array<string,mixed> $filters Query filters.
	 * @param int                 $per_page Number of records per page.
	 * @param int                 $page_number Page number.
	 * @param string              $order_by Order column.
	 * @param string              $order Order direction.
	 *
	 * @inheritDoc
	 */
	public function get_records( $filters = array(), $per_page = 0, $page_number = 0, $order_by = '', $order = 'ASC' ) {
		try {
			$query_filter = new QueryFilter();
			$this->map_array_conditions_to_query_filter( $filters, $query_filter );

			if ( $per_page > 0 && $page_number > 0 ) {
				$query_filter->setLimit( $per_page );
				$query_filter->setOffset( ( $page_number - 1 ) * $per_page );
			}

			$order = wc_strtoupper( $order );

			if ( ! empty( $order_by ) && in_array(
				$order,
				array( $query_filter::ORDER_ASC, $query_filter::ORDER_DESC ),
				true
			) ) {
				$query_filter->orderBy( $order_by, $order );
			}

			return $this->filter( $query_filter );
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to get automation records.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);

			return array();
		}
	}

	/**
	 * Retrieves number of Automation Records that meet given criteria.
	 *
	 * @param array<string,mixed> $filters Query filters.
	 *
	 * @inheritDoc
	 */
	public function count( $filters = array() ) {
		try {
			$query_filter = new QueryFilter();
			$this->map_array_conditions_to_query_filter( $filters, $query_filter );

			return $this->getRepository()->count( $query_filter );
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to count automation records.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);

			return 0;
		}
	}

	/**
	 * Saves pagination for Abandoned Cart overview page.
	 *
	 * @param int $per_page Number of records per page.
	 *
	 * @inheritDoc
	 */
	public function save_pagination( $per_page ) {
		$user_id = get_current_user_id();
		if ( 0 < $user_id ) {
			return update_user_meta( $user_id, self::PAGINATION_META_KEY, $per_page );
		}

		return false;
	}

	/**
	 * Map array of conditions to query filter.
	 *
	 * @param array<string,mixed> $conditions Conditions.
	 * @param QueryFilter         $query_filter Query filter.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Query Filter Invalid Parameter Exception.
	 */
	private function map_array_conditions_to_query_filter( $conditions, $query_filter ) {
		foreach ( $conditions as $column => $value ) {
			if ( null === $value ) {
				$query_filter->where( $column, Operators::NULL );
			} elseif ( 'email' === $column ) {
				$query_filter->where( $column, Operators::LIKE, '%' . $value . '%' );
			} elseif ( 'scheduledTimeFrom' === $column ) {
				$offset = get_option( 'gmt_offset' );
				/**
				 * DateTime.
				 *
				 * @var DateTime $value
				 */
				$value = DateTime::createFromFormat( get_option( 'date_format' ), $value );
				$value->setTime( 0, 0 );

				if ( 0.0 !== (float) $offset ) {
					$offset_string = (float) $offset > 0 ? '-' . abs( $offset ) : '+' . abs( $offset );
					$value->modify( "{$offset_string} hour" );
				}

				$column = 'scheduledTime';
				$query_filter->where( $column, Operators::GREATER_OR_EQUAL_THAN, $value );
			} elseif ( 'scheduledTimeTo' === $column ) {
				$offset = get_option( 'gmt_offset' );
				/**
				 * DateTime.
				 *
				 * @var DateTime $value
				 */
				$value = DateTime::createFromFormat( get_option( 'date_format' ), $value );
				$value->setTime( 23, 59 );

				if ( 0.0 !== (float) $offset ) {
					$offset_string = (float) $offset > 0 ? '-' . abs( $offset ) : '+' . abs( $offset );
					$value->modify( "{$offset_string} hour" );
				}

				$column = 'scheduledTime';
				$query_filter->where( $column, Operators::LESS_OR_EQUAL_THAN, $value );
			} else {
				$query_filter->where( $column, Operators::EQUALS, $value );
			}
		}
	}
}
