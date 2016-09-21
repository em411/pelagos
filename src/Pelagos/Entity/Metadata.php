<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as Serializer;

/**
 * This class represents Pelagos Dataset metadata.
 *
 * @ORM\Entity
 */
class Metadata extends Entity
{
    /**
     * The Dataset this DIF identifies.
     *
     * @var Dataset
     *
     * @ORM\OneToOne(targetEntity="Dataset", inversedBy="metadata")
     */
    protected $dataset;

    /**
     * XML of the Metadata.
     *
     * @var \SimpleXMLElement
     *
     * @Serializer\Exclude
     *
     * @ORM\Column(type="xml")
     */
    protected $xml;

    /**
     * Geometry of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="geometry", options={"srid"=4326}, nullable=true)
     */
    protected $geometry;

    /**
     * Description of the extent in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $extentDescription;

    /**
     * Title of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $title;

    /**
     * Abstract of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $abstract;

    /**
     * Start date of the dataset in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $beginPosition;

    /**
     * End date of the dataset in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $endPosition;

    /**
     * The file format of the dataset in the metadata.
     *
     * @var PROPTYPE
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $fileFormat;

    /**
     * The purpose of the dataset in the metadata.
     *
     * @var PROPTYPE
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $purpose;

    /**
     * An array of theme keywords for the Metadata.
     *
     * @var array
     *
     * @ORM\Column(type="text_array", nullable=true)
     */
    protected $themeKeywords;

    /**
     * Contructor.
     *
     * @param Dataset $dataset Dataset this metadata is for.
     * @param string  $xml     XML for this metadata.
     */
    public function __construct(Dataset $dataset, $xml)
    {
        $this->setDataset($dataset);
        $this->setXml($xml);
    }

    /**
     * Get the Dataset for this Metadata.
     *
     * @return Dataset
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * Set the Dataset for this Metadata.
     *
     * @param Dataset $dataset The dataset for this Metadata.
     *
     * @return void
     */
    public function setDataset(Dataset $dataset)
    {
        $this->dataset = $dataset;
        if ($dataset->getMetadata() !== $this) {
            $this->dataset->setMetadata($this);
        }
    }

    /**
     * Get the XML metadata for this Metadata.
     *
     * @return \SimpleXMLElement
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Set the XML metadata for this Metadata.
     *
     * @param \SimpleXMLElement|string $xml XML representation of metadata.
     *
     * @return void
     */
    public function setXml($xml)
    {
        if (!$xml instanceof \SimpleXMLElement) {
            $this->xml = new \SimpleXMLElement($xml);
        } else {
            $this->xml = $xml;
        }
        $this->setPropertiesFromXml();
    }

    /**
     * Get the geometry for this Metadata.
     *
     * @return string
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    /**
     * Sets the geometry extracted from the XML.
     *
     * @param string $geometry String representing a PostGreSQL geometry.
     *
     * @return void
     */
    private function setGeometry($geometry)
    {
        $this->geometry = $geometry;
    }

    /**
     * Get the extent description for this Metadata.
     *
     * @return string
     */
    public function getExtentDescription()
    {
        return $this->extentDescription;
    }

    /**
     * Set the extent description for this Metadata.
     *
     * @param string $extentDescription String description of extent type.
     *
     * @return void
     */
    private function setExtentDescription($extentDescription)
    {
        $this->extentDescription = $extentDescription;
    }

    /**
     * Gets the title.
     *
     * @return string Title from metadata.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title Title from metadata.
     *
     * @return void
     */
    private function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get the abstract.
     *
     * @return string Abstract from the metadata.
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Sets the abstract.
     *
     * @param string $abstract The abstract from the metadata.
     *
     * @return void
     */
    private function setAbstract($abstract)
    {
        $this->abstract = $abstract;
        if ($this->getDataset() instanceof Dataset) {
            $this->getDataset()->updateAbstract();
        }
    }

    /**
     * Gets the begin_position (date).
     *
     * @return string
     */
    public function getBeginPosition()
    {
        return $this->beginPosition;
    }

    /**
     * Sets the begin_position (date).
     *
     * @param string $beginPosition The begin_position extracted from XML.
     *
     * @return void
     */
    private function setBeginPosition($beginPosition)
    {
        $this->beginPosition = $beginPosition;
    }

    /**
     * Gets the end_position (date).
     *
     * @return string
     */
    public function getEndPosition()
    {
        return $this->endPosition;
    }

    /**
     * Sets the end_position (date).
     *
     * @param string $endPositions The end_position extracted from XML.
     *
     * @return void
     */
    public function setEndPosition($endPositions)
    {
        $this->endPosition = $endPositions;
    }

