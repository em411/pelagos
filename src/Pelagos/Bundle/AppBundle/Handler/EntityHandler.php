<?php

namespace Pelagos\Bundle\AppBundle\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\Common\Collections\Collection;

use Pelagos\Entity\Entity;
use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Exception\UnmappedPropertyException;
use Pelagos\Bundle\AppBundle\Security\PelagosEntityVoter;
use Pelagos\Bundle\AppBundle\Security\EntityProperty;

/**
 * A handler for entities.
 */
class EntityHandler
{
    /**
     * The entity manager to use in this entity handler.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * The token storage to use in this entity handler.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * The authorization checker to use in this entity handler.
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * Constructor for EntityHandler.
     *
     * @param EntityManager                 $entityManager        The entity manager to use.
     * @param TokenStorageInterface         $tokenStorage         The token storage to use.
     * @param AuthorizationCheckerInterface $authorizationChecker The authorization checker to use.
     */
    public function __construct(
        EntityManager $entityManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Return an entity of $entityClass identified by $id.
     *
     * @param string  $entityClass The type of entity to retrieve.
     * @param integer $id          The id of the entity to retrieve.
     *
     * @return Entity|null The entity.
     */
    public function get($entityClass, $id)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->find($id);
    }

    /**
     * Return all entities of $entityClass.
     *
     * @param string $entityClass The type of entity to retrieve.
     *
     * @return Collection A collection of entities.
     */
    public function getAll($entityClass)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->findAll();
    }

    /**
     * Return all entities of $entityClass filtered by $criteria and sorted by $orderBy.
     *
     * @param string     $entityClass The type of entity to retrieve.
     * @param array      $criteria    The criteria to filter by.
     * @param array|null $orderBy     The properties to sort by.
     *
     * @return Collection A collection of entities.
     */
    public function getBy($entityClass, array $criteria, $orderBy = null)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->findBy($criteria, $orderBy);
    }

    /**
     * Create a new entity.
     *
     * @param Entity $entity The entity to create.
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to create the entity.
     *
     * @return Entity The new entity.
     */
    public function create(Entity $entity)
    {
        if (!$this->authorizationChecker->isGranted('CAN_CREATE', $entity)) {
            throw new AccessDeniedException(
                'You do not have sufficient privileges to create this ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        // Set the creator to the currently authenticated user.
        $entity->setCreator($this->getAuthenticatedPerson());
        // Get the id.
        $id = $entity->getId();
        // Get the class metadata for this entity.
        $metadata = $this->entityManager->getClassMetaData(get_class($entity));
        // Save the original ID generator.
        $idGenerator = $metadata->idGenerator;
        // If the entity has been manually assigned an ID.
        if ($id !== null) {
            // Temporarily change the ID generator to AssignedGenerator.
            $metadata->setIdGenerator(new AssignedGenerator());
        }
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        // If the entity has been manually assigned an ID.
        if ($id !== null) {
            // Restore the original ID generator for entities of this class.
            $metadata->setIdGenerator($idGenerator);
        }
        return $entity;
    }

    /**
     * Update an entity.
     *
     * @param Entity $entity The entity to update.
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to update the entity.
     *
     * @return Entity The updated entity.
     */
    public function update(Entity $entity)
    {
        if (!$this->authorizationChecker->isGranted(PelagosEntityVoter::CAN_EDIT, $entity)) {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($entity);
            foreach (array_keys($changeSet) as $property) {
                $entityProperty = new EntityProperty($entity, $property);
                if (!$this->authorizationChecker->isGranted(PelagosEntityVoter::CAN_EDIT, $entityProperty)) {
                    throw new AccessDeniedException(
                        'You do not have sufficient privileges to edit this ' . $entity::FRIENDLY_NAME . '.'
                    );
                }
            }
        }
        // Set the modifier to the currently authenticated user.
        $entity->setModifier($this->getAuthenticatedPerson());
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        return $entity;
    }

    /**
     * Delete an entity.
     *
     * @param Entity $entity The entity object to delete.
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to delete the entity.
     *
     * @return Entity The entity object that was deleted.
     */
    public function delete(Entity $entity)
    {
        if (!$this->authorizationChecker->isGranted(PelagosEntityVoter::CAN_DELETE, $entity)) {
            throw new AccessDeniedException(
                'You do not have sufficient privileges to delete this ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        return $entity;
    }

    /**
     * Get a list of all distinct values for a property of a given Entity class.
     *
     * @param string $entityClass Entity class to get distinct values from.
     * @param string $property    Property to get distinct values of.
     *
     * @throws AccessDeniedException     When the user does not have sufficient privileges to get
     *                                   a list of distinct values for properties of the entity.
     * @throws UnmappedPropertyException When Entity $entityClass does not have a mapped property $property.
     *
     * @return array List of all distinct values for $property for $entityClass.
     */
    public function getDistinctVals($entityClass, $property)
    {
        $entity = new $entityClass;
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException(
                'Only authenticated users may retrieve a list of distinct values ' .
                "for property $property of " . $entity::FRIENDLY_NAME . '.'
            );
        }
        $class = $this->entityManager->getClassMetadata($entityClass);
        if (!$class->hasField($property) && !$class->hasAssociation($property)) {
            $exception = new UnmappedPropertyException;
            $exception->setClassName($entityClass);
            $exception->setPropertyName($property);
            throw $exception;
        }
        $this->entityManager
            ->getConfiguration()
            ->addCustomHydrationMode(
                'COLUMN_HYDRATOR',
                'Pelagos\DoctrineExtensions\Hydrators\ColumnHydrator'
            );
        // Get distinct vals
        $query = $this->entityManager
            ->getRepository($entityClass)
            ->createQueryBuilder('entity')
            ->select("entity.$property")
            ->where("entity.$property IS NOT NULL")
            ->distinct()
            ->orderBy("entity.$property")
            ->getQuery();
        return $query->getResult('COLUMN_HYDRATOR');
    }

    /**
     * Get the currently authenticated Person.
     *
     * @return Person The currently authenticated Person.
     */
    protected function getAuthenticatedPerson()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        // If user is authenticated.
        if ($user instanceof Account) {
            // Return the authenticated person.
            return $user->getPerson();
        }
        // Return the anonymous person by default.
        return $this->get(Person::class, -1);
    }
}
