<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 16.1.2019
 */

namespace Pavher\Sdao\Database;


use Nette\NotImplementedException;
use Pavher\Sdao\IEntity;
use Pavher\Sdao\ReadonlyEntity;

/**
 * Class CompositeDatabaseEntity - unpersistable readonly entity, able to derive other entities from itself
 * @package Pavher\Sdao\Database
 */
abstract class CompositeDatabaseEntity extends ReadonlyEntity
{
    public function asPartialArray(?array $propertyMap = null, ?string $ownerEntityName = null)
    {
        throw new NotImplementedException();
    }
}