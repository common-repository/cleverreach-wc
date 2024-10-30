<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

class DeactivateReceiverTask extends Task
{
    /**
     * @var string
     */
    private $email;

    /**
     * ActivateReceiverTask constructor.
     *
     * @param string $email
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * @inheritdoc
     */
    public function getEqualityComponents()
    {
        return array('email' => $this->email);
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize($this->email);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->email = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('email' => $this->email);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        return new static($array['email']);
    }

    /**
     * Activates a receiver identified by the email.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $receiver = new Receiver();
        $receiver->setEmail($this->email);
        $receiver->setActivated('0');

        $this->reportProgress(50);

        $existingReceiver = $this->getProxy()->findReceiverByEmail($this->getGroupService()->getId(), $this->email);
        if ($existingReceiver) {
            $this->getProxy()->upsertPlus($this->getGroupService()->getId(), array($receiver));
        }

        $this->reportProgress(100);
    }

    /**
     * Retrieves receiver proxy.
     *
     * @return Proxy
     */
    private function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Retrieves group service.
     *
     * @return GroupService
     */
    private function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }
}
