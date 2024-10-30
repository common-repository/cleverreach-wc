<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data\TimestampsAware;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;

/**
 * Class Receiver
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO
 */
class Receiver extends TimestampsAware
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $source;
    /**
     * @var \DateTime|string|null
     */
    protected $activated;
    /**
     * @var \DateTime
     */
    protected $registered;
    /**
     * @var \DateTime|string|null
     */
    protected $deactivated;
    /**
     * List of receiver tags.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag[]
     */
    protected $tags;
    /**
     * List of modifiers that will be applied to field(s) when updating receiver.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier[]
     */
    protected $modifiers;
    /**
     * List of order items.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem[]
     */
    protected $orderItems;
    /**
     * Flag that indicates whether is a receiver active
     * Read only, to change active/inactive status, please use activated/deactivated properties
     *
     * @var bool
     */
    protected $active;
    /**
     * @var array<string,mixed>
     */
    protected $globalAttributes = array();

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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @return \DateTime | null | string
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * @param \DateTime | string $activated
     *
     * @return void
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;
    }

    /**
     * @return \DateTime | null
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * @param \DateTime $registered | null
     *
     * @return void
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;
    }

    /**
     * @return \DateTime | null | string
     */
    public function getDeactivated()
    {
        return $this->deactivated;
    }

    /**
     * @param \DateTime | string $deactivated
     *
     * @return void
     */
    public function setDeactivated($deactivated)
    {
        $this->deactivated = $deactivated;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->getGlobalAttribute('salutation');
    }

    /**
     * @param string $salutation
     *
     * @return void
     */
    public function setSalutation($salutation)
    {
        $this->setGlobalAttribute('salutation', $salutation);
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->getGlobalAttribute('firstname');
    }

    /**
     * @param string $firstName
     *
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->setGlobalAttribute('firstname', $firstName);
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->getGlobalAttribute('lastname');
    }

    /**
     * @param string $lastName
     *
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->setGlobalAttribute('lastname', $lastName);
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->getGlobalAttribute('street');
    }

    /**
     * @param string $street
     *
     * @return void
     */
    public function setStreet($street)
    {
        $this->setGlobalAttribute('street', $street);
    }

    /**
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->getGlobalAttribute('streetnumber');
    }

    /**
     * @param string $streetNumber
     *
     * @return void
     */
    public function setStreetNumber($streetNumber)
    {
        $this->setGlobalAttribute('streetnumber', $streetNumber);
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->getGlobalAttribute('zip');
    }

    /**
     * @param string $zip
     *
     * @return void
     */
    public function setZip($zip)
    {
        $this->setGlobalAttribute('zip', $zip);
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->getGlobalAttribute('city');
    }

    /**
     * @param string $city
     *
     * @return void
     */
    public function setCity($city)
    {
        $this->setGlobalAttribute('city', $city);
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->getGlobalAttribute('company');
    }

    /**
     * @param string $company
     *
     * @return void
     */
    public function setCompany($company)
    {
        $this->setGlobalAttribute('company', $company);
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->getGlobalAttribute('state');
    }

    /**
     * @param string $state
     *
     * @return void
     */
    public function setState($state)
    {
        $this->setGlobalAttribute('state', $state);
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->getGlobalAttribute('country');
    }

    /**
     * @param string $country
     *
     * @return void
     */
    public function setCountry($country)
    {
        $this->setGlobalAttribute('country', $country);
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthday()
    {
        if ($this->getGlobalAttribute('birthday')) {
            return static::getDate($this->getGlobalAttribute('birthday'));
        }

        return null;
    }

    /**
     * @param \DateTime|null $birthday
     *
     * @return void
     */
    public function setBirthday($birthday)
    {
        $this->setGlobalAttribute('birthday', static::getTimestamp($birthday));
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->getGlobalAttribute('phone');
    }

    /**
     * @param string $phone
     *
     * @return void
     */
    public function setPhone($phone)
    {
        $this->setGlobalAttribute('phone', $phone);
    }

    /**
     * @return string
     */
    public function getShop()
    {
        return $this->getGlobalAttribute('shop');
    }

    /**
     * @param string $shop
     *
     * @return void
     */
    public function setShop($shop)
    {
        $this->setGlobalAttribute('shop', $shop);
    }

    /**
     * @return string
     */
    public function getCustomerNumber()
    {
        return $this->getGlobalAttribute('customernumber');
    }

    /**
     * @param string $customerNumber
     *
     * @return void
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->setGlobalAttribute('customernumber', $customerNumber);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->getGlobalAttribute('language');
    }

    /**
     * @param string $language
     *
     * @return void
     */
    public function setLanguage($language)
    {
        $this->setGlobalAttribute('language', $language);
    }

    /**
     * @return \DateTime|string|null
     */
    public function getLastOrderDate()
    {
        if ($this->getGlobalAttribute('lastorderdate')) {
            return static::getDate($this->getGlobalAttribute('lastorderdate'));
        }

        return '';
    }

    /**
     * @param \DateTime $lastOrderDate
     *
     * @return void
     */
    public function setLastOrderDate($lastOrderDate)
    {
        $this->setGlobalAttribute('lastorderdate', static::getTimestamp($lastOrderDate));
    }

    /**
     * @return int
     */
    public function getOrderCount()
    {
        return $this->getGlobalAttribute('ordercount');
    }

    /**
     * @param int $orderCount
     *
     * @return void
     */
    public function setOrderCount($orderCount)
    {
        $this->setGlobalAttribute('ordercount', $orderCount);
    }

    /**
     * @return string|float|int
     */
    public function getTotalSpent()
    {
        return $this->getGlobalAttribute('totalspent');
    }

    /**
     * @param string|float|int $totalSpent
     *
     * @return void
     */
    public function setTotalSpent($totalSpent)
    {
        $this->setGlobalAttribute('totalspent', $totalSpent);
    }

    /**
     * @return string
     */
    public function getMarketingOptInLevel()
    {
        return $this->getGlobalAttribute('marketingoptinlevel');
    }

    /**
     * @param string $marketingOptInLevel
     *
     * @return void
     */
    public function setMarketingOptInLevel($marketingOptInLevel)
    {
        $this->setGlobalAttribute('marketingoptinlevel', $marketingOptInLevel);
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag[] $tags
     *
     * @return void
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag $tag
     *
     * @return void
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * @param Tag[] | null $tags
     *
     * @return void
     */
    public function addTags($tags)
    {
        $tags = is_array($tags) ? $tags : array();

        if ($this->tags === null) {
            $this->tags = $tags;
        } else {
            $this->tags = array_merge($this->tags, $tags);
        }
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier[] $modifiers
     *
     * @return void
     */
    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier $modifier
     *
     * @return void
     */
    public function addModifier(Modifier $modifier)
    {
        $this->modifiers[] = $modifier;
    }

    /**
     * Adds modifiers.
     *
     * @param Modifier[] | null $modifiers
     *
     * @return void
     */
    public function addModifiers($modifiers)
    {
        $modifiers = is_array($modifiers) ? $modifiers : array();

        if ($this->modifiers === null) {
            $this->modifiers = $modifiers;
        } else {
            $this->modifiers = array_merge($modifiers, $this->modifiers);
        }
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem[]
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem[] $orderItems
     *
     * @return void
     */
    public function setOrderItems($orderItems)
    {
        $this->orderItems = $orderItems;
    }

    /**
     * Adds order item to the internal order items collection.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem $orderItem
     *
     * @return void
     */
    public function addOrderItem(OrderItem $orderItem)
    {
        $this->orderItems[] = $orderItem;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return void
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $rawData)
    {
        $receiver = new static();

        $receiver->setId(static::getDataValue($rawData, 'email', null));
        $receiver->setEmail(static::getDataValue($rawData, 'email', null));
        $receiver->setSource(static::getDataValue($rawData, 'source', null));
        $receiver->setActivated(static::getDate(static::getDataValue($rawData, 'activated', null)));
        $receiver->setRegistered(static::getDate(static::getDataValue($rawData, 'registered', null)));
        $receiver->setDeactivated(static::getDate(static::getDataValue($rawData, 'deactivated', null)));

        $receiver->setGlobalAttributes(static::getDataValue($rawData, 'global_attributes', array()));

        $receiver->setTags(Tag::fromBatch(static::getDataValue($rawData, 'tags', array())));
        $receiver->setModifiers(Modifier::fromBatch(static::getDataValue($rawData, 'modifiers', array())));
        $receiver->setOrderItems(OrderItem::fromBatch(static::getDataValue($rawData, 'orders', array())));

        $receiver->active = static::getDataValue($rawData, 'active', false);

        return $receiver;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $tags = array();
        if ($this->getTags() !== null) {
            foreach ($this->getTags() as $tag) {
                $tags[] = $tag->__toString();
            }
        }

        $result = array(
            'email' => $this->getEmail(),
            'source' => $this->getSource(),
            'activated' => static::getTimestamp($this->getActivated()),
            'registered' => static::getTimestamp($this->getRegistered()),
            'deactivated' => static::getTimestamp($this->getDeactivated()),
            'tags' => $tags,
            'modifiers' => Transformer::batchTransform($this->getModifiers()),
            'orders' => Transformer::batchTransform($this->getOrderItems()),
            'global_attributes' => $this->getGlobalAttributes(),
        );

        if (!empty($this->id)) {
            $result['id'] = $this->id;
        }

        return $result;
    }

    /**
     * Returns global attributes
     *
     * @return array<string,mixed>
     */
    public function getGlobalAttributes()
    {
        return $this->globalAttributes;
    }

    /**
     * @param array<string,mixed> $globalAttributes
     *
     * @return void
     */
    public function setGlobalAttributes(array $globalAttributes)
    {
        $this->globalAttributes = $globalAttributes;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setGlobalAttribute($key, $value)
    {
        $this->globalAttributes[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getGlobalAttribute($key)
    {
        return static::getDataValue($this->globalAttributes, $key, null);
    }
}
