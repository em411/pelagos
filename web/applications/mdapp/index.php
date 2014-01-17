<?php
# METADATA APPROVAL APPLICATION
# Author: Michael Scott Williamson  DEC 2013-JAN 2014

# Note: hardcoded smtp.tamucc.edu, triton.tamucc.edu (ldap) in file.

# LOGFILE - SET THIS ACCORDINGLY
$GLOBALS['logfile_location'] = '/home/users/mwilliamson/hg/mdapp/log/logfile.txt';

# database utilities
require_once("/usr/local/share/GRIIDC/php/db-utils.lib.php");
# Framework (model/view)
require_once '/usr/local/share/Slim/Slim/Slim.php';
# templating engine - views
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';
# GRIIDC drupal extensions to allow use of drupal-intended code outside of drupal
require_once '/usr/local/share/GRIIDC/php/drupal.php';
# PHP streams anything in an includes/ directory.  This is for use WITH slim.
# if not using slim, use aliasIncludes.php instead.
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';
# various functions for accessing the RIS database
require_once '/usr/local/share/GRIIDC/php/rpis.php';
# various functions for accessing GRIIDC datasets
require_once '/usr/local/share/GRIIDC/php/datasets.php';
# misc utilities and stuff...
require_once '/usr/local/share/GRIIDC/php/utils.php';
# local functions for data-discovery module
require_once 'lib/search.php';
# LDAP functionality
require_once '/usr/local/share/GRIIDC/php/ldap.php';

# add js library - informs drupal to add these standard js libraries upstream.  
# can also use drupal_add_js to specify a full path to a js library to include.
# similarly, there is a drupal_add_css function.  These js includes are sent
# to the browser at the time drupal sends its own.  "system" is the main
# drupal module. 
drupal_add_library('system', 'ui.tabs');

global $user;

$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['TwigView']['twigDirectory'];

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]"));
    $app->view()->appendData(array('pageName' => $GLOBALS['PAGE_NAME']));
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/', function () use ($app) {
    $stash=index($app);
    $stash['m_dataset']['accepted']    = GetMetadata('accepted');
    $stash['m_dataset']['submitted']   = GetMetadata('submitted');
    $stash['srvr'] = "https://$_SERVER[HTTP_HOST]";
    return $app->render('html/main.html',$stash);
});

