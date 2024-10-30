<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\BufferConfiguration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories\BufferConfigurationRepositoryInterface;
use wpdb;

/**
 * Class Buffer_Config_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Buffer_Config_Repository implements BufferConfigurationRepositoryInterface {

	const TABLE_NAME = 'cleverreach_wc_buffer_config';

	/**
	 * Database session object.
	 *
	 * @var wpdb Database session object.
	 */
	private $db;

	/**
	 * Stores buffer configuration in database.
	 *
	 * @param BufferConfiguration $buffer_configuration Buffer configuration.
	 */
	public function createConfiguration( BufferConfiguration $buffer_configuration ) {
		$sql                = 'INSERT INTO ' . Base_Repository::get_table_name( self::TABLE_NAME ) .
								' (interval_type, interval_time, next_run, has_events) VALUES (%s, %d, %d, %d)' .
								' ON DUPLICATE KEY UPDATE interval_type = %s, interval_time = %d, next_run = %d, has_events = %d';
		$prepared_statement = $this->get_db()
									->prepare(
										$sql,
										$buffer_configuration->getIntervalType(),
										$buffer_configuration->getInterval(),
										$buffer_configuration->getNextRun(),
										(int) $buffer_configuration->isHasEvents(),
										$buffer_configuration->getIntervalType(),
										$buffer_configuration->getInterval(),
										$buffer_configuration->getNextRun(),
										(int) $buffer_configuration->isHasEvents()
									);
		$this->get_db()->query( $prepared_statement );
	}

	/**
	 * Returns buffer configuration for given context
	 *
	 * @param string $context Context.
	 *
	 * @return BufferConfiguration|null
	 */
	public function getConfiguration( $context ) {
		$query = 'SELECT * FROM ' . Base_Repository::get_table_name( self::TABLE_NAME ) .
				' WHERE context IS NULL';

		$raw_results = $this->get_db()->get_results( $query, ARRAY_A );

		if ( empty( $raw_results ) ) {
			return null;
		}

		$item = $raw_results[0];

		return new BufferConfiguration(
			isset( $item['context'] ) ? $item['context'] : '',
			isset( $item['interval_type'] ) ? $item['interval_type'] : '',
			isset( $item['interval_time'] ) ? $item['interval_time'] : 0,
			isset( $item['next_run'] ) ? $item['next_run'] : 0,
			isset( $item['has_events'] ) ? filter_var( $item['has_events'], FILTER_VALIDATE_BOOLEAN ) : false
		);
	}

	/**
	 * Returns buffer configurations that satisfy given parameters
	 *
	 * @param integer $from_timestamp From timestamp.
	 * @param bool    $has_events Has events.
	 *
	 * @return BufferConfiguration[]
	 */
	public function getFilteredConfigurations( $from_timestamp, $has_events ) {
		$config = $this->getConfiguration( '' );

		if ( $config && $config->getNextRun() <= $from_timestamp && $config->isHasEvents() === $has_events ) {
			return array( $config );
		}

		return array();
	}

	/**
	 * Update flag hasEvents for the provided context
	 *
	 * @param string $context Context.
	 * @param bool   $has_events Has events.
	 *
	 * @return void
	 */
	public function updateHasEvents( $context, $has_events ) {
		$data = array(
			'has_events' => (int) $has_events,
		);

		$this->get_db()->update(
			Base_Repository::get_table_name( self::TABLE_NAME ),
			$data,
			array( 'context' => null )
		);
	}

	/**
	 * Updates next run field for the given context
	 *
	 * @param string $context Context.
	 * @param int    $next_run Next run.
	 */
	public function updateNextRun( $context, $next_run ) {
		$data = array(
			'next_run' => $next_run,
		);

		$this->get_db()->update(
			Base_Repository::get_table_name( self::TABLE_NAME ),
			$data,
			array( 'context' => null )
		);
	}

	/**
	 * Updates provided fields for the given context
	 *
	 * @param string $context Context.
	 * @param string $interval_type Interval type.
	 * @param int    $interval Interval.
	 * @param int    $next_run Next run.
	 *
	 * @return void
	 */
	public function saveInterval( $context, $interval_type, $interval, $next_run ) {
		$data = array(
			'interval_type' => $interval_type,
			'interval_time' => $interval,
			'next_run'      => $next_run,
		);

		$this->get_db()->update(
			Base_Repository::get_table_name( self::TABLE_NAME ),
			$data,
			array( 'context' => null )
		);
	}

	/**
	 * Returns database session object.
	 *
	 * @return wpdb
	 */
	private function get_db() {
		if ( null === $this->db ) {
			global $wpdb;
			$this->db = $wpdb;
		}

		return $this->db;
	}
}
