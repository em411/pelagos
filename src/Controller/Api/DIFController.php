<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\View;

use App\Entity\Dataset;
use App\Entity\DIF;

use App\Event\EntityEventDispatcher;

use App\Form\DIFType;
use App\Security\Voter\DIFVoter;
use App\Util\Udi;

/**
 * The DIF api controller.
 */
class DIFController extends EntityController
{
    /**
     * Get a count of DIFs.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   input = {
     *     "class": "App\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "DIFs",
     *       "data_class": "App\Entity\DIF"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of DIFs was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @View()
     *
     * @Route("/api/difs/count", name="pelagos_api_difs_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(DIF::class, $request);
    }

    /**
     * Get a collection of DIFs.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<App\Entity\DIF>",
     *   statusCodes = {
     *     200 = "The requested collection of DIFs was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs", name="pelagos_api_difs_get_collection", methods={"GET"})
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return array
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(DIF::class, $request);
    }

    /**
     * Get a single DIF for a given id.
     *
     * @param integer $id The id of the DIF to return.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   output = "App\Entity\DIF",
     *   statusCodes = {
     *     200 = "The requested DIF was successfully retrieved.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/difs/{id}", name="pelagos_api_difs_get", methods={"GET"}, defaults={"_format"="json"})
     *
     * @return DIF
     */
    public function getAction($id)
    {
        return $this->handleGetOne(DIF::class, $id);
    }

    /**
     * Create a new DIF from the submitted data.
     *
     * @param Request $request The request object.
     * @param EntityEventDispatcher $entityEventDispatcher
     * @param Udi $udiUtil
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new DIF in the Location header.
     * @throws \Exception
     * @ApiDoc(
     *   section = "DIFs",
     *   input = {"class" = "App\Form\DIFType", "name" = ""},
     *   statusCodes = {
     *     201 = "The DIF was successfully created.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to create the DIF.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs", name="pelagos_api_difs_post", methods={"POST"})
     */
    public function postAction(Request $request, EntityEventDispatcher $entityEventDispatcher, Udi $udiUtil)
    {
        // Create a new Dataset.
        $dataset = new Dataset;
        // Set the creator for the Dataset.
        $dataset->setCreator($this->getUser()->getPerson());
        // Create a new DIF for the Dataset.
        $dif = new DIF($dataset);
        // Handle the post (DIF will be created and Dataset creation will cascade).
        $this->handlePost(DIFType::class, DIF::class, $request, $dif);
        // Mint an UDI for the Dataset.
        $udi = $udiUtil->mintUdi($dataset);
        // Update the Dataset with the new UDI.
        $this->entityHandler->update($dataset);
        // If the "Save and Continue Later" button was pressed.
        if ($request->request->get('button') === 'save') {
            // Dispatch an event to indicate a DIF has been saved but not submitted.
            $entityEventDispatcher->dispatch($dif, 'saved_not_submitted');
        }
        // Return a created response, adding the UDI as a custom response header.
        return $this->makeCreatedResponse('pelagos_api_difs_get', $dif->getId(), array('X-UDI' => $udi));
    }

