<?php
namespace App\Domains\Portfolio\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Portfolio\Models\PersonalInfo;
use App\Domains\Portfolio\Requests\UpdatePersonalInfoRequest;
use App\Domains\Portfolio\Services\PersonalInfoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PersonalInfoController extends Controller
{
    public function __construct(
        protected PersonalInfoService $personalInfoService
    ) {}

    // عرض بياناتك الشخصية (مع إضافة Cache و Lighthouse Optimization)
    public function index(): JsonResponse
    {
        // جلب البيانات من الكاش لتسريع الـ API بدلاً من الاستعلام من قاعدة البيانات كل مرة
        $info = Cache::rememberForever('personal_info_data', function () {
            return PersonalInfo::first();
        });

        if ($info) {
            $info = $this->personalInfoService->formatUrls($info);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $info,
        ])
        ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400'); 
    }

    // تعديل بياناتك الشخصية
    public function update(UpdatePersonalInfoRequest $request): JsonResponse
    {
        try {
            $info = $this->personalInfoService->updateInfo($request->validated(), $request);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم تحديث البيانات الشخصية بنجاح',
                'data'    => $info,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
