<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';

	protected $fillable = [
		'name',
		'description',
		'image'
	];
}
