<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Util;

use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Init_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;
use wpdb;

/**
 * Class Database
 *
 * @package CleverReach\WooCommerce\Components\Utility
 */
class Database {

	const BASE_TABLE                  = 'cleverreach_wc_entity';
	const AUTOMATION_TABLE            = 'cleverreach_wc_automation';
	const OLD_CONFIG_TABLE            = 'cleverreach_wc_config';
	const ARCHIVE_TABLE               = 'cleverreach_wc_archive';
	const DATA_RESOURCES_ENTITY_TABLE = 'cleverreach_wc_data_resources_entity';
	const BUFFER_CONFIG_TABLE         = 'cleverreach_wc_buffer_config';
	const EVENTS_BUFFER_TABLE         = 'cleverreach_wc_events_buffer_entity';

	/**
	 * WordPress database session.
	 *
	 * @var wpdb WordPress database
	 */
	private $db;

	/**
	 * Database constructor.
	 *
	 * @param wpdb $db Wodrpess database.
	 */
	public function __construct( $db ) {
		$this->db = $db;
	}

	/**
	 * Checks if plugin was already installed and initialized.
	 *
	 * @return bool
	 */
	public function plugin_already_initialized() {
		$table_name     = $this->db->prefix . self::BASE_TABLE;
		$old_table_name = $this->db->prefix . self::OLD_CONFIG_TABLE;

		return $this->db->get_var( "SHOW TABLES LIKE '" . $table_name . "'" ) === $table_name
				|| $this->db->get_var( "SHOW TABLES LIKE '" . $old_table_name . "'" ) === $old_table_name;
	}

	/**
	 * Checks if plugin version older than v3 was already installed and initialized.
	 *
	 * @return bool
	 */
	public function plugin_older_than_v3_already_initialized() {
		$old_table_name = $this->db->prefix . self::OLD_CONFIG_TABLE;

		return $this->db->get_var( "SHOW TABLES LIKE '" . $old_table_name . "'" ) === $old_table_name;
	}

	/**
	 * Executes installation scripts.
	 *
	 * @return void
	 */
	public function install() {
		$this->create_entity_table();
		$this->create_automation_table();
		$this->create_archive_table();
		$this->create_data_resources_entity_table();
		$this->create_buffer_config_table();
		$this->create_events_buffer_entity_table();
	}

	/**
	 * Executes uninstallation.
	 *
	 * @return void
	 */
	public function uninstall() {
		$tables   = array();
		$tables[] = Base_Repository::get_table_name( self::BASE_TABLE );
		$tables[] = Base_Repository::get_table_name( self::AUTOMATION_TABLE );
		$tables[] = Base_Repository::get_table_name( self::ARCHIVE_TABLE );
		$tables[] = Base_Repository::get_table_name( self::DATA_RESOURCES_ENTITY_TABLE );
		$tables[] = Base_Repository::get_table_name( self::BUFFER_CONFIG_TABLE );
		$tables[] = Base_Repository::get_table_name( self::EVENTS_BUFFER_TABLE );

		foreach ( $tables as $table_name ) {
			$query = "DROP TABLE IF EXISTS $table_name";
			$this->db->query( $query );
		}
	}

	/**
	 * Get config value form old table
	 *
	 * @param string $key key.
	 *
	 * @return string|null
	 */
	public function get_old_config_value( $key ) {
		$query = 'SELECT `value` FROM ' . Base_Repository::get_table_name( self::OLD_CONFIG_TABLE ) .
				" WHERE `key` = '$key'";

		return $this->db->get_var( $query );
	}

