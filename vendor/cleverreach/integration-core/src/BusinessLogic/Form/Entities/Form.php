<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;

class Form extends Entity
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var string[]
     */
    protected $fields = array(
        'id',
        'apiId',
        'context',
        'name',
        'customerTableId',
        'content',
    );

    /**
     * @var string
     */
    protected $apiId;
    /**
     * @var string
     */
    protected $context;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $customerTableId;
    /**
     * @var string
     */
    protected $content;

    /**
     * @return string
     */
    public function getApiId()
    {
        return $this->apiId;
    }

    /**
     * @param string $apiId
     *
     * @return void
     */
    public function setApiId($apiId)
    {
        $this->apiId = $apiId;
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
    public function getCustomerTableId()
    {
        return $this->customerTableId;
    }

    /**
     * @param string $customerTableId
     *
     * @return void
     */
    public function setCustomerTableId($customerTableId)
    {
        $this->customerTableId = $customerTableId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }


    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('apiId');
        $indexMap->addStringIndex('context');

        return new EntityConfiguration($indexMap, 'Form');
    }
}
