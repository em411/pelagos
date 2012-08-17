<?PHP 
################################################################
#                  Includes & Requires                         #
################################################################
include ('/home/users/jlann/public_html/ff/includes/php/functions.php'); 
require ('/home/users/mvandeneijnden/public_html/ProjectDB/getOptionList.php');
################################################################
#                        FORM                                  #
################################################################
?>
<form class="cleair cmxform" id="commentForm" name="ed" action="https://proteus.tamucc.edu/Form2" method="post"> 
<INPUT TYPE="hidden" name=flag value=<?PHP echo "$flag"; ?>>
<INPUT TYPE="hidden" name=modts VALUE=<?PHP echo "$m[0]";?> >

 <fieldset>
<STRONG> NOTE: <FONT COLOR="grey"> This <I>Dataset Information Form</I> is a supplement to the Data Management Plan that defines what datasets are expected to be collected/generated by the project. The inputs to this form are designed to assist GRIIDC design its infrastructure and allocate resources accordingly. The inputs on this form can be modified as the need arises. If you require assistance in completing this form, do not hesitate to contact GRIIDC. <A HREF=mailto:griidc.help@gomri.org>griidc.help@gomri.org</A> </FONT></STRONG>  
 </fieldset>

<H1>Dataset Information&nbsp&nbsp&nbsp&nbsp;<?PHP echo "<SPAN  style=float:right; ><img src=\"https://proteus.tamucc.edu/~jlann/ff/images/$status.png\"></SPAN>"; ?> </H1>
<FONT COLOR="#FF0000">*</FONT>Denotes a field that must be field in.
<hr /><br />

<p><fieldset> <label for="ctask"><em>*</em>Task Title: <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'demo1_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label>
<select name=task style="width:800px;" width="800px"; id="ctask" <?PHP if ($status==2){echo "disabled";} ?>  class="required" >

<option value='0'>[SELECT A TASK]</option>
<?PHP getTaskOptionListByName($lastName,$firstName, $m[1]); ?>
 </select> </fieldset> </p>


<p><fieldset><label for="ctitle"><em>*</em>Dataset Title:<span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'demo2_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span>
</label> <textarea <?PHP if ($status==2){echo "disabled";} ?> name="title" id="ctitle" class="required" rows=3 cols=98  onkeypress="return imposeMaxLength(this, 200);" }><?PHP if ($flag=="update"){echo $m[2];} ?></textarea></fieldset></p>

<table WIDTH="100%"><tr><td> <p> 
<fieldset><label for="cppoc"> <em>*</em> Primary Point of Contact: <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'demo3_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label> 
<select name=ppoc id="cppoc" <?PHP if ($status==2){echo "disabled";} ?>  class="required" style="width:385px;">
  <?PHP  getPersonOptionListByName($lastName,$firstName, $m[19]); ?>
</select></fieldset></p>
</td><td>&nbsp;&nbsp;&nbsp;&nbsp;
</td><td>

<p><fieldset><label for="cspoc"> Secondary Point of Contact: <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'demo4_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label>
<select name=spoc id="cspoc" style="width:385px;" <?PHP if ($status==2){echo "disabled";} ?> >
  <?PHP  getPersonOptionListByName($lastName,$firstName, $m[20]); ?>
</select></fieldset>
</p> </td></tr></table>

<p><fieldset><labelbr for="cabstract"> <b><em>*</em>Dataset Abstract:</b> <span  style="float:right;" class="tooltip" class="tooltip" onmouseover="tooltip.add(this, 'demo5_tip')"><IMG SRC="/sites/all/themes/litejazz/images/info.png"></span></label><br />
<textarea <?PHP if ($status==2){echo "disabled";} ?> name="abstract" id="cabstract" class="required" rows=3 cols=98 onkeypress="return imposeMaxLength(this, 400);" ><?PHP if ($flag=="update"){echo $m[3];} ?></textarea></fieldset></p>



<p><fieldset><b> Dataset Type:</b> <labelbr for="cdatatype"><span  style="float:right;" class="tooltip" class="tooltip" onmouseover="tooltip.add(this, 'demo6_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
<TABLE WIDTH="100%"><TR>


