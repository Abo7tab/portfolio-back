<?php

namespace App\Domains\Settings\Middlewares; // تأكد إن الـ Namespace مطابق للمسار الجديد

use Closure;
use Illuminate\Http\Request;
use App\Domains\Settings\Models\SiteVisit; // استدعاء موديل الزيارات اللي عملناه

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Exclude internal API calls (optional, but good practice) and specific paths if needed
        // If we want to track EVERYTHING, we can skip this check.
        // But to avoid "5 visits per page load", we should debounce.

        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');
        $url = $request->fullUrl();

        // 2. Debounce: Check if the same IP visited in the last 60 minutes
        // We use Cache instead of DB to avoid timezone mismatches
        $cacheKey = 'visitor_ip_' . $ip;

        if (! \Illuminate\Support\Facades\Cache::has($cacheKey)) {
            // Set cache for 60 minutes
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 60 * 60);

            // 3. Simple User Agent Parsing
            $device = 'Desktop';
            if (preg_match('/(Mobile|Android|Tablet|GoBrowser|[0-9]x[0-9]*|rbt|nav|sfr)/i', $userAgent)) {
                $device = 'Mobile';
            }

            $browser = 'Unknown';
            $os = 'Unknown';

            // Detect Browser
            if (strpos($userAgent, 'Firefox') !== false) $browser = 'Firefox';
            elseif (strpos($userAgent, 'Chrome') !== false) $browser = 'Chrome';
            elseif (strpos($userAgent, 'Safari') !== false) $browser = 'Safari';
            elseif (strpos($userAgent, 'Edge') !== false) $browser = 'Edge';
            elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) $browser = 'Opera';

            // Detect OS
            if (preg_match('/windows|win32/i', $userAgent)) $os = 'Windows';
            elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $os = 'Mac OS';
            elseif (preg_match('/linux/i', $userAgent)) $os = 'Linux';
            elseif (preg_match('/android/i', $userAgent)) $os = 'Android';
            elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $os = 'iOS';

            SiteVisit::create([
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device_type' => $device,
                'browser' => $browser,
                'os' => $os,
                'url_path'   => $url,
                'method'     => $request->method(),
                'visited_at' => \Carbon\Carbon::now(),
            ]);
        }

        return $next($request);
    }
}