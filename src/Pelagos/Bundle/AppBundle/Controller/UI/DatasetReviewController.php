<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pelagos\Entity\Dataset;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-review")
 */
class DatasetReviewController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action for Dataset Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $dataset = null;
        $udi = $request->query->get('udiReview');

        if ($udi !== null) {
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));
            if (count($datasets) == 1) {
                $dataset = $datasets[0];
            }
        }

        return $this->render(
            'PelagosAppBundle:DatasetReview:index.html.twig',
            [
                'dataset' => $dataset,
                'udi' => $udi
            ]
        );
    }
}
