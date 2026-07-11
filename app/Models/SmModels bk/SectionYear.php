<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SectionYear
 * 
 * @property int $section_year_id
 * @property int $section_id
 * @property int $sy_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Section $section
 * @property SchoolYear $school_year
 *
 * @package App\Models
 */
class SectionYear extends Model
{
	protected $table = 'section_year';
	protected $primaryKey = 'section_year_id';

	protected $casts = [
		'section_id' => 'int',
		'sy_id' => 'int'
	];

	protected $fillable = [
		'section_id',
		'sy_id'
	];

	public function section()
	{
		return $this->belongsTo(Section::class);
	}

	public function school_year()
	{
		return $this->belongsTo(SchoolYear::class, 'sy_id');
	}
}
