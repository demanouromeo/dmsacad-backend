<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClasseDecisionParam
 * 
 * @property int $id
 * @property int $sy_id
 * @property int $classe_id
 * @property float $avgDissmissalTh
 * @property float $avgTrialPromoTh
 * @property float $absTh
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
 * @property SchoolYear $school_year
 * @property Classe $classe
 *
 * @package App\Models
 */
class ClasseDecisionParam extends Model
{
	protected $table = 'classe_decision_param';

	protected $casts = [
		'sy_id' => 'int',
		'classe_id' => 'int',
		'avgDissmissalTh' => 'float',
		'avgTrialPromoTh' => 'float',
		'absTh' => 'float',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'classe_id',
		'avgDissmissalTh',
		'avgTrialPromoTh',
		'absTh',
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

	public function classe()
	{
		return $this->belongsTo(Classe::class);
	}
}
