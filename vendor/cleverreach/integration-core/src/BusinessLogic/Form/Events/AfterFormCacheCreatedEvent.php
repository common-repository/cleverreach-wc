<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

/**
 * Class AfterFormCacheCreatedEvent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events
 */
class AfterFormCacheCreatedEvent extends Event
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form
     */
    private $form;

    /**
     * AfterFormCacheCreatedEvent constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form
     */
    public function getForm()
    {
        return $this->form;
    }
}
