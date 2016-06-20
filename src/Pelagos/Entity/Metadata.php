<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\OneToOne(targetEntity="Dataset", mappedBy="metadata", cascade={"persist"})
     */
    protected $dataset;

    /**
     * XML of the Metadata.
     *
     * @var \SimpleXMLElement
     *
     * @ORM\Column(type="xml")
     */
    protected $xml;

    /**
     * Geometry of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="geometry", options={"geometry_type"="Geometry", "srid"=4326)
     */
    protected $geometry;

    /**
     * Description of the extent in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $extentDescription;

    /**
     * Title of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * Abstract of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $abstract;

    /**
     * Start date of the dataset in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $beginPositions;

    /**
     * End date of the dataset in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $endPositions;

    /**
     * The file format of the dataset in the metadata.
     *
     * @var PROPTYPE
     *
     * @ORM\Column(type="string")
     */
    protected $fileFormat;

    /**
     * The purpose of the dataset in the metadata.
     *
     * @var PROPTYPE
     *
     * @ORM\Column(type="string")
     */
    protected $purpose;

    /**
     * An array of theme keywords for the Metadata.
     *
     * @var array
     *
     * @ORM\Column(type="text_array")
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
     * @param string $beginPositions The begin_position extracted from XML.
     *
     * @return void
     */
    private function setBeginPosition($beginPositions)
    {
        $this->beginPosition = $beginPositions;
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
     * Sets the Metadata title from the Metadata XML.
     *
     * @return void
     */
    private function setPropertiesFromXml()
    {
        if (null == $this->xml) {
            return;
        }

        $titles = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/gmd:title/gco:CharacterString/text()'
        );

        if (count($titles) == 1) {
            $this->setTitle($titles[0]);
        }

        $abstracts = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:abstract/gco:CharacterString/text()'
        );

        if (count($abstracts) == 1) {
            $this->setAbstract($abstracts[0]);
        }

        $beginPositions = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:beginPosition/text()'
        );

        if (count($beginPositions) == 1) {
            $this->setBeginPosition($beginPosition[0]);
        }

        $endPositions = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:temporalElement/gmd:EX_TemporalExtent/gmd:extent/gml:TimePeriod/gml:endPosition/text()'
        );

        if (count($endPositions) == 1) {
            $this->setEndPosition($endPositions[0]);
        }

        $extentDescriptions = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:description/gco:CharacterString/text()'
        );

        if (count($extentDescriptions) == 1) {
            $this->setExtentDescription($extentDescriptions[0]);
        }

        $fileFormats = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:distributionInfo/gmd:MD_Distribution/gmd:distributor/gmd:MD_Distributor/gmd:distributorFormat/gmd:MD_Format/gmd:name/gco:CharacterString/text()'
        );

        if (count($fileFormats) == 1) {
            $this->setFileFormat($fileFormats[0]);
        }

        $purpose = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:purpose/gco:CharacterString/text()'
        );

        if (count($purpose) == 1) {
            $this->setPurpose($purpose[0]);
        }

        $themeKeywords = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:descriptiveKeywords/gmd:MD_Keywords/gmd:type[descendant::text()="theme"]/parent::gmd:MD_Keywords/gmd:keyword/gco:CharacterString/text()'
        );

        if (count($themeKeywords) > 0) {
            $this->setThemeKeywords($themeKeywords);
        }

    }
}
