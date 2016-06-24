<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;

/**
 * The Metadata api controller.
 */
class MetadataController extends EntityController
{
    /**
     * Get a single Metadata for a given id.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Metadata",
     *   output = "XML",
     *   statusCodes = {
     *     200 = "The requested Metadata was successfully retrieved.",
     *     404 = "The requested Dataset was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @throws \Exception             When more than one dataset is found.
     * @throws NotFoundHttpException When dataset is not found, or no metadata is available.
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        $datasets = $this->handleGetCollection(Dataset::class, $request);

        if (count($datasets) > 1) {
            throw new \Exception('Found more than one Dataset');
        } elseif (count($datasets) == 0) {
            throw new NotFoundHttpException('Dataset Not Found');
        }

        $dataset = $datasets[0];
        $metadata = $dataset->getMetadata();

        if ($dataset->getMetadataStatus() != DatasetSubmission::METADATA_STATUS_NONE) {
            if ($dataset->getMetadata() instanceof Metadata and
                $dataset->getMetadata()->getXml() instanceof \SimpleXMLElement
            ) {
                $xml = $metadata->getXml()->asXML();
            } else {
                $fileInfo = $this->get('pelagos.util.data_store')->getDownloadFileInfo($dataset->getUdi(), 'metadata');
                $xmlDoc = new \SimpleXMLElement($fileInfo->getRealPath(), null, true);
                $xml = $xmlDoc->asXML();
            }
        } else {
            $xml = $this->get('twig')->render(
                'PelagosAppBundle:MetadataGenerator:MI_Metadata.xml.twig',
                array(
                    'dataset' => $dataset,
                    'metadataFilename' => preg_replace('/:/', '-', $dataset->getUdi()) . '-metadata.xml',
                    )
            );
        }

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
