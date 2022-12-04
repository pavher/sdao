<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 7.12.2018
 */

namespace Pavher\Sdao\Tests\_files;


use Pavher\Sdao\Database\DatabaseEntity;

/**
 * @property int $id_user
 * @property string $name
 * @property string $surname
 * @property bool $is_active
 * @property \DateTime $created
 * @property int $default_col
 */
class User extends DatabaseEntity
{

}