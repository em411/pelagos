<?php

namespace Pelagos;

/**
 * Unit tests for Pelagos\HTTPStatus.
 *
 * @group Pelagos
 * @group Pelagos\HTTPStatus
 */
class HTTPStatusTest extends \PHPUnit_Framework_TestCase
{
    /** @var int $testCode An HTTP status code to use for testing. **/
    protected static $testCode = 200;

    /** @var string $testMessage A message to use for testing. **/
    protected static $testMessage = 'Success!';

    /** @var array $testData A data package to use for testing. **/
    protected static $testData = array(
        'foo' => 1,
        'bar' => 2,
        'baz' => 3,
    );

    /**
     * Test that getters return values passed to constructor.
     */
    public function testGetters()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage, self::$testData);
        $this->assertEquals(self::$testCode, $status->getCode());
        $this->assertEquals(self::$testMessage, $status->getMessage());
        $this->assertEquals(self::$testData, $status->getData());
    }

    /**
     * Test that message is null by default if not passed to constructor.
     */
    public function testNullMessageByDefault()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode);
        $this->assertEquals(self::$testCode, $status->getCode());
        $this->assertNull($status->getMessage());
    }

    /**
     * Test that data is null by default if not passed to constructor.
     */
    public function testNullDataByDefault()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode);
        $this->assertNull($status->getData());
    }

    /**
     * Test that HTTPStatus is JSON serializable and returns expected JSON.
     */
    public function testJsonSerializable()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage, self::$testData);
        $this->assertEquals(
            $this->makeHTTPStatusJSON(self::$testCode, self::$testMessage, self::$testData),
            json_encode($status)
        );
    }

    /**
     * Test that HTTPStatus is JSON serializable and returns expected JSON when data is not set.
     */
    public function testJsonSerializableNoData()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage);
        $this->assertEquals(
            $this->makeHTTPStatusJSON(self::$testCode, self::$testMessage),
            json_encode($status)
        );
    }

    /**
     * Test that asJSON() returns expected JSON.
     */
    public function testAsJSON()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage);
        $this->assertEquals(
            $this->makeHTTPStatusJSON(self::$testCode, self::$testMessage),
            $status->asJSON()
        );
    }

    /**
     * Utility method to build a JSON string equivalent to a JSON serialized HTTPStatus.
     *
     * @param int $code The HTTP status code.
     * @param string $message The HTTP status message.
     * @param mixed $data The data package.
     * @return string A JSON string containing $code, $message, and $data (if set).
     */
    protected function makeHTTPStatusJSON($code, $message = null, $data = null)
    {
        $serialized = array(
            'code' => $code,
            'message' => $message,
        );
        if (isset($data)) {
            $serialized['data'] = $data;
        }
        return json_encode($serialized);
    }
}
