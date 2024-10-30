<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class UserInfo
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO
 */
class UserInfo extends DataTransferObject
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $firstName;
    /**
     * @var string
     */
    protected $lastName;
    /**
     * @var string
     */
    protected $salutation;
    /**
     * @var string
     */
    protected $street;
    /**
     * @var string
     */
    protected $company;
    /**
     * @var string
     */
    protected $zip;
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $phone;
    /**
     * @var string
     */
    protected $fax;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $loginDomain;
    /**
     * @var string
     */
    protected $whiteLabelId;
    /**
     * @var string
     */
    protected $language;

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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param string $salutation
     *
     * @return void
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return void
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     *
     * @return void
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     *
     * @return void
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return void
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     *
     * @return void
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
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
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getLoginDomain()
    {
        return $this->loginDomain;
    }

    /**
     * @param string $loginDomain
     *
     * @return void
     */
    public function setLoginDomain($loginDomain)
    {
        $this->loginDomain = $loginDomain;
    }

    /**
     * @return string
     */
    public function getWhiteLabelId()
    {
        return $this->whiteLabelId;
    }

    /**
     * @param string $whiteLabelId
     *
     * @return void
     */
    public function setWhiteLabelId($whiteLabelId)
    {
        $this->whiteLabelId = $whiteLabelId;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return void
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getLastName(),
            'firstname' => $this->getFirstName(),
            'salutation' => $this->getSalutation(),
            'street' => $this->getStreet(),
            'zip' => $this->getZip(),
            'city' => $this->getCity(),
            'phone' => $this->getPhone(),
            'fax' => $this->getFax(),
            'email' => $this->getEmail(),
            'country' => $this->getCountry(),
            'company' => $this->getCompany(),
            'login_domain' => $this->getLoginDomain(),
            'whitelabel_id' => $this->getWhiteLabelId(),
            'lang' => $this->getLanguage(),
        );
    }

    /**
     * Instantiates user info from array.
     *
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\UserInfo
     */
    public static function fromArray(array $data)
    {
        $entity = new static();

        $entity->setId(static::getDataValue($data, 'id'));
        $entity->setLastName(static::getDataValue($data, 'name'));
        $entity->setFirstName(static::getDataValue($data, 'firstname'));
        $entity->setSalutation(static::getDataValue($data, 'salutation'));
        $entity->setStreet(static::getDataValue($data, 'street'));
        $entity->setCompany(static::getDataValue($data, 'company'));
        $entity->setZip(static::getDataValue($data, 'zip'));
        $entity->setCity(static::getDataValue($data, 'city'));
        $entity->setPhone(static::getDataValue($data, 'phone'));
        $entity->setFax(static::getDataValue($data, 'fax'));
        $entity->setEmail(static::getDataValue($data, 'email'));
        $entity->setCountry(static::getDataValue($data, 'country'));
        $entity->setLoginDomain(static::getDataValue($data, 'login_domain'));
        $entity->setWhiteLabelId(static::getDataValue($data, 'whitelabel_id'));
        $entity->setLanguage(static::getDataValue($data, 'lang'));

        return $entity;
    }
}
