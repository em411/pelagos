<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Exception;
use Pelagos\Entity\Dataset;
use Pelagos\Util\DOIutil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This Symfony Command generates a report for DOI migration.
 *
 * It produces a CSV file.
 */
class ReportDoiStatusDatasetStatus extends ContainerAwareCommand
{
    /**
     * A name of the file that will store the results of this report program.
     *
     * It is initialized to a default value.
     *
     * @var string
     */
    protected $outputFileName = '';

    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface fileOutput
     */
    protected $fileOutput = null;

    /**
     * The Doctrine entity manager - ORM critter.
     *
     * @var EntityManager entityManager
     */
    protected $entityManager;

    /**
     * The file output array which stores the data.
     *
     * @var array
     */
    protected $fileOutputArray;

    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-doi:report-status')
            ->setDescription('Report of udi(s) with doi status, dataset status')
            ->addArgument('outputFileName', InputArgument::REQUIRED, 'What is the output file path and name?');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws Exception Exception thrown when openIO function fails to generate report.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputFileName = $input->getArgument('outputFileName');
        try {
            $datasets = self::openIO($output);
            $this->createReportForDoiMigration($datasets);
        } catch (Exception $e) {
            throw new Exception('Unable to generate report' . $e->getMessage());
        }
        return 0;
    }

    /**
     * Prepare the output file for writting.
     *
     * @param OutputInterface $output An class that handles output files.
     *
     * @return Dataset
     */
    protected function openIO(OutputInterface $output)
    {
        if ($this->fileOutput === null) {
            $handle = fopen($this->outputFileName, 'w');
            $this->fileOutput = new StreamOutput($handle);
            $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        }
        $output->writeln('The output file is ' . $this->outputFileName);

        $datasets = array();
        $datasets = $this->entityManager->getRepository(Dataset::class)
            ->findBy($datasets, array('udi' => 'ASC'));

        return $datasets;
    }

    /**
     * Generate report for doi migration.
     *
     * @param Dataset $datasets Collection of datasets.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    private function createReportForDoiMigration(Dataset $datasets)
    {
        $headers = array(
            'udi',
            'dataset_status',
            'doi_status',
            'doi_id'
        );

        $this->fileOutput->writeln(implode(',', $headers));

        foreach ($datasets as $dataset) {
            $this->fileOutputArray = array();
            $this->fileOutputArray[] = $dataset->getUdi();
            $this->fileOutputArray[] = $dataset->getMetadataStatus();
            $this->fileOutputArray[] = $dataset->getDoiStatus($dataset);
        }

        return 0;

    }

    /**
     * Gets the DOI status from EZ API.
     *
     * @param Dataset $dataset The dataset instance.
     *
     * @throws Exception Exception thrown when doi metadata method fails.
     *
     * @return string
     */
    private function getDoiStatus(Dataset $dataset)
    {
        $doiStatus = null;

        if ($dataset->getDoi()->getDoi()) {
            try {
                $doiUtil = new DOIutil();
                $doiMetadata = $doiUtil->getDOIMetadata($dataset->getDoi()->getDoi());
                $doiStatus = $doiMetadata['_status'];

            } catch (Exception $e) {
                throw new Exception('Unable to get DOI metadata' . $e->getMessage());
            }
        }

        return $doiStatus;
    }
}
