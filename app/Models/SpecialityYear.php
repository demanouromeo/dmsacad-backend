<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpecialityYear
 * 
 * @property int $speciality_year_id
 * @property int $speciality_id
 * @property int $sy_id
 * @property int $filiere_id
 * @property int $section_id
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * 
 * @property SchoolYear $school_year
 * @property Speciality $speciality
 * @property Section $section
 *
 * @package App\Models
 */
class SpecialityYear extends Model
{
	protected $table = 'speciality_year';
	protected $primaryKey = 'speciality_year_id';
	public $timestamps = false;

	protected $casts = [
		'speciality_id' => 'int',
		'sy_id' => 'int',
		'filiere_id' => 'int',
		'section_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'speciality_id',
		'sy_id',
		'filiere_id',
		'section_id',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function speciality()
	{
		return $this->belongsTo(Speciality::class);
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}
}
