<?php
/**
 * This file contains the implementation of the FundingOrganization entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage FundingOrganization
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent funding organizations.
 *
 * @Assert\UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="A Funding Organization with this name already exists"
 * )
 */
class FundingOrganization extends Entity
{
    /**
     * Name of a funding organization.
     *
     * @var string $name
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Name cannot contain angle brackets (< or >)"
     * )
     */
    protected $name;

    /**
     * Funding organization's logo.
     *
     * @var string|resource $logo
     *
     * @access protected
     */
    protected $logo;

    /**
     * Funding organization's email address.
     *
     * @var string $emailAddress
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid"
     * )
     */
    protected $emailAddress;

    /**
     * Description of a funding organization.
     *
     * @var string $description
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    protected $description;

    /**
     * Funding organization's Website url.
     *
     * @var string $url
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Funding organization's telephone number.
     *
     * @var string $phoneNumber
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     */
    protected $phoneNumber;

    /**
     * Funding organization's delivery point (street address).
     *
     * @var string $deliveryPoint
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Delievery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    protected $deliveryPoint;

    /**
     * Funding organization's city.
     *
     * @var string $city
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    protected $city;

    /**
     * Funding organization's administrative area (state).
     *
     * @var string $administrativeArea
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    protected $administrativeArea;

    /**
     * Funding organization's postal code (zipcode).
     *
     * @var string $postalCode
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    protected $postalCode;

    /**
     * Funding organization's country.
     *
     * @var string $country
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;

    /**
     * Funding organization's Funding Cycle's.
     *
     * @var FundingCycle
     *
     * @access protected
     */
    protected $fundingCycles;

    /**
     * Getter for fundingCycles.
     *
     * @access public
     *
     * @return string String containing fundingCycles of funding organization.
     */
    public function getFundingCycles()
    {
        return $this->fundingCycles;
    }

    /**
     * Setter for fundingCycles.
     *
     * @param array|\Traversable $fundingCycles Set of FundingCycle objects.
     *
     * @access public
     *
     * @throws \Exception When Non-funding cycle found in $fundingCycles.
     * @throws \Exception When $fundingCycles is not an array or traversable object.
     *
     * @return void
     */
    public function setFundingCycles($fundingCycles)
    {
        if (is_array($fundingCycles) || $fundingCycles instanceof \Traversable) {
            $this->fundingCycles = $fundingCycles;
            foreach ($fundingCycles as $fundingCycle) {
                if (!$fundingCycle instanceof FundingCycle) {
                    throw new \Exception('Non-funding cycle found in FundingCycles');
                }
                $fundingCycle->setFundingOrganization($this);
            }
        } else {
            throw new \Exception('Funding Cycles must be array or traversable objects');
        }
    }

    /**
     * Setter for name.
     *
     * @param string $name Textual name of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter for name.
     *
     * @access public
     *
     * @return string String containing name of funding organization.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for logo.
     *
     * @param string|resource $logo Containing byte string of logo.
     *
     * @access public
     *
     * @return void
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Getter for logo.
     *
     * @param boolean $asStream Whether to return the logo as a stream.
     *
     * @access public
     *
     * @return string|resource Binary string containing the logo or a stream resource pointing to it.
     */
    public function getLogo($asStream = false)
    {
        if ($asStream) {
            if (is_resource($this->logo) and get_resource_type($this->logo) == 'stream') {
                return $this->logo;
            } else {
                return null;
            }
        }
        if (is_resource($this->logo) and get_resource_type($this->logo) == 'stream') {
            rewind($this->logo);
            return stream_get_contents($this->logo);
        }
        return $this->logo;
    }

