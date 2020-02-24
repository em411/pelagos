<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Account;
use App\Event\LogActionItemEventDispatcher;

use App\Util\Search;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 */
class SearchPageController extends AbstractController
{

    /**
     * The log action item entity event dispatcher.
     *
     * @var LogActionItemEventDispatcher
     */
    protected $logActionItemEventDispatcher;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LogActionItemEventDispatcher $logActionItemEventDispatcher The log action item event dispatcher.
     */
    public function __construct(LogActionItemEventDispatcher $logActionItemEventDispatcher)
    {
        $this->logActionItemEventDispatcher = $logActionItemEventDispatcher;
    }

    /**
     * The default action for Dataset Review.
     *
     * @param Request $request    The Symfony request object.
     * @param Search  $searchUtil Search utility class object.
     *
     * @Route("/search", name="pelagos_app_ui_searchpage_default")
     *
     * @return Response
     */
    public function defaultAction(Request $request, Search $searchUtil)
    {
//        $results = array();
//        $count = 0;
//        $requestParams = $this->getRequestParams($request);
//        $researchGroupsInfo = array();
//        $fundingOrgInfo = array();
//        $statusInfo = array();
//
//        if (!empty($requestParams['query'])) {
//            $buildQuery = $searchUtil->buildQuery($requestParams);
//            $results = $searchUtil->findDatasets($buildQuery);
//            $count = $searchUtil->getCount($buildQuery);
//            $researchGroupsInfo = $searchUtil->getResearchGroupAggregations($buildQuery);
//            $fundingOrgInfo = $searchUtil->getFundingOrgAggregations($buildQuery);
//            $statusInfo = $searchUtil->getStatusAggregations($buildQuery);
//            $elasticScoreFirstResult = null;
//            if (!empty($results)) {
//                $elasticScoreFirstResult = $results[0]->getResult()->getHit()['_score'];
//            }
//            $this->dispatchSearchTermsLogEvent($requestParams, $count, $elasticScoreFirstResult);
//        }
//
//        return $this->render(
//            'Search/default.html.twig',
//            array(
//                'query' => $requestParams['query'],
//                'field' => $requestParams['field'],
//                'results' => $results,
//                'count' => $count,
//                'page' => $requestParams['page'],
//                'researchGroupsInfo' => $researchGroupsInfo,
//                'fundingOrgInfo' => $fundingOrgInfo,
//                'statusInfo' => $statusInfo,
//                'collectionStartDate' => $requestParams['collectionStartDate'],
//                'collectionEndDate' => $requestParams['collectionEndDate'],
//            )
//        );
        return $this->render('Search/vue-index.html.twig');
    }

    /**
     * The default action for Dataset Review.
     *
     * @param Request $request    The Symfony request object.
     * @param Search  $searchUtil Search utility class object.
     *
     * @Route("/search/results", name="pelagos_app_ui_searchpage_results")
     *
     * @return Response
     */
    public function getSearchResults(Request $request, Search $searchUtil)
    {
        $results = array();
        $count = 0;
        $requestParams = $this->getRequestParams($request);
        $researchGroupsInfo = array();
        $fundingOrgInfo = array();
        $statusInfo = array();

        if (!empty($requestParams['query'])) {
            $buildQuery = $searchUtil->buildQuery($requestParams);
            $results = $searchUtil->findDatasets($buildQuery);
            $count = $searchUtil->getCount($buildQuery);
            $researchGroupsInfo = $searchUtil->getResearchGroupAggregations($buildQuery);
            $fundingOrgInfo = $searchUtil->getFundingOrgAggregations($buildQuery);
            $statusInfo = $searchUtil->getStatusAggregations($buildQuery);
            $elasticScoreFirstResult = null;
            if (!empty($results)) {
                $elasticScoreFirstResult = $results[0]->getResult()->getHit()['_score'];
            }
//            $this->dispatchSearchTermsLogEvent($requestParams, $count, $elasticScoreFirstResult);
        }

        return $this->json(
            array(
                'query' => $requestParams['query'],
                'field' => $requestParams['field'],
                'results' => $results,
                'count' => $count,
                'page' => $requestParams['page'],
                'researchGroupsInfo' => $researchGroupsInfo,
                'fundingOrgInfo' => $fundingOrgInfo,
                'statusInfo' => $statusInfo,
                'collectionStartDate' => $requestParams['collectionStartDate'],
                'collectionEndDate' => $requestParams['collectionEndDate'],
            )
        );
    }

    /**
     * Gets the request parameters from the request.
     *
     * @param Request $request The Symfony request object.
     *
     * @return array
     */
    private function getRequestParams(Request $request): array
    {
        return array(
            'query' => $request->get('query'),
            'page' => $request->get('page'),
            'field' => $request->get('field'),
            'collectionStartDate' => $request->get('collectionStartDate'),
            'collectionEndDate' => $request->get('collectionEndDate'),
            'options' => array(
                'rgId' => ($request->get('resGrp')) ? str_replace('rg_', '', $request->get('resGrp')) : null,
                'funOrgId' => ($request->get('fundOrg')) ? str_replace('fo_', '', $request->get('fundOrg')) : null,
                'status' => $request->get('status') ? str_replace('status_', '', $request->get('status')) : null,
            ),
            'sessionId' => $request->getSession()->getId()
        );
    }

    /**
     * This dispatches a search term log event.
     *
     * @param array   $requestParams           The request passed from datasetAction.
     * @param integer $numOfResults            Number of results returned by a search.
     * @param integer $elasticScoreFirstResult Elastic score of the first result.
     *
     * @return void
     */
    protected function dispatchSearchTermsLogEvent(array $requestParams, int $numOfResults, int $elasticScoreFirstResult = null): void
    {
        //get logged in user's id
        $clientInfo = array(
            'sessionId' => $requestParams['sessionId']
        );
        if ($this->getUser() instanceof Account) {
            $clientInfo['userType'] = 'GoMRI';
            $clientInfo['userId'] = $this->getUser()->getUserId();
        } else {
            $clientInfo['userType'] = 'Non-GoMRI';
            $clientInfo['userId'] = 'anonymous';
        }

        //get form inputs and facets
        $searchQueryParams = array(
            'inputFormTerms' => array(
                'searchTerms' => $requestParams['query'] ,
                'specificFieldType' => $requestParams['field'],
                'dataCollectionStartDate' => $requestParams['collectionStartDate'],
                'dataCollectionEndDate' => $requestParams['collectionEndDate'],
            ),
            'aggregations' => array(
                'datasetStatus' => $requestParams['options']['status'],
                'fundingOrganizations' => $requestParams['options']['funOrgId'],
                'researchGroups' => $requestParams['options']['rgId']
            )
        );

        //dispatch the event
        $this->logActionItemEventDispatcher->dispatch(
            array(
                'actionName' => 'New Search',
                'subjectEntityName' => null,
                'subjectEntityId' => null,
                'payLoad' => array(
                    'clientInfo' => $clientInfo,
                    'searchQueryParams' => $searchQueryParams,
                    'numResults' => $numOfResults,
                    'elasticScoreFirstResult' => $elasticScoreFirstResult
                )
            ),
            'search_terms_log'
        );
    }
}
