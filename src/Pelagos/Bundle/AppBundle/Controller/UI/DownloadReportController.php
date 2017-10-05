<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\LogActionItem;
use Pelagos\Entity\Metadata;

use Pelagos\Util\ISOMetadataExtractorUtil;

use \DateTime;

/**
 * The dataset download report generator.
 *
 * @Route("/download-report")
 */
class DownloadReportController extends UIController
{
    //Date Time format used in the csv report
    const INREPORT_DATETIMEFORMAT = 'm-d-Y';

    /**
     * This defaultAction generates the form to select the date range for the report.
     *
     * @param Request $request Message response.
     *
     * @Route("")
     *
     * @throws \Exception When startDate is later than endDate.
     *
     * @return Response|StreamedResponse A Response instance.
     */
    public function defaultAction(Request $request)
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
        $form = $this->createFormBuilder()->add(
            'startDate',
            DateType::class,
            array('label' => 'Start Date:',
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'yyyy-mm-dd',
                    'class' => 'startDate',
                )
            )
        )
            ->add(
                'endDate',
                DateType::class,
                array('label' => 'End Date:',
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'yyyy-MM-dd',
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'yyyy-mm-dd',
                        'class' => 'endDate',
                        )
                )
            )
            ->add('submit', SubmitType::class, array('label' => 'Generate Report'))->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $startDate = $form->getData()['startDate'];
            $endDate = $form->getData()['endDate'];
            if ($startDate <= $endDate) {
                return $this->downloadReport($startDate, $endDate);
            } else {
                throw new \Exception('Start Date cannot be later than End Date');
            }
        }
        return $this->render(
            'PelagosAppBundle:template:ReportDatasetDownload.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Running query, doing calculation for number of download, and create the Streamed Response.
     *
     * @param DateTime $startDate First day of the report.
     * @param DateTime $endDate   Last day of the report (inclusively).
     *
     * @return StreamedResponse A csv file.
     */
    public function downloadReport(DateTime $startDate, DateTime $endDate)
    {
        $container = $this->container;
        $response = new StreamedResponse(function () use ($container, $startDate, $endDate) {
            $entityManager = $container->get('doctrine')->getManager();
            $endDateInclusively = clone $endDate;
            $endDateInclusively->add(new \DateInterval('P1D'));
            //Query
            $queryString = 'SELECT dataset.udi,log.payLoad from ' .
                LogActionItem::class . ' log join ' . Dataset::class . ' dataset with
                log.subjectEntityId = dataset.id where log.actionName = :actionName and
                log.subjectEntityName = :subjectEntityName and 
                log.creationTimeStamp >= :startDate 
                and log.creationTimeStamp <= :endDate order by dataset.udi ASC';

            $query = $entityManager->createQuery($queryString);
            $query->setParameters([
                'actionName' => 'File Download',
                'subjectEntityName' => 'Pelagos\Entity\Dataset',
                'startDate' => $startDate,
                'endDate' => $endDateInclusively
            ]);
            $results = $query->getResult();

            $finalArray = [];
            foreach ($results as $result) {
                //initialize array with key  = udi, set title and primary POC
                if (!array_key_exists($result['udi'], $finalArray)) {
                    $finalArray[$result['udi']] = [];
                    $finalArray[$result['udi']]['totalCount'] = 0;
                    $finalArray[$result['udi']]['GoMRI'] = 0;
                    $finalArray[$result['udi']]['NonGoMRI'] = 0;
                    $dataset = $this->entityHandler->getBy(Dataset::class, ['udi' => $result['udi']])[0];
                    $finalArray[$result['udi']]['title'] = $dataset->getTitle();
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
                        $finalArray[$result['udi']]['primaryPointOfContact']
                            = $dataset->getPrimaryPointOfContact()->getLastName() .
                            ', ' . $dataset->getPrimaryPointOfContact()->getFirstName();
                        $finalArray[$result['udi']]['primaryPointOfContactEmail']
                            = $dataset->getPrimaryPointOfContact()->getEmailAddress();
                    }
                }
                //count user downloads and total download
                if ($result['payLoad']['userType'] == 'GoMRI') {
                    $finalArray[$result['udi']]['GoMRI']++;
                } else {
                    if ($result['payLoad']['userType'] == 'Non-GoMRI') {
                        $finalArray[$result['udi']]['NonGoMRI']++;
                    }
                }
                  $finalArray[$result['udi']]['totalCount']++;
            }

            //write csv file
            $handle = fopen('php://output', 'r+');

            // Add header to CSV.
            $createdTime = new DateTime('now');
            fputcsv($handle, ['From', $startDate->format(self::INREPORT_DATETIMEFORMAT)]);
            fputcsv($handle, ['To', $endDate->format(self::INREPORT_DATETIMEFORMAT)]);
            fputcsv($handle, [
                'Created at',
                $createdTime->format(self::INREPORT_DATETIMEFORMAT)
            ]);
            fputcsv(
                $handle,
                [
                    'UDI',
                    'Title',
                    'Primary Point Of Contact',
                    'Primary Point Of Contact Email',
                    'Total Downloads',
                    '# of GoMRI downloads',
                    '# of Google downloads'
                ]
            );
            foreach ($finalArray as $udi => $value) {
                fputcsv($handle, [
                    $udi,
                    $value['title'],
                    $value['primaryPointOfContact'],
                    $value['primaryPointOfContactEmail'],
                    $value['totalCount'],
                    $value['GoMRI'],
                    $value['NonGoMRI']
                ]);
            }
            fclose($handle);
        });
        //generate report filename
        $now = new DateTime('now');
        $fileName = 'downloadReport-' . $now->format('Y-m-d') . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        return $response;
    }
}
