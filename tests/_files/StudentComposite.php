<?php
declare(strict_types=1);

namespace Pavher\Sdao\Tests\_files;
use Pavher\Sdao\Database\CompositeDatabaseEntity;

/**
 * @property int $id_student
 * @property string $student_name
 * @property string $student_surname
 * @property string $study_programme_name
 */
class StudentComposite extends CompositeDatabaseEntity
{

}