<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SubjectCompetence
 * 
 * @property int $subject_competence_id
 * @property int $classe_id
 * @property int $sy_id
 * @property int $term_id
 * @property int $subject_id
 * @property int $section_id
 * @property string $competence_text
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
 * @property Subject $subject
 * @property SchoolYear $school_year
 * @property Section $section
 * @property Classe $classe
 * @property Collection|StudCompMark[] $stud_comp_marks
 *
 * @package App\Models
 */
class SubjectCompetence extends Model
{
	protected $table = 'subject_competences';
	protected $primaryKey = 'subject_competence_id';

	protected $casts = [
		'classe_id' => 'int',
		'sy_id' => 'int',
		'term_id' => 'int',
		'subject_id' => 'int',
		'section_id' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'classe_id',
		'sy_id',
		'term_id',
		'subject_id',
		'section_id',
		'competence_text',
		'str1',
		'str2',
		'str3',
		'str4',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	public function subject()
	{
		return $this->belongsTo(Subject::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}

	public function classe()
	{
		return $this->belongsTo(Classe::class);
	}

	public function stud_comp_marks()
	{
		return $this->hasMany(StudCompMark::class);
	}
}
