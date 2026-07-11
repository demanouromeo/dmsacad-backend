<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StaffYear
 * 
 * @property int $staff_year_id
 * @property int $staff_id
 * @property int $sy_id
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Staff $staff
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class StaffYear extends Model
{
	protected $table = 'staff_year';
	protected $primaryKey = 'staff_year_id';

	protected $casts = [
		'staff_id' => 'int',
		'sy_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'staff_id',
		'sy_id',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function staff()
	{
		return $this->belongsTo(Staff::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
