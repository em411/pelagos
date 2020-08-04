<?php

namespace App\Controller\UI;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

use App\Twig\Extensions as TwigExtentions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Entity\Person;
use App\Entity\ResearchGroup;

/**
 * The Dataset Monitoring controller.
 */
class StatsController extends AbstractController
{
    /**
     * The Entity Manager.
     *
     * @var entityManager
     */
    protected $entityManager;

    /**
     * Class constructor.
     *
     * @param EntityManagerInterface $em A Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * Statistics Page.
     *
     * @Route("/stats", name="pelagos_app_ui_stats_default")
     *
     * @return Response
     */
    public function defaultAction()
    {
        $this->getStatistics($totalDatasets, $totalSize, $peopleCount, $researchGroupCount);

        return $this->render(
            'Stats/index.html.twig',
            $twigData = array(
                'datasets' => $totalDatasets,
                'totalsize' => $totalSize,
                'people' => $peopleCount,
                'researchGroups' => $researchGroupCount,
            )
        );
    }

    /**
     * Get Statistics Data by reference.
     *
     * @param integer|null $totalDatasets      The total count of datasets.
     * @param string|null  $totalSize          The total size of data.
     * @param integer|null $peopleCount        The total count of people.
     * @param integer|null $researchGroupCount The total count of research groups.
     *
     * @return void
     */
    private function getStatistics(?int &$totalDatasets, ?string &$totalSize, ?int &$peopleCount, ?int &$researchGroupCount) : void
    {
        // Get the people count.
        $peopleCount = $this->entityManager
            ->getRepository(Person::class)
            ->countPeople();

        // Get the research group count.
        $researchGroupCount = $this->entityManager
            ->getRepository(ResearchGroup::class)
            ->countResearchGroups();

        $datasetRespository = $this->entityManager->getRepository(Dataset::class);

        $totalDatasets = $datasetRespository->countRegistered();
        $totalSize = $datasetRespository->totalDatasetSize();
    }

    /**
     * Get Statistics Data as JSON.
     *
     * @Route("/stats/json", name="pelagos_app_ui_stats_getstatisticsjson")
     *
     * @return Response
     */
    public function getStatisticsJson()
    {
        $this->getStatistics($totalDatasets, $totalSize, $peopleCount, $researchGroupCount);

        $result = array();
        $result['totalDatasets'] = $totalDatasets;
        $result['totalSize'] = TwigExtentions::formatBytes($totalSize, 1);
        $result['peopleCount'] = $peopleCount;
        $result['researchGroupCount'] = $researchGroupCount;

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * JSON data for Datasets over Time.
     *
     * @Route("/stats/data/total-records-over-time", name="pelagos_app_ui_stats_getdatasetovertime")
     *
     * @return Response
     */
    public function getDatasetOverTimeAction()
    {
        $registeredDatasets = $this->entityManager
            ->getRepository(DatasetSubmission::class)
            ->getRegisteredDatasets();
        
        $availableDatasets = $this->entityManager
            ->getRepository(DatasetSubmission::class)
            ->getAvailableDatasets();

        $registered = array();
        foreach ($registeredDatasets as $index => $value) {
            $registered[] = array('date' => ($value['creationTimeStamp']->format('Y/m/d')), 'registered' => ($index + 1));
        }

        $available = array();
        foreach ($availableDatasets as $index => $value) {
            $available[] = array('date' => ($value['creationTimeStamp']->format('Y/m/d')), 'available' => ($index + 1));
        }

        $result = array();

        $result = array_merge($available, $registered);
        sort($result);

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * JSON data for Dataset Size Ranges.
     *
     * @Route("/stats/data/dataset-size-ranges", name="pelagos_app_ui_stats_getdatasetsizeranges")
     *
     * @return Response
     */
    public function getDatasetSizeRangesAction()
    {
        $dataSizes = array(
            'KB' => 1000,
            'MB' => pow(1000, 2),
            'GB' => pow(1000, 3),
            'TB' => pow(1000, 4)
        );

        $dataSizeRanges = array(
            array(
                'label' => '< 1 MB',
                'range1' => ($dataSizes['MB'])
            ),
            array(
                'label' => '1 MB - 100 MB',
                'range0' => ($dataSizes['MB']),
                'range1' => ($dataSizes['MB'] * 100)
            ),
            array(
                'label' => '100 MB - 1 GB',
                'range0' => ($dataSizes['MB'] * 100),
                'range1' => ($dataSizes['GB'])
            ),
            array(
                'label' => '1 GB - 100 GB',
                'range0' => ($dataSizes['GB']),
                'range1' => ($dataSizes['GB'] * 100)
            ),
            array(
                'label' => '100 GB - 1 TB',
                'range0' => ($dataSizes['GB'] * 100),
                'range1' => ($dataSizes['TB'])
            ),
            array(
                'label' => '> 1 TB',
                'range0' => ($dataSizes['TB'])
            )
        );

        $repository = $this->entityManager
            ->getRepository(Dataset::class);

        $dataSizes = array();

        foreach ($dataSizeRanges as $index => $range) {
            $lower =  array_key_exists('range0', $range) ? $range['range0'] : null;
            $upper =  array_key_exists('range1', $range) ? $range['range1'] : null;

            $datasetCount = $repository->getDatasetByFileSizeRange($lower, $upper);

            $dataSizes[] = array(
                'label' => $range['label'],
                'count' => $datasetCount,
            );
        }

        $datasetSizeRanges = $dataSizes;

        $response = new Response(json_encode($datasetSizeRanges));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