	/**
	 * Executes update database functions.
	 *
	 * @param Version_File_Reader $version_file_reader Version file reader.
	 *
	 * @return bool
	 */
	public function update( $version_file_reader ) {
		while ( $version_file_reader->has_next() ) {
			$statements = $version_file_reader->read_next();
			if ( ! is_array( $statements ) ) {
				try {
					$this->db->query( $statements );

					return true;
				} catch ( Exception $ex ) {
					$this->handle_failed_update( $ex, $statements );

					return false;
				}
			}

			foreach ( $statements as $statement ) {
				try {
					$this->db->query( $statement );
				} catch ( Exception $ex ) {
					$this->handle_failed_update( $ex, $statement );

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Handles failed update.
	 *
	 * @param Exception $exception Exception.
	 * @param mixed     $statement Statement.
	 *
	 * @return void
	 */
	private function handle_failed_update( $exception, $statement ) {
		Init_Service::init();
		Logger::logInfo( $exception->getMessage(), 'Database Update' );
		Logger::logInfo( $statement, 'SQL' );
	}

	/**
	 * Prepares database queries for inserting tables.
	 *
	 * @return void
	 */
	private function create_entity_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( self::BASE_TABLE ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `index_3` VARCHAR(255),
            `index_4` VARCHAR(255),
            `index_5` VARCHAR(255),
            `index_6` VARCHAR(255),
            `index_7` VARCHAR(255),
            `index_8` VARCHAR(255),
            `index_9` VARCHAR(255),
            `index_10` VARCHAR(255),
            `data` LONGTEXT,
            PRIMARY KEY (`id`),
            INDEX configKey (type, index_1),
            INDEX latestByType (index_2, index_5),
            INDEX typeStatus (index_1, index_3, index_8),
            INDEX equalityHash (index_3, index_9)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}

	/**
	 * Create new table
	 *
	 * @return void
	 */
	private function create_automation_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_automation' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `index_3` VARCHAR(255),
            `index_4` VARCHAR(255),
            `index_5` VARCHAR(255),
            `index_6` VARCHAR(255),
            `index_7` VARCHAR(255),
            `index_8` VARCHAR(255),
            `index_9` VARCHAR(255),
            `index_10` VARCHAR(255),
            `data` TEXT,
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}

	/**
	 * Create cleverreach_wc_archive table
	 *
	 * @return void
	 */
	private function create_archive_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_archive' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `index_3` VARCHAR(255),
            `index_4` VARCHAR(255),
            `index_5` VARCHAR(255),
            `index_6` VARCHAR(255),
            `index_7` VARCHAR(255),
            `index_8` VARCHAR(255),
            `index_9` VARCHAR(255),
            `index_10` VARCHAR(255),
            `data` LONGTEXT,
            PRIMARY KEY (`id`),
            INDEX configKey (type, index_1),
            INDEX latestByType (index_2, index_5),
            INDEX typeStatus (index_1, index_3, index_8)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}

	/**
	 * Create cleverreach_wc_data_resources_entity table
	 *
	 * @return void
	 */
	private function create_data_resources_entity_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( self::DATA_RESOURCES_ENTITY_TABLE ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `index_3` VARCHAR(255),
            `index_4` VARCHAR(255),
            `data` TEXT,
            PRIMARY KEY (`id`),
            INDEX type_index1 (type, index_1)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}

	/**
	 * Creates cleverreach_wc_buffer_config table
	 *
	 * @return void
	 */
	private function create_buffer_config_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_buffer_config' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `context` VARCHAR(255),
            `interval_type` VARCHAR(255),
            `interval_time` INT,
            `next_run` INT,
            `has_events` TINYINT(1),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}

	/**
	 * Creates cleverreach_wc_events_buffer_entity table
	 *
	 * @return void
	 */
	private function create_events_buffer_entity_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_events_buffer_entity' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `data` LONGTEXT,
            PRIMARY KEY (`id`),
            INDEX index1 (index_1),
            INDEX index1_index2 (index_1, index_2)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}

	/**
	 * Truncates tables
	 *
	 * @return void
	 */
	public function truncate_tables() {
		$tables   = array();
		$tables[] = Base_Repository::get_table_name( self::BASE_TABLE );
		$tables[] = Base_Repository::get_table_name( self::AUTOMATION_TABLE );
		$tables[] = Base_Repository::get_table_name( self::DATA_RESOURCES_ENTITY_TABLE );
		$tables[] = Base_Repository::get_table_name( self::EVENTS_BUFFER_TABLE );
		$tables[] = Base_Repository::get_table_name( self::BUFFER_CONFIG_TABLE );

		foreach ( $tables as $table_name ) {
			$query = "TRUNCATE $table_name;";
			$this->db->query( $query );
		}
	}
}
