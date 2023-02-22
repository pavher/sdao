<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2.12.2018
 */

namespace Pavher\Sdao;


interface IIdGetter
{
    /**
     * Get entity primary key value.
     * @return int|null
     */
    public function getId(): ?int;
}