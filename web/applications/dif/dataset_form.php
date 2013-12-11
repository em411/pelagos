<?php
// Module: dif.php
// Author(s): Jew-Lee Irena Lann
// Last Updated: 21 August 2012
// Parameters: On modification/view Database parameters are taken to autofill else no parameters are taken in.
// Returns: Form 
// Purpose: Wrapper for form and action scripts to update database & email at later date.
//FORM 


$js = '
(function ($) {
    $().ready(function() {
    $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
        position: {
        adjust: {
        method: "shift shift"
        },
        my: "middle left",
        at: "middle right",
        viewport: $(window)
        },
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
        classes: "ui-tooltip-shadow ui-tooltip-tipped"
    }
    });

';

foreach ($pu as $pus)
    {
 
    $js .= '$("#i'. $pus .'").qtip({
    content: $("#'.$pus.'_tip"),
    position: {
    target: $(\'*[id="i'. $pus  .'"]\')
    }
    });
    
    ';

};

$js .= ' 
       
    });

})(jQuery);';


drupal_add_js("$js",array('type'=>'inline'));
?>

<script type="text/javascript">
   $("#commentForm").validate();
</script>
<style>
    #commentForm .textareacontainer {
        position: relative;
        height: 60px;
    }
    #commentForm textarea {
        position: absolute;
        left: 0px;
        right: 0px;
        height: 50px;
    }
</style>

<?php

if (!isset($_POST['submit']) and isset($_GET['uid']) and $status == 0) {
    $message = "This record has not yet been submitted for approval. Please click \"Submit &amp; Done\" when you are ready to submit this DIF.";
    drupal_set_message($message,'warning');
}

?>


<form class="cmxform" id="commentForm" name="ed" action="" method="post">