    /**
     * Get the mime type of logo.
     *
     * @access public
     *
     * @return string The mime type of logo.
     */
    public function getLogoMimeType()
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($this->getLogo());
    }

    /**
     * Setter for emailAddress.
     *
     * @param string $emailAddress Containing email address of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Getter for emailAddress.
     *
     * @access public
     *
     * @return string Containing emailADdress.
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Setter for description.
     *
     * @param string $description Description of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Getter for description.
     *
     * @access public
     *
     * @return string Description of funding organization.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for url.
     *
     * @param string $url Funding organization's Website URL.
     *
     * @access public
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Getter for url.
     *
     * @access public
     *
     * @return string URL of funding organization's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string $phoneNumber Funding organization's phone number.
     *
     * @access public
     *
     * @return void
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Getter for phoneNumber.
     *
     * @access public
     *
     * @return string Phone number of funding organization.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string $deliveryPoint Street address of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setDeliveryPoint($deliveryPoint)
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    /**
     * Getter for deliveryPoint.
     *
     * @access public
     *
     * @return string Street address of funding organization.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string $city City of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Getter for city.
     *
     * @access public
     *
     * @return string City of funding organization.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string $administrativeArea Funding organization's administrative area (state).
     *
     * @access public
     *
     * @return void
     */
    public function setAdministrativeArea($administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;
    }

    /**
     * Getter for administrativeArea.
     *
     * @access public
     *
     * @return string Funding organization's administrative area (state).
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * Setter for postalCode.
     *
     * @param string $postalCode Postal (zip) code.
     *
     * @access public
     *
     * @return void
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Getter for postalCode.
     *
     * @access public
     *
     * @return string Containing postal (zip) code.
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Setter for country.
     *
     * @param string $country Funding organization's country.
     *
     * @access public
     *
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Getter for country.
     *
     * @access public
     *
     * @return string Funding organization's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'name' => array(
            'type' => 'string',
            'getter' => 'getName',
            'setter' => 'setName',
        ),
        'logo' => array(
            'type' => 'string',
            'getter' => 'getLogo',
            'setter' => 'setLogo',
            'serializer' => 'serializeBinary',
        ),
        'emailAddress' => array(
            'type' => 'string',
            'getter' => 'getEmailAddress',
            'setter' => 'setEmailAddress',
        ),
        'description' => array(
            'type' => 'string',
            'getter' => 'getDescription',
            'setter' => 'setDescription',
        ),
        'url' => array(
            'type' => 'string',
            'getter' => 'getUrl',
            'setter' => 'setUrl',
        ),
        'phoneNumber' => array(
            'type' => 'string',
            'getter' => 'getPhoneNumber',
            'setter' => 'setPhoneNumber',
        ),
        'deliveryPoint' => array(
            'type' => 'string',
            'getter' => 'getDeliveryPoint',
            'setter' => 'setDeliveryPoint',
        ),
        'city' => array(
            'type' => 'string',
            'getter' => 'getCity',
            'setter' => 'setCity',
        ),
        'administrativeArea' => array(
            'type' => 'string',
            'getter' => 'getAdministrativeArea',
            'setter' => 'setAdministrativeArea',
        ),
        'postalCode' => array(
            'type' => 'string',
            'getter' => 'getPostalCode',
            'setter' => 'setPostalCode',
        ),
        'country' => array(
            'type' => 'string',
            'getter' => 'getCountry',
            'setter' => 'setCountry',
        ),
        'fundingCycles' => array(
            'type' => 'fundingCycle',
            'getter' => 'getFundingCycles',
            'setter' => 'setFundingCycles',
            'serialize' => false,
        )
    );

    /**
     * Method that returns a FundingOrganization's properties as an array.
     *
     * Default is to not localize time stamps.
     *
     * @param array   $properties         An array listing the properties to include.
     * @param boolean $localizeTimeStamps A flag to inidcate whether or not to localize time stamps.
     *
     * @return array An array of property values for this FundingOrganization.
     */
    public function asArray(array $properties, $localizeTimeStamps = false)
    {
        $personArray = array();
        foreach ($properties as $property) {
            switch ($property) {
                case 'id':
                    $personArray[] = $this->getId();
                    break;
                case 'name':
                    $personArray[] = $this->getName();
                    break;
                case 'description':
                    $personArray[] = $this->getDescription();
                    break;
                case 'phoneNumber':
                    $personArray[] = $this->getPhoneNumber();
                    break;
                case 'emailAddress':
                    $personArray[] = $this->getEmailAddress();
                    break;
                case 'url':
                    $personArray[] = $this->getUrl();
                    break;
                case 'administrativeArea':
                    $personArray[] = $this->getAdministrativeArea();
                    break;
                case 'postalCode':
                    $personArray[] = $this->getPostalCode();
                    break;
                case 'country':
                    $personArray[] = $this->getCountry();
                    break;
                case 'logo':
                    $personArray[] = $this->getLogo();
                    break;
                case 'creationTimeStamp':
                    $personArray[] = $this->getCreationTimeStamp($localizeTimeStamps);
                    break;
                case 'creator':
                    $personArray[] = $this->getCreator();
                    break;
                case 'modificationTimeStamp':
                    $personArray[] = $this->getModificationTimeStamp($localizeTimeStamps);
                    break;
                case 'modifier':
                    $personArray[] = $this->getModifier();
                    break;
                case 'city':
                    $personArray[] = $this->getCity();
                    break;
                case 'deliveryPoint':
                    $personArray[] = $this->getDeliveryPoint();
                    break;
            }
        }
        return $personArray;
    }
}
