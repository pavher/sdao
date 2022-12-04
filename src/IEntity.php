<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Pavel Herink
 * Date: 2.12.2018
 */

namespace Pavher\Sdao;

use Pavher\Sdao\Exceptions\EntityValidationException;
use Pavher\Sdao\Exceptions\UndeclaredPropertyException;

interface IEntity
{
    /**
     * @return string
     */
    public static function getEntityName(): string;

    /**
     * @return bool
     */
    public function isChanged(): bool;

    /**
     * Returns property data as simple property=>value array.
     * @param string|null $ownerEntityName
     * @param bool $serializeObjects
     * @param bool $changedOnly
     * @return array
     */
    public function asArray(string $ownerEntityName = null, bool $serializeObjects = false, bool $changedOnly = false): array;
}