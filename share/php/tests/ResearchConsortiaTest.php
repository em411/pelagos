<?php

namespace Pelagos\ResearchConsortia;

/**
 * @runTestsInSeparateProcesses
 */

class ResearchConsortiaTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        # add parent directory to include path so tests can be run from anywhere
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
        require_once 'ResearchConsortia.php';
        require_once 'stubs/RISStub.php';
        $GLOBALS['ldap'] = parse_ini_file('tests/ldap.ini', true);
    }

    public function testGetRCsFromUserNull()
    {
        $this->assertEquals(array(), getRCsFromUser(null));
    }

    public function testGetRCsFromUserEmptyString()
    {
        $this->assertEquals(array(), getRCsFromUser(''));
    }

    public function testGetRCsFromUserUnknown()
    {
        $this->assertEquals(array(), getRCsFromUser('foobar'));
    }

    public function testGetRCsFromUserNonRIS()
    {
        $this->assertEquals(array(), getRCsFromUser('jdavis'));
    }

    public function testGetRCsFromUserNonRC()
    {
        $this->assertEquals(array(), getRCsFromUser('jbaatz'));
    }

    public function testGetRCsFromUserSingleRC()
    {
        $this->assertEquals(array(134), getRCsFromUser('schen'));
    }

    public function testGetRCsFromUserMultipleRCs()
    {
        $RCs = getRCsFromUser('dhastings');
        sort($RCs);
        $this->assertEquals(array(135,138), $RCs);
    }

    public function testGetRCFromUDINull()
    {
        $this->assertEquals(null, getRCFromUDI(null));
    }

    public function testGetRCFromUDIEmptyString()
    {
        $this->assertEquals(null, getRCFromUDI(''));
    }

    public function testGetRCFromUDIInvalidFormat()
    {
        $this->assertEquals(null, getRCFromUDI('0123456789012345'));
    }

    public function testGetRCFromUDIUnknown()
    {
        $this->assertEquals(null, getRCFromUDI('R1.x555.115:0002'));
    }

    public function testGetRCFromUDIValid()
    {
        $this->assertEquals(134, getRCFromUDI('R1.x134.115:0002'));
        $this->assertEquals(135, getRCFromUDI('R1.x135.120:0002'));
    }
}
