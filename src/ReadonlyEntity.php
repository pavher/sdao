<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 16.1.2018
 */

namespace Pavher\Sdao;

use Nette\Utils\DateTime;
use Pavher\Sdao\Exceptions\EntityConfigurationException;
use Pavher\Sdao\Exceptions\ReadonlyEntityChangeException;
use Pavher\Sdao\Exceptions\UndeclaredPropertyException;
use Pavher\Sdao\Exceptions\UnsetPropertyException;
use Pavher\Sdao\Utils\EntityPropertiesParser;

abstract class ReadonlyEntity implements IEntity, \JsonSerializable
{
    //<editor-fold desc="Consts">

    private const ENTITY_PROPERTY_VALUE_KEY = "value";
    protected const ENTITY_PROPERTY_IS_CHANGED_KEY = "is_changed";

    //</editor-fold>

    //<editor-fold desc="Properties">

    /**
     * @var array
     */
    private static $entityPropertiesCache = [];

    /**
     * @var array
     */
    protected $propertyArray = [];

    //</editor-fold>

    //<editor-fold desc="Ctor">

    /**
     * Entity constructor.
     * @param array|\ArrayAccess $data Initial data collection.
     * @param bool $checkAllProperties
     * @throws EntityConfigurationException
     * @throws UndeclaredPropertyException
     * @throws UnsetPropertyException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data = null, bool $checkAllProperties = true)
    {
        if ($data !== null && (!is_array($data) && !$data instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException('$data must be type of array or ArrayAccess');
        }

        $defaultData = $this->defaultData();

        if($defaultData !== null) {
            if(!is_array($defaultData) && !$defaultData instanceof \ArrayAccess) {
                throw new EntityConfigurationException('$defaultData must be type of array or ArrayAccess');
            }

            foreach ($defaultData as $defaultKey => $defaultVal) {
                $this->performSetProperty($defaultKey, $defaultVal, false, $checkAllProperties);
            }
        }

        if($data !== null) {
            $propertiesConfArray = $this->getEntityPropertiesConfigurationArray();
            $unsetPropertyKeys = [];
            foreach ($propertiesConfArray as $propName => $propValue) {
                if ($checkAllProperties && !array_key_exists($propName, $data)) {
                    $unsetPropertyKeys[] = $propName;
                } else if (array_key_exists($propName, $data)) {
                    $this->performSetProperty($propName, $data[$propName],  false, $checkAllProperties);
                    unset($data[$propName]);
                }
            }

            if (count($unsetPropertyKeys) > 0) {
                // some unset properties
                throw new UnsetPropertyException(sprintf('Failed to create entity instance: %s, some unset entity properties: [%s]',
                    get_class($this), implode(',', $unsetPropertyKeys)));
            }

            if (count(array_keys($data)) > 0) {
                // some extra property
                throw new UndeclaredPropertyException(sprintf('Failed to create entity instance: %s, undeclared entity properties: [%s]',
                    get_class($this), implode(',', array_keys($data))));
            }
        }
    }

    //</editor-fold>

    //<editor-fold desc="Methods - public">

    /**
     * @param string $json
     * @param bool $checkAllProperties
     * @return static
     */
    public static function createFromJson(string $json, bool $checkAllProperties = false)
    {
        return new static(json_decode($json, true), $checkAllProperties);
    }

    /**
     * @return string
     */
    public static function getEntityName(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }

    /**
     * @param string $name Property name.
     * @return mixed Property value.
     * @throws UndeclaredPropertyException
     */
    public function __get($name)
    {
        return $this->getPropertyValue($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws ReadonlyEntityChangeException
     */
    public function __set($name, $value)
    {
        throw new ReadonlyEntityChangeException(sprintf('%s - Illegal ReadonlyEntity operation. Property (%s) can\'t be assigned or modified by set method.',
            get_class($this), $name));
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public function __isset($propertyName)
    {
        try {
            $this->getEntityPropertyConfigurationArray($propertyName);
            return true;
        } catch (UndeclaredPropertyException $e) {
            return false;
        }
    }

    /**
     * @param string $name Property name.
     * @param bool $serializeObjects
     * @return mixed Property value.
     * @throws UndeclaredPropertyException
     */
    public function getPropertyValue(string $name, $serializeObjects = false)
    {
        // get property value from values array
        if (isset($this->propertyArray[$name][self::ENTITY_PROPERTY_VALUE_KEY])) {

            $value = $this->propertyArray[$name][self::ENTITY_PROPERTY_VALUE_KEY];

            if ($serializeObjects && $value instanceof IEntity) {
                return json_encode($value->asArray(null, true));
            }

            return $value;
        }

        // check whether property is declared, throw exception otherwise
        $this->getEntityPropertyConfigurationArray($name);

        return null;
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return false;
    }

    /**
     * Get property information object.
     * @param string $propertyName
     * @return PropertyInfo
     * @throws UndeclaredPropertyException
     */
    public function getPropertyInfo(string $propertyName): PropertyInfo
    {
        $propertyConfigurationArray = $this->getEntityPropertyConfigurationArray($propertyName);

        $value = $this->getPropertyValue($propertyName);
        $type = $propertyConfigurationArray[EntityPropertiesParser::ENTITY_PROPERTY_PARSER_TYPE_KEY];
        $owner = $propertyConfigurationArray[EntityPropertiesParser::ENTITY_PROPERTY_PARSER_OWNER_KEY];
        $isChanged = $this->propertyArray[$propertyName][self::ENTITY_PROPERTY_IS_CHANGED_KEY] ?? false;

        return new PropertyInfo($propertyName, $type, $owner, $value, $isChanged);
    }

    /**
     * Returns property data as simple property=>value array.
     * @param string|null $ownerEntityName
     * @param bool $serializeObjects
     * @param bool $changedOnly
     * @return array
     */
    public function asArray(
        ?string $ownerEntityName = null,
        bool $serializeObjects = false,
        bool $changedOnly = false
    ): array {
        $entityPropertiesConfigurationArray = $this->getEntityPropertiesConfigurationArray();
        $resultArray = [];

        foreach ($entityPropertiesConfigurationArray as $propertyConfiguration) {
            $propertyName = $propertyConfiguration[EntityPropertiesParser::ENTITY_PROPERTY_PARSER_NAME_KEY];

            if (($ownerEntityName === null || $propertyConfiguration[EntityPropertiesParser::ENTITY_PROPERTY_PARSER_OWNER_KEY] === $ownerEntityName) && ($changedOnly === false || ($this->propertyArray[$propertyName][self::ENTITY_PROPERTY_IS_CHANGED_KEY] ?? false))) {
                $resultArray[$propertyName] = $this->getPropertyValue($propertyName, $serializeObjects);
            }
        }

        return $resultArray;
    }
    //</editor-fold>


    //<editor-fold desc="Methods - protected">

    /**
     * @return array|null
     */
    protected function defaultData(): ?array
    {
        return null;
    }

    /**
     * @param string $name Property name.
     * @param mixed $value Property value.
     * @param bool $setChanged
     * @param bool $checkAllNestedProperties
     */
    protected function performSetProperty(string $name, $value, bool $setChanged = false, $checkAllNestedProperties = true): void
    {
        $castValue = null;

        if ($value !== null && $value !== '') {
            $type = $this->getEntityPropertyConfigurationArray($name)[EntityPropertiesParser::ENTITY_PROPERTY_PARSER_TYPE_KEY];
            $isArrayOfType = $this->getEntityPropertyConfigurationArray($name)[EntityPropertiesParser::ENTITY_PROPERTY_PARSER_TYPE_IS_ARRAY_KEY];
            switch ($type) {
                case "int":
                case "integer":
                    $castValue = (int)$value;
                    break;
                case "double":
                case "float":
                case "real":
                    $castValue = (double)$value;
                    break;
                case "bool":
                case "boolean":
                    $castValue = (bool)$value;
                    break;
                case "string":
                    $castValue = (string)$value;
                    break;
                case "DateTime":
                case "\DateTime":
                    if ($value instanceof \DateTimeImmutable) {
                        $castValue = \DateTime::createFromFormat(
                            \DateTimeInterface::ATOM,
                            $value->format(\DateTimeInterface::ATOM)
                        );
                    } elseif ($value instanceof \DateTime) {
                        $castValue = $value;
                    } elseif (is_array($value) && isset($value["date"])) {
                        // deserialized json assoc array
                        $castValue = new DateTime($value["date"]);
                    } else {
                        // db date string
                        $castValue = new DateTime($value);
                    }
                    break;
                case "array":
                    if (is_string($value)) {
                        $castValue = unserialize($value, null);
                    } else {
                        $castValue = $value;
                    }
                    break;
                default:
                    if($isArrayOfType && class_exists($type) && isset(class_implements($type)['Pavher\Sdao\IEntity'])) {
                        // for annotation like ImageData[] serialize as array of types
                        $castValue = [];
                        if($value !== null) {
                            $arr = [];
                            if(is_array($value)) {
                                $arr = $value;
                            } else if(is_string($value)) {
                                $arr = json_decode($value, true);
                            }
                            foreach ($arr as $key => $item) {
                                $castValue[$key] = new $type($item, $checkAllNestedProperties);
                            }
                        }
                    } else if (class_exists($type) && isset(class_implements($type)['Pavher\Sdao\IEntity']) && is_array($value)) {
                        $castValue = new $type($value, $checkAllNestedProperties);
                    } else if (class_exists($type) && isset(class_implements($type)['Pavher\Sdao\IEntity']) && is_string($value)) {
                        $castValue = new $type(json_decode($value, true), $checkAllNestedProperties);
                    } else {
                        $castValue = $value;
                    }
            }
        }

        $this->propertyArray[$name] = [
            self::ENTITY_PROPERTY_VALUE_KEY => $castValue,
            self::ENTITY_PROPERTY_IS_CHANGED_KEY => $setChanged
        ];
    }

    //</editor-fold>

    //<editor-fold desc="Methods - private">

    /**
     * @return array of property information name => [name =>, type =>]
     */
    private function getEntityPropertiesConfigurationArray(): array
    {
        $entityName = static::getEntityName();
        if (!array_key_exists($entityName, self::$entityPropertiesCache)) {
            self::$entityPropertiesCache[$entityName] = EntityPropertiesParser::processPHPDocClass(new \ReflectionClass($this));
        }

        return self::$entityPropertiesCache[$entityName];
    }

    /**
     * @param string $propertyName
     * @return array
     * @throws UndeclaredPropertyException
     */
    private function getEntityPropertyConfigurationArray(string $propertyName): array
    {
        $entityName = static::getEntityName();
        $entityPropertiesConfigurationArray = $this->getEntityPropertiesConfigurationArray();

        if (!array_key_exists($propertyName, $entityPropertiesConfigurationArray)) {
            throw new UndeclaredPropertyException(sprintf('Undeclared %s entity property: %s', $entityName,
                $propertyName));
        }

        return self::$entityPropertiesCache[$entityName][$propertyName];
    }

    public function jsonSerialize()
    {
        $res = [];
        $arr = get_object_vars($this);
        foreach ($arr["propertyArray"] as $key => $item) {
            $res[$key] = $item["value"];
        }
        return $res;
    }

    //</editor-fold>


}