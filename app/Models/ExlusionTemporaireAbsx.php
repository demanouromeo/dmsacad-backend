<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExlusionTemporaireAbsx
 * 
 * @property int $id
 * @property int $sy_id
 * @property int $abs
 * @property int $x
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class ExlusionTemporaireAbsx extends Model
{
	protected $table = 'exlusion_temporaire_absx';

	protected $casts = [
		'sy_id' => 'int',
		'abs' => 'int',
		'x' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'abs',
		'x'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
