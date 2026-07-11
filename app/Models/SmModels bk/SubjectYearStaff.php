<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SubjectYearStaff
 * 
 * @property int $subject_year_staff_id
 * @property int $subject_year_id
 * @property int $staff_year_id
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property StaffYear $staff_year
 * @property SubjectYear $subject_year
 *
 * @package App\Models
 */
class SubjectYearStaff extends Model
{
	protected $table = 'subject_year_staff';
	protected $primaryKey = 'subject_year_staff_id';

	protected $casts = [
		'subject_year_id' => 'int',
		'staff_year_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'subject_year_id',
		'staff_year_id',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function staff_year()
	{
		return $this->belongsTo(StaffYear::class);
	}

	public function subject_year()
	{
		return $this->belongsTo(SubjectYear::class);
	}
}