<TD> <input type="checkbox" name="sascii"  value="Structured, Generic Text/ASCII File (CSV, TSV)" <?PHP if ($status==2){echo "disabled";} ?>  <?PHP if (($flag=="update")&&($dtt[0]=="Structured, Generic Text/ASCII File (CSV, TSV)")){echo " checked";} ?>  />Structured, Generic Text/ASCII File (CSV, TSV)&nbsp;&nbsp;&nbsp;&nbsp; </TD>
<TD> <input type="checkbox" name="images" value="Images" <?PHP if ($status==2){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtt[2] == "Images")){echo " checked";} ?>  />Images&nbsp;&nbsp;&nbsp; </TD>
<TD> <input type="checkbox" name="gml" value="GML/XML Structured" <?PHP if ($status==2){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtt[6] == "GML/XML Structured")){echo " checked";} ?>  />GML/XML Structured&nbsp;&nbsp;&nbsp; </TD>


</tr> <tr>


<td> <input type="checkbox" name="uascii" value="Unstructured, Generic Text/ASCII File (TXT)" <?PHP if ($status==2){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtt[1] == "Unstructured, Generic Text/ASCII File (TXT)")){echo " checked";} ?>  />Unstructured, Generic Text/ASCII File (TXT)&nbsp;&nbsp;&nbsp;&nbsp; </TD>

<td> <input type="checkbox" name="netCDF" value="netCDF" <?PHP if ($status==2){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtt[3] == "netCDF")){echo " checked";} ?>  />netCDF&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td COLSPAN="2">

<TABLE WIDTH="95%"><TR><TD> Other: </TD><TD><input style= "width:100%;" type="text" name="otherdty" width=100% <?PHP if ($status==2){echo "disabled";} ?>  value=<?PHP  if (($flag=="update")&&($dtt[7])){echo "'$dtt[7]'";} ?>></TD></TR></TABLE>
</td>

</tr> <tr>
<td> <input type="checkbox" onclick="enable_text(this.checked)" name="dtvideo" value="Video" <?PHP if ($status==2){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtt[4] == "Video")){echo " checked";} ?> />Video&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
<tr><td COLSPAN="3"> 


<p><labelbr for="cprovisions"> <b>If Dataset Type is Video, Please Provide Video Attributes:</b> <span  style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo7_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />

<textarea name="video" <?PHP if ($status==2){echo "disabled";} ?> id="cprovisions"  rows=3 cols=98  onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag=="update"){echo "$dtt[5]";} ?></textarea>


</td></tr></table>



</fieldset></p>









<!--
<p><fieldset><labelbr for="cprovisions"> <b>If Dataset Type is Video, Please Provide Video Attributes:</b> <span  style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo7_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
<textarea name="video" <?PHP if ($status==2){echo "disabled";} ?> id="cprovisions"  rows=3 cols=98  onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag=="update"){echo $m[5];} ?></textarea></fieldset>

</p>
-->



