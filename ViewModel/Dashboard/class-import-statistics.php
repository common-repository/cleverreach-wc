<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Dashboard;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Group_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Language\Translation_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\Contracts\DashboardService as Base_Dashboard_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\DashboardService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService as Base_Translation_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Contracts\SegmentService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Import_Statistics
 *
 * @package CleverReach\WooCommerce\ViewModel\Dashboard
 */
class Import_Statistics {


	/**
	 * Returns number of synced recipients
	 *
	 * @return string
	 */
	public function get_number_of_synced_recipients() {
		try {
			$number_of_recipients = $this->get_dashboard_service()->getSyncedReceiversCount();
		} catch ( QueryFilterInvalidParamException $e ) {
			$number_of_recipients = 0;
		}

		return $this->get_translation_service()->format_number( $number_of_recipients );
	}

	/**
	 * Returns group name
	 *
	 * @return string
	 */
	public function get_group_name() {
		return $this->get_group_service()->getName();
	}

	/**
	 * Returns segments
	 *
	 * @return string
	 */
	public function get_segments() {
		$segments       = $this->get_segments_service()->getSegments();
		$segments_names = array();

		foreach ( $segments as $segment ) {
			$segments_names[] = $segment->getName();
		}

		return $this->build_segments( $segments_names );
	}

	/**
	 * Formats segments for report panel on dashboard after initial sync
	 *
	 * @param string[] $segments Array of segments.
	 *
	 * @return string
	 */
	private function build_segments( $segments ) {
		$segment_list = '';

		for ( $i = 0; $i < 3; $i++ ) {
			$segment_list .= $segments[ $i ] . ', ';
		}

		$segment_list .= '...';

		return $segment_list;
	}

	/**
	 * Returns Segment Service
	 *
	 * @return SegmentService
	 */
	private function get_segments_service() {
		/**
		 * Segment service.
		 *
		 * @var SegmentService $segments_service
		 */
		$segments_service = ServiceRegister::getService( SegmentService::CLASS_NAME );

		return $segments_service;
	}

	/**
	 * Returns translation service
	 *
	 * @return Translation_Service
	 */
	private function get_translation_service() {
		/**
		 * Translation service.
		 *
		 * @var Translation_Service $translation_service
		 */
		$translation_service = ServiceRegister::getService( Base_Translation_Service::CLASS_NAME );

		return $translation_service;
	}

	/**
	 * Returns dashboard service
	 *
	 * @return DashboardService
	 */
	private function get_dashboard_service() {
		/**
		 * Dashboard service.
		 *
		 * @var DashboardService $dashboard_service
		 */
		$dashboard_service = ServiceRegister::getService( Base_Dashboard_Service::CLASS_NAME );

		return $dashboard_service;
	}

	/**
	 * Returns dashboard service
	 *
	 * @return Group_Service
	 */
	private function get_group_service() {
		/**
		 * Group service.
		 *
		 * @var Group_Service $group_service
		 */
		$group_service = ServiceRegister::getService( Group_Service::CLASS_NAME );

		return $group_service;
	}
}
