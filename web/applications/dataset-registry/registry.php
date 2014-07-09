<?php
include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';

if (!file_exists('config.php')) {
    echo 'Error: config.php is missing. Please see config.php.example for an example config file.';
    exit;
}
require_once 'config.php';

include_once '/usr/local/share/GRIIDC/php/ldap.php';
include_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dif-registry.php';
require_once '/usr/local/share/GRIIDC/php/db-utils.lib.php';

include_once 'pdo_functions.php';

include_once 'lib/functions.php';

$isGroupAdmin = false;

$alltasks="";

$conn = pdoDBConnect('pgsql:'.GOMRI_DB_CONN_STRING);
$DBH = OpenDB('GOMRI_RW');

$ldap = connectLDAP('triton.tamucc.edu');
$baseDN = 'dc=griidc,dc=org';
$uid = getUID();
if (isset($uid)) {
    $submittedby ="";
    $userDNs = getDNs($ldap,$baseDN,"uid=$uid");
    $userDN = $userDNs[0]['dn'];
    if (count($userDNs) > 0) {
        $attributes = getAttributes($ldap,$userDN,array('givenName','sn','employeeNumber'));
        if (count($attributes) > 0) {
            if (array_key_exists('givenName',$attributes)) $firstName = $attributes['givenName'][0];
            if (array_key_exists('sn',$attributes)) $lastName = $attributes['sn'][0];
            if (array_key_exists('employeeNumber',$attributes)) $submittedby = $attributes['employeeNumber'][0];
        }
    }
}

# first try to get tasks for which we have a task role
$tasks = getTasks($ldap,$baseDN,$userDN,$submittedby,true);

# if we have no task roles, try to get tasks for which we have any role
if (count($tasks) == 0) {
    $tasks = getTasks($ldap,$baseDN,$userDN,$submittedby,false);
}

# if we still have no tasks, show a warning
if (count($tasks) == 0) {
    drupal_set_message("No identified datasets found for $firstName $lastName.<br>If you or someone in your organization has completed a DIF to identify datasets that you are now attempting to register, please contact GRIIDC at <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a>",'warning');
}

$GLOBALS['personid'] ="";
if ($_GET)
{
    if (isset($_GET['dataurl']))
    {
        include 'checkurl.php';
        echo checkURL($_GET['dataurl']);

        exit;
    }

    if (isset($_GET['uid']))
    {
        $dif_id = $_GET['uid'];
    }

    if (isset($_GET['regid']))
    {
        $reg_id = $_GET['regid'];
    }

    if (isset($_GET['personID']))
    {
        $personid = $_GET['personID'];
        ob_clean();
        ob_flush();
        $tasks = filterTasks($tasks,$personid);
        echo displayTaskStatus($tasks,true,$personid);
        exit;
    }

    if (isset($_GET['persontask']))
    {
        $personid = $_GET['persontask'];
        ob_clean();
        ob_flush();
        $tasks = filterTasks($tasks,$personid);
        echo "<option value=' '>[SELECT A TASK]</option>";
        echo getTaskOptionList($tasks, null);
        exit;
    }

    if (isset($_GET['prsid']))
    {
        $GLOBALS['personid'] = $_GET['prsid'];
        $alltasks = $tasks;
        $tasks = filterTasks($tasks,$GLOBALS['personid']);
    }
}