    /**
     * Gets the file format extracted from XML.
     *
     * @return string.
     */
    public function getFileFormat()
    {
        return $this->fileFormat;
    }

    /**
     * Sets the file format.
     *
     * @param string $fileFormat The file format as specified in the XML.
     *
     * @return void
     */
    public function setFileFormat($fileFormat)
    {
        $this->fileFormat = $fileFormat;
    }

    /**
     * Gets the purpose.
     *
     * @return string
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Sets the purpose as specified in the XML.
     *
     * @param string $purpose The Purpose specified in the metadata XML.
     *
     * @return void
     */
    private function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * Gets array of keywords as specified in the XML.
     *
     * @return array An array of keywords as specified in the XML file.
     */
    public function getThemeKeywords()
    {
        return $this->themeKeywords;
    }

    /**
     * Extracts BoundingPolygonGML from SimpleXML object.
     *
     * @param \SimpleXMLElement $simpleXml Containing zero or more bounding polygons.
     *
     * @return array;
     */
    public static function extractBoundingPolygonGML(\SimpleXMLElement $simpleXml)
    {
        $polygonArray = array();

        $polygons = $simpleXml->xpath(
            '/gmi:MI_Metadata' .
            '/gmd:identificationInfo[*]' .
            '/gmd:MD_DataIdentification[*]' .
            '/gmd:extent[*]' .
            '/gmd:EX_Extent[*]' .
            '/gmd:geographicElement[*]' .
            '/gmd:EX_BoundingPolygon[*]' .
            '/gmd:polygon[*]'
        );

        foreach ($polygons as $polygon) {
            foreach ($polygon->children('gml', true) as $child) {
                $polygonArray[] = $child->asXml();
            }
        }

        return $polygonArray;
    }

    /**
     * Sets the keywords as specfied in the XML.
     *
     * @param array $themeKeywords An array of keywords extracted from the XML.
     *
     * @return void
     */
    private function setThemeKeywords(array $themeKeywords)
    {
        $this->themeKeywords = $themeKeywords;
    }

