<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Util;

use CleverReach\WooCommerce\Migrations\Contracts\Update_Schema;

/**
 * Class Version_File_Reader
 *
 * @package CleverReach\WooCommerce\Components\Utility
 */
class Version_File_Reader {


	const MIGRATION_FILE_PREFIX = 'class-migration-';

	/**
	 * Migrations directory.
	 *
	 * @var string
	 */
	private $migrations_directory;
	/**
	 * Version number.
	 *
	 * @var string
	 */
	private $version;
	/**
	 * Files for execution.
	 *
	 * @var string[]
	 */
	private $sorted_files_for_execution = array();
	/**
	 * Pointer.
	 *
	 * @var int
	 */
	private $pointer = 0;

	/**
	 * Version_File_Reader constructor.
	 *
	 * @param string $migration_directory Migration directory.
	 * @param string $version Version.
	 */
	public function __construct( $migration_directory, $version ) {
		$this->migrations_directory = $migration_directory;
		$this->version              = $version;
	}

	/**
	 * Read next file from list of files for execution
	 *
	 * @return Update_Schema|null
	 */
	public function read_next() {
		if ( ! $this->has_next() ) {
			return null;
		}

		include_once $this->migrations_directory . $this->sorted_files_for_execution[ $this->pointer ];
		$version    = $this->get_file_version( $this->sorted_files_for_execution[ $this->pointer ] );
		$class_name = $this->get_class_name( $version );
		++$this->pointer;

		/**
		 * Update schema.
		 *
		 * @var Update_Schema|null
		 */
		return class_exists( $class_name ) ? new $class_name() : null;
	}

	/**
	 * Checks if there is a next file from list of files for execution
	 *
	 * @return bool
	 */
	public function has_next() {
		if ( empty( $this->sorted_files_for_execution ) ) {
			$this->sort_files();
		}

		return isset( $this->sorted_files_for_execution[ $this->pointer ] );
	}

	/**
	 * Sort and filter files for execution
	 *
	 * @return void
	 */
	private function sort_files() {
		$file_list = scandir( $this->migrations_directory, 0 );
		if ( ! $file_list ) {
			$file_list = array();
		}

		$files = array_diff( $file_list, array( '.', '..' ) );
		if ( $files ) {
			$self = $this;
			usort(
				$files,
				function ( $file1, $file2 ) use ( $self ) {
					$file_1_version = $self->get_file_version( $file1 );
					$file_2_version = $self->get_file_version( $file2 );

					return version_compare( $file_1_version, $file_2_version );
				}
			);

			foreach ( $files as $file ) {
				$file_version = $this->get_file_version( $file );
				if ( version_compare( $this->version, $file_version, '<' ) ) {
					$this->sorted_files_for_execution[] = $file;
				}
			}
		}
	}

	/**
	 * Get file version based on file name
	 *
	 * @param string $file File name.
	 *
	 * @return string
	 */
	private function get_file_version( $file ) {
		$version = str_ireplace( array( self::MIGRATION_FILE_PREFIX, '.php' ), '', $file );

		return str_replace( '-', '.', $version );
	}

	/**
	 * Get migration class name
	 *
	 * @param string $version version.
	 *
	 * @return string
	 */
	private function get_class_name( $version ) {
		return 'CleverReach\\WooCommerce\\Migrations\\Scripts\\Migration_' . str_replace( '.', '_', $version );
	}
}
