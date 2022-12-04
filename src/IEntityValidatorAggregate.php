<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Pavel Herink
 * Date: 2019-10-31
 */

namespace Pavher\Sdao;

interface IEntityValidatorAggregate
{
    /**
     * @param IEntity $entity
     * @return IEntityValidatorConfigurable
     */
    public function getEntityValidator(IEntity $entity): IEntityValidatorConfigurable;
}