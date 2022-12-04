<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2020-02-05
 */

namespace Pavher\Sdao\Tests\_files;

use Pavher\Sdao\Entity;

/**
 * @property int $custom_int_property
 * @property bool $custom_bool_property
 * @property string $custom_string_property
 * @property \DateTime $custom_date_property
 */
class SimpleEntityWithDefaultData extends Entity
{
    /**
     * @return array
     */
    protected function defaultData(): array
    {
        return ['custom_string_property' => 'default string'];
    }

}