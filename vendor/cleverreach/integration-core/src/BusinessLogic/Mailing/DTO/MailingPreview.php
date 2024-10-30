<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class MailingPreview
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO
 */
class MailingPreview extends DataTransferObject
{
    /**
     * @var mixed[]
     */
    protected $receivers;
    /**
     * @var string
     */
    protected $previewText;

    /**
     * MailingPreview constructor.
     *
     * @param mixed[] $receivers
     * @param string $previewText
     */
    public function __construct(array $receivers, $previewText = '')
    {
        $this->receivers = $receivers;
        $this->previewText = $previewText;
    }

    /**
     * @return mixed[]
     */
    public function getReceivers()
    {
        return $this->receivers;
    }

    /**
     * @return string
     */
    public function getPreviewText()
    {
        return $this->previewText;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'receivers' => $this->receivers,
            'previewText' => $this->previewText,
        );
    }
}
