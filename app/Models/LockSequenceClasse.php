<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LockSequenceClasse
 * 
 * @property int $id
 * @property int $sy_id
 * @property int $seq
 * @property int $classe_id
 * @property bool|null $is_blocked
 * @property int|null $val1
 * @property int|null $val2
 * @property int|null $val3
 * @property int|null $val4
 * @property string|null $str1
 * @property string|null $str2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Classe $classe
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class LockSequenceClasse extends Model
{
	protected $table = 'lock_sequence_classe';

	protected $casts = [
		'sy_id' => 'int',
		'seq' => 'int',
		'classe_id' => 'int',
		'is_blocked' => 'bool',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'seq',
		'classe_id',
		'is_blocked',
		'val1',
		'val2',
		'val3',
		'val4',
		'str1',
		'str2'
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
