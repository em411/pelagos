<?php
/**
 * This file contains an abstract implementation of a Pelagos entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage Entity
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract class that contains basic properties and methods common to all Pelagos entities.
 */
abstract class Entity implements \JsonSerializable
{
    /**
     * Entity identifier.
     *
     * @var int $id
     */
    protected $id;

    /**
     * The username of the user who created this Entity.
     *
     * @var string $creator;
     *
     * @Assert\NotBlank(
     *     message="Creator is required"
     * )
     */
    protected $creator;

    /**
     * The creation time stamp (in UTC) for this Entity.
     *
     * @var \DateTime $creationTimeStamp;
     */
    protected $creationTimeStamp;

    /**
     * Getter for id property.
     *
     * @return int Persistent identifier for the Entity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter for creator.
     *
     * @param string $creator This entity's creator's username.
     *
     * @access public
     *
     * @return void
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Getter for creator.
     *
     * @access public
     *
     * @return string This entity's creator's username.
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Setter for creationTimeStamp property.
     *
     * @param \DateTime $timeStamp Creation time stamp to set.
     *
     * @return void
     *
     * @throws \Exception When $timeStamp does not have a timezone of UTC.
     */
    public function setCreationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ($timeStamp->getTimezone()->getName() != 'UTC') {
                throw new \Exception('creationTimeStamp must be in UTC');
            }
            $this->creationTimeStamp = $timeStamp;
        } else {
            $this->creationTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * Getter for creationTimeStamp property.
     *
     * The default is to return the time stamp in UTC.
     * Setting $localized to true will return the time stamp localized to the current timezone.
     * This getter also makes sure the creationTimeStamp property is set to UTC.
     *
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return \DateTime Creation time stamp for this Person.
     */
    public function getCreationTimeStamp($localized = false)
    {
        if (!isset($this->creationTimeStamp)) {
            return null;
        }
        $this->creationTimeStamp->setTimeZone(new \DateTimeZone('UTC'));
        if ($localized) {
            $timeStamp = clone $this->creationTimeStamp;
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            return $timeStamp;
        }
        return $this->creationTimeStamp;
    }

    /**
     * Get the creationTimeStamp property as an ISO8601 string.
     *
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return string ISO8601 string representing creationTimeStamp.
     */
    public function getCreationTimeStampAsISO($localized = false)
    {
        if (isset($this->creationTimeStamp) and $this->creationTimeStamp instanceof \DateTime) {
            return $this->getCreationTimeStamp($localized)->format(\DateTime::ISO8601);
        }
        return null;
    }

    /**
     * Update the time stamps to the current time.
     *
     * The creation time stamp is only updated if not already set.
     *
     * @return void
     */
    public function updateTimeStamps()
    {
        if ($this->creationTimeStamp == null) {
            $this->setCreationTimeStamp();
        }
    }

    /**
     * Implement JsonSerializable.
     *
     * @return array An array suitable for JSON serialization of the object.
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'creationTimeStamp' => $this->getCreationTimeStampAsISO(),
            'creator' => $this->getCreator(),
        );
    }
}
