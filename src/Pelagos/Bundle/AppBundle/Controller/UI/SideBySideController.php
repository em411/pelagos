<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\Account;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;

use Pelagos\Response\TerminateResponse;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 *
 * @Route("/sidebyside")
 */
class SideBySideController extends UIController
{
    /**
     * Valid values for $datasetFileTransferType and $metadataFileTransferType.
     */
    const SUBMISSIONS_STATES = array(
        DatasetSubmission::STATUS_UNSUBMITTED => 'Unsubmitted',
        DatasetSubmission::STATUS_INCOMPLETE => 'Draft',
        DatasetSubmission::STATUS_COMPLETE => 'Submitted',
        DatasetSubmission::STATUS_IN_REVIEW => 'In Review',
    );

    /**
     * The default action for Side by Side.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $udi     The UDI of the Dataset to load.
     *
     * @Route("/{udi}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $udi = null)
    {
        // if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // return $this->redirect('/user/login?destination=' . $request->getPathInfo());
        // }

        $datasetSubmissionHistory = $this->getDatasetSubmissionHistory($udi);

        $submissions = array();

        foreach ($datasetSubmissionHistory->getIterator() as $i => $submission) {
            $data = array();
            $data['version'] = $i;
            $data['udi'] = $submission->getDataset()->getUdi();
            $data['sequence'] = $submission->getSequence();
            $data['status'] = self::SUBMISSIONS_STATES[$submission->getStatus()];
            $data['modifier'] = $submission->getModifier()->getLastName() .
                ', ' . $submission->getModifier()->getFirstName();
            $data['modificationtimestamp'] = $submission->getModificationTimeStamp()->format('c');
            $submissions[] = $data;
        }

        return $this->render(
            'PelagosAppBundle:SideBySide:index.html.twig',
            array(
                'udi' => $datasetSubmissionHistory->first()->getDataset()->getUdi(),
                'submissions' => $submissions,
            )
        );
    }

    /**
     * The get submission form action for the Side By Side controller.
     *
     * @param Request     $request  The Symfony request object.
     * @param string|null $udi      The UDI of the Dataset to load.
     * @param string|null $revision The revision number of the Submission to load.
     *
     * @throws \Exception If revision does not exists.
     *
     * @Route("/getForm/{udi}/{revision}")
     *
     * @return Response A Response instance.
     */
    public function getSubmissionFormAction(Request $request, $udi = null, $revision = null)
    {
        // if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // return $this->redirect('/user/login?destination=' . $request->getPathInfo());
        // }

        $datasetSubmissionHistory = $this->getDatasetSubmissionHistory($udi);

        if ($datasetSubmissionHistory->count() < $revision and $revision !== null) {
            throw new \Exception("Revision $revision does not exist for UDI: $udi");
        }

        if ($revision !== null) {
            $datasetSubmission = $datasetSubmissionHistory[$revision];
        } else {
            $datasetSubmission = $datasetSubmissionHistory->first();
        }

        $researchGroupList = array();
        $account = $this->getUser();
        if (null !== $account) {
            $user = $account->getPerson();
            // Find all RG's user has CREATE_DIF_DIF_ON on.
            $researchGroups = $user->getResearchGroups();
            $researchGroupList = array_map(
                function ($researchGroup) {
                    return $researchGroup->getId();
                },
                $researchGroups
            );
        }

        $form = $this->get('form.factory')->createNamed(null, DatasetSubmissionType::class, $datasetSubmission);

        $terminateResponse = new TerminateResponse();

        return $this->render(
            'PelagosAppBundle:SideBySide:submissionForm.html.twig',
            array(
                'form' => $form->createView(),
                'datasetSubmission' => $datasetSubmission,
                'showForceImport' => false,
                'showForceDownload' => false,
                'researchGroupList' => $researchGroupList,
                'mode' => 'view',
            ),
            $terminateResponse
        );
    }

    /**
     * Get the dataset submission history from UDI.
     *
     * @param string|null $udi The UDI of the Dataset to load.
     *
     * @throws \Exception If dataset if not found.
     * @throws \Exception If more than one dataset is returned.
     *
     * @return DatasetSubmissionHistory An array collection of submissions.
     */
    private function getDatasetSubmissionHistory($udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (count($datasets) == 0) {
            throw \Exception("No dataset found for UDI: $udi");
        }

        if (count($datasets) > 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }

        $dataset = $datasets[0];

        return $dataset->getDatasetSubmissionHistory();
    }
}
