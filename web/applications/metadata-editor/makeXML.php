<?php

function makeXML($xml,$doc)
{
	echo '<pre>';
	var_dump($_POST);
	
	createNodesXML($xml,$doc);
	
	echo '</pre>';
}

function createBlankXML()
{
	$doc = new DomDocument('1.0','UTF-8');
	
	$root = createXmlNode($doc,$doc,'gmi:MI_Metadata');
	
	$root->setAttribute('xmlns','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gco','http://www.isotc211.org/2005/gco');
	$root->setAttribute('xmlns:gmd','http://www.isotc211.org/2005/gmd');
	$root->setAttribute('xmlns:gmi','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gml','http://www.opengis.net/gml/3.2');
	$root->setAttribute('xmlns:gmx','http://www.isotc211.org/2005/gmx');
	$root->setAttribute('xmlns:gsr','http://www.isotc211.org/2005/gsr');
	$root->setAttribute('xmlns:gss','http://www.isotc211.org/2005/gss');
	$root->setAttribute('xmlns:gts','http://www.isotc211.org/2005/gts');
	$root->setAttribute('xmlns:xlink','http://www.w3.org/1999/xlink');
	$root->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
	$root->setAttribute('xsi:schemaLocation','http://www.isotc211.org/2005/gmi http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd');
	
	$doc->normalizeDocument();
	
	$xmlString = $doc->saveXML();
	
	$newdoc = new DomDocument('1.0','UTF-8');
	$newdoc->loadXML($xmlString);
	
	return $newdoc;
}

