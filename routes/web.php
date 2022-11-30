<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Papers\PapersRepository;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (PapersRepository $repository) {
    return view('dashboard', [
        'papers' => request()->has('q')
            ? $repository->search(request('q'))
            : App\Models\Paper::all(),
    ]);
})->middleware(['auth'])->name('dashboard'); 

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