<div class="cleair" style="width:auto; padding:10px;">
    <input type="hidden" name=flag value=<?PHP echo "$flag"; ?>>
    <input type="hidden" name=modts VALUE=<?PHP echo "$m[0]";?> >
    <input type="hidden" name="dataset_udi" VALUE="<?PHP echo "$m[25]";?>">

    <fieldset>
        <p><STRONG> NOTE: </STRONG><FONT COLOR="grey"> This <I>Dataset Information Form</I> is a supplement to the Data Management Plan that defines what datasets are expected to be collected/generated by the project. 
        The information you provide via this form will assist GRIIDC in designing its infrastructure, identifying areas which need special attention, and allocating resources accordingly. We understand that some of 
        the answers to the following questions may not be exactly known at this time or are subject to change; this form allows users to revise or add information as necessary. Please use the UDI of the dataset when reporting issues. If you require assistance in completing
        this form, do not hesitate to contact GRIIDC (email: <A HREF=mailto:griidc@gomri.org>griidc@gomri.org</A>).</FONT></p>
        <p align="right"><img src="/dif/images/pdf.png" width="27" height="32" alt="PDF" /> <a href="/dif/docs/DIF_User_Ref.pdf" target="_blank">Download a User Guide</a></p>
    </fieldset>

    <h1>Dataset Information&nbsp&nbsp&nbsp&nbsp;<?PHP echo "<span  style=float:right; ><img style=\"width:24px;height:24px;\" src=\"/dif/images/$status.png\"></SPAN>"; ?> </h1>
    <strong>NOTICE:</strong> Fields that are preceded by an asterisk (<em style="font-weight: bold; vertical-align: top; color:#FF0000;">*</em>) are required inputs. Note that only records that have not been submitted can be edited. Also, <i>Tasks</i> may have more than one 
    <i>Dataset</i>. Submitting a record with the same <i>Task Title</i>, but with a different <i>Dataset Title</i>, produces several <i>Dataset</i> records for that <i>Task</i>.<hr /><br />

    <?PHP if ($status != 0){echo "<div STYLE=text-align:right><a href=\"?\"><IMG SRC=/dif/images/button.png></A></div>"; }?>

   <p><fieldset id="qtask"> 
       <?PHP helps("ctask", "<em class='form'>*</em>Task Title", "itask"); ?>
       <?PHP  if (!$flag){ ?>
           <span id="span1"> 
                <select id="ctask" name="task" style="width:100%;" size="1" onchange="setOptions(document.ed.task.options[document.ed.task.selectedIndex].value);" class="required" >
                    <option value="" selected="selected">[SELECT A TASK]</option>
                    <?PHP getTaskOptionList($tasks); ?>
                </select>
           </span>
       <?PHP }else{ ?>
           <span id="span1">
               <select id="ctask" name="task" style="width:100%;" size="1" onchange="setOptions(document.ed.task.options[document.ed.task.selectedIndex].value);" class="required" <?PHP if ($status != 0){echo "disabled";} ?>  >
                   <option value=' '>[SELECT A TASK]</option>
                   <?PHP getTaskOptionList($tasks, $mtask); ?>
               </select>
       <?PHP } ?>
    </fieldset></p> 

    <?php if ($flag and $m[25] != '') { ?>
    <p><fieldset id="qudi">
        <?PHP helps("cudi", "Unique Dataset Identifer (UDI)", "iudi"); ?>
        <?php echo "<span style='margin-left:20px; font-size:125%;'><strong><em>$m[25]</em></strong></span>"; ?>
    </fieldset></p>
    <?php } ?>
 
    <p><fieldset id="qtitle">
        <?PHP helps("ctitle", "<em class='form'>*</em>Dataset Title", "ititle"); ?>
        <div class="textareacontainer">
        <textarea <?PHP if ($status != 0){echo "disabled";} ?> name="title" id="ctitle" class="required" maxlength="200" rows=3 cols=70 onkeypress="return imposeMaxLength(this, 200);" ><?PHP if ($flag=="update"){echo $m[2];} ?></textarea>
        </div>
    </fieldset></p>

    <table WIDTH="100%"><tr><td> 
    <p><fieldset id="qppoc">
       <?PHP helps("cppoc", "<em class='form'>*</em>Primary Point of Contact", "ippoc"); ?>
           <select name="ppoc" id="cppoc" <?PHP if ($status != 0) echo "disabled"; ?> class="required" style="width:300px;">
               <?PHP if (!$flag) { ?>
                   <option value="">Please Choose a Task First</option>
               <?PHP } else getPersonOptionList($m[19],$m[1],$m[24]); ?>
           </select>
    </fieldset></p>
    </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
    <p><fieldset id="qspoc">
       <?PHP helps("cspoc", "Secondary Point of Contact", "ispoc"); ?>
           <select name="spoc" id="cspoc" style="width:300px;" <?PHP if ($status != 0){echo "disabled";} ?> >
               <?PHP if (!$flag) { ?>
                   <option value="">Please Choose a Task First</option>
               <?PHP } else getPersonOptionList($m[20],$m[1],$m[24]); ?>
           </select>
    </fieldset></p> 
    </td></tr></table>

    <p><fieldset id="qabstract">
        <?PHP helps("cabstract", "<em class='form'>*</em>Dataset Abstract", "iabstract"); ?>
            <div class="textareacontainer">
           <textarea <?PHP if ($status != 0){echo "disabled";} ?> name="abstract" id="cabstract" class="required" maxlength="4000" rows=3 cols=70 onkeypress="return imposeMaxLength(this, 4000);" ><?PHP if ($flag=="update"){echo $m[3];} ?></textarea> 
            </div>
    </fieldset></p>

    <p><fieldset>
        <?PHP helps("cdatatype", "Dataset Type", "idatatype"); ?>
        <TABLE id="cdatatype" WIDTH="100%"><TR>
        <div id="qdatatype">
          <TD width="39%"> <input type="checkbox" name="sascii"  value="Structured, Generic Text/ASCII File (CSV, TSV)" <?PHP if ($status != 0){echo "disabled";} ?>  <?PHP if (($flag=="update")&&($dtt[0]=="Structured, Generic Text/ASCII File (CSV, TSV)")){echo " checked";} ?>  />Structured, Generic Text/ASCII File (CSV, TSV)&nbsp;&nbsp;&nbsp;&nbsp; </TD>
          <TD width="27%"> <input type="checkbox" name="images" value="Images" <?PHP if ($status != 0){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtt[2] == "Images")){echo " checked";} ?>  />Images&nbsp;&nbsp;(JPG, TIFF, PNG, GIF)&nbsp; </TD>
          <TD width="34%"> <input type="checkbox" name="gml" value="GML/XML Structured" <?PHP if ($status != 0){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtt[6] == "GML/XML Structured")){echo " checked";} ?>  />Structured, GML/XML-base&nbsp;&nbsp;&nbsp; </TD>
