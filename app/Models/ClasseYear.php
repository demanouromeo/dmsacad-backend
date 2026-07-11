<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClasseYear
 * 
 * @property int $classe_year_id
 * @property int $sy_id
 * @property int $classe_id
 * @property int|null $section_id
 * @property int|null $speciality_id
 * @property int|null $classe_master
 * @property int|null $sg_id
 * @property bool $basculated
 * @property int $isTriBlocked
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Classe $classe
 * @property Staff|null $staff
 * @property Section|null $section
 * @property SchoolYear $school_year
 * @property Speciality|null $speciality
 *
 * @package App\Models
 */
class ClasseYear extends Model
{
	protected $table = 'classe_year';
	protected $primaryKey = 'classe_year_id';

	protected $casts = [
		'sy_id' => 'int',
		'classe_id' => 'int',
		'section_id' => 'int',
		'speciality_id' => 'int',
		'classe_master' => 'int',
		'sg_id' => 'int',
		'basculated' => 'bool',
		'isTriBlocked' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'classe_id',
		'section_id',
		'speciality_id',
		'classe_master',
		'sg_id',
		'basculated',
		'isTriBlocked',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function classe()
	{
		return $this->belongsTo(Classe::class);
	}

	public function staff()
	{
		return $this->belongsTo(Staff::class, 'sg_id');
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function speciality()
	{
		return $this->belongsTo(Speciality::class);
	}
}
