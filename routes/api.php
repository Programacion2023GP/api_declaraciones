<?php

use App\Http\Controllers\ControllerPaises;
use App\Http\Controllers\ControllerRegimemMatrimonial;
use App\Http\Controllers\ControllerSituacionPersonalEstadoCivil;
use App\Http\Controllers\ControllerUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post("login", [ControllerUsers::class, 'login']);
Route::prefix('estadoCivil')->group(function () {
    Route::get('show', [ControllerSituacionPersonalEstadoCivil::class,'show']);
});
Route::prefix('regimenes')->group(function () {
    Route::get('show', [ControllerRegimemMatrimonial::class,'show']);
});
Route::prefix('paises')->group(function(){
    Route::get('show',[ControllerPaises::class,'show']);
    Route::get('showNacionalidad',[ControllerPaises::class,'showNacionalidad']);
});
