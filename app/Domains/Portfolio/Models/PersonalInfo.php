<?php

namespace App\Domains\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    protected $table = 'personal_infos';

    protected $fillable = [
        'full_name',
        'job_title',
        'bio',
        'theme_color',
        'profile_image',
        'phone',
        'email',
        'cv_web_url',
        'cv_network_url',
        'cv_web_btn_text',
        'cv_network_btn_text',
        'years_experience',
        'completed_projects',
        'satisfied_customers',
    ];
}
