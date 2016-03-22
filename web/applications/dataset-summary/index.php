<?php

namespace DatasetSummary;

require_once __DIR__ . '/../../../vendor/autoload.php';

set_include_path('../../../share/php' . PATH_SEPARATOR . get_include_path());

// load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
$GLOBALS['db'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/db.ini', true);
$GLOBALS['pelagos']['title'] = 'Dataset Summary';

// load Drupal functions
require_once 'drupal.php';
// load includes file dumper
require_once 'dumpIncludesFile.php';
// load database utilities
require_once 'DBUtils.php';
// load LDAP library
require_once 'ldap.php';
// load local library
require_once 'DatasetSummary.php';

// initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

// add custom Twig extensions
$app->view->parserExtensions = array(
    new \Pelagos\TwigExtensions()
);

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/', function () use ($app) {
    $env = $app->environment();
    drupal_add_js("$env[SCRIPT_NAME]/includes/dataset-summary.js", array('type' => 'external'));
    return $app->render(
        'index.html',
        array(
            'metadata_reviewer' => memberHasApplicationRole($GLOBALS['user']->name, 'Metadata', 'reviewers'),
        )
    );
});

$app->get('/:udi/delete', function ($udi) use ($app) {
    header('Content-Type: text/plain');
    if (!memberHasApplicationRole($GLOBALS['user']->name, 'Metadata', 'reviewers')) {
        echo 'Only Metadata Reviewers can delete datasets!';
        return;
    }
    if (!DatasetSummary::validUdi($udi)) {
        echo "$udi is not a valid UDI!";
        return;
    }
    $dbh = openDB('GOMRI_RO');
    if (!DatasetSummary::datasetExists($dbh, $udi)) {
        echo "No records exist for dataset: $udi";
        return;
    }
    foreach (DatasetSummary::getTables($dbh, $udi) as $table => $where) {
        if ($table == 'registry') {
            continue;
        }
        $sth = $dbh->prepare("DELETE FROM $table WHERE $where");
#        $sth->execute();
        echo "Deleted $udi from table: $table\n\n";
    }
    $sth = $dbh->prepare('SELECT MAX(registry_id) FROM registry WHERE dataset_udi = ?');
    $sth->execute(array($udi));
    $registryId = $sth->fetchColumn();
    if ($registryId !== null) {
        $sth = $dbh->prepare('UPDATE registry SET dataset_download_status = \'PendingDeletion\' WHERE registry_id = ?');
#        $sth->execute(array($registryId));
        echo "Marked $udi as PendingDeletion in table: registry\n\n";
        system($GLOBALS['config']['paths']['root'] . '/daemons/filer/trigger-filer');
        echo "Triggered filer to remove files from disk and delete registry entries for $udi";
    }
    $dbh = null;
    drupal_exit();
});

$app->get('/:udi/check-exists', function ($udi) use ($app) {
    $dbh = openDB('GOMRI_RO');
    $datasetExists = false;
    if (DatasetSummary::datasetExists($dbh, $udi)) {
        $datasetExists = true;
    }
    $dbh = null;
    header('Content-Type: application/json');
    echo json_encode($datasetExists);
    drupal_exit();
});

$app->get('/:udi(/:action)', function ($udi, $action = null) use ($app) {
    if (!DatasetSummary::validUdi($udi)) {
        echo "$udi is not a valid UDI";
        drupal_exit();
    }
    $dbh = openDB('GOMRI_RO');
    if (!DatasetSummary::datasetExists($dbh, $udi)) {
        echo "No records exist for dataset $udi";
        drupal_exit();
    }
    $fileNamePrefix = preg_replace('/:/', '.', $udi) . '-' . date('Ymd');

    // Make unique between runs.
    $pid = getmypid();

    // Create directory for dumps.
    exec("mkdir /var/tmp/$fileNamePrefix.$pid");

    $dbHost = $GLOBALS['db']['GOMRI_RO']['host'];
    $dbPort = $GLOBALS['db']['GOMRI_RO']['port'];
    $dbName = $GLOBALS['db']['GOMRI_RO']['dbname'];
    $dbUser = $GLOBALS['db']['GOMRI_RO']['username'];
    putenv('PGPASSWORD=' . $GLOBALS['db']['GOMRI_RO']['password']);

    $tables = DatasetSummary::getTables($dbh, $udi);

    foreach ($tables as $table => $where) {
        $queryFilename = "/var/tmp/$fileNamePrefix.$pid/$table.sql";
        $resultFilename = "/var/tmp/$fileNamePrefix.$pid/$fileNamePrefix-$table.csv";
        $query = "\COPY (SELECT * FROM $table WHERE $where) TO '$resultFilename' WITH csv header";
        file_put_contents($queryFilename, $query);
        // in production, we should create a separate read/only account for this pgpass auth to work safer.
        exec("/usr/bin/psql -h $dbHost -p $dbPort -U $dbUser $dbName < $queryFilename");
        unlink($queryFilename);
    }

    if ($action == 'download') {
        exec("zip -rj /var/tmp/$fileNamePrefix.zip /var/tmp/$fileNamePrefix.$pid");
        // send zip to browser
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=$fileNamePrefix.zip");
        readfile("/var/tmp/$fileNamePrefix.zip");
        unlink("/var/tmp/$fileNamePrefix.zip");
    } else {
        header('Content-Type: text/plain');
        foreach (array_keys($tables) as $table) {
            echo "$fileNamePrefix-$table.csv\n";
            readfile("/var/tmp/$fileNamePrefix.$pid/$fileNamePrefix-$table.csv");
            echo "\n";
        }
    }
    exec("rm -rf /var/tmp/$fileNamePrefix.$pid");
    drupal_exit();
});

$app->run();
