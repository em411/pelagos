<?php
/**
 * This file contains the implementation of the FundingCycle entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage FundingCycle
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent funding cycles.
 */
class FundingCycle extends Entity
{
    /**
     * Name of a funding cycle.
     *
     * @var string $name
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Last name cannot contain angle brackets (< or >)"
     * )
     */
    protected $name;

    /**
     * Description of a funding cycle.
     *
     * @var string $description
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    protected $description;

    /**
     * Funding cycle's Website url.
     *
     * @var string $url
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Funding cycle's start date.
     *
     * @var \Datetime $startDate
     * @access protected
     */
    protected $startDate;

    /**
     * Funding cycle's end date.
     *
     * @var \Datetime $endDate
     * @access protected
     */
    protected $endDate;

    /**
     * Funding cycle's Funding Organization.
     *
     * @var FundingOrganization
     * @access protected
     */
    protected $fundingOrganization;

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'name' => array(
            'type' => 'string',
            'setter' => 'setName',
            'getter' => 'getName',
        ),
        'description' => array(
            'type' => 'string',
            'setter' => 'setDescription',
            'getter' => 'getDescription',
        ),
        'url' => array(
            'type' => 'string',
            'setter' => 'setUrl',
            'getter' => 'geturl',
        ),
        'startDate' => array(
            'type' => 'object',
            'class' => 'DateTime',
            'resolver' => 'resolveDateTime',
            'setter' => 'setStartDate',
            'getter' => 'getStartDate',
            'serializer' => 'serializeDate',
        ),
        'endDate' => array(
            'type' => 'object',
            'class' => 'DateTime',
            'resolver' => 'resolveDateTime',
            'setter' => 'setEndDate',
            'getter' => 'getEndDate',
            'serializer' => 'serializeDate',
        ),
        'fundingOrganization' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\FundingOrganization',
            'entity' => 'FundingOrganization',
            'setter' => 'setFundingOrganization',
            'getter' => 'getFundingOrganization',
        ),
    );

    /**
     * Static method to get a list of properties for this class.
     *
     * @return array The list of properties for this class.
     */
    public static function getProperties()
    {
        return array_merge(parent::getProperties(), self::$properties);
    }

    /**
     * Setter for name.
     *
     * @param string $name Textual name of funding cycle.
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
     * @return string String containing name of funding cycle.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for description.
     *
     * @param string $description Description of funding cycle.
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
     * @return string Description of funding cycle.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for Funding Cycle.
     *
     * @param FundingOrganization $fundingOrg The funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setFundingOrganization(FundingOrganization $fundingOrg)
    {
        $this->fundingOrganization = $fundingOrg;
    }

    /**
     * Getter for Funding Organization.
     *
     * @access public
     *
     * @return FundingOrganization Funding Organization.
     */
    public function getFundingOrganization()
    {
        return $this->fundingOrganization;
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
     * @return string URL of funding cycle's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for startDate.
     *
     * @param mixed $startDate The Start Date.
     *
     * @access public
     *
     * @return void
     */
    public function setStartDate($startDate)
    {
        if (gettype($startDate) == 'string') {
            $startDate = new \DateTime($startDate);
        }
        $this->startDate = $startDate;
    }

    /**
     * Getter for startDate.
     *
     * @access public
     *
     * @return \DateTime startDate of funding cycle's Website.
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Get the startDate property as an ISO8601 string.
     *
     * @return string ISO8601 string representing startDate.
     */
    public function getStartDateAsISO()
    {
        if (isset($this->startDate) and $this->startDate instanceof \DateTime) {
            return $this->getStartDate()->format('Y-m-d');
        }
        return null;
    }

    /**
     * Setter for endDate.
     *
     * @param mixed $endDate The End Date.
     *
     * @access public
     *
     * @return void
     */
    public function setEndDate($endDate)
    {
        if (gettype($endDate) == 'string') {
            $endDate = new \DateTime($endDate);
        }
        $this->endDate = $endDate;
    }

    /**
     * Getter for endDate.
     *
     * @access public
     *
     * @return \DateTime endDate of funding cycle's Website.
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Get the endDate property as an ISO8601 string.
     *
     * @return string ISO8601 string representing endDate.
     */
    public function getEndDateAsISO()
    {
        if (isset($this->endDate) and $this->endDate instanceof \DateTime) {
            return $this->getEndDate()->format('Y-m-d');
        }
        return null;
    }
}
