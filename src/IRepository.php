<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Pavel Herink
 * Date: 2020-09-06
 */

namespace Pavher\Sdao;

use Pavher\Sdao\Exceptions\EntityUnrelatedToRepositoryException;

interface IRepository
{
    /**
     * Create empty entity.
     * @param array|null $initialData
     * @param array|null $allowedKeys
     * @return mixed
     */
    public function createEntity(?array $initialData = null, ?array $allowedKeys = null);

    /**
     * Validate entity (and throw ValidationException).
     * Use for one entity validation in one request.
     * @param IEntity $entity
     * @throws EntityUnrelatedToRepositoryException
     * @throws Exceptions\ValidationException
     */
    public function validateEntity(IEntity $entity): void;

    /**
     * Validate entity (and do not throw ValidationException, update IEntityValidatorAggregate instead).
     * Use for several entities validation in one request.
     * @param IEntity $entity
     * @param IEntityValidatorAggregate|null $entityValidatorAggregate
     * @throws EntityUnrelatedToRepositoryException
     */
    public function validateEntityRaw(IEntity $entity, IEntityValidatorAggregate $entityValidatorAggregate): void;
}