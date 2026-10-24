<?php

namespace App\Domains\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // 1. تحديد اسم الجدول
    protected $table = 'categories';

    // 2. السماح بالإضافة في كل الحقول (Mass Assignment)
    protected $guarded = [];

    // 3. العلاقات (Relations)
    
    // علاقة القسم بالأقسام الفرعية (الأبناء)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // علاقة القسم بالقسم الرئيسي (الأب)
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // علاقة القسم بالمشاريع
    public function projects()
    {
        return $this->hasMany(Project::class, 'category_id');
    }

    // علاقة القسم بالشهادات
    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'category_id');
    }

    // علاقة القسم بالمهارات
    public function skills()
    {
        return $this->hasMany(Skill::class, 'category_id');
    }
}
