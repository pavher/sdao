<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2.12.2018
 */

namespace Pavher\Sdao\Database;

use Nette\SmartObject;
use Pavher\Sdao\Exceptions\EntityUnrelatedToRepositoryException;

abstract class DatabaseRepository extends ReadonlyDatabaseRepository implements IWritableDatabaseRepository
{
    use SmartObject;

    //<editor-fold desc="Methods - public">

    /**
     * @param int $id
     * @return mixed|null
     * @throws \RuntimeException
     */
    public function getById(int $id)
    {
        return $this->get($this->dbContext->query(
            'SELECT ' . $this->getSqlQueryFieldsForSelectOneItem()
            . ' FROM ' . $this->getSqlQueryFromClauseForSelect()
            . ' WHERE ' . $this->getTablePrimaryKeyNameForDbQuery()
            . ' = ?',
            $id));
    }

    /**
     * Save entity to database.
     * @param IPersistableDatabaseEntity $entity
     * @throws EntityUnrelatedToRepositoryException
     * @throws \Pavher\Sdao\Exceptions\ValidationException
     */
    public function save(IPersistableDatabaseEntity &$entity): void
    {
        $this->checkIsEntityRelatedToRepository($entity);

        $this->beforeValidate($entity);

        $this->validateEntity($entity);

        $this->beforeSave($entity);

        if ($entity->isNewRecord()) {

            // insert new entity and update entity (clone it and fill in primary key)
            $this->dbContext->query('INSERT INTO ' . $this->getTableNameForDbQuery() . ' ?',
                $entity->asArray(null, true, true));

            $entityClassName = get_class($entity);
            $entity = new $entityClassName(array_merge($entity->asArray(null, true, true),
                [$this->getEntityTablePrimaryKeyName() => $this->dbContext->getInsertId()]), false);

        } elseif ($entity->isChanged()) {
            $this->dbContext->query('UPDATE ' . $this->getTableNameForDbQuery()  . ' SET',
                $entity->asArray(null, true, true), 'WHERE ' . $this->getTablePrimaryKeyNameForDbQuery() . ' = ?',
                $entity->getId());
        }
    }

    /**
     * Delete entity.
     * @param IPersistableDatabaseEntity $entity
     * @throws EntityUnrelatedToRepositoryException
     */
    public function delete(IPersistableDatabaseEntity $entity): void
    {
        $this->checkIsEntityRelatedToRepository($entity);
        $this->deleteById($entity->getId());
    }

    /**
     * Delete entity by id.
     * @param int $id
     */
    public function deleteById(int $id): void
    {
        $this->dbContext->query('DELETE FROM ' . $this->getTableNameForDbQuery() . ' WHERE ' . $this->getTablePrimaryKeyNameForDbQuery() . ' = ?',
            $id);
    }

    /**
     * Delete several entities by id.
     * @param array $ids
     */
    public function deleteManyByIds(array $ids): void
    {
        $this->dbContext->query('DELETE FROM ' . $this->getTableNameForDbQuery() . ' WHERE ' . $this->getTablePrimaryKeyNameForDbQuery() . ' IN (?)',
            $ids);
    }

    //</editor-fold>


    //<editor-fold desc="Abstract methods implementation">

    protected function getSqlQueryFromClauseForSelect(): string
    {
        return $this->getTableNameForDbQuery();
    }

    //</editor-fold>

    //<editor-fold desc="Methods - protected">

    /**
     * @return string
     */
    protected function getEntityTableName(): string
    {
        $entityClassName = $this->getEntityClassName();

        /** @var IPersistableDatabaseEntity $entityClassName */
        return $entityClassName::getEntityRelatedTableName();
    }

    /**
     * @return string
     */
    protected function getTableNameForDbQuery(): string
    {
        return sprintf('`%s`', $this->getEntityTableName());
    }

    /**
     * @return string
     */
    protected function getEntityTablePrimaryKeyName(): string
    {
        $entityClassName = $this->getEntityClassName();

        /** @var IPersistableDatabaseEntity $entityClassName */
        return $entityClassName::getIdName();
    }

    protected function getTablePrimaryKeyNameForDbQuery(): string
    {
        return sprintf('`%s`', $this->getEntityTablePrimaryKeyName());
    }

    /**
     * @param IPersistableDatabaseEntity $entity
     */
    protected function beforeValidate($entity): void
    {
    }

    /**
     * @param IPersistableDatabaseEntity $entity
     */
    protected function beforeSave($entity): void
    {
    }
    
    //</editor-fold>

}