</tr> <tr>
         <td> <input type="checkbox" name="uascii" value="Unstructured, Generic Text/ASCII File (TXT)" <?PHP if ($status != 0){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtt[1] == "Unstructured, Generic Text/ASCII File (TXT)")){echo " checked";} ?>  />
Unstructured, Generic Text/ASCII File (TXT, ASC)&nbsp;&nbsp;&nbsp;&nbsp; </TD>
        <td> <input type="checkbox" name="netCDF" value="CDF/netCDF" <?PHP if ($status != 0){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtt[3] == "CDF/netCDF")){echo " checked";} ?>  />CDF/netCDF&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td COLSPAN="2">
           <TABLE WIDTH="95%"><TR>
                <TD> Others: </TD><TD><input style= "width:100%;" type="text" name="otherdty" width=100%   <?PHP if ($status != 0){echo "disabled";} ?>  value=<?PHP  if (($flag=="update")&&($dtt[7])){echo "'$dtt[7]'";} ?>></td></tr></table></td>
</tr> <tr> </div>
<td> 
<?PHP #if ($flag=="update"){  if ($dtt[4] == "video"){$toggle="true";}else{$toggle=="false";} } else{ $toggle="this.checked"; } ?>
<input type="checkbox" onclick="enable_text(this.checked)" name="dtvideo" value="Video" <?PHP if ($status != 0){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtt[4] == "Video")){echo " checked";} ?> />
Video&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
<tr><td COLSPAN="3"> 
<p id="qvideo">
        <?PHP helps("cvideo", "Video Attributes (if applicable)", "ivideo"); ?>
        <div class="textareacontainer">
            <textarea name="video" <?PHP if ($status != 0){echo "disabled";} ?> id="cvideo" maxlength="200" rows=3 cols=70  onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag=="update"){echo "$dtt[5]";} ?></textarea>
        </div>
    </p>