    /**
     * Replace a DIF with the submitted data.
     *
     * @param integer $id      The id of the DIF to replace.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DIFType", "name" = ""},
     *   statusCodes = {
     *     204 = "The DIF was successfully replaced.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs/{id}", name="pelagos_api_difs_put", methods={"PUT"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction($id, Request $request)
    {
        $this->handleUpdate(DIFType::class, DIF::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a DIF with the submitted data.
     *
     * @param integer $id      The id of the DIF to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DIFType", "name" = ""},
     *   statusCodes = {
     *     204 = "The DIF was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs/{id}", name="pelagos_api_difs_patch", methods={"PATCH"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(DIFType::class, DIF::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Submit a DIF.
     *
     * @param integer $id The id of the DIF to submit.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to submit the DIF.
     * @throws BadRequestHttpException When the DIF could not be submitted.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully submitted.",
     *     400 = "The DIF could not be submitted (see error message for reason).",
     *     403 = "You do not have sufficient privileges to submit this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs/{id}/submit", name="pelagos_api_difs_submit", methods={"PATCH"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function submitAction($id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to submit it.
        if (!$this->isGranted(DIFVoter::CAN_SUBMIT, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to submit this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // Try to submit the DIF.
            $dif->submit();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch a 'submitted' event.
        $this->entityHandler->update($dif, 'submitted');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Approve a DIF.
     *
     * @param integer $id The id of the DIF to approve.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to approve the DIF.
     * @throws BadRequestHttpException When the DIF could not be approved.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully approved.",
     *     400 = "The DIF could not be approved (see error message for reason).",
     *     403 = "You do not have sufficient privileges to approve this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs/{id}/approve", name="pelagos_api_difs_approve", methods={"PATCH"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function approveAction($id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to approve it.
        if (!$this->isGranted(DIFVoter::CAN_APPROVE, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to approve this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // If DIF was saved but not submitted and approval is attempted, submit it first then approve.
            if ($dif->getStatus() === DIF::STATUS_UNSUBMITTED) {
                $dif->submit();
            }
            // Try to approve the DIF.
            $dif->approve();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch an 'approved' event.
        $this->entityHandler->update($dif, 'approved');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Reject a DIF.
     *
     * @param integer $id The id of the DIF to reject.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to reject the DIF.
     * @throws BadRequestHttpException When the DIF could not be rejected.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully rejected.",
     *     400 = "The DIF could not be rejected (see error message for reason).",
     *     403 = "You do not have sufficient privileges to reject this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs/{id}/reject", name="pelagos_api_difs_reject", methods={"PATCH"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function rejectAction($id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to reject it.
        if (!$this->isGranted(DIFVoter::CAN_REJECT, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to reject this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // Try to reject the DIF.
            $dif->reject();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch a 'rejected' event.
        $this->entityHandler->update($dif, 'rejected');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Unlock a DIF.
     *
     * @param integer $id The id of the DIF to unlock.
     *
     * @throws AccessDeniedHttpException   When the DIF authenticated user does not have permission to unlock the DIF.
     * @throws BadRequestHttpException When the DIF could not be unlocked.
     *
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully unlocked.",
     *     400 = "The DIF could not be unlocked (see error message for reason).",
     *     403 = "You do not have sufficient privileges to unlock this DIF.",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs/{id}/unlock", name="pelagos_api_difs_unlock", methods={"PATCH"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function unlockAction($id)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to unlock it.
        if (!$this->isGranted(DIFVoter::CAN_UNLOCK, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to unlock this ' . $dif::FRIENDLY_NAME . '.'
            );
        }
        try {
            // Try to unlock the DIF.
            $dif->unlock();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        // Update the DIF in persistence and dispatch an 'unlocked' event.
        $this->entityHandler->update($dif, 'unlocked');
        // Update the Dataset too because cascade persist doesn't work as expected.
        $this->entityHandler->update($dif->getDataset());
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }

    /**
     * Request a DIF be unlocked.
     *
     * @param integer $id The id of the DIF to request unlock for.
     * @param EntityEventDispatcher $entityEventDispatcher
     *
     * @return Response A response object with an empty body and a "no content" status code.
     * @ApiDoc(
     *   section = "DIFs",
     *   statusCodes = {
     *     204 = "The DIF was successfully requested to be unlocked.",
     *     400 = "The DIF cannot be requested to be unlocked (see error message for reason).",
     *     404 = "The requested DIF was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/difs/{id}/request-unlock", name="pelagos_api_difs_request_unlock", methods={"PATCH"})
     *
     */
    public function requestUnlockAction($id, EntityEventDispatcher $entityEventDispatcher)
    {
        // Get the specified DIF.
        $dif = $this->handleGetOne(DIF::class, $id);
        // Check if the user has permission to request it be unlocked.
        if (!$this->isGranted(DIFVoter::CAN_REQUEST_UNLOCK, $dif)) {
            // Throw an exception if they don't.
            throw new AccessDeniedHttpException(
                'You do not have sufficient privileges to request this ' . $dif::FRIENDLY_NAME . ' be unlocked'
            );
        }
        // Check if the DIF is in an unlockable state.
        if (!$dif->isUnlockable()) {
            // Throw an exception if it's not.
            throw new BadRequestHttpException('This ' . $dif::FRIENDLY_NAME . ' cannot be unlocked');
        }
        // Dispatch an 'unlock_requested' event.
        $entityEventDispatcher->dispatch($dif, 'unlock_requested');
        // Return a no content success response.
        return $this->makeNoContentResponse();
    }
}