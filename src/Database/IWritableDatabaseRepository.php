<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Pavel Herink
 * Date: 2020-09-06
 */

namespace Pavher\Sdao\Database;

use Pavher\Sdao\Exceptions\EntityUnrelatedToRepositoryException;

interface IWritableDatabaseRepository extends IReadableDatabaseRepository
{
    /**
     * @param int $id
     * @return mixed|null
     * @throws \RuntimeException
     */
    public function getById(int $id);

    /**
     * @param ?array $whereArr Assoc array of conditions [name1 => value1, name2 => value2] ... -> WHERE name1 = value1 AND name2 = value2 ...
     * @param ?array $orderArr Assoc array [orderByField1 => true, orderByField2 => false] ... -> ORDER BY orderByField1 ASC, orderByField2 DESC
     * @param int $limit Default 0.
     * @param int $offset Default 1000.
     * @return IDatabaseEntityIterator
     */
    public function getMany(
        ?array $whereArr = null,
        ?array $orderArr = null,
        int $limit = 1000,
        int $offset = 0
    ): IDatabaseEntityIterator;

    /**
     * @param array $whereAndArr
     * @return int
     */
    public function getTotal(
        array $whereAndArr
    ): int;

    /**
     * Save entity to database.
     * @param IPersistableDatabaseEntity $entity
     * @throws EntityUnrelatedToRepositoryException
     * @throws \Pavher\Sdao\Exceptions\ValidationException
     */
    public function save(IPersistableDatabaseEntity &$entity): void;

    /**
     * Delete entity.
     * @param IPersistableDatabaseEntity $entity
     * @throws EntityUnrelatedToRepositoryException
     */
    public function delete(IPersistableDatabaseEntity $entity): void;

    /**
     * Delete entity by id.
     * @param int $id
     */
    public function deleteById(int $id): void;

    /**
     * Delete several entities by id.
     * @param array $ids
     */
    public function deleteManyByIds(array $ids): void;
}