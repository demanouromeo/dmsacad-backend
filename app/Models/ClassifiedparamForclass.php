<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClassifiedparamForclass
 * 
 * @property int $id
 * @property int $sy_id
 * @property int $classe_id
 * @property int $nb_matieres_rate
 * @property int $total_coef_rate
 * @property bool $classified
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
 * @property Classe $classe
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class ClassifiedparamForclass extends Model
{
	protected $table = 'classifiedparam_forclass';

	protected $casts = [
		'sy_id' => 'int',
		'classe_id' => 'int',
		'nb_matieres_rate' => 'int',
		'total_coef_rate' => 'int',
		'classified' => 'bool',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'classe_id',
		'nb_matieres_rate',
		'total_coef_rate',
		'classified',
		'str1',
		'str2',
		'str3',
		'str4',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	public function classe()
	{
		return $this->belongsTo(Classe::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
