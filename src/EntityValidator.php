<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-02-08
 */

namespace Pavher\Sdao;

class EntityValidator implements IEntityValidator, IEntityValidatorConfigurable
{
    //<editor-fold desc="Properties">

    /**
     * @var string
     */
    private $globalValidationMessage;

    /**
     * @var array
     */
    private $validationResults = [];

    //</editor-fold>

    //<editor-fold desc="Methods - interface implementation">

    /**
     * @param string $globalValidationMessage
     * @return mixed
     */
    public function setGlobalValidationMessage(string $globalValidationMessage): void
    {
        $this->globalValidationMessage = $globalValidationMessage;
    }

    /**
     * @return string
     */
    public function getGlobalValidationMessage(): string
    {
        return $this->globalValidationMessage;
    }

    /**
     * @return array
     */
    public function getValidationResults(): array
    {
        return $this->validationResults;
    }

    /**
     * @param string $propertyName
     * @param bool $validationResult
     * @param null|string $propertyValidationMessage
     * @param bool $append
     */
    public function addPropertyValidation(
        string $propertyName,
        bool $validationResult,
        ?string $propertyValidationMessage = null,
        bool $append = false
    ): void {
        if ($validationResult === false) {
            if (!isset($this->validationResults[$propertyName])) {
                $this->validationResults[$propertyName] = $propertyValidationMessage;
            } else {
                if ($append) {
                    $this->validationResults[$propertyName] .= ' ' . $propertyValidationMessage;
                }
            }
        }
    }

    //</editor-fold>

}