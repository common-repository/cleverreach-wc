<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class Survey
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO
 */
class Survey extends DataTransferObject
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var PollData
     */
    protected $meta;
    /**
     * @var string
     */
    protected $layout;
    /**
     * @var NPS
     */
    protected $nps;
    /**
     * @var string
     */
    protected $template;
    /**
     * @var string
     */
    protected $token;

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\PollData
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\PollData $meta
     *
     * @return void
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     *
     * @return void
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\NPS
     */
    public function getNps()
    {
        return $this->nps;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\NPS $nps
     *
     * @return void
     */
    public function setNps($nps)
    {
        $this->nps = $nps;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return void
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'meta' => $this->meta->toArray(),
            'layout' => $this->layout,
            'template' => $this->template,
            'token' => $this->token
        );
    }

    /**
     * @inheritDoc
     *
     * @return Survey
     */
    public static function fromArray(array $data)
    {
        $survey = new static();
        $survey->meta = PollData::fromArray(static::getDataValue($data, 'meta', array()));
        $survey->layout = static::getDataValue($data, 'layout');
        $survey->nps = NPS::fromArray(static::getDataValue($data, 'nps', array()));
        $survey->template = static::getDataValue($data, 'template');
        $survey->token = static::getDataValue($data, 'token');

        return $survey;
    }
}
