<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class PollAnswer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO
 */
class PollAnswer extends DataTransferObject
{
    /**
     * @var string
     */
    protected $poll;
    /**
     * @var int
     */
    protected $result;
    /**
     * @var string
     */
    protected $freetext;
    /**
     * @var string
     */
    protected $referer;
    /**
     * @var array<string,mixed>
     */
    protected $attributes = array();
    /**
     * @var string
     */
    protected $customerId;
    /**
     * @var int
     */
    protected $ignoreDuration;

    /**
     * @return string
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * @param string $poll
     *
     * @return void
     */
    public function setPoll($poll)
    {
        $this->poll = $poll;
    }

    /**
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param int $result
     *
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return string
     */
    public function getFreetext()
    {
        return $this->freetext;
    }

    /**
     * @param string $freetext
     *
     * @return void
     */
    public function setFreetext($freetext)
    {
        $this->freetext = $freetext;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @param string $referer
     *
     * @return void
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * @return array<string,mixed>
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array<string,mixed> $attributes
     *
     * @return void
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     *
     * @return void
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return int
     */
    public function getIgnoreDuration()
    {
        return $this->ignoreDuration;
    }

    /**
     * @param int $ignoreDuration
     *
     * @return void
     */
    public function setIgnoreDuration($ignoreDuration)
    {
        $this->ignoreDuration = $ignoreDuration;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $data = array(
            'poll' => $this->poll,
            'result' => $this->result,
            'freetext' => $this->freetext,
            'referer' => $this->referer,
            'attributes' => $this->attributes,
            'customer_id' => $this->customerId,
            'ignore_duration' => $this->ignoreDuration,
        );

        if ($this->result === null) {
            unset($data['result']);
        }

        return $data;
    }

    /**
     * @inheritDoc
     *
     * @return DataTransferObject An instance of the data transfer object.
     */
    public static function fromArray(array $data)
    {
        $answer = new static();
        $answer->poll = static::getDataValue($data, 'poll');
        $answer->result = static::getDataValue($data, 'result', null);
        $answer->freetext = static::getDataValue($data, 'freetext');
        $answer->referer = static::getDataValue($data, 'referer');
        $answer->customerId = static::getDataValue($data, 'customer_id');
        $answer->ignoreDuration = static::getDataValue($data, 'ignore_duration', 0);
        $answer->attributes = static::getDataValue($data, 'attributes', array());

        return $answer;
    }
}
