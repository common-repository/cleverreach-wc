<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Tasks\CreateFieldsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Tasks\CreateSegmentsTask;

/**
 * Class FieldsSynchronization
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components
 */
class FieldsSynchronization extends InitialSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * FieldsSynchronization constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getSubTasks());
    }

    /**
     * Retrieves sub tasks.
     *
     * @return array<string,int>
     */
    protected function getSubTasks()
    {
        return array(
            CreateFieldsTask::CLASS_NAME => 30,
            CreateSegmentsTask::CLASS_NAME => 70,
        );
    }
}
