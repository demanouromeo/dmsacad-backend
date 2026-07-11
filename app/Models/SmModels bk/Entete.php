<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Entete
 * 
 * @property int $number
 * @property string|null $logo
 * @property string|null $logo_name
 * @property string|null $line1En
 * @property string|null $line1Fr
 * @property string|null $line2En
 * @property string|null $line2Fr
 * @property string|null $line3En
 * @property string|null $line3Fr
 * @property string|null $line4En
 * @property string|null $line4Fr
 * @property string|null $line5En
 * @property string|null $line5Fr
 * @property string|null $line6En
 * @property string|null $line6Fr
 * @property string|null $line7En
 * @property string|null $line7Fr
 * @property string|null $str1En
 * @property string|null $str1Fr
 * @property string|null $str2En
 * @property string|null $str2Fr
 * @property string|null $str3En
 * @property string|null $str3Fr
 * @property string|null $str4En
 * @property string|null $str4Fr
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Entete extends Model
{
	protected $table = 'entete';
	protected $primaryKey = 'number';
	public $incrementing = false;

	protected $casts = [
		'number' => 'int'
	];

	protected $fillable = [
		'logo',
		'logo_name',
		'line1En',
		'line1Fr',
		'line2En',
		'line2Fr',
		'line3En',
		'line3Fr',
		'line4En',
		'line4Fr',
		'line5En',
		'line5Fr',
		'line6En',
		'line6Fr',
		'line7En',
		'line7Fr',
		'str1En',
		'str1Fr',
		'str2En',
		'str2Fr',
		'str3En',
		'str3Fr',
		'str4En',
		'str4Fr'
	];
}
