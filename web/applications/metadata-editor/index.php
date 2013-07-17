<?php

include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';
include 'metaData.php';
include 'loadXML.php';
include 'makeXML.php';

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');

drupal_add_css('includes/css/metadata.css',array('type'=>'external'));
drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));
//drupal_add_css('misc/ui/jquery.ui.button.css');
//drupal_add_css('misc/ui/jquery.ui.datepicker.css');
//drupal_add_css('misc/ui/jquery.ui.tabs.css');
//drupal_add_css('misc/ui/jquery.ui.dialog.css');

if (array_key_exists('action',$_GET) and $_GET['action'] == 'help') {
    require 'help.html';
    exit;
}

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
	if ($_FILES["file"]["type"] == "text/xml")
	{
		$xmldoc = loadXML($thefile);
		$dMessage = 'Succesfully loaded file: ' .  $_FILES["file"]["name"];
		drupal_set_message($dMessage,'status');
	}
	else
	{
		$dMessage = 'Sorry.' .  $_FILES["file"]["name"] . ', is not an XML file!';
		drupal_set_message($dMessage,'warning');
	}
}

$mMD = new metaData();

if (isset($xmldoc))
{
	$mMD->xmldoc = $xmldoc;
}

include 'MI_Metadata.php';
$myMImeta = new MI_Metadata($mMD,'MIMeta',"metadata.xml");

echo "\n\n<script type=\"text/javascript\">\n";
$mMD->jsString .= $mMD->twig->render('js/base.js', array('onReady' => $mMD->onReady,'jqUIs' => $mMD->jqUIs,'validateRules' => $mMD->validateRules, 'validateMessages' => $mMD->validateMessages));
echo $mMD->jsString;
echo "</script>\n";

?>

<div style="font-size:smaller;" id="metadialog"></div>
<div style="font-size:smaller;display:none;" id="savedialog">
<span id="dialogtxt">All required fields are complete.<br/>
Your metadata file is ready for download.<br/></span>
<p>
<label for="filename">Please enter a filename</label><input type="text" id="filename" size="50">
</p>
Click OK to download.
</div>
<div style="font-size:smaller;" id="errordialog"></div>

<div id="udidialog" title="Load from UDI">
  <p>Please enter your UDI/Registration ID.</p>
  <form>
  <fieldset>
    <label for="name">UDI</label>
    <input size="40" type="text" name="udifld" id="udifld" class="text ui-widget-content ui-corner-all" />
  </fieldset>
  </form>
</div>

<table class="altrowstable" id="alternatecolor" width="60%" border="0">
	<tr>
		<td width="100%">
			<div id="metatoolbar" class="ui-widget-header ui-corner-all toolbarbutton">
				<button id="upload">Load from File</button>
				<button id="fromudi">Fill from UDI</button>
				<button id="forcesave">Save to File</button>
				<button id="startover">Clear Form</button>
				<button id="generate">Check and Save to File</button>
				<button id="helpscreen">Help</button>
			</div>
			<div id="loadfrm" style="display:none;">
			<frameset>
					Please select a file...
				<form id="uploadfrm" method="post" enctype="multipart/form-data">
					<input onfocus="uploadFile();"  id="file" name="file" type="file" />
				</form>
			</frameset>
			</div>
			<form name="metadata" id="metadata" method="post">
			<fieldset>
				<?php echo $myMImeta->getHTML(); ?>
			</fieldset>
			</form>
		</td>
	</tr>
</table>
