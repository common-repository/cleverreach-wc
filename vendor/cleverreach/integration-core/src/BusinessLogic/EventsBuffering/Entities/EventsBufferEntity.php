<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;

/**
 * Class EventsBufferEntity
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities
 */
class EventsBufferEntity extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * @var string[]
     */
    protected $fields = array(
        'id',
        'email',
        'syncAction',
    );
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $syncAction;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag[]
     */
    protected $tagsToRemove;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getSyncAction()
    {
        return $this->syncAction;
    }

    /**
     * @param string $syncAction
     *
     * @return void
     */
    public function setSyncAction($syncAction)
    {
        $this->syncAction = $syncAction;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag[]
     */
    public function getTagsToRemove()
    {
        return $this->tagsToRemove;
    }

    /**
     * @param Tag[] $tagsToRemove
     *
     * @return void
     */
    public function setTagsToRemove(array $tagsToRemove)
    {
        $this->tagsToRemove = $tagsToRemove;
    }

    public function getConfig()
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('email');
        $indexMap->addStringIndex('syncAction');

        return new EntityConfiguration($indexMap, 'EventsBufferEntity');
    }

    public function inflate(array $data)
    {
        parent::inflate($data);

        $this->setTagsToRemove(Tag::fromBatch(Tag::getDataValue($data, 'tagsToRemove', array())));
    }

    public function toArray()
    {
        $data = parent::toArray();

        $data['tagsToRemove'] = array_map(static function (Tag $tag) {
            return (string)$tag;
        }, $this->getTagsToRemove());

        return $data;
    }
}
