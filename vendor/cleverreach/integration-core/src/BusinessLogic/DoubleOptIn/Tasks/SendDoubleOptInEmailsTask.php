<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoubleOptInEmail;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy as ReceiverProxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class SendDoubleOptInEmailsTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Tasks
 */
class SendDoubleOptInEmailsTask extends Task
{
    const INITIAL_PROGRESS_PERCENT = 10;

    /**
     * @var DoubleOptInEmail[]
     */
    protected $emails;
    /**
     * @var Proxy
     */
    protected $proxy;
    /**
     * @var ReceiverProxy
     */
    protected $receiverProxy;

    /**
     * SendDoubleOptInEmailsTask constructor.
     *
     * @param DoubleOptInEmail[] $emails
     */
    public function __construct(array $emails)
    {
        $this->emails = $emails;
    }
    /**
     * @inheritdoc
     */
    public function getEqualityComponents()
    {
        return array('emails' => $this->emails);
    }

    public function serialize()
    {
        return Serializer::serialize($this->emails);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->emails = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        return new static(DoubleOptInEmail::fromBatch($array['emails']));
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'emails' => Transformer::batchTransform($this->emails),
        );
    }

    /**
     * Sends double opt-in email.
     *
     * @return void
     *
     * @throws FailedToRefreshAccessToken
     * @throws FailedToRetrieveAuthInfoException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function execute()
    {
        $this->reportProgress(static::INITIAL_PROGRESS_PERCENT);

        $currentProgress = static::INITIAL_PROGRESS_PERCENT;
        $progressStep = count($this->emails) > 0 ?
            (int)((100 - self::INITIAL_PROGRESS_PERCENT) / count($this->emails)) : 0;

        foreach ($this->emails as $key => $email) {
            $groupId = $this->getGroupService()->getId();
            $receiver = $this->getReceiver($groupId, $email->getEmail());

            if (!$receiver || $receiver->isActive()) {
                $this->inactivateReceiver($email, $groupId);
            } else {
                $this->whitelistInactiveReceiver($email);
            }

            $this->getProxy()->sendDoubleOptInEmail($email);

            unset($this->emails[$key]);

            $currentProgress += $progressStep;
            $this->reportProgress($currentProgress);
        }

        $this->reportProgress(100);
    }

    /**
     * Fetches a receiver from CR API
     *
     * @param string $groupId
     * @param string $email
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     */
    protected function getReceiver($groupId, $email)
    {
        try {
            $receiver = $this->getReceiverProxy()->getReceiver($groupId, $email);
            $this->reportAlive();
        } catch (HttpRequestException $exception) {
            $receiver = null;
        }

        return $receiver;
    }

    /**
     * Creates receiver as inactive
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoubleOptInEmail $email
     * @param string $groupId
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function inactivateReceiver(DoubleOptInEmail $email, $groupId)
    {
        $receiver = new Receiver();

        $receiver->setEmail($email->getEmail());
        $this->getReceiverProxy()->upsertPlus($groupId, array($receiver));

        $receiver->setActivated('0');
        $this->getReceiverProxy()->upsertPlus($groupId, array($receiver));

        return $receiver;
    }

    /**
     * Removes inactive receiver from a blacklist.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoubleOptInEmail $email
     *
     * @return void
     */
    protected function whitelistInactiveReceiver(DoubleOptInEmail $email)
    {
        try {
            $suffix = $this->getGroupService()->getBlacklistedEmailsSuffix();
            $this->getReceiverProxy()->whitelist($email->getEmail() . $suffix);
        } catch (\Exception $e) {
            Logger::logInfo(
                "Failed to remove receiver from a blacklist because: {$e->getMessage()}.",
                'Core',
                array(new LogContextData('trace', $e->getTraceAsString()))
            );
        }

        $this->reportAlive();
    }

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        if ($this->proxy === null) {
            /** @var Proxy $proxy */
            $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
            $this->proxy = $proxy;
        }

        return $this->proxy;
    }

    /**
     * @return ReceiverProxy
     */
    protected function getReceiverProxy()
    {
        if ($this->receiverProxy === null) {
            /** @var ReceiverProxy $receiverProxy */
            $receiverProxy = ServiceRegister::getService(ReceiverProxy::CLASS_NAME);
            $this->receiverProxy = $receiverProxy;
        }

        return $this->receiverProxy;
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
