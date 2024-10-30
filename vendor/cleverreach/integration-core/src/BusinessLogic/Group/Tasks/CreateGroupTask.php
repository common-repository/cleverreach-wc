<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class CreateGroupTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Tasks
 *
 * @access protected
 */
class CreateGroupTask extends Task
{
    const CLASS_NAME = __CLASS__;

    /**
     * Group service.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService
     */
    protected $groupService;

    /**
     * Creates group (if group does not already exist). Sets group id locally.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $groupService = $this->getGroupService();

        if ($this->groupIdExists()) {
            $this->reportProgress(100);
            return;
        }

        $name = $groupService->getName();

        $this->reportProgress(5);

        $group = $groupService->getGroupByName($name);

        $this->reportProgress(50);

        if ($group === null) {
            $group = $groupService->createGroup($name);
        }

        $groupService->setId($group->getId());
        $this->reportProgress(100);
    }

    /**
     * @return bool
     */
    protected function groupIdExists()
    {
        $groupId = $this->getGroupService()->getId();

        return !empty($groupId);
    }

    /**
     * Retrieves group service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService Instance of the GroupService.
     */
    protected function getGroupService()
    {
        if ($this->groupService === null) {
            /** @var GroupService $groupService */
            $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);
            $this->groupService = $groupService;
        }

        return $this->groupService;
    }
}
