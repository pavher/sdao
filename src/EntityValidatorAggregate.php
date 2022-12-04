<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-10-30
 */

namespace Pavher\Sdao;


use Pavher\Sdao\Exceptions\ValidationException;

class EntityValidatorAggregate implements IEntityValidatorAggregate
{

    //<editor-fold desc="Properties">

    /**
     * @var IEntityValidator[]
     */
    private $entityValidatorCollection = [];

    //</editor-fold>

    //<editor-fold desc="Methods - interface implementation">

    /**
     * @param IEntity $entity
     * @return IEntityValidatorConfigurable
     */
    public function getEntityValidator(IEntity $entity): IEntityValidatorConfigurable
    {
        $entityName = $entity::getEntityName();
        if (!array_key_exists($entityName, $this->entityValidatorCollection)) {
            $this->entityValidatorCollection[$entityName] = new EntityValidator();
        }

        return $this->entityValidatorCollection[$entityName];
    }

    //</editor-fold>

    //<editor-fold desc="Methods - public">

    /**
     * @throws ValidationException
     */
    public function check(): void
    {
        $validationResultArray = [];
        $globalValidationMessage = '';

        foreach ($this->entityValidatorCollection as $entityName => $entityValidator) {
            $entityValidationResults = $entityValidator->getValidationResults();
            if (count($entityValidationResults) > 0) {
                $validationResultArray[$entityName] = $entityValidationResults;
                $globalValidationMessage .= $entityValidator->getGlobalValidationMessage() . ' ';
            }
        }

        if (count($validationResultArray) > 0) {
            throw new ValidationException(trim($globalValidationMessage), $validationResultArray);
        }
    }

    //</editor-fold>
}