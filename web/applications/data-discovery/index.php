<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';
require_once '/usr/local/share/GRIIDC/php/rpis.php';
require_once '/usr/local/share/GRIIDC/php/datasets.php';

require_once '/usr/local/share/GRIIDC/php/utils.php';

require_once 'lib/search.php';
require_once 'lib/package.php';

drupal_add_library('system', 'ui.tabs');

$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['TwigView']['twigDirectory'];

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->hook('slim.before', function () use ($app) {
    global $user;
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]"));
    $app->view()->appendData(array('pageName' => $GLOBALS['PAGE_NAME']));
    $app->view()->appendData(array('currentPage' => urlencode(preg_replace('/^\//','',$_SERVER['REQUEST_URI']))));
    if (!empty($user->name)) {
        $app->view()->appendData(array('uid' => $user->name));
    }
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    header('Content-type: text/javascript');
    $stash['logged_in'] = user_is_logged_in();
    $app->render("js/$name.js",$stash);
    exit;
});

$app->get('/css/:name.css', function ($name) use ($app) {
    header('Content-type: text/css');
    $app->render("css/$name.css");
    exit;
});

$app->get('/', function () use ($app) {
    drupal_add_js('/includes/openlayers/lib/OpenLayers.js',array('type'=>'external'));
    drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false',array('type'=>'external'));
    drupal_add_js('/~mvandeneijnden/map/geoviz.js',array('type'=>'external'));
    return $app->render('html/index.html',index($app));
});

$app->post('/', function () use ($app) {
    $stash = index($app);
    if (user_is_logged_in()) {
        $stash['download'] = $app->request()->post('download');
    }
    return $app->render('html/index.html',$stash);
});

function index($app) {
    drupal_add_js('/includes/tinyscrollbar/jquery.tinyscrollbar.min.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.events.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.min.js',array('type'=>'external'));
    drupal_add_js('/tree/js/tree.js',array('type'=>'external'));
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/search.js",array('type'=>'external'));
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/package.js",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/css/search.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/scrollbars.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/datasets.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/dataset_details.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/dataset_download.css",array('type'=>'external'));
    if (array_key_exists('treePaneCollapsed',$GLOBALS['config']['DataDiscovery'])) {
        $stash['treePaneCollapsed'] = $GLOBALS['config']['DataDiscovery']['treePaneCollapsed'];
    }
    else {
        $stash['treePaneCollapsed'] = 0;
    }
    $stash['defaultFilter'] = $app->request()->get('filter');
    return $stash;
}

$app->get('/datasets/:filter/:by/:id/:geo_filter', function ($filter,$by,$id,$geo_filter) use ($app) {
    $stash = array();
    $stash['registered_datasets'] = array();
    $stash['identified_datasets'] = array();

    $reg_filters = array('dataset_download_status=done');

    if (!empty($by)) {
        if ($by == 'otherSources') {
            $reg_filters[] = 'registry_id=00%';
        }
        elseif ($by == 'otherSource') {
            $reg_filters[] = "registry_id=00.x$id%";
        }
        else {
            if ($by != 'projectId') {
                if ($by == 'YR1') {
                    $filters = array("fundSrc>0","fundSrc<6");
                }
                else {
                    $filters = array("$by=$id");
                }
                $projects = getProjectDetails(getDBH('RPIS'),$filters);
                $projectIds = array();
                foreach ($projects as $project) {
                    $projectIds[] = $project['ID'];
                }
                $by = 'projectIds';
                $id = implode(',', $projectIds);
            }
            $reg_filters[] = "$by=$id";
            $reg_filters[] = 'registry_id!=00%';
        }
    }

    if (!empty($geo_filter) and $geo_filter != 'undefined') {
        $reg_filters[] = "geo_filter=$geo_filter";
    }

    $unrestricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('restricted=0')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);

    foreach ($unrestricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset);
        $stash['unrestricted_datasets'][] = $dataset;
    }

    $restricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('restricted=1')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);

    foreach ($restricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset);
        $stash['restricted_datasets'][] = $dataset;
    }

    if (empty($geo_filter) or $geo_filter == 'undefined') {
        $identified_datasets = get_identified_datasets(getDBH('GOMRI'),array("$by=$id",'dataset_download_status!=done','status=2'),$filter,$GLOBALS['config']['DataDiscovery']['identifiedOrderBy']);
        foreach ($identified_datasets as $dataset) {
            add_project_info($dataset);
            $stash['identified_datasets'][] = $dataset;
        }
    }

    $stash['filt'] = $filter;

    $app->render('html/datasets.html',$stash);
    exit;
})->conditions(array('filter' => '.*', 'by' => '.*', 'id' => '.*', 'geo_filter' => '.*'));

$app->get('/dataset_details/:udi', function ($udi) use ($app) {

    if (preg_match('/^00/',$udi)) {
        $stash['datasets'] = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $stash['datasets'] = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }

    $app->render('html/dataset_details.html',$stash);
    exit;
});

$app->get('/package.*', function () use ($app) {
    global $user;
    if (empty($user->name)) {
        drupal_set_message("You must be logged in to download data.",'error');
        return $app->render('html/not_logged_in.html');
    }
    $app->pass();
});

