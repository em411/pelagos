<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Doctrine\Common\Collections\Collection;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetPublication;
use Pelagos\Entity\Publication;
use Pelagos\Entity\PublicationCitation;

/**
 * The Publication api controller.
 */
class PublicationController extends EntityController
{
    /**
     * Fetch and cache a citation for a given DOI.
     *
     * @param Request $request A Symfony http request object, data includes the doi.
     *
     * @ApiDoc(
     *   section = "Publications",
     *   output = "Pelagos\Entity\PublicationCitation",
     *   statusCodes = {
     *     200 = "The requested Dataset was successfully retrieved.",
     *     404 = "The requested Dataset was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @throws \Exception Upon DOI pull failure.
     * @throws \Exception If more than one cached publication found by DOI.
     * @throws \Exception Upon internal unexpected result.
     *
     * @return PublicationCitation
     */
    public function postAction(Request $request)
    {
        //query for a current publication/citation, if non-existent or too old,
        //re-pull from doi.org and re-cache.

        $doi = $request->request->get('doi');
        $pubLinkUtil = $this->get('pelagos.util.publink');
        $entityHandler = $this->get('pelagos.entity.handler');

        // Attempt to get publication by DOI.
        $publications = $entityHandler->getBy(Publication::class, array('doi' => $doi));
        if (gettype($publications) == 'array') {
            // Case 1 - Data was previously cached.  Return cached copy instead, but lie a little about creation.
            if (count($publications) == 1) {
                $publication = $publications[0];
                $publicationCitations = $publication->getCitations();

                $citation = $publicationCitations[0];
                $citationAge = $citation->getModificationTimeStamp();

                return $this->makeCreatedResponse('pelagos_api_publications_get', $publication->getId());
            // Does not exist in cache.  Pull from doi.org, cache and return citation.
            } elseif (count($publications == 0)) {
                $citationStruct = $pubLinkUtil->getCitationFromDoiDotOrg($doi);
                if (200 == $citationStruct['status']) {
                    $entityHandler = $this->get('pelagos.entity.handler');

                    $publication = new Publication($doi);
                    $entityHandler->create($publication);

                    $publicationCitation = $citationStruct['citation'];
                    $publicationCitation->setPublication($publication);

                    $entityHandler->create($publicationCitation);

                    return $this->makeCreatedResponse('pelagos_api_publications_get', $publication->getId());
                } else {
                    throw new \Exception('Unable to pull citation from doi.org.');
                }
            } else {
                throw new \Exception("Unexpected system error. DOI $doi references more than 1 cached Publication.");
            }
        } else {
            throw new \Exception('Unexpected system error. Expected array of Publications, but got something else.');
        }
    }

    /**
     * Get a single Publication.
     *
     * @param integer $id Entity ID for Publication.
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return Publication
     */
    public function getAction($id)
    {
        return $this->handleGetOne(Publication::class, $id);
    }

    /**
     * Link a Publication to a Dataset by their respective IDs.
     *
     * @param integer $pubId
     *
     * @return Request
     *
     * @ApiDoc(
     *   section = "Publications",
     *   parameters = {
     *                    {"name"="dataset", "dataType"="integer", "required"=true, "description"="Numeric ID of Dataset to be linked."}
     *                },
     *   statusCodes = {
     *     204 = "The Publication has been linked to the Dataset.",
     *     400 = "The request could not be processed. (see message for reason)",
     *     404 = "The Publication requested could not be found.",
     *     403 = "The authenticated user was not authorized to create a Publication to Dataset link.",
     *     500 = "An internal error has occurred."
     *   }
     * )
     *
     * @Rest\View
     */
    public function linkAction($id, Request $request)
    {
        $datasetId = $request->query->get('dataset');

        $publication = $this->handleGetOne(Publication::class, $id);
        try {
            $dataset = $this->handleGetOne(Dataset::class, $datasetId);
        } catch (NotFoundHttpException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        // do something...
        $dataPub = new DatasetPublication($publication, $dataset);
        $entityHandler = $this->get('pelagos.entity.handler');
        $entityHandler->create($dataPub);

        return $this->makeNoContentResponse();
    }
}