</td></tr></table>
</fieldset></p>

    <p><fieldset>
       <?PHP helps("cdatafor", "Dataset For", "idatasetfor"); ?>
       <TABLE WIDTH="100%"><TR>
             <TD><input type="checkbox" name="eco"  value="Ecological/Biological" <?PHP if ($status != 0){echo "disabled";} ?>  <?PHP if (($flag=="update")&&($dtf[0]=="Ecological/Biological")){echo " checked";} ?>  />
                  Ecological/Biological&nbsp;&nbsp;&nbsp;&nbsp; </TD>       
             <TD><input type="checkbox" name="phys" value="Physical Oceanographical" <?PHP if ($status != 0){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtf[1] == "Physical Oceanographical")){echo " checked";} ?>  />
                  Physical Oceanography&nbsp;&nbsp;&nbsp;&nbsp; </TD>
             <TD> <input type="checkbox" name="atm" value="Atmospheric" <?PHP if ($status != 0){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtf[2] == "Atmospheric")){echo " checked";} ?>  /> 
                  Atmospheric&nbsp;&nbsp;&nbsp;&nbsp; </TD>
         </TR><TR>
             <TD> <input type="checkbox" name="ch" value="Chemical" <?PHP if ($status != 0){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[3] == "Chemical")){echo " checked";} ?>  />
                  Chemical&nbsp;&nbsp;&nbsp;&nbsp;</TD>
             <TD> <input type="checkbox" name="geog" value="Human Health" <?PHP if ($status != 0){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[4] == "Human Health")){echo " checked";} ?>  />
                  Human Health&nbsp;&nbsp;&nbsp; </TD>
             <TD> <input type="checkbox" name="scpe" value="Social/Cultural/Political" <?PHP if ($status != 0){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[5] == "Social/Cultural/Political")){echo " checked";} ?>  /> 
                  Social/Cultural/Political&nbsp;&nbsp;&nbsp;&nbsp; </TD>
        </TR><TR>
             <TD> <input type="checkbox" name="econom" value="Economics" <?PHP if ($status != 0){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[6] == "Economics")){echo " checked";} ?> />
                 Economics&nbsp;&nbsp;&nbsp;&nbsp; </TD>
             <TD></TD> 
             <TD>
                <TABLE WIDTH="95%"><TR>
                     <TD> Others: </TD>
                     <TD><input style= "width:100%;" type="text" name="dtother" width=100% <?PHP if ($status != 0){echo "disabled";} ?>  value=<?PHP  if (($flag=="update")&&($dtf[8])){echo "'$dtf[8]'";} ?>></TD>
                </TR></TABLE>
             </TD>
        </TR></TABLE>
    </fieldset></p>

    <?PHP if (($flag == "update")&&($m[6]=="< 1GB")){$size[0]=" checked";} ?>
    <?PHP if (($flag == "update")&&($m[6]=="1GB-10GB")){$size[1]=" checked";} ?>
    <?PHP if (($flag == "update")&&($m[6]=="10GB-200GB")){$size[2]=" checked";} ?>
    <?PHP if (($flag == "update")&&($m[6]=="200GB-1TB")){$size[3]=" checked";} ?>
    <?PHP if (($flag == "update")&&($m[6]=="1TB-5TB")){$size[4]=" checked";} ?>
    <?PHP if (($flag == "update")&&($m[6]==">5TB")){$size[5]=" checked";} ?>
 
    <p><fieldset>
        <?PHP helps("csize", "<em class='form'>* </em>Approximate Dataset Size", "isize"); ?>
            <input type="radio" name="size" <?PHP if ($status != 0){echo "disabled";} ?> value="< 1 Gb"   <?PHP echo " $size[0]"; ?> checked="checked"> < 1GB &nbsp;&nbsp;&nbsp;&nbsp; 
            <input type="radio" name="size" <?PHP if ($status != 0){echo "disabled";} ?> value="1GB-10GB" <?PHP echo " $size[1]"; ?>> 1GB-10GB&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="size" <?PHP if ($status != 0){echo "disabled";} ?> value="10GB-200GB"<?PHP echo " $size[2]"; ?>> 10GB-200GB&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="size" <?PHP if ($status != 0){echo "disabled";} ?> value="200GB-1TB"<?PHP echo " $size[3]"; ?>> 200GB-1TB&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="size" <?PHP if ($status != 0){echo "disabled";} ?> value="1TB-5TB"<?PHP echo " $size[4]"; ?>> 1TB-5TB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="size" <?PHP if ($status != 0){echo "disabled";} ?> value=">5TB" <?PHP echo " $size[5]"; ?>>  >5TB
    </fieldset></p>

    <p><fieldset> 
        <?PHP helps("coberservations", "Phenomenon/Variables Observed or Generated", "iobservation"); ?>
            <div class="textareacontainer">
                <textarea  name="observation" <?PHP if ($status != 0){echo "disabled";} ?> id="coberservations" rows=3 cols=70  maxlength="300"   onkeypress="return imposeMaxLength(this, 300);"><?PHP if ($flag=="update"){echo $m[7];} ?></textarea>
            </div>
    </fieldset></p>

    <p><fieldset>
        <?PHP helps("capproach", "Procedure for Acquiring or Creating the Data", "iapproach"); ?>
        <TABLE WIDTH="100%"><TR>
             <TD><input type="checkbox" name="field" id="capproach" <?PHP if ($status != 0){echo "disabled";} ?> value="Field Sampling" <?PHP  if (($flag=="update")&&($aq[0] == "Field Sampling")){echo " checked";} ?> />
                 Field Sampling&nbsp;&nbsp;&nbsp;&nbsp;</TD>
             <TD><input type="checkbox" name="sim" id="capproach"  <?PHP if ($status != 0){echo "disabled";} ?> value="Simulated or Generated" <?PHP  if (($flag=="update")&&($aq[1] == "Simulated or Generated")){echo " checked";} ?> />
                 Simulated/Generated&nbsp;&nbsp;&nbsp;&nbsp;</TD>
             <TD><input type="checkbox" name="lab" id="capproach"  <?PHP if ($status != 0){echo "disabled";} ?> value="Labratory" <?PHP  if (($flag=="update")&&($aq[2] == "Labratory")){echo " checked";} ?> />
                 Laboratory&nbsp;&nbsp;&nbsp;&nbsp;</TD>
        </TR><TR>
             <TD><input type="checkbox" name="lit" id="capproach"  <?PHP if ($status != 0){echo "disabled";} ?> value="Literature Based"  <?PHP  if (($flag=="update")&&($aq[3] == "Literature Based")){echo " checked";} ?> />
                 Literature Based&nbsp;&nbsp;&nbsp;&nbsp;</TD>
             <TD><input type="checkbox" name="remote" id="capproach" <?PHP if ($status != 0){echo "disabled";} ?> value="Remote Sensing" <?PHP  if (($flag=="update")&&($aq[4] == "Remote Sensing")){echo " checked";} ?> />
                 Remote Sensing&nbsp;&nbsp;&nbsp;&nbsp;</TD>
             <TD>
                 <TABLE WIDTH="100%"><TR>
                     <TD> Others:</TD><TD> <input style= "width:95%;" type="text" <?PHP if ($status != 0){echo "disabled";} ?> name="approachother" value=<?PHP  if (($flag=="update")&&($aq[5])){echo "'$aq[5]'";} ?>> </TD>
                 </TR></TABLE>
            </TD>
        </TR></TABLE>
    </fieldset></p>

   <p><fieldset> 
        <?PHP helps("cdate", "Data Sampling/Generation Period", "idate"); ?>
       <center> <STRONG>Start Date</STRONG> 
           <select name=smo size=1 <?PHP if ($status != 0){echo "disabled";} ?> class=required><?PHP array_walk($change, 'test_print', $l[1]);?> </select>
           <select name=sda size=1 <?PHP if ($status != 0){echo "disabled";} ?> class=required> <?PHP for ($z = 1; $z <= 31; $z++) {echo "<option value=$z"; if ($l[2] == $z){echo " SELECTED";} echo ">$z</option>\n\r";}?> </select>
           <select name=sye size=1 <?PHP if ($status != 0){echo "disabled";} ?> class=required> <?PHP for ($z = 2009; $z <= 2022; $z++) {echo "<option value=$z";  if ($l[0] == $z){echo " SELECTED";} echo">$z\n\r";}?> </SELECT> &nbsp;&nbsp; to&nbsp;&nbsp; 
           <select name=emo size=1 <?PHP if ($status != 0){echo "disabled";} ?> class=required> <?PHP array_walk($change, 'test_print', $n[1]);?> </select>
           <select name=eda size=1 <?PHP if ($status != 0){echo "disabled";} ?> class=required> <?PHP for ($z = 1; $z <= 31; $z++) {echo "<option value=$z"; if ($n[2] == $z){echo " SELECTED";} echo ">$z</option>\n\r";}?> </select>
           <select name=eye size=1 <?PHP if ($status != 0){echo "disabled";} ?> class=required> <?PHP for ($z = 2009; $z <= 2022; $z++) {echo "<option value=$z";  if ($n[0] == $z){echo " SELECTED";} echo">$z\n\r";}?> </SELECT><STRONG> End Date</STRONG>
      </center> 
    </fieldset> </p>

    <p><fieldset>
        <?PHP helps("cgeoloc", "Geographic/Study Area", "igeoloc"); ?>
           <a href="javascript:void(0);" onClick=window.open("/map/","","width=1050,height=740,left=400,top=400,toolbar=0,status=0,scollbars=1,resizable=0,location=0");><img src="/map/images/red-dot.png" height=15> click here to select points on a map</a>
            <div class="textareacontainer">
               <textarea name="geoloc"<?PHP if ($status != 0){echo "disabled";} ?>  id="cgeoloc"  rows=3 cols=70 maxlength=200 onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag=="update"){echo $m[11];} ?></textarea>
            </div>
    </fieldset></p>

    <p><fieldset>
        <?PHP helps("chistorical", "Historical Data References (if applicable)", "ihistorical"); ?>
            <div class="textareacontainer">
                <textarea name="historical" <?PHP if ($status != 0){echo "disabled";} ?> id="chistoric" rows=3 cols=70 maxlength=300 onkeypress="return imposeMaxLength(this, 300);"><?PHP if ($flag=="update"){echo $m[12];} ?></textarea>
            </div>
    </fieldset></p>

    <p><fieldset>
        <?PHP helps("ced", "Metadata Editor to  Use", "ied"); ?>
            <div class="textareacontainer">
                <textarea name="ed" <?PHP if ($status != 0){echo "disabled";} ?> id="ced" rows=3 cols=70 maxlength=300 onkeypress="return imposeMaxLength(this, 300);"><?PHP  if ($flag=="update"){echo $m[13];} ?></textarea>
            </div>
    </fieldset></p>