$app->get('/package', function () use ($app) {
    $stash = array();
    $env = $app->environment();
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/package.js",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/datasets.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/dataset_details.css",array('type'=>'external'));
    $stash['defaultFilter'] = $app->request()->get('filter');
    $app->render('html/package.html',$stash);
});

$app->get('/package/add/:udi', function ($udi) use ($app) {
    $username = getDrupalUserName();
    addToPackage(getDBH('GOMRI'),$username,$udi);
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/remove/:udi', function ($udi) use ($app) {
    $username = getDrupalUserName();
    removeFromPackage(getDBH('GOMRI'),$username,$udi);
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/empty', function () use ($app) {
    $username = getDrupalUserName();
    emptyPackage(getDBH('GOMRI'),$username);
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/items', function () use ($app) {
    $username = getDrupalUserName();
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/datasets/:udis', function ($udis) use ($app) {
    $stash = array();
    $stash['registered_datasets'] = array();

    if ($udis != '') {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_ids=$udis"),'',$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
        foreach ($datasets as $dataset) {
            add_download_size($dataset);
            add_project_info($dataset);
            $stash['registered_datasets'][] = $dataset;
        }
    }

    $stash['package'] = true;
    $app->render('html/datasets.html',$stash);
    exit;
})->conditions(array('udis' => '.*'));

$app->get('/package/download/:udis', function ($udis) use ($app) {
    $datasets = null;

    if (empty($udis)) {
        drupal_set_message("Error: no datasets selected for download",'error');
        exit;
    }

    $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_ids=$udis"));

    $files = array();

    $zippath = '/tmp';
    $zipfile = gen_uuid() . '.zip';

    $zip = new ZipArchive();

    if ($zip->open("$zippath/$zipfile", ZIPARCHIVE::OVERWRITE)) {

        $error = false;

        foreach ($datasets as $dataset) {
            $dat_file = "/sftp/data/$dataset[udi]/$dataset[udi].dat";
            if (file_exists($dat_file)) {
                $zip->addFile($dat_file,"$dataset[udi]/$dataset[dataset_filename]");
            }
            else {
                drupal_set_message("Error creating zip: data file not found: $dat_file",'error');
                $error = true;
            }
            $met_file = "/sftp/data/$dataset[udi]/$dataset[udi].met";
            if (file_exists($met_file)) {
                $zip->addFile($met_file,"$dataset[udi]/$dataset[metadata_filename]");
            }
        }
    
        $zip->close();

        if ($error) { exit; };
    
        if (file_exists("$zippath/$zipfile")) {
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for i.e.
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:" . filesize("$zippath/$zipfile"));
            header("Content-Disposition: attachment; filename=$zipfile");
            readfile("$zippath/$zipfile");
            exit;
        }
        else {
            drupal_set_message("Error creating zip.",'error');
        }
    }
    else {
        drupal_set_message("Error opening zip file.",'error');
    }
});

$app->get('/metadata/:udi', function ($udi) use ($app) {
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];
    $met_file = "/sftp/data/$dataset[udi]/$dataset[udi].met";
    if (file_exists($met_file)) {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($info, $met_file);
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for i.e.
        header("Content-Type: $mime");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . filesize($met_file));
        header("Content-Disposition: attachment; filename=$dataset[metadata_filename]");
        readfile($met_file);
        exit;
    }
    else {
        drupal_set_message("Error retrieving metadata file: file not found: $met_file",'error');
    }
});

$app->get('/download/:udi', function ($udi) use ($app) {
    global $user;
    if (!user_is_logged_in()) {
        $stash['error_message'] = "You must be logged in to download datasets.";
        $app->render('html/download_error.html',$stash);
        exit;
    }
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];

    if ($dataset['access_status'] == "Restricted") {
        $stash['error_message'] = "This dataset is restricted for author use only.";
        $app->render('html/download_error.html',$stash);
        exit;
    }

    if ($dataset['access_status'] == "Approval") {
        $stash['error_message'] = "This dataset can only be downloaded with author approval.";
        $app->render('html/download_error.html',$stash);
        exit;
    }

    $dat_file = "/sftp/data/$dataset[udi]/$dataset[udi].dat";
    if (file_exists($dat_file)) {
        $env = $app->environment();
        $uid = uniqid($user->name . '_');
        mkdir("/sftp/download/$uid/");
        symlink($dat_file,"/sftp/download/$uid/$dataset[dataset_filename]");
        $stash = array();
        $stash['server'] = $env['SERVER_NAME'];
        $stash['uid'] = $uid;
        $stash['dataset'] = $dataset;
        $stash['bytes'] = filesize($dat_file);
        $stash['filesize'] = bytes2filesize($stash['bytes'],1);
        $stash['filt'] = $app->request()->get('filter');
        $app->render('html/download.html',$stash);
        exit;
    }
    else {
        $stash['error_message'] = "Error retrieving data file: file not found: $dat_file";
        $app->render('html/download_error.html',$stash);
        exit;
    }
});

$app->get('/download_redirect/:udi', function ($udi) use ($app) {
    $stash['udi'] = $udi;
    $stash['final_destination'] = $app->request()->get('final_destination');
    $app->render('html/download_redirect.html',$stash);
    exit;
});

$app->run();

?>
