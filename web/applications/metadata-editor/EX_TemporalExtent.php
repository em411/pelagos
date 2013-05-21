<?php
include 'TimePeriod.php';
include 'TimeInstant.php';


class EX_TemporalExtent
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $xmlArray, $alttype=false)
	{
		
		//$instanceType .= "-gmd:EX_TemporalExtent!$instanceName";
		$instanceType .= "-gmd:EX_TemporalExtent!$instanceName".'time';
		
		if ($alttype==true)
		{
			$mytimep = new TimeInstant($mMD, $instanceType.'-gmd:extent-gml:TimeInstant', $instanceName.'timeperiod', $xmlArray["gmd:EX_TemporalExtent"]["gmd:extent"]);
			
		}
		else
		{
			$mytimep = new TimePeriod($mMD, $instanceType.'-gmd:extent-gml:TimePeriod', $instanceName.'extent', $xmlArray["gmd:EX_TemporalExtent"]["gmd:extent"]);
		}
		
		$Time = $mytimep->getHTML();
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'Time' => $Time);
		
		$this->htmlString = $mMD->twig->render('html/EX_TemporalExtent.html', $twigArr);
		
		return true;
		
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}



?>