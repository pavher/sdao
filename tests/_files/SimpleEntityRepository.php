<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-10-26
 */

namespace Pavher\Sdao\Tests\_files;


use Nette\Utils\Validators;
use Pavher\Sdao\IEntity;
use Pavher\Sdao\IEntityValidatorConfigurable;
use Pavher\Sdao\Repository;

class SimpleEntityRepository extends Repository
{
    //<editor-fold desc="Methods - public">

    /**
     * @param array|null $initialData
     * @param array|null $allowedKeys
     * @return SimpleEntity
     */
    public function createEntity(?array $initialData = null, ?array $allowedKeys = null): SimpleEntity
    {
        return parent::createEntity($initialData, $allowedKeys);
    }

    //</editor-fold>

    //<editor-fold desc="Abstract methods implementation">

    protected function getEntityClassName(): string
    {
        return SimpleEntity::class;
    }

    //</editor-fold>

    //<editor-fold desc="Methods - protected">

    /**
     * @param IEntity $entity
     * @param IEntityValidatorConfigurable $entityValidator
     */
    protected function setupEntityValidation(IEntity $entity, IEntityValidatorConfigurable $entityValidator): void
    {
        parent::setupEntityValidation($entity, $entityValidator);
        $entityValidator->setGlobalValidationMessage("Global validation message 1");
        /**@var \Pavher\Sdao\Tests\_files\SimpleEntityTwo $entity */
        $entityValidator->addPropertyValidation('custom_string_property',
            Validators::is($entity->custom_string_property, 'string:10..'),
            'Property length must be at least 10 characters');
    }

    //</editor-fold>


}