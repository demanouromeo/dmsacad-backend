<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SubjectClasse
 * 
 * @property int $subject_classe_id
 * @property float $coef
 * @property int $sy_id
 * @property int $classe_id
 * @property int $subject_id
 * @property int $groupe_id
 * @property int $section_id
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Subject $subject
 * @property Classe $classe
 * @property Groupe $groupe
 * @property SchoolYear $school_year
 * @property Section $section
 * @property Collection|Staff[] $staff
 *
 * @package App\Models
 */
class SubjectClasse extends Model
{
	protected $table = 'subject_classe';
	protected $primaryKey = 'subject_classe_id';

	protected $casts = [
		'coef' => 'float',
		'sy_id' => 'int',
		'classe_id' => 'int',
		'subject_id' => 'int',
		'groupe_id' => 'int',
		'section_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'coef',
		'sy_id',
		'classe_id',
		'subject_id',
		'groupe_id',
		'section_id',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function subject()
	{
		return $this->belongsTo(Subject::class);
	}

	public function classe()
	{
		return $this->belongsTo(Classe::class);
	}

	public function groupe()
	{
		return $this->belongsTo(Groupe::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}

	public function staff()
	{
		return $this->belongsToMany(Staff::class, 'subject_classe_staff')
					->withPivot('id', 'str1', 'str2', 'val1', 'val2')
					->withTimestamps();
	}
}
