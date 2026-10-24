<?php
use App\Domains\Settings\Middlewares\TrackVisitor;
use App\Domains\Portfolio\Controllers\Api\ProjectController;
use App\Domains\Portfolio\Controllers\Api\SkillController;
use App\Domains\Portfolio\Controllers\Api\CertificateController;
use App\Domains\Portfolio\Controllers\Api\CategoryController;
use App\Domains\Portfolio\Controllers\Api\PersonalInfoController;
use App\Domains\Portfolio\Controllers\Api\SocialLinkController;
use App\Domains\Portfolio\Controllers\Api\WorkExperienceController;
use Illuminate\Support\Facades\Route;
use App\Domains\Auth\Controllers\Api\AuthController;

// ============================================
// 1. عام تماماً (بدون أي middleware)
// ============================================
Route::post('/login', [AuthController::class, 'login']);

// ============================================
// 2. عام + مراقبة الزوار
// ============================================
Route::middleware([TrackVisitor::class])->group(function () {
    // معلوماتي الشخصية
    Route::get('/personal-info', [PersonalInfoController::class, 'index']);

    // روابط السوشيال ميديا
    Route::get('/social-links', [SocialLinkController::class, 'index']);

    // الأقسام
    Route::get('/categories', [CategoryController::class, 'index']);

    // المشاريع
    Route::get('/projects',      [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    // المهارات
    Route::get('/skills', [SkillController::class, 'index']);

    // الشهادات
    Route::get('/certificates', [CertificateController::class, 'index']);

    // خبرات العمل
    Route::get('/work-experiences', [WorkExperienceController::class, 'index']);
});

// ============================================
// 3. محمي بالتوكن — Admin فقط (auth:sanctum)
// ============================================
Route::middleware('auth:sanctum')->group(function () {

    // Personal Info
    Route::post('/personal-info', [PersonalInfoController::class, 'update']);

    // Categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Social Links
    Route::post('/social-links',        [SocialLinkController::class, 'store']);
    Route::delete('/social-links/{id}', [SocialLinkController::class, 'destroy']);

    // Projects CRUD
    Route::post('/projects',        [ProjectController::class, 'store']);
    Route::put('/projects/{id}',    [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // Skills
    Route::post('/skills',        [SkillController::class, 'store']);
    Route::post('/skills/{id}',   [SkillController::class, 'update']); // POST with _method=PUT for FormData compat
    Route::put('/skills/{id}',    [SkillController::class, 'update']);
    Route::delete('/skills/{id}', [SkillController::class, 'destroy']);

    // Certificates
    Route::post('/certificates',        [CertificateController::class, 'store']);
    Route::post('/certificates/{id}',   [CertificateController::class, 'update']); // FormData support
    Route::put('/certificates/{id}',    [CertificateController::class, 'update']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);

    // Work Experience
    Route::post('/work-experiences',        [WorkExperienceController::class, 'store']);
    Route::post('/work-experiences/{id}',   [WorkExperienceController::class, 'update']); // FormData support
    Route::put('/work-experiences/{id}',    [WorkExperienceController::class, 'update']);
    Route::delete('/work-experiences/{id}', [WorkExperienceController::class, 'destroy']);

    // Dashboard Stats
    Route::get('/dashboard/stats', [\App\Domains\Settings\Controllers\Api\DashboardController::class, 'stats']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});