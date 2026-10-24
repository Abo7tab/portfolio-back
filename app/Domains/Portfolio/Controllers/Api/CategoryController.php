<?php
namespace App\Domains\Portfolio\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Portfolio\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // عرض كل الأقسام
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $categories,
        ]);
    }

    // إضافة قسم جديد (Admin فقط)
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
                'parent_id'   => 'nullable|exists:categories,id',
            ]);

            $data['is_active'] = true;
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

            $category = Category::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Category created',
                'data'    => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Category error: ' . $e->getMessage()], 500);
        }
    }

    // حذف قسم (Admin فقط)
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Category deleted',
        ]);
    }
}
