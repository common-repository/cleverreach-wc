<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class EventRegisterResult
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO
 */
class EventRegisterResult extends DataTransferObject
{
    /**
     * @var bool
     */
    private $success;
    /**
     * @var string
     */
    private $callToken;
    /**
     * @var string
     */
    private $secret;

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return void
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getCallToken()
    {
        return $this->callToken;
    }

    /**
     * @param string $callToken
     *
     * @return void
     */
    public function setCallToken($callToken)
    {
        $this->callToken = $callToken;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     *
     * @return void
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'success' => $this->isSuccess(),
            'call_token' => $this->getCallToken(),
            'secret' => $this->getSecret(),
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult
     */
    public static function fromArray(array $data)
    {
        $entity = new static();
        $entity->setSuccess(static::getDataValue($data, 'success'));
        $entity->setCallToken(static::getDataValue($data, 'call_token'));
        $entity->setSecret(static::getDataValue($data, 'secret'));

        return $entity;
    }
}