<?PHP if (isset($m[14]) and !empty($m[14])) { list($stand[0], $stand[1], $stand[2], $stand[3], $stand[4])=explode("|", $m[14] ); } ?>

<table width="100%"><TR><TD>
    <p><fieldset>
        <?PHP helps("cstandards", "Metadata Standards to Use", "istandards"); ?>
             <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="s1" value="ISO19115"<?PHP  if (($flag=="update")&&($stand[0] == "ISO19115")){echo " checked";} ?>  id="cstandards"  />ISO 19115<br />
             <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="s2" value="CSDGM" <?PHP  if (($flag=="update")&&($stand[1] == "CSDGM")){echo " checked";} ?> id="cstandards"  />FGDC-CSDGM<br />
             <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="s3" value="DUBLIN" <?PHP  if (($flag=="update")&&($stand[2] == "DUBLIN")){echo " checked";} ?>  id="cstandards"  />Dublin/Darwin Core<br />
             <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="s4" value="EML" <?PHP  if (($flag=="update")&&($stand[3] == "EML")){echo " checked";} ?>  id="cstandards"  />Ecological Metadata Language (EML)<br />
             <table><tr>
                 <td>Others:</td>
                 <td width="100%"><input style="width:95%;" type="text" <?PHP if ($status != 0){echo "disabled";} ?> name="otherst" value=<?PHP  if (($flag=="update")&&($stand[4])){echo "'$stand[4]'";} ?>></td>
             </tr></table>
    </fieldset></P>
