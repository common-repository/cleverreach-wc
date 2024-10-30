<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class DynamicContent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO
 */
class DynamicContent extends DataTransferObject
{
    const NAME_FORMAT = '{label} - ({shopName})';

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var bool
     */
    protected $cors;
    /**
     * @var string
     */
    protected $icon;

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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
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
    public function isCors()
    {
        return $this->cors;
    }

    /**
     * @param bool $cors
     *
     * @return void
     */
    public function setCors($cors)
    {
        $this->cors = $cors;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     *
     * @return void
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'password' => $this->password,
            'type' => $this->type,
            'cors' => $this->cors,
            'icon' => $this->icon,
        );
    }

    /**
     * @inheritDoc
     *
     * @return DynamicContent
     */
    public static function fromArray(array $data)
    {
        $content = new static();

        $content->id = static::getDataValue($data, 'id', null);
        $content->name = static::getDataValue($data, 'name');
        $content->password = static::getDataValue($data, 'password');
        $content->url = static::getDataValue($data, 'url');
        $content->type = static::getDataValue($data, 'type');
        $content->icon = static::getDataValue($data, 'icon');
        $content->cors = static::getDataValue($data, 'cors', false);

        return $content;
    }

    /**
     * Returns name in uniform format
     *
     * @param string $shopName
     * @param string $label
     *
     * @return string
     */
    public static function formatName($shopName, $label)
    {
        $params = array(
            '{shopName}' => $shopName,
            '{label}' => $label,
        );

        return strtr(static::NAME_FORMAT, $params);
    }
}
