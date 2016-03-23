<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use \Pelagos\Exception\PasswordException;
use \Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation\Exclude;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;

/**
 * Implementation of the Account class.
 *
 * This class defines an Account, which is a set of credentials for a Person.
 *
 * @package Pelagos\Entity
 */
class Account extends Entity implements UserInterface, \Serializable
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Account';

    /**
     * The standard role given to all users.
     */
    const ROLE_USER = 'ROLE_USER';

    /**
     * A role given only to Data Repository Managers.
     */
    const ROLE_DATA_REPOSITORY_MANAGER = 'ROLE_DATA_REPOSITORY_MANAGER';

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array
     *
     * @see Entity
     *
     * @Exclude
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
     *
     * @Exclude
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
     *
     * @Exclude
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
     *
     * @Exclude
     */
    protected $passwordHashSalt;

    /**
     * Constructor for Account.
     *
     * @param Person $person   The Person this account is for.
     * @param string $userId   The user ID for this account.
     * @param string $password The password for this account.
     */
    public function __construct(Person $person = null, $userId = null, $password = null)
    {
        if ($person !== null) {
            $this->setPerson($person);
        }
        if ($userId !== null) {
            $this->setUserId($userId);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }
    }

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
        if ($this->person !== null and $this->person->getAccount() !== $this) {
            $this->person->setAccount($this);
        }
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
     * @throws PasswordException When $password is shorter than 8 characters.
     * @throws PasswordException When $password does not meet complexity requirements.
     * @throws \Exception        When unable to generate a cryptographically strong password hash salt.
     *
     * @return void
     */
    public function setPassword($password)
    {
        if (strlen($password) < 8) {
            throw new PasswordException('Password is not long enough (must be at least 8 characters)');
        }

        $passwordComplexityRegEx
            = '/^' .
            // Password must contain:
            '(?:' .
                // a digit, a lowercase letter, and an uppercase letter
                '(?:(?=.*\d)(?=.*\p{Ll})(?=.*\p{Lu}))' .
                // or
                '|' .
                // a digit, a lowercase letter, and a character that is not a digit or cased letter
                '(?:(?=.*\d)(?=.*\p{Ll})(?=.*[^\d\p{Ll}\p{Lu}]))' .
                // or
                '|' .
                // a digit, an uppercase letter, and a character that is not a digit or cased letter
                '(?:(?=.*\d)(?=.*\p{Lu})(?=.*[^\d\p{Ll}\p{Lu}]))' .
                // or
                '|' .
                // a lowercase letter, an uppercase letter, and a character that is not a digit or cased letter
                '(?:(?=.*\p{Ll})(?=.*\p{Lu})(?=.*[^\d\p{Ll}\p{Lu}]))' .
            ')' .
            // and can contain any other characters as long as the above matches.
            '.+$/';

        if (!preg_match($passwordComplexityRegEx, $password)) {
            throw new PasswordException('Password is not complex enough');
        }

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
        $hash = sha1($password . $this->getSalt(), true);
        if ($hash === $this->getPassword()) {
            return true;
        }
        return false;
    }

    /**
     * Returns the userId for this Account.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return string The userId for this Account.
     */
    public function getUsername()
    {
        return $this->userId;
    }

    /**
     * Returns the passwordHashSalt for this Account.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return string The passwordHashSalt for this Account.
     */
    public function getSalt()
    {
        if (is_resource($this->passwordHashSalt)) {
            return stream_get_contents($this->passwordHashSalt);
        }
        return $this->passwordHashSalt;
    }

    /**
     * Returns the passwordHash for this Account.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return string The passwordHash for this Account.
     */
    public function getPassword()
    {
        if (is_resource($this->passwordHash)) {
            return stream_get_contents($this->passwordHash);
        }
        return $this->passwordHash;
    }

    /**
     * Returns the roles for this Account.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return array The roles for this Account.
     */
    public function getRoles()
    {
        $roles = array(self::ROLE_USER);
        foreach ($this->getPerson()->getPersonDataRepositories() as $personDataRepository) {
            if ($personDataRepository->getRole()->getName() == DataRepositoryRoles::MANAGER and
                !in_array(self::ROLE_DATA_REPOSITORY_MANAGER, $roles)) {
                $roles[] = self::ROLE_DATA_REPOSITORY_MANAGER;
            }
        }
        return $roles;
    }

    /**
     * Does nothing because aren't keeping the plaintext password in the Account object.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }

    /**
     * Serialize this Account.
     *
     * This is required by \Serializable.
     *
     * @return string Serialized Account string.
     */
    public function serialize()
    {
        return serialize(array(
            $this->person,
            $this->userId,
        ));
    }

    /**
     * Unserialize this Account.
     *
     * This is required by \Serializable.
     *
     * @param string $serialized Serialized Account string.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
            $this->person,
            $this->userId,
        ) = unserialize($serialized);
    }

    /**
     * Returns the hashing algorithm used to generate the password hash.
     *
     * @return string The hashing algorithm.
     */
    public function getHashAlgorithm()
    {
        return $this->passwordHashAlgorithm;
    }
}
