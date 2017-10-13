<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\ReportDatasetDownloadType;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\LogActionItem;
use Pelagos\Entity\Metadata;
use Pelagos\Exception\InvalidDateSelectedException;

use Pelagos\Util\ISOMetadataExtractorUtil;

/**
 * The dataset download report generator.
 *
 * @Route("/dataset-download-report")
 */
class DatasetDownloadReportController extends ReportController
{
    //DateTime format used for date range
    const INREPORT_DATETIMEFORMAT = 'm-d-Y';

    /**
     * This defaultAction generates the form to select the date range for the report.
     *
     * @param Request $request Message response.
     *
     * @Route("")
     *
     * @throws InvalidDateSelectedException Selected Dates are invalid.
     *
     * @return Response|StreamedResponse A Response instance.
     */
    public function defaultAction(Request $request)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
        $form = $this->get('form.factory')->createNamed(
            null,
            ReportDatasetDownloadType::class,
            null
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $startDate = $form->getData()['startDate'];
            $endDate = $form->getData()['endDate'];
            if ($startDate && $endDate) {
                if ($startDate <= $endDate) {
                    $optionalHeaders = [
                        'Start Date' => $startDate->format(self::INREPORT_DATETIMEFORMAT),
                        'End Date' => $endDate->format(self::INREPORT_DATETIMEFORMAT),
                    ];
                    $labels = [
                        'UDI',
                        'TITLE',
                        'PRIMARY POINT OF CONTACT',
                        'PRIMARY POINT OF CONTACT EMAIL',
                        'TOTAL DOWNLOADS',
                        '# OF GOMRI DOWNLOADS',
                        '# OF GOOGLE DOWNLOADS'
                    ];
                    $data = $this->queryData([
                        'startDate' => $startDate,
                        'endDate' => $endDate
                    ]);
                    return $this->writeCsvResponse(
                        $labels,
                        $data,
                        'DatasetDownloadReport-' .
                            (new \DateTime('now'))->format(parent::FILENAME_DATETIMEFORMAT) .
                            '.csv',
                        $optionalHeaders
                    );
                }
            } else {
                throw new InvalidDateSelectedException('The dates selected are invalid.');
            }
        }
        return $this->render(
            'PelagosAppBundle:template:ReportDatasetDownload.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * This implements the abstract method from ReportController to get the data.
     *
     * @param array|NULL $options Additional parameters needed to run the query.
     *
     * @return array  Return an indexed array.
     */
    protected function queryData(array $options = null)
    {
        $container = $this->container;
        $entityManager = $container->get('doctrine')->getManager();
        //Query
        $queryString = 'SELECT dataset.udi,log.payLoad from ' .
            LogActionItem::class . ' log join ' . Dataset::class . ' dataset with
                log.subjectEntityId = dataset.id where log.actionName = :actionName and
                log.subjectEntityName = :subjectEntityName and 
                log.creationTimeStamp >= :startDate 
                and log.creationTimeStamp <= :endDate order by dataset.udi ASC';

        $query = $entityManager->createQuery($queryString);
        $endDateInclusively = clone $options['endDate'];
        $endDateInclusively = $endDateInclusively->add(new \DateInterval('P1D'));
        $query->setParameters([
            'actionName' => 'File Download',
            'subjectEntityName' => 'Pelagos\Entity\Dataset',
            'startDate' => $options['startDate'],
            'endDate' => $endDateInclusively
        ]);
        $results = $query->getResult();

        //process result query into an array with organized data
        $finalArray = array();
        $currentIndex = 0;
        foreach ($results as $result) {
            //initialize array with key  = udi, set title and primary POC
            if (isset($finalArray[$currentIndex]['udi']) && $result['udi'] != $finalArray[$currentIndex]['udi']) {
                $currentIndex++;
            }
            if (!isset($finalArray[$currentIndex])) {
                $finalArray[$currentIndex] = array(
                    'udi' => $result['udi'],
                    'title' => null,
                    'primaryPointOfContact' => null,
                    'primaryPointOfContactEmail' => null,
                    'totalCount' => 0,
                    'GoMRI' => 0,
                    'NonGoMRI' => 0,
                );

                $dataset = $this->container->get('doctrine')->getRepository(Dataset::class)
                    ->findOneBy(array('udi' => $result['udi']));

                $finalArray[$currentIndex]['title'] = $dataset->getTitle();
                //get Primary point of contact from the XML
                $datasetSubmission = $dataset->getDatasetSubmission();
                if ($datasetSubmission instanceof DatasetSubmission
                    and $dataset->getMetadata() instanceof Metadata) {
                    $datasetSubmission->getDatasetContacts()->clear();
                    ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                        $dataset->getMetadata()->getXml(),
                        $datasetSubmission,
                        $this->getDoctrine()->getManager()
                    );
                    $dataset->setDatasetSubmission($datasetSubmission);
                    $finalArray[$currentIndex]['primaryPointOfContact']
                        = $dataset->getPrimaryPointOfContact()->getLastName() .
                        ', ' . $dataset->getPrimaryPointOfContact()->getFirstName();
                    $finalArray[$currentIndex]['primaryPointOfContactEmail']
                        = $dataset->getPrimaryPointOfContact()->getEmailAddress();
                }
            }
            //count user downloads and total download
            if ($result['payLoad']['userType'] == 'GoMRI') {
                $finalArray[$currentIndex]['GoMRI']++;
            } else {
                if ($result['payLoad']['userType'] == 'Non-GoMRI') {
                    $finalArray[$currentIndex]['NonGoMRI']++;
                }
            }
            $finalArray[$currentIndex]['totalCount']++;
        }
        return $finalArray;
    }
}
