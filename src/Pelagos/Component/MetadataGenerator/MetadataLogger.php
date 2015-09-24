<?php

namespace Pelagos\Component\MetadataGenerator;

/**
 * A class to write html log messages for the MetadataGenerator application.
 *
 * It extends the functionality of Logger to add date time to the filename as well as the html file extension.
 *
 * @see Logger
 */
class MetadataLogger extends Logger
{

    const EXT = '.html';
    const BR = '<br>';

    /**
     * Constructor for metadata logger.
     *
     * @param string $fileName The name of the log file.
     * @param string $udi      The UDI to append to the log file name.
     */
    public function __construct($fileName = null, $udi = null)
    {
        $lfileName = parent::getDefaultFileName();
        if ($fileName != null) {
            $lfileName = $fileName;
        }

        if ($udi != null) {
            $lfileName .= '_' . $udi . '_';
        }
        $dateTime = date('Y-m-d_h:i:s');
        $lfileName .= '_' . $dateTime . self::EXT;
        parent::__construct($lfileName);
    }
}
