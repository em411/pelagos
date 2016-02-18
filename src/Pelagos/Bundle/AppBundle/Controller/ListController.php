<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Pelagos\Bundle\AppBundle\Form\ResearchGroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
#use Symfony\Component\HttpFoundation\Response;
#use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The default controller for the Pelagos UI App Bundle.
 */
class ListController extends Controller
{
    /**
     * The Research Group Generate List action.
     *
     * @Route("/ResearchGroup")
     *
     * @return Response A list of Research Groups.
     */
    public function researchGroupListAction(Request $request)
    {
        //return new Response(var_dump($request));
        $ui = array();
        $ui['entityType'] = 'ResearchGroup';
        $ui['isUserLoggedIn'] = true;
        return $this->render('PelagosAppBundle:template:UI-ResearchGroupList.html.twig', $ui);
    }
}
