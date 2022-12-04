<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 7.12.2018
 */

namespace Pavher\Sdao\Tests\_files;


use Pavher\Sdao\Database\DatabaseRepository;

class UserRepository extends DatabaseRepository
{

    //<editor-fold desc="Interface implementation">
    /**
     * @param array|null $initialData
     * @param array|null $allowedKeys
     * @return User
     */

    public function createEntity(?array $initialData = null, ?array $allowedKeys = null): User
    {
        return parent::createEntity($initialData, $allowedKeys);
    }

    //</editor-fold>

    //<editor-fold desc="Abstract methods implementation">

    protected function getEntityClassName(): string
    {
        return User::class;
    }

    //</editor-fold>

    //<editor-fold desc="Methods - public">

    /**
     * @param int $id
     * @return null|User
     * @throws \RuntimeException
     */
    public function getById(int $id): ?User
    {
        return parent::getById($id);
    }

    /**
     * @return UserEntityIterator
     */
    public function getAllInactiveUsers(): UserEntityIterator
    {
        $query = $this->dbContext->query('SELECT * FROM ' . $this->getEntityTableName() . ' WHERE is_active = 0');
        return new UserEntityIterator($query, $this);
    }

    /**
     * @return UserEntityIterator
     */
    public function getNoUsers(): UserEntityIterator
    {
        $query = $this->dbContext->query('SELECT * FROM ' . $this->getEntityTableName() . ' WHERE surname = "unknown surname"');
        return new UserEntityIterator($query, $this);
    }

    //</editor-fold>

}