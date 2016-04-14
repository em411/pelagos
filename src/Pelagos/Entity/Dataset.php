<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Pelagos\Entity\DIF;

/**
 * Dataset Entity class.
 *
 * @ORM\Entity
 */
class Dataset extends Entity
{
    
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset';
    
    /**
     * The UDI for this Dataset.
     *
     * @var string
     *
     * @ORM\Column
     */
    protected $udi;
    
    /**
     * The DIF for this Dataset.
     *
     * @var DIF
     *
     * @ORM\OneToOne(targetEntity="DIF")
     */
    protected $dif;
        
    /**
     * Sets the UDI for this Dataset.
     *
     * @param string $udi The UDI for this Dataset.
     *
     * @return void
     */
    public function setUdi($udi)
    {
        $this->udi = $udi;
    }
    
    /**
     * Gets the UDI for this Dataset.
     *
     * @return string The UDI for this Dataset.
     */
    public function getUdi()
    {
        return $this->udi;
    }
    
    /**
     * Sets the DIF for this Dataset.
     *
     * @param DIF $dif The DIF for this Dataset.
     *
     * @return void
     */
    public function setDif(DIF $dif)
    {
        $this->dif = $dif;
    }
    
    /**
     * Gets the DIF for this Dataset.
     *
     * @return DIF The DIF for this Dataset.
     */
    public function getDif()
    {
        return $this->dif;
    }
}
