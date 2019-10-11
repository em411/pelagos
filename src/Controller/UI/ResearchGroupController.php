<?php

namespace App\Controller\UI;

use App\Entity\ResearchGroup;
use App\Handler\EntityHandler;
use App\Security\EntityProperty;
use App\Form\ResearchGroupType;
use App\Form\PersonResearchGroupType;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class ResearchGroupController extends AbstractController
{
    /**
     * The Research Group action.
     *
     * @param EntityHandler $entityHandler
     * @param string $id The id of the entity to retrieve.
     *
     * @return Response A Response instance.
     *
     * @Route("/research-group/{id}", name="pelagos_app_ui_researchgroup_default")
     */
    public function defaultAction(EntityHandler $entityHandler, $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();
        $ui['PersonResearchGroups'] = array();

        if (isset($id)) {
            $researchGroup = $entityHandler->get(ResearchGroup::class, $id);

            if (!$researchGroup instanceof \App\Entity\ResearchGroup) {
                throw $this->createNotFoundException('The Research Group was not found!');
            }

            foreach ($researchGroup->getPersonResearchGroups() as $personResearchGroup) {
                $form = $this
                    ->get('form.factory')
                    ->createNamed(null, PersonResearchGroupType::class, $personResearchGroup);
                $formView = $form->createView();

                $ui['PersonResearchGroups'][] = $personResearchGroup;
                $ui['PersonResearchGroupForms'][$personResearchGroup->getId()] = $formView;
                $ui['PersonResearchGroupEditLabel'][$personResearchGroup->getId()]
                    = new EntityProperty($personResearchGroup, 'label');
            }

            $newResearchGroupPerson = new \App\Entity\PersonResearchGroup;
            $newResearchGroupPerson->setResearchGroup($researchGroup);
            $ui['newResearchGroupPerson'] = $newResearchGroupPerson;
            $ui['newResearchGroupPersonForm'] = $this
                ->get('form.factory')
                ->createNamed(null, PersonResearchGroupType::class, $ui['newResearchGroupPerson'])
                ->createView();
        } else {
            $researchGroup = new \App\Entity\ResearchGroup;
        }

        $form = $this->get('form.factory')->createNamed(null, ResearchGroupType::class, $researchGroup);
        $ui['form'] = $form->createView();
        $ui['ResearchGroup'] = $researchGroup;
        $ui['entityService'] = $entityHandler;

        return $this->render('template/ResearchGroup.html.twig', $ui);
    }
}
