<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 6.12.2018
 */

namespace Pavher\Sdao\Tests;


use Nette\Utils\DateTime;
use Pavher\Sdao\Exceptions\EntityUnrelatedToRepositoryException;
use Pavher\Sdao\Tests\_files\NoUserEntity;
use Pavher\Sdao\Tests\_files\User;
use Pavher\Sdao\Tests\_files\UserRepository;

class DatabaseRepositoryTest extends DatabaseTestParent
{
    public function testGetEntityDatabaseTableName(): void
    {
        $userRepository = new UserRepository(static::$dbContext);
        $user = $userRepository->createEntity();

        $this->assertEquals('user', $user::getEntityRelatedTableName());
    }

    public function testSaveNewEntityToRepository(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $user1 = $userRepository->createEntity();
        $user1->name = 'Jan';
        $user1->surname = 'Novák';
        $user1->created = new \DateTime('2019-01-15');
        $user1->is_active = false;

        try {
            $userRepository->save($user1);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(1, $user1->getId());

        $user2 = $userRepository->createEntity();
        $user2->name = 'Petr';
        $user2->surname = 'Svoboda';
        $user2->default_col = 111;
        $user2->created = new \DateTime('2019-01-15');
        $user2->is_active = true;

        try {
            $userRepository->save($user2);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(2, $user2->getId());

        $formValuesArray = [
            'name' => 'Tomáš',
            'surname' => 'Novotný',
            'created' => new \DateTime('2019-01-15'),
            'is_active' => true];

        $user3 = $userRepository->createEntity($formValuesArray);

        try {
            $userRepository->save($user3);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(3, $user3->getId());

        $formValuesArray2 = [
            'name' => 'Radek',
            'surname' => 'Peterka',
            'created' => new \DateTime('2020-04-30'),
            'is_active' => false];

        $user4 = User::createFromArray($formValuesArray2);

        try {
            $userRepository->save($user4);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(4, $user4->getId());
    }

    public function testSaveUnrelatedEntityToRepository(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $someUnrelatedEntity = new NoUserEntity();
        $someUnrelatedEntity->my_property = "test";

        $this->expectException(EntityUnrelatedToRepositoryException::class);
        $userRepository->save($someUnrelatedEntity);
    }

    /**
     * @depends testSaveNewEntityToRepository
     */
    public function testUpdateEntity(): void
    {
        $changedSurname = 'Fiala';
        $changedIsActive = false;

        $userRepository = new UserRepository(static::$dbContext);

        $userEntity = $userRepository->getById(2);
        $userEntity->surname = $changedSurname;
        $userEntity->is_active = $changedIsActive;

        try {
            $userRepository->save($userEntity);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        // update confirmation
        $confirmationArray = [
            'id_user' => 2,
            'name' => 'Petr',
            'surname' => $changedSurname,
            'default_col' => 111,
            'created' => new DateTime('2019-01-15'),
            'is_active' => $changedIsActive];

        $userRepositoryConfirmation = new UserRepository(static::$dbContext);
        $userEntityConfirmation = $userRepositoryConfirmation->getById(2);

        $this->assertEquals($userEntityConfirmation->asArray(), $confirmationArray);

    }

    /**
     * @depends testUpdateEntity
     */
    public function testGetEntityById(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $userEntity1 = $userRepository->getById(1);
        $this->assertEquals('Jan', $userEntity1->name);
        $this->assertEquals('Novák', $userEntity1->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity1->created);
        $this->assertEquals(105, $userEntity1->default_col);
        $this->assertFalse($userEntity1->is_active);

        $userEntity2 = $userRepository->getById(2);
        $this->assertEquals('Petr', $userEntity2->name);
        $this->assertEquals('Fiala', $userEntity2->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity2->created);
        $this->assertEquals(111, $userEntity2->default_col);
        $this->assertFalse($userEntity2->is_active);

        $userEntity3 = $userRepository->getById(3);
        $this->assertEquals('Tomáš', $userEntity3->name);
        $this->assertEquals('Novotný', $userEntity3->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity3->created);
        $this->assertEquals(105, $userEntity3->default_col);
        $this->assertTrue($userEntity3->is_active);

        $userEntity4 = $userRepository->getById(4);
        $this->assertEquals('Radek', $userEntity4->name);
        $this->assertEquals('Peterka', $userEntity4->surname);
        $this->assertEquals(new \DateTime('2020-04-30'), $userEntity4->created);
        $this->assertEquals(105, $userEntity4->default_col);
        $this->assertFalse($userEntity4->is_active);
    }

    /**
     * @depends testUpdateEntity
     */
    public function testCountAllRecords(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $this->assertEquals(4, $userRepository->getTotal());
    }

    public function testCountRecords(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $this->assertEquals(3, $userRepository->getTotal(['created' => new DateTime('2019-01-15')]));
    }

    public function testGetManyWhereOrder(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $iterator = $userRepository->getMany(['is_active' => false], ['default_col' => true, 'created' => false]);

        $results = iterator_to_array($iterator);

        $userEntity4 = $results[0];
        $this->assertEquals('Radek', $userEntity4->name);
        $this->assertEquals('Peterka', $userEntity4->surname);
        $this->assertEquals(new \DateTime('2020-04-30'), $userEntity4->created);
        $this->assertEquals(105, $userEntity4->default_col);
        $this->assertFalse($userEntity4->is_active);

        $userEntity1 = $results[1];
        $this->assertEquals('Jan', $userEntity1->name);
        $this->assertEquals('Novák', $userEntity1->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity1->created);
        $this->assertEquals(105, $userEntity1->default_col);
        $this->assertFalse($userEntity1->is_active);

        $userEntity2 = $results[2];
        $this->assertEquals('Petr', $userEntity2->name);
        $this->assertEquals('Fiala', $userEntity2->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity2->created);
        $this->assertEquals(111, $userEntity2->default_col);
        $this->assertFalse($userEntity2->is_active);
    }

    public function testGetManyWhere(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $iterator = $userRepository->getMany(['created' => new DateTime('2019-01-15')]);
        $results = iterator_to_array($iterator);

        $userEntity1 = $results[0];
        $this->assertEquals('Jan', $userEntity1->name);
        $this->assertEquals('Novák', $userEntity1->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity1->created);
        $this->assertEquals(105, $userEntity1->default_col);
        $this->assertFalse($userEntity1->is_active);

        $userEntity2 = $results[1];
        $this->assertEquals('Petr', $userEntity2->name);
        $this->assertEquals('Fiala', $userEntity2->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity2->created);
        $this->assertEquals(111, $userEntity2->default_col);
        $this->assertFalse($userEntity2->is_active);

        $userEntity3 = $results[2];
        $this->assertEquals('Tomáš', $userEntity3->name);
        $this->assertEquals('Novotný', $userEntity3->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity3->created);
        $this->assertEquals(105, $userEntity3->default_col);
        $this->assertTrue($userEntity3->is_active);
    }

    public function testGetManyOrder(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $iterator = $userRepository->getMany(null, ['surname' => true]);

        $results = iterator_to_array($iterator);

        $userEntity2 = $results[0];
        $this->assertEquals('Petr', $userEntity2->name);
        $this->assertEquals('Fiala', $userEntity2->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity2->created);
        $this->assertEquals(111, $userEntity2->default_col);
        $this->assertFalse($userEntity2->is_active);

        $userEntity1 = $results[1];
        $this->assertEquals('Jan', $userEntity1->name);
        $this->assertEquals('Novák', $userEntity1->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity1->created);
        $this->assertEquals(105, $userEntity1->default_col);
        $this->assertFalse($userEntity1->is_active);

        $userEntity3 = $results[2];
        $this->assertEquals('Tomáš', $userEntity3->name);
        $this->assertEquals('Novotný', $userEntity3->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity3->created);
        $this->assertEquals(105, $userEntity3->default_col);
        $this->assertTrue($userEntity3->is_active);

        $userEntity4 = $results[3];
        $this->assertEquals('Radek', $userEntity4->name);
        $this->assertEquals('Peterka', $userEntity4->surname);
        $this->assertEquals(new \DateTime('2020-04-30'), $userEntity4->created);
        $this->assertEquals(105, $userEntity4->default_col);
        $this->assertFalse($userEntity4->is_active);
    }

    public function testGetMany(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $iterator = $userRepository->getMany(null, null, 2, 1);

        $results = iterator_to_array($iterator);

        $userEntity2 = $results[0];
        $this->assertEquals('Petr', $userEntity2->name);
        $this->assertEquals('Fiala', $userEntity2->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity2->created);
        $this->assertEquals(111, $userEntity2->default_col);
        $this->assertFalse($userEntity2->is_active);

        $userEntity3 = $results[1];
        $this->assertEquals('Tomáš', $userEntity3->name);
        $this->assertEquals('Novotný', $userEntity3->surname);
        $this->assertEquals(new \DateTime('2019-01-15'), $userEntity3->created);
        $this->assertEquals(105, $userEntity3->default_col);
        $this->assertTrue($userEntity3->is_active);
    }

    public function testGetNullById(): void
    {
        $userRepository = new UserRepository(static::$dbContext);
        $userEntity = $userRepository->getById(1000000);

        $this->assertNull($userEntity);
    }

    /**
     * @depends testGetEntityById
     */
    public function testDeleteByEntity(): void
    {
        $userRepository = new UserRepository(static::$dbContext);
        $userEntity1 = $userRepository->getById(2);
        $userRepository->delete($userEntity1);

        $this->assertInstanceOf(User::class, $userRepository->getById(1));
        $this->assertNull($userRepository->getById(2));
        $this->assertInstanceOf(User::class, $userRepository->getById(3));
    }

    /**
     * @depends testGetEntityById
     */
    public function testDeleteByUnrelatedEntity(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $noUserEntity = NoUserEntity::createFromArray(["id_no_user_entity" => 3]);

        $this->expectException(EntityUnrelatedToRepositoryException::class);
        $userRepository->delete($noUserEntity);
    }

    /**
     * @depends testGetEntityById
     */
    public function testDeleteById(): void
    {
        $userRepository = new UserRepository(static::$dbContext);
        $userRepository->deleteById(3);
        $this->assertNull($userRepository->getById(3));
    }

    /**
     * @depends testGetEntityById
     */
    public function testDeleteManyByIds(): void
    {
        $userRepository = new UserRepository(static::$dbContext);
        $user5 = $userRepository->createEntity();
        $user5->name = 'Alex';
        $user5->surname = 'Newman';
        $user5->created = new \DateTime('2020-01-4');
        $user5->is_active = true;

        try {
            $userRepository->save($user5);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $userRepository->deleteManyByIds([1, 4]);
        $this->assertNull($userRepository->getById(1));
        $this->assertNull($userRepository->getById(4));
    }

    public function testMultipleWrite(): void
    {
        $userRepository = new UserRepository(static::$dbContext);

        $user6 = $userRepository->createEntity();
        $user6->name = 'Paul';
        $user6->surname = 'Young';
        $user6->created = new \DateTime('2020-04-30');
        $user6->is_active = false;

        try {
            $userRepository->save($user6);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(6, $user6->getId());
        $this->assertEquals('Paul', $user6->name);
        $this->assertEquals('Young', $user6->surname);
        $this->assertEquals(new \DateTime('2020-04-30'), $user6->created);
        $this->assertEquals(null, $user6->default_col); // the property value is still null, there are no db reload (currently, in db it has 105 by default)
        $this->assertFalse($user6->is_active);

        // do some more (re)assignments and save entity once more

        $user6->default_col = 123; // new
        $user6->is_active = true; // reassign

        try {
            $userRepository->save($user6);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(6, $user6->getId());
        $this->assertEquals(123, $user6->default_col);
        $this->assertTrue($user6->is_active);

        $loadedUser6 = $userRepository->getById(6);
        $this->assertEquals(6, $loadedUser6->getId());
        $this->assertEquals('Paul', $loadedUser6->name);
        $this->assertEquals('Young', $loadedUser6->surname);
        $this->assertEquals(new \DateTime('2020-04-30'), $loadedUser6->created);
        $this->assertEquals(123, $loadedUser6->default_col); // the value is still null, no db reload (105 default db value)
        $this->assertTrue($loadedUser6->is_active);
    }

}