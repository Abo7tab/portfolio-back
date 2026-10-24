<?php
namespace App\Domains\Portfolio\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Portfolio\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // عرض كل المشاريع
    public function index()
    {
        $projects = Project::with('category')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($p) {
                return $this->formatProject($p);
            });

        return response()->json([
            'status' => 'success',
            'data'   => $projects,
        ]);
    }

    // عرض مشروع واحد
    public function show($id)
    {
        $project = Project::with('category')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatProject($project),
        ]);
    }

    // إضافة مشروع جديد (Admin فقط)
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'title'       => 'required|string|max:255',
                'description' => 'required|string',
                'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
                'live_link'   => 'nullable|url',
                'github_link' => 'nullable|url',
                'is_active'   => 'nullable',
                'sort_order'  => 'nullable|integer',
            ]);

            if ($request->hasFile('image')) {
                $data['image_url'] = $this->uploadImage($request->file('image'));
            }

            // Don't insert null category_id if column doesn't allow NULL
            if (empty($data['category_id'])) {
                unset($data['category_id']);
            }

            $data['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

            $project = Project::create($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'تم إضافة المشروع بنجاح',
                'data'    => $this->formatProject($project),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' | ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    // تعديل مشروع (Admin فقط)
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image'       => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'live_link'   => 'nullable|url',
            'github_link' => 'nullable|url',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->uploadImage($request->file('image'));
        }

        if (isset($data['is_active'])) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $project->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم تعديل المشروع بنجاح',
            'data'    => $this->formatProject($project->fresh()),
        ]);
    }

    // حذف مشروع (Admin فقط)
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم حذف المشروع',
        ]);
    }

    // ---- Helpers ----

    /**
     * Upload image. Uses DOCUMENT_ROOT for reliable path on shared hosting.
     * This ensures images land in the web-accessible public_html/uploads/.
     */
    private function uploadImage($file)
    {
        // DOCUMENT_ROOT = /home/.../public_html on cPanel
        // This is always the correct web-accessible root
        $webRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(public_path())), '/\\');
        $uploadDir = $webRoot . DIRECTORY_SEPARATOR . 'uploads';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new \RuntimeException("Cannot create uploads directory: {$uploadDir}");
            }
        }

        if (!is_writable($uploadDir)) {
            throw new \RuntimeException("Uploads directory is not writable: {$uploadDir}");
        }

        // Sanitize filename
        $originalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $filename = time() . '_' . $originalName;
        $file->move($uploadDir, $filename);

        return 'uploads/' . $filename;
    }

    /**
     * Format project for API response with full image URL.
     */
    private function formatProject(Project $project)
    {
        $appUrl   = rtrim(config('app.url'), '/');
        $imageUrl = null;

        if ($project->image_url) {
            // Support both relative paths and absolute URLs
            if (strpos($project->image_url, 'http') === 0) {
                $imageUrl = $project->image_url;
            } else {
                $imageUrl = $appUrl . '/' . ltrim($project->image_url, '/');
            }
        }

        return [
            'id'          => $project->id,
            'title'       => $project->title,
            'description' => $project->description,
            'image_url'   => $imageUrl,
            'live_link'   => $project->live_link,
            'github_link' => $project->github_link,
            'is_active'   => (bool) $project->is_active,
            'sort_order'  => $project->sort_order,
            'category_id' => $project->category_id,
            'category'    => $project->category,
            'created_at'  => $project->created_at,
        ];
    }
}