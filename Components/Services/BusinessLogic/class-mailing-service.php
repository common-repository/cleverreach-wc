<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Contracts\DefaultMailingService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\MailingContent;

/**
 * Class Mailing_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Mailing_Service implements DefaultMailingService {



	/**
	 * Retrieves name.
	 *
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'My first WooCommerce email' );
	}

	/**
	 * Retrieves subject.
	 *
	 * @inheritDoc
	 */
	public function getSubject() {
		return __( 'This is my first newsletter with CleverReach' );
	}

	/**
	 * Retrieves content.
	 *
	 * @inheritDoc
	 */
	public function getContent() {
		$content = new MailingContent();
		$content->setType( 'html/text' );
		$content->setText( '' );
		$content->setHtml( '' );

		return $content;
	}
}
