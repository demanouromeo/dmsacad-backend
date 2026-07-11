<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Staff
 * 
 * @property int $staff_id
 * @property string $name
 * @property string|null $surname
 * @property int|null $phone1
 * @property int|null $phone2
 * @property int $function
 * @property string|null $sexe
 * @property string|null $photo
 * @property string|null $matricule
 * @property int|null $status
 * @property string|null $civility
 * @property string|null $dob
 * @property string|null $pob
 * @property string|null $posting_decision
 * @property int|null $longivity
 * @property int $acc_id
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
 * @property Account $account
 * @property Collection|ClasseYear[] $classe_years
 * @property Collection|StaffCourse[] $staff_courses
 * @property Collection|StaffYear[] $staff_years
 * @property Collection|SubjectClasse[] $subject_classes
 *
 * @package App\Models
 */
class Staff extends Model
{
	protected $table = 'staff';
	protected $primaryKey = 'staff_id';

	protected $casts = [
		'phone1' => 'int',
		'phone2' => 'int',
		'function' => 'int',
		'status' => 'int',
		'longivity' => 'int',
		'acc_id' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'name',
		'surname',
		'phone1',
		'phone2',
		'function',
		'sexe',
		'photo',
		'matricule',
		'status',
		'civility',
		'dob',
		'pob',
		'posting_decision',
		'longivity',
		'acc_id',
		'str1',
		'str2',
		'str3',
		'str4',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	public function account()
	{
		return $this->belongsTo(Account::class, 'acc_id');
	}

	public function classe_years()
	{
		return $this->hasMany(ClasseYear::class, 'sg_id');
	}

	public function staff_courses()
	{
		return $this->hasMany(StaffCourse::class);
	}

	public function staff_years()
	{
		return $this->hasMany(StaffYear::class);
	}

	public function subject_classes()
	{
		return $this->belongsToMany(SubjectClasse::class, 'subject_classe_staff')
					->withPivot('id', 'str1', 'str2', 'val1', 'val2')
					->withTimestamps();
	}
}
