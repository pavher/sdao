<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 16.12.2018
 */

namespace Pavher\Sdao\Database;


use Nette\Database\Context;
use Nette\Database\ResultSet;
use Nette\NotImplementedException;
use Nette\SmartObject;
use Pavher\Sdao\Repository;

abstract class ReadonlyDatabaseRepository extends Repository implements IReadableDatabaseRepository
{
    use SmartObject;

    //<editor-fold desc="Properties">

    /**
     * Database context
     */
    protected $dbContext;

    //</editor-fold>

    //<editor-fold desc="Ctor">

    /**
     * Repository constructor.
     * @param Context $dbContext
     */
    public function __construct(Context $dbContext)
    {
        $this->dbContext = $dbContext;
    }

    //</editor-fold>

    //<editor-fold desc="Methods - abstract">

    /**
     * Get row from result set and create entity.
     * @param ResultSet $resultSet
     * @return mixed (null|IEntity)
     * @throws \RuntimeException
     */

    public function get(ResultSet $resultSet)
    {
        $current = $resultSet->fetch();

        if (!$current) {
            return null;
        }

        $entityClassName = $this->getEntityClassName();
        return new $entityClassName((array)$current, true);
    }

    //</editor-fold>

    //<editor-fold desc="Methods - public">

    /**
     * @param ?array $whereArr Assoc array of conditions [name1 => value1, name2 => value2] ... -> WHERE name1 = value1 AND name2 = value2 ...
     * @param ?array $orderArr Assoc array [orderByField1 => true, orderByField2 => false] ... -> ORDER BY orderByField1 ASC, orderByField2. DESC Default order by primary key.
     * @param int $limit Default 0.
     * @param int $offset Default 1000.
     * @return IDatabaseEntityIterator
     */
    public function getMany(
        ?array $whereArr = null,
        ?array $orderArr = null,
        int $limit = 1000,
        int $offset = 0
    ): IDatabaseEntityIterator {

        if($whereArr !== null && $orderArr !== null) {
            $query = $this->dbContext->query(/** @lang MySQL */ 'SELECT '. $this->getSqlQueryFieldsForSelectItemList()
                . ' FROM ' . $this->getSqlQueryFromClauseForSelect()
                . ' WHERE ?and ORDER BY ?order LIMIT ? OFFSET ?', $whereArr, $orderArr, $limit, $offset);
        }else if($whereArr !== null) {
            $query = $this->dbContext->query(/** @lang MySQL */ 'SELECT '. $this->getSqlQueryFieldsForSelectItemList()
                . ' FROM ' . $this->getSqlQueryFromClauseForSelect()
                . ' WHERE ?and LIMIT ? OFFSET ?', $whereArr, $limit, $offset);
        }else if($orderArr !== null) {
            $query = $this->dbContext->query(/** @lang MySQL */ 'SELECT '. $this->getSqlQueryFieldsForSelectItemList()
                . ' FROM ' . $this->getSqlQueryFromClauseForSelect()
                . ' ORDER BY ?order LIMIT ? OFFSET ?', $orderArr, $limit, $offset);
        } else {
            $query = $this->dbContext->query(/** @lang MySQL */ 'SELECT '. $this->getSqlQueryFieldsForSelectItemList()
                . ' FROM ' . $this->getSqlQueryFromClauseForSelect()
                . ' LIMIT ? OFFSET ?', $limit, $offset);
        }

        $entityIteratorClassName = $this->getEntityIteratorClassName();

        return new $entityIteratorClassName($query, $this);
    }

    /**
     * @param ?array $whereAndArr
     * @return int
     */
    public function getTotal(
        ?array $whereAndArr = null
    ): int {
        if($whereAndArr !== null) {
            return $this->dbContext->query(/** @lang MySQL */ 'SELECT COUNT(*) FROM ' . $this->getSqlQueryFromClauseForSelect() .
                ' WHERE ?and ', $whereAndArr)->fetchField();
        }

        return $this->dbContext->query(/** @lang MySQL */ 'SELECT COUNT(*) FROM ' . $this->getSqlQueryFromClauseForSelect())->fetchField();
    }

    //</editor-fold>

    //<editor-fold desc="Methods - protected">

    /**
     * Returns SELECT ... part of sql query for getting one item.
     * @return string
     */
    protected function getSqlQueryFieldsForSelectOneItem(): string
    {
        return '*';
    }

    /**
     * Returns SELECT ... part of sql query for getting item list.
     * @return string
     */
    protected function getSqlQueryFieldsForSelectItemList(): string
    {
        return $this->getSqlQueryFieldsForSelectOneItem();
    }

    /**
     * Returns FROM ... [JOIN] part of sql query for getting item and item list.
     * @return string
     */
    protected function getSqlQueryFromClauseForSelect(): string
    {
        throw new NotImplementedException('Method getSqlQueryFromClauseForSelect() have to be implemented before calling "get" methods');
    }

    /**
     * Returns primary key for getById method.
     * @return string
     */
    protected function getTablePrimaryKeyNameForDbQuery(): string
    {
        throw new NotImplementedException('Method getTablePrimaryKeyNameForDbQuery() have to be implemented before calling "getById" method');
    }

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
    //</editor-fold>
}