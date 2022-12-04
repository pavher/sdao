<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2.12.2018
 */

namespace Pavher\Sdao\Database;


use Pavher\Sdao\IEntity;

interface IPersistableDatabaseEntity extends IEntity
{
    /**
     * Get entity primary key name.
     * @return string
     */
    public static function getIdName(): string;

    /**
     * Get entity database table name.
     * @return string
     */
    public static function getEntityRelatedTableName(): string;

    /**
     * Is entity new or loaded from database.
     * @return bool
     */
    public function isNewRecord(): bool;

    /**
     * Get entity primary key value.
     * @return int
     */
    public function getId(): ?int;

}