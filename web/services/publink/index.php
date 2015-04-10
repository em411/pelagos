<?php

$comp = new \Pelagos\Component();

require_once "ldap.php";
require_once "lib/Storage.php";
require_once "lib/Publink.php";

global $quit;
$quit = false;

$comp->slim->get('/', function () use ($comp) {
    $GLOBALS['pelagos']['title'] = 'Publink Service';
    print 'This service creates associations between datasets and publications.';
});

$comp->slim->map('/:udi/:doiShoulder/:doiBody(/)', function ($udi, $doiShoulder, $doiBody) use ($comp) {
    global $user;
    global $quit;
    if (!isset($user->name)) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(401,'Login Required to use this feature.');
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    // check for valid format of UDI
    if (preg_match('/(?:Y1|R[1-9])\.x\d{3}\.\d{3}:\d{4}/',$udi) == 0) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400,"Invalid UDI format");
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    $doi = $doiShoulder.'/'.$doiBody;
    // check for valid format of doi
    if (preg_match('/(10[.]\d{4,}[^\s"\/\<\>]*\/[^\s"\<\>]+)/',$doi) == 0) {
        # Thx to Alix Alex for the regexp at:
        # http://stackoverflow.com/questions/27910/finding-a-doi-in-a-document-or-page
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400,"Invalid doi format");
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    $Publink = new \Pelagos\Publink;
    try {
        $Publink->createLink($udi,$doi,getEmployeeNumberFromUID($user->name));
    } catch (\PDOException $ee) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(500,$ee->getMessage());
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }
    # if successful
    $HTTPStatus = new \Pelagos\HTTPStatus(200,"A Link has been successfully created between dataset $udi and publication $doi.");
    $comp->slim->response->headers->set('Content-Type', 'application/json');
    $comp->slim->response->status($HTTPStatus->code);
    $comp->slim->response->setBody($HTTPStatus->asJSON());

    $quit = true;
})->via('LINK','GET');

$comp->slim->run();

if($quit) {
    $comp->quit();
}