function createNodesXML($xml,$doc)
{
	
	if (is_null($doc))
	{
		$doc = createBlankXML();
	}
	
	
		
	$root = $doc->firstChild;
	$parent = $doc;
	
	$now = date('c');
	
	$xmlComment = "Created with GRIIDC Metadata Editor V1.0 on $now";
	$commentNode = $doc->createComment($xmlComment);
	$commentNode = $doc->appendChild($commentNode);
	
	$xpathdoc = new DOMXpath($doc);
	
	foreach ($xml as $key=>$val)
	{
		$xpath = "";
		
		$nodelevels = preg_split("/-/",$key);
		
		foreach ($nodelevels as $nodelevel)
		{
			$splitnodelevel = preg_split("/\!/",$nodelevel);
			
			$xpath .= "/" . $splitnodelevel[0];
		}
		
		//echo $xpath . '<br>';
		
		$xpathdoc = new DOMXpath($doc);
		$elements = $xpathdoc->query($xpath);
		
		//var_dump($elements);
		
		if ($elements->length > 0)
		{
			$node = $elements->item(0);
			$val = htmlspecialchars($val, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
			$node->nodeValue = $val;
			$parent = $node->parentNode;
			//echo $parent->nodeName .'<br>';
		}
		else
		{
			$nodelevels = preg_split("/\//",$xpath);
			
			$xpath = "";
			
			//var_dump($nodelevels);
			
			$parent = $doc;
			
			foreach ($nodelevels as $nodelevel)
			{
				if($nodelevel <> "" AND $val<>"!")
				{
					$xpath .= "/" . $nodelevel;
					$elements = $xpathdoc->query($xpath);
													
					echo 'now working: ';
					echo $xpath . '<br>';
					
					//var_dump($elements);
					
					if($elements->length > 0)
					{
						$xpathdocsub = new DOMXpath($doc);
						$subelements = $xpathdocsub->query($xpath);
						$node = $subelements->item(0);
						//$val = htmlspecialchars($val, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
						//$node->nodeValue = $val;
						$parent = $node->parentNode;
						$parentname = $parent->nodeName;
						$parentXpath = $xpath;
						echo "Found path: $xpath. Parent is: " . $parentname . "<br>";
					}
					else
					{
						$xpathdocsub = new DOMXpath($doc);
						$subelements = $xpathdocsub->query($parentXpath);
						$node = $subelements->item(0);
						$parent = $node;
						$parentname = $parent->nodeName;
												
						echo "Found last path: $parentXpath. Old parent is: " . $parentname . "<br>";
						
						$node = $doc->createElement($nodelevel);
						$node = $parent->appendChild($node);
						
						addNodeAttributes($doc,$parent,$node,$nodelevel,$val);
						
						
						echo "Addded Node: $nodelevel<br>";
						
						$parentXpath = $xpath;
					}
				}
				
				$doc->normalizeDocument();
				$xmlString = $doc->saveXML();
				$doc->loadXML($xmlString);
			}
			
			$xpathdocsub = new DOMXpath($doc);
			$subelements = $xpathdocsub->query($parentXpath);
			if ($subelements <> false)
			{
				$node = $subelements->item(0);
				$parent = $node->parentNode;
				$grandparent = $parent->parentNode;
				$nodelevel = $parent->nodeName;
				
				$val = htmlspecialchars($val, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
				$node->nodeValue = $val;
				
				addNodeAttributes($doc,$grandparent,$parent,$nodelevel,$val);
			}
			
			$parent = $doc;
		}
	}
	
	
		
	header("Content-type: text/xml; charset=utf-8"); 
	//header('Content-Disposition: attachment; filename=metadata.xml');
	$doc->normalizeDocument();
	$doc->formatOutput = true;
	ob_clean();
	flush();
	echo $doc->saveXML();
	exit;
		
	//*/
}

function createXmlNode($doc,$parent,$nodeName)
{
	//echo $nodeName;
	$node = $doc->createElement($nodeName);
	$node = $parent->appendChild($node);
	
	addNodeAttributes($doc,$parent,$node,$nodeName);
	
	return $node;
}

function codeLookup($codeList,$codeListValue)
{
	#TODO
	return '000'; 
	
}

function addNodeAttributes($doc,$parent,$node,$fieldname,$fieldvalue=null)
{
	switch ($fieldname)
	{
		case 'gmd:MD_CharacterSetCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_CharacterSetCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_ScopeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ScopeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:CI_RoleCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:CI_DateTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_ProgressCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ProgressCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_KeywordTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_KeywordTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_KeywordTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_KeywordTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:EX_Extent':
		{
			$node->setAttribute('id','boundingExtent');
			break;
		}
		case 'gmd:EX_GeographicBoundingBox':
		{
			$node->setAttribute('id','boundingGeographicBoundingBox');
			break;
		}
		case 'gml:TimePeriod':
		{
			$node->setAttribute('gml:id','boundingTemporalExtent');
			break;
		}
		case 'gmd:version':
		{
			$node->setAttribute('nilReason','Unknown');
			$child = $node->firstChild;
			if (isset($child))
			{
				$node->removeChild($child);
			}

			break;
		}
		case 'gmd:CI_DateTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:keywordtheme':
		{
			$parent->removeChild($node);
			
			$arrkeywords = preg_split("/\;/",$fieldvalue);
			
			foreach ($arrkeywords as $myKeywords)
			{
				$mdkwrd = $doc->createElement('gmd:keyword');
				$mdkwrd = $parent->appendChild($mdkwrd);
				$addnode = addXMLChildValue($doc,$mdkwrd,"gco:CharacterString",$myKeywords);
			}
			
			$tpkwrd = $doc->createElement('gmd:type');
			$tpkwrd = $parent->appendChild($tpkwrd);
			
			$addnode = addXMLChildValue($doc,$tpkwrd,"gmd:MD_KeywordTypeCode",'theme');
			break;
		}
		case 'gmd:keywordplace':
		{
			$parent->removeChild($node);
			
			#go back two nodes
			$newparent = $parent->parentNode;
			$parent = $newparent->parentNode;
			
			#make another descriptiveKeywords node
			$dsckwrd = $doc->createElement('gmd:descriptiveKeywords');
			$dsckwrd = $parent->appendChild($dsckwrd);
			#make another MD_Keywords node
			$parent = $doc->createElement('gmd:MD_Keywords');
			$parent = $dsckwrd->appendChild($parent);
			
			$arrkeywords = preg_split("/\;/",$fieldvalue);
			
			foreach ($arrkeywords as $myKeywords)
			{
				$mdkwrd = $doc->createElement('gmd:keyword');
				$mdkwrd = $parent->appendChild($mdkwrd);
				$addnode = addXMLChildValue($doc,$mdkwrd,"gco:CharacterString",$myKeywords);
			}
			
			$tpkwrd = $doc->createElement('gmd:type');
			$tpkwrd = $parent->appendChild($tpkwrd);
			
			$addnode = addXMLChildValue($doc,$tpkwrd,"gmd:MD_KeywordTypeCode",'place');
			
			break;
		}
		case 'gmd:topicCategory':
		{
			$parent->removeChild($node);
			
			$arrkeywords = preg_split("/\;/",$fieldvalue);
			
			foreach ($arrkeywords as $myKeywords)
			{
				$mdkwrd = $doc->createElement('gmd:topicCategory');
				$mdkwrd = $parent->appendChild($mdkwrd);
				$addnode = addXMLChildValue($doc,$mdkwrd,"gmd:MD_TopicCategoryCode",$myKeywords);
				//$child = $doc->createElement('gmd:MD_TopicCategoryCode');
				//$child = $parent->appendChild($child);
				//$value = $doc->createTextNode($myKeywords);
				//$value = $child->appendChild($value);
			}
			break;
		}
	}
}

function addXMLChildValue($doc,$parent,$fieldname,$fieldvalue)
{
	$fieldvalue = htmlspecialchars($fieldvalue, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
	$child = $doc->createElement($fieldname);
	$child = $parent->appendChild($child);
	$value = $doc->createTextNode($fieldvalue);
	$value = $child->appendChild($value);
	
	addNodeAttributes($doc,$parent,$child,$fieldname,$fieldvalue);
	
	return $child;
}



function createNodesXML_old($xml)
{
	$doc = new DomDocument('1.0','UTF-8');
	
	$root = createXmlNode($doc,$doc,'gmi:MI_Metadata');
	
	$root->setAttribute('xmlns','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gco','http://www.isotc211.org/2005/gco');
	$root->setAttribute('xmlns:gmd','http://www.isotc211.org/2005/gmd');
	$root->setAttribute('xmlns:gmi','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gml','http://www.opengis.net/gml/3.2');
	$root->setAttribute('xmlns:gmx','http://www.isotc211.org/2005/gmx');
	$root->setAttribute('xmlns:gsr','http://www.isotc211.org/2005/gsr');
	$root->setAttribute('xmlns:gss','http://www.isotc211.org/2005/gss');
	$root->setAttribute('xmlns:gts','http://www.isotc211.org/2005/gts');
	$root->setAttribute('xmlns:xlink','http://www.w3.org/1999/xlink');
	$root->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
	$root->setAttribute('xsi:schemaLocation','http://www.isotc211.org/2005/gmi http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd');
	
	$parent = $root;
	
	$nodeinstance;
	
	foreach ($xml as $key=>$val)
	{
		$nodelevels = preg_split("/-/",$key);
		
		$nodecnt = count($nodelevels);
		
		$icnt=0;
		
		//var_dump($nodelevels);
		
		array_shift($nodelevels); #Due to the new structure, this old function is still being used for now.
		
		
		foreach ($nodelevels as $nodelevel)
		{
			$splitlevel = $nodelevel;
			$splitnodelevel = preg_split("/\!/",$splitlevel);
			
			//var_dump($splitnodelevel);
			
			if (isset($splitnodelevel[1]))
			{
				$nodeinstance =  '_'.$splitnodelevel[1];
			}
			
			$nodelevel = $splitnodelevel[0];
			
			//var_dump($nodeinstance);
						
			$icnt++;
			if ($icnt == $nodecnt)
			{
				#makechild
				$nodename = str_replace(":","_",$nodelevels[$nodecnt-2]);
				if (isset($nodeinstance))
				{
					$nodevar = 'node_'.$nodeinstance.'_'.$nodename;
				}
				else
				{
					$nodevar = 'node_'.$nodename;
				}
				//var_dump($nodevar);
				$thisparent = ${$nodevar};
				addXMLChildValue($doc,$thisparent,$nodelevel,$val);
			}
			else
			{
				#makenode
				$nodename = str_replace(":","_",$nodelevel);
				if (isset($nodeinstance))
				{
					$nodevar = 'node_'.$nodeinstance.'_'.$nodename;
				}
				else
				{
					$nodevar = 'node_'.$nodename;
				}
				//var_dump($nodevar);
				if (!isset(${$nodevar}))
				{
					${$nodevar} = createXmlNode($doc,$parent,$nodelevel);
				}
				$parent = ${$nodevar};
			}
		}
		$parent = $root;
		
	}
	
	header("Content-type: text/xml; charset=utf-8"); 
	//header('Content-Disposition: attachment; filename=metadata.xml');
		
	$doc->normalizeDocument();
	$doc->formatOutput = true;
	ob_clean();
	flush();
	echo $doc->saveXML();
	exit;
}

function guid(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
		}else{
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		return $uuid;
	}
}

function removeFromArray($array,$position)
{
	$temparr = array();
	
	for ($i=0;$i<=count($array)-1;$i++)
	{
		if ($i<>$position)
		{
			$temparr[] = $array[$i];
		}
	}
	
	return $temparr;
}

function insertIntoArray($array,$position,$var)
{
	$temparr = array();
	
	$i = 0;
	
	if ($position>count($array))
	{
		$temparr = $array;
		array_push($temparr,$var);
	}
	else
	{
		
		foreach ($array as $node)
		{
			if ($i==$position)
			{
				$temparr[] = $var;
				
			}
			else
			{
				$temparr[] = $node;
			}
			$i++;
		}
	}
	return $temparr;
}

?>