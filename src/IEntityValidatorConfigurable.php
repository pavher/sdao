<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-10-31
 */

namespace Pavher\Sdao;


interface IEntityValidatorConfigurable
{
    /**
     * @param string $globalValidationMessage
     * @return mixed
     */
    public function setGlobalValidationMessage(string $globalValidationMessage): void;

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
    ): void;
}