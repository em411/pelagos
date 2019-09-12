<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;


use Pelagos\Bundle\AppBundle\Form\LoginForm;

use Pelagos\Entity\Account;
use Pelagos\Entity\LoginAttempts;
use Pelagos\Entity\Password;
use Pelagos\Entity\Person;

/**
 * The login form authenticator.
 *
 * @see AbstractFormLoginAuthenticator
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * An instance of FormFactory.
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * An instance of a Doctrine EntityManager class.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * An instance of Router.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * A Monolog logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * String describing max PW age.
     *
     * @var string maxPwAge
     */
    protected $maxPwAge;

    /**
     * Class constructor for Dependency Injection.
     *
     * @param FormFactoryInterface   $formFactory   A Form Factory.
     * @param EntityManagerInterface $entityManager An Entity Manager.
     * @param RouterInterface        $router        A Router.
     * @param LoggerInterface        $logger        A Monolog logger.
     * @param string                 $maxPwAge      The max age for password parameter.
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        LoggerInterface $logger,
        string $maxPwAge
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->logger = $logger;
        $this->maxPwAge = $maxPwAge;
    }

    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return bool True if this a login request.
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'security_login' && $request->isMethod('POST');
    }

    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return array Return the credentials array.
     */
    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->createNamed(null, LoginForm::class);
        $form->handleRequest($request);

        $data = $form->getData();

        $this->logAttempt($request);

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['_username']
        );

        return $data;
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * @param array                 $credentials  Credentials Array.
     * @param UserProviderInterface $userProvider A User Provider.
     *
     * @throws AuthenticationException When login is invalid.
     *
     * @return UserInterface Return the user.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        $theUser = $this->entityManager->getRepository(Account::class)
            ->findOneBy(['userId' => $username]);

        if (null == $theUser) {
            throw new AuthenticationException('Invalid Credentials');
        }

        return $theUser;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * @param array         $credentials Credentials Array.
     * @param UserInterface $user        The user.
     *
     * @throws AuthenticationException When account is locked out.
     * @throws AuthenticationException When this is a bad password.
     * @throws AuthenticationException When this is an expired password.
     *
     * @return bool True if the credentials are valid.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // Here check to see if $user is locked out?
        if ($user->isLockedOut()) {
            throw new AuthenticationException('Too many login attempts');
        }

        // Check for expired password.
        $now = new \DateTime('now');
        $expiration = $user->getPasswordEntity()->getModificationTimeStamp()->add(new \DateInterval($this->maxPwAge));
        // If today is past the expiration date, the milk is sour.
        if ($now > $expiration) {
            throw new AuthenticationException('Password is expired.');
        }

        $this->userAttempt($user);

        $password = $credentials['_password'];
        if ($user->getPasswordEntity()->comparePassword($password)) {
            return true;
        } else {
            throw new AuthenticationException('Invalid Credentials');
        }
    }

    /**
     * Return the URL to the login page.
     *
     * @return string The login page route.
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    /**
     * Return the URL to the home page.
     *
     * @return string The home page route.
     */
    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('pelagos_homepage');
    }

    /**
     * Set a cookie and return the response to the target page.
     *
     * @param Request        $request     A Symfony Request, req by interface.
     * @param TokenInterface $token       A Symfony user token, req by interface.
     * @param string         $providerKey The name of the used firewall key.
     *
     * @return Response The response or null to continue request.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $destination = $request->query->get('destination');

        $session = $request->getSession();
        $targetPath = $this->getTargetPath($session, $providerKey);

        if (!isset($targetPath) and !empty($destination)) {
            $targetPath = $destination;
        } elseif (!isset($targetPath)) {
            $targetPath = $this->router->generate('pelagos_homepage');
        }

        $response = new RedirectResponse($targetPath);

        $cookie = new Cookie('GRIIDC_USERNAME', $token->getUser()->getUserId());
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Return to login page and add destination for redirect.
     *
     * @param Request                 $request   A Symfony Request, req by interface.
     * @param AuthenticationException $exception The exception thrown.
     *
     * @return Response The response or null to continue request.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $destination = $request->query->get('destination');
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        $url = $this->router->generate(
            'security_login',
            ['destination' => $destination]
        );
        return new RedirectResponse($url);
    }

    /**
     * Logs the attemps to log.
     *
     * @param Request $request The home directory.
     *
     * @return void
     */
    private function logAttempt(Request $request)
    {
        $loggingContext = array(
            'ipAddress' => $request->getClientIp(),
            'userName' => $request->request->get('_username'),
            'user-agent' => $request->headers->get('User-Agent'),
        );
        $this->logger->info('Login Attempt:', $loggingContext);
    }

    /**
     * Log the attempt in loginAttemps.
     *
     * @param UserInterface $user The home directory.
     *
     * @return void
     */
    private function userAttempt(UserInterface $user)
    {
        $anonymousPerson = $this->entityManager->find(Person::class, -1);

        $loginAttempt = new LoginAttempts($user);
        $loginAttempt->setCreator($anonymousPerson);
        $this->entityManager->persist($loginAttempt);
        $this->entityManager->flush($loginAttempt);
    }
}
