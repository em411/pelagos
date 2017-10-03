<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;

use \DateTime;
use \DateInterval;

const MONTH_DAY_FORMAT = 'M Y';
const GOMRI_STRING = 'Gulf of Mexico Research Initiative (GoMRI)';

/**
 * The GOMRI datasets report generator.
 *
 * @Route("/gomri")
 */
class GomriReportController extends UIController
{
    /**
     * This is a parameterless report, so all is in the default action.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction()
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $container = $this->container;
        $response = new StreamedResponse(function () use ($container) {
            // final results array.
            $stats = array();

            $entityManager = $container->get('doctrine')->getManager();

            $dateTime = new DateTime('May 2012');

            $now = new DateTime('now');

            while ($dateTime < $now) {
                $stats[$dateTime->format(MONTH_DAY_FORMAT)] = array(
                    'monthly_identified' => 0,
                    'monthly_registered' => 0,
                    'monthly_available' => 0,
                );
                $dateTime->add(new DateInterval('P1M'));
            }

            // Query Identified.
            $queryString = 'SELECT dif.creationTimeStamp ' .
                'FROM ' . Dataset::class . ' dataset ' .
                'JOIN dataset.dif dif ' .
                'JOIN dataset.researchGroup researchgroup ' .
                'JOIN researchgroup.fundingCycle fundingCycle ' .
                'JOIN fundingCycle.fundingOrganization fundingOrganization ' .
                'WHERE fundingOrganization.name = :gomri ' .
                'AND dif.status = :difStatusApproved';
            $query = $entityManager->createQuery($queryString);
            $query->setParameters(array(
                'difStatusApproved' => DIF::STATUS_APPROVED,
                'gomri' => GOMRI_STRING,
            ));
            $results = $query->getResult();

            foreach ($results as $result) {
                $monthDay = date(MONTH_DAY_FORMAT, $result['creationTimeStamp']->getTimestamp());
                $stats[$monthDay]['monthly_identified']++;
            }

            // Query Registered.
            $queryString = 'SELECT datasetsubmission.creationTimeStamp ' .
                'FROM ' . DatasetSubmission::class . ' datasetsubmission ' .
                'JOIN datasetsubmission.dataset dataset ' .
                'JOIN dataset.researchGroup researchgroup ' .
                'JOIN researchgroup.fundingCycle fundingCycle ' .
                'JOIN fundingCycle.fundingOrganization fundingOrganization ' .
                'WHERE datasetsubmission IN ' .
                '   (SELECT MIN(subdatasetsubmission.id)' .
                '   FROM ' . DatasetSubmission::class . ' subdatasetsubmission' .
                '   WHERE subdatasetsubmission.datasetFileUri IS NOT null ' .
                '   GROUP BY subdatasetsubmission.dataset)' .
                'AND fundingOrganization.name = :gomri ';
            $query = $entityManager->createQuery($queryString);
            $query->setParameters(array(
                'gomri' => GOMRI_STRING,
            ));
            $results = $query->getResult();

            foreach ($results as $result) {
                $monthDay = date(MONTH_DAY_FORMAT, $result['creationTimeStamp']->getTimestamp());
                $stats[$monthDay]['monthly_registered']++;
            }

            // Query Available.
            $queryString = 'SELECT datasetsubmission.creationTimeStamp ' .
                'FROM ' . DatasetSubmission::class . ' datasetsubmission ' .
                'JOIN datasetsubmission.dataset dataset ' .
                'JOIN dataset.researchGroup researchgroup ' .
                'JOIN researchgroup.fundingCycle fundingCycle ' .
                'JOIN fundingCycle.fundingOrganization fundingOrganization ' .
                'WHERE datasetsubmission IN ' .
                '(SELECT MIN(subdatasetsubmission.id) ' .
                '   FROM ' . DatasetSubmission::class . ' subdatasetsubmission' .
                '   WHERE subdatasetsubmission.datasetFileUri is not null ' .
                '   AND subdatasetsubmission.metadataStatus = :metadataStatus ' .
                '   AND subdatasetsubmission.restrictions = :restrictions ' .
                '   AND (subdatasetsubmission.datasetFileTransferStatus = :fileTransferStatusCompleted ' .
                '       OR subdatasetsubmission.datasetFileTransferStatus = :fileTransferStatusRemotelyHosted) ' .
                '   GROUP BY subdatasetsubmission.dataset)' .
                'AND fundingOrganization.name = :gomri';
            $query = $entityManager->createQuery($queryString);
            $query->setParameters(array(
                'metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED,
                'restrictions' => DatasetSubmission::RESTRICTION_NONE,
                'fileTransferStatusCompleted' => DatasetSubmission::TRANSFER_STATUS_COMPLETED,
                'fileTransferStatusRemotelyHosted' => DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED,
                'gomri' => GOMRI_STRING,
            ));
            $results = $query->getResult();

            foreach ($results as $result) {
                $monthDay = date(MONTH_DAY_FORMAT, $result['creationTimeStamp']->getTimestamp());
                $stats[$monthDay]['monthly_available']++;
            }

            $handle = fopen('php://output', 'r+');

            // Add header to CSV.
            fputcsv(
                $handle,
                array(
                    'Month', 'Year',
                    'Monthly Identified',
                    'Total Identified',
                    'Monthly Registered',
                    'Total Registered',
                    'Monthly Available',
                    'Total Available',
                )
            );

            $totalIdentified = 0;
            $totalRegistered = 0;
            $totalAvailable = 0;

            foreach ($stats as $monthDay => $stat) {
                $totalIdentified += $stat['monthly_identified'];
                $totalRegistered += $stat['monthly_registered'];
                $totalAvailable += $stat['monthly_available'];
                $date = new DateTime($monthDay);

                fputcsv(
                    $handle,
                    array(
                        $date->format('m'),
                        $date->format('Y'),
                        $stat['monthly_identified'],
                        $totalIdentified,
                        $stat['monthly_registered'],
                        $totalRegistered,
                        $stat['monthly_available'],
                        $totalAvailable,
                    )
                );
            }

            fclose($handle);
        });

        $now = new DateTime('now');
        $fileName = 'gomriReport-' . $now->format('Y-m-d') . '.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        return $response;
    }
}
