<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$comp = new \Pelagos\Component();

$slim = new \Slim\Slim(
    array(
        'view' => new \Slim\Views\Twig(),
    )
);

require_once "ldap.php";
require_once "lib/PLinker/Storage.php";
require_once "lib/PLinker/Publink.php";

global $quit;
$quit = false;

$slim->get(
    '/',
    function () use ($comp) {
        $GLOBALS['pelagos']['title'] = 'Publink Service';
        print 'This service creates associations between datasets and publications.';
    }
);

$slim->map(
    '/',
    function () use ($comp, $slim) {
        global $quit;
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400, 'No parameters provided');
        $slim->response->headers->set('Content-Type', 'application/json');
        $slim->response->status($HTTPStatus->getCode());
        $slim->response->setBody($HTTPStatus->asJSON());
        return;
    }
)->via('LINK', 'DELETE');

$slim->map(
    '/:udi(/)',
    function ($udi) use ($comp, $slim) {
        global $quit;
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400, 'No DOI provided');
        $slim->response->headers->set('Content-Type', 'application/json');
        $slim->response->status($HTTPStatus->getCode());
        $slim->response->setBody($HTTPStatus->asJSON());
        return;
    }
)->via('LINK', 'DELETE');

$slim->map(
    '/:udi/:doi+',
    function ($udi, $doiArray) use ($comp, $slim) {
        global $user;
        global $quit;
        if (!isset($user->name)) {
            $quit = true;
            $HTTPStatus = new \Pelagos\HTTPStatus(401, 'Login Required to use this feature.');
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        // Check for valid format of UDI.
        if (preg_match('/(?:Y1|R[1-9])\.x\d{3}\.\d{3}:\d{4}/', $udi) == 0) {
            $quit = true;
            $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid UDI format");
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        $doi = join('/', $doiArray);
        // Check for valid format of doi.
        if (preg_match('/^10\..*\/.*$/', $doi) == 0) {
            $quit = true;
            $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid doi format");
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        $publink = new \PLinker\Publink;
        try {
            $publink->createLink($udi, $doi, $user->name);
        } catch (\Exception $ee) {
            $quit = true;
            $code = 0;
            if ($ee->getMessage() == 'Record Does not exist in publication table') {
                $code = 417;
            } elseif ($ee->getMessage() == 'A link has already been established between '
            . 'the given dataset and publication.') {
                $code = 403;
            }
            $HTTPStatus = new \Pelagos\HTTPStatus($code, $ee->getMessage());
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }
        // If successful.
        $HTTPStatus = new \Pelagos\HTTPStatus(
            200,
            "A Link has been successfully created between dataset $udi and publication $doi."
        );
        $slim->response->headers->set('Content-Type', 'application/json');
        $slim->response->status($HTTPStatus->getCode());
        $slim->response->setBody($HTTPStatus->asJSON());

        $quit = true;
    }
)->via('LINK');

$slim->map(
    '/:udi/:doi+',
    function ($udi, $doiArray) use ($comp, $slim) {
        global $user;
        global $quit;
        if (!isset($user->name)) {
            $quit = true;
            $HTTPStatus = new \Pelagos\HTTPStatus(401, 'Login Required to use this feature.');
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        // Check for valid format of UDI.
        if (preg_match('/(?:Y1|R[1-9])\.x\d{3}\.\d{3}:\d{4}/', $udi) == 0) {
            $quit = true;
            $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid UDI format");
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        $doi = join('/', $doiArray);
        // Check for valid format of doi.
        if (preg_match('/^10\..*\/.*$/', $doi) == 0) {
            $quit = true;
            $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid doi format");
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        $publink = new \PLinker\Publink;
        try {
            $publink->removeLink($udi, $doi, $user->name);
        } catch (\PDOException $ee) {
            $quit = true;
            $code = 500;
            $HTTPStatus = new \Pelagos\HTTPStatus($code, $ee->getMessage());
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        } catch (\Exception $ee) {
            $quit = true;
            $code = 0;
            if ($ee->getMessage() == 'A link between the given doi and UDI does not exist.') {
                $code = 417;
            }
            $HTTPStatus = new \Pelagos\HTTPStatus($code, $ee->getMessage());
            $slim->response->headers->set('Content-Type', 'application/json');
            $slim->response->status($HTTPStatus->getCode());
            $slim->response->setBody($HTTPStatus->asJSON());
            return;
        }
        // If successful.
        $HTTPStatus = new \Pelagos\HTTPStatus(
            200,
            "The link between dataset $udi and publication $doi has been removed."
        );
        $slim->response->headers->set('Content-Type', 'application/json');
        $slim->response->status($HTTPStatus->getCode());
        $slim->response->setBody($HTTPStatus->asJSON());

        $quit = true;
    }
)->via('DELETE');

$slim->run();

if ($quit) {
    $comp->quit();
}
