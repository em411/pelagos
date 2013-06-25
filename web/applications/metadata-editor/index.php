<?php
#Show Errors (debug only)
error_reporting(-1);
ini_set('display_errors', '1');

include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';
include 'metaData.php';
include 'loadXML.php';
include 'makeXML.php';

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'ui');
drupal_add_css('includes/css/metadata.css',array('type'=>'external'));
drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

$xmldoc = null;

if (isset($_FILES["file"]))
{
	
	if ($_FILES["file"]["error"] > 0)
	{
		//echo "Error: " . $_FILES["file"]["error"] . "<br>";
		$dMessage = 'Error while loading file: ' .  $_FILES["file"]["error"];
		drupal_set_message($dMessage,'error',false);
	}
	else
	{
		//echo "Upload: " . $_FILES["file"]["name"] . "<br>";
		//echo "Type: " . $_FILES["file"]["type"] . "<br>";
		//echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
		//echo "Stored in: " . $_FILES["file"]["tmp_name"];
		$thefile = $_FILES["file"]["tmp_name"];
		$dMessage = 'Succesfully loaded file: ' .  $_FILES["file"]["name"];
		drupal_set_message($dMessage,'status');
	}
}

if (isset($_GET["dataUrl"]))
{
	$xmlURL = $_GET["dataUrl"];
	$xmldoc = loadXML($xmlURL);
	//$xmldoc->loadXML($xmlString);
}

if (isset($_POST))
{
	if (count($_POST)>1)
	{
		makeXML($_POST,$xmldoc);
	}
}

if (isset($thefile))
{
	$xmldoc = loadXML($thefile);
}

$mMD = new metaData();

if (isset($xmldoc))
{
	$mMD->xmldoc = $xmldoc;
}

include 'MI_Metadata.php';
$myMImeta = new MI_Metadata($mMD,'MIMeta',guid());

echo "\n\n<script type=\"text/javascript\">\n";
$mMD->jsString .= $mMD->twig->render('js/base.js', array('jqUIs' => $mMD->jqUIs,'validateRules' => $mMD->validateRules, 'validateMessages' => $mMD->validateMessages));
echo $mMD->jsString;
echo "</script>\n";

?>

<table class="altrowstable" id="alternatecolor" width="60%" border="0">
	<tr>
		<td width="100%">
			<div id="toolbar" class="ui-widget-header ui-corner-all toolbarbutton">
				<button id="generate">Generate Metadata File</button>
				<button id="upload">Load Metadata File</button>
				<button id="forcesave">Save without Validating</button>
				<button id="startover">Reload the Form</button>
				<!--button id="reset">Clear Current Tab</button-->
			</div>
			<form id="uploadfrm" method="post" enctype="multipart/form-data">
				<input style="display:none;" onchange="uploadFile();" id="file" name="file" type="file" />
			</form>
			<form name="metadata" id="metadata" method="post">
			<fieldset>
				<?php echo $myMImeta->getHTML(); ?>
			</fieldset>
			</form>
		</td>
	</tr>
</table>

