<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subject
 * 
 * @property int $subject_id
 * @property string $subject_title
 * @property string|null $subject_code
 * @property string|null $str1
 * @property string|null $str2
 * @property string|null $str3
 * @property string|null $str4
 * @property int|null $val1
 * @property int|null $val2
 * @property int|null $val3
 * @property int|null $val4
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AbsenceDaily[] $absence_dailies
 * @property Collection|StaffCourse[] $staff_courses
 * @property Collection|StudCompMark[] $stud_comp_marks
 * @property Collection|Student[] $students
 * @property Collection|Classe[] $classes
 * @property Collection|SubjectCompetence[] $subject_competences
 * @property Collection|SubjectYear[] $subject_years
 *
 * @package App\Models
 */
class Subject extends Model
{
	protected $table = 'subject';
	protected $primaryKey = 'subject_id';

	protected $casts = [
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'subject_title',
		'subject_code',
		'str1',
		'str2',
		'str3',
		'str4',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	public function absence_dailies()
	{
		return $this->hasMany(AbsenceDaily::class);
	}

	public function staff_courses()
	{
		return $this->hasMany(StaffCourse::class);
	}

	public function stud_comp_marks()
	{
		return $this->hasMany(StudCompMark::class);
	}

	public function students()
	{
		return $this->belongsToMany(Student::class, 'student_subject', 'subject_id', 'stud_id')
					->withPivot('student_subject_id', 'sy_id', 'sequence', 'eval_date', 'mark', 'isEmpty', 'str1', 'str2', 'val1', 'val2')
					->withTimestamps();
	}

	public function classes()
	{
		return $this->belongsToMany(Classe::class, 'subject_classe')
					->withPivot('subject_classe_id', 'coef', 'sy_id', 'groupe_id', 'section_id', 'str1', 'str2', 'val1', 'val2')
					->withTimestamps();
	}

	public function subject_competences()
	{
		return $this->hasMany(SubjectCompetence::class);
	}

	public function subject_years()
	{
		return $this->hasMany(SubjectYear::class);
	}
}
