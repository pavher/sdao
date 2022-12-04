<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-02-08
 */

namespace Pavher\Sdao\Exceptions;


use Throwable;

class ValidationException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $validationResultArray;

    public function __construct($message = "", $validationResultArray = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->validationResultArray = $validationResultArray;
    }

    /**
     * @return array
     */
    public function getResultArray(): array
    {
        return $this->validationResultArray;
    }

    /**
     * @return array
     */
    public function getResultArraySimple(): array
    {
        return array_merge([], ...array_values($this->validationResultArray));
    }

}