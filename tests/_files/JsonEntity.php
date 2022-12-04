<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-03-16
 */

namespace Pavher\Sdao\Tests\_files;

use Pavher\Sdao\Entity;

/**
 * @property int $id_json
 * @property string $json_string
 * @property int $json_int
 * @property bool $json_boolean
 * @property \DateTime $json_date
 */
class JsonEntity extends Entity
{

}