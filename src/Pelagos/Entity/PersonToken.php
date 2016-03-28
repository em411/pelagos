<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use JMS\Serializer\Annotation as Serializer;

/**
 * Enitity class to represent a Person Token.
 *
 * @ORM\Entity
 */
class PersonToken extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Person Token';

    /**
     * This is defined here to override the base class id.
     *
     * This is not used by the PersonToken Entity because it gets its identity through Person.
     *
     * @var null
     */
    protected $id;

    /**
     * Property containing a \DateInterval of validity of token.
     *
     * @var \DateInterval $validFor
     *
     * @ORM\Column(type="interval")
     */
    protected $validFor;

    /**
     * Person entity this PersonToken belongs to.
     *
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person", inversedBy="token")
     * @ORM\Id
     *
     * @Assert\NotBlank(
     *     message="Person is required"
     * )
     */
    protected $person;

    /**
     * PersonToken's text string.
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Token text is required."
     * )
     *
     * @Serializer\Exclude
     */
    protected $tokenText;

    /**
     * PersonToken's use string.
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Token use is required."
     * )
     */
    protected $use;

    /**
     * Constructor for PersonToken.
     *
     * @param Person        $person   Person who owns the token to be created.
     * @param string        $use      String representing use of token to be created.
     * @param \DateInterval $validFor Validity duration of token to be created.
     *
     * @return void
     */
    public function __construct(Person $person, $use, \DateInterval $validFor)
    {
        $this->setPerson($person);
        $this->setUse($use);
        $this->setValidFor($validFor);
        $this->generateTokenText();
    }

    /**
     * Setter for Person.
     *
     * @param Person|null $person The Person entity this token belongs to.
     *
     * @return void
     */
    public function setPerson(Person $person = null)
    {
        $this->person = $person;
        if ($this->person !== null and $this->person->getToken() !== $this) {
            $this->person->setToken($this);
        }
    }

    /**
     * Getter for Person.
     *
     * @return Person|null The Person entity this token belongs to.
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Getter for tokenText property.
     *
     * @access public
     *
     * @return string Token text for a PersonToken entity.
     */
    public function getTokenText()
    {
        return $this->tokenText;
    }

    /**
     * Setter for use.
     *
     * @param string $use String enumerating token usage.
     *
     * @return void
     */
    public function setUse($use)
    {
        $this->use = $use;
    }

    /**
     * Getter for use.
     *
     * @return string String enumerating token usage.
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * Setter for ValidFor.
     *
     * @param \DateInterval $validFor The interval entity is valid beyond creationtime.
     *
     * @return void
     */
    public function setValidFor(\DateInterval $validFor)
    {
        $this->validFor = $validFor;
    }

    /**
     * Getter for ValidFor.
     *
     * @return \DateInterval The interval entity is valid beyond creationtime.
     */
    public function getValidFor()
    {
        return $this->validFor;
    }

    /**
     * Generate a random token and save it as a hexadecimal string.
     *
     * @access public
     *
     * @throws \Exception When unable to generate a cryptographically strong token.
     *
     * @return void
     */
    public function generateTokenText()
    {
        // Assume the token is not crptographically strong by default.
        $cryptoStrong = false;
        // Attempt to generate a cryptographically strong 32 byte random byte string.
        $randomBytes = openssl_random_pseudo_bytes(32, $cryptoStrong);
        // If the generated byte string is not cryptographically strong.
        if (!$cryptoStrong) {
            throw new \Exception('Could not generate a cryptographically strong token');
        }
        // Encode the byte string as hex, and save it as the token text.
        $this->tokenText = bin2hex($randomBytes);
    }

    /**
     * Return true if token is valid, otherwise false.
     *
     * @access public
     *
     * @return boolean|null
     */
    public function isValid()
    {
        $now = new \DateTime();
        // \DateTime object
        $created = $this->getCreationTimeStamp();
        // \DateInterval object
        $goodFor = $this->getValidFor();

        if ($now < ($created->add($goodFor))) {
            return true;
        } else {
            return false;
        }
    }
}
