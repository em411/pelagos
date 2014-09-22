<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';
require_once '/usr/local/share/GRIIDC/php/auth.php'; # for user_is_logged_in_somehow()

drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/xmlverbatim.css",array('type'=>'external'));
drupal_add_css('/includes/qTip2/jquery.qtip.min.css',array('type'=>'external'));
drupal_add_css('/data-discovery/includes/css/dataset_download.css',array('type'=>'external'));
drupal_add_css('/data-discovery/includes/css/logins.css',array('type'=>'external'));
drupal_add_library('system', 'jquery.cookie');

drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'ui.button');

drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/details.css",array('type'=>'external'));
drupal_add_js('/includes/openlayers/lib/OpenLayers.js',array('type'=>'external'));
drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false',array('type'=>'external'));
drupal_add_js('/includes/geoviz/geoviz.js','external');
drupal_add_js('/data-discovery/js/search.js',array('type'=>'external'));

drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/status.css",array('type'=>'external'));

$GLOBALS['griidc'] = parse_ini_file('/etc/griidc.ini',true);
$GLOBALS['config'] = parse_ini_file('config.ini',true);

require_once 'Twig_Extensions_GRIIDC.php';

include_once '/usr/local/share/GRIIDC/php/pdo.php';

require_once '/usr/share/pear/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($loader,array('autoescape' => false));
$twig->addExtension(new Twig_Extensions_GRIIDC());

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$pageLessBaseUrl="$protocol$_SERVER[SERVER_NAME]";
$server_name = $_SERVER['SERVER_NAME'];

$udi='';
$dsscript='';
$prow ='';
$mrow ='';
$mprow ='';
$drow = '';

$URI = preg_split('/\?/',$_SERVER['REQUEST_URI']);

$URIs = preg_split('/\//',$_SERVER['REQUEST_URI']);

$udi = urldecode($URIs[2]);
$logged_in = user_is_logged_in_somehow(); # returns bool, true if logged in.

