<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * The MDApp controller.
 *
 * @Route("/mdapp")
 */
class MdAppController extends UIController
{
    /**
     * MDApp UI.
     *
     * @Route("")
     *
     * @return Response
     */
    public function defaultAction()
    {
        return $this->renderUi();
    }

    /**
     * Download metadata from persistance.
     *
     * @Route("/download-db-xml/{id}")
     *
     * @return XML
     */
    public function downloadMetadataFromDB($id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        $dataset = $entityHandler->get(Dataset::class, $id);
        $metadataXml = $dataset->getMetadata()->getXml()->asXml();
        $windowsFilenameSafeUdi =  str_replace(':', '-', $dataset->getUdi());
        $response = new Response($metadataXml);
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $windowsFilenameSafeUdi . '-metadata.xml;');
        return $response;
    }

    /**
     * Change the metadata status.
     *
     * @param Request $request The Symfony request object.
     * @param integer $id      The id of the Dataset to change the metadata status for.
     *
     * @Route("/change-metadata-status/{id}")
     * @Method("POST")
     *
     * @return Response
     */
    public function changeMetadataStatusAction(Request $request, $id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        $dataset = $entityHandler->get(Dataset::class, $id);
        if (null !== $request->request->get('to')) {
            $dataset->getDatasetSubmission()->setMetadataStatus(
                $request->request->get('to')
            );
        }
        $entityHandler->update($dataset);
        return $this->renderUi();
    }

    /**
     * Render the UI for MDApp.
     *
     * @return Response
     */
    protected function renderUi()
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        return $this->render(
            'PelagosAppBundle:MdApp:main.html.twig',
            array(
                'jiraBase' => 'https://triton.tamucc.edu/issues',
                'm_dataset' => array(
                    'submitted' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_SUBMITTED)
                    ),
                    'inreview' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_IN_REVIEW)
                    ),
                    'secondcheck' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_SECOND_CHECK)
                    ),
                    'accepted' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED)
                    ),
                    'backtosubmitter' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER)
                    ),
                ),
            )
        );
    }
}
