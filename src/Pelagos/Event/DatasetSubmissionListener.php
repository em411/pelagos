<?php
namespace Pelagos\Event;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * Listener class for Dataset Submission-related events.
 */
class DatasetSubmissionListener extends EventListener
{
    /**
     * Method to send an email to user on a create event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onCreated(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-created.email.twig');
        $this->sendMailMsg($template, array('datasetSubmission' => $datasetSubmission));
    }

    /**
     * Method to send an email to user and DRPMs on a submitted event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onSubmitted(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-submitted.email.twig');
        $this->sendMailMsg($template, array('dataset' => $dataset), $this->getDMs($dataset));
    }

    /**
     * Method to send an email to DMs on a updated event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onResubmitted(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();
        $dataset = $datasetSubmission->getDataset();

        // email DM
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:data-managers.dataset-updated.email.twig');
        $this->sendMailMsg($template, array('dataset' => $dataset), $this->getDMs($dataset));
    }

    /**
     * Method to send an email to user on a dataset_processed event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onDatasetProcessed(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email creator
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.dataset-processed.email.twig');
        $this->sendMailMsg(
            $template,
            array('datasetSubmission' => $datasetSubmission),
            array($datasetSubmission->getCreator())
        );
        $transport = $this->mailer->getTransport();
        $spool = $transport->getSpool();
        $spool->flushQueue($this->mailerTransportReal);
    }

    /**
     * Method to send an email to user on a metadata_processed event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onMetadataProcessed(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email creator
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:user.metadata-processed.email.twig');
        $this->sendMailMsg(
            $template,
            array('datasetSubmission' => $datasetSubmission),
            array($datasetSubmission->getCreator())
        );
        $transport = $this->mailer->getTransport();
        $spool = $transport->getSpool();
        $spool->flushQueue($this->mailerTransportReal);
    }
}
