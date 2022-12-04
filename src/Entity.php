<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 1.12.2018
 */

namespace Pavher\Sdao;


use Pavher\Sdao\Exceptions\EntityValidationException;
use Pavher\Sdao\Exceptions\UndeclaredPropertyException;

abstract class Entity extends ReadonlyEntity
{

    //<editor-fold desc="Properties">

    /**
     * @var bool
     */
    protected $changed = false;

    //</editor-fold>

    //<editor-fold desc="Methods - public">

    /**
     * @param array $dataArray
     * @param bool $checkAllProperties
     * @return static
     */
    public static function createFromArray(array $dataArray, bool $checkAllProperties = false)
    {
        return new static($dataArray, $checkAllProperties);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws UndeclaredPropertyException
     */
    public function __set($name, $value)
    {
        $this->setPropertyValue($name, $value);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws UndeclaredPropertyException
     */
    public function setPropertyValue(string $name, $value): void
    {
        $this->performSetProperty($name, $value, true);
        $this->changed = true;
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->changed;
    }

    //</editor-fold>

}