    /**
     * Updated the the timestamp of the XML.
     *
     * @param \DateTime $timeStamp An time stamp, by default "now" in time zone UTC.
     *
     * @throws \Exception When gmd:dateStamp does not exist.
     *
     * @return void
     */
    public function updateXmlTimeStamp(\DateTime $timeStamp = null)
    {
        if (null === $timeStamp) {
            $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        $timeStamps = $this->xml->xpath(
            '/gmi:MI_Metadata' .
            '/gmd:dateStamp'
        );

        if (count($timeStamps) == 1) {
            // Check and see if there is gco:DateTime.
            $childFound = false;
            foreach ($timeStamps[0]->children('gco', true) as $child) {
                if ($child->getName() == 'DateTime') {
                    $childFound = true;
                }
            }
            if (false === $childFound) {
                // gco:DateTime now found, so one is created.
                $timeStamps[0]->addChild(
                    'DateTime',
                    $timeStamp->format('c'),
                    'http://www.isotc211.org/2005/gco'
                );
            } else {
                // gco:DateTime was found, so it's updated.
                $timeStamps[0]->{'DateTime'} = $timeStamp->format('c');
            }
        } else {
            throw new \Exception('gmd:dateStamp does not Exist');
        }
    }

    /**
     * Add a new maintenance node to the xml.
     *
     * @param string $note A text string describing the maintenance note.
     *
     * @return void
     */
    public function addMaintenanceNote($note)
    {
        $maintenanceInformation = $this->xml->xpath(
            '/gmi:MI_Metadata' .
            '/gmd:metadataMaintenance' .
            '/gmd:MD_MaintenanceInformation'
        );

        if (count($maintenanceInformation) >= 1) {
            $maintenanceInformation = $maintenanceInformation[0];
        } else {
            // Not found, so we'll add one.
            $metadataMaintenance = $this->xml->addChild(
                'metadataMaintenance',
                null,
                'http://www.isotc211.org/2005/gmd'
            );
            $maintenanceInformation = $metadataMaintenance->addChild(
                'MD_MaintenanceInformation',
                null,
                'http://www.isotc211.org/2005/gmd'
            );
            $maintenanceAndUpdateFrequency = $maintenanceInformation->addChild(
                'gmd:maintenanceAndUpdateFrequency',
                null,
                'http://www.isotc211.org/2005/gmd'
            );
            $maintenanceAndUpdateFrequency->addAttribute(
                'gco:nilReason',
                'unknown',
                'http://www.isotc211.org/2005/gco'
            );
        }

        $maintenanceNote = $maintenanceInformation->addChild(
            'maintenanceNote',
            null,
            'http://www.isotc211.org/2005/gmd'
        );

        $maintenanceNote->addChild(
            'CharacterString',
            $note,
            'http://www.isotc211.org/2005/gco'
        );
    }

    /**
     * Add bounding box to Metadata XML.
     *
     * @param array $boundingBox An array with west, east, south, north bound lat,long keys.
     *
     * @return void
     */
    public function addBoundingBoxToXml(array $boundingBox)
    {
        $westBoundLongitude = $boundingBox['westBoundLongitude'];
        $eastBoundLongitude = $boundingBox['eastBoundLongitude'];
        $southBoundLatitude = $boundingBox['southBoundLatitude'];
        $northBoundLatitude = $boundingBox['northBoundLatitude'];

        $envelope
            = <<<EOF
<gmd:geographicElement>
                       <gmd:EX_GeographicBoundingBox>
                           <gmd:westBoundLongitude>
                               <gco:Decimal>$westBoundLongitude</gco:Decimal>
                           </gmd:westBoundLongitude>
                           <gmd:eastBoundLongitude>
                               <gco:Decimal>$eastBoundLongitude</gco:Decimal>
                           </gmd:eastBoundLongitude>
                           <gmd:southBoundLatitude>
                               <gco:Decimal>$southBoundLatitude</gco:Decimal>
                           </gmd:southBoundLatitude>
                           <gmd:northBoundLatitude>
                               <gco:Decimal>$northBoundLatitude</gco:Decimal>
                           </gmd:northBoundLatitude>
                       </gmd:EX_GeographicBoundingBox>
                    </gmd:geographicElement>
EOF;

        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML($this->xml->asXML());
        $xpath = new \DOMXpath($doc);

        // locate any existing EX_GeographicBoundingBox and delete its parent.
        $bBoxes = $xpath->query(
            '/gmi:MI_Metadata' .
            '/gmd:identificationInfo' .
            '/gmd:MD_DataIdentification' .
            '/gmd:extent' .
            '/gmd:EX_Extent' .
            '/gmd:geographicElement' .
            '/gmd:EX_GeographicBoundingBox'
        );

        foreach ($bBoxes as $box) {
            $parent = $box->parentNode;
            $grandParent = $parent->parentNode;
            $grandParent->removeChild($parent);
        }

        // create new gmd:geographicElement for the bounding-box envelope
        $fragment = $doc->createDocumentFragment();
        $fragment->appendXML($envelope);

        $parent = $xpath->query(
            '/gmi:MI_Metadata' .
            '/gmd:identificationInfo[1]' .
            '/gmd:MD_DataIdentification[1]' .
            '/gmd:extent[1]' .
            '/gmd:EX_Extent[1]'
        );

        // locate reference node
        $referenceNode = $xpath->query(
            '/gmi:MI_Metadata' .
            '/gmd:identificationInfo[1]' .
            '/gmd:MD_DataIdentification[1]' .
            '/gmd:extent[1]' .
            '/gmd:EX_Extent[1]' .
            '/gmd:geographicElement[1]'
        );

        // insert into XML before first existing geographicElement
        $parent->item(0)->insertBefore($fragment, $referenceNode->item(0));
        $doc->normalizeDocument();
        $doc->formatOutput = true;
        $this->setXml($doc->saveXML());
    }

    /**
     * Checks if the URL of the Metadata URL matches the UDI.
     *
     * @throws \Exception When the URL does not exist.
     *
     * @return boolean
     */
    public function doesUdiMatchMetadataUrl()
    {
        $metadataUrl = $this->xml->xpath(
            '/gmi:MI_Metadata' .
            '/gmd:dataSetURI' .
            '/gco:CharacterString' .
            '/text()'
        );

        if (count($metadataUrl) > 0) {
            $udi = $this->dataset->getUdi();
            return (bool) preg_match("/\/$udi$/", $metadataUrl[0]);
        } else {
            throw new \Exception('Metadata URL does not exist');
        }
    }

    /**
     * Checks if the URL of the Distributor URL matches the UDI.
     *
     * @throws \Exception When the URL does not exist.
     *
     * @return boolean
     */
    public function doesUdiMatchDistributionUrl()
    {
        $distributionUrl = $this->xml->xpath(
            '/gmi:MI_Metadata' .
            '/gmd:distributionInfo' .
            '/gmd:MD_Distribution' .
            '/gmd:distributor' .
            '/gmd:MD_Distributor' .
            '/gmd:distributorTransferOptions' .
            '/gmd:MD_DigitalTransferOptions' .
            '/gmd:onLine' .
            '/gmd:CI_OnlineResource' .
            '/gmd:linkage' .
            '/gmd:URL' .
            '/text()'
        );

        if (count($distributionUrl) > 0) {
            $udi = $this->dataset->getUdi();
            return (bool) preg_match("/\/$udi$/", $distributionUrl[0]);
        } else {
            throw new \Exception('Distribution URL does not exist');
        }
    }

    /**
     * Sets the Metadata title from the Metadata XML.
     *
     * @return void
     */
    private function setPropertiesFromXml()
    {
        if (null == $this->xml) {
            return;
        }

        $basePath = '/gmi:MI_Metadata';

        $fileFormats = $this->xml->xpath(
            $basePath .
            '/gmd:distributionInfo' .
            '/gmd:MD_Distribution' .
            '/gmd:distributor' .
            '/gmd:MD_Distributor' .
            '/gmd:distributorFormat' .
            '/gmd:MD_Format' .
            '/gmd:name' .
            '/gco:CharacterString' .
            '/text()'
        );

        if (count($fileFormats) > 0) {
            $this->setFileFormat($fileFormats[0]);
        } else {
            $this->setFileFormat(null);
        }

        $basePath .= '/gmd:identificationInfo' .
                     '/gmd:MD_DataIdentification';

        $titles = $this->xml->xpath(
            $basePath .
            '/gmd:citation' .
            '/gmd:CI_Citation' .
            '/gmd:title' .
            '/gco:CharacterString' .
            '/text()'
        );

        if (count($titles) > 0) {
            $this->setTitle($titles[0]);
        } else {
            $this->setTitle(null);
        }

        $abstracts = $this->xml->xpath(
            $basePath .
            '/gmd:abstract' .
            '/gco:CharacterString' .
            '/text()'
        );

        if (count($abstracts) > 0) {
            $this->setAbstract($abstracts[0]);
        } else {
            $this->setAbstract(null);
        }

        $purpose = $this->xml->xpath(
            $basePath .
            '/gmd:purpose' .
            '/gco:CharacterString' .
            '/text()'
        );

        if (count($purpose) > 0) {
            $this->setPurpose($purpose[0]);
        } else {
            $this->setPurpose(null);
        }

        $themeKeywords = $this->xml->xpath(
            $basePath .
            '/gmd:descriptiveKeywords' .
            '/gmd:MD_Keywords' .
            '/gmd:type[descendant::text()="theme"]' .
            '/parent::gmd:MD_Keywords' .
            '/gmd:keyword' .
            '/gco:CharacterString' .
            '/text()'
        );

        if (count($themeKeywords) > 0) {
            $this->setThemeKeywords($themeKeywords);
        } else {
            $this->setThemeKeywords(null);
        }

        $basePath .= '/gmd:extent' .
                     '/gmd:EX_Extent';

        $beginPositions = $this->xml->xpath(
            $basePath .
            '/gmd:temporalElement' .
            '/gmd:EX_TemporalExtent' .
            '/gmd:extent' .
            '/gml:TimePeriod' .
            '/gml:beginPosition' .
            '/text()'
        );

        if (count($beginPositions) > 0) {
            $this->setBeginPosition($beginPositions[0]);
        } else {
            $this->setBeginPosition(null);
        }

        $endPositions = $this->xml->xpath(
            $basePath .
            '/gmd:temporalElement' .
            '/gmd:EX_TemporalExtent' .
            '/gmd:extent' .
            '/gml:TimePeriod' .
            '/gml:endPosition' .
            '/text()'
        );

        if (count($endPositions) > 0) {
            $this->setEndPosition($endPositions[0]);
        } else {
            $this->setEndPosition(null);
        }

        $extentDescriptions = $this->xml->xpath(
            $basePath .
            '/gmd:description' .
            '/gco:CharacterString' .
            '/text()'
        );

        if (count($extentDescriptions) > 0) {
            $this->setExtentDescription($extentDescriptions[0]);
        } else {
            $this->setExtentDescription(null);
        }

        $gmls = $this->xml->xpath(
            $basePath .
            '/gmd:geographicElement' .
            '/gmd:EX_BoundingPolygon' .
            '/gmd:polygon' .
            '/child::*'
        );

        if (count($gmls) > 0) {
            $gml = $gmls[0]->asXML();
            $this->setGeometry($gml);
        } else {
            $this->setGeometry(null);
        }
    }
}
