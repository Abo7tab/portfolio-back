<?php

namespace App\Domains\Portfolio\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Portfolio\Models\WorkExperience;
use Illuminate\Http\Request;

class WorkExperienceController extends Controller
{
    // ─── Public: list all ────────────────────────────────────────────────
    public function index()
    {
        $experiences = WorkExperience::orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn($e) => $this->format($e));

        return response()->json(['status' => 'success', 'data' => $experiences]);
    }

    // ─── Admin: create ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'company_name' => 'required|string|max:255',
                'position'     => 'required|string|max:255',
                'description'  => 'nullable|string',
                'logo'         => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
                'start_date'   => 'required|string|max:50',
                'end_date'     => 'nullable|string|max:50',
                'is_current'   => 'nullable',
                'sort_order'   => 'nullable|integer',
            ]);

            if ($request->hasFile('logo')) {
                $data['logo_url'] = $this->uploadLogo($request->file('logo'));
            }

            $data['is_current'] = filter_var($data['is_current'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // If current job, clear end_date
            if ($data['is_current']) {
                $data['end_date'] = null;
            }

            $exp = WorkExperience::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم إضافة خبرة العمل بنجاح',
                'data'    => $this->format($exp),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' | ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'فشل الحفظ: ' . $e->getMessage()], 500);
        }
    }

    // ─── Admin: update ───────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $exp = WorkExperience::findOrFail($id);

        try {
            $data = $request->validate([
                'company_name' => 'sometimes|string|max:255',
                'position'     => 'sometimes|string|max:255',
                'description'  => 'nullable|string',
                'logo'         => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
                'start_date'   => 'sometimes|string|max:50',
                'end_date'     => 'nullable|string|max:50',
                'is_current'   => 'nullable',
                'sort_order'   => 'nullable|integer',
            ]);

            if ($request->hasFile('logo')) {
                $data['logo_url'] = $this->uploadLogo($request->file('logo'));
            }

            if (isset($data['is_current'])) {
                $data['is_current'] = filter_var($data['is_current'], FILTER_VALIDATE_BOOLEAN);
                if ($data['is_current']) {
                    $data['end_date'] = null;
                }
            }

            $exp->update($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم تعديل خبرة العمل بنجاح',
                'data'    => $this->format($exp->fresh()),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' | ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'فشل التعديل: ' . $e->getMessage()], 500);
        }
    }

    // ─── Admin: delete ───────────────────────────────────────────────────
    public function destroy($id)
    {
        WorkExperience::findOrFail($id)->delete();

        return response()->json(['status' => 'success', 'message' => 'تم الحذف']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function uploadLogo($file): string
    {
        $webRoot   = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(public_path())), '/\\');
        $uploadDir = $webRoot . DIRECTORY_SEPARATOR . 'uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_exp_logo_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $file->move($uploadDir, $filename);

        return 'uploads/' . $filename;
    }

    private function format(WorkExperience $exp): array
    {
        $appUrl  = rtrim(config('app.url'), '/');
        $logoUrl = null;

        if ($exp->logo_url) {
            $logoUrl = str_starts_with($exp->logo_url, 'http')
                ? $exp->logo_url
                : $appUrl . '/' . ltrim($exp->logo_url, '/');
        }

        return [
            'id'           => $exp->id,
            'company_name' => $exp->company_name,
            'position'     => $exp->position,
            'description'  => $exp->description,
            'logo_url'     => $logoUrl,
            'start_date'   => $exp->start_date,
            'end_date'     => $exp->end_date,
            'is_current'   => (bool) $exp->is_current,
            'sort_order'   => $exp->sort_order,
            'created_at'   => $exp->created_at,
        ];
    }
}