<p><fieldset><b> Dataset For:</b> <labelbr for="cdatafor"><span  style="float:right;" class="tooltip" class="tooltip" onmouseover="tooltip.add(this, 'demo23_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
<TABLE WIDTH="100%"><TR><TD> <input type="checkbox" name="eco"  value="Ecological/Biological" <?PHP if ($status==2){echo "disabled";} ?>  <?PHP if (($flag=="update")&&($dtf[0]=="Ecological/Biological")){echo " checked";} ?>  />Ecological/Biological&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="phys" value="Physical Oceanographical" <?PHP if ($status==2){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtf[1] == "Physical Oceanographical")){echo " checked";} ?>  />Physical Oceanographical&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="atm" value="Atmospheric" <?PHP if ($status==2){echo "disabled";} ?>  <?PHP  if (($flag=="update")&&($dtf[2] == "Atmospheric")){echo " checked";} ?>  />Atmospheric&nbsp;&nbsp;&nbsp;&nbsp;
</TD></TR><TR><TD> <input type="checkbox" name="ch" value="Chemical" <?PHP if ($status==2){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[3] == "Chemical")){echo " checked";} ?>  />Chemical&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="geog" value="Geographical" <?PHP if ($status==2){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[4] == "Geographical")){echo " checked";} ?>  />Geographical&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="scpe" value="Social/Cultural/Political" <?PHP if ($status==2){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[5] == "Social/Cultural/Political")){echo " checked";} ?>  />Social/Cultural/Political&nbsp;&nbsp;&nbsp;&nbsp;
</TD></TR><TR><TD> <input type="checkbox" name="econom" value="Economical" <?PHP if ($status==2){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[6] == "Economical")){echo " checked";} ?> />Economical&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="geop" value="Geophysical" <?PHP if ($status==2){echo "disabled";} ?> <?PHP  if (($flag=="update")&&($dtf[7] == "Geophysical")){echo " checked";} ?> />Geophysical&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD><TABLE WIDTH="95%"><TR><TD> Other: </TD><TD><input style= "width:100%;" type="text" name="dtother" width=100% <?PHP if ($status==2){echo "disabled";} ?>  value=<?PHP  if (($flag=="update")&&($dtf[8])){echo "'$dtf[8]'";} ?>></TD></TR></TABLE>
</TD></TR></TABLE></fieldset></p>



 <?PHP if (($flag == "update")&&($m[6]=="< 1GB")){$size[0]=" checked";} ?>
 <?PHP if (($flag == "update")&&($m[6]=="1GB-10GB")){$size[1]=" checked";} ?>
 <?PHP if (($flag == "update")&&($m[6]=="10GB-200GB")){$size[2]=" checked";} ?>
 <?PHP if (($flag == "update")&&($m[6]=="200GB-1TB")){$size[3]=" checked";} ?>
 <?PHP if (($flag == "update")&&($m[6]=="1TB-5TB")){$size[4]=" checked";} ?>
 <?PHP if (($flag == "update")&&($m[6]==">5TB")){$size[5]=" checked";} ?>

<p><fieldset><labelbr for="csize"> <b><em>* </em>Approximate Size:</b> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo8_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br /> 
<input type="radio" name="size" <?PHP if ($status==2){echo "disabled";} ?> value="< 1 Gb"   <?PHP echo " $size[0]"; ?>> < 1GB &nbsp;&nbsp;&nbsp;&nbsp; 
<input type="radio" name="size" <?PHP if ($status==2){echo "disabled";} ?> value="1GB-10GB" <?PHP echo " $size[1]"; ?>> 1GB-10GB&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="size" <?PHP if ($status==2){echo "disabled";} ?> value="10GB-200GB"<?PHP echo " $size[2]"; ?>> 10GB-200GB&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="size" <?PHP if ($status==2){echo "disabled";} ?> value="200GB-1TB"<?PHP echo " $size[3]"; ?>> 200GB-1TB&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="size" <?PHP if ($status==2){echo "disabled";} ?> value="1TB-5TB"<?PHP echo " $size[4]"; ?>> 1TB-5TB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="size" <?PHP if ($status==2){echo "disabled";} ?> value=">5TB" <?PHP echo " $size[5]"; ?> class="required">  >5TB
</fieldset>
</p>

<p><fieldset> <labelbr for="coberservations"> <b>Phenomenon/Variables Being Measured or Sampled:</b> <span  style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo9_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
<textarea  name="observation" <?PHP if ($status==2){echo "disabled";} ?> id="coberservations" rows=3 cols=98  onkeypress="return imposeMaxLength(this, 300);"><?PHP if ($flag=="update"){echo $m[7];} ?></textarea></fieldset></p>

