<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApcLevel
 * 
 * @property int $apc_level_id
 * @property int $sy_id
 * @property int $level
 * @property int|null $activated
 * 
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class ApcLevel extends Model
{
	protected $table = 'apc_level';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'apc_level_id' => 'int',
		'sy_id' => 'int',
		'level' => 'int',
		'activated' => 'int'
	];

	protected $fillable = [
		'apc_level_id',
		'sy_id',
		'level',
		'activated'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
