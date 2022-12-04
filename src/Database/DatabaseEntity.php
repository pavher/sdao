<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2.12.2018
 */

namespace Pavher\Sdao\Database;


use Pavher\Sdao\Entity;
use Pavher\Sdao\Exceptions\PrimaryKeyChangeException;

abstract class DatabaseEntity extends Entity implements IPersistableDatabaseEntity
{
    //<editor-fold desc="Consts">

    private const PRIMARY_KEY_PREFIX = 'id_';

    //</editor-fold>

    //<editor-fold desc="Properties">

    /**
     * @var bool
     */
    private $isNewRecord = true;

    //</editor-fold>


    //<editor-fold desc="Constructor">

    public function __construct($data = null, bool $checkAllProperties = true)
    {
        $notEmptyPrimaryKey = ($data !== null && (is_array($data) || $data instanceof \ArrayAccess)) && array_key_exists(static::getIdName(),
                $data);

        parent::__construct($data, $checkAllProperties);

        if($data === null) return;

        if ($notEmptyPrimaryKey) {
            // loaded from db, all properties are marked as unchanged
            $this->isNewRecord = false;
        } else {
            // constructed with some data (form form, etc.), set changed to all not empty properties
            $this->changed = true;
            foreach ($this->propertyArray as $name => $val) {
                $this->propertyArray[$name][self::ENTITY_PROPERTY_IS_CHANGED_KEY] = true;
            }
        }
    }

    //</editor-fold>


    //<editor-fold desc="Interface implementation">

    /**
     * Get entity primary key name.
     * @return string
     */
    public static function getIdName(): string
    {
        return self::PRIMARY_KEY_PREFIX . static::getEntityRelatedTableName();
    }

    /**
     * Get entity database table name.
     * @return string
     */
    public static function getEntityRelatedTableName(): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', static::getEntityName(), $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    /**
     * Is entity new or loaded from database.
     * @return bool
     */
    public function isNewRecord(): bool
    {
        return $this->isNewRecord;
    }

    /**
     * Get entity primary key value.
     * @return int|null
     * @throws \Pavher\Sdao\Exceptions\UndeclaredPropertyException
     */
    public function getId(): ?int
    {
        $idVal = $this->getPropertyValue(static::getIdName());
        return $idVal === null ? null : (int)$idVal;
    }

    //</editor-fold>


    //<editor-fold desc="Methods - public">

    /**
     * @param string $name
     * @param mixed $value
     * @throws PrimaryKeyChangeException
     * @throws \Pavher\Sdao\Exceptions\UndeclaredPropertyException
     */
    public function setPropertyValue(string $name, $value): void
    {
        if ($name === static::getIdName() && $this->getId() !== null) {
            throw new PrimaryKeyChangeException('Entity primary key can\'t be changed.');
        }

        parent::setPropertyValue($name, $value);
    }

    /**
     * @param int $value
     * @param bool $markAsNewRecord
     * @throws PrimaryKeyChangeException
     * @throws \Pavher\Sdao\Exceptions\UndeclaredPropertyException
     */
    public function setId(int $value, bool $markAsNewRecord = false): void
    {
        $this->setPropertyValue(static::getIdName(), $value);
        $this->isNewRecord = $markAsNewRecord;
    }


    public function __clone()
    {
        unset($this->propertyArray[static::getIdName()]);

        $this->isNewRecord = true;
        $this->changed = false;
    }

    //</editor-fold>
}