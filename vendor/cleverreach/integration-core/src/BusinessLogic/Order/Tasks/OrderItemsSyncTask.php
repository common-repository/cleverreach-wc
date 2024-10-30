<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\BlacklistFilterService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Contracts\OrderService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\Attribute\Attribute;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\Category\Category;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value\Increment;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class OrderItemsSyncTask extends Task
{
    /**
     * Integrated system order id.
     *
     * @var string | int
     */
    protected $orderId;
    /**
     * Buyer's email.
     *
     * @var string
     */
    protected $receiverEmail;
    /**
     * Order tracking mailing id.
     *
     * @var string
     */
    protected $mailingId;

    /**
     * OrderItemsSyncTask constructor.
     *
     * @param int|string $orderId
     * @param string $receiverEmail
     * @param string $mailingId
     */
    public function __construct($orderId, $receiverEmail, $mailingId = '')
    {
        $this->orderId = $orderId;
        $this->receiverEmail = $receiverEmail;
        $this->mailingId = $mailingId;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'orderId' => $this->orderId,
            'receiverEmail' => $this->receiverEmail,
            'mailingId' => $this->mailingId,
        );
    }

    /**
     * @inheritDoc
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        return new static(
            $data['orderId'],
            $data['receiverEmail'],
            $data['mailingId']
        );
    }

    /**
     * Serializes task.
     *
     * @return string
     */
    public function serialize()
    {
        return Serializer::serialize(
            array(
                $this->orderId,
                $this->receiverEmail,
                $this->mailingId,
            )
        );
    }

    /**
     * Unserializes serialized task.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list($this->orderId, $this->receiverEmail, $this->mailingId) = Serializer::unserialize($serialized);
    }

    /**
     * Appends order item information to a CleverReach receiver identified by the email.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        if (!$this->getOrderService()->canSynchronizeOrderItems()) {
            $this->reportProgress(100);

            return;
        }

        $this->receiverEmail = $this->getBlacklistEmailService()->filterEmail($this->receiverEmail);
        if (empty($this->receiverEmail)) {
            $this->reportProgress(100);

            return;
        }

        $groupId = $this->getGroupService()->getId();

        $receiver = new Receiver();
        $receiver->setEmail($this->receiverEmail);
        $receiver->setSource($this->getOrderService()->getOrderSource($this->orderId));

        $this->reportProgress(5);

        $orderItems = $this->getOrderService()->getOrderItems($this->orderId);

        $this->reportAlive();

        foreach ($orderItems as $orderItem) {
            if ($this->mailingId !== '') {
                $orderItem->setMailingId($this->mailingId);
            }

            $receiver->addOrderItem($orderItem);

            $this->addAttributeTags($orderItem->getAttributes(), $receiver);
            $this->addCategoryTags($orderItem->getCategories(), $receiver);
        }

        $receiver->addModifier(new Increment('ordercount', 1));

        $this->reportProgress(70);

        $this->getReceiverProxy()->upsertPlus($groupId, array($receiver));

        $this->reportProgress(100);
    }

    /**
     * @return BlacklistFilterService
     */
    protected function getBlacklistEmailService()
    {
        /** @var BlacklistFilterService $blacklistEmailService */
        $blacklistEmailService = ServiceRegister::getService(BlacklistFilterService::CLASS_NAME);

        return $blacklistEmailService;
    }

    /**
     * Retrieves group service.
     *
     * @return GroupService
     */
    protected function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }

    /**
     * Retrieves order service.
     *
     * @return OrderService
     */
    protected function getOrderService()
    {
        /** @var OrderService $orderService */
        $orderService = ServiceRegister::getService(OrderService::CLASS_NAME);

        return $orderService;
    }

    /**
     * Retrieves receiver proxy.
     *
     * @return Proxy
     */
    protected function getReceiverProxy()
    {
        /** @var Proxy $receiverProxy */
        $receiverProxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $receiverProxy;
    }

    /**
     * Adds product attributes as receiver tags (Source-AttributeKey.AttributeValue)
     *
     * @param Attribute[] $attributes
     * @param Receiver $receiver
     *
     * @return void
     */
    protected function addAttributeTags(array $attributes, Receiver $receiver)
    {
        foreach ($attributes as $attribute) {
            $receiver->addTag($this->createTag($attribute->getValue(), $attribute->getKey()));
        }
    }

    /**
     * Adds product categories as receiver tags (Source-Category.CategoryValue)
     *
     * @param Category[] $categories
     * @param Receiver $receiver
     *
     * @return void
     */
    private function addCategoryTags(array $categories, Receiver $receiver)
    {
        foreach ($categories as $category) {
            $receiver->addTag($this->createTag($category->getValue(), 'Category'));
        }
    }

    /**
     * Creates tag with given value and type.
     *
     * @param string $value
     * @param string $type
     *
     * @return Tag
     */
    protected function createTag($value, $type)
    {
        $source = $this->getConfigService()->getIntegrationName();
        $tag = new Tag($source, $value);
        $tag->setType($type);

        return $tag;
    }
}
