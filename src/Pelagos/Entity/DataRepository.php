<?php
/**
 * This file contains the implementation of the DataRepository entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage DataRepository
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use \Pelagos\Exception\NotDeletableException;

/**
 * Class to represent a Data Repository.
 *
 * @Assert\UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="A Data Repository with this name already exists"
 * )
 */
class DataRepository extends Entity
{
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
        'personDataRepositories' => array(
            'type' => 'object',
            'class' => '\Doctrine\Common\Collections\Collection',
            'getter' => 'getPersonDataRepositories',
            'setter' => 'setPersonDataRepositories',
            'serialize' => false,
        ),
        'fundingOrganizations' => array(
            'type' => 'object',
            'class' => '\Doctrine\Common\Collections\Collection',
            'getter' => 'getFundingOrganizations',
         //   'setter' => 'setFundingOrganizations',
            'serialize' => false,
        ),
    );

    /**
     * Name of a Data Repository.
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
     * Data Repository's email address.
     *
     * @var string $emailAddress
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Email address is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid"
     * )
     */
    protected $emailAddress;

    /**
     * Description of a Data Repository.
     *
     * @var string $description
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Description is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    protected $description;

    /**
     * Data Repository's Website url.
     *
     * @var string $url
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Website URL is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Data Repository's telephone number.
     *
     * @var string $phoneNumber
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Phone number is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     */
    protected $phoneNumber;

    /**
     * Data Repository's delivery point (street address).
     *
     * @var string $deliveryPoint
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Delivery point is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Delivery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    protected $deliveryPoint;

    /**
     * Data Repository's city.
     *
     * @var string $city
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="City is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    protected $city;

    /**
     * Data Repository's administrative area (state).
     *
     * @var string $administrativeArea
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Administrative area (state) is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    protected $administrativeArea;

    /**
     * Data Repository's postal code (zipcode).
     *
     * @var string $postalCode
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Postal code (zip) is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    protected $postalCode;

    /**
     * Data Repository's country.
     *
     * @var string $country
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Country is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;

    /**
     * Data Repository's relationship with Persons.
     *
     * @var \Doctrine\Common\Collections\Collection $personDataRepositories
     *
     * @access protected
     */
    protected $personDataRepositories;

    /**
     * Data Repositories collection of Funding Organization.
     *
     * @var FundingOrganizations
     *
     * @access protected
     */
    protected $fundingOrganizations;

    /**
     * Setter for name.
     *
     * @param string $name Name of Data Repository.
     *
     * @access public Assign a name to object
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
     * @return string String containing name of Data Repository.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for emailAddress.
     *
     * @param string $emailAddress Containing email address of Data Repository.
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
     * @param string $description Description of Data Repository.
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
     * @return string Description of Data Repository.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for url.
     *
     * @param string $url Data Repository's Website URL.
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
     * @return string URL of Data Repository's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string $phoneNumber Data Repository's phone number.
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
     * @return string Phone number of Data Repository.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string $deliveryPoint Street address of Data Repository.
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
     * @return string Street address of Data Repository.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string $city City of Data Repository.
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
     * @return string City of Data Repository.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string $administrativeArea Data Repository's administrative area (state).
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
     * @return string Data Repository's administrative area (state).
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
     * @param string $country Data Repository's country.
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
     * @return string Data Repository's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for personDataRepositories.
     *
     * @param array|\Traversable $personDataRepositories Set of PersonDataRepository objects.
     *
     * @access public
     *
     * @throws \Exception When $personDataRepositories is not an array or traversable object.
     * @throws \Exception When Non-PersonDataRepository found within $personDataRepositories.
     *
     * @return void
     */
    public function setPersonDataRepositories($personDataRepositories)
    {
        if (is_array($personDataRepositories) || $personDataRepositories instanceof \Traversable) {
            foreach ($personDataRepositories as $personDataRepository) {
                if (!$personDataRepository instanceof PersonDataRepository) {
                    throw new \Exception('Non-PersonDataRepository found in personDataRepositories.');
                }
            }
            $this->personDataRepositories = $personDataRepositories;
            foreach ($this->personDataRepositories as $personDataRepository) {
                $personDataRepository->setDataRepository($this);
            }
        } else {
            throw new \Exception('personDataRepositories must be either array or traversable objects.');
        }
    }

    /**
     * Getter for personDataRepositories.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing personDataRepositories
     *                                                 listings for this Data Repository.
     */
    public function getPersonDataRepositories()
    {
        return $this->personDataRepositories;
    }

    /**
     * Setter for fundingOrganizations.
     *
     * @param array|\Traversable $fundingOrganizations Set of FundingOrganization objects.
     *
     * @access public
     *
     * @return void
     */
    public function addFundingOrganization(FundingOrganization $fo) {
        if($this->fundingOrganizations == null) {
            $this->fundingOrganizations = array();
        }
        if(!in_array($fo,$this->fundingOrganizations)) {
            $this->fundingOrganizations[] = $fo;
        }
    }
    /**
     * Setter for fundingOrganizations.
     *
     * @param array|\Traversable $fundingOrganizations Set of FundingOrganization objects.
     *
     * @access public
     *
     * @throws \Exception When $fundingOrganizations is not an array or traversable object.
     * @throws \Exception When Non-FundingOrganization found within $fundingOrganizations.
     *
     * @return void
     */
    public function setFundingOrganizations($fundingOrganizations)
    {
        if (is_array($fundingOrganizations) || $fundingOrganizations instanceof \Traversable) {
            foreach ($fundingOrganizations as $fundingOrganization) {
                if (!$fundingOrganization instanceof FundingOrganization) {
                    throw new \Exception('Non-FundingOrganization found in fundingOrganizations.');
                }
            }
            $this->fundingOrganizations = $fundingOrganizations;
            foreach ($this->fundingOrganizations as $fundingOrganization) {
                $fundingOrganization->setDataRepository($this);
            }
        } else {
            throw new \Exception('fundingOrganizations must be either array or traversable objects.');
        }
    }
    /**
     * Getter for fundingOrganizations.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing FundingOrganization
     *                                                 listings for this Data Repository.
     */
    public function getFundingOrganizations()
    {
        return $this->fundingOrganizations;
    }
}