<p><fieldset><labelbr for="capproach"> <b> Approach to Aquiring or Creating the Data: </b> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo10_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
<TABLE WIDTH="100%"><TR><TD>
<input type="checkbox" name="field" id="capproach" <?PHP if ($status==2){echo "disabled";} ?> value="Field Sampling" <?PHP  if (($flag=="update")&&($aq[0] == "Field Sampling")){echo " checked";} ?> />Field Sampling&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="sim" id="capproach"  <?PHP if ($status==2){echo "disabled";} ?> value="Simulated or Generated" <?PHP  if (($flag=="update")&&($aq[1] == "Simulated or Generated")){echo " checked";} ?> />Simulated/Generated&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="lab" id="capproach"  <?PHP if ($status==2){echo "disabled";} ?> value="Labratory" <?PHP  if (($flag=="update")&&($aq[2] == "Labratory")){echo " checked";} ?> />Labratory&nbsp;&nbsp;&nbsp;&nbsp;
</TD></TR><TR><TD> <input type="checkbox" name="lit" id="capproach"  <?PHP if ($status==2){echo "disabled";} ?> value="Literature Based"  <?PHP  if (($flag=="update")&&($aq[3] == "Literature Based")){echo " checked";} ?> />Literature Based&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD> <input type="checkbox" name="remote" id="capproach" <?PHP if ($status==2){echo "disabled";} ?> value="Remote Sensing" <?PHP  if (($flag=="update")&&($aq[4] == "Remote Sensing")){echo " checked";} ?> />Remote Sensing&nbsp;&nbsp;&nbsp;&nbsp;
</TD><TD><TABLE WIDTH="100%"><TR><TD> Other:</TD><TD> <input style= "width:95%;" type="text" <?PHP if ($status==2){echo "disabled";} ?> name="approachother" value=<?PHP  if (($flag=="update")&&($aq[4])){echo "'$aq[5]'";} ?>> </TD></TR></TABLE></TD><TD>
</TABLE>
</fieldset>
</p>

