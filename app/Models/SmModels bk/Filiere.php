<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Filiere
 * 
 * @property int $filiere_id
 * @property string $nom_filiere
 * @property string|null $str1
 * @property string|null $str2
 * @property int|null $val1
 * @property int|null $val2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|FiliereYear[] $filiere_years
 *
 * @package App\Models
 */
class Filiere extends Model
{
	protected $table = 'filiere';
	protected $primaryKey = 'filiere_id';

	protected $casts = [
		'val1' => 'int',
		'val2' => 'int'
	];

	protected $fillable = [
		'nom_filiere',
		'str1',
		'str2',
		'val1',
		'val2'
	];

	public function filiere_years()
	{
		return $this->hasMany(FiliereYear::class);
	}
}
