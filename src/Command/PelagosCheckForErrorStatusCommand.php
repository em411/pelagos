<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Length;

class PelagosCheckForErrorStatusCommand extends Command
{
    protected static $defaultName = 'pelagos:error-checker';
    protected static $defaultDescription = 'Checks for error status in current files of a dataset';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('udi', InputArgument::OPTIONAL, 'Run only against passed UDI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');

        if ($udi) {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(
                array('udi' => $udi)
            );
        } else {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();
        }

        $io->note('checking: ' . count($datasets) . ' dataset(s).');
        foreach ($datasets as $dataset) {
            /** @var Dataset $dataset */
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $fileset = $datasetSubmission->getFileset();
                if ($fileset instanceof Fileset) {
                    $files = $fileset->getAllFiles()->filter(function (File $file) {
                        return $file->getStatus() === File::FILE_ERROR;
                    });
                    $fileCountInError = count($files);
                    if ($fileCountInError > 0) {
                        $io->warning('Dataset ' . $dataset->getUdi() . ' has the following ' . $fileCountInError . ' files in error.');
                    }
                    /** @var File $file */
                    foreach ($files as $file) {
                        $io->writeln('filename: ' . $file->getFilePathName());
                        $io->writeln('on-disk: ' . $file->getPhysicalFilePath());
                    }
                }
            }
        }
        return 0;
    }
}
