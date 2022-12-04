<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-10-22
 */

namespace Pavher\Sdao;


use Pavher\Sdao\Exceptions\EntityUnrelatedToRepositoryException;

abstract class Repository implements IRepository
{
    //<editor-fold desc="Constants">

    const ENTITY_ITERATOR_CLASS_SUFFIX = "EntityIterator";

    //</editor-fold>


    //<editor-fold desc="Methods - public">

    /**
     * @param array|null $initialData
     * @param array|null $allowedKeys
     * @return mixed
     */
    public function createEntity(?array $initialData = null, ?array $allowedKeys = null)
    {
        if ($allowedKeys !== null) {
            $initialData = array_intersect_key($initialData, array_flip($allowedKeys));
        }

        $entityClassName = $this->getEntityClassName();
        return new $entityClassName($initialData, false);
    }

    /**
     * Validate entity (and throw ValidationException).
     * Use for one entity validation in one request.
     * @param IEntity $entity
     * @throws EntityUnrelatedToRepositoryException
     * @throws Exceptions\ValidationException
     */
    public function validateEntity(IEntity $entity): void
    {
        $entityValidatorAggregate = new EntityValidatorAggregate();
        $this->validateEntityRaw($entity, $entityValidatorAggregate);
        $entityValidatorAggregate->check();
    }

    /**
     * Validate entity (and do not throw ValidationException, update EntityValidatorAggregate instead).
     * Use for several entities validation in one request.
     * @param IEntity $entity
     * @param IEntityValidatorAggregate|null $entityValidatorAggregate
     * @throws EntityUnrelatedToRepositoryException
     */
    public function validateEntityRaw(IEntity $entity, IEntityValidatorAggregate $entityValidatorAggregate): void
    {
        $this->checkIsEntityRelatedToRepository($entity);
        $entityValidator = $entityValidatorAggregate->getEntityValidator($entity);
        $this->setupEntityValidation($entity, $entityValidator);
    }

    //</editor-fold>

    //<editor-fold desc="Methods - protected">

    /**
     * @param IEntity $entity
     * @param IEntityValidatorConfigurable $entityValidator
     */
    protected function setupEntityValidation(IEntity $entity, IEntityValidatorConfigurable $entityValidator): void
    {

    }

    /**
     * @param IEntity $entity
     * @throws EntityUnrelatedToRepositoryException
     */
    protected function checkIsEntityRelatedToRepository(IEntity $entity): void
    {
        if (get_class($entity) !== $this->getEntityClassName()) {
            throw new EntityUnrelatedToRepositoryException(sprintf('Entity must be instance of %s, but %s given.',
                $this->getEntityClassName(), (new \ReflectionClass($entity))->getName()));
        }
    }

    protected function getEntityIteratorClassName(): string
    {
        return $this->getEntityClassName() . self::ENTITY_ITERATOR_CLASS_SUFFIX;
    }

    //</editor-fold>


    //<editor-fold desc="Methods - abstract">

    abstract protected function getEntityClassName(): string;

    //</editor-fold>
}