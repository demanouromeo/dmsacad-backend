<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StudentSubject
 * 
 * @property int $student_subject_id
 * @property int $sy_id
 * @property int $stud_id
 * @property int $subject_id
 * @property int $sequence
 * @property string|null $eval_date
 * @property float $mark
 * @property int|null $isEmpty
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Student $student
 * @property Subject $subject
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class StudentSubject extends Model
{
	protected $table = 'student_subject';
	protected $primaryKey = 'student_subject_id';

	protected $casts = [
		'sy_id' => 'int',
		'stud_id' => 'int',
		'subject_id' => 'int',
		'sequence' => 'int',
		'mark' => 'float',
		'isEmpty' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'stud_id',
		'subject_id',
		'sequence',
		'eval_date',
		'mark',
		'isEmpty',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function student()
	{
		return $this->belongsTo(Student::class, 'stud_id');
	}

	public function subject()
	{
		return $this->belongsTo(Subject::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
