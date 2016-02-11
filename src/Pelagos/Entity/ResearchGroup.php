<?php
/**
 * This file contains the implementation of the ResearchGroup entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage ResearchGroup
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use \Pelagos\Exception\NotDeletableException;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class to represent research groups.
 *
 * @Assert\UniqueEntity(
 *     fields={"name", "fundingCycle"},
 *     errorPath="name",
 *     message="A Research Group with this name already exists"
 * )
 */
class ResearchGroup extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Research Group';

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     *
     * @Serializer\Exclude
     */
    protected static $properties = array(
        'name' => array(
            'type' => 'string',
            'getter' => 'getName',
            'setter' => 'setName',
        ),
        'fundingCycle' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\FundingCycle',
            'entity' => 'FundingCycle',
            'setter' => 'setFundingCycle',
            'getter' => 'getFundingCycle',
            'serialize' => true,
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
        'description' => array(
            'type' => 'string',
            'getter' => 'getDescription',
            'setter' => 'setDescription',
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
        'personResearchGroups' => array(
            'type' => 'object',
            'class' => '\Doctrine\Common\Collections\Collection',
            'getter' => 'getPersonResearchGroups',
            'setter' => 'setPersonResearchGroups',
            'serialize' => false,
        ),
    );

    /**
     * Name of a research group.
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
     * Research group's parent Funding Cycle.
     *
     * @var FundingCycle $fundingCycle
     *
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Funding Cycle is required"
     * )
     *
     * @Serializer\MaxDepth(1)
     */
    protected $fundingCycle;

    /**
     * Research group's Website url.
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
     * Research group's telephone number.
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
     * Research group's delivery point (street address).
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
     * Research group's city.
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
     * Research group's administrative area (state).
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
     * Research group's postal code (zipcode).
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
     * Research group's country.
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
     * Description of a research group.
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
     * Research group's logo.
     *
     * @var string|resource $logo
     *
     * @access protected
     *
     * @Serializer\Exclude
     */
    protected $logo;

    /**
     * Research group's email address.
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
     * Research group's PersonResearchGroups.
     *
     * @var \Doctrine\Common\Collections\Collection $personResearchGroups
     *
     * @access protected
     *
     * @Serializer\MaxDepth(2)
     */
    protected $personResearchGroups;

    /**
     * Setter for name.
     *
     * @param string $name Textual name of research group.
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
     * @return string String containing name of research group.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for fundingCycle.
     *
     * @param FundingCycle $fundingCycle The FundingCycle to associate this ResearchGroup with.
     *
     * @access public
     *
     * @return void
     */
    public function setFundingCycle(FundingCycle $fundingCycle = null)
    {
        $this->fundingCycle = $fundingCycle;
    }

    /**
     * Getter for fundingCycles.
     *
     * @access public
     *
     * @return string String containing fundingCycles of research group.
     */
    public function getFundingCycle()
    {
        return $this->fundingCycle;
    }

    /**
     * Setter for url.
     *
     * @param string $url Research group's Website URL.
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
     * @return string URL of research group's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string $phoneNumber Research group's phone number.
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
     * @return string Phone number of research group.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string $deliveryPoint Street address of research group.
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
     * @return string Street address of research group.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string $city City of research group.
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
     * @return string City of research group.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string $administrativeArea Research group's administrative area (state).
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
     * @return string Research group's administrative area (state).
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
     * @param string $country Research group's country.
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
     * @return string Research group's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for description.
     *
     * @param string $description Description of research group.
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
     * @return string Description of research group.
     */
    public function getDescription()
    {
        return $this->description;
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
     * Setter for emailAddress.
     *
     * @param string $emailAddress Containing email address of research group.
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
     * Setter for personResearchGroups.
     *
     * @param array|\Traversable $personResearchGroups Set of PersonResearchGroup objects.
     *
     * @access public
     *
     * @throws \Exception When Non-PersonResearchGroup found in $personResearchGroups.
     * @throws \Exception When $personResearchGroups is not an array or traversable object.
     *
     * @return void
     */
    public function setPersonResearchGroups($personResearchGroups)
    {
        if (is_array($personResearchGroups) || $personResearchGroups instanceof \Traversable) {
            $this->personResearchGroups = $personResearchGroups;
            foreach ($personResearchGroups as $personResearchGroup) {
                if (!$personResearchGroup instanceof PersonResearchGroup) {
                    throw new \Exception('Non-PersonResearchGroup found in personResearchGroups.');
                }
                $personResearchGroup->setResearchGroup($this);
            }
        } else {
            throw new \Exception('personResearchGroups must be either array or traversable objects.');
        }
    }

    /**
     * Getter for personResearchGroups.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing personResearchGroups
     *                                                 listings for this research group.
     */
    public function getPersonResearchGroups()
    {
        return $this->personResearchGroups;
    }

    /**
     * Check if this ResearchGroup is deletable.
     *
     * This method throws a NotDeletableException when the ResearchGroup has associated
     * Persons. The NotDeletableException will have its reasons set to a list of
     * reasons the ResearchGroup is not deletable.
     *
     * @throws NotDeletableException When the ResearchGroup has associated Persons.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();
        $personResearchGroupCount = count($this->getPersonResearchGroups());
        if ($personResearchGroupCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personResearchGroupCount > 1 ? 'are' : 'is') .
                " $personResearchGroupCount associated Person" .
                ($personResearchGroupCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }

    /**
     * Return a ArrayCollection of Person objects that are associated with this ResearchGroup.
     *
     * @return ArrayCollection A set of Person that are connected to this ResearchGroup via PersonResearchGroup objects.
     */
    public function getAssociatedPersons()
    {
        $people = new ArrayCollection();
        $personResearchGroups = $this->getPersonResearchGroups();
        foreach ($personResearchGroups as $personResearchGroup) {
            $people->add($personResearchGroup->getPerson());
        }
        return $people;
    }
}
