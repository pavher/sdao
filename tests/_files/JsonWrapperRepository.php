<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-03-16
 */

namespace Pavher\Sdao\Tests\_files;

use Pavher\Sdao\Database\DatabaseRepository;

class JsonWrapperRepository extends DatabaseRepository
{

    //<editor-fold desc="Interface implementation">
    /**
     * @param array|null $initialData
     * @param array|null $allowedKeys
     * @return JsonWrapper
     */

    public function createEntity(?array $initialData = null, ?array $allowedKeys = null): JsonWrapper
    {
        return parent::createEntity($initialData, $allowedKeys);
    }

    //</editor-fold>

    //<editor-fold desc="Abstract methods implementation">

    protected function getEntityClassName(): string
    {
        return JsonWrapper::class;
    }

    //</editor-fold>

    //<editor-fold desc="Methods - public">

    /**
     * @param int $id
     * @return null|JsonWrapper
     * @throws \RuntimeException
     */
    public function getById(int $id): ?JsonWrapper
    {
        return parent::getById($id);
    }

    //</editor-fold>
}