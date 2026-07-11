<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StudCompMark
 * 
 * @property int $stud_comp_mark_id
 * @property int $sy_id
 * @property int $term_id
 * @property int $subject_id
 * @property int $subject_competence_id
 * @property int $stud_id
 * @property float $mark
 * @property int|null $isEmpty
 * @property float|null $val1
 * @property float|null $val2
 * @property string|null $str1
 * @property string|null $str2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SubjectCompetence $subject_competence
 * @property Student $student
 * @property SchoolYear $school_year
 * @property Subject $subject
 *
 * @package App\Models
 */
class StudCompMark extends Model
{
	protected $table = 'stud_comp_mark';
	protected $primaryKey = 'stud_comp_mark_id';

	protected $casts = [
		'sy_id' => 'int',
		'term_id' => 'int',
		'subject_id' => 'int',
		'subject_competence_id' => 'int',
		'stud_id' => 'int',
		'mark' => 'float',
		'isEmpty' => 'int',
		'val1' => 'float',
		'val2' => 'float'
	];

	protected $fillable = [
		'sy_id',
		'term_id',
		'subject_id',
		'subject_competence_id',
		'stud_id',
		'mark',
		'isEmpty',
		'val1',
		'val2',
		'str1',
		'str2'
	];

	public function subject_competence()
	{
		return $this->belongsTo(SubjectCompetence::class);
	}

	public function student()
	{
		return $this->belongsTo(Student::class, 'stud_id');
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function subject()
	{
		return $this->belongsTo(Subject::class);
	}
}
