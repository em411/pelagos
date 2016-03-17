<?php

namespace Pelagos\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Pelagos\Exception\NotDeletableException;

/**
 * Abstract class that contains basic properties and methods common to all Pelagos entities.
 *
 * @UniqueEntity(
 *     fields={"id"},
 *     errorPath="id",
 *     message="This id has already been assigned"
 * )
 */
abstract class Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Pelagos Entity';

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
     * The last modification time stamp (in UTC) for this Person.
     *
     * @var \DateTime $modificationTimeStamp;
     */
    protected $modificationTimeStamp;

    /**
     * The username of the user who last modified this Person.
     *
     * @var string $creator;
     *
     * @Assert\NotBlank(
     *     message="Modifier is required"
     * )
     */
    protected $modifier;

    /**
     * The time zone to use when returning time stamps.
     *
     * @var string $timeZone
     */
    protected $timeZone = 'UTC';

    /**
     * Setter for identifier.
     *
     * @param integer $id This entity's Identifier.
     *
     * @return void
     */
    public function setId($id)
    {
        if ($this->id === null) {
            $this->id = $id;
        }
    }

    /**
     * Getter for id property.
     *
     * @return integer Persistent identifier for the Entity.
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
        $this->modifier = $creator;
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
     * Setter for modifier property.
     *
     * @param string $modifier The username of the user who modified this Person.
     *
     * @return void
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * Getter for modifier property.
     *
     * @return string The username of the user who modified this Person.
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Setter for creationTimeStamp property.
     *
     * @param \DateTime $timeStamp Creation time stamp to set.
     *
     * @throws \Exception When $timeStamp does not have a timezone of UTC.
     *
     * @return void
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
        $this->setModificationTimeStamp(clone $this->getCreationTimeStamp());
    }

    /**
     * Getter for creationTimeStamp property.
     *
     * The default is to return the time stamp in the time zone set in $this->timeZone.
     * Setting $localized to true will return the time stamp localized to the current time zone.
     *
     * @param boolean $localized Whether to convert time stamp to the local time zone.
     *
     * @return \DateTime Creation time stamp for this Person.
     */
    public function getCreationTimeStamp($localized = false)
    {
        if (!isset($this->creationTimeStamp)) {
            return null;
        }
        $timeStamp = clone $this->creationTimeStamp;
        if ($localized) {
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        } else {
            $timeStamp->setTimeZone(new \DateTimeZone($this->timeZone));
        }
        return $timeStamp;
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
        $this->setModificationTimeStamp();
    }

    /**
     * Setter for modificationTimeStamp property.
     *
     * @param \DateTime $timeStamp Modification time stamp to set.
     *
     * @throws \Exception When $timeStamp does not have a timezone of UTC.
     *
     * @return void
     */
    public function setModificationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ($timeStamp->getTimezone()->getName() != 'UTC') {
                throw new \Exception('modificationTimeStamp must be in UTC');
            }
            $this->modificationTimeStamp = $timeStamp;
        } else {
            $this->modificationTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * Getter for modificationTimeStamp property.
     *
     * The default is to return the time stamp in the time zone set in $this->timeZone.
     *
     * @param boolean $localized Whether to convert time stamp to the local time zone.
     *
     * @return \DateTime Modification time stamp for this Person.
     */
    public function getModificationTimeStamp($localized = false)
    {
        if (!isset($this->modificationTimeStamp)) {
            return null;
        }
        $timeStamp = clone $this->modificationTimeStamp;
        if ($localized) {
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        } else {
            $timeStamp->setTimeZone(new \DateTimeZone($this->timeZone));
        }
        return $timeStamp;
    }

    /**
     * Get the modificationTimeStamp property as an ISO8601 string.
     *
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return string ISO8601 string representing modificationTimeStamp.
     */
    public function getModificationTimeStampAsISO($localized = false)
    {
        if (isset($this->modificationTimeStamp) and $this->modificationTimeStamp instanceof \DateTime) {
            return $this->getModificationTimeStamp($localized)->format(\DateTime::ISO8601);
        }
        return null;
    }

    /**
     * Setter for $timeZone.
     *
     * @param string $timeZone The time zone to set.
     *
     * @return void
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * Check if the Entity is deletable.
     *
     * By default, there is no checking, so this method does nothing.
     * It exists here because it will be called just before attempting to delete any Entity.
     * Entities that need checking should override this method and throw NotDeletableExceptions
     * when the Entity is not deletable.
     *
     * @see \Pelagos\Exception\NotDeletableException
     *
     * @return void
     */
    public function checkDeletable()
    {
        // Do nothing.
    }

    /**
     * Static method to serialize a binary attribute.
     *
     * @param string $binaryData The binary data to serialize.
     *
     * @return string The serialized binary data.
     */
    public static function serializeBinary($binaryData)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData);

        return array(
            'mimeType' => $mimeType,
            'base64' => base64_encode($binaryData)
        );
    }

    /**
     * Return true if the class type and instance id of the $other are the same is $this.
     *
     * @param Entity $other The object to which this is compared.
     *
     * @return bool Return true if the type and id match.
     */
    public function isSameTypeAndId(Entity $other)
    {
        if (get_class($this) == get_class($other) &&
            $this->getId() == $other->getId()) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the entty is deletable.
     *
     * @return boolean True is the entity is deletable, false otherwise.
     */
    public function isDeletable()
    {
        try {
            $this->checkDeletable();
        } catch (NotDeletableException $e) {
            return false;
        }
        return true;
    }
}
