<?php

use App\Http\Controllers\Api\GateCategoryController;
use App\Http\Controllers\Api\GateController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', [GateCategoryController::class, 'index']);
Route::get('/categories/{gateCategory}', [GateCategoryController::class, 'show']);

Route::get('/gates', [GateController::class, 'index']);
Route::get('/gates/{gate}', [GateController::class, 'show']);

Route::post('/projects', [ProjectController::class, 'store']);
Route::patch('/projects/{project}/canvas', [ProjectController::class, 'saveCanvas']);
Route::post('/projects/{project}/result', [ProjectController::class, 'saveResult']);

Route::post('/leads', [LeadController::class, 'store']);
