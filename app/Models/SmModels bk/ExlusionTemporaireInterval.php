<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExlusionTemporaireInterval
 * 
 * @property int $id
 * @property int $sy_id
 * @property int $a
 * @property int $b
 * @property int|null $x
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class ExlusionTemporaireInterval extends Model
{
	protected $table = 'exlusion_temporaire_interval';

	protected $casts = [
		'sy_id' => 'int',
		'a' => 'int',
		'b' => 'int',
		'x' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'a',
		'b',
		'x'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
