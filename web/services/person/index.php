<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use \Pelagos\HTTPStatus;
use \Pelagos\Entity\Person;
use \Symfony\Component\Validator\Validation;
use \Pelagos\Exception\ValidationException;
use \Pelagos\Exception\ArgumentException;
use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Pelagos\Exception\MissingRequiredFieldPersistenceException;
use \Pelagos\Exception\RecordExistsPersistenceException;
use \Pelagos\Exception\RecordNotFoundPersistenceException;
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
            $person = new Person();
            $person = $comp->persist(
                $comp->validate(
                    $person->update($slim->request->params()),
                    Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator()
                )
            );
            $status = new HTTPStatus(
                201,
                sprintf(
                    'A person has been successfully created: %s %s (%s) with at ID of %d.',
                    $person->getFirstName(),
                    $person->getLastName(),
                    $person->getEmailAddress(),
                    $person->getId()
                )
            );
            $response->headers->set('Location', $comp->getUri() . '/' . $person->getId());
        } catch (ValidationException $e) {
            $violations = array();
            foreach ($e->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $status = new HTTPStatus(
                400,
                'Cannot create person because: ' . join(', ', $violations)
            );
        } catch (MissingRequiredFieldPersistenceException $e) {
            $status = new HTTPStatus(400, 'Cannot create person because a required field is missing.');
        } catch (RecordExistsPersistenceException $e) {
            $status = new HTTPStatus(409, 'Cannot create person: ' . $e->getDatabaseErrorMessage());
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->get(
    '/:id',
    function ($id) use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);
        try {
            $person = $comp->getPerson($id);
            $status = new HTTPStatus(200, "Found Person with id: $id", $person);
        } catch (ArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->put(
    '/:id',
    function ($id) use ($comp, $slim) {
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
            $person = $comp->persist(
                $comp->validate(
                    $comp->getPerson($id)->update($slim->request->params()),
                    Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator()
                )
            );
            $status = new HTTPStatus(200, "Updated Person with id: $id", $person);
        } catch (ArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (ValidationException $e) {
            $violations = array();
            foreach ($e->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $status = new HTTPStatus(
                400,
                'Cannot update person because: ' . join(', ', $violations)
            );
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
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
