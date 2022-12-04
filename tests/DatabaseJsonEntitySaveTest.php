<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-03-16
 */

namespace Pavher\Sdao\Tests;

use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Nette\Utils\DateTime;
use Pavher\Sdao\Tests\_files\JsonEntity;
use Pavher\Sdao\Tests\_files\JsonWrapperRepository;
use Pavher\Sdao\Tests\_files\TestStorage;
use PHPUnit\Framework\TestCase;

class DatabaseJsonEntitySaveTest extends TestCase
{
    /**
     * @var Context
     */
    protected static $dbContext;

    public static function setUpBeforeClass(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUpBeforeClass();

        $dsn = 'mysql:host=127.0.0.1;dbname=sdao-test';
        $user = 'root';
        $password = '';

        $connection = new Connection($dsn, $user, $password);

        $connection->query('DROP TABLE IF EXISTS `json_wrapper`');

        $connection->query('CREATE TABLE IF NOT EXISTS `json_wrapper` (
                `id_json_wrapper` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `json_entity` TEXT NOT NULL,
                PRIMARY KEY (`id_json_wrapper`)
            )
            COLLATE=\'utf8_czech_ci\'
            ; ');


        $storage = new TestStorage();

        $structure = new Structure($connection, $storage);

        $dbContext = new Context($connection, $structure);

        static::$dbContext = $dbContext;
    }

    public function testSaveJsonEntityWrapper(): void
    {
        $jsonWrapperRepository = new JsonWrapperRepository(static::$dbContext);

        $jsonEntity = new JsonEntity();
        $jsonEntity->id_json = 16;
        $jsonEntity->json_string = 'some json string';
        $jsonEntity->json_boolean = false;
        $jsonEntity->json_date = new DateTime('2019-03-16');

        $jsonWrapperEntity = $jsonWrapperRepository->createEntity();
        $jsonWrapperEntity->json_entity = $jsonEntity;

        try {
            $jsonWrapperRepository->save($jsonWrapperEntity);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(1, $jsonWrapperEntity->getId());

        $jsonEntity2 = $jsonWrapperEntity->json_entity;

        $this->assertEquals(16, $jsonEntity2->id_json);
        $this->assertEquals('some json string', $jsonEntity2->json_string);
        $this->assertEquals(false, $jsonEntity2->json_boolean);
        $this->assertEquals(new DateTime('2019-03-16'), $jsonEntity2->json_date);
    }

    /**
     * @depends testSaveJsonEntityWrapper
     */
    public function testLoadJsonEntityWrapper(): void
    {
        $jsonWrapperRepository = new JsonWrapperRepository(static::$dbContext);
        $jsonWrapperEntity = $jsonWrapperRepository->getById(1);

        $jsonEntity = $jsonWrapperEntity->json_entity;
        $this->assertEquals(16, $jsonEntity->id_json);
        $this->assertEquals('some json string', $jsonEntity->json_string);
        $this->assertEquals(false, $jsonEntity->json_boolean);
        $this->assertEquals(new DateTime('2019-03-16'), $jsonEntity->json_date);
    }

    public static function tearDownAfterClass(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::tearDownAfterClass();

        static::$dbContext->query('DROP TABLE `json_wrapper`');
    }
}