<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Student
 * 
 * @property int $stud_id
 * @property string|null $matricule
 * @property string $name
 * @property string|null $surname
 * @property string|null $bday
 * @property string|null $bplace
 * @property string $sexe
 * @property string|null $photo
 * @property int|null $p_id
 * @property int|null $acc_id
 * @property Carbon|null $regDateTime
 * @property int|null $handicape
 * @property int|null $position
 * @property int|null $val1
 * @property int|null $val2
 * @property string|null $st1
 * @property string|null $str2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property StudParent|null $stud_parent
 * @property Account|null $account
 * @property Collection|AbsenceDaily[] $absence_dailies
 * @property Collection|Appreciation[] $appreciations
 * @property Collection|Discipline[] $disciplines
 * @property Collection|StudCompMark[] $stud_comp_marks
 * @property Collection|Classe[] $classes
 * @property Collection|Subject[] $subjects
 *
 * @package App\Models
 */
class Student extends Model
{
	protected $table = 'student';
	protected $primaryKey = 'stud_id';

	protected $casts = [
		'p_id' => 'int',
		'acc_id' => 'int',
		'regDateTime' => 'datetime',
		'handicape' => 'int',
		'position' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'matricule',
		'name',
		'surname',
		'bday',
		'bplace',
		'sexe',
		'photo',
		'p_id',
		'acc_id',
		'regDateTime',
		'handicape',
		'position',
		'val1',
		'val2',
		'st1',
		'str2'
	];

	public function stud_parent()
	{
		return $this->belongsTo(StudParent::class, 'p_id');
	}

	public function account()
	{
		return $this->belongsTo(Account::class, 'acc_id');
	}

	public function absence_dailies()
	{
		return $this->hasMany(AbsenceDaily::class, 'stud_id');
	}

	public function appreciations()
	{
		return $this->hasMany(Appreciation::class, 'stud_id');
	}

	public function disciplines()
	{
		return $this->hasMany(Discipline::class, 'stud_id');
	}

	public function stud_comp_marks()
	{
		return $this->hasMany(StudCompMark::class, 'stud_id');
	}

	public function classes()
	{
		return $this->belongsToMany(Classe::class, 'student_classe', 'stud_id')
					->withPivot('student_classe_id', 'sy_id', 'basculated', 'repeating', 'solvable1', 'solvable2', 'cas_social', 'abandon', 'position_classe', 'str1', 'str2', 'str3', 'str4', 'val1', 'val2', 'val3', 'val4')
					->withTimestamps();
	}

	public function subjects()
	{
		return $this->belongsToMany(Subject::class, 'student_subject', 'stud_id')
					->withPivot('student_subject_id', 'sy_id', 'sequence', 'eval_date', 'mark', 'isEmpty', 'str1', 'str2', 'val1', 'val2')
					->withTimestamps();
	}
}
