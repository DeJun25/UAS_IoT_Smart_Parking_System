<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlotController;

// Route for displaying dashboard data directly from the database
Route::get('/dashboard', [SlotController::class, 'showDashboard']);  // Dashboard view route

// Route for fetching daily hours for the chart
Route::get('/slots/daily-hours', [SlotController::class, 'getSlots']); // Route for daily chart data

// Route to receive POST data from ESP32 and save to the database
Route::post('/parking-slot', [SlotController::class, 'store']); // Route to store data from ESP32

// Route for fetching the latest status for slots (for real-time updates)
Route::get('/slots/latest-status', [SlotController::class, 'getLatestStatus']); // Route for real-time slot status