if ($udi <> '')
{
    $configini = parse_ini_file("/etc/griidc/db.ini",true);
    $pconfig = $configini["GOMRI_RW"];

    $mconfig = $configini["RIS_RO"];

    $dbconnstr = 'pgsql:host='. $pconfig["host"];
    $dbconnstr .= ' port=' . $pconfig["port"];
    $dbconnstr .= ' dbname=' . $pconfig["dbname"];
    $dbconnstr .= ' user=' . $pconfig["username"];
    $dbconnstr .= ' password=' . $pconfig["password"];

    $pconn = pdoDBConnect($dbconnstr);
    
    # Toggle per ini file parameter the enforcement of dataset downloadability requiring accepted metadata 
    $condCase = '';
    if( (isset($GLOBALS['griidc']['syswide']['enforce_approved_metadata'] ) and ( $GLOBALS['griidc']['syswide']['enforce_approved_metadata'] == 1 ))) {
        $condCase = "WHEN metadata_status <> 'Accepted' THEN 1";
    }
    
    $pquery = "
    SELECT * , ST_AsText(metadata.geom) AS \"the_geom\",
    CASE WHEN datasets.dataset_udi IS NULL THEN registry.dataset_udi ELSE datasets.dataset_udi END AS dataset_udi,
    CASE WHEN registry.dataset_title IS NULL THEN title ELSE registry.dataset_title END AS title,

    CASE WHEN status = 2 THEN 10
         WHEN status = 1 THEN 1
         ELSE 0
    END AS identified,

    CASE WHEN registry.registry_id IS NULL OR url_data IS NULL THEN 0
         ELSE 10
    END AS registered,

    CASE WHEN metadata_dl_status = 'Completed' THEN
             CASE WHEN metadata_status = 'Accepted' THEN 10
                  WHEN metadata_status = 'InReview' THEN 2
                  ELSE 1
             END
         ELSE 0
    END AS metadata,

    CASE WHEN dataset_download_status = 'Completed' THEN
             CASE $condCase
                  WHEN access_status = 'None' THEN 10
                  WHEN access_status = 'Approval' THEN 9
                  WHEN access_status = 'Restricted' THEN 8
                  ELSE 0
             END
         WHEN dataset_download_status = 'RemotelyHosted' THEN
             CASE WHEN access_status = 'None' THEN 7
                  WHEN access_status = 'Approval' THEN 6
                  WHEN access_status = 'Restricted' THEN 5
                  ELSE 0
             END
         ELSE 0
    END AS available

    FROM registry_view registry
    LEFT OUTER JOIN datasets ON substr(registry.registry_id,0,17) = datasets.dataset_udi
    LEFT OUTER JOIN metadata on registry.registry_id = metadata.registry_id
    WHERE registry.registry_id LIKE '$udi%'
    ;
    ";

    $prow = pdoDBQuery($pconn,$pquery);
    
    $prow = $prow[0];

    $dquery = "select * from datasets where dataset_udi='$udi';";

    $drow = pdoDBQuery($pconn,$dquery);
    
    $drow = $drow[0];

    if ($prow["the_geom"] == null OR $prow == null)
    {
        if ($prow["metadata_xml"] == "")
        {
            $dsscript = "addImage('$_SERVER[SCRIPT_NAME]/includes/images/nodata.png',0.4);$('#metadatadl').button('disable');dlmap.makeStatic();";
        }
        else
        {
            $dsscript = "dlmap.addImage('$_SERVER[SCRIPT_NAME]/includes/images/labonly.png',0.4);dlmap.makeStatic();";
        }
    }
    else
    {
        $dsscript = 'dlmap.addFeatureFromWKT("'. $prow['the_geom'] .'",{"udi":"'.$prow['dataset_udi'].'"});dlmap.gotoAllFeatures();';
    }

    $dbconnstr = 'mysql:host='. $mconfig["host"];
    $dbconnstr .= ';port=' . $mconfig["port"];
    $dbconnstr .= ';dbname=' . $mconfig["dbname"];
    $mconn = new PDO($dbconnstr,
        $mconfig["username"],
        $mconfig["password"],
        array(PDO::ATTR_PERSISTENT => false));

    $mquery = "  SELECT * FROM Projects
    JOIN Programs ON Projects.Program_ID = Programs.Program_ID
    LEFT OUTER JOIN FundingSource ON  FundingSource.Fund_ID = Programs.Program_FundSrc
    WHERE Programs.Program_ID = '".$prow["project_id"]."'
    AND Projects.Project_ID = '".$prow["task_uid"]."'
    ;
    ";

    $mrow = pdoDBQuery($mconn,$mquery);
    
    $mrow = $mrow[0];
    
    $mquery = "
    SELECT 
	People_FirstName, People_LastName,
    Institution_Name, Department_URL, Department_Name, 
    Department_Addr1, Department_Addr2,
    Department_City, Department_State, Department_Zip, Department_Country, People_Email
    FROM People
    LEFT OUTER JOIN Institutions ON Institutions.Institution_ID = People.People_Institution
    LEFT OUTER JOIN Departments ON Departments.Department_ID = People.People_Department
    
    WHERE People_ID = ".$prow["primary_poc"]."
    ;
    ";

    $mprow = pdoDBQuery($mconn,$mquery);

    $mprow = $mprow[0]; 
}



function transform($xml, $xsl) {
    if ($xml <> "" AND $xml != null)
    {

        $xml_doc = new DOMDocument();
        $xml_doc->loadXML($xml);

        // XSL
        $xsl_doc = new DOMDocument();
        $xsl_doc->load($xsl);

        // Proc
        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl_doc);
        $newdom = $proc->transformToDoc($xml_doc);

        return $newdom->saveXML();
    }
    else
    {
        return "No Metadata Available";
    }
}


