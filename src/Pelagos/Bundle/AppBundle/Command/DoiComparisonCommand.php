<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;
use Pelagos\Entity\DOI;

/**
 * This Symfony Command compares dois between griidc and datacite.
 *
 * @see ContainerAwareCommand
 */
class DoiComparisonCommand extends ContainerAwareCommand
{
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface fileOutput
     */
    protected $fileOutput = null;

    /**
     * The file output array which stores the data.
     *
     * @var array
     */
    protected $fileOutputArray;

    /**
     * A value for doi state from Datacite.
     */
    const DOI_FINDABLE = 'findable';

    /**
     * A value for doi state from Datacite.
     */
    const DOI_DRAFT = 'draft';

    /**
     * A value for doi state from Datacite.
     */
    const DOI_REGISTERED = 'registered';

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-doi:comparison')
            ->setDescription('DOI comparison tool.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client();
        $response = null;
        $doiJson = array();
        $doiData = array();
        $pageNumber = 1;

        do {
            $url = 'https://api.datacite.org/dois?client-id=tdl.griidc&page%5Bnumber%5D=' . $pageNumber . '&page%5Bsize%5D=1000';
            $body = $this->getRestApiData($client, $url);
            $doiJson[$pageNumber] = $body['data'];
            $pageNumber++;
        } while (array_key_exists('next', $body['links']));

        foreach ($doiJson as $dois) {
            foreach ($dois as $doi) {
                $doiData[$doi['id']] = array(
                    'doi' => $doi['attributes']['doi'],
                    'url' => $doi['attributes']['url'],
                    'udi' => $this->getUdi($doi['attributes']['url']),
                    'title' => str_replace(',', '', $doi['attributes']['titles'][0]['title']),
                    'author' => str_replace(',', '', $doi['attributes']['creators'][0]['name']),
                    'publisher' => $doi['attributes']['publisher'],
                    'pubYear' => $doi['attributes']['publicationYear'],
                    'state' => $doi['attributes']['state'],
                    'resourceType' => $this->getResourceType($doi['attributes']['types'])
                );
            }
        }

        $this->syncConditions($doiData);
    }

    /**
     * Get udi from Url.
     *
     * @param string $url Url that needs to be fetched.
     *
     * @return null
     */
    private function getUdi(string $url)
    {
        $udi = null;
        $udiRegEx = '/\b([A-Z\d]{2}\.x\d\d\d\.\d\d\d:\d\d\d\d)\b/';
        if (preg_match_all($udiRegEx, $url, $matches)) {
            trim(preg_replace($udiRegEx, '', $url));
            $udi = $matches[1][0];
        }

        return $udi;
    }

    /**
     * Get the resource type for the Doi.
     *
     * @param array $types Types of resources from doi.
     *
     * @return string
     */
    private function getResourceType(array $types): string
    {
        $resourceType = '';
        if (array_key_exists('resourceTypeGeneral', $types)) {
            $resourceType = $types['resourceTypeGeneral'];
        } elseif (array_key_exists('resourceType', $types)) {
            $resourceType = $types['resourceType'];
        }

        return $resourceType;
    }

    /**
     * Get a list of dois using Datacite REST API.
     *
     * @param Client $client Guzzle Http client instance.
     * @param string $url    Url that needs to be fetched.
     *
     * @return array
     */
    private function getRestApiData(Client $client, string $url): array
    {
        $header = ['Accept' => 'application/vnd.api+json'];

        try {
            $response = $client->request('get', $url, $header);
        } catch (GuzzleException $exception) {
            echo $exception->getMessage();
        }

        $body = json_decode($response->getBody()->getContents(), true);

        return $body;
    }

    /**
     * Checks sync conditions for Dois.
     *
     * @param array $doiData Dois metadata from Datacite.
     *
     * @return void
     */
    private function syncConditions(array $doiData): void
    {
        $errorDoi = array();
        foreach ($doiData as $doi) {
            if ($doi['udi']) {
                $datasets = $this->getDataset($doi['udi']);
                $dataset = $datasets[0];
                if ($dataset instanceof Dataset) {
                    if (!$this->compareFields($dataset, $doi)) {
                        array_push($errorDoi, $doi['doi']);
                    }
                } else {
                    array_push($errorDoi, $doi['doi']);
                }
            }
        }

        if (!empty($errorDoi)) {
            $this->sendEmail($errorDoi);
        }
    }

