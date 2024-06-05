<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 16.1.2019
 */

namespace Pavher\Sdao\Tests;

use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Pavher\Sdao\Tests\_files\TestStorage;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestParent extends TestCase
{

    /**
     * @var Context
     */
    protected static $dbContext;

    public static function setUpBeforeClass(
    ): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUpBeforeClass();

        $dsn = 'mysql:host=127.0.0.1;dbname=sdao-test';
        $user = 'root';
        $password = '';

        $connection = new Connection($dsn, $user, $password);

        $connection->query('DROP TABLE IF EXISTS `user`');

        $connection->query('CREATE TABLE IF NOT EXISTS `user` (
                `id_user` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` CHAR(255) NOT NULL,
                `surname` CHAR(255) NOT NULL,
                `created` DATE NOT NULL,
                `is_active` TINYINT NOT NULL,
                `default_col` INT NOT NULL DEFAULT 105,
                PRIMARY KEY (`id_user`)
            )
            COLLATE=\'utf8_czech_ci\'
            ; ');


        $storage = new TestStorage();

        $structure = new Structure($connection, $storage);

        $dbContext = new Context($connection, $structure);

        static::$dbContext = $dbContext;
    }

    public static function tearDownAfterClass(
    ): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::tearDownAfterClass();

        static::$dbContext->query('DROP TABLE `user`');
    }
}