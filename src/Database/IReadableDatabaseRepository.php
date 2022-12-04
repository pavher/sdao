<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 5.12.2018
 */

namespace Pavher\Sdao\Database;

use Nette\Database\ResultSet;
use Pavher\Sdao\IRepository;

interface IReadableDatabaseRepository extends IRepository
{
    public function get(ResultSet $resultSet);
}