<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 16.1.2019
 */

namespace Pavher\Sdao\Tests;


use Pavher\Sdao\Tests\_files\UserRepository;
use PHPUnit\Framework\TestCase;

class DatabaseEntityIteratorTest extends DatabaseTestParent
{
    public static function setUpBeforeClass(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUpBeforeClass();
        $userRepository = new UserRepository(static::$dbContext);

        $user1 = $userRepository->createEntity();
        $user1->name = 'Jan';
        $user1->surname = 'Novák';
        $user1->created = new \DateTime('2019-01-15');
        $user1->is_active = false;
        $userRepository->save($user1);

        $user2 = $userRepository->createEntity();
        $user2->name = 'Petr';
        $user2->surname = 'Svoboda';
        $user2->created = new \DateTime('2019-01-15');
        $user2->is_active = true;
        $userRepository->save($user2);

        $user3 = $userRepository->createEntity();
        $user3->name = 'Tomáš';
        $user3->surname = 'Novotný';
        $user3->created = new \DateTime('2019-01-15');
        $user3->is_active = false;
        $userRepository->save($user3);
    }

    public function testGetEntitiesByQuery(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $userEntityIterator = $userRepository->getAllInactiveUsers();

        $this->assertCount(2, $userEntityIterator);

        $resultArray = [];
        foreach($userEntityIterator as $userEntity) {
            $resultArray[] = $userEntity->asArray();
        }

        $this->assertEquals([
            [
                'id_user' => 1,
                'name' => 'Jan',
                'surname' => 'Novák',
                'is_active' => false,
                'created' => new \DateTime('2019-01-15'),
                'default_col' => 105
            ],[
                'id_user' => 3,
                'name' => 'Tomáš',
                'surname' => 'Novotný',
                'is_active' => false,
                'created' => new \DateTime('2019-01-15'),
                'default_col' => 105
            ]
        ], $resultArray);
    }

    /**
     *
     */
    public function testGetNoResults(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $userEntityIterator = $userRepository->getNoUsers();

        $this->assertCount(0, $userEntityIterator);

        $resultArray = [];
        foreach($userEntityIterator as $userEntity) {
            $resultArray[] = $userEntity->asArray();
        }

        $this->assertEquals([], $resultArray);
    }

    /**
     * @depends testGetEntitiesByQuery
     */
    public function testGetEntitiesByQueryMultipleIteration(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $userEntityIterator = $userRepository->getAllInactiveUsers();

        $resultArray1 = [];
        foreach($userEntityIterator as $userEntity) {
            $resultArray1[] = $userEntity->asArray();
        }

        $resultArray2 = [];
        foreach($userEntityIterator as $userEntity) {
            $resultArray2[] = $userEntity->asArray();
        }

        $this->assertEquals([
            [
                'id_user' => 1,
                'name' => 'Jan',
                'surname' => 'Novák',
                'is_active' => false,
                'created' => new \DateTime('2019-01-15'),
                'default_col' => 105
            ],[
                'id_user' => 3,
                'name' => 'Tomáš',
                'surname' => 'Novotný',
                'is_active' => false,
                'created' => new \DateTime('2019-01-15'),
                'default_col' => 105
            ]
        ], $resultArray2);
    }
}