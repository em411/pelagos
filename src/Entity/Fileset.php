<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

/**
 * Fileset Entity class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FilesetRepository")
 */
class Fileset extends Entity
{
    /**
     * Collection of files.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="File", mappedBy="fileset", cascade={"persist"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * Zipped files path on disk.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $zipFilePath;

    /**
     * Fileset constructor.
     */
    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    /**
     * Getter for all files.
     *
     * @return Collection
     */
    public function getAllFiles() : Collection
    {
        return $this->files;
    }

    /**
     * Getter for processed files.
     *
     * @return Collection
     */
    public function getProcessedFiles() : Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_DONE;
        });
    }

    /**
     * Getter for unprocessed/new files.
     *
     * @return Collection
     */
    public function getNewFiles() : Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_NEW;
        });
    }

    /**
     * Getter for deleted files.
     *
     * @return Collection
     */
    public function getDeletedFiles() : Collection
    {
        return $this->files->filter(function (File $file) {
            return $file->getStatus() === File::FILE_DELETED;
        });
    }

    /**
     * Adder for file entity.
     *
     * @param File $file Single file to be added.
     *
     * @return void
     */
    public function addFile(File $file)
    {
        $file->setFileset($this);
        $this->files->add($file);
    }

    /**
     * Remover for File entity.
     *
     * @param File $file Single file to be removed.
     *
     * @return void
     */
    public function removeFile(File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Check if the fileset is done processesing.
     *
     * @return boolean
     */
    public function isDone() :bool
    {
        $criteria = Criteria::create()
        ->where(
            new Comparison(
                'status',
                Comparison::IN,
                array(
                    File::FILE_NEW,
                    File::FILE_IN_PROGRESS
                )
            )
        );

        return count($this->files->matching($criteria)) === 0;
    }

    /**
     * Checks if file exists.
     *
     * @param string $newFileName Filename of the new file to check.
     *
     * @return bool
     */
    public function doesFileExist(string $newFileName) : bool
    {
        return $this->files->exists(function ($key, File $file) use ($newFileName) {
            return $file->getFileName() === $newFileName;
        });
    }

    /**
     * Returns the total filesize for this fileset.
     *
     * @return integer
     */
    public function getFileSize() :int
    {
        $fileSize = 0;

        foreach ($this->files as $file) {
            $fileSize += (int) $file->getFileSize();
        }

        return $fileSize;
    }

    /**
     * Gets the existing File entity.
     *
     * @param string $fileName Filename key to get the File entity.
     *
     * @return File
     */
    public function getExistingFile(string $fileName) : File
    {
        $criteria = Criteria::create()
            ->where(
                new Comparison(
                    'fileName',
                    Comparison::EQ,
                    $fileName
                )
            );

        return $this->files->matching($criteria)->first();
    }

    /**
     * Getter for zip file path.
     *
     * @return string
     */
    public function getZipFilePath(): string
    {
        return $this->zipFilePath;
    }

    /**
     * Setter for zip file path.
     *
     * @param string $zipFilePath Filepath on disk.
     *
     * @return void
     */
    public function setZipFilePath(string $zipFilePath): void
    {
        $this->zipFilePath = $zipFilePath;
    }
}