<?PHP if (isset($m[15]) and !empty($m[15])) { list($point[0], $point[1], $point[2], $point[3])=explode("|", $m[15] ); } ?>
</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD> 
    <p><fieldset>
        <?PHP helps("caccess", "Data Access Points", "iaccess"); ?>
           <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="a1" id="caccess" value="FTP" <?PHP  if (($flag=="update")&&($point[0] == "FTP")){echo " checked";} ?> />File Transfer Protocol (FTP)<br />
           <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="a2" id="caccess" value="TDS" <?PHP  if (($flag=="update")&&($point[1] == "TDS")){echo " checked";} ?> />THREDDS Data Server (TDS)<br />
           <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="a3" id="caccess" value="ERDAP" <?PHP  if (($flag=="update")&&($point[2] == "ERDAP")){echo " checked";} ?> />Environmental Research Division's Data Access Program (ERDDAP)<br />
           <br /><table><tr><td>Others:</td><td width="100%"><input style="width:95%;" type="text" <?PHP if ($status != 0){echo "disabled";} ?> name="accessother" value=<?PHP  if (($flag=="update")&&($point[3])){echo "'$point[3]'";} ?>></td></tr></table>
    </fieldset></P>
</TD></TR></TABLE>


<?php if (isset($m[16]) and !empty($m[16])) { list($nta[0], $nta[1], $nta[2], $nta[3], $nta[4], $nta[5], $nta[6])=explode("|", $m[16] ); } ?>
<p><fieldset>
        <?PHP helps("cnational", "National Data Center(s) that the Dataset will be Submitted to", "inational"); ?>
   <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="nat1" value="National Oceanographic Data Center" <?PHP  if (($flag=="update")&&($nta[0] == "National Oceanographic Data Center")){echo " checked";} ?>  id="cnational"  />National Oceanographic Data Center <a href="http://www.nodc.noaa.gov" target="_new">(http://www.nodc.noaa.gov)</a><br />
   <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="nat2" value="US EPA Storet" <?PHP  if (($flag=="update")&&($nta[1] == "US EPA Storet")){echo " checked";} ?> id="cnational"  />US EPA Storet <a href="http://www.epa.gov/storet/wqx" target="_new">(http://www.epa.gov/storet/wqx)</a><br />
   <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="nat3" value="Global Biodiversity Information Facility" <?PHP  if (($flag=="update")&&($nta[2] == "Global Biodiversity Information Facility")){echo " checked";} ?>  id="cnational"  />Global Biodiversity Information Facility <a href="http://www.gbif.org" target="_new">(http://www.gbif.org)</a><br />
   <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="nat4" value="National Center for Biotechnology Information" <?PHP  if (($flag=="update")&&($nta[3] == "National Center for Biotechnology Information")){echo " checked";} ?>  id="cnational"  />National Center for Biotechnology Information <a href="http://www.ncbi.nlm.nih.gov" target="_new">(http://www.ncbi.nlm.nih.gov)</a><br />
   <input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="nat5" value="Data.gov Dataset Management System" <?PHP  if (($flag=="update")&&($nta[4] == "Data.gov Dataset Management System")){echo " checked";} ?>  id="cnational"  />Data.gov Dataset Management System <a href="http://dms.data.gov" target="_new">(http://dms.data.gov)</a><br />
   <hr />
<input type="checkbox" <?PHP if ($status != 0){echo "disabled";} ?> name="nat6" value="Gulf of Mexico Research Initiative Information and Data Cooperative (GRIIDC)" <?PHP  if (($flag=="update")&&($nta[5] == "Gulf of Mexico Research Initiative Information and Data Cooperative (GRIIDC)")){echo " checked";} ?>  id="cnational"  />Gulf of Mexico Research Initiative Information and Data Cooperative (GRIIDC) <a href="https://griidc.gomri.org" target="_new">(https://griidc.gomri.org)</a><br /><hr /> 
   <table>
   <tr>
     <td>Others:</td>
     <td width="100%"><input style="width:95%;" type="text" <?PHP if ($status != 0){echo "disabled";} ?> name="othernat" value=<?PHP  if (($flag=="update")&&($nta[6])){echo "'$nta[6]'";} ?>></td></tr></table>
</fieldset></P>

 <?PHP if (($flag == "update")&&($zz[0]=="No")){$ep[0]=" checked";} ?>
 <?PHP if (($flag == "update")&&($zz[0]=="Yes")){$ep[1]=" checked";} ?>
 <?PHP if (($flag == "update")&&($zz[0]=="Uncertain")){$ep[2]=" checked";} ?>
  
    <p><fieldset>
        <?PHP helps("cprivacy", "Ethical or Privacy Issues", "iprivacy"); ?>
            <input type="radio" <?PHP if ($status != 0){echo "disabled";} ?> name="privacy" id="cprivacy" value="No" <?PHP echo " $ep[0]"; ?>> No<br />
            <input type="radio" <?PHP if ($status != 0){echo "disabled";} ?> name="privacy" id="cprivacy" value="Yes" <?PHP echo " $ep[1]"; ?>> Yes<br />
            <input type="radio" <?PHP if ($status != 0){echo "disabled";} ?> name="privacy" id="cprivacy" value="Uncertain" <?PHP echo " $ep[2]"; ?>> Uncertain &nbsp;&nbsp;&nbsp;
            <table width="100%"><tr>
                <td width="212">If yes or uncertain, please explain:</TD><TD width="657"> 
                   <input style="width:95%" type="text"<?PHP if ($status != 0){echo "disabled";} ?>  name="privacyother" value=<?PHP  if (($flag=="update")&&($zz[1])){echo "'$zz[1]'";}?>></td>
           </tr></table>
    </fieldset></p>

    <p><fieldset>
        <?PHP helps("cremarks", "Remarks", "iremarks"); ?>
            <div class="textareacontainer">
                <textarea name="remarks" <?PHP if ($status != 0){echo "disabled";} ?> id="cremarks"  rows=3 cols=70 maxlength=200 onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag =="update"){echo $m[18];} ?></textarea>
            </div>
    </fieldset></p>

</div>

    <?PHP
        if ((isAdmin() and !array_key_exists('as_user',$_GET))&&(($status ==1)||($status==2))){
    ?>
    <div style="text-align:center;">
        <input class="submit" type="submit" name="accept" value="Accept">
        <input class="submit" type="submit" name="reject" value="Reject">
    </div>
    <?PHP }else{ ?>

    <?PHP if ($status==2){

            $message = "This record has been approved and locked. Please contact <A HREF=\"mailto:griidc@gomri.org?subject=[DIF-Web Request] Record Request To Be Unlocked: ".$_GET['uid']."\">GRIIDC</a> if this record needs to be unlocked.";
            drupal_set_message($message,'status');
          }elseif ($status==1){

            $message = "This record has been locked and is awaiting approval. Please contact <A HREF=\"mailto:griidc@gomri.org?subject=[DIF-Web Request] Record Request To Be Unlocked: ".$_GET['uid']."\">GRIIDC</a> if this record needs to be unlocked.";
            drupal_set_message($message,'warning');


}else{ ?>
    <div style="padding:10px; margin-top:10px;">
        <strong>NOTE:</strong> Clicking the <i>Save & Continue Later</i> or the <i>Submit &amp; Done</i> buttons will save the DIF and clear the form so that you may submit an additional DIF. Please be reminded that clicking the <i>Submit &amp; Done</i> button will lock the record for review, but it can be unlocked for modification by contacting GRIIDC (<a HREF="mailto:griidc@gomri.org">griidc@gomri.org</a>).
    </div>
    <div style="text-align:center;">
        <input class="submit" type="submit" name="later" value="Save &amp; Continue Later">
        <input class="submit" type="submit" name="submit" value="Submit &amp; Done">
    </div>
<?PHP }} ?>
</form>

