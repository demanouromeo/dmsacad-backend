<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SubjectYear
 * 
 * @property int $subject_year_id
 * @property int $sy_id
 * @property int $subject_id
 * @property int $section_id
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Subject $subject
 * @property Section $section
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class SubjectYear extends Model
{
	protected $table = 'subject_year';
	protected $primaryKey = 'subject_year_id';

	protected $casts = [
		'sy_id' => 'int',
		'subject_id' => 'int',
		'section_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'subject_id',
		'section_id',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function subject()
	{
		return $this->belongsTo(Subject::class);
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
