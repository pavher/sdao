<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 15.12.2018
 */

namespace Pavher\Sdao\Tests;


use Pavher\Sdao\Tests\_files\User;
use PHPUnit\Framework\TestCase;

class DatabaseEntityTest extends TestCase
{
    public function testGetEntityIdName(): void
    {
        $userEntity = new User();

        $this->assertEquals('id_user', $userEntity::getIdName());
    }

    public function testGetEntityId(): void
    {
        $initialData = ['id_user' => '5', 'name' => 'Petr', 'surname' => 'Veliký', 'is_active' => true, 'created' => new \DateTime(), 'default_col' => null];

        $userEntity = new User($initialData, true);

        $this->assertEquals(5, $userEntity->getId());
    }

    public function testGetEntityDatabaseTableName(): void
    {
        $this->assertEquals('user', User::getEntityRelatedTableName());
    }

}