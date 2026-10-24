<?php

namespace App\Domains\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
   // السماح بإضافة الحقول دي في الداتابيز
 protected $fillable = [
    'category_id', 
    'title', 
    'description', 
    'image_url', 
    'live_link',    // اللينك اللايف
    'github_link',  // لينك الجيت هاب
    'is_active',    // هل هو شغال؟
    'sort_order'    // الترتيب
];

    // علاقة المشروع بالقسم (كل مشروع ينتمي لقسم واحد)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
