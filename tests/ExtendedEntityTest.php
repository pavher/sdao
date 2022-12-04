<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 5.12.2018
 */

namespace Pavher\Sdao\Tests;


use Pavher\Sdao\Tests\_files\ExtendedSimpleEntity;
use Pavher\Sdao\Tests\_files\SimpleEntity;
use PHPUnit\Framework\TestCase;

class ExtendedEntityTest extends TestCase
{
    public function testExtendedEntityName(): void
    {
        $extendedTestEntity = new ExtendedSimpleEntity();
        $this->assertEquals((new \ReflectionClass(new ExtendedSimpleEntity()))->getShortName(), $extendedTestEntity::getEntityName());
    }

    public function testExtendedEntityProperty(): void
    {
        $extendedTestEntity = new ExtendedSimpleEntity();
        $extendedTestEntity->custom_string_property = 'base entity property test string';
        $extendedTestEntity->extended_entity_string_property = 'extended entity property test string';

        $this->assertEquals('base entity property test string', $extendedTestEntity->custom_string_property);
        $this->assertEquals((new \ReflectionClass(new SimpleEntity()))->getShortName(),
            $extendedTestEntity->getPropertyInfo('custom_string_property')->getOwner());

        $this->assertEquals('extended entity property test string',
            $extendedTestEntity->extended_entity_string_property);
        $this->assertEquals((new \ReflectionClass(new ExtendedSimpleEntity()))->getShortName(),
            $extendedTestEntity->getPropertyInfo('extended_entity_string_property')->getOwner());
    }

    public function testExtendedEntityAsArray(): void
    {
        $initialDataArray = [
            'extended_entity_string_property' => 'extended value 1',
            'custom_int_property' => 56,
            'custom_bool_property' => true,
            'custom_string_property' => 'parent value 1',
            'custom_date_property' => new \DateTime('2018-12-15')
        ];

        $extendedSdaoEntity = new ExtendedSimpleEntity($initialDataArray);

        $propertyArray = $extendedSdaoEntity->asArray();

        $this->assertEquals([
            'extended_entity_string_property' => 'extended value 1',
            'custom_int_property' => 56,
            'custom_bool_property' => true,
            'custom_string_property' => 'parent value 1',
            'custom_date_property' => new \DateTime('2018-12-15')
        ], $propertyArray);
    }

    public function testExtendedEntityAsArrayActualClass(): void
    {
        $initialDataArray = [
            'extended_entity_string_property' => 'extended value 1',
            'custom_int_property' => 56,
            'custom_bool_property' => true,
            'custom_string_property' => 'parent value 1',
            'custom_date_property' => new \DateTime('2018-12-15')
        ];

        $extendedSdaoEntity = new ExtendedSimpleEntity($initialDataArray);

        $array2 = $extendedSdaoEntity->asArray((new \ReflectionClass($extendedSdaoEntity))->getShortName());

        $this->assertEquals([
            'extended_entity_string_property' => 'extended value 1'
        ], $array2);
    }

    public function testExtendedEntityAsArrayParentClass(): void
    {
        $initialDataArray = [
            'extended_entity_string_property' => 'extended value 1',
            'custom_int_property' => 56,
            'custom_bool_property' => true,
            'custom_string_property' => 'parent value 1',
            'custom_date_property' => new \DateTime('2018-12-15')
        ];

        $extendedSdaoEntity = new ExtendedSimpleEntity($initialDataArray);

        $array2 = $extendedSdaoEntity->asArray((new \ReflectionClass($extendedSdaoEntity))->getParentClass()->getShortName());

        $this->assertEquals([
            'custom_int_property' => 56,
            'custom_bool_property' => true,
            'custom_string_property' => 'parent value 1',
            'custom_date_property' => new \DateTime('2018-12-15')
        ], $array2);
    }
}