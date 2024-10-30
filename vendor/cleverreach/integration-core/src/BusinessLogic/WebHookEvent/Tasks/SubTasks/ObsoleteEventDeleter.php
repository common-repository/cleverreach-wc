<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

class ObsoleteEventDeleter extends SubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Removes event that has been previously registered.
     */
    public function execute()
    {
        try {
            $groupId = $this->getGroupService()->getId();
            $type = $this->getEventsService()->getType();
            $this->getEventsProxy()->deleteEvent($groupId, $type);
        } catch (\Exception $e) {
            Logger::logWarning(
                'Failed to delete obsolete event.',
                'Core',
                array(new LogContextData('trace', $e->getTraceAsString()))
            );
        }

        $this->reportProgress(100);
    }
}
