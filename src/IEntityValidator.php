<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Pavel Herink
 * Date: 2019-10-31
 */

namespace Pavher\Sdao;

interface IEntityValidator
{
    /**
     * @return string
     */
    public function getGlobalValidationMessage(): string;

    /**
     * @return array
     */
    public function getValidationResults(): array;
}