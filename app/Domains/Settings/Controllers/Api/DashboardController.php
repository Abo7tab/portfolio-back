<?php

namespace App\Domains\Settings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Settings\Models\SiteVisit;
use App\Domains\Portfolio\Models\Project;
use App\Domains\Portfolio\Models\Skill;
use App\Domains\Portfolio\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        // 1. Unique visitors only (by IP)
        $uniqueVisitors = SiteVisit::distinct('ip_address')->count('ip_address');
        
        // Total page hits
        $totalVisits = SiteVisit::count();
        
        // Content Counters
        $projectsCount = Project::count();
        $skillsCount = Skill::count();
        $certificatesCount = Certificate::count();

        // 2. Get recent UNIQUE visitors (grouped by IP, showing latest visit per IP)
        $recentVisits = SiteVisit::select('ip_address', 
                DB::raw('MAX(id) as latest_id'))
            ->groupBy('ip_address')
            ->orderBy('latest_id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($grouped) {
                $visit = SiteVisit::find($grouped->latest_id);
                if (!$visit) return null;
                
                // Both visited_at and now() are from PHP, so diff is always correct
                $visitTime = Carbon::parse($visit->visited_at);
                
                return [
                    'ip' => $visit->ip_address,
                    'path' => $visit->url_path,
                    'device' => $visit->device_type ?: 'Unknown',
                    'browser' => $visit->browser ?: 'Unknown',
                    'os' => $visit->os ?: 'Unknown',
                    'time' => $visitTime->toIso8601String(),
                    'time_humans' => $visitTime->diffForHumans(),
                ];
            })
            ->filter(); // Remove nulls

        return response()->json([
            'status' => 'success',
            'data' => [
                'counts' => [
                    'visits' => $totalVisits,
                    'unique_visitors' => $uniqueVisitors,
                    'projects' => $projectsCount,
                    'skills' => $skillsCount,
                    'certificates' => $certificatesCount,
                ],
                'recent_visits' => $recentVisits->values()
            ]
        ]);
    }
}
