<?php

include_once 'MD_Format.php';
include_once 'MD_DigitalTransferOptions.php';

class MD_Distributor
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName,$Legend='Data Identification')
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= "-gmd:MD_Distributor!$instanceName";
		
		$mydistrp = new CI_ResponsibleParty($mMD,'gmd:distributorContact','contactDist',false,'CI_RoleCode_distributor','Distribution Contact');
		$ResponsibleParty = $mydistrp->getHTML();
		
		$transferOpt = new MD_DigitalTransferOptions($mMD, $instanceType.'-gmd:distributorTransferOptions', $instanceName, $xmlArray['gmd-distributorTransferOptions']);
		$DigitalTransferOptions = $transferOpt->getHTML();
		
		$myFormat = new MD_Format($mMD, $instanceType.'-gmd:distributorFormat', $instanceName);
		$Format = $myFormat->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'ResponsibleParty' => $ResponsibleParty,'DigitalTransferOptions' => $DigitalTransferOptions, 'Format' => $Format);
		
		$this->htmlString .= $mMD->twig->render('html/MD_Distributor.html', $twigArr);
		
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}


?>