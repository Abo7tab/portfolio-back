<?php
namespace App\Domains\Portfolio\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Portfolio\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    // عرض كل الروابط
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data'   => SocialLink::all(),
        ]);
    }

    // إضافة رابط (Admin فقط)
    public function store(Request $request)
    {
        $data = $request->validate([
            'platform'   => 'required|string|max:50',
            'url'        => 'required|url',
            'icon_class' => 'nullable|string|max:50',
        ]);

        $link = SocialLink::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم إضافة الرابط بنجاح',
            'data'    => $link,
        ], 201);
    }

    // حذف رابط (Admin فقط)
    public function destroy($id)
    {
        $link = SocialLink::findOrFail($id);
        $link->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم حذف الرابط',
        ]);
    }
}