if ($_POST)
{
    $formHash = sha1(serialize($_POST));

    $doi = '';

    extract($_POST);

    $SQL = "SELECT MAX(registry_id) AS maxregid FROM registry WHERE registry_id LIKE ?;";
    $sth = $DBH->prepare($SQL);
    if ($udi == "") $sth->execute(array('00.x000.000:%'));
    else $sth->execute(array("$udi.%"));
    $result = $sth->fetch();

    $newserial = (int) substr($result['maxregid'],13,4) + 1;
    $newsub = (int) substr($result['maxregid'],17,3) + 1;

    $newserial = str_pad($newserial, 4,'0',STR_PAD_LEFT);
    $newsub = str_pad($newsub, 3,'0',STR_PAD_LEFT);

    if ($udi == "")
    {
        $reg_id = '00.x000.000:' . $newserial . '.001';
    }
    else
    {
        $reg_id = $udi.'.'.$newsub;
    }

    if ($title == "" OR $abstrct == "" OR $pocemail == "" OR $pocname == "" OR $dataset_originator == "")
    {
        $dMessage = 'Not all required fields where filled out!';
        drupal_set_message($dMessage,'warning');
    }
    else
    {
        //date_default_timezone_set('UTC');
        $now = date('c');
        $ip = $_SERVER['REMOTE_ADDR'];


        $title = pg_escape_string($title);
        $abstrct = pg_escape_string($abstrct);

        if (!$_SESSION['submitok']) {
            if ($servertype == "upload") {
                $home_dir = getHomedir($uid);
                $home_dir = preg_replace('/\/+$/','',$home_dir);
                $dest_dir = "$home_dir/incoming";
                if (!file_exists($dest_dir)) {
                    $dest_dir = "/san/home/upload/$uid/incoming";
                    if (!file_exists("/san/home/upload/$uid")) mkdir("/san/home/upload/$uid");
                    if (!file_exists($dest_dir)) mkdir($dest_dir);
                }

                $data_file_path = '';
                if (array_key_exists('upload_dataurl',$_POST)) $data_file_path = $_POST['upload_dataurl'];
                if (array_key_exists('datafile',$_FILES) and !empty($_FILES["datafile"]["name"])) {
                    if ($_FILES['datafile']['error'] > 0) {
                        echo "Error uploading data file: " . $_FILES['datafile']['error'] . "<br>";
                    }
                    else {
                        move_uploaded_file($_FILES["datafile"]["tmp_name"],"$dest_dir/" . $_FILES["datafile"]["name"]);
                        $data_file_path = "file://$dest_dir/" . $_FILES["datafile"]["name"];
                    }
                }

                $metadata_file_path = '';
                if (array_key_exists('upload_metadataurl',$_POST)) {
                    $metadata_file_path = $_POST['upload_metadataurl'];
                }
                if (array_key_exists('metadatafile',$_FILES) and !empty($_FILES["metadatafile"]["name"])) {
                    if ($_FILES['metadatafile']['error'] > 0) {
                        echo "Error upload metadata file: " . $_FILES['metadatafile']['error'] . "<br>";
                    }
                    else {
                        move_uploaded_file($_FILES["metadatafile"]["tmp_name"],"$dest_dir/" . $_FILES["metadatafile"]["name"]);
                        $metadata_file_path = "file://$dest_dir/" . $_FILES["metadatafile"]["name"];
                    }
                }

                $SQL = "INSERT INTO registry ( registry_id, data_server_type, dataset_udi, dataset_title, dataset_abstract,
                                               dataset_poc_name, dataset_poc_email, url_data, url_metadata, access_status,
                                               data_source_pull, doi, generatedoi, submittimestamp, userid, dataset_originator )
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
                $sth = $DBH->prepare($SQL);
                $result = $sth->execute(array( $reg_id, $servertype, $udi, $title, $abstrct,
                                               $pocname, $pocemail, $data_file_path, $metadata_file_path, $avail,
                                               'Yes', $doi, $generatedoi, $now, $uid, $dataset_originator ));

                $dataurl = $data_file_path;
                $metadataurl = $metadata_file_path;
            }

            if ($servertype == "HTTP") {
                $SQL = "INSERT INTO registry ( registry_id, data_server_type, dataset_udi, dataset_title, dataset_abstract,
                                               dataset_poc_name, dataset_poc_email, url_data, url_metadata, username,
                                               password, availability_date, authentication, access_status, access_period,
                                               access_period_start, access_period_weekdays, data_source_pull, doi, generatedoi,
                                               submittimestamp, userid, dataset_originator )
                        VALUES ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
                $sth = $DBH->prepare($SQL);
                $result = $sth->execute(array( $reg_id, $servertype, $udi, $title, $abstrct,
                                               $pocname, $pocemail, $dataurl, $metadataurl, $uname,
                                               $pword, $availdate, $auth, $avail, $whendl,
                                               "$dlstart$timezone", $weekdayslst, $pullds, $doi, $generatedoi,
                                               $now, $uid, $dataset_originator ));
            }

            if ($servertype == "SFTP") {
                $SQL = "INSERT INTO registry ( registry_id, data_server_type, dataset_udi, dataset_title, dataset_abstract,
                                               dataset_poc_name, dataset_poc_email, url_data, url_metadata, access_status,
                                               data_source_pull, doi, generatedoi, submittimestamp, userid, dataset_originator )
                        VALUES ( ?,?,?,?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?);";
                $sth = $DBH->prepare($SQL);
                $result = $sth->execute(array( $reg_id, $servertype, $udi, $title, $abstrct,
                                               $pocname, $pocemail, $sshdatapath, $sshmetadatapath, $avail,
                                               'Yes', $doi, $generatedoi, $now, $uid, $dataset_originator ));
                $dataurl = $sshdatapath;
                $metadataurl = $sshmetadatapath;
            }

            if ($result)
            {
                $dMessage = "Thank you for your submission. Please email <a href=\"mailto:griidc@gomri.org?subject=DOI Form\">griidc@gomri.org</a> if you have any questions.";
                drupal_set_message($dMessage,'status');
                $_SESSION['submitok'] = true;
            }
            else
            {
                $dMessage= "A database error happened, please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.<br/>".$sth->errorInfo();
                drupal_set_message($dMessage,'error',false);
            }
        }
        else
        {
            $dMessage= "Sorry, the data was already succesfully submitted. Please email <a href=\"mailto:griidc@gomri.org?subject=REG Form\">griidc@gomri.org</a> if you have any questions.";
            drupal_set_message($dMessage,'warning',false);
            $_SESSION['submitok'] = true;
        }

    }
}
else
{
    $_SESSION['submitok'] = false;
}

if ($_SESSION['submitok'])
{
    include 'submit.php';
    # trigger filer
    system('/usr/local/griidc/filer/trigger-filer');
}
else
{
    echo '<table  border="0">';
    echo '<tr>';
    echo '<td width="60%" style="vertical-align: top; background: transparent;">';
    include 'reg_form.php';
    echo '</td>';
    echo '<td width="*">&nbsp;&nbsp;</td>';
    echo '<td width="40%" style="vertical-align: top; background: transparent;">';
    include '/usr/local/share/GRIIDC/php/sidebar.php';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
};

?>





