<?php
namespace App\Domains\Portfolio\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Portfolio\Models\Skill;
use App\Domains\Portfolio\Models\Category;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    // عرض كل المهارات مجمعة حسب القسم
    public function index()
    {
        $skills = Skill::with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $skills,
        ]);
    }

    // إضافة مهارة جديدة (Admin فقط)
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'name'        => 'required|string|max:255',
                'proficiency' => 'nullable|integer|min:0|max:100',
                'icon'        => 'nullable|string|max:255',
                'sort_order'  => 'nullable|integer',
            ]);

            // If no category given, use a default "General" category or create one
            if (empty($data['category_id'])) {
                $general = Category::firstOrCreate(
                    ['name' => 'General'],
                    ['slug' => 'general', 'is_active' => true]
                );
                $data['category_id'] = $general->id;
            }

            $data['proficiency'] = $data['proficiency'] ?? 100;
            $data['sort_order']  = $data['sort_order'] ?? 0;

            $skill = Skill::create($data);
            $skill->load('category');

            return response()->json([
                'status'  => 'success',
                'message' => 'تم إضافة المهارة بنجاح',
                'data'    => $skill,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // تعديل مهارة (Admin فقط)
    public function update(Request $request, $id)
    {
        try {
            $skill = Skill::findOrFail($id);

            $data = $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'name'        => 'sometimes|string|max:255',
                'proficiency' => 'nullable|integer|min:0|max:100',
                'icon'        => 'nullable|string|max:255',
                'sort_order'  => 'nullable|integer',
            ]);

            if (isset($data['category_id']) && empty($data['category_id'])) {
                unset($data['category_id']);
            }

            $skill->update($data);
            $skill->load('category');

            return response()->json([
                'status'  => 'success',
                'message' => 'تم تعديل المهارة بنجاح',
                'data'    => $skill->fresh(['category']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // حذف مهارة (Admin فقط)
    public function destroy($id)
    {
        $skill = Skill::findOrFail($id);
        $skill->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم حذف المهارة',
        ]);
    }
}
