<?php
namespace App\Domains\Portfolio\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Portfolio\Models\Certificate;
use App\Domains\Portfolio\Models\Category;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    // عرض كل الشهادات مع القسم
    public function index()
    {
        $certificates = Certificate::with('category')
            ->orderBy('date_acquired', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($c) => $this->formatCertificate($c));

        return response()->json([
            'status' => 'success',
            'data'   => $certificates,
        ]);
    }

    // إضافة شهادة جديدة (Admin فقط)
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'category_id'      => 'nullable|exists:categories,id',
                'title'            => 'required|string|max:255',
                'issuer'           => 'required|string|max:255',
                'certificate_file' => 'required|file|mimes:pdf,jpeg,png,jpg,webp|max:10240',
                'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
                'date_acquired'    => 'nullable|date',
            ]);

            // Auto-create General category if none provided
            if (empty($data['category_id'])) {
                $general = Category::firstOrCreate(
                    ['name' => 'General'],
                    ['slug' => 'general', 'is_active' => true]
                );
                $data['category_id'] = $general->id;
            }

            // Remove file input keys — they are NOT DB columns
            unset($data['certificate_file'], $data['image']);

            // Upload PDF / certificate file
            if ($request->hasFile('certificate_file')) {
                $data['file_url'] = $this->uploadFile(
                    $request->file('certificate_file'),
                    'cert'
                );
            }

            // Upload preview image (optional)
            if ($request->hasFile('image')) {
                $data['image_url'] = $this->uploadFile(
                    $request->file('image'),
                    'cert_img'
                );
            }

            $certificate = Certificate::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم إضافة الشهادة بنجاح',
                'data'    => $this->formatCertificate($certificate),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // تعديل شهادة (Admin فقط)
    public function update(Request $request, $id)
    {
        try {
            $cert = Certificate::findOrFail($id);

            $data = $request->validate([
                'category_id'      => 'nullable|exists:categories,id',
                'title'            => 'sometimes|string|max:255',
                'issuer'           => 'sometimes|string|max:255',
                'certificate_file' => 'sometimes|nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:10240',
                'image'            => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
                'date_acquired'    => 'nullable|date',
            ]);

            if (empty($data['category_id'])) unset($data['category_id']);

            // Remove file input keys — they are NOT DB columns
            unset($data['certificate_file'], $data['image']);

            if ($request->hasFile('certificate_file')) {
                $data['file_url'] = $this->uploadFile($request->file('certificate_file'), 'cert');
            }

            if ($request->hasFile('image')) {
                $data['image_url'] = $this->uploadFile($request->file('image'), 'cert_img');
            }

            $cert->update($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم تعديل الشهادة بنجاح',
                'data'    => $this->formatCertificate($cert->fresh(['category'])),
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // حذف شهادة (Admin فقط)
    public function destroy($id)
    {
        $certificate = Certificate::findOrFail($id);
        $certificate->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم حذف الشهادة',
        ]);
    }

    // ---- Helpers ----

    private function uploadFile($file, string $prefix = 'file'): string
    {
        // On this shared hosting, public_html/index.php bridges to Laravel's public/index.php
        // So public_path() IS effectively the web root for serving static files
        // Try DOCUMENT_ROOT first, fallback to public_path
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';

        // DOCUMENT_ROOT should point to public_html (not /home alone)
        // Validate: if it's a short path (e.g. just /home), fall back to public_path
        if ($docRoot && strlen($docRoot) > 6 && is_dir($docRoot)) {
            $uploadDir = rtrim($docRoot, '/\\') . '/uploads';
        } else {
            $uploadDir = public_path('uploads');
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!is_writable($uploadDir)) {
            throw new \RuntimeException("Upload dir not writable: {$uploadDir}");
        }

        $originalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $filename     = time() . '_' . $prefix . '_' . $originalName;
        $file->move($uploadDir, $filename);

        return 'uploads/' . $filename;
    }

    private function formatCertificate(Certificate $cert): array
    {
        $appUrl = rtrim(config('app.url'), '/');

        $makeUrl = function ($path) use ($appUrl) {
            if (!$path) return null;
            return (strpos($path, 'http') === 0) ? $path : $appUrl . '/' . ltrim($path, '/');
        };

        return [
            'id'            => $cert->id,
            'title'         => $cert->title,
            'issuer'        => $cert->issuer,
            'file_url'      => $makeUrl($cert->file_url),
            'image_url'     => $makeUrl($cert->image_url),
            'date_acquired' => $cert->date_acquired,
            'category_id'   => $cert->category_id,
            'category'      => $cert->category,
            'created_at'    => $cert->created_at,
        ];
    }
}
