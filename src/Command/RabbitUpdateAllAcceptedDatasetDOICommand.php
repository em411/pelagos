<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use App\Entity\Dataset;
use App\Entity\DIF;

/**
 * This command publishes a rabbit message for every accepted dataset forcing update of DOI info.
 *
 * @see Command
 */
class RabbitUpdateAllAcceptedDatasetDOICommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'dataset-doi:force-doi-update-all';

    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * A Rabbitmq producer instance.
     *
     * @var Producer $producer
     */
    protected $producer;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param Producer               $producer      A Rabbitmq producer instance.
     */
    public function __construct(EntityManagerInterface $entityManager, Producer $producer)
    {
        $this->entityManager = $entityManager;
        $this->producer = $producer;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Force DOI update for all datasets having an accepted submission.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception When dataset not found.
     * @throws \Exception When datasetSubmission not found.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(array(
            'identifiedStatus' => DIF::STATUS_APPROVED));

        foreach ($datasets as $dataset) {
            $this->producer->publish($dataset->getId(), 'update');
            echo 'Requesting DOI update for dataset ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n";
        }

        return 0;
    }
}