if ($prow != null)
{

?>

<script>

$(document).ready(function() {
    // If cookie is set and we are logged in (per php variable as a literal in js) remove it and initiate download
    if ((<?php if ($logged_in) { print "1"; } else { print "0";} ?>) && (typeof $.cookie('dl_attempt_udi_cookie') != 'undefined')) {
        var dl_cookie = $.cookie('dl_attempt_udi_cookie');
        $.cookie("dl_attempt_udi_cookie", null, { path: "/", domain: "<?php print "$server_name"; ?>" });
        showDatasetDownload(dl_cookie);
    }
});

var dlmap = new GeoViz();

(function ($) {
    $(function() {

        resizeMap();

        $( window ).resize(function()
        {
            resizeMap();
        });

        $("#rawxml").width($(document).width()*.90);

        $("#tabs").tabs({ heightStyle: "content" });

        $("#xmlradio").buttonset();

        $("#xmlraw").click(function() {
            $("#formatedxml").hide();
            $("#rawxml").show();
        });

        $("#xmlformated").click(function() {
            $("#formatedxml").show();
            $("#rawxml").hide();
        });

        dlmap.initMap('dlolmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':true,'labelAttr':'udi'});

        $("#downloadds").button().click(function() {
            if(<?php echo "\"".$prow['dataset_download_status']."\"" ?> == "RemotelyHosted") {
                showDatasetDownloadExternal('<?php echo $udi;?>')
            } else {
                showDatasetDownload('<?php echo $udi;?>')
            }
        });
        
        $("#downloaddsden").button();

        $("#metadatadl").button().click(function() {
            window.location = '<?php echo "$pageLessBaseUrl/metadata/$udi"; ?>';
        });

        $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                event: "mouseleave blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "qtip-default qtip-shadow qtip-tipped"
            }
        });

        $("#downloadds").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Download Dataset'
            }
        });
        
        $("#downloaddsden").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'This dataset is not currently available for download.'
            }
        });
        
        $("#downloaddsdenmd").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'This dataset is not currently available for download until its metadata is approved.'
            }
        });

        $("#metadatadl").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Download Metadata'
            }
        });
        
        $("#metadatadl-dis").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Metadata will be available after it is approved.'
            }
        });

        $('td[title]').qtip({
            position: {
                my: 'right bottom',
                at: 'middle left',
                adjust: {
                    x: -2
                },
                viewport: $(window)
            },
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                fixed: true,
                delay: 100
            }
        });

    });

    function resizeMap()
    {
        $("#dlolmap").width($(document).width()*.40);
        mapscreenhgt = $("#dlolmap").width()/4*3;
        summaryhgt = $("#summary").height()
        if (mapscreenhgt > summaryhgt)
        {
            $("#dlolmap").height(mapscreenhgt)
        }
        else
        {
            $("#dlolmap").height(summaryhgt)
        }
    };

    $(document).on('imready', function(e) {
        <?php echo $dsscript;?>
    });
})(jQuery);
</script>

<div id="dataset_download" style="display: none;">
    <div id="dataset_download_close"><input type="image" src="/data-discovery/includes/images/close.gif" onclick="jQuery('#dataset_download').hide();"></div>
    <div id="dataset_download_content">

    </div>
</div>

<div id="pre_login" style="display: none;">
    <div id="pre_login_close"><input type="image" src="/data-discovery/includes/images/close.gif" onclick="jQuery('#pre_login').hide();"></div>
    <div id="pre_login_content">
        <table cellpadding="10">
            <tbody><tr>
                <td colspan="3" align="center">
                    <h3>Please log in for access to this data.</h3>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div id="griidc-logo">
                        <a class="redir_url" href="/auth/cas?dest=<?php echo "$_SERVER[REQUEST_URI]";?>"><img src="/data-discovery/includes/images/GRIIDC-logo.png" alt="GRIIDC logo"></a>
                    </div>
                    <div>
                        GoMRI Users, please use your<br>
                        <a class="redir_url" href="/auth/cas?dest=<?php echo "$_SERVER[REQUEST_URI]";?>">GRIIDC login</a> to download data.
                    </div>
                </td>
                <td><img src="/data-discovery/includes/images/vbar.png"></td>
                <td align="center">
                    <div>
                        <a href="/auth/openid/google?dest=<?php echo "$_SERVER[REQUEST_URI]";?>"><img src="/data-discovery/includes/images/googleauth.png" alt="google auth logo"></a>
                    </div>
                    <div>
                        Members of the public may use their<br>
                        <a href="/auth/openid/google?dest=<?php echo "$_SERVER[REQUEST_URI]";?>">Google login</a> to download data.
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                    <button onclick="jQuery('#pre_login').hide();" style="font-weight:bold;">Cancel</button>
                </td>
            </tr>
        </tbody></table>
    </div>
