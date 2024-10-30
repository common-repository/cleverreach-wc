<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldType;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\FieldService;

/**
 * Class Receiver_Fields_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Receiver_Fields_Service extends FieldService {


	/**
	 * Retrieves supported fields
	 *
	 * @return Field[]
	 */
	public function getSupportedFields() {
		return array_merge(
			parent::getSupportedFields(),
			array(
				new Field( 'shop', FieldType::TEXT ),
				new Field( 'customernumber', FieldType::TEXT ),
				new Field( 'lastorderdate', FieldType::DATE ),
				new Field( 'ordercount', FieldType::NUMBER ),
				new Field( 'totalspent', FieldType::NUMBER ),
			)
		);
	}

	/**
	 * Retrieves list of fields that an integration supports.
	 *
	 * @return Field[]
	 */
	public function getEnabledFields() {
		return $this->getSupportedFields();
	}
}
