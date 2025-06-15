<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlotController;

// Route untuk menampilkan dashboard
Route::get('/dashboard', [SlotController::class, 'showDashboard']);
