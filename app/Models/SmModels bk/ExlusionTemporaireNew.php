<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExlusionTemporaireNew
 * 
 * @property int $id
 * @property int $sy_id
 * @property int $a1
 * @property int $b1
 * @property int $x1
 * @property int $a2
 * @property int $b2
 * @property int $x2
 * @property int $a3
 * @property int $b3
 * @property int $x3
 * @property int $a4
 * @property int $b4
 * @property int $x4
 * @property int|null $str1
 * @property int|null $str2
 * @property int $val1
 * @property int $val2
 * @property int $val3
 * @property int $val4
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class ExlusionTemporaireNew extends Model
{
	protected $table = 'exlusion_temporaire_new';

	protected $casts = [
		'sy_id' => 'int',
		'a1' => 'int',
		'b1' => 'int',
		'x1' => 'int',
		'a2' => 'int',
		'b2' => 'int',
		'x2' => 'int',
		'a3' => 'int',
		'b3' => 'int',
		'x3' => 'int',
		'a4' => 'int',
		'b4' => 'int',
		'x4' => 'int',
		'str1' => 'int',
		'str2' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'a1',
		'b1',
		'x1',
		'a2',
		'b2',
		'x2',
		'a3',
		'b3',
		'x3',
		'a4',
		'b4',
		'x4',
		'str1',
		'str2',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