// Download from file on disk - probably going away
$app->get('/download-metadata/:udi', function ($udi) use ($app) {
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
    } else {
        drupal_set_message("Error retrieving metadata file: file not found: $met_file",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page (is there a better way to do this?
    }
});

// Download from XML in database
$app->get('/download-metadata-db/:udi', function ($udi) use ($app) {
    # This SQL uses a subselect to resolve the newest registry_id
    # associated with the passed in UDI.
    $sql = "
    select 
        metadata_xml, 
        coalesce(
            cast(
                xpath('/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]/text()',metadata_xml,
                    ARRAY[
                    ARRAY['gmi', 'http://www.isotc211.org/2005/gmi'],
                    ARRAY['gmd', 'http://www.isotc211.org/2005/gmd'],
                    ARRAY['gco', 'http://www.isotc211.org/2005/gco']
                    ]
                ) as character varying
            ), 
            dataset_metadata
        ) 

    as filename  
    FROM metadata left join registry on registry.registry_id = metadata.registry_id
    WHERE 
        metadata.registry_id = (   select registry_id 
                                    from curr_reg_view 
                                    where dataset_udi = ?
                                )";

    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $raw_data = $data->fetch(); 
    if ($raw_data) {
        // We changed from generating a filename to using the filename referenced in the XML.
        //$filename = "$udi-metadata.xml";
        $filename = preg_replace(array('/{/','/}/'),array('',''),$raw_data['filename']);
        # colons aren't allowed in filenames so substitute dash '-' character instead.
        $filename = preg_replace("/:/",'-',$filename); 
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for i.e.
        header("Content-Type: text/xml");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . strlen($raw_data['metadata_xml']));
        header("Content-Disposition: attachment; filename=$filename");
        ob_clean();
        flush();
        print $raw_data['metadata_xml'];
        exit;
    } else {
        drupal_set_message("Error retrieving metadata from database.",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page (is there a better way to do this?
    }
});

$app->post('/upload-new-metadata-file', function () use ($app) {
    global $user;
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $env = $app->environment();
    $baseUrl = "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]";
    try {
        if (
            !isset($_FILES['newMetadataFile']['error']) ||
            is_array($_FILES['newMetadataFile']['error'])
        ) {
            throw new RuntimeException('Invalid parameters.');
        }

        switch ($_FILES['newMetadataFile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }

        /*
        if ($_FILES['newMetadataFile']['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }
        */

        // read file into string
        $filename = $_FILES['newMetadataFile']['tmp_name'];
        $orig_filename = $_FILES['newMetadataFile']['name'];

        // pattern match check file       
        if(!preg_match('/-metadata.xml$/',$orig_filename)) {
            throw new RuntimeException('Bad filename: Filename must be "UDI-metadata.xml"');
        } 

        $udi = preg_replace('/-metadata.xml$/','',$orig_filename); # need to verify this!
        $udi = preg_replace('/-/',':',$udi);

        $fhandle = fopen($filename,"r");
        $raw_xml = fread($fhandle,filesize($filename));
        fclose($fhandle);

        // load XML into DOM
        $doc = new DomDocument('1.0','UTF-8');
        $tmpp = @$doc->loadXML($raw_xml);
        if (!$tmpp) {
            $err = libxml_get_last_error();
            $err_str = $err->message;
            throw new RuntimeException("Malformed XML: The XML file supplied could not be parsed. ($err_str)");
        }
   
        // also load as simplxml object for quick xpath tests
        $xml = simplexml_import_dom($doc);
        
        // Check to see if filename matches existing UDI.
        if(!checkForUDI($udi)) {
            throw new RuntimeException("The UDI $udi is not found in the registry.");
        }

        // Check to see if filename matches XML internal filename reference 
        $loc_1_xpath = "/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]"; # as filename
        $loc_1 = $xml->xpath($loc_1_xpath);
        $loc_1_val = $loc_1[0][0];
        if(!preg_match("/^$orig_filename$/",$loc_1_val)) {
                throw new RuntimeException('xpath test failed:  Filename uploaded does not match filename referenced in XML.');
        }

        // Check to see if filename matches XML internal UDI reference #1
        $loc_2_xpath = "/gmi:MI_Metadata/gmd:dataSetURI[1]/gco:CharacterString[1]"; # as UDI
        $loc_2 = $xml->xpath($loc_2_xpath);
        $loc_2_val = $loc_2[0][0];
        if(!preg_match("/$udi$/",$loc_2_val)) { # URL must end with UDI
                throw new RuntimeException('xpath test failed:  UDI in filename uploaded does not match UDI referenced in XML.');
        }
        
        // Check to see if filename matches XML internal UDI reference #2 
        $loc_3_xpath = "/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1]";
        $loc_3 = $xml->xpath($loc_3_xpath);
        $loc_3_val = $loc_3[0][0];
        if(!preg_match("/$udi$/",$loc_3_val)) { # URL must end with UDI
                throw new RuntimeException('xpath test failed:  UDI in filename uploaded does not match UDI referenced in XML.');
        }
        
        // Check keyword element(s) to verify there aren't commas included. 
        $check_4_xpath = "/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:descriptiveKeywords[1]/gmd:MD_Keywords[1]/gmd:keyword/gco:CharacterString";
        $check_4 = $xml->xpath($check_4_xpath);
        foreach ($check_4 as $node) {
            if(preg_match("/,/",$node)) { # URL must end with UDI
                throw new RuntimeException("GRIIDC XML check failed: XML contains commas in keyword element. ($node)");
            } 
        }
        
        // Check that time period description contains either the phase 'ground condition' or 'modeled period'
        $check_5_xpath = "/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:temporalElement[1]/gmd:EX_TemporalExtent[1]/gmd:extent[1]/gml:TimePeriod[1]/gml:description";
        $check_5 = $xml->xpath($check_5_xpath);
        $ok=0;
        foreach ($check_5 as $node) {
            if(preg_match("/ground condition/i",$node)) { # URL must end with UDI
                $ok++;
            } 
            if(preg_match("/modeled period/i",$node)) { # URL must end with UDI
                $ok++;
            }
        }
        if ($ok != 1) {
            throw new RuntimeException("GRIIDC XML check failed: XML time period description needs to indicate 'ground condition' xor 'modeled period'");
        }

        // Determine geometry type
        if ($geo = $xml->xpath('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_BoundingPolygon[1]/gmd:polygon[1]/gml:Polygon[1]')) {
            // Polygon - Ideally this is case
            $geoflag='yes';
        } elseif ($geo = $xml->xpath('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_GeographicBoundingBox')) {
            // If metadata has a bounding box, convert it to a polygon.
            $coords=array();
            $bounds=array('westBoundLongitude','eastBoundLongitude','southBoundLatitude','northBoundLatitude');
            foreach ($bounds as $boundry) {
                $coords[$boundry] = $xml->xpath("/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_GeographicBoundingBox/gmd:$boundry/gco:Decimal");
            }
            // enumerate polygons clockwise & repeat first point as last
            $coord_list  = $coords['northBoundLatitude'][0].','.$coords['westBoundLongitude'][0].' ';
            $coord_list .= $coords['northBoundLatitude'][0].','.$coords['eastBoundLongitude'][0].' ';
            $coord_list .= $coords['southBoundLatitude'][0].','.$coords['eastBoundLongitude'][0].' ';
            $coord_list .= $coords['southBoundLatitude'][0].','.$coords['westBoundLongitude'][0].' ';
            $coord_list .= $coords['northBoundLatitude'][0].','.$coords['westBoundLongitude'][0];

            $xpathdoc = new DOMXpath($doc);
            $searchXpath = "/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox";
            $elements = $xpathdoc->query($searchXpath);
            $node = $elements->item(0);

            if ($elements->length > 0) {
                $parent = $node->parentNode;
                $parent->removeChild($node);

                $newnode = createXmlNode($doc,$parent,'gmd:EX_BoundingPolygon');
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gmd:polygon');
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gml:Polygon');
                $attr = $doc->createAttribute('gml:id');
                $attr->value = "Polygon";
                $newnode->appendChild($attr);
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gml:exterior');
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gml:LinearRing');
                $parent = $newnode;

                addXMLChildValue($doc,$parent,'gml:coordinates',$coord_list);
                $geoflag='yes';
                $msg = "A bounding box was detected.  This has been converted into a polygon.";
                drupal_set_message($msg,'warning');
            }
        } else {
            $geoflag='no';
        }

        $dbms = OpenDB("GOMRI_RW");
        try {
            $doc->normalizeDocument();
            $doc->formatOutput=true;
            $xml_save=$doc->saveXML(); 

            // clean up formatting via tidy 
            $tidy_config = array('indent' => true,'indent-spaces' => 4,'input-xml' => true,'output-xml' => true,'wrap' => 0);
            $tidy = new tidy;
            $tidy->parseString($xml_save, $tidy_config, 'utf8');
            $tidy->cleanRepair();

            // substitute exterior for interior (always)
            if (preg_match('/gml:interior>/',$tidy)) {
                $tidy = preg_replace('/gml:interior>/','gml:exterior>',$tidy);
                drupal_set_message('Exterior polygon boundries assumed','warning');
            }

            $dbms->beginTransaction();

            // query database for current (highest) registry_id for particular UDI
            $sql = "select max(registry_id) as newest_reg from registry where substring(registry_id, 1, 16) = ?";
            $data = $dbms->prepare($sql);
            $data->execute(array($udi));
            $tmp=$data->fetchAll(); $reg_id=$tmp[0]['newest_reg'];
            
            // query database to deteremine if metadata table is populated for this
            // registry ID, set bool variable.
            $sql = "SELECT COUNT(*) as cnt FROM metadata where registry_id = ?";
            $data = $dbms->prepare($sql);
            $data->execute(array($reg_id));
            $tmp=$data->fetchAll(); 
            $has_metadata_in_db=false;
            $has_metadata_in_db = ($tmp[0]['cnt'] ==  1);

            // if user has opted to submit GML override data, (checkbox and content)
            // remove the polygon (if any) from the GML and replace with this one.
            // If the original data had a bounding box, it will already be a polygon
            // by this point.
            $flagged_gmloverride=false;
            if (isset($_POST['overrideGML']) and $_POST['overrideGML']=='on' and isset($_POST['GMLOverride'])) {
                $coordinate_list=$_POST['GMLOverride'];
                $flagged_gmloverride=true;
                // xpath locate/remove polygon
    
                $doc2 = new DomDocument('1.0','UTF-8');
                $tmpp = @$doc2->loadXML($tidy);
                if (!$tmpp) {
                    $err = libxml_get_last_error();
                    $err_str = $err->message;
                    throw new RuntimeException("Malformed XML: The XML file supplied could not be parsed. ($err_str)");
                }
    
                $xpathdoc = new DOMXpath($doc2);
                $searchXpath = "/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_BoundingPolygon";
                $elements = $xpathdoc->query($searchXpath);
                $node = $elements->item(0);

                if ($elements->length > 0) {
                    $parent = $node->parentNode;
                    $parent->removeChild($node);

                    $newnode = createXmlNode($doc2,$parent,'gmd:EX_BoundingPolygon');
                    $parent = $newnode;
                    $newnode = createXmlNode($doc2,$parent,'gmd:polygon');
                    $parent = $newnode;
                    $newnode = createXmlNode($doc2,$parent,'gml:Polygon');
                    $attr = $doc2->createAttribute('gml:id');
                    $attr->value = "Polygon";
                    $newnode->appendChild($attr);
                    $parent = $newnode;
                    $newnode = createXmlNode($doc2,$parent,'gml:exterior');
                    $parent = $newnode;
                    $newnode = createXmlNode($doc2,$parent,'gml:LinearRing');
                    $parent = $newnode;
                    addXMLChildValue($doc2,$parent,'gml:coordinates',$coordinate_list);
            
                    $doc2->normalizeDocument();
                    $doc2->formatOutput=true;
                    $tidy=$doc2->saveXML(); // should still be clean without a 2nd run through tidy 

                    $geoflag='yes';
                    $msg = "The GML from file has been overridden by user.";
                    drupal_set_message($msg,'warning');
                }
            }

            $geo_status='Nothing to verify';
            $geometery=null;
            if ($geoflag=='yes') {
            // attempt to have PostGIS validate any geometry, if found.
                $xml = simplexml_load_string($tidy);
                $geo = $xml->xpath('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_BoundingPolygon[1]/gmd:polygon[1]/gml:Polygon[1]');
                $sql2="select ST_GeomFromGML('".$geo[0]->asXML()."', 4326) as geometry";
                $data2 = $dbms->prepare($sql2);
                if ($data2->execute()) {
                    $geo_status = 'Verified by PostGIS as OK';
                    $tmp=$data2->fetchAll();
                    $geometry=$tmp[0]['geometry'];
                } else {
                    $dbErr = $data2->errorInfo();
                    $geo_status = "<font color=red>Rejected by PostGIS - ".$dbErr[2]."</font>";
                    throw new RuntimeException("PostGIS rejected geometry supplied");
                }
            }
                
            // insert (or update) data in metadata table
            $sql = '';
            if ($has_metadata_in_db) {
                $sql = "update metadata set metadata_xml=?, geom=?where  registry_id = ?";
            } else {
                $sql = "insert into metadata ( metadata_xml, geom, registry_id ) values (?,?,?)";
            }
            $data3 = $dbms->prepare($sql);
            if(!$data3->execute(array($tidy,$geometry,$reg_id,))) {
                $err=$data3->errorInfo();
                $err_str=$err[2];
                throw new RuntimeException("Error saving to database: $err_str (p2=$geometry)");
            }
            
            // update approved flag, if selected
            $flagged_accepted=false;
            $sql = '';
            if (isset($_POST['approveMetadata']) and $_POST['approveMetadata']=='on') {
                $flagged_accepted=true;
                $sql = "update registry set metadata_status = 'Accepted' where registry_id = ?";
            $data4 = $dbms->prepare($sql);
                if(!$data4->execute(array($reg_id,))) {
                    $err=$data4->errorInfo();
                    $err_str=$err[2];
                    throw new RuntimeException("Error saving to database: $err_str");
                }
            }

            // send email if approved and mail flag is set
            $dm_contacted=false;
            $dataManager=getDataManager($udi); #array  ('full name', 'email')
            $dataManager['email']='fightingtexasaggie@gmail.com';
            $userMail=getUserMail($user->name); #array  ('full name', 'email')
            if (isset($_POST['approveMetadata']) and $_POST['approveMetadata']=='on' and isset($_POST['contactOwner']) and $_POST['contactOwner']=='on') {
                $dm_contacted=true;
                sendEmail($dataManager['email'],$userMail['email'],"$udi metadata","The metadata for $udi has been approved by GRIIDC.  Thank you!");
            }
             
            $thanks_msg = "Thank you ".$user->name.".  The metadata file for registry ID $reg_id has been recorded into the database.
                            <p>
                            Details:
                                <ul>
                                    <li> Registry ID: <a href=\"$protocol$env[SERVER_NAME]/data/$udi/\" target=0>$reg_id</a></li>
                                    <li> UDI: $udi</a></li>
                                    <li> Uploaded filename: $orig_filename</li>
                                    <li> Geometry Detected: $geoflag</li>
                                    <li> Geometry Status: $geo_status </li>
                                </ul>
                            </p>";

            drupal_set_message($thanks_msg,'status');
            $loginfo=$user->name." successfully uploaded metadata for $reg_id";
            if($flagged_gmloverride){ $loginfo .= " and GML was overridden via interface"; }
            if($flagged_accepted) {$loginfo .= " and data was flagged as accepted";}
            if($dm_contacted) { $loginfo .= " and data manager was emailed"; }
            $loginfo .= '.'; // Punctuation is important.
            writeLog($loginfo);

            $dbms->commit();

        } catch (RuntimeException $ee){
            $dbms->rollBack();
            throw $ee;
        }
    } catch (RuntimeException $e) {
        $err_str=$e->getMessage();
        drupal_set_message($user->name.": File upload error: $err_str",'error');
        writeLog($user->name." ".$err_str);
    }
    echo "<a href=.>Continue</a>";
});

function index($app) {
    drupal_add_js("/$GLOBALS[PAGE_NAME]/includes/js/mdapp.js",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/mdapp.css",array('type'=>'external'));
    $stash['defaultFilter'] = $app->request()->get('filter');
    return $stash;
}

$app->run();

function addXMLChildValue($doc,$parent,$fieldname,$fieldvalue) {
    $fieldvalue = htmlspecialchars($fieldvalue, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
    $child = $doc->createElement($fieldname);
    $child = $parent->appendChild($child);
    $value = $doc->createTextNode($fieldvalue);
    $value = $child->appendChild($value);
    return $child;
}

function createXmlNode($doc,$parent,$nodeName) {
    $node = $doc->createElement($nodeName);
    $node = $parent->appendChild($node);
    return $node;
}

function checkForUDI($udi) {
    $sql = "SELECT COUNT(*) FROM curr_reg_view where dataset_udi = ?";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $result = $data->fetchAll();
    $count = $result[0]['count'];
    return ($count==1);
}
            
function getDataManager($udi) {
    // returns: array  ('full name', 'email')
    $sql = 'SELECT 
    "EmailInfo_Address", coalesce("Person_HonorificTitle",\'\')||
    \' \'||"Person_FirstName"||\' \'||coalesce("Person_MiddleName",\'\')||
    \' \'||"Person_LastName"||\' \'||coalesce("Person_NameSuffix",\'\') as fullname
    FROM 
    "HRI"."Dept-GoMRIPerson-Project-Role", 
    "HRI"."EmailInfo", 
    "HRI"."Person",
    "HRI"."Project"
    WHERE 
    "EmailInfo"."Person_Number" = "Person"."Person_Number" AND
    "Person"."Person_Number" = "Dept-GoMRIPerson-Project-Role"."Person_Number"
    AND "Dept-GoMRIPerson-Project-Role"."ProjRole_Number" = 3
    AND "Dept-GoMRIPerson-Project-Role"."Project_Sequence" = "Project"."Project_Sequence"
    AND "Project"."FundingEnvelope_Cycle" = ? and "Project"."Project_Sequence" = ?';

    $dbms = OpenDB("GRIIDC_RO");
    $data = $dbms->prepare($sql);

    $fundingCycle=substr($udi,0,1).'0'.substr($udi,1,1);
    $fundingCycle=preg_replace('/Y01/','B01',$fundingCycle);
    $projSec=substr($udi,4,3);
    $data->execute(array($fundingCycle,$projSec));
    $result = $data->fetchAll();
    // will only have one
    $email = $result[0]['EmailInfo_Address'];
    $fullname = $result[0]['fullname'];
    $ret['fullname']=$fullname;
    $ret['email']=$email;
    return $ret;
}

function getUserMail($gomri_userid) {
    $ldap = connectLDAP('triton.tamucc.edu');
    $baseDN = 'dc=griidc,dc=org';
    $userDNs = getDNs($ldap,$baseDN,"uid=$gomri_userid");
    if (count($userDNs) > 0) {
        $userDN = $userDNs[0]['dn'];
        $attributes = getAttributes($ldap,$userDN,array('cn','mail'));
        if (count($attributes) > 0) {
            if (array_key_exists('cn',$attributes)) $cn = $attributes['cn'][0];
            if (array_key_exists('mail',$attributes)) $mail = $attributes['mail'][0];
            $ret['fullname']=$cn;
            $ret['email']=$mail;
            return $ret;
        }
    }
}

function sendEmail($to,$from,$sub,$message) {
    ini_set("SMTP","smtp.tamucc.edu" );
    $header = "From: <$from>\r\n";
    $header .= "CC: $from\r\n";
    mail($to,$sub,$message,$header); 
}

function GetMetadata($type) {
    $type=strtolower($type);
    switch($type) {
        case "accepted":
            $sql = "SELECT metadata_status, url_metadata, dataset_udi, dataset_metadata, (metadata_xml is not null) as hasxml
                    FROM curr_reg_view left join metadata
                    ON curr_reg_view.registry_id = metadata.registry_id 
                    where metadata_status = 'Accepted' 
                    and url_metadata like '/sftp/data/%.met' 
                    order by curr_reg_view.registry_id";
            break;
        case "submitted":
            #$sql = "SELECT metadata_status, url_metadata, dataset_udi, dataset_metadata
            $sql = "SELECT metadata_status, url_metadata, dataset_udi, dataset_metadata, (metadata_xml is not null) as hasxml
                    FROM curr_reg_view left join metadata
                    ON curr_reg_view.registry_id = metadata.registry_id 
                    where metadata_status = 'Submitted' 
                    and url_metadata like '/sftp/data/%.met' 
                    order by curr_reg_view.registry_id";
            break;
    }
    if(isset($sql)) {       
        $dbms = OpenDB("GOMRI_RO");
        $data = $dbms->prepare($sql);
        $data->execute();
        return $data->fetchAll();
    } else {
        return;
    }
}

// Eventually this really needs to go into the database in
// some official capacity
function writeLog($message) {
    $logfile_location = $GLOBALS['logfile_location'];
    $dstamp = date('YmdHis');
    file_put_contents($logfile_location,"$dstamp:$message\n", FILE_APPEND);
}

?>
