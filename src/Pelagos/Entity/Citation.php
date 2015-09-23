<?php

namespace Pelagos\Entity;

/**
 * This class holds a Citation object.
 */
class Citation
{
    /**
     * Citation ID.
     *
     * @var $id string
     */
    private $id;

    /**
     * Citation Text.
     *
     * @var $text string
     */
    private $text;

    /**
     * Citation Style.
     *
     * @var $style string
     */
    private $style;

    /**
     * Ciation Locale.
     *
     * @var $locale string
     */
    private $locale;

    /**
     * Citation Timestamp.
     *
     * @var $timestamp \DateTime
     */
    private $timestamp;

    /**
     * Citation Constructor.
     *
     * Will create a Citation Object from given parameters.
     *
     * @param string    $id        Citation ID, can be DOI or UDI.
     * @param string    $text      Citation Text.
     * @param string    $style     Citation Style commonly APA.
     * @param string    $locale    Citation Text Locale commonly utf-8.
     * @param \DateTime $timestamp Timestamp when citation is "generated" if left blank will be autogenerated.
     */
    public function __construct(
        $id,
        $text = null,
        $style = null,
        $locale = null,
        \DateTime $timestamp = null
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->style = $style;
        $this->locale = $locale;
        $this->setTimeStamp($timestamp);
    }

    /**
     * Will set the Citation Timestamp.
     *
     * @param \DateTime $timestamp The time stamp to set.
     *
     * @return void
     */
    public function setTimeStamp(\DateTime $timestamp)
    {
        if ($timestamp === null) {
            $this->timestamp = new \DateTime();
        } else {
            $this->timestamp = $timestamp;
        }
    }

    /**
     * Returns the Citation Object as an array.
     *
     * @return array
     */
    public function asArray()
    {
        return array(
            'id' => $this->id,
            'text' => $this->text,
            'style' => $this->style,
            'locale' => $this->locale,
            'timestamp' => $this->timestamp->format('c'),
        );
    }

    /**
     * Return the Citation Object as JSON.
     *
     * @return JSON
     */
    public function asJSON()
    {
        return json_encode($this->asArray(), JSON_UNESCAPED_SLASHES);
    }
}
