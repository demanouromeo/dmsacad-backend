<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Groupe
 * 
 * @property int $groupe_id
 * @property string $groupe_name
 * @property string|null $description
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|GroupeYear[] $groupe_years
 * @property Collection|SubjectClasse[] $subject_classes
 *
 * @package App\Models
 */
class Groupe extends Model
{
	protected $table = 'groupe';
	protected $primaryKey = 'groupe_id';

	protected $casts = [
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'groupe_name',
		'description',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function groupe_years()
	{
		return $this->hasMany(GroupeYear::class);
	}

	public function subject_classes()
	{
		return $this->hasMany(SubjectClasse::class);
	}
}
