<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Administrateur
 * 
 * @property int $admin_id
 * @property int $acc_id
 * @property string $name
 * @property string|null $email
 * @property int|null $phone
 * @property string|null $photo
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Account $account
 *
 * @package App\Models
 */
class Administrateur extends Model
{
	protected $table = 'administrateur';
	protected $primaryKey = 'admin_id';

	protected $casts = [
		'acc_id' => 'int',
		'phone' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'acc_id',
		'name',
		'email',
		'phone',
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
}
