<?php

use App\Http\Controllers\ControllerDatosGenerales;
use App\Http\Controllers\ControllerDocumentoObtenido;
use App\Http\Controllers\ControllerDomicilioDeclarante;
use App\Http\Controllers\ControllerEntidadFederativa;
use App\Http\Controllers\ControllerEstatus;
use App\Http\Controllers\ControllerMunicipios;
use App\Http\Controllers\ControllerNivelEsudios;
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
    Route::get('show', [ControllerSituacionPersonalEstadoCivil::class, 'show']);
});
Route::prefix('regimenes')->group(function () {
    Route::get('show', [ControllerRegimemMatrimonial::class, 'show']);
});
Route::prefix('paises')->group(function () {
    Route::get('show', [ControllerPaises::class, 'show']);
    Route::get('showNacionalidad', [ControllerPaises::class, 'showNacionalidad']);
});
Route::prefix('entidades')->group(function () {
    Route::get('show', [ControllerEntidadFederativa::class, 'show']);
});
Route::prefix('entidades')->group(function () {
    Route::get('show', [ControllerEntidadFederativa::class, 'show']);
});
Route::prefix('nivelestudios')->group(function () {
    Route::get('show', [ControllerNivelEsudios::class, 'show']);
});
Route::prefix('documentosbtenidos')->group(function () {
    Route::get('show', [ControllerDocumentoObtenido::class, 'show']);
});
Route::prefix('estatus')->group(function () {
    Route::get('show', [ControllerEstatus::class, 'show']);
});
Route::prefix('datosgenerales')->group(function () {
    Route::post('create', [ControllerDatosGenerales::class, 'create']);
});
Route::prefix('domiciliodeclarante')->group(function () {
    Route::post("create", [ControllerDomicilioDeclarante::class, 'create']);
});
