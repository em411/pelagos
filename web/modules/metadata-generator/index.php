<?php

namespace Pelagos\Component\MetadataGenerator;

require_once __DIR__ . '/../../../vendor/autoload.php';

require_once __DIR__ . '/lib/MetadataGenerator.php';

$env = getLocalEnvironment();

# add pelagos/share/php to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . $env['config']['paths']['share'] . '/php');

require_once 'codelists.php';
require_once 'drupal.php';

use \Pelagos\Exception\PersistenceException;
use \Pelagos\Exception\InvalidXmlException;
use \Pelagos\Exception\NotFoundException;

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

$app->get(
    '/',
    function () use ($app) {
            echo <<<EOT
<p>Usage: $_SERVER[SCRIPT_NAME]/\$udi</p>
<p>Example: <a href="$_SERVER[SCRIPT_NAME]/R1.x134.114:0008">/metadata-generator/R1.x134.114:0008</a></p>
EOT;
    }
);

/**
 * New entry point. Same url syntax as legacy route.
 * Return XML from storage.
 * 1st - search the Database for approved XML.
 * 2nd - failing to find 1st, look for stored file
 *       referenced in the database.
 * 3rd - failing 1st and 2nd - return a blank dataset template
 */
$app->get(
    '/:udi',
    function ($udi) use ($app) {

        $trimUdi = trim($udi);

        $logger = new MetadataLogger("indexPHP", $trimUdi);
        $logger->setOff();
        $env = getLocalEnvironment();

        $metadataXml = null;
        $logger->write("Metadata Generator starting  udi: " . $trimUdi);

        //  try the database for stored xml first - CASE 1
        try {
            $logger->write("Metadata Generator - calling getMetadataXmlFromGomriDB() udi: " . $trimUdi);
            $metadataXml = getMetadataXmlFromGomriDB($trimUdi);
            $logger->write("Metadata Generator - back from  getMetadataXmlFromGomriDB() udi: " . $trimUdi);
            finishAllSuccessfulCases($trimUdi, $app, $metadataXml);
            $logger->write("Data found with Case 1 method getMetadataXmlFromGomriDB ");
            //$whichCaseSolution = "Data found with Case 1 method getMetadataXmlFromGomriDB ";
        } catch (NotFoundException $ex) { // Case 1 Not Found Catch
            $logger->write("Metadata Generator - case 1 Not Found Exception udi: " . $trimUdi);
            //  CASE 2
            //  No XML found in the database.
            //  Try retrieving from a file referenced in the database
            try {
                $logger->write("Metadata Generator - calling getMetadataXmlFromFile() udi: " . $trimUdi);
                $metadataXml = getMetadataXmlFromFile($trimUdi);
                finishAllSuccessfulCases($trimUdi, $app, $metadataXml);
                $logger->write("Data found with Case 2 method getMetadataXmlFromFile ");
                exit;
            } catch (InvalidXmlException $ex) {
                $logger->write(
                    "Metadata Generator after getMetadataXmlFromFile caught - InvalidXmlException udi: " . $trimUdi
                );
                header("status: 204");
                header("HTTP/1.0 204 No Response");
                exit;
            } catch (PersistenceException $ex) {
                $logger->write(
                    "Metadata Generator after getMetadataXmlFromFile caught - PersistenceException udi: " .
                    $trimUdi
                );
                header("status: 500");
                header("HTTP/1.0 505 Internal Server Error");
                exit;
            } catch (NotFoundException $ex) { // Case 2 Not Found Catch
                $logger->write("Metadata Generator - case 2 Not Found Exception udi: " . $trimUdi);
                try {
                    $logger->write("Metadata Generator - calling legacyGetMetadataXml() udi: " . $trimUdi);
                    // CASE 3 -
                    $metadataXml = legacyGetMetadataXml($trimUdi, $app, $logger);
                    finishAllSuccessfulCases($trimUdi, $app, $metadataXml);
                    //$whichCaseSolution = " Data found with Case 3 method legacyGetMetadataXml ";
                    $logger->write("Data found with Case 3 method legacyGetMetadataXml ");
                    exit;
                } catch (NotFoundException $ex) { // Case 3 Not Found Catch
                    $logger->write("Metadata Generator - case 3 Not Found Exception udi: " . $trimUdi);
                    header("status: 404");
                    header("HTTP/1.0 404 Not Found");
                    exit;
                }
            }
        } catch (PersistenceException $ex) {
            $logger->write("Metadata Generator -PersistenceException udi: " . $trimUdi);
            header("status: 500");
            header("HTTP/1.0 505 Internal Server Error");
            exit;
        }
        exit;
    }
);

$app->run();
