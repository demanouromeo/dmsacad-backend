<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Discipline
 * 
 * @property int $discipline_id
 * @property int $stud_id
 * @property int $term
 * @property int $sy_id
 * @property int|null $absunjust
 * @property int|null $absjust
 * @property int|null $lateness
 * @property int|null $blame
 * @property bool|null $blame_bool
 * @property int|null $avertissement
 * @property bool|null $avertissement_bool
 * @property int|null $nb_jour_exclusion
 * @property bool|null $exclusion_definitive
 * @property int|null $consigne
 * @property string|null $sdm_decision
 * @property string|null $commentOnDiscipline
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
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class Discipline extends Model
{
	protected $table = 'discipline';
	protected $primaryKey = 'discipline_id';

	protected $casts = [
		'stud_id' => 'int',
		'term' => 'int',
		'sy_id' => 'int',
		'absunjust' => 'int',
		'absjust' => 'int',
		'lateness' => 'int',
		'blame' => 'int',
		'blame_bool' => 'bool',
		'avertissement' => 'int',
		'avertissement_bool' => 'bool',
		'nb_jour_exclusion' => 'int',
		'exclusion_definitive' => 'bool',
		'consigne' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'stud_id',
		'term',
		'sy_id',
		'absunjust',
		'absjust',
		'lateness',
		'blame',
		'blame_bool',
		'avertissement',
		'avertissement_bool',
		'nb_jour_exclusion',
		'exclusion_definitive',
		'consigne',
		'sdm_decision',
		'commentOnDiscipline',
		'str1',
		'str2',
		'str3',
		'str4',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	public function student()
	{
		return $this->belongsTo(Student::class, 'stud_id');
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
