<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Section
 * 
 * @property int $section_id
 * @property string $section_name
 * @property string|null $description
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ClasseYear[] $classe_years
 * @property Collection|FiliereYear[] $filiere_years
 * @property Collection|GroupeYear[] $groupe_years
 * @property Collection|SectionYear[] $section_years
 * @property Collection|SpecialityYear[] $speciality_years
 * @property Collection|StaffCourse[] $staff_courses
 * @property Collection|SubjectClasse[] $subject_classes
 * @property Collection|SubjectCompetence[] $subject_competences
 * @property Collection|SubjectYear[] $subject_years
 *
 * @package App\Models
 */
class Section extends Model
{
	protected $table = 'section';
	protected $primaryKey = 'section_id';

	protected $casts = [
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'section_name',
		'description',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function classe_years()
	{
		return $this->hasMany(ClasseYear::class);
	}

	public function filiere_years()
	{
		return $this->hasMany(FiliereYear::class);
	}

	public function groupe_years()
	{
		return $this->hasMany(GroupeYear::class);
	}

	public function section_years()
	{
		return $this->hasMany(SectionYear::class);
	}

	public function speciality_years()
	{
		return $this->hasMany(SpecialityYear::class);
	}

	public function staff_courses()
	{
		return $this->hasMany(StaffCourse::class);
	}

	public function subject_classes()
	{
		return $this->hasMany(SubjectClasse::class);
	}

	public function subject_competences()
	{
		return $this->hasMany(SubjectCompetence::class);
	}

	public function subject_years()
	{
		return $this->hasMany(SubjectYear::class);
	}
}
