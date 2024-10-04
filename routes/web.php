<?php

use App\Events\MessageSent;
use App\Http\Controllers\ClaseController;
use App\Http\Controllers\DiagramClassController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalaController;
use App\Models\Clase;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        // 'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
    // return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('index', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard1');

    Route::get('/about', fn () => Inertia::render('About'))->name('about');

    Route::get('users', [UserController::class, 'index'])->name('users.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('salas', SalaController::class);
    Route::prefix('salas')->group(function () {
        Route::post('join_room', [SalaController::class, 'join_room'])->name('sala.join_room');
        Route::post('get_room', [SalaController::class, 'get_room'])->name('sala.get_room');
    });
    
    Route::prefix('diagram_class')->group(function () {
        Route::get('indexD', [DiagramClassController::class, 'index'])->name('diagram_class.index');
        Route::get('index_get', [DiagramClassController::class, 'index_get'])->name('diagram_class.index_get');
        Route::post('get_room', [DiagramClassController::class, 'getRoom'])->name('diagram_class.get_room');
        Route::get('sent_data', [DiagramClassController::class, 'sent_data'])->name('diagram_class.sent_data');
        Route::post('peticion_socket', [DiagramClassController::class, 'peticion_socket'])->name('diagram_class.peticion_socket');
        
        Route::get('download-xmi', [DiagramClassController::class, 'download_diagram'])->name('diagram_class.download_diagram');
    });
    Route::prefix('clase')->group(function () {
        Route::get('get_id_key', [ClaseController::class, 'get_id_key'])->name('clase.get_id_key');
        
    });
});


require __DIR__.'/auth.php';
