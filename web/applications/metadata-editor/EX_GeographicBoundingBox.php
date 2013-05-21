<?php

class EX_GeographicBoundingBox
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $xmlArray)
	{
		$instanceType .= '-gmd:EX_GeographicBoundingBox';
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'xmlArray' => $xmlArray["gmd:EX_GeographicBoundingBox"]);
		
		$this->htmlString = $mMD->twig->render('html/EX_GeographicBoundingBox.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}








?>