</div>

<table border="0" width="100%">
    <tr>
        <td width="40%">
            <div id="dlolmap" style="width: 640px;height: 480px;"></div>

        </td>
        <td style="padding:10px;" width="60%" valign="top">
            <div id="summary">
            <?php
            $dl_ok = 0;
            # if either metadata has been approved, or we are not enforcing rule, or flag not set in ini altogether THEN ok to download, otherwise not.
            if( ($prow['metadata_status'] == 'Accepted') or (!(( isset($GLOBALS['griidc']['syswide']['enforce_approved_metadata'] ) and ( $GLOBALS['griidc']['syswide']['enforce_approved_metadata'] == 1 ))))) {
                $dl_ok = 1;
            } else {
                $dl_ok = 0;
            }
            echo $twig->render('summary.html', array('pdata' => $prow,'mdata' => $mrow,'mpdata' => $mprow, 'baseurl' => $_SERVER['SCRIPT_NAME'], 'dl_ok' => $dl_ok));
            ?>
            </div>
        </td>
        </tr>
</table>

<div>
    <div id="tabs" style="width:100%">
        <ul>
            <li><a href="#tabs-1">Details</a></li>
            <li><a href="#tabs-2">Metadata</a></li>
            <!--
            <li><a href="#tabs-3">Publications</a></li>
            <li><a href="#tabs-4">Manifest</a></li>
            -->
        </ul>
        <div class="tabb" id="tabs-1">

            <?php echo $twig->render('details.html', array('pdata' => $prow,'mdata' => $mrow,'mpdata' => $mprow)); ?>
        </div>
        <div class="tabb" id="tabs-2" style="overflow:auto;word-wrap:break-word;height:100%;">
            <div id="xmlradio">
                <input type="radio" id="xmlformated" name="radio" checked="checked"><label for="xmlformated">Formatted</label>
                <input type="radio" id="xmlraw" name="radio" ><label for="xmlraw">Raw</label>
            </div>
            <p>
            <div id="formatedxml">
            <?php

            $xml = '';
            $xsl = 'xsl/xml-to-html-ISO.xsl';

            if ($prow <>'')
            {
                $xml = $prow["metadata_xml"];
            }

            echo transform($xml,$xsl);

            ?>
            </div>
            <div id="rawxml" style="display:none;">
                <?php
                    $xml = '';

                    $xsl = 'xsl/xmlverbatim.xsl';

                    if ($prow <>'')
                    {
                        $xml = $prow["metadata_xml"];
                    }

                    echo transform($xml,$xsl);

                ?>
                </div>
            </p>
        </div>
    </div>
</div>

<?php
}
elseif ($drow != null)
{
    #Has DIF, but NO registry
?>
<p>
<h1>Dataset not found</h1>
    This dataset has been identified, but has not yet been registered.<br/>
    If you are experiencing difficulties, please contact <a href="mailto:griidc@gomri.org">GRIIDC</a>.
</p>
<?php
}
else
{
    #Has NO DIF, and NO registry
?>
<p>
<h1>Dataset not found</h1>
No dataset has been identified or registered with the UDI: <?php echo "$udi";?><br/>
If you are experiencing difficulties, please contact <a href="mailto:griidc@gomri.org">GRIIDC</a>.
</p>

<?php };?>
