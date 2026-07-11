<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FiliereYear
 * 
 * @property int $filiere_year_id
 * @property int $sy_id
 * @property int $filiere_id
 * @property int $section_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property SchoolYear $school_year
 * @property Section $section
 * @property Filiere $filiere
 *
 * @package App\Models
 */
class FiliereYear extends Model
{
	protected $table = 'filiere_year';
	protected $primaryKey = 'filiere_year_id';

	protected $casts = [
		'sy_id' => 'int',
		'filiere_id' => 'int',
		'section_id' => 'int'
	];

	protected $fillable = [
		'sy_id',
		'filiere_id',
		'section_id'
	];

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}

	public function section()
	{
		return $this->belongsTo(Section::class);
	}

	public function filiere()
	{
		return $this->belongsTo(Filiere::class);
	}
}
