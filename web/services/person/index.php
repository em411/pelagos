<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use \Pelagos\HTTPStatus;
use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Pelagos\Exception\MissingRequiredFieldPersistenceException;
use \Pelagos\Exception\RecordExistsPersistenceException;
use \Pelagos\Exception\PersistenceException;

$comp = new \Pelagos\Component\PersonService();

$slim = new \Slim\Slim();

$slim->get(
    '/',
    function () use ($slim) {
        $GLOBALS['pelagos']['title'] = 'Person Web Service';
        return $slim->render('html/index.html');
    }
);

$slim->post(
    '/',
    function () use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);

        // Check to see that user is logged in.
        // THIS IS AN INSUFFICIENT SECURITY CHECK, THIS WILL
        // HAVE TO BE TIED TO SOME SORT OF ACCESS LIST WHEN
        // RELEASED.
        if (!$comp->userIsLoggedIn()) {
            $status = new HTTPStatus(401, 'Login Required to use this feature');
            $response->status($status->getCode());
            $response->body(json_encode($status));
            return;
        }

        try {
            $person = $comp->createPerson(
                $slim->request->post('firstName'),
                $slim->request->post('lastName'),
                $slim->request->post('emailAddress')
            );
            $status = new HTTPStatus(
                200,
                sprintf(
                    'A person has been successfully created: %s %s (%s) with at ID of %d.',
                    $person->getFirstName(),
                    $person->getLastName(),
                    $person->getEmailAddress(),
                    $person->getId()
                )
            );
        } catch (EmptyRequiredArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (InvalidFormatArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (MissingRequiredFieldPersistenceException $e) {
            $status = new HTTPStatus(400, 'A required field is missing: ' . $e->getDatabaseErrorHint());
        } catch (RecordExistsPersistenceException $e) {
            $status = new HTTPStatus(409, 'This record already exists.');
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->run();
$comp->finalize();
