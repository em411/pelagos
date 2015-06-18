<?php

namespace Pelagos\Component\MetadataGenerator;

use \Pelagos\Exception\NotFoundException;
use \Pelagos\Exception\PersistenceException;

/**
 * A class to get XML metadata files from a file.
 *
 * This pulls an XML metadata file from filesystem
 * and returns it as a string.  The file location is
 * determined by the UDI passed.
 */
class XMLDataFile
{
    private $logger = null;
    private static $instance = null;

    /**
     * singleton implementation
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new XMLDataFile();
        }
        return self::$instance;
    }

    /**
     * @param string $udi - dataset identification
     * @return string - absolute filesystem location of metadata file associated with supplied UDI
     */
    private function getFileLocation($udi)
    {
        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load the Common library
        require_once 'Common.php';

        # check for local config file
        if (file_exists(__DIR__ . '/../config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file(__DIR__ . '/../config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];
        $filePath = $metadataPath . '/' . $udi . '/' . $udi . '.met';

        return $filePath;
    }

    /**
     * Get the location of the xml file from the database.
     * Read it and return it. Throw PersistenceException
     * if there is a database problem.
     * Throw NotFoundException if the database finds a path
     * but the path is not readable.
     * @param string $udi
     * @return bool|string
     * @throws NotFoundException
     * @throws PersistenceException
     * @throws InvalidXmlException
     */
    public function getXML($udi)
    {
        $targetUdi = trim($udi);
        $this->logger = new MetadataLogger("XMLDataFile", $targetUdi);
        $this->logger->setOff();
        $xmlText = false;
        $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") calling getFileLocation()");
        $path = $this->getFileLocation($targetUdi);
        if ($path == false) {
            throw new NotFoundException("XMLDataFile No XML found in path: " . $path);
        } elseif (is_readable($path)) {
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") reading file: " . $path);
            $xmlText = file_get_contents($path);
            if ($xmlText === false) {
                throw new NotFoundException("XMLDataFile file_get_contents is FALSE for path: " . $path);
            }
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") create validator");
            $validator = new XMLValidator();
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") call validator");
            $validator->validate($xmlText);  // throws InvalidXmlException
            $this->logger->write("XMLDataFile.getXML(" . $targetUdi . ") returning xml text");
            return $xmlText;
        }
        throw new NotFoundException("XMLDataFile No XML found in path: " . $path);
    }
}
