<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 29.1.2019
 *
 *
CREATE TABLE `student` (
`id_student` INT(10) UNSIGNED NOT NULL,
`id_study_programme` INT(10) UNSIGNED NOT NULL,
`name` VARCHAR(50) NOT NULL COLLATE 'utf8_czech_ci',
`surname` VARCHAR(50) NOT NULL COLLATE 'utf8_czech_ci',
PRIMARY KEY (`id_student`),
INDEX `id_study_programme` (`id_study_programme`),
CONSTRAINT `FK_student_study_programme` FOREIGN KEY (`id_study_programme`) REFERENCES `study_programme` (`id_study_programme`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_czech_ci'
ENGINE=InnoDB
;

CREATE TABLE `study_programme` (
`id_study_programme` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` VARCHAR(50) NOT NULL COLLATE 'utf8_czech_ci',
`is_active` TINYINT(4) NOT NULL,
PRIMARY KEY (`id_study_programme`)
)
COLLATE='utf8_czech_ci'
ENGINE=InnoDB
;
 */

namespace Pavher\Sdao\Tests;


use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Pavher\Sdao\Tests\_files\StudentCompositeRepository;
use Pavher\Sdao\Tests\_files\TestStorage;
use PHPUnit\Framework\TestCase;

class CompositeDatabaseEntityTest extends TestCase
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

        $connection->query('DROP TABLE IF EXISTS `student`');
        $connection->query('DROP TABLE IF EXISTS `study_programme`');

        $connection->query('CREATE TABLE `study_programme` (
                            `id_study_programme` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                            `name` VARCHAR(50) NOT NULL COLLATE \'utf8_czech_ci\',
                            `is_active` TINYINT(4) NOT NULL,
                            PRIMARY KEY (`id_study_programme`)
                            )
                            COLLATE=\'utf8_czech_ci\'
                            ENGINE=InnoDB;');

        $connection->query('CREATE TABLE `student` (
                            `id_student` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                            `id_study_programme` INT(10) UNSIGNED NOT NULL,
                            `name` VARCHAR(50) NOT NULL COLLATE \'utf8_czech_ci\',
                            `surname` VARCHAR(50) NOT NULL COLLATE \'utf8_czech_ci\',
                            PRIMARY KEY (`id_student`),
                            INDEX `id_study_programme` (`id_study_programme`),
                            CONSTRAINT `FK_student_study_programme` FOREIGN KEY (`id_study_programme`) REFERENCES `study_programme` (`id_study_programme`) ON UPDATE CASCADE ON DELETE CASCADE
                            )
                            COLLATE=\'utf8_czech_ci\'
                            ENGINE=InnoDB;');

        $connection->query('INSERT INTO `study_programme` (`name`, `is_active`) VALUES (\'History\', \'1\');');
        $connection->query('INSERT INTO `study_programme` (`name`, `is_active`) VALUES (\'Architecture\', \'0\');');
        $connection->query('INSERT INTO `student` (`id_study_programme`, `name`, `surname`) VALUES (\'1\', \'Jonathan \', \'Chaffer\');');
        $connection->query('INSERT INTO `student` (`id_study_programme`, `name`, `surname`) VALUES (\'1\', \'Karl\', \'Swedberg\');');
        $connection->query('INSERT INTO `student` (`id_study_programme`, `name`, `surname`) VALUES (\'2\', \'Paul\', \'Young\');');
        //$connection->query('');

        $storage = new TestStorage();

        $structure = new Structure($connection, $storage);

        $dbContext = new Context($connection, $structure);

        static::$dbContext = $dbContext;
    }


    public function testLoadCompositeDatabaseEntityFormDb()
    {
        $studentCompositeRepository = new StudentCompositeRepository(static::$dbContext);

        $studentCompositeEntityIterator = $studentCompositeRepository->getAllStudentsInActiveCourses();

        $resultArray = [];
        foreach($studentCompositeEntityIterator as $userEntity) {
            $resultArray[] = $userEntity->asArray();
        }

        $this->assertEquals([
            [
                'student_name' => 'Jonathan ',
                'student_surname' => 'Chaffer',
                'study_programme_name' => 'History',
                'id_student' => 1
            ],[
                'student_name' => 'Karl',
                'student_surname' => 'Swedberg',
                'study_programme_name' => 'History',
                'id_student' => 2
            ]
        ], $resultArray);
    }



    public static function tearDownAfterClass(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::tearDownAfterClass();

        //static::$dbContext->query('DROP TABLE `study_programme`');
        //static::$dbContext->query('DROP TABLE `student`');
    }


}