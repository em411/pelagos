<?php

namespace App\Command;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\DatasetSubmission;

/**
 * Back fill all the submitted metadata xml to dataset submission.
 *
 * @see Command
 */
class HandleSubmissionWithBadGMLCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'onetime:handle-bad-gml-submission';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * A Rabbitmq producer instance.
     *
     * @var Producer $producer
     */
    protected $producer;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param Producer               $producer      A Rabbitmq producer instance.
     */
    public function __construct(EntityManagerInterface $entityManager, Producer $producer)
    {
        $this->entityManager = $entityManager;
        $this->producer = $producer;
        parent::__construct();
    }

    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Make the submission accessible in Dataset Review and Submission tool and retrigger filer/hasher.')
            ->addArgument('udi', InputArgument::REQUIRED, 'What is the UDI of the dataset?');
    }

    /**
     * Script to nullify spatial extents and remove distribution point tied to the dataset submission of a dataset.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception When dataset not found.
     * @throws \Exception When datasetSubmission not found.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $udi = $input->getArgument('udi');

        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(array('udi' => $udi));

        if (count($datasets) == 0) {
            throw new \Exception('Could not find a dataset with the udi provided.');
        }

        $dataset = $datasets[0];

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!($datasetSubmission instanceof DatasetSubmission)) {
            throw new \Exception('Could not find Dataset Submission.');
        }

        $datasetSubmission->setSpatialExtent(null);

        //clear distribution points if there is one that has no Data Center
        if (count($datasetSubmission->getDistributionPoints()) === 1) {
            if (null === $datasetSubmission->getDistributionPoints()[0]->getDataCenter()) {
                $datasetSubmission->removeDistributionPoint($datasetSubmission->getDistributionPoints()[0]);
            }
        }

        $this->entityManager->persist($datasetSubmission);
        $this->entityManager->flush();

        //re-trigger dataset submission producer
        $this->producer->publish(
            $datasetSubmission->getId(),
            'dataset.' . $datasetSubmission->getDatasetFileTransferType()
        );

        $output->writeln('Success: submission ID:' . $datasetSubmission->getId() . ' - Dataset udi: ' . $udi);
    }
}