    /**
     * Gets a dataset by udi.
     *
     * @param string $udi Identifier used to get a dataset.
     *
     * @return Collection
     */
    private function getDataset(string $udi): array
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array('udi' => array('udi' => substr($udi, 0, 16))));

        return $datasets;
    }

    /**
     * Compares fields of doi metadata from datactie and GRIIDC.
     *
     * @param Dataset $dataset     A dataset instance.
     * @param array   $doiElements Doi metadata elements.
     *
     * @return boolean
     */
    private function compareFields(Dataset $dataset, array $doiElements): bool
    {
        if ($doiElements['title'] !== $dataset->getTitle()) {
            //incorrect title
            return false;
        }

        if ($doiElements['author'] !== $dataset->getAuthors() and $doiElements !== '(:tba)') {
            //incorrect author
            return false;
        }
        // PublicationYear field can not be null, as it is a required field when the DOI is published
        $pubYear = $dataset->getReferenceDateYear();
        if (empty($pubYear) and
            $dataset->getDif()->getApprovedDate() instanceof \Datetime) {
            $pubYear = $dataset->getDif()->getApprovedDate()->format('Y');
        }

        if ($doiElements['pubYear'] !== $pubYear) {
            //incorrect publication year
            return false;
        }

        if ($doiElements['publisher'] !== 'Harte Research Institute') {
            //incorrect publisher
            return false;
        }

        if (!$this->isStateValid($dataset, $doiElements)) {
            //incorrect state/url
            return false;
        }

        return true;
    }

    /**
     * Checks if the doi state is valid.
     *
     * @param Dataset $dataset     A dataset instance.
     * @param array   $doiElements Doi metadata elements.
     *
     * @return boolean
     */
    private function isStateValid(Dataset $dataset, array $doiElements): bool
    {
        $doiStatus = $this->getDoiStatus($doiElements['state']);

        if ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_NONE and $dataset->getIdentifiedStatus() === DIF::STATUS_APPROVED) {
            if ($doiStatus === DOI::STATUS_RESERVED || $doiStatus === DOI::STATUS_UNAVAILABLE) {
                return $this->isUrlValid($doiElements['url'], 'tombstone');
            } else {
                //incorrect state
                return false;
            }
        } elseif ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_NONE) {
            if ($doiStatus === DOI::STATUS_PUBLIC) {
                if ($dataset->isAvailable()) {
                    return $this->isUrlValid($doiElements['url'], 'data');
                } else {
                    return $this->isUrlValid($doiElements['url'], 'tombstone');
                }
            }
        }
    }

    /**
     * Gets the doi status according to griidc system.
     *
     * @param string $state Datacite doi state.
     *
     * @return string
     */
    private function getDoiStatus(string $state): string
    {
        switch (true) {
            case ($state === self::DOI_DRAFT):
                return DOI::STATUS_RESERVED;
                break;
            case ($state === self::DOI_FINDABLE):
                return DOI::STATUS_PUBLIC;
                break;
            case ($state === self::DOI_REGISTERED):
                return DOI::STATUS_UNAVAILABLE;
                break;
        }
    }

    /**
     * Check if the url is valid.
     *
     * @param string $url    The haystack string.
     * @param string $needle Needle to search the string.
     *
     * @return boolean
     */
    private function isUrlValid(string $url, string $needle): bool
    {
        if (strpos($url, $needle) !== false) {
            return true;
        } else {
            // incorrect url
            return false;
        }
    }

    /**
     * To send an email.
     *
     * @param array $errorDoi List of dois which are out of sync.
     *
     * @return void
     */
    private function sendEmail(array $errorDoi): void
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('DOI Sync Log - List of Dois which are out of sync')
            ->setFrom(array('griidc@gomri.org' => 'GRIIDC'))
            ->setTo(array('griidc@gomri.org' => 'GRIIDC'))
            ->setCharset('UTF-8')
            ->setBody($this->getContainer()->get('templating')->render(
                'PelagosAppBundle:Email:data-repository-managers.error-remotely-hosted.email.twig',
                array('listOfDoi' => $errorDoi)
            ), 'text/html');
        $this->getContainer()->get('mailer')->send($message);
    }
}
