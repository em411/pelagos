<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\DataCenter;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DistributionPoint;
use Pelagos\Entity\Person;

/**
 * Back fill all the accepted dataset metadata xml to dataset submission.
 *
 * @see ContainerAwareCommand
 */
class BackFillDistributionPointCommand extends ContainerAwareCommand
{
    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-submission:back-fill-distribution-point-command')
            ->setDescription('Back fill distribution points for accepted/submitted dataset submission from dataset hosted by GRIIDC.');
    }

    /**
     * Script to generate dataset-submissions for already accepted metadata.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception  When dataset or person is not found.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This command takes no input.
        unset($input);

        // to show no. of datasetssubmission.
        $i = 0;

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $queryString = 'SELECT dataset.udi udi, dsubmission datasetSubmission FROM ' .
            DatasetSubmission::class . ' dsubmission JOIN ' . Dataset::class .
            ' dataset WITH dsubmission.dataset = dataset 
                WHERE dsubmission.datasetFileTransferStatus != :remotelyhosted 
                AND (dsubmission.metadataStatus = :submittedstatus 
                OR dsubmission.metadataStatus = :acceptedstatus)';
        $query = $entityManager->createQuery($queryString);
        $query->setParameters([
            'remotelyhosted' => DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED,
            'submittedstatus' => DatasetSubmission::METADATA_STATUS_SUBMITTED,
            'acceptedstatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED
        ]);
        $results = $query->getResult();

        $defaultDistributionContact = $entityManager->getRepository(DataCenter::class)->findOneBy(array('organizationName' => 'GRIIDC'));
        $defaultRoleCode = 'distributor';
        $defaultBaseDistributionUrl = 'https://data.gulfresearchinitiative.org/data/';
        $creatorPerson = $entityManager->getRepository(Person::class)->findOneBy(array('id' => 0));

        foreach ($results as $row) {

            $datasetSubmission = $row['datasetSubmission'];
            if ($datasetSubmission->getDistributionPoints()->isEmpty()) {
                $distributionPoint = new DistributionPoint();
                $distributionPoint->setDataCenter($defaultDistributionContact);
                $distributionPoint->setRoleCode($defaultRoleCode);
                $distributionPoint->setDistributionUrl($defaultBaseDistributionUrl . $row['udi']);
                $distributionPoint->setCreator($creatorPerson);

                $datasetSubmission->addDistributionPoint($distributionPoint);

                $entityManager->persist($datasetSubmission);

                $i++;
                echo "\n #" . $i . ' Backfilling completed for dataset submission id ' . $datasetSubmission->getId();
            }
        }

        if ($i > 0) {
            echo '\n Flushing...';
            $entityManager->flush();
        }
        echo "\n Backfilling completed for " . $i . " entries!\n";

        return 0;
    }
}
