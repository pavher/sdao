<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2.12.2018
 */

namespace Pavher\Sdao;


use Nette\SmartObject;

class PropertyInfo
{
    use SmartObject;

    //<editor-fold desc="Properties">

    /**
     * @var string;
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $owner;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var bool
     */
    protected $isChanged;

    //</editor-fold>

    //<editor-fold desc="Ctor">
    /**
     * PropertyObject constructor.
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @param bool $isChanged
     */
    public function __construct(string $name, string $type, string $owner, $value, bool $isChanged)
    {
        $this->name = $name;
        $this->type = $type;
        $this->owner = $owner;
        $this->value = $value;
        $this->isChanged = $isChanged;
    }
    //</editor-fold>

    //<editor-fold desc="Methods - public">
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->isChanged;
    }
    //</editor-fold>

}