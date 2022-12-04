<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-10-24
 */

namespace Pavher\Sdao\Tests;


use Pavher\Sdao\EntityValidatorAggregate;
use Pavher\Sdao\Exceptions\EntityUnrelatedToRepositoryException;
use Pavher\Sdao\Exceptions\ValidationException;
use Pavher\Sdao\Tests\_files\SimpleEntity;
use Pavher\Sdao\Tests\_files\SimpleEntityRepository;
use Pavher\Sdao\Tests\_files\SimpleEntityTwoRepository;
use Pavher\Sdao\Tests\_files\User;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    public function testCreateEntity(): void
    {
        $simpleEntityRepository = new SimpleEntityRepository();
        $simpleEntity = $simpleEntityRepository->createEntity();
        $this->assertInstanceOf(SimpleEntity::class, $simpleEntity);
    }

    public function testValidateUnrelatedEntity(): void
    {
        $simpleEntityRepository = new SimpleEntityRepository();

        $userEntity = new User();
        $this->expectException(EntityUnrelatedToRepositoryException::class);

        $simpleEntityRepository->validateEntity($userEntity);
    }

    public function testValidateSingleEntitySuccess(): void
    {
        $simpleEntityRepository = new SimpleEntityRepository();
        $simpleEntity = $simpleEntityRepository->createEntity();

        $simpleEntity->custom_string_property = '1234567890';

        try {
            $simpleEntityRepository->validateEntity($simpleEntity);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function testValidateSingleEntityFail(): void
    {
        $simpleEntityRepository = new SimpleEntityRepository();
        $simpleEntity = $simpleEntityRepository->createEntity();

        $simpleEntity->custom_string_property = '123456789';

        try {
            $simpleEntityRepository->validateEntity($simpleEntity);
            $this->fail("ValidationException has not thrown.");
        } catch (ValidationException $e) {
            $this->assertEquals(["SimpleEntity" => ["custom_string_property" => "Property length must be at least 10 characters"]],
                $e->getResultArray());
            $this->assertEquals(["custom_string_property" => "Property length must be at least 10 characters"],
                $e->getResultArraySimple());
            $this->assertEquals("Global validation message 1", $e->getMessage());
            echo($e->getMessage());
        }
    }

    public function testValidateSeveralEntitiesSuccess(): void
    {
        $simpleEntityRepository = new SimpleEntityRepository();
        $simpleEntity = $simpleEntityRepository->createEntity();
        $simpleEntity->custom_string_property = '1234567890';

        $simpleEntityTwoRepository = new SimpleEntityTwoRepository();
        $simpleEntityTwo = $simpleEntityTwoRepository->createEntity();
        $simpleEntityTwo->email = 'test@test.com';

        $entityValidatorAggregate = new EntityValidatorAggregate();
        $simpleEntityRepository->validateEntityRaw($simpleEntity, $entityValidatorAggregate);
        $simpleEntityTwoRepository->validateEntityRaw($simpleEntityTwo, $entityValidatorAggregate);

        try {
            $entityValidatorAggregate->check();
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function testValidateSeveralEntitiesFail(): void
    {
        $simpleEntityRepository = new SimpleEntityRepository();
        $simpleEntity = $simpleEntityRepository->createEntity();
        $simpleEntity->custom_string_property = '123456789';

        $simpleEntityTwoRepository = new SimpleEntityTwoRepository();
        $simpleEntityTwo = $simpleEntityTwoRepository->createEntity();
        $simpleEntityTwo->email = 'test';

        $entityValidatorAggregate = new EntityValidatorAggregate();
        $simpleEntityRepository->validateEntityRaw($simpleEntity, $entityValidatorAggregate);
        $simpleEntityTwoRepository->validateEntityRaw($simpleEntityTwo, $entityValidatorAggregate);

        try {
            $entityValidatorAggregate->check();
            $this->fail("ValidationException has not thrown.");
        } catch (ValidationException $e) {
            $this->assertEquals([
                "SimpleEntity" => ["custom_string_property" => "Property length must be at least 10 characters"],
                "SimpleEntityTwo" => ["email" => "Property must be valid email"]
            ], $e->getResultArray());

            $this->assertEquals([
                "custom_string_property" => "Property length must be at least 10 characters",
                "email" => "Property must be valid email"
            ], $e->getResultArraySimple());

            $this->assertEquals("Global validation message 1 Global validation message 2", $e->getMessage());
        }

        $this->assertTrue(true);
    }

}