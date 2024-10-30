<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Segments;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag\Tag_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\SegmentService;

/**
 * Class Segment_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Segments
 */
class Segment_Service extends SegmentService {


	/**
	 * Retrieves list of available segments.
	 *
	 * @return Segment[] The list of available segments.
	 */
	public function getSegments() {
		$tag_service = new Tag_Service();
		$tags        = $tag_service->get_tags();

		return $this->transformToSegments( $tags );
	}

	/**
	 * Transforms tags into segments.
	 *
	 * @param Tag[] $tags List of tags to transform.
	 *
	 * @return Segment[]
	 */
	private function transformToSegments( array $tags ) {
		$segments = array();

		foreach ( $tags as $tag ) {
			$segments[] = $tag->toSegment();
		}

		return $segments;
	}
}
