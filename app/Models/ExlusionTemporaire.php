<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExlusionTemporaire
 * 
 * @property int $id
 * @property int $a
 * @property int $b
 * @property int|null $x
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ExlusionTemporaire extends Model
{
	protected $table = 'exlusion_temporaire';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'a' => 'int',
		'b' => 'int',
		'x' => 'int'
	];

	protected $fillable = [
		'a',
		'b',
		'x'
	];
}
