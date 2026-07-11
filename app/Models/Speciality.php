<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Speciality
 * 
 * @property int $speciality_id
 * @property string $speciality_name
 * @property string|null $description
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ClasseYear[] $classe_years
 * @property Collection|SpecialityYear[] $speciality_years
 *
 * @package App\Models
 */
class Speciality extends Model
{
	protected $table = 'speciality';
	protected $primaryKey = 'speciality_id';

	protected $casts = [
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'speciality_name',
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

	public function speciality_years()
	{
		return $this->hasMany(SpecialityYear::class);
	}
}
