<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Classe
 * 
 * @property int $classe_id
 * @property string $classe_name
 * @property int $level
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $description
 * 
 * @property Collection|ClasseDecisionParam[] $classe_decision_params
 * @property Collection|ClasseYear[] $classe_years
 * @property Collection|ClassifiedparamForclass[] $classifiedparam_forclasses
 * @property Collection|LockSequenceClasse[] $lock_sequence_classes
 * @property Collection|Student[] $students
 * @property Collection|Subject[] $subjects
 * @property Collection|SubjectCompetence[] $subject_competences
 *
 * @package App\Models
 */
class Classe extends Model
{
	protected $table = 'classe';
	protected $primaryKey = 'classe_id';

	protected $casts = [
		'level' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'classe_name',
		'level',
		'str1',
		'str2',
		'val1',
		'val2',
		'description'
	];

	public function classe_decision_params()
	{
		return $this->hasMany(ClasseDecisionParam::class);
	}

	public function classe_years()
	{
		return $this->hasMany(ClasseYear::class);
	}

	public function classifiedparam_forclasses()
	{
		return $this->hasMany(ClassifiedparamForclass::class);
	}

	public function lock_sequence_classes()
	{
		return $this->hasMany(LockSequenceClasse::class);
	}

	public function students()
	{
		return $this->belongsToMany(Student::class, 'student_classe', 'classe_id', 'stud_id')
					->withPivot('student_classe_id', 'sy_id', 'basculated', 'repeating', 'solvable1', 'solvable2', 'cas_social', 'abandon', 'position_classe', 'str1', 'str2', 'str3', 'str4', 'val1', 'val2', 'val3', 'val4')
					->withTimestamps();
	}

	public function subjects()
	{
		return $this->belongsToMany(Subject::class, 'subject_classe')
					->withPivot('subject_classe_id', 'coef', 'sy_id', 'groupe_id', 'section_id', 'str1', 'str2', 'val1', 'val2')
					->withTimestamps();
	}

	public function subject_competences()
	{
		return $this->hasMany(SubjectCompetence::class);
	}
}
