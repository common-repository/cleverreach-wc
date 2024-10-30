<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution;

class TasksToBeDeleted
{
    /**
     * Contains all the tasks which should be deleted right after they are completed
     *
     * @return string[]
     */
    public static function getTaskForDeletion()
    {
        return array(
            'ScheduleCheckTask',
            'TaskCleanupTask'
        );
    }
}
