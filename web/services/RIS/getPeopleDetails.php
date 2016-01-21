<?php
// @codingStandardsIgnoreFile
// Module: getPeopleDetails.php
// Author(s): Michael van den Eijnden
// Last Updated: 15 August 2012
// Parameters: None
// Returns: xml rest request
// Purpose: Entry point in REST (using Slim) to return XML data for People Data

require_once __DIR__.'/../../../vendor/autoload.php';

// Disable deprecation errors if enabled
error_reporting(~E_DEPRECATED);

require 'getPeopleData.php';

$debug = false;

//With default settings
$app = new \Slim\Slim();

$app->config('debug', $debug);

if (!$debug)
{
	//$app->error();
}

//Default function
$app->get('/', function () use ($app) {
    global $debug;
	$allGetParams = $app->request()->get();
	$results = getData($allGetParams); //Has to return TRUE or no XML.
	$response = $app->response();
	$response['Content-Type'] = 'application/xml';
	$response['X-Powered-By'] = 'Slim';
	//}
});

$app->run();

?>
