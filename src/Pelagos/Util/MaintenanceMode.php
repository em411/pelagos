<?php

namespace Pelagos\Util;

/**
 * A utility to create and issue DOI from EZID API.
 */
class MaintenanceMode
{
    /**
     * FOS Elastica Object to find elastica documents.
     *
     * @var TransformedFinder
     */
    protected $fileName;

    /**
     * The kernel root path.
     *
     * @var string
     */
    private $kernelRootDir;

    /**
     * Constructor.
     *
     * @param string $fileName The filename of the maintenance file.
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Is the system in maintenance mode.
     *
     * @return boolean If in maintenance mode.
     */
    public function isMaintenanceMode() : bool
    {
        return file_exists($this->fileName);
    }

    /**
     * Gets the maintenance text.
     *
     * @return string|null Returns maintenance mode banner text.
     */
    public function getMaintenanceModeText() : ? string
    {
        if (!file_exists($this->fileName)) {
            return null;
        }

        $values = parse_ini_file($this->fileName);

        if (!array_key_exists('maintenance_text', $values)) {
            return null;
        }

        return $values['maintenance_text'];
    }

    /**
     * Gets maintenance mode color.
     *
     * @return string|null Returns maintenance mode banner color.
     */
    public function getMaintenanceModeColor() : ? string
    {
        if (!file_exists($this->fileName)) {
            return null;
        }

        $values = parse_ini_file($this->fileName);

        if (!array_key_exists('maintenance_color', $values)) {
            return null;
        }

        return $values['maintenance_color'];
    }

    /**
     * Create the maintenance mode ini file.
     *
     * @param string $text  The banner text.
     * @param string $color The banner background color.
     *
     * @return void
     */
    public function activateMaintenanceMode(string $text, string $color)
    {
        $contents = 'maintenance_text = "' . $text . '"' . PHP_EOL .
            'maintenance_color = "' . $color . '"' . PHP_EOL;
        file_put_contents($this->fileName, $contents);
    }

    /**
     * Create the maintenance mode ini file.
     *
     * @return void
     */
    public function deactivateMaintenanceMode()
    {
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }
    }
}
