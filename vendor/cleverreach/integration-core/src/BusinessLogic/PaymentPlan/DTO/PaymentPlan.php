<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class PaymentPlan
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\DTO
 */
class PaymentPlan extends DataTransferObject
{
    const CURRENT_RATE_PATTERN = '{name} - {currency} {price} / {period}';
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $flatRate;
    /**
     * @var int
     */
    private $emails;
    /**
     * @var int
     */
    private $receiverCap;
    /**
     * @var string
     */
    private $runtime;
    /**
     * @var float
     */
    private $price;
    /**
     * @var string
     */
    private $currency;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
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
     * @return int
     */
    public function getFlatRate()
    {
        return $this->flatRate;
    }

    /**
     * @param int $flatRate
     *
     * @return void
     */
    public function setFlatRate($flatRate)
    {
        $this->flatRate = $flatRate;
    }

    /**
     * @return int
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param int $emails
     *
     * @return void
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }

    /**
     * @return int
     */
    public function getReceiverCap()
    {
        return $this->receiverCap;
    }

    /**
     * @param int $receiverCap
     *
     * @return void
     */
    public function setReceiverCap($receiverCap)
    {
        $this->receiverCap = $receiverCap;
    }

    /**
     * @return string
     */
    public function getRuntime()
    {
        return $this->runtime;
    }

    /**
     * @param string $runtime
     *
     * @return void
     */
    public function setRuntime($runtime)
    {
        $this->runtime = $runtime;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return void
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'flatrate' => $this->getFlatRate(),
            'emails' => $this->getEmails(),
            'receiver_cap' => $this->getReceiverCap(),
            'runtime' => $this->getRuntime(),
            'price' => $this->getPrice(),
            'currency' => $this->getCurrency(),
        );
    }

    /**
     * @inheritDoc
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        $entity = new static();

        $entity->setId(static::getDataValue($data, 'id'));
        $entity->setName(static::getDataValue($data, 'name'));
        $entity->setFlatRate(static::getDataValue($data, 'flatrate', 0));
        $entity->setEmails(static::getDataValue($data, 'emails', 0));
        $entity->setReceiverCap(static::getDataValue($data, 'receiver_cap', 0));
        $entity->setRuntime(static::getDataValue($data, 'runtime'));
        $entity->setPrice(static::getDataValue($data, 'price', 0.0));
        $entity->setCurrency(static::getDataValue($data, 'currency'));

        return $entity;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        // todo resolve period

        $params = array(
            '{name}' => $this->getName(),
            '{currency}' => $this->getCurrency(),
            '{price}' => number_format((float)$this->getPrice(), 2),
            '{period}' => 'Per month'
        );

        return strtr(static::CURRENT_RATE_PATTERN, $params);
    }
}
