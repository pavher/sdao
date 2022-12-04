<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 4.12.2018
 */

namespace Pavher\Sdao\Tests;


use Nette\Utils\DateTime;
use Pavher\Sdao\Exceptions\UndeclaredPropertyException;
use Pavher\Sdao\Exceptions\UnsetPropertyException;
use Pavher\Sdao\Tests\_files\SimpleEntity;
use Pavher\Sdao\Tests\_files\SimpleEntityWithDefaultData;
use Pavher\Sdao\Tests\_files\User;

class EntityTest extends \PHPUnit\Framework\TestCase
{
    public function testGetEntityName(): void
    {
        $testEntity = new SimpleEntity();
        $this->assertEquals((new \ReflectionClass(new SimpleEntity()))->getShortName(), $testEntity::getEntityName());
    }

    public function testCreateEntityInstanceWithConstructorData(): void
    {
        $date = new \DateTime('2018-04-12');
        $testEntity = new SimpleEntity(['custom_int_property' => 12, 'custom_string_property' => 'custom str', 'custom_bool_property' => true, 'custom_date_property' => $date]);
        $this->assertSame(12, $testEntity->custom_int_property);
        $this->assertSame('custom str', $testEntity->custom_string_property);
        $this->assertTrue($testEntity->custom_bool_property);
        $this->assertSame($date, $testEntity->custom_date_property);
    }

    public function testCreateEntityInstanceWithConstructorDataUnchecked(): void
    {
        $testEntity = SimpleEntity::createFromArray(['custom_int_property' => 12, 'custom_string_property' => 'custom str', 'custom_bool_property' => true]);
        $this->assertInstanceOf(SimpleEntity::class, $testEntity);
    }

    public function testCreateEntityInstanceWithConstructorDataCheckedNotThrowException(): void
    {
        $testEntity = new SimpleEntity(['custom_int_property' => 12, 'custom_string_property' => 'custom str', 'custom_bool_property' => true, 'custom_date_property' => new \DateTime('2018-04-12')], true);
        $this->assertInstanceOf(SimpleEntity::class, $testEntity);
    }

    public function testCreateEntityInstanceWithConstructorDataCheckedThrowException(): void
    {
        $this->expectException(UnsetPropertyException::class);
        $testEntity = new SimpleEntity(['custom_int_property' => 12, 'custom_string_property' => 'custom str', 'custom_bool_property' => true], true);
    }

    public function testGetCustomIntPropertyObject(): void
    {
        $testEntity = new SimpleEntity();
        $testEntity->custom_int_property = 45;
        $propertyObject = $testEntity->getPropertyInfo('custom_int_property');
        $this->assertEquals('int', $propertyObject->getType());
        $this->assertEquals('custom_int_property', $propertyObject->getName());
        $this->assertIsInt($propertyObject->getValue());
        $this->assertEquals(45, $propertyObject->getValue());
        $this->assertTrue($propertyObject->isChanged());
    }

    public function testGetCustomStringPropertyObject(): void
    {
        $testEntity = new SimpleEntity();
        $testEntity->custom_string_property = 'this is our string property';
        $propertyObject = $testEntity->getPropertyInfo('custom_string_property');
        $this->assertEquals('string', $propertyObject->getType());
        $this->assertEquals('custom_string_property', $propertyObject->getName());
        $this->assertIsString($propertyObject->getValue());
        $this->assertEquals('this is our string property', $propertyObject->getValue());
        $this->assertTrue($propertyObject->isChanged());
    }

    public function testGetCustomBoolPropertyObject(): void
    {
        $testEntity = new SimpleEntity();
        $testEntity->custom_bool_property = false;
        $propertyObject = $testEntity->getPropertyInfo('custom_bool_property');
        $this->assertEquals('bool', $propertyObject->getType());
        $this->assertEquals('custom_bool_property', $propertyObject->getName());
        $this->assertFalse($propertyObject->getValue());
        $this->assertTrue($propertyObject->isChanged());
    }

    public function testGetCustomDatePropertyObject(): void
    {
        $testEntity = new SimpleEntity();
        $date = new \DateTime('2018-04-12');
        $testEntity->custom_date_property = $date;
        $propertyObject = $testEntity->getPropertyInfo('custom_date_property');
        $this->assertEquals('\DateTime', $propertyObject->getType());
        $this->assertEquals('custom_date_property', $propertyObject->getName());
        $this->assertEquals($date, $propertyObject->getValue());
        $this->assertTrue($propertyObject->isChanged());
    }

