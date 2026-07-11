<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SubjectClasseStaff
 * 
 * @property int $id
 * @property int $subject_classe_id
 * @property int $staff_id
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Staff $staff
 * @property SubjectClasse $subject_classe
 *
 * @package App\Models
 */
class SubjectClasseStaff extends Model
{
	protected $table = 'subject_classe_staff';

	protected $casts = [
		'subject_classe_id' => 'int',
		'staff_id' => 'int',
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'subject_classe_id',
		'staff_id',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function staff()
	{
		return $this->belongsTo(Staff::class);
	}

	public function subject_classe()
	{
		return $this->belongsTo(SubjectClasse::class);
	}
}
