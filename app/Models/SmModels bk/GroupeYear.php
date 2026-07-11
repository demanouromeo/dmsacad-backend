<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GroupeYear
 * 
 * @property int $groupe_year_id
 * @property int $sy_id
 * @property int $groupe_id
 * @property int $section_id
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Groupe $groupe
 * @property Section $section
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class GroupeYear extends Model
{
	protected $table = 'groupe_year';
	protected $primaryKey = 'groupe_year_id';

	protected $casts = [
		'sy_id' => 'int',
		'groupe_id' => 'int',
		'section_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'groupe_id',
		'section_id',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function groupe()
	{
		return $this->belongsTo(Groupe::class);
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
