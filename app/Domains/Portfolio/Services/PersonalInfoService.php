<?php
namespace App\Domains\Portfolio\Services;

use App\Domains\Portfolio\Models\PersonalInfo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PersonalInfoService
{
    public function updateInfo(array $data, Request $request): PersonalInfo
    {
        // 1. Remove file keys so they aren't mass-assigned
        unset($data['cv_web_file'], $data['cv_network_file'], $data['profile_image']);

        // 2. Upload files using Storage facade (public disk for Alwaysdata)
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->uploadFile($request->file('profile_image'), 'avatars');
        }
        if ($request->hasFile('cv_web_file')) {
            $data['cv_web_url'] = $this->uploadFile($request->file('cv_web_file'), 'cvs/web');
        }
        if ($request->hasFile('cv_network_file')) {
            $data['cv_network_url'] = $this->uploadFile($request->file('cv_network_file'), 'cvs/network');
        }

        // 3. Update or Create
        $info = PersonalInfo::first();
        if ($info) {
            $info->update($data);
        } else {
            $info = PersonalInfo::create($data);
        }

        // 4. Clear cache
        Cache::forget('personal_info_data');

        return $this->formatUrls($info);
    }

    private function uploadFile($file, string $path): string
    {
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $storedPath = $file->storeAs($path, $filename, 'public');
        
        return '/uploads/' . $storedPath;
    }

    public function formatUrls(PersonalInfo $info): PersonalInfo
    {
        $appUrl = rtrim(config('app.url'), '/');

        if (request() && (strpos($appUrl, 'localhost') !== false || strpos($appUrl, '127.0.0.1') !== false)) {
            $appUrl = rtrim(request()->schemeAndHttpHost(), '/');
        }

        if (substr($appUrl, -4) === '/api') {
            $appUrl = substr($appUrl, 0, -4);
        }

        if ($info->profile_image && strpos($info->profile_image, 'http') !== 0) {
            $info->profile_image = $appUrl . '/' . ltrim($info->profile_image, '/');
        }
        if ($info->cv_web_url && strpos($info->cv_web_url, 'http') !== 0) {
            $info->cv_web_url = $appUrl . '/' . ltrim($info->cv_web_url, '/');
        }
        if ($info->cv_network_url && strpos($info->cv_network_url, 'http') !== 0) {
            $info->cv_network_url = $appUrl . '/' . ltrim($info->cv_network_url, '/');
        }

        return $info;
    }
}
