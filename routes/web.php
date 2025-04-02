<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\reviewController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TalkingvetAssistController;
use App\Http\Controllers\UserController;
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

Route::get('/', function () {
    return redirect(\route('login'));
});


Route::middleware(['web'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/review', [ReviewController::class, 'view'])->name('review.view');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('internal-auth')->group(function () {
    Route::get('assist/home', [TalkingvetAssistController::class, 'index'])->name('assist.home');
    Route::get('user/new', [UserController::class, 'index'])->name('user.new');
    Route::get('summary/{id}', [SummaryController::class, 'index'])->name('summary.view');
    Route::get('prompts', [PromptController::class, 'index'])->name('prompts.home');
    Route::get('prompts/view/{id}', [PromptController::class, 'view'])->name('prompts.view');
    Route::get('prompts/create', [PromptController::class, 'create'])->name('prompts.create');
    Route::post('prompts/store', [PromptController::class, 'store'])->name('prompts.store');
    Route::post('prompts/update', [PromptController::class, 'update'])->name('prompts.update');
    Route::delete('prompts/delete/{id}', [PromptController::class, 'delete'])->name('prompts.delete');
    Route::post('/re-generate-summary', [SummaryController::class, 're_generate']);
});

require __DIR__ . '/auth.php';
