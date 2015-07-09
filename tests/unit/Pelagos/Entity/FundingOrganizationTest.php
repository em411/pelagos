<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\FundingOrganization.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\FundingOrganization
 */
class FundingOrganizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of FundingOrganization for testing.
     *
     * @var FundingOrganization $fundingOrganization
     */
    protected $fundingOrganization;

    /**
     * Static class variable containing a name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'My Funding Organization';

    /**
     * Class variable to hold a logo to use for testing.
     *
     * @var string $testLogo
     */
    protected $testLogo;

    /**
     * Static class variable containing an email address to use for testing.
     *
     * @var string $testEmailAddress
     */
    protected static $testEmailAddress = 'griidc@gomri.org';

    /**
     * Static class variable containing a description to use for testing.
     *
     * @var string $testDescription
     */
    protected static $testDescription = 'This is an organization that funds stuff. That is all.';

    /**
     * Static class variable containing a URL to use for testing.
     *
     * @var string $testUrl
     */
    protected static $testUrl = 'http://gulfresearchinitiative.org';

    /**
     * Static class variable containing a phone number to use for testing.
     *
     * @var string $testPhoneNumber
     */
    protected static $testPhoneNumber = '555-555-5555';

    /**
     * Static class variable containing a delivery point to use for testing.
     *
     * @var string $testDeliveryPoint
     */
    protected static $testDeliveryPoint = '6300 Ocean Dr.';

    /**
     * Static class variable containing a city to use for testing.
     *
     * @var string $testCity
     */
    protected static $testCity = 'Corpus Christi';

    /**
     * Static class variable containing an administrative area to use for testing.
     *
     * @var string $testAdministrativeArea
     */
    protected static $testAdministrativeArea = 'Texas';

    /**
     * Static class variable containing a postal code to use for testing.
     *
     * @var string $testPostalCode
     */
    protected static $testPostalCode = '78412';

    /**
     * Static class variable containing a country to use for testing.
     *
     * @var string $testCountry
     */
    protected static $testCountry = 'USA';

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of FundingOrganization.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fundingOrganization = new FundingOrganization;
        $this->fundingOrganization->setName(self::$testName);
        $this->testLogo = file_get_contents(__DIR__ . '/../../../data/gomri-logo.jpg');
        $this->fundingOrganization->setLogo($this->testLogo);
        $this->fundingOrganization->setEmailAddress(self::$testEmailAddress);
        $this->fundingOrganization->setDescription(self::$testDescription);
        $this->fundingOrganization->setUrl(self::$testUrl);
        $this->fundingOrganization->setPhoneNumber(self::$testPhoneNumber);
        $this->fundingOrganization->setDeliveryPoint(self::$testDeliveryPoint);
        $this->fundingOrganization->setCity(self::$testCity);
        $this->fundingOrganization->setAdministrativeArea(self::$testAdministrativeArea);
        $this->fundingOrganization->setPostalCode(self::$testPostalCode);
        $this->fundingOrganization->setCountry(self::$testCountry);
    }

    /**
     * Test the getId method.
     *
     * This method should always return null because it can not be set (even by the constructor).
     * The id property can only be set when a FundingOrganization is instantiated from persistence by Doctrine.
     *
     * @return void
     */
    public function testGetID()
    {
        $this->assertEquals(
            $this->fundingOrganization->getId(),
            null
        );
    }

    /**
     * Test the getName method.
     *
     * This method should return the name that was set in setUp.
     *
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals(
            $this->fundingOrganization->getName(),
            self::$testName
        );
    }

    /**
     * Test the getLogo method.
     *
     * This method should return the logo that was set in setUp.
     *
     * @return void
     */
    public function testGetLogo()
    {
        $this->assertEquals(
            $this->fundingOrganization->getLogo(),
            $this->testLogo
        );
    }

    /**
     * Test the getEmailAddress method.
     *
     * This method should return the email address that was set in setUp.
     *
     * @return void
     */
    public function testGetEmailAddress()
    {
        $this->assertEquals(
            $this->fundingOrganization->getEmailAddress(),
            self::$testEmailAddress
        );
    }

    /**
     * Test the getDescription method.
     *
     * This method should return the description that was set in setUp.
     *
     * @return void
     */
    public function testGetDescription()
    {
        $this->assertEquals(
            $this->fundingOrganization->getDescription(),
            self::$testDescription
        );
    }

    /**
     * Test the getUrl method.
     *
     * This method should return the URL that was set in setUp.
     *
     * @return void
     */
    public function testGetUrl()
    {
        $this->assertEquals(
            $this->fundingOrganization->getUrl(),
            self::$testUrl
        );
    }

    /**
     * Test the getPhoneNumber method.
     *
     * This method should return the phone number that was set in setUp.
     *
     * @return void
     */
    public function testGetPhoneNumber()
    {
        $this->assertEquals(
            $this->fundingOrganization->getPhoneNumber(),
            self::$testPhoneNumber
        );
    }

    /**
     * Test the getDeliveryPoint method.
     *
     * This method should return the delivery point that was set in setUp.
     *
     * @return void
     */
    public function testGetDeliveryPoint()
    {
        $this->assertEquals(
            $this->fundingOrganization->getDeliveryPoint(),
            self::$testDeliveryPoint
        );
    }

    /**
     * Test the getCity method.
     *
     * This method should return the city that was set in setUp.
     *
     * @return void
     */
    public function testGetCity()
    {
        $this->assertEquals(
            $this->fundingOrganization->getCity(),
            self::$testCity
        );
    }

    /**
     * Test the getAdministrativeArea method.
     *
     * This method should return the administrative area that was set in setUp.
     *
     * @return void
     */
    public function testGetAdministrativeArea()
    {
        $this->assertEquals(
            $this->fundingOrganization->getAdministrativeArea(),
            self::$testAdministrativeArea
        );
    }

    /**
     * Test the getPostalCode method.
     *
     * This method should return the postal code that was set in setUp.
     *
     * @return void
     */
    public function testGetPostalCode()
    {
        $this->assertEquals(
            $this->fundingOrganization->getPostalCode(),
            self::$testPostalCode
        );
    }

    /**
     * Test the getCountry method.
     *
     * This method should return the country that was set in setUp.
     *
     * @return void
     */
    public function testGetCountry()
    {
        $this->assertEquals(
            $this->fundingOrganization->getCountry(),
            self::$testCountry
        );
    }
}