    public function testUndeclaredPropertyException(): void
    {
        $testEntity = new SimpleEntity();
        $this->expectException(UndeclaredPropertyException::class);
        $testEntity->undeclared_property = 'this is undeclared property, it will throw exception';
    }

    public function testDeclaredButUnsetProperties(): void
    {
        $testEntity = new SimpleEntity();
        $this->assertNull($testEntity->custom_int_property);
        $this->assertNull($testEntity->custom_bool_property);
        $this->assertNull($testEntity->custom_string_property);
        $this->assertNull($testEntity->custom_date_property);

        $propertyObject = $testEntity->getPropertyInfo('custom_string_property');
        $this->assertEquals('string', $propertyObject->getType());
        $this->assertEquals('custom_string_property', $propertyObject->getName());
        $this->assertNull($propertyObject->getValue());
        $this->assertFalse($propertyObject->isChanged());
    }

    public function testUnchangedProperty(): void
    {
        // set initial data in constructor - entity is unchanged
        $testEntity = SimpleEntity::createFromArray(['custom_int_property' => 5]);
        $intPropertyObject = $testEntity->getPropertyInfo('custom_int_property');
        $this->assertFalse($intPropertyObject->isChanged());

        $intPropertyObject = $testEntity->getPropertyInfo('custom_string_property');
        $this->assertFalse($intPropertyObject->isChanged());
    }

    public function testIsUnchangedEmptyEntity(): void
    {
        $testEntity = new SimpleEntity();
        $this->assertFalse($testEntity->isChanged());
    }

    public function testIsUnchangedEntityWithInitializedProperty(): void
    {
        $testEntity = SimpleEntity::createFromArray(['custom_int_property' => 5]);
        $this->assertFalse($testEntity->isChanged());
    }

    public function testIsChangedEntity(): void
    {
        $testEntity = new SimpleEntity();
        $testEntity->custom_string_property = 'we can change some property';
        $this->assertTrue($testEntity->isChanged());
    }

    public function testAsArray(): void
    {
        $testEntity = new SimpleEntity();
        $testEntity->custom_int_property = 1234;
        $testEntity->custom_bool_property = true;
        $testEntity->custom_string_property = 'Enjoy The Silence';

        $this->assertEquals([
            'custom_int_property' => 1234,
            'custom_bool_property' => true,
            'custom_string_property' => 'Enjoy The Silence',
            'custom_date_property' => null
        ], $testEntity->asArray());
    }

    public function testAsArrayChangedOnly(): void
    {
        $testEntity = new SimpleEntity();
        $testEntity->custom_int_property = 1234;
        $testEntity->custom_bool_property = null;

        $this->assertEquals([
            'custom_int_property' => 1234,
            'custom_bool_property' => null
        ], $testEntity->asArray(null, false, true));
    }

    public function testCreateFromJson(): void
    {
        $json = '{"id_user":2, "name":"Testname", "surname":"Testsurname", "is_active": true, "created": "2019-01-15 15:16:17"}';

        $userEntity = User::createFromJson($json);

        $this->assertEquals([
            'id_user' => 2,
            'name' => 'Testname',
            'surname' => 'Testsurname',
            'is_active' => true,
            'created' => new DateTime("2019-01-15 15:16:17"),
            'default_col' => null
        ], $userEntity->asArray());
    }

    public function testAssignNullValues(): void
    {
        $dataArray = [
            'id_user' => null,
            'name' => null,
            'surname' => null,
            'is_active' => null,
            'created' => null,
            'default_col' => null
        ];

        $userEntity = new User($dataArray);

        $this->assertEquals($dataArray, $userEntity->asArray());
    }

    public function testDefaultDataInNewEntity(): void
    {
        $testEntity = new SimpleEntityWithDefaultData();

        $this->assertEquals([
            'custom_int_property' => null,
            'custom_bool_property' => null,
            'custom_string_property' => 'default string',
            'custom_date_property' => null
        ], $testEntity->asArray());
    }

    public function testDefaultDataInNewEntityWithUserData(): void
    {
        $formData = ['custom_int_property' => 20];
        $testEntity = SimpleEntityWithDefaultData::createFromArray($formData);

        $this->assertEquals([
            'custom_int_property' => 20,
            'custom_bool_property' => null,
            'custom_string_property' => 'default string',
            'custom_date_property' => null
        ], $testEntity->asArray());
    }
}