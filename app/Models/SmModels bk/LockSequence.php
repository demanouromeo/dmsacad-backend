<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LockSequence
 * 
 * @property int $id
 * @property int $seq
 * @property int $sy_id
 * @property bool|null $is_blocked
 * @property bool|null $is_lock_classbased
 * @property int|null $val1
 * @property int|null $val2
 * @property int|null $val3
 * @property int|null $val4
 * @property string|null $str1
 * @property string|null $str2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class LockSequence extends Model
{
	protected $table = 'lock_sequence';

	protected $casts = [
		'seq' => 'int',
		'sy_id' => 'int',
		'is_blocked' => 'bool',
		'is_lock_classbased' => 'bool',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'seq',
		'sy_id',
		'is_blocked',
		'is_lock_classbased',
		'val1',
		'val2',
		'val3',
		'val4',
		'str1',
		'str2'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
