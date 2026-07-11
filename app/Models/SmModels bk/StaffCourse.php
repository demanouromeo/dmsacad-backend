<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StaffCourse
 * 
 * @property int $staff_course_id
 * @property int $sy_id
 * @property int $staff_id
 * @property int $subject_id
 * @property int $section_id
 * @property string|null $str1
 * @property string|null $str2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SchoolYear $school_year
 * @property Subject $subject
 * @property Staff $staff
 * @property Section $section
 *
 * @package App\Models
 */
class StaffCourse extends Model
{
	protected $table = 'staff_course';
	protected $primaryKey = 'staff_course_id';

	protected $casts = [
		'sy_id' => 'int',
		'staff_id' => 'int',
		'subject_id' => 'int',
		'section_id' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'staff_id',
		'subject_id',
		'section_id',
		'str1',
		'str2'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function subject()
	{
		return $this->belongsTo(Subject::class);
	}

	public function staff()
	{
		return $this->belongsTo(Staff::class);
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}
}
