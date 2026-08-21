<?php

use App\Http\Controllers\Admin\AdministrativeUserController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ContactInquiryController as AdminContactInquiryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MetricsController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\SessionActivityController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DonorCardController;
use App\Http\Controllers\DonorRegistrationController;
use App\Http\Controllers\DonorSessionActivityController;
use App\Http\Controllers\ContactInquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdentityVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/contactenos', [ContactInquiryController::class, 'create'])->name('contact.create');
Route::post('/contactenos', [ContactInquiryController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/registro', [IdentityVerificationController::class, 'create'])->name('registration.identity');
Route::get('/registro/captcha.svg', [IdentityVerificationController::class, 'captchaImage'])->name('registration.captcha.image');
Route::post('/registro/captcha/renovar', [IdentityVerificationController::class, 'refreshCaptcha'])->name('registration.captcha.refresh');
Route::post('/registro/validar-identidad', [IdentityVerificationController::class, 'store'])->name('registration.identity.store');
Route::middleware(['identity.verified', 'donor.session'])->group(function () {
    Route::get('/registro/identidad-validada', [IdentityVerificationController::class, 'verified'])->name('registration.identity.verified');
    Route::post('/registro/actividad', DonorSessionActivityController::class)->name('registration.session.activity');
    Route::get('/registro/datos', [DonorRegistrationController::class, 'create'])->name('registration.form');
    Route::post('/registro/datos', [DonorRegistrationController::class, 'store'])->name('registration.store');
    Route::post('/registro/baja', [DonorRegistrationController::class, 'withdraw'])->name('registration.withdraw');
    Route::get('/registro/reactivar', [DonorRegistrationController::class, 'reactivationForm'])->name('registration.reactivation.form');
    Route::post('/registro/reactivar', [DonorRegistrationController::class, 'reactivate'])->name('registration.reactivation.store');
    Route::get('/registro/actualizar', [DonorRegistrationController::class, 'updateForm'])->name('registration.update.form');
    Route::post('/registro/actualizar', [DonorRegistrationController::class, 'update'])->name('registration.update.store');
});
Route::get('/registro/completado', [DonorRegistrationController::class, 'completed'])->name('registration.completed');
Route::get('/registro/carnet/{donor}/imprimir', [DonorCardController::class, 'registrationPrint'])->whereNumber('donor')->name('registration.card.print');
Route::get('/registro/carnet/{donor}/pdf', [DonorCardController::class, 'registrationPdf'])->whereNumber('donor')->name('registration.card.pdf');
Route::view('/registro/baja-completada', 'registration.withdrawn')->name('registration.withdrawn');
Route::get('/verificar-carnet/{token}', [DonorCardController::class, 'verify'])->where('token', '[a-f0-9]{64}')->name('cards.verify');

Route::middleware('guest')->group(function () {
    Route::get('/administracion/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/administracion/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active', 'admin.session'])->prefix('administracion')->name('admin.')->group(function () {
    Route::post('/actividad', SessionActivityController::class)->name('session.activity');
    Route::get('/cambiar-contrasena', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/cambiar-contrasena', [PasswordController::class, 'update'])->name('password.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('password.changed')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/metricas', [MetricsController::class, 'index'])->name('metrics.index');
        Route::get('/contenidos', [ContentController::class, 'index'])->name('contents.index');
        Route::post('/contenidos', [ContentController::class, 'store'])->name('contents.store');
        Route::put('/contenidos/{content}', [ContentController::class, 'update'])->whereNumber('content')->name('contents.update');
        Route::delete('/contenidos/{content}', [ContentController::class, 'destroy'])->whereNumber('content')->name('contents.destroy');
        Route::get('/consultas', [AdminContactInquiryController::class, 'index'])->name('contact-inquiries.index');
        Route::get('/consultas/{inquiry}', [AdminContactInquiryController::class, 'show'])->whereNumber('inquiry')->name('contact-inquiries.show');
        Route::post('/consultas/{inquiry}/tomar', [AdminContactInquiryController::class, 'take'])->whereNumber('inquiry')->name('contact-inquiries.take');
        Route::post('/consultas/{inquiry}/responder', [AdminContactInquiryController::class, 'respond'])->whereNumber('inquiry')->name('contact-inquiries.respond');
        Route::post('/consultas/{inquiry}/cerrar', [AdminContactInquiryController::class, 'close'])->whereNumber('inquiry')->name('contact-inquiries.close');
        Route::get('/donantes/exportar.csv', [DashboardController::class, 'exportCsv'])->name('donors.export.csv');
        Route::get('/donantes/{donor}', [DashboardController::class, 'show'])->whereNumber('donor')->name('donors.show');
        Route::get('/donantes/{donor}/carnet/imprimir', [DonorCardController::class, 'adminPrint'])->whereNumber('donor')->name('donors.card.print');
        Route::get('/donantes/{donor}/carnet/pdf', [DonorCardController::class, 'adminPdf'])->whereNumber('donor')->name('donors.card.pdf');

        Route::middleware('master')->group(function () {
            Route::post('/consultas/{inquiry}/asignar', [AdminContactInquiryController::class, 'assign'])->whereNumber('inquiry')->name('contact-inquiries.assign');
            Route::get('/usuarios', [AdministrativeUserController::class, 'index'])->name('users.index');
            Route::post('/usuarios', [AdministrativeUserController::class, 'store'])->name('users.store');
            Route::put('/usuarios/{user}', [AdministrativeUserController::class, 'update'])->whereNumber('user')->name('users.update');
        });
    });
});
