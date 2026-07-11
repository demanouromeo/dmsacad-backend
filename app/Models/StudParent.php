<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StudParent
 * 
 * @property int $p_id
 * @property string|null $p_name
 * @property string|null $p_surname
 * @property int $p_phone1
 * @property int|null $p_phone2
 * @property int|null $p_phone3
 * @property int|null $whatapp
 * @property int $acc_id
 * @property string|null $photo
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Account $account
 * @property Collection|Student[] $students
 *
 * @package App\Models
 */
class StudParent extends Model
{
	protected $table = 'stud_parent';
	protected $primaryKey = 'p_id';

	protected $casts = [
		'p_phone1' => 'int',
		'p_phone2' => 'int',
		'p_phone3' => 'int',
		'whatapp' => 'int',
		'acc_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'p_name',
		'p_surname',
		'p_phone1',
		'p_phone2',
		'p_phone3',
		'whatapp',
		'acc_id',
		'photo',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'acc_id');
	}

	public function students()
	{
		return $this->hasMany(Student::class, 'p_id');
	}
}
