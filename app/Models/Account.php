<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * 
 * @property int $acc_id
 * @property string $login
 * @property string $pwd
 * @property int $type
 * @property string|null $email
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Administrateur[] $administrateurs
 * @property Staff $staff
 * @property Collection|StudParent[] $stud_parents
 * @property Collection|Student[] $students
 *
 * @package App\Models
 */
class Account extends Model
{
	protected $table = 'account';
	protected $primaryKey = 'acc_id';

	protected $casts = [
		'type' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'login',
		'pwd',
		'type',
		'email',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function administrateurs()
	{
		return $this->hasMany(Administrateur::class, 'acc_id');
	}

	public function staff()
	{
		return $this->hasOne(Staff::class, 'acc_id');
	}

	public function stud_parents()
	{
		return $this->hasMany(StudParent::class, 'acc_id');
	}

	public function students()
	{
		return $this->hasMany(Student::class, 'acc_id');
	}
}
