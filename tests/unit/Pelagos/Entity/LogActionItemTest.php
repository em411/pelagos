<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\LogActionItem.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\LogActionItem
 *
 * @package Pelagos\Entity
 */
class LogActionItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of Log Action Item for testing.
     * @var LogActionItem
     */
    protected $logActionItem;

    /**
     * Property to hold an Action Name for testing..
     * @var string
     */
    protected static $testActionName = 'HTTP Download';

    /**
     * Property to hold a Subject Entity Name for testing.
     * @var string
     */
    protected static $testSubjectEntityName = 'Dataset';

    /**
     * Property to hold a Subject Entity Id for testing.
     * @var integer
     */
    protected static $testSubjectEntityId = '1111';

    /**
     * JSON array property to hold Payload of a log action item for testing.
     * @var integer
     */
    protected static $testPayLoad = array('user' => 'abc@xyz.com','keyName' => 'valueName');

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Entity and sets its properties.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->logActionItem = new LogActionItem();
        $this->logActionItem->setActionName(self::$testActionName);
        $this->logActionItem->setSubjectEntityName(self::$testSubjectEntityName);
        $this->logActionItem->setSubjectEntityId(self::$testSubjectEntityId);
        $this->logActionItem->setPayLoad(self::$testPayLoad);
    }

    /**
     * Test the getActionName method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetActionName()
    {
        $this->assertEquals(
            self::$testActionName,
            $this->logActionItem->getActionName()
        );
    }

    /**
     * Test the getSubjectEntityName method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetSubjectEntityName()
    {
        $this->assertEquals(
            self::$testSubjectEntityName,
            $this->logActionItem->getSubjectEntityName()
        );
    }

    /**
     * Test the getSubjectEntityId method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetSubjectEntityId()
    {
        $this->assertEquals(
            self::$testSubjectEntityId,
            $this->logActionItem->getSubjectEntityId()
        );
    }

    /**
     * Test the getPayLoad method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetPayLoad()
    {
        $this->assertEquals(
            self::$testPayLoad,
            $this->logActionItem->getPayLoad()
        );
    }

    /**
     * Test the getPayLoadItemByKey method.
     *
     * This method should test the key and value of an item in the payload array.
     *
     * @return void
     */
    public function testGetPayLoadItemByKey()
    {
        $keyName = 'keyName';
        $this->assertEquals(
            self::$testPayLoad[$keyName],
            $this->logActionItem->getPayLoadItemByKey($keyName)
        );
    }

    /**
     * Clean up after tests.
     *
     * @return void
     */
    public function tearDown()
    {
    }
}
