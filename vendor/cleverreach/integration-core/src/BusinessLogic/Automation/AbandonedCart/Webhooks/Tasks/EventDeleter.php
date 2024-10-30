<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\SubTask;

/**
 * Class EventDeleter
 *
 * Tries to delete abandoned cart events before they are created (in case they are already registered).
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\Tasks
 */
class EventDeleter extends SubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * @return void
     */
    public function execute()
    {
        // In this case group id is equivalent to abandoned cart id.
        $condition = $this->getExecutionContext()->getEvent()->getGroupId();
        $type = $this->getExecutionContext()->getEvent()->getEvent();

        try {
            $this->getEventsProxy()->deleteEvent($condition, $type);
        } catch (\Exception $e) {
            // Noting to do here.
        }

        $this->reportProgress(100);
    }
}
