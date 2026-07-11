<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SchoolYear
 * 
 * @property int $sy_id
 * @property string $year
 * @property string|null $description
 * @property bool $is_current
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AbsenceDaily[] $absence_dailies
 * @property ApcLevel $apc_level
 * @property Collection|Appreciation[] $appreciations
 * @property BasicSchoolConfig $basic_school_config
 * @property Collection|ClasseDecisionParam[] $classe_decision_params
 * @property Collection|ClasseYear[] $classe_years
 * @property Classifiedparam $classifiedparam
 * @property Collection|ClassifiedparamForclass[] $classifiedparam_forclasses
 * @property Collection|ClassifiedparamForterm[] $classifiedparam_forterms
 * @property Collection|Discipline[] $disciplines
 * @property Collection|ExlusionTemporaireAbsx[] $exlusion_temporaire_absxes
 * @property Collection|ExlusionTemporaireInterval[] $exlusion_temporaire_intervals
 * @property ExlusionTemporaireNew $exlusion_temporaire_new
 * @property Collection|FiliereYear[] $filiere_years
 * @property Collection|GroupeYear[] $groupe_years
 * @property Collection|LockSequence[] $lock_sequences
 * @property Collection|LockSequenceClasse[] $lock_sequence_classes
 * @property Collection|SectionYear[] $section_years
 * @property Collection|SpecialityYear[] $speciality_years
 * @property Collection|StaffCourse[] $staff_courses
 * @property Collection|StaffYear[] $staff_years
 * @property Collection|StudCompMark[] $stud_comp_marks
 * @property Collection|StudentClasse[] $student_classes
 * @property Collection|StudentSubject[] $student_subjects
 * @property Collection|SubjectClasse[] $subject_classes
 * @property Collection|SubjectCompetence[] $subject_competences
 * @property Collection|SubjectYear[] $subject_years
 * @property Thparam $thparam
 *
 * @package App\Models
 */
class SchoolYear extends Model
{
	protected $table = 'school_year';
	protected $primaryKey = 'sy_id';

	protected $casts = [
		'is_current' => 'bool',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'year',
		'description',
		'is_current',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function absence_dailies()
	{
		return $this->hasMany(AbsenceDaily::class, 'sy_id');
	}

	public function apc_level()
	{
		return $this->hasOne(ApcLevel::class, 'sy_id');
	}

	public function appreciations()
	{
		return $this->hasMany(Appreciation::class, 'sy_id');
	}

	public function basic_school_config()
	{
		return $this->hasOne(BasicSchoolConfig::class, 'sy_id');
	}

	public function classe_decision_params()
	{
		return $this->hasMany(ClasseDecisionParam::class, 'sy_id');
	}

	public function classe_years()
	{
		return $this->hasMany(ClasseYear::class, 'sy_id');
	}

	public function classifiedparam()
	{
		return $this->hasOne(Classifiedparam::class, 'sy_id');
	}

	public function classifiedparam_forclasses()
	{
		return $this->hasMany(ClassifiedparamForclass::class, 'sy_id');
	}

	public function classifiedparam_forterms()
	{
		return $this->hasMany(ClassifiedparamForterm::class, 'sy_id');
	}

	public function disciplines()
	{
		return $this->hasMany(Discipline::class, 'sy_id');
	}

	public function exlusion_temporaire_absxes()
	{
		return $this->hasMany(ExlusionTemporaireAbsx::class, 'sy_id');
	}

	public function exlusion_temporaire_intervals()
	{
		return $this->hasMany(ExlusionTemporaireInterval::class, 'sy_id');
	}

	public function exlusion_temporaire_new()
	{
		return $this->hasOne(ExlusionTemporaireNew::class, 'sy_id');
	}

	public function filiere_years()
	{
		return $this->hasMany(FiliereYear::class, 'sy_id');
	}

	public function groupe_years()
	{
		return $this->hasMany(GroupeYear::class, 'sy_id');
	}

	public function lock_sequences()
	{
		return $this->hasMany(LockSequence::class, 'sy_id');
	}

	public function lock_sequence_classes()
	{
		return $this->hasMany(LockSequenceClasse::class, 'sy_id');
	}

	public function section_years()
	{
		return $this->hasMany(SectionYear::class, 'sy_id');
	}

	public function speciality_years()
	{
		return $this->hasMany(SpecialityYear::class, 'sy_id');
	}

	public function staff_courses()
	{
		return $this->hasMany(StaffCourse::class, 'sy_id');
	}

	public function staff_years()
	{
		return $this->hasMany(StaffYear::class, 'sy_id');
	}

	public function stud_comp_marks()
	{
		return $this->hasMany(StudCompMark::class, 'sy_id');
	}

	public function student_classes()
	{
		return $this->hasMany(StudentClasse::class, 'sy_id');
	}

	public function student_subjects()
	{
		return $this->hasMany(StudentSubject::class, 'sy_id');
	}

	public function subject_classes()
	{
		return $this->hasMany(SubjectClasse::class, 'sy_id');
	}

	public function subject_competences()
	{
		return $this->hasMany(SubjectCompetence::class, 'sy_id');
	}

	public function subject_years()
	{
		return $this->hasMany(SubjectYear::class, 'sy_id');
	}

	public function thparam()
	{
		return $this->hasOne(Thparam::class, 'sy_id');
	}
}
