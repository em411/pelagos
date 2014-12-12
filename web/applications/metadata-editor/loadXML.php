<?php

/**
 * convert xml string to php array - useful to get a serializable value
	*
 * @param string $xmlstr
 * @return array
	*
 * @author Adrien aka Gaarf & contributors
 * @see http://gaarf.info/2009/08/13/xml-string-to-php-array/
 */

function xmlstr_to_array($xmlstr) {
	$doc = new DOMDocument();
	$doc->loadXML($xmlstr);
	$output=null;
	if (isset($doc))
	{
		$root = $doc->documentElement;
		$output = domnode_to_array($root);
		$output['@root'] = $root->tagName;
	}
	return $output;
}

function domnode_to_array($node) {
	
	$output = null;
	
	switch ($node->nodeType) {
		
		case XML_CDATA_SECTION_NODE:
		case XML_TEXT_NODE:
		$output = trim($node->textContent);
		break;
		
		case XML_ELEMENT_NODE:
		for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
			$child = $node->childNodes->item($i);
			$v = domnode_to_array($child);
			if(isset($child->tagName)) {
				$t = $child->tagName;
				if(!isset($output[$t])) {
					$output[$t] = array();
				}
				$output[$t][] = $v;
			}
			elseif($v || $v === '0') {
				$output = (string) $v;
			}
		}
		if($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
			$output = array('@content'=>$output); //Change output into an array.
		}
		if(is_array($output)) {
			if($node->attributes->length) {
				$a = array();
				foreach($node->attributes as $attrName => $attrNode) {
					$a[$attrName] = (string) $attrNode->value;
				}
				$output['@attributes'] = $a;
			}
			foreach ($output as $t => $v) {
				if(is_array($v) && count($v)==1 && $t!='@attributes') {
					$output[$t] = $v[0];
				}
			}
		}
		break;
	}
	return $output;
}

function loadXML($url)
{
	$doc = new DomDocument('1.0','UTF-8');

	if(!@$doc->load($url))
	{
		$doc = null;
	}

	return $doc;
}

function getNodeValue($nodeName,$doc)
{
	$results = array();
	
	$nodes = $doc->getElementsByTagName ($nodeName);
	foreach ($nodes as $node) {
		$nodeValue =  $node->nodeValue;
		$nodePath =  $node->getNodePath();
		array_push($results,array($nodePath => $nodeValue));
	};
	return $results;
}

//$test =  getNodeValue('fileIdentifier',$doc);

?>