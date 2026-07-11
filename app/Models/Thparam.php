<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Thparam
 * 
 * @property int $th_id
 * @property float $lb
 * @property float $ub
 * @property float $lb_default
 * @property float $ub_default
 * @property int|null $seuil_abs
 * @property int $seuil_abs_default
 * @property int|null $sy_id
 * @property string|null $str1
 * @property string|null $str2
 * @property string|null $str3
 * @property string|null $str4
 * @property int|null $val1
 * @property int|null $val2
 * @property int|null $val3
 * @property int|null $val4
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SchoolYear|null $school_year
 *
 * @package App\Models
 */
class Thparam extends Model
{
	protected $table = 'thparam';
	protected $primaryKey = 'th_id';
	public $incrementing = false;

	protected $casts = [
		'th_id' => 'int',
		'lb' => 'float',
		'ub' => 'float',
		'lb_default' => 'float',
		'ub_default' => 'float',
		'seuil_abs' => 'int',
		'seuil_abs_default' => 'int',
		'sy_id' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'lb',
		'ub',
		'lb_default',
		'ub_default',
		'seuil_abs',
		'seuil_abs_default',
		'sy_id',
		'str1',
		'str2',
		'str3',
		'str4',
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
