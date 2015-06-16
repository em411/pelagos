<?php

namespace Pelagos\Component;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PersonServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Pelagos\Component\PersonService $personService Property to hold an instance of PersonService. **/
    protected $personService;

    /** @var \Pelagos\Entity\Person $mockPerson Property to hold a mock person for testing. **/
    protected $mockPerson;

    /** @var \Doctrine\ORM\EntityManager $mockEntityManager Propety to hold a mock EntityManager. **/
    protected $mockEntityManager;

    /** @var \Doctrine\DBAL\Driver\DriverException $mockDriverException Propety to hold a mock DriverException. **/
    protected $mockDriverException;

    /** @var mixed $mockValidator Propety to hold a mock validator. **/
    protected $mockValidator;

    /** @var string $firstName A valid first name to use for testing. **/
    protected static $firstName = 'test';

    /** @var string $lastName A valid last name to use for testing. **/
    protected static $lastName = 'user';

    /** @var string $emailAddress A valid email address to use for testing. **/
    protected static $emailAddress = 'test.user@testdomian.tld';

    /** @var string $emailAddress An invalid email address to use for testing. **/
    protected static $badEmailAddress = 'bademail@testdomian';

    /**
     * Set up for tests.
     * Since this is a unit tests, we mock all dependencies:
     * - \Pelagos\Entity\Person
     * - \Doctrine\ORM\EntityManager
     * - \Pelagos\Persistance
     * - \Doctrine\DBAL\Driver\DriverException
     */
    protected function setUp()
    {
        $this->personService = new \Pelagos\Component\PersonService();

        $this->mockPerson = \Mockery::mock('overload:\Pelagos\Entity\Person');
        $this->mockPerson->shouldReceive('getId')->andReturn(0);
        $this->mockPerson->shouldReceive('getFirstName')->andReturn(self::$firstName);
        $this->mockPerson->shouldReceive('getLastName')->andReturn(self::$lastName);
        $this->mockPerson->shouldReceive('getEmailAddress')->andReturn(self::$emailAddress);

        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');

        $mockPersistence = \Mockery::mock('alias:\Pelagos\Persistance');
        $mockPersistence->shouldReceive('createEntityManager')->andReturn($this->mockEntityManager);

        $this->mockDriverException = \Mockery::mock('\Doctrine\DBAL\Driver\DriverException');

        $this->mockValidator = \Mockery::mock('\Symfony\Component\Validator\Validator');
    }

    /**
     * Test validating a person with success.
     */
    public function testValidateSuccess()
    {
        $this->mockValidator->shouldReceive('validate')->andReturn(array());
        $person = $this->personService->validate($this->mockPerson, $this->mockValidator);
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
    }

    /**
     * Test validating a person with validation failure.
     *
     * @expectedException \Pelagos\Exception\ValidationException
     */
    public function testValidateFailure()
    {
        $this->mockValidator->shouldReceive('validate')->andReturn(array(1));
        $person = $this->personService->validate($this->mockPerson, $this->mockValidator);
    }

    /**
     * Test persisting a person successfully.
     */
    public function testPersistSuccess()
    {
        $this->mockEntityManager->shouldReceive('flush');
        $person = $this->personService->persist($this->mockPerson);
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
        $this->assertSame(0, $person->getId());
        $this->assertEquals(self::$firstName, $person->getFirstName());
        $this->assertEquals(self::$lastName, $person->getLastName());
        $this->assertEquals(self::$emailAddress, $person->getEmailAddress());
    }

    /**
     * Test handling of attempting to persist a person with a missing required field.
     *
     * @expectedException \Pelagos\Exception\MissingRequiredFieldPersistenceException
     */
    public function testPersistMissingRequiredField()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\NotNullConstraintViolationException',
            null,
            $this->mockDriverException
        );
        $person = $this->personService->persist($this->mockPerson);
    }

    /**
     * Test handling of attempting to persist a person that already exists in persistence.
     *
     * @expectedException \Pelagos\Exception\RecordExistsPersistenceException
     */
    public function testPersistRecordExists()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\UniqueConstraintViolationException',
            null,
            $this->mockDriverException
        );
        $person = $this->personService->persist($this->mockPerson);
    }

    /**
     * Test handling of attempting to persist a person and encountering a persistence error.
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     */
    public function testPersistPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow('\Doctrine\DBAL\DBALException');
        $person = $this->personService->persist($this->mockPerson);
    }

    /**
     * Test getting a person that exists.
     * Should return the person for the provided id.
     */
    public function testGetPerson()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturn($this->mockPerson);
        $person = $this->personService->getPerson(0);
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
        $this->assertSame(0, $person->getId());
        $this->assertEquals(self::$firstName, $person->getFirstName());
        $this->assertEquals(self::$lastName, $person->getLastName());
        $this->assertEquals(self::$emailAddress, $person->getEmailAddress());
    }

    /**
     * Test getting a person that exists by passing a string that contains an integer.
     * Should return the person for the provided id.
     */
    public function testGetPersonIntegerString()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturn($this->mockPerson);
        $person = $this->personService->getPerson('0');
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
        $this->assertSame(0, $person->getId());
        $this->assertEquals(self::$firstName, $person->getFirstName());
        $this->assertEquals(self::$lastName, $person->getLastName());
        $this->assertEquals(self::$emailAddress, $person->getEmailAddress());
    }

    /**
     * Test handling of attempting to get a person with an invalid id.
     *
     * @expectedException \Pelagos\Exception\ArgumentException
     */
    public function testGetPersonInvalidID()
    {
        $person = $this->personService->getPerson('foo');
    }

    /**
     * Test handling of attempting to get a person with an invalid id
     * and getting back the id sent upon catching the exception.
     */
    public function testGetPersonInvalidIDGetID()
    {
        try {
            $person = $this->personService->getPerson('foo');
        } catch (\Pelagos\Exception\ArgumentException $e) {
            $this->assertEquals('id', $e->getArgumentName());
            $this->assertEquals('foo', $e->getArgumentValue());
        }
    }

    /**
     * Test handling of attempting to get a person that does not exist in persistence.
     *
     * @expectedException \Pelagos\Exception\RecordNotFoundPersistenceException
     */
    public function testGetPersonRecordNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        $person = $this->personService->getPerson(0);
    }

    /**
     * Test handling of attempting to get a person and encountering a persistence error.
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     */
    public function testGetPersonPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow('\Doctrine\DBAL\DBALException');
        $person = $this->personService->getPerson(0);
    }
}
