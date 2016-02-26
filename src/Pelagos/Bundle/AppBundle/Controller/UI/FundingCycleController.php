<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;


use Pelagos\Bundle\AppBundle\Form\FundingCycleType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class FundingCycleController extends UIController
{
    /**
     * The Funding Cycle action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/FundingCycle/{id}")
     *
     * @return Response A Response instance.
     */
    public function fundingCycleAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        
        $ui = array();
        
        if ($id !== null) {
            $fundingCycle = $entityHandler->get('Pelagos:FundingCycle', $id);
            
            if (!$fundingCycle instanceof \Pelagos\Entity\FundingCycle) {
                throw $this->createNotFoundException('The Funding Cycle was not found');
            }
            
        } else {
            $fundingCycle = new \Pelagos\Entity\FundingCycle;
        }
        
        $form = $this->get('form.factory')->createNamed(null, FundingCycleType::class, $fundingCycle);
        
        $ui['FundingCycle'] = $fundingCycle;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;
        
        return $this->render('PelagosAppBundle:template:FundingCycle.html.twig', $ui);
    }
}
