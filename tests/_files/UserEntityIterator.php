<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 13.12.2018
 */

namespace Pavher\Sdao\Tests\_files;


use Pavher\Sdao\Database\DatabaseEntityIterator;

class UserEntityIterator extends DatabaseEntityIterator
{
    public function current(): User
    {
        return parent::current();
    }

}