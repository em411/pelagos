<?php

class metaData
{
	public $htmlString;
	public $jsString;
	public $validateRules;
	public $validateMessages;
	public $jqUIs;
	public $qtipS;
	public $xmlArray;
	public $xmldoc;
	public $onReady;
	
	public $twig;
	
	private $loader;
	
	public function __construct()
	{
		require_once 'Twig/Autoloader.php';
		Twig_Autoloader::register();
		
		$this->loader = new Twig_Loader_Filesystem('./templates');
		$this->twig = new Twig_Environment($this->loader,array('autoescape' => false));
	}
	
	public function loadINI($filename)
	{
		#todo: Should support default values, and overwrite with instance values (use: array_merge)
		$ini_path = "config/$filename";
		return parse_ini_file($ini_path,true);
	}
	
	public function returnXmlString($query)
	{
		if ($this->xmldoc != null)
		{
			//$mynodes = $this->xmldoc->getElementsByTagNameNS($NameSpace, $NodeName);
            
            $xpath = new DOMXPath($this->xmldoc);
            
            //$query = '/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_BoundingPolygon[1]/gmd:polygon[1]/*';
            
            $mynodes = $xpath->query($query);
            
			if( $mynodes->length > 0 )
			{
				$mynode = $mynodes->item(0);
                $this->xmldoc->normalizeDocument();
                $gml = $this->xmldoc->saveXML($mynode);
                
                return $gml;
                
			}
		}
	}
	
	public function returnPath($path)
	{
		if (is_null($this->xmldoc))
		{
			return false;
		}
		
		//$xpath = "/gmi:MI_Metadata";
		$xpath = "/";
		
		$xpathdoc = new DOMXpath($this->xmldoc);
		
		$nodelevels = preg_split("/-/",$path);
		
		foreach ($nodelevels as $nodelevel)
		{
			$splitnodelevel = preg_split("/\!/",$nodelevel);
			
			$xpath .= "/" . $splitnodelevel[0];
		}
		
		//echo "$xpath<br>";
		
		$elements = $xpathdoc->query($xpath);
		
		$xmlArray = array();
		
		if (!is_null($elements)) {
			foreach ($elements as $element) {
				//echo "<br/>[". $element->nodeName. "]";
				
				$nodes = $element->childNodes;
				foreach ($nodes as $node) 
				{
					switch ($node->nodeType) 
					{
						
						case XML_TEXT_NODE:
						//$xmlArray[] = trim($node->textContent);
						break;
						
						case XML_ELEMENT_NODE:
						
						array_push($xmlArray, domnode_to_array($node));							
						//echo $node->nodeName. ":";
						//echo $node->nodeValue. "<br/>";
						break;
					}	
				}
			}
		}
		
		//$xmlArray = domnode_to_array($element->childNodes);
		
		if (count($xmlArray) > 0)
		{
			return $xmlArray;
		}
		else
		{
			return false;
		}
	}
}

?>