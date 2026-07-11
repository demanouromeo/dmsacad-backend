<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Appreciation
 * 
 * @property int $appreciation_id
 * @property int $stud_id
 * @property int $sy_id
 * @property int $term
 * @property int $ctba
 * @property int $cba
 * @property int $ca
 * @property int $cma
 * @property int $cna
 * @property int|null $val1
 * @property int|null $val2
 * @property int|null $val3
 * @property int|null $val4
 * @property int|null $val5
 * @property string|null $str1
 * @property string|null $str2
 * @property string|null $str3
 * @property string|null $str4
 * @property string|null $str5
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Student $student
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class Appreciation extends Model
{
	protected $table = 'appreciation';
	protected $primaryKey = 'appreciation_id';

	protected $casts = [
		'stud_id' => 'int',
		'sy_id' => 'int',
		'term' => 'int',
		'ctba' => 'int',
		'cba' => 'int',
		'ca' => 'int',
		'cma' => 'int',
		'cna' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int',
		'val5' => 'int'
	];

	protected $fillable = [
		'stud_id',
		'sy_id',
		'term',
		'ctba',
		'cba',
		'ca',
		'cma',
		'cna',
		'val1',
		'val2',
		'val3',
		'val4',
		'val5',
		'str1',
		'str2',
		'str3',
		'str4',
		'str5'
	];

	public function student()
	{
		return $this->belongsTo(Student::class, 'stud_id');
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
