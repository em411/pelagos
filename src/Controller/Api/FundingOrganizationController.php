<?php

namespace App\Controller\Api;

use App\Entity\FundingOrganization;
use App\Form\FundingOrganizationType;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The FundingOrganization api controller.
 */
class FundingOrganizationController extends EntityController
{
    /**
     * Get a count of Funding Organizations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Get a count of Funding Organizations.",
     *     @SWG\Response(
     *         response="200",
     *         description="A count of Funding Organizations was successfully returned."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/count",
     *     name="pelagos_api_funding_organizations_count",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(FundingOrganization::class, $request);
    }

    /**
     * Validate a value for a property of a Funding Organization.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Validate a value for a property of a Funding Organization.",
     *     @SWG\Parameter(
     *         name="someProperty",
     *         in="body",
     *         description="todo",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Validation was performed successfully (regardless of validity)."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad parameters were passed in the query string."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/validateProperty",
     *     name="pelagos_api_funding_organizations_validate_property",
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
        return $this->validateProperty(FundingOrganizationType::class, FundingOrganization::class, $request);
    }

    /**
     * Validate a value for a property of an existing Funding Organization.
     *
     * @param integer $id      The id of the existing Funding Organization.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Validate a value for a property of an existing Funding Organization.",
     *     @SWG\Parameter(
     *         name="someProperty",
     *         in="body",
     *         description="todo",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Validation was performed successfully (regardless of validity)."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Bad parameters were passed in the query string."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Organization was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}/validateProperty",
     *     name="pelagos_api_funding_organizations_validate_property_existing",
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
        return $this->validateProperty(FundingOrganizationType::class, FundingOrganization::class, $request, $id);
    }

    /**
     * Get a collection of Funding Organizations.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Get a collection of Funding Organizations.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested collection of Funding Organizations was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations",
     *     name="pelagos_api_funding_organizations_get_collection",
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
        return $this->handleGetCollection(
            FundingOrganization::class,
            $request,
            array('logo' => 'pelagos_api_funding_organizations_get_logo')
        );
    }

    /**
     * Get a single Funding Organization for a given id.
     *
     * @param integer $id The id of the Funding Organization to return.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Get a single Funding Organization for a given id.",
     *     @SWG\Response(
     *         response="200",
     *         description="The requested Funding Organization was successfully retrieved."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Organization was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return FundingOrganization The Funding Organization that was retrieved.
     */
    public function getAction(int $id)
    {
        $fundingOrganization = $this->handleGetOne(FundingOrganization::class, $id);
        if ($fundingOrganization instanceof FundingOrganization and $fundingOrganization->getLogo(true) !== null) {
            $fundingOrganization->setLogo(
                $this->getResourceUrl(
                    'pelagos_api_funding_organizations_get_logo',
                    $fundingOrganization->getId()
                )
            );
        }
        return $fundingOrganization;
    }

    /**
     * Create a new Funding Organization from the submitted data.
     *
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Create a new Funding Organization from the submitted data.",
     *     @SWG\Response(
     *         response="201",
     *         description="The Funding Organization was successfully created."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to create the Funding Organization."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations",
     *     name="pelagos_api_funding_organizations_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Funding Organization in the Location header.
     */
    public function postAction(Request $request)
    {
        $fundingOrganization = $this->handlePost(FundingOrganizationType::class, FundingOrganization::class, $request);
        return $this->makeCreatedResponse('pelagos_api_funding_organizations_get', $fundingOrganization->getId());
    }

    /**
     * Replace a Funding Organization with the submitted data.
     *
     * @param integer $id      The id of the Funding Organization to replace.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Replace a Funding Organization with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Funding Organization was successfully replaced."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Funding Organization."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Organization was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(FundingOrganizationType::class, FundingOrganization::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Funding Organization with the submitted data.
     *
     * @param integer $id      The id of the Funding Organization to update.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Update a Funding Organization with the submitted data.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Funding Organization was successfully updated."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="The request could not be processed due to validation or other errors."
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="The authenticated user was not authorized to edit the Funding Organization."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Organization was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(FundingOrganizationType::class, FundingOrganization::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Funding Organization.
     *
     * @param integer $id The id of the Funding Organization to delete.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Delete a Funding Organization.",
     *     @SWG\Response(
     *         response="204",
     *         description="The Funding Organization was successfully deleted."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The requested Funding Organization was not found."
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="An internal error has occurred."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}",
     *     name="pelagos_api_funding_organizations_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $this->handleDelete(FundingOrganization::class, $id);
        return $this->makeNoContentResponse();
    }

    /**
     * Get the logo for a Funding Organization.
     *
     * @param integer $id The id of the Funding Organization to get the logo for.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Get the logo for a Funding Organization.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when the Funding Organization is not found or it does not have a logo."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}/logo",
     *     name="pelagos_api_funding_organizations_get_logo",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object containing the logo.
     */
    public function getLogoAction(int $id)
    {
        return $this->getProperty(FundingOrganization::class, $id, 'logo');
    }

    /**
     * Set or replace the logo of a Funding Organization via multipart/form-data POST.
     *
     * @param integer $id      The id of the Funding Organization to replace the logo for.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Set or replace the logo of a Funding Organization via multipart/form-data POST.",
     *     @SWG\Parameter(
     *         name="logo",
     *         in="formData",
     *         description="todo",
     *         required=false,
     *         type="file"
     *     ),
     *     @SWG\Response(
     *         response="204",
     *         description="Returned when the logo is successfully set or replaced."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when the Funding Organization is not found."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}/logo",
     *     name="pelagos_api_funding_organizations_post_logo",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function postLogoAction(int $id, Request $request)
    {
        return $this->postProperty(FundingOrganization::class, $id, 'logo', $request);
    }

    /**
     * Set or replace the logo of a Funding Organization via HTTP PUT file upload.
     *
     * @param integer $id      The id of the Funding Organization to replace the logo for.
     * @param Request $request The request object.
     *
     * @Operation(
     *     tags={"Funding Organizations"},
     *     summary="Set or replace the logo of a Funding Organization via HTTP PUT file upload.",
     *     @SWG\Response(
     *         response="204",
     *         description="Returned when the logo is successfully set or replaced."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when the Funding Organization is not found."
     *     )
     * )
     *
     *
     * @Route(
     *     "/api/funding-organizations/{id}/logo",
     *     name="pelagos_api_funding_organizations_put_logo",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putLogoAction(int $id, Request $request)
    {
        return $this->putProperty(FundingOrganization::class, $id, 'logo', $request);
    }
}
