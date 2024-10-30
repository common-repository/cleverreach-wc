<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;

/**
 * Class CartAutomation
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities
 */
class CartAutomation extends Entity
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $context;
    /**
     * Store id that the automation is related to.
     *
     * @var string
     */
    protected $storeId;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $source;
    /**
     * @var string
     */
    protected $condition;
    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var boolean
     */
    protected $isActive;
    /**
     * @var string ['initialized' | 'creating' | 'created' | 'incomplete']
     */
    protected $status;
    /**
     * @var string
     */
    protected $webhookVerificationToken;
    /**
     * @var string
     */
    protected $webhookCallToken;
    /**
     * @var mixed[]
     */
    protected $settings;
    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array(
        'id',
        'context',
        'storeId',
        'name',
        'description',
        'source',
        'condition',
        'clientId',
        'type',
        'isActive',
        'status',
        'webhookVerificationToken',
        'webhookCallToken',
        'settings',
    );

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     *
     * @return void
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     *
     * @return void
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return void
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getWebhookVerificationToken()
    {
        return $this->webhookVerificationToken;
    }

    /**
     * @param string $webhookVerificationToken
     *
     * @return void
     */
    public function setWebhookVerificationToken($webhookVerificationToken)
    {
        $this->webhookVerificationToken = $webhookVerificationToken;
    }

    /**
     * @return string
     */
    public function getWebhookCallToken()
    {
        return $this->webhookCallToken;
    }

    /**
     * @param string $webhookCallToken
     *
     * @return void
     */
    public function setWebhookCallToken($webhookCallToken)
    {
        $this->webhookCallToken = $webhookCallToken;
    }

    /**
     * @return mixed[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param mixed[] $settings
     *
     * @return void
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Returns entity configuration object.
     *
     * @return EntityConfiguration Configuration object.
     */
    public function getConfig()
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('storeId');
        $indexMap->addStringIndex('condition');
        $indexMap->addStringIndex('context');
        $indexMap->addBooleanIndex('isActive');

        return new EntityConfiguration($indexMap, 'CartAutomation');
    }
}
