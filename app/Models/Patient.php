<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Patient
 * 
 * @property int $p_id
 * @property string $p_name
 * @property string $pwd
 * @property string|null $region 
 * @property string|null $gender 
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at 
 *
 * @package App\Models
 */
class Patient extends Model
{
	protected $table = 'patient';
	protected $primaryKey = 'p_id';
 

	protected $fillable = [
		'p_name',
		'pwd',
		'region', 
		'gender',
	];

}
