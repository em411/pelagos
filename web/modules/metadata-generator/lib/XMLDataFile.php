<?php

namespace MetadataGenerator;

require_once "exceptions/NotFoundException.php";
require_once "exceptions/PersistenceEngineException.php";
require_once '../../../share/php/db-utils.lib.php';

use \Exception\NotFoundException as NotFoundException;
use \Exception\PersistenceEngineException as PersistenceEngineException;
use \PDO as PDO;

class XMLDataFile
{
    private $dbcon = null;

    public function __construct() {

        $this->dbcon = OpenDB("GOMRI_RW");
        $this->dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }
    private function getLocationUgly($udi)
    {
        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        # load Common library from global share
        require_once(__DIR__.'/../../../../share/php/Common.php');

        # check for local config file
        if (file_exists(__DIR__.'/config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];
        $filePath = $metadataPath . '/' . $udi . '/' . $udi . '.met';

        return $filePath;
    }

    private function getLocationFromDB($udi)
    {
        # load global pelagos config
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
        # load Common libraries

        # check for local config file
        if (file_exists(__DIR__.'/config.ini')) {
            # merge local config with global config
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];

        $sql = "SELECT REGEXP_REPLACE(
                                        REGEXP_REPLACE(
                                                        dataset_metadata,
                                                        '-metadata.xml$', ''),
                                        '-',
                                        ':')
               AS dataset_metadata
               FROM registry_view
               WHERE dataset_udi = :udi";

        $db_error = false;
        $sth = $this->dbcon->prepare($sql);
        $sth->bindparam(":udi", $udi);

        try {
            $sth->execute();
        } catch (\PDOException $e) {
            $db_error = true;
            $sth = null;
            $this->dbcon = null;
            throw new PersistenceEngineException("C-2 XMLDataFile ".$e->getMessage());
        }

        $data = $sth->fetchAll();

        if (($db_error == false) and ($sth->rowCount() > 0)) {
            $filepath = $metadataPath . '/' . $data[0][0] . '/' . $data[0][0] .  '.met';
            $sth = null;
            $this->dbcon = null;
            return $filepath;
        } else {
            $sth = null;
            $this->dbcon = null;
            return false;
        }

    }

    /**
     * Get the location of the xml file from the database.
     * Read it and return it. Throw PersistenceEngineException
     * if there is a database problem.
     * Throw NotFoundException if the database finds a path
     * but the path is not readable.
     * @param $udi
     * @return bool|string
     * @throws NotFoundException
     * @throws PersistenceEngineException
     */
    public function getXML($udi)
    {

        $xmlText = false;
        $path = $this->getLocationFromDB($udi);

        if($path == false) {
            throw new NotFoundException("C-2 XMLDataFile No XML found in path: ".$path);
        } elseif (is_readable($path)) {
            $xmlText = file_get_contents($path);
            if($xmlText === false) {
                throw new NotFoundException("C-2 XMLDataFile file_get_contents is FALSE for path: ".$path);
            }
            return $xmlText;
        }
        throw new NotFoundException("C-2 XMLDataFile No XML found in path: ".$path);
    }
}
