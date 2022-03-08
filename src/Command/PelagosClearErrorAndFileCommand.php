<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PelagosClearErrorAndFileCommand extends Command
{
    protected static $defaultName = 'pelagos:clear-error-file';
    protected static $defaultDescription = 'Removes an error-status file from fileset and if exists, filesystem.';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * String with .env homedir prefix path.
     *
     * @var String $homedirPrefix;
     */
    protected $homedirPrefix;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $homedirPrefix
    ) {
        $this->entityManager = $entityManager;
        $this->homedirPrefix = $homedirPrefix;

        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    /**
     * Configure function to allow for options and parameters.
     */
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('fileId', InputArgument::REQUIRED, 'ID of file in error to delete')
            ->addArgument('udi', InputArgument::REQUIRED, 'UDI of the dataset containing file to delete.')
        ;
    }

    /**
     * Symfony command execute section.
     *
     * @param InputInterface  $input  Required by Command.
     * @param OutputInterface $output Required by Command.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fileId = $input->getArgument('fileId');
        $udi = $input->getArgument('udi');

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(
            array('udi' => $udi)
        );

        $dql = "SELECT f FROM \App\Entity\File f
            WHERE f.id = :fileId
            AND f.fileset = :filesetId
            AND f.status = :fileStatus";

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('filesetId', $dataset->getDatasetSubmission()->getFileset());
        $query->setParameter('fileId', $fileId);
        // Only identify files in an error state.
        $query->setParameter('fileStatus', File::FILE_ERROR);
        /**  @var File $fileToDelete */
        $fileToDelete = $query->getResult()[0];

        // This section is just added for extra safety. Don't delete
        // unless the file is represented in the datastore. Don't delete
        // the only copy of a file.
        $newUdi = preg_replace('/:/', '.', $udi);
        $uploadDir = $this->homedirPrefix . '/upload/files';
        $dqlSafe = "SELECT f FROM \App\Entity\File f
            WHERE f.id <> :fileId
            AND f.fileset = :filesetId
            AND f.physicalFilePath like '$newUdi%'
            AND f.physicalFilePath not like '$uploadDir/%'";
        $query = $this->entityManager->createQuery($dqlSafe);
        $query->setParameter('filesetId', $dataset->getDatasetSubmission()->getFileset());
        $query->setParameter('fileId', $fileId);
        if (count($query->getResult()) > 0) {
            try {
                $this->entityManager->remove($fileToDelete);
                $this->entityManager->flush();
                unlink($fileToDelete->getPhysicalFilePath());
            } catch (\Exception $e) {
                $io->error("Could not delete file." . $e->getMessage());
            }
        } else {
            $io->warning("Not deleting the only copy of file!");
        }

        return 0;
    }
}
