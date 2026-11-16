<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StudentClasse
 * 
 * @property int $student_classe_id
 * @property int $stud_id
 * @property int $sy_id
 * @property int $classe_id
 * @property bool|null $basculated
 * @property bool|null $repeating
 * @property int|null $solvable1
 * @property int|null $solvable2
 * @property int|null $cas_social
 * @property int|null $abandon
 * @property int|null $position_classe
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
 * @property Student $student
 * @property Classe $classe
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class StudentClasse extends Model
{
	protected $table = 'student_classe';
	protected $primaryKey = 'student_classe_id';

	protected $casts = [
		'stud_id' => 'int',
		'sy_id' => 'int',
		'classe_id' => 'int',
		'basculated' => 'bool',
		'repeating' => 'bool',
		'solvable1' => 'int',
		'solvable2' => 'int',
		'cas_social' => 'int',
		'abandon' => 'int',
		'position_classe' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'stud_id',
		'sy_id',
		'classe_id',
		'basculated',
		'repeating',
		'solvable1',
		'solvable2',
		'cas_social',
		'abandon',
		'position_classe',
		'str1',
		'str2',
		'str3',
		'str4',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	// Business rule: a student (stud_id - one row per matricule, see StudentController::saveStudents)
	// is enrolled in at most ONE classe per school year; the same student may of course appear in
	// different classes in different years. Every enrolment path (add student, import, change
	// classe, promotion, repeaters, basculement...) creates its row through this model, so the rule
	// is enforced here once instead of in each controller. Callers that move a student delete the old
	// row first; any other attempt now throws and surfaces as that caller's own "failed" result
	// instead of silently creating a second enrolment.
	protected static function booted()
	{
		static::creating(function (StudentClasse $sc) {
			$exists = static::where('stud_id', $sc->stud_id)
				->where('sy_id', $sc->sy_id)
				->exists();
			if ($exists) {
				throw new \RuntimeException(
					"Student {$sc->stud_id} is already enrolled in a classe for this school year (sy_id {$sc->sy_id})."
				);
			}
		});
	}

	public function student()
	{
		return $this->belongsTo(Student::class, 'stud_id');
	}

	public function classe()
	{
		return $this->belongsTo(Classe::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
