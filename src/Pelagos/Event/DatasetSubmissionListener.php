<?php
namespace Pelagos\Event;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Entity\DatasetSubmission;

/**
 * Listener class for Dataset Submission-related events.
 */
class DatasetSubmissionListener extends EventListener
{
    /**
     * This is the class constructor to handle dependency injections.
     *
     * @param \Twig_Environment $twig         Twig engine.
     * @param \Swift_Mailer     $mailer       Email handling library.
     * @param TokenStorage      $tokenStorage Symfony's token object.
     * @param string            $fromAddress  Sender's email address.
     * @param string            $fromName     Sender's name to include in email.
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TokenStorage $tokenStorage,
        $fromAddress,
        $fromName
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
        $this->from = array($fromAddress => $fromName);
    }

    /**
     * Method to send an email to user and DRPMs on a create event.
     *
     * @param EntityEvent $event Event being acted upon.
     *
     * @return void
     */
    public function onCreated(EntityEvent $event)
    {
        $datasetSubmission = $event->getEntity();

        // email User
        $template = $this->twig->loadTemplate('PelagosAppBundle:Email:registered.email.twig');
        $this->sendMailMsg($template, array('datasetSubmission' => $datasetSubmission));

        // // email Data Managers
        // $template = $this->twig->loadTemplate('@DIFEmail/data-managers/data-managers.dif-submitted.email.twig');
        // $this->sendMailMsg($template, $dif, $this->getDMs($dif));
    }
}
