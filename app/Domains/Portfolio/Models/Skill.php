<?php

namespace App\Domains\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $table = 'skills';
    protected $guarded = [];

    // علاقة المهارة بالقسم (عشان نعرف هي Backend ولا Frontend)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
