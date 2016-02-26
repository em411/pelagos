<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonResearchGroupController extends UIController
{

    /**
     * The Person Research Group action.
     *
     * @param Request $request The HTTP request.
     * @param string  $id      The id of the entity to retrieve.
     *
     * @throws BadRequestHttpException When the Research Group ID is not provided.
     *
     * @Route("/PersonResearchGroup/{id}")
     *
     * @return Response A Response instance.
     */
    public function personResearchGroupAction(Request $request, $id = null)
    {
        $researchGroupId = $request->query->get('ResearchGroup');

        if (!isset($researchGroupId) and !isset($id)) {
            throw new BadRequestHttpException('Research Group parameter is not set');
        }

        $entityHandler = $this->get('pelagos.entity.handler');

        $ui = array();

        if (isset($id)) {
            $personResearchGroup = $entityHandler->get('Pelagos:PersonResearchGroup', $id);
        } else {
            $personResearchGroup = new \Pelagos\Entity\PersonResearchGroup;
            $personResearchGroup->setResearchGroup($entityHandler->get('Pelagos:ResearchGroup', $researchGroupId));
        }

        $form = $this->get('form.factory')->createNamed(null, PersonResearchGroupType::class, $personResearchGroup);

        $ui['PersonResearchGroup'] = $personResearchGroup;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;

        return $this->render('PelagosAppBundle:template:PersonResearchGroup.html.twig', $ui);
    }
}
