<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 5.12.2018
 */

namespace Pavher\Sdao\Database;


use Pavher\Sdao\Database\IDatabaseEntityIterator;
use Nette\Database\ResultSet;

class DatabaseEntityIterator implements IDatabaseEntityIterator
{
    /**
     * @var ResultSet
     */
    private $resultSet;

    /**
     * @var \ArrayAccess
     */
    private $rowCollection = [];

    /**
     * @var IReadableDatabaseRepository
     */
    private $databaseRepository;

    /**
     * @var int
     */
    private $key = 0;

    /**
     * DatabaseEntityIterator constructor.
     * @param ResultSet $resultSet
     * @param IReadableDatabaseRepository $databaseRepository
     */
    public function __construct(ResultSet $resultSet, IReadableDatabaseRepository $databaseRepository)
    {
        $this->resultSet = $resultSet;
        $this->databaseRepository = $databaseRepository;
    }


    /**
     * Return the current element
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->rowCollection[$this->key()];
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        $this->key++;
    }

    /**
     * Return the key of the current element
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * Checks if current position is valid
     */
    public function valid(): bool
    {
        if (array_key_exists($this->key(), $this->rowCollection)) {
            return true;
        }

        $result = $this->databaseRepository->get($this->resultSet);
        if ($result !== null) {
            $this->rowCollection[$this->key()] = $result;
            return true;
        }

        return false;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return $this->resultSet->getRowCount() ?? 0;
    }
}