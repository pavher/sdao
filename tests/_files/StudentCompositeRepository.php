<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 29.1.2019
 */

namespace Pavher\Sdao\Tests\_files;


use Pavher\Sdao\Database\ReadonlyDatabaseRepository;

class StudentCompositeRepository extends ReadonlyDatabaseRepository
{
    //<editor-fold desc="Abstract methods implementation">
    protected function getEntityClassName(): string
    {
        return StudentComposite::class;
    }

    protected function getSqlQueryFromClauseForSelect(): string
    {
        return 'student';
    }

    //</editor-fold>

    //<editor-fold desc="Methods - public">

    public function getAllStudentsInActiveCourses(): StudentCompositeIterator
    {
        $query = $this->dbContext->query('SELECT id_student, student.name AS student_name, student.surname AS student_surname, study_programme.name AS study_programme_name 
                    FROM student 
                    JOIN study_programme ON student.id_study_programme=study_programme.id_study_programme
                    WHERE study_programme.is_active = 1');

        return new StudentCompositeIterator($query, $this);
    }

    //</editor-fold>
}