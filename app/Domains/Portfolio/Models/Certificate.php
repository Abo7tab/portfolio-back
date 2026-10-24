<?php

namespace App\Domains\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = 'certificates';
    protected $guarded = [];

    // علاقة الشهادة بالقسم
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
