<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatoriController;
use App\Http\Controllers\ProduseController;
use App\Http\Controllers\RapoarteController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\UploadVanzariController;
use App\Http\Controllers\UploadOperatorVanzariController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\OneCController;

// Rute publice
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rute protejate
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alias');
    
    // Rute operatori
    Route::get('/operatori', [OperatoriController::class, 'index'])->name('operatori');
    
    // Rute operatori - doar pentru admin (trebuie să fie înainte de ruta {id})
    Route::middleware([\App\Http\Middleware\CheckAdmin::class])->group(function () {
        Route::get('/operatori/create', [OperatoriController::class, 'create'])->name('operatori.create');
        Route::post('/operatori', [OperatoriController::class, 'store'])->name('operatori.store');
        Route::get('/operatori/{id}/edit', [OperatoriController::class, 'edit'])->name('operatori.edit');
        Route::put('/operatori/{id}', [OperatoriController::class, 'update'])->name('operatori.update');
        Route::delete('/operatori/{id}', [OperatoriController::class, 'destroy'])->name('operatori.destroy');
        
        // Rute vânzări operatori - doar pentru admin (pe luni)
        Route::post('/operatori/{operatorId}/vanzari', [OperatoriController::class, 'storeVanzare'])->name('operatori.vanzari.store');
        Route::put('/operatori/{operatorId}/vanzari/{luna}', [OperatoriController::class, 'updateVanzare'])->name('operatori.vanzari.update');
        Route::delete('/operatori/{operatorId}/vanzari/{luna}', [OperatoriController::class, 'destroyVanzare'])->name('operatori.vanzari.destroy');
        
        // Rute upload vânzări operator din Excel - download-template trebuie ÎNAINTE de {operatorId}
        Route::get('/operatori/download-template', [UploadOperatorVanzariController::class, 'downloadTemplate'])->name('operatori.download-template');
        Route::get('/operatori/{operatorId}/upload', [UploadOperatorVanzariController::class, 'uploadForm'])->name('operatori.upload');
        Route::post('/operatori/{operatorId}/upload', [UploadOperatorVanzariController::class, 'upload'])->name('operatori.upload.post');
        
        // Rute utilizatori - doar pentru admin
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    // Ruta show trebuie să fie după create pentru a evita conflictele
    Route::get('/operatori/{id}', [OperatoriController::class, 'show'])->name('operatori.show');
    
    Route::get('/produse', [ProduseController::class, 'index'])->name('produse');
    
    Route::get('/rapoarte', [RapoarteController::class, 'index'])->name('rapoarte');
    Route::get('/rapoarte/comparare', [RapoarteController::class, 'comparare'])->name('rapoarte.comparare');
    Route::get('/istoric', [RapoarteController::class, 'istoric'])->name('istoric');
    Route::get('/trafic', [DashboardController::class, 'trafic'])->name('trafic');
    Route::get('/trafic/statistici', [DashboardController::class, 'traficStats'])->name('trafic.stats');
    Route::get('/trafic/analiza', [DashboardController::class, 'traficAnaliza'])->name('trafic.analiza');
    Route::get('/setari', [DashboardController::class, 'setari'])->name('setari');
    Route::post('/setari/update', [DashboardController::class, 'updateSettings'])->name('setari.update');
    Route::post('/setari/password', [DashboardController::class, 'changePassword'])->name('setari.password');
    
    // Upload Excel Vânzări
    Route::post('/upload/vanzari', [UploadVanzariController::class, 'upload'])->name('upload.vanzari');
    
    // API Routes
    Route::get('/api/kpi', [KpiController::class, 'index'])->name('api.kpi');
    Route::put('/api/kpi/plan', [KpiController::class, 'updatePlan'])->name('api.kpi.plan.update');
    Route::get('/api/trafic', [\App\Http\Controllers\Api\TraficController::class, 'index'])->name('api.trafic');
    Route::get('/api/vanzari-lunare', [\App\Http\Controllers\Api\VanzariLunareController::class, 'index'])->name('api.vanzari.lunare');
    Route::get('/api/vanzari-zilnice', [\App\Http\Controllers\Api\VanzariZilniceController::class, 'index'])->name('api.vanzari.zilnice');
    Route::get('/api/comenzi-conversie', [\App\Http\Controllers\Api\ComenziConversieController::class, 'index'])->name('api.comenzi.conversie');
    Route::get('/api/sesiuni', [\App\Http\Controllers\Api\SesiuniController::class, 'index'])->name('api.sesiuni');
    Route::get('/api/vanzari-detalii', [\App\Http\Controllers\Api\VanzariDetaliiController::class, 'index'])->name('api.vanzari.detalii');
    Route::get('/api/istoric', [\App\Http\Controllers\Api\IstoricController::class, 'index'])->name('api.istoric');
    Route::get('/export/istoric/pdf', [\App\Http\Controllers\ExportPdfController::class, 'exportIstoric'])->name('export.istoric.pdf');
    
    // Google Analytics Routes
    Route::post('/api/ga/sync', [\App\Http\Controllers\GoogleAnalyticsController::class, 'sync'])->name('api.ga.sync');
    Route::get('/api/ga/users', [\App\Http\Controllers\Api\GAAnalyticsController::class, 'users'])->name('api.ga.users');
    Route::get('/api/ga/devices', [\App\Http\Controllers\Api\GAAnalyticsController::class, 'devices'])->name('api.ga.devices');
    Route::get('/api/ga/geo', [\App\Http\Controllers\Api\GAAnalyticsController::class, 'geo'])->name('api.ga.geo');
    Route::get('/api/ga/content', [\App\Http\Controllers\Api\GAAnalyticsController::class, 'content'])->name('api.ga.content');
    Route::get('/api/ga/ecommerce', [\App\Http\Controllers\Api\GAAnalyticsController::class, 'ecommerce'])->name('api.ga.ecommerce');
    Route::get('/api/ga/campaigns', [\App\Http\Controllers\Api\GAAnalyticsController::class, 'campaigns'])->name('api.ga.campaigns');

    // 1C Sync Routes
    Route::post('/api/1c/sync-kpi', [OneCController::class, 'syncKpi'])->name('api.1c.sync.kpi');
});
