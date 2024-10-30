<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\EventsBufferEntity;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;

/**
 * Class Event
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events
 */
class Event
{
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $action;
    /**
     * @var Tag[]
     */
    private $tagsToRemove;

    /**
     * Event constructor.
     *
     * @param string $email
     * @param string $action
     * @param Tag[] $tagsToRemove
     */
    private function __construct($email, $action, array $tagsToRemove = array())
    {
        $this->email = $email;
        $this->action = $action;
        $this->tagsToRemove = $tagsToRemove;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return Tag[]
     */
    public function getTagsToRemove()
    {
        return $this->tagsToRemove;
    }

    /**
     * @param Tag[] $otherTagsToRemove
     *
     * @return void
     */
    public function mergeTagsToRemove(array $otherTagsToRemove)
    {
        if (empty($otherTagsToRemove)) {
            return;
        }

        $tagsToRemoveAsString = array_map(function (Tag $tag) {
            return (string)$tag;
        }, $this->getTagsToRemove());
        $otherTagsToRemoveAsString = array_map(function (Tag $tag) {
            return (string)$tag;
        }, $otherTagsToRemove);

        $this->tagsToRemove = Tag::fromBatch(array_unique(array_merge($tagsToRemoveAsString, $otherTagsToRemoveAsString)));
    }

    /**
     * @param Event $other
     *
     * @return bool
     */
    public function equals(Event $other)
    {
        return $this->getEmail() === $other->getEmail() &&
            $this->getAction() === $other->getAction() &&
            $this->tagsToRemoveAreEqual($other);
    }

    /**
     * Compares tags to remove from this and other event and returns true if tags collections are equal; false otherwise
     *
     * @param Event $other
     *
     * @return bool
     */
    private function tagsToRemoveAreEqual(Event $other)
    {
        if (count($this->getTagsToRemove()) !== count($other->getTagsToRemove())) {
            return false;
        }

        $tagsToRemoveAsString = array_map(function (Tag $tag) {
            return (string)$tag;
        }, $this->getTagsToRemove());
        $otherTagsToRemoveAsString = array_map(function (Tag $tag) {
            return (string)$tag;
        }, $other->getTagsToRemove());

        foreach ($tagsToRemoveAsString as $tagAsString) {
            if (!in_array($tagAsString, $otherTagsToRemoveAsString)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function subscriberCreated($email)
    {
        return new self($email, SyncActions::UPSERT_RECEIVER);
    }

    /**
     * @param string $email
     * @param Tag[] $tagsToRemove
     *
     * @return self
     */
    public static function subscriberUpdated($email, array $tagsToRemove = array())
    {
        return new self($email, SyncActions::UPSERT_RECEIVER, $tagsToRemove);
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function subscriberDeleted($email)
    {
        return new self($email, SyncActions::DELETE_RECEIVER);
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function subscriberSubscribed($email)
    {
        return new self($email, SyncActions::SUBSCRIBE_RECEIVER);
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function subscriberUnsubscribed($email)
    {
        return new self($email, SyncActions::UNSUBSCRIBE_RECEIVER);
    }

    /**
     * @param string $oldEmail
     * @param string $newEmail
     *
     * @return self[]
     */
    public static function subscriberEmailChanged($oldEmail, $newEmail)
    {
        return array(
            new self($oldEmail, SyncActions::DELETE_RECEIVER),
            new self($newEmail, SyncActions::UPSERT_RECEIVER),
        );
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function contactCreated($email)
    {
        return new self($email, SyncActions::UPSERT_RECEIVER);
    }

    /**
     * @param string $email
     * @param Tag[] $tagsToRemove
     *
     * @return self
     */
    public static function contactUpdated($email, array $tagsToRemove = array())
    {
        return new self($email, SyncActions::UPSERT_RECEIVER, $tagsToRemove);
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function contactDeleted($email)
    {
        return new self($email, SyncActions::DELETE_RECEIVER);
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function buyerCreated($email)
    {
        return new self($email, SyncActions::UPSERT_RECEIVER);
    }

    /**
     * @param string $email
     * @param Tag[] $tagsToRemove
     *
     * @return self
     */
    public static function buyerUpdated($email, array $tagsToRemove = array())
    {
        return new self($email, SyncActions::UPSERT_RECEIVER, $tagsToRemove);
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public static function buyerDeleted($email)
    {
        return new self($email, SyncActions::DELETE_RECEIVER);
    }

    /**
     * @param EventsBufferEntity $entity
     *
     * @return self
     */
    public static function fromEntity(EventsBufferEntity $entity)
    {
        return new self($entity->getEmail(), $entity->getSyncAction(), $entity->getTagsToRemove());
    }

    /**
     * @return EventsBufferEntity
     */
    public function toEntity()
    {
        return EventsBufferEntity::fromArray(array(
            'email' => $this->getEmail(),
            'syncAction' => $this->getAction(),
            'tagsToRemove' => $this->getTagsToRemove(),
        ));
    }
}
