<?php

use App\Http\Controllers\DasarHukumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IsuStrategisController;
use App\Http\Controllers\LegalProductArchiveController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StakeholderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

Route::middleware('dashboard.auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengaturan', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/pengaturan/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::get('/data-isu-strategis', [DashboardController::class, 'data'])->name('isu-strategis.data');
    Route::get('/pencarian', [DashboardController::class, 'search'])->name('isu-strategis.search');
    Route::get('/isu-strategis/create', [IsuStrategisController::class, 'create'])->name('isu-strategis.create');
    Route::post('/isu-strategis', [IsuStrategisController::class, 'store'])->name('isu-strategis.store');
    Route::get('/isu-strategis/{isuStrategi}/edit', [IsuStrategisController::class, 'edit'])->name('isu-strategis.edit');
    Route::put('/isu-strategis/{isuStrategi}', [IsuStrategisController::class, 'update'])->name('isu-strategis.update');
    Route::delete('/isu-strategis/{isuStrategi}', [IsuStrategisController::class, 'destroy'])->name('isu-strategis.destroy');
    Route::get('/tracking-kegiatan', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/stakeholder', [StakeholderController::class, 'index'])->name('stakeholders.index');
    Route::post('/stakeholder', [StakeholderController::class, 'store'])->name('stakeholders.store');
    Route::get('/stakeholder/{stakeholder}', [StakeholderController::class, 'show'])->name('stakeholders.show');
    Route::put('/stakeholder/{stakeholder}', [StakeholderController::class, 'update'])->name('stakeholders.update');
    Route::delete('/stakeholder/{stakeholder}', [StakeholderController::class, 'destroy'])->name('stakeholders.destroy');
    Route::post('/stakeholder/{stakeholder}/anggota', [StakeholderController::class, 'storeMember'])->name('stakeholders.members.store');
    Route::put('/stakeholder/{stakeholder}/anggota/{member}', [StakeholderController::class, 'updateMember'])->name('stakeholders.members.update');
    Route::delete('/stakeholder/{stakeholder}/anggota/{member}', [StakeholderController::class, 'destroyMember'])->name('stakeholders.members.destroy');
    Route::get('/stakeholder/{stakeholder}/anggota/{member}/foto', [StakeholderController::class, 'photo'])->name('stakeholders.members.photo');
    Route::get('/dasar-hukum/{slug}', [DasarHukumController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.show');
    Route::post('/dasar-hukum/{slug}/peraturan', [DasarHukumController::class, 'storeRegulation'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.regulations.store');
    Route::put('/dasar-hukum/{slug}/peraturan/{regulation}', [DasarHukumController::class, 'updateRegulation'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.regulations.update');
    Route::delete('/dasar-hukum/{slug}/peraturan/{regulation}', [DasarHukumController::class, 'destroyRegulation'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.regulations.destroy');
    Route::get('/dasar-hukum/{slug}/peraturan/{regulation}/dokumen', [DasarHukumController::class, 'download'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.regulations.download');
    Route::get('/dasar-hukum/{slug}/peraturan/{regulation}/pratinjau', [DasarHukumController::class, 'preview'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.regulations.preview');
    Route::get('/arsip-produk-hukum/{type}', [LegalProductArchiveController::class, 'index'])
        ->where('type', 'pks|mou')
        ->name('legal-products.index');
    Route::post('/arsip-produk-hukum/{type}', [LegalProductArchiveController::class, 'store'])
        ->where('type', 'pks|mou')
        ->name('legal-products.store');
    Route::put('/arsip-produk-hukum/{type}/{archive}', [LegalProductArchiveController::class, 'update'])
        ->where('type', 'pks|mou')
        ->name('legal-products.update');
    Route::post('/arsip-produk-hukum/{type}/{archive}/adendum', [LegalProductArchiveController::class, 'storeAddendum'])
        ->where('type', 'pks|mou')
        ->name('legal-products.addenda.store');
    Route::delete('/arsip-produk-hukum/{type}/{archive}', [LegalProductArchiveController::class, 'destroy'])
        ->where('type', 'pks|mou')
        ->name('legal-products.destroy');
    Route::get('/arsip-produk-hukum/{type}/{archive}/dokumen', [LegalProductArchiveController::class, 'download'])
        ->where('type', 'pks|mou')
        ->name('legal-products.download');
    Route::post('/dasar-hukum/{slug}/peraturan/{regulation}/pasal', [DasarHukumController::class, 'storeArticle'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.articles.store');
    Route::delete('/dasar-hukum/{slug}/peraturan/{regulation}/pasal/{article}', [DasarHukumController::class, 'destroyArticle'])
        ->where('slug', '[a-z0-9-]+')
        ->name('dasar-hukum.articles.destroy');
    Route::post('/tracking-kegiatan/tahapan', [TrackingController::class, 'storeStage'])->name('tracking.stages.store');
    Route::put('/tracking-kegiatan/tahapan/{stage}', [TrackingController::class, 'updateStage'])->name('tracking.stages.update');
    Route::delete('/tracking-kegiatan/tahapan/{stage}', [TrackingController::class, 'destroyStage'])->name('tracking.stages.destroy');
    Route::post('/tracking-kegiatan/{issue}/mulai', [TrackingController::class, 'start'])->name('tracking.start');
    Route::post('/tracking-kegiatan/{tracking}/selesai', [TrackingController::class, 'complete'])->name('tracking.complete');
});
