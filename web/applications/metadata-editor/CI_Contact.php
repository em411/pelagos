<?php
include_once 'CI_Telephone.php';
include_once 'CI_Address.php';

class CI_Contact
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $xmlArray, $onlineresource, $operationHours=false)
	{
		$instanceType .= '-gmd:CI_Contact';
		
		$phoneArray = $xmlArray["gmd:phone"];
		$addressArray = $xmlArray["gmd:address"];
		
		${'mytelephone'.$instanceName} = new CI_Telephone($mMD, $instanceType.'-gmd:phone',$instanceName, $phoneArray);
		${'myaddress'.$instanceName} = new CI_Address($mMD, $instanceType.'-gmd:address', $instanceName, $addressArray, $onlineresource);
		
		$Telephone = ${'mytelephone'.$instanceName}->getHTML();
		$Address = ${'myaddress'.$instanceName}->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'Telephone' => $Telephone,'Address' => $Address,'operationHours' => $operationHours);
		
		$this->htmlString .= $mMD->twig->render('html/CI_Contact.html', $twigArr);

		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>
	