<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbsenceDaily
 * 
 * @property int $absence_daily_id
 * @property int $stud_id
 * @property int $subject_id
 * @property int $sequence
 * @property string $abs_day
 * @property string $abs_time
 * @property int|null $sy_id
 * @property int|null $number_of_absences
 * @property int|null $blame
 * @property int|null $lateness
 * @property string|null $observation
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Student $student
 * @property SchoolYear|null $school_year
 * @property Subject $subject
 *
 * @package App\Models
 */
class AbsenceDaily extends Model
{
	protected $table = 'absence_daily';
	protected $primaryKey = 'absence_daily_id';

	protected $casts = [
		'stud_id' => 'int',
		'subject_id' => 'int',
		'sequence' => 'int',
		'sy_id' => 'int',
		'number_of_absences' => 'int',
		'blame' => 'int',
		'lateness' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'stud_id',
		'subject_id',
		'sequence',
		'abs_day',
		'abs_time',
		'sy_id',
		'number_of_absences',
		'blame',
		'lateness',
		'observation',
		'str1',
		'str2',
		'val1',
		'val2'
	];

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
