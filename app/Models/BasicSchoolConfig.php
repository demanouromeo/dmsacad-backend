<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BasicSchoolConfig
 * 
 * @property int $id
 * @property int $sy_id
 * @property int|null $number_of_sequences
 * @property int $classe_max_size
 * @property string $name_fr
 * @property string $name_en
 * @property string|null $del_regionale_fr
 * @property string|null $del_regionale_en
 * @property string|null $del_dept_fr
 * @property string|null $del_dept_en
 * @property int $phone1
 * @property int|null $phone2
 * @property string|null $email
 * @property string|null $pobox
 * @property string|null $town
 * @property string|null $logo
 * @property string|null $logo_path
 * @property string|null $type
 * @property string|null $date_signature
 * @property string|null $lieu_signature
 * @property string|null $school_matricule
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
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class BasicSchoolConfig extends Model
{
	protected $table = 'basic_school_config';

	protected $casts = [
		'sy_id' => 'int',
		'number_of_sequences' => 'int',
		'classe_max_size' => 'int',
		'phone1' => 'int',
		'phone2' => 'int',
		'val1' => 'int',
		'val2' => 'int',
		'val3' => 'int',
		'val4' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'number_of_sequences',
		'classe_max_size',
		'name_fr',
		'name_en',
		'del_regionale_fr',
		'del_regionale_en',
		'del_dept_fr',
		'del_dept_en',
		'phone1',
		'phone2',
		'email',
		'pobox',
		'town',
		'logo',
		'logo_path',
		'type',
		'date_signature',
		'lieu_signature',
		'school_matricule',
		'str1',
		'str2',
		'str3',
		'str4',
		'val1',
		'val2',
		'val3',
		'val4'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