<p><fieldset><labelbr for="cdate"> <span style="float:right; class="tooltip" class="tooltip" onmouseover="tooltip.add(this, 'demo11_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> <b>Sampling Data Generation:</b> </label>
<CENTER> <STRONG>Start Date</STRONG> 
      <select name=smo size=1 <?PHP if ($status==2){echo "disabled";} ?> class=required><?PHP array_walk($change, 'test_print', $l[1]);?> </select>
      <select name=sye size=1 <?PHP if ($status==2){echo "disabled";} ?> class=required> <?PHP for ($z = 2012; $z <= 2022; $z++) {echo "<option value=$z";  if ($l[0] == $z){echo " SELECTED";} echo">$z\n\r";}?> </SELECT> &nbsp;&nbsp; to&nbsp;&nbsp; 
      <select name=emo size=1 <?PHP if ($status==2){echo "disabled";} ?> class=required> <?PHP array_walk($change, 'test_print', $n[1]);?> </select>
      <select name=eye size=1 <?PHP if ($status==2){echo "disabled";} ?> class=required> <?PHP for ($z = 2012; $z <= 2022; $z++) {echo "<option value=$z";  if ($n[0] == $z){echo " SELECTED";} echo">$z\n\r";}?> </SELECT><STRONG> End Date</STRONG>
</center> </fieldset> </p>


<!-- FORM INSERT
<p><fieldset><labelbr for="cgeoloc"> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo12_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"></span> <b>Geographic/Study Area:</b>i<!--<IMG SRC="/sites/all/themes/litejazz/images/marker.png"></label><br /> -->
<?PHP 
#include("../../connect.php");
?>
<!--</fieldset></p>-->

<p><fieldset><labelbr for="cgeoloc"> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo12_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"></span> <b>Geographic/Study Area:</b><!--<IMG SRC="/sites/all/themes/litejazz/images/marker.png">--></label><br /> 
<textarea name="geoloc"<?PHP if ($status==2){echo "disabled";} ?>  id="cgeoloc"  rows=3 cols=98 onkeypress="return imposeMaxLength(this, 300);"><?PHP if ($flag=="update"){echo $m[11];} ?></textarea></fieldset></p>

<p><fieldset><labelbr for="chistoric"> <b>If Using Historic Data, Please List References: </b> <span  style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo13_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"></span></label><br />
<textarea name="historical" <?PHP if ($status==2){echo "disabled";} ?> id="chistoric" rows=3 cols=98  onkeypress="return imposeMaxLength(this, 300);"><?PHP if ($flag=="update"){echo $m[12];} ?></textarea></fieldset></p>


<p><fieldset><labelbr for="ced"> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo14_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> <b>Metadata Editor to be Used:</b> </label><br /> 
<textarea name="ed" <?PHP if ($status==2){echo "disabled";} ?> id="ced" rows=3 cols=98  onkeypress="return imposeMaxLength(this, 300);"><?PHP  if ($flag=="update"){echo $m[13];} ?></textarea></fieldset></p>

<?PHP list($stand[0], $stand[1], $stand[2], $stand[3], $stand[4])=explode("|", $m[14] ); ?>
<TABLE WIDTH="100%"><TR><TD>
<p><fieldset><labelbr for="cstandards"> <b>Metadata Standards:</b> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo15_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br /> 
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="s1" value="ISO19115"<?PHP  if (($flag=="update")&&($stand[0] == "ISO19115")){echo " checked";} ?>  id="cstandards"  />ISO 19115<br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="s2" value="CSDGM" <?PHP  if (($flag=="update")&&($stand[1] == "CSDGM")){echo " checked";} ?> id="cstandards"  />FGDC-CSDGM<br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="s3" value="DUBLIN" <?PHP  if (($flag=="update")&&($stand[2] == "DUBLIN")){echo " checked";} ?>  id="cstandards"  />Dublin/Darwin Core<br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="s4" value="EML" <?PHP  if (($flag=="update")&&($stand[3] == "EML")){echo " checked";} ?>  id="cstandards"  />EML<br />
   <table><tr><td>Other:</td><td width="100%"><input style="width:95%;" type="text" <?PHP if ($status==2){echo "disabled";} ?> name="otherst" value=<?PHP  if (($flag=="update")&&($stand[4])){echo "'$stand[4]'";} ?>></td></tr></table>
</fieldset></P>


<?PHP list($point[0], $point[1], $point[2], $point[3])=explode("|", $m[15] );?>
</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD> <p><fieldset><labelbr for="caccess"> <b>Data Access Point: </b> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo16_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span></label><br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="a1" id="caccess" value="FTP" <?PHP  if (($flag=="update")&&($point[0] == "FTP")){echo " checked";} ?> />File Transfer Protocol (FTP)<br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="a2" id="caccess" value="TDS" <?PHP  if (($flag=="update")&&($point[1] == "TDS")){echo " checked";} ?> />THREDDS Data Server (TDS)<br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="a3" id="caccess" value="ERDAP" <?PHP  if (($flag=="update")&&($point[2] == "ERDAP")){echo " checked";} ?> />Envionmental Research Division's Data Access Program (ERRDAP)<br />
   <br /><table><tr><td>Other:</td><td width="100%"><input style="width:95%;" type="text" <?PHP if ($status==2){echo "disabled";} ?> name="accessother" value=<?PHP  if (($flag=="update")&&($point[3])){echo "'$point[3]'";} ?>></td></tr></table>
</fieldset></P>
</TD></TR></TABLE>


<?php list($nta[0], $nta[1], $nta[2], $nta[3], $nta[4], $nta[5], $nta[6])=explode("|", $m[16] );?>
<p><fieldset><labelbr for="cnational"> <b>List/Identify any National Data Center(s) Used for this Dataset:</b> <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'demo17_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="nat1" value="National Oceanographic Data Center" <?PHP  if (($flag=="update")&&($nta[0] == "National Oceanographic Data Center")){echo " checked";} ?>  id="cnational"  />National Oceanographic Data Center <a href="http://www.nodc.noaa.gov" target="_new">(http://www.nodc.noaa.gov)</a><br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="nat2" value="US EPA Storet" <?PHP  if (($flag=="update")&&($nta[1] == "US EPA Storet")){echo " checked";} ?> id="cnational"  />US EPA Storet <a href="http://www.epa.gov/storet/wqx" target="_new">(http://www.epa.gov/storet/wqx)</a><br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="nat3" value="Global Biodiversity Information Facility" <?PHP  if (($flag=="update")&&($nta[2] == "Global Biodiversity Information Facility")){echo " checked";} ?>  id="cnational"  />Global Biodiversity Information Facility <a href="http://www.gbig.org" target="_new">(http://www.gbif.org)</a><br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="nat4" value="National Center for Biotechnology Information" <?PHP  if (($flag=="update")&&($nta[3] == "National Center for Biotechnology Information")){echo " checked";} ?>  id="cnational"  />National Center for Biotechnology Information <a href="http://www.ncbi.nlm.nih.gov" target="_new">(http://www.ncbi.nlm.nih.gov)</a><br />
   <input type="checkbox" <?PHP if ($status==2){echo "disabled";} ?> name="nat5" value="Data.gov Dataset Management System" <?PHP  if (($flag=="update")&&($nta[4] == "Data.gov Dataset Management System")){echo " checked";} ?>  id="cnational"  />Data.gov Dataset Management System <a href="http://www.dms.data.gov" target="_new">(http://www.dms.data.gov)</a><br />
   <table><tr><td>Other:</td><td width="100%"><input style="width:95%;" type="text" <?PHP if ($status==2){echo "disabled";} ?> name="othernat" value=<?PHP  if (($flag=="update")&&($nta[5])){echo "'$nta[5]'";} ?>></td></tr></table>
</fieldset></P>
<!--
<p><fieldset><labelbr for="cnational"> <b>List/Identify any National Data Center(s) Used for this Dataset:</b> <span style="float:right;" class="tooltip" onmouseover="tooltip.add(this, 'demo17_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
<p><fieldset><labelbr for="cnational"> <b>List/Identify any National Data Center(s) Used for this Dataset:</b> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo17_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
<textarea name="national" id="cnational"  rows=3 cols=98  <?PHP if ($status==2){echo "disabled";} ?> onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag =="update"){echo $m[16];} ?></textarea></fieldset></p>

-->

 <?PHP if (($flag == "update")&&($zz[0]=="No")){$ep[0]=" checked";} ?>
 <?PHP if (($flag == "update")&&($zz[0]=="Yes")){$ep[1]=" checked";} ?>
<p><fieldset><labelbr for="privacy"> <b>Ethical or Privacy issues:</b> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo18_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label><br />
   <input type="radio" <?PHP if ($status==2){echo "disabled";} ?> name="privacy" id="cprivacy" value="No" <?PHP echo " $ep[0]"; ?>> No<br />
   <input type="radio" <?PHP if ($status==2){echo "disabled";} ?> name="privacy" id="cprivacy" value="Yes" <?PHP echo " $ep[1]"; ?>> Yes &nbsp;&nbsp;&nbsp;<table width="100%"><tr><td width="100">Please Explain:</TD><TD> <input style="width:95%" type="text"<?PHP if ($status==2){echo "disabled";} ?>  name="privacyother" value=<?PHP  if (($flag=="update")&&($zz[1])){echo "'$zz[1]'";}?>></td></tr></table>


</fieldset></p>

<p><fieldset><labelbr for="cremarks"> <b>Remarks:</b> <span style="float:right; class="tooltip" onmouseover="tooltip.add(this, 'demo19_tip')"> <IMG SRC="/sites/all/themes/litejazz/images/info.png"> </span> </label> <br />
<textarea name="remarks" <?PHP if ($status==2){echo "disabled";} ?> id="cremarks"  rows=3 cols=98  onkeypress="return imposeMaxLength(this, 200);"><?PHP if ($flag =="update"){echo $m[18];} ?></textarea></fieldset></p>
<?PHP if ($status==2){echo "<FONT COLOR=green><strong>This record has been approved and locked.</strong></FONT>";}else{ ?>
<br /> <input class="submit" type="submit" name="later" value="Save &amp; Continue Later" size="30">  <input class="submit" type="submit" name="submit" value="Submit &amp; Done" size="30"> 
<?PHP } ?>
 </form>
