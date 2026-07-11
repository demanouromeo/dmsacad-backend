<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Products2
 * 
 * @property int $id
 * @property string $name
 * @property string|null $image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Products2 extends Model
{
	protected $table = 'products2';

	protected $fillable = [
		'name',
		'image_path'
	];
}
