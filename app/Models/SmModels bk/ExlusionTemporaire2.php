<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExlusionTemporaire2
 * 
 * @property int $id
 * @property int $val
 * @property int $x
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ExlusionTemporaire2 extends Model
{
	protected $table = 'exlusion_temporaire2';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'val' => 'int',
		'x' => 'int'
	];

	protected $fillable = [
		'val',
		'x'
	];
}
