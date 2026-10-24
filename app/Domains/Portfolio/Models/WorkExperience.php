<?php

namespace App\Domains\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $table = 'work_experiences';

    protected $fillable = [
        'company_name',
        'position',
        'description',
        'logo_url',
        'start_date',
        'end_date',
        'is_current',
        'sort_order',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];
}
