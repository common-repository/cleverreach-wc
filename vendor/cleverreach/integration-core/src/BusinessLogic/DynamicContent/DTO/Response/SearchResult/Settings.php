<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class Settings
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter\SearchResult
 */
class Settings extends DataTransferObject
{
    const RSS = 'rss';
    const PRODUCT = 'product';
    const CONTENT = 'content';
    /**
     * @var string
     */
    protected $type;
    /**
     * @var bool
     */
    protected $linkEditable;
    /**
     * @var bool
     */
    protected $linkTextEditable;
    /**
     * @var bool
     */
    protected $imageSizeEditable;

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
        $allowedTypes = array(static::PRODUCT, static::CONTENT, static::RSS);
        if (!in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException("$type is not allowed. Allowed types: " . implode(', ', $allowedTypes));
        }

        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isLinkEditable()
    {
        return $this->linkEditable;
    }

    /**
     * @param bool $linkEditable
     *
     * @return void
     */
    public function setLinkEditable($linkEditable)
    {
        $this->linkEditable = $linkEditable;
    }

    /**
     * @return bool
     */
    public function isLinkTextEditable()
    {
        return $this->linkTextEditable;
    }

    /**
     * @param bool $linkTextEditable
     *
     * @return void
     */
    public function setLinkTextEditable($linkTextEditable)
    {
        $this->linkTextEditable = $linkTextEditable;
    }

    /**
     * @return bool
     */
    public function isImageSizeEditable()
    {
        return $this->imageSizeEditable;
    }

    /**
     * @param bool $imageSizeEditable
     *
     * @return void
     */
    public function setImageSizeEditable($imageSizeEditable)
    {
        $this->imageSizeEditable = $imageSizeEditable;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'type' => $this->type,
            'link_editable' => (bool)$this->linkEditable,
            'link_text_editable' => (bool)$this->linkTextEditable,
            'image_size_editable' => (bool)$this->imageSizeEditable,
        );
    }
}
