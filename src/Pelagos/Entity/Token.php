<?php
/**
 * This file contains the implementation of the Token entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage Token
 */

namespace Pelagos\Entity;

/**
 * Class to represent a token.
 */
class Token extends Entity
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * Used by common update code.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'tokenText' => array(
            'type' => 'string',
            'getter' => 'getTokenText',
        ),
    );

    /**
     * Token's identifying text string.
     *
     * @var string $tokenText
     *
     * @Assert\NotBlank(
     *     message="Token text is required."
     * )
     */
    protected $tokenText;

    /**
     * Constructor for PersonToken.
     */
    public function __construct()
    {
        $this->generateTokenText();
    }

    /**
     * Getter for tokenText property.
     *
     * @access public
     *
     * @return string Token text for a Token entity.
     */
    public function getTokenText()
    {
        return $this->tokenText;
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
}
