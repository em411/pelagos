<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Implementation of the Account class.
 *
 * This class defines an Account, which is a set of credentials for a Person.
 *
 * @package Pelagos\Entity
 */
class Account extends Entity
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array
     *
     * @see Entity
     */
    protected static $properties = array(
        'person' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\Person',
            'entity' => 'Person',
            'setter' => 'setPerson',
            'getter' => 'getPerson',
        ),
        'userId' => array(
            'type' => 'string',
            'setter' => 'setUserId',
            'getter' => 'getUserId',
        ),
    );

    /**
     * Person this account is attached to.
     *
     * @var Person
     *
     * @Assert\NotBlank(
     *     message="An account must be attached to a Person"
     * )
     */
    protected $person;

    /**
     * User's ID.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="User ID is required"
     * )
     */
    protected $userId;

    /**
     * A binary string containing the hashed password.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Password hash is required"
     * )
     */
    protected $passwordHash;

    /**
     * The algorithm used to hash the password.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Password hash algorithm is required"
     * )
     */
    protected $passwordHashAlgorithm;

    /**
     * A binary string containing the salt used when hashing the password.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Password hash salt is required"
     * )
     */
    protected $passwordHashSalt;

    /**
     * Attach a Person to this account.
     *
     * @param Person $person The person to attach to this account.
     *
     * @return void
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
    }

    /**
     * Get the Person this account is attached to.
     *
     * @return Person The Person this account is attached to.
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Assign for userId property.
     *
     * @param string $userId User credential user id of a Person.
     *
     * @return void
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Retrieve the userId property.
     *
     * @return string Account user id.
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the password attributes for a provided plain text password.
     *
     * @param string $password Plain text password.
     *
     * @throws \Exception When unable to generate a cryptographically strong password hash salt.
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->passwordHashAlgorithm = 'SSHA';
        // Assume the salt is not crptographically strong by default.
        $cryptoStrongSalt = false;
        // Attempt to generate a cryptographically strong 4 byte random salt.
        $this->passwordHashSalt = openssl_random_pseudo_bytes(4, $cryptoStrongSalt);
        // If the generate salt is not cryptographically strong.
        if (!$cryptoStrongSalt) {
            throw new \Exception('Could not generate a cryptographically strong password hash salt');
        }
        // Append the salt to the password, hash it, and save the hash.
        $this->passwordHash = sha1($password . $this->passwordHashSalt, true);
    }

    /**
     * Compare a plain text password against the hashed password.
     *
     * @param string $password Plain text password.
     *
     * @return boolean Whether or not the provided password matches the hash.
     */
    public function comparePassword($password)
    {
        $hash = sha1($password . $this->passwordHashSalt, true);
        if ($hash === $this->passwordHash) {
            return true;
        }
        return false;
    }
}
