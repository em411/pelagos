<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\FinderInterface;

class Search
{
    /**
     * The entity manager to use.
     *
     * @var EntityManager
     */
    protected $finder;

    /**
     * Constructor.
     *
     * @param FinderInterface $finder The finder interface object.
     */
    public function __construct(FinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Search Query using Fos Elastic search.
     *
     * @param string $queryTerm Query string.
     *
     * @return array
     */
    public function findDatasets(string $queryTerm): array
    {
        $boolQuery = new \Elastica\Query\BoolQuery();
        $titleQuery = new \Elastica\Query\Match();
        $titleQuery->setFieldQuery('title', $queryTerm);
        $boolQuery->addShould($titleQuery);
        $results = $this->finder->find($boolQuery, '20');

        return $results;
    }
}
