<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApcLevel
 * 
 * @property int $apc_level_id
 * @property int $sy_id
 * @property int $level
 * @property int|null $activated
 * @property int|null $val1
 * @property int|null $val2
 * @property string|null $str1
 * @property string|null $str2
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property int $section_id
 * 
 * @property SchoolYear $school_year
 * @property Section $section
 *
 * @package App\Models
 */
class ApcLevel extends Model
{
	protected $table = 'apc_level';
	public $incrementing = false;

	protected $casts = [
		'apc_level_id' => 'int',
		'sy_id' => 'int',
		'level' => 'int',
		'activated' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'section_id' => 'int'
	];

	protected $fillable = [
		'apc_level_id',
		'sy_id',
		'level',
		'activated',
		'val1',
		'val2',
		'str1',
		'str2',
		'section_id'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}
}
