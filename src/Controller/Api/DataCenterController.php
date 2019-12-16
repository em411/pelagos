<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use App\Form\DataCenterType;

use App\Entity\DataCenter;
use App\Entity\Entity;

/**
 * The API Controller Class for DataCenter.
 */
class DataCenterController extends EntityController
{
    /**
     * Get a count of Data Centers.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Centers",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Data Centers",
     *       "data_class": "Pelagos\Entity\DataCenter"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Data Centers was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/data-center/count", name="pelagos_api_data_center_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(DataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a Data center.
     *
     * @param Request $request A Symfony request instance.
     *
     * @ApiDoc(
     *     section = "Data Centers",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-center/validateProperty",
     *     name="pelagos_api_data_center_validate_property",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyAction(Request $request)
    {
        return $this->validateProperty(DataCenterType::class, DataCenter::class, $request);
    }

    /**
     * Validate a value for a property of a existing Data center.
     *
     * @param integer $id      The id of the existing Data center.
     * @param Request $request A Symfony request instance.
     *
     * @ApiDoc(
     *     section = "Data Centers",
     *   parameters = {{"name"="someProperty", "dataType"="string", "required"="true"}},
     *   statusCodes = {
     *     200 = "Validation was performed successfully (regardless of validity).",
     *     400 = "Bad parameters were passed in the query string.",
     *     404 = "The requested Data center was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-center/{id}/validateProperty",
     *     name="pelagos_api_data_center_validate_property_existing",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(DataCenterType::class, DataCenter::class, $request, $id);
    }

    /**
     * Get a collection of Datacenters.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Centers",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCollectionType",
     *     "name": "",
     *     "options": {
     *       "label": "Data Center",
     *       "data_class": "Pelagos\Entity\DataCenter"
     *     }
     *   },
     *   output = "array<Pelagos\Entity\DataCenter>",
     *   statusCodes = {
     *     200 = "The requested collection of Data Centers was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-center",
     *     name="pelagos_api_data_center_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(DataCenter::class, $request);
    }

    /**
     * Get a data center for a given id.
     *
     * @param integer $id The id of the Data center.
     *
     * @ApiDoc(
     *     section = "Data Centers",
     *     input = {"class" = "Pelagos\Bundle\AppBundle\Form\DataCenterType", "name" = ""},
     *     statusCodes = {
     *       200 = "Successfully retrieved the Data Center.",
     *       404 = "The requested Data Center was not found.",
     *       500 = "An internal error has occurred.",
     *     }
     * )
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return Entity(DataCenter)
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(DataCenter::class, $id);
    }

    /**
     * Create a new Data Center.
     *
     * @param Request $request The Symfony request object.
     *
     * @ApiDoc(
     *     section = "Data Centers",
     *     input = {"class" = "Pelagos\Bundle\AppBundle\Form\DataCenterType", "name" = ""},
     *     statusCodes = {
     *       201 = "Successfully created a new Data Center.",
     *       400 = "The request could not be processed due to validation or other errors.",
     *       403 = "The authenticated user was not authorized to create the Data Center.",
     *       500 = "An internal error has occurred.",
     *     }
     * )
     *
     * @Route(
     *     "/api/data-center",
     *     name="pelagos_api_data_center_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with empty body and status code.
     */
    public function postAction(Request $request)
    {
        $dataCenter = $this->handlePost(DataCenterType::class, DataCenter::class, $request);
        return $this->makeCreatedResponse('pelagos_api_data_center_get', $dataCenter->getId());
    }

    /**
     * Replace a Data Center with the submitted data.
     *
     * @param integer $id      The id of the Data Center to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Centers",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DataCenterType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Data Center was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Data Center.",
     *     404 = "The requested Data Center was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(DataCenterType::class, DataCenter::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Data Center with the submitted data.
     *
     * @param integer $id      The id of the Data Center to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Data Centers",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DataCenterType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Data Center was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Data Center.",
     *     404 = "The requested Data Center was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(DataCenterType::class, DataCenter::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Data Center.
     *
     * @param integer $id The id of the Data Center to delete.
     *
     * @ApiDoc(
     *   section = "Data Centers",
     *   statusCodes = {
     *     204 = "The Data Center was successfully deleted.",
     *     404 = "The requested Data Center was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/data-center/{id}",
     *     name="pelagos_api_data_center_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(DataCenter::class, $id);
        return $this->makeNoContentResponse();
    }
}