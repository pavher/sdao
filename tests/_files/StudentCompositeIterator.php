<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 29.1.2019
 */

namespace Pavher\Sdao\Tests\_files;


use Pavher\Sdao\Database\DatabaseEntityIterator;

class StudentCompositeIterator extends DatabaseEntityIterator
{
    public function current(): StudentComposite
    {
        return parent::current();
    }

}