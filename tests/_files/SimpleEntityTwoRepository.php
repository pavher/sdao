<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-11-01
 */

namespace Pavher\Sdao\Tests\_files;


use Nette\Utils\Validators;
use Pavher\Sdao\IEntity;
use Pavher\Sdao\IEntityValidatorConfigurable;
use Pavher\Sdao\Repository;

class SimpleEntityTwoRepository extends Repository
{

    protected function getEntityClassName(): string
    {
        return SimpleEntityTwo::class;
    }

    /**
     * @param array|null $initialData
     * @param array|null $allowedKeys
     * @return SimpleEntityTwo
     */
    public function createEntity(?array $initialData = null, ?array $allowedKeys = null): SimpleEntityTwo
    {
        return parent::createEntity($initialData, $allowedKeys);
    }

    /**
     * @param IEntity $entity
     * @param IEntityValidatorConfigurable $entityValidator
     */
    protected function setupEntityValidation(IEntity $entity, IEntityValidatorConfigurable $entityValidator): void
    {
        parent::setupEntityValidation($entity, $entityValidator);
        $entityValidator->setGlobalValidationMessage("Global validation message 2");
        /**@var \Pavher\Sdao\Tests\_files\SimpleEntity $entity */
        $entityValidator->addPropertyValidation('email', Validators::isEmail($entity->email),
            'Property must be valid email');
    }
}