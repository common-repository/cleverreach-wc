<?php

/** @noinspection DuplicatedCode */

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;

/**
 * Class AbandonedCart
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO
 */
class AbandonedCart extends DataTransferObject
{
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var string
     */
    protected $returnUrl;
    /**
     * @var float
     */
    protected $total;
    /**
     * @var float
     */
    protected $vat;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\CartItem[]
     */
    protected $cartItems;

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
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     *
     * @return void
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     *
     * @return void
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     *
     * @return void
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return void
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\CartItem[]
     */
    public function getCartItems()
    {
        return $this->cartItems;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\CartItem[] $cartItems
     *
     * @return void
     */
    public function setCartItems($cartItems)
    {
        $this->cartItems = $cartItems;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'storeId' => $this->getStoreId(),
            'returnUrl' => $this->getReturnUrl(),
            'total' => $this->getTotal(),
            'vat' => $this->getVat(),
            'currency' => $this->getCurrency(),
            'cartItems' => Transformer::batchTransform($this->getCartItems()),
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\AbandonedCart
     */
    public static function fromArray(array $data)
    {
        $entity = new static();
        $entity->setStoreId(self::getDataValue($data, 'storeId'));
        $entity->setReturnUrl(self::getDataValue($data, 'returnUrl'));
        $entity->setTotal(self::getDataValue($data, 'total', 0.0));
        $entity->setVat(self::getDataValue($data, 'vat', 0.0));
        $entity->setCurrency(self::getDataValue($data, 'currency'));
        $entity->setCartItems(CartItem::fromBatch(self::getDataValue($data, 'cartItems', array())));

        return $entity;
    }
}
