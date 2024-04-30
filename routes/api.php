<?php

use App\Http\Controllers\ControllerAmbitoPublico;
use App\Http\Controllers\ControllerBienesInmuebles;
use App\Http\Controllers\ControllerDatosCurriculares;
use App\Http\Controllers\ControllerDatosEmpleoCargoComision;
use App\Http\Controllers\ControllerDatosGenerales;
use App\Http\Controllers\ControllerDatosPareja;
use App\Http\Controllers\ControllerDependientesEconomicos;
use App\Http\Controllers\ControllerDocumentoObtenido;
use App\Http\Controllers\ControllerDomicilioDeclarante;
use App\Http\Controllers\ControllerEntidadFederativa;
use App\Http\Controllers\ControllerEstatus;
use App\Http\Controllers\ControllerExperienciaLaboral;
use App\Http\Controllers\ControllerFormaAdquisicion;
use App\Http\Controllers\ControllerFormaPago;
use App\Http\Controllers\ControllerIngresosNetos;
use App\Http\Controllers\ControllerMonedas;
use App\Http\Controllers\ControllerMotivosBaja;
use App\Http\Controllers\ControllerMunicipios;
use App\Http\Controllers\ControllerNivelEsudios;
use App\Http\Controllers\ControllerNivelOrdenGobierno;
use App\Http\Controllers\ControllerNombreEntePublico;
use App\Http\Controllers\ControllerPaises;
use App\Http\Controllers\ControllerRegimemMatrimonial;
use App\Http\Controllers\ControllerRelacionDeclarante;
use App\Http\Controllers\ControllerSectores;
use App\Http\Controllers\ControllerServidorPublico;
use App\Http\Controllers\ControllerSituacionPersonalEstadoCivil;
use App\Http\Controllers\ControllerTipoBienEnajenacionBienes;
use App\Http\Controllers\ControllerTipoInmueble;
use App\Http\Controllers\ControllerTiposInstrumentos;
use App\Http\Controllers\ControllerTitular;
use App\Http\Controllers\ControllerUsers;
use App\Http\Controllers\ControllerValorConforme;
use App\Http\Controllers\ControllerVehiculos;
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
/*
TODO DATOS GENERALES
*/
Route::prefix('estadoCivil')->group(function () {
    Route::get('show', [ControllerSituacionPersonalEstadoCivil::class, 'show']);
});
Route::prefix('regimenes')->group(function () {
    Route::get('show', [ControllerRegimemMatrimonial::class, 'show']);
});

/*
TODO DOMICILIO DECLARANTE
*/

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
Route::prefix('municipios')->group(function () {
    Route::get('show/{code}', [ControllerMunicipios::class, 'show']);
});
/*
TODO DATOS CURRICULARES 
*/
Route::prefix('nivelestudios')->group(function () {
    Route::get('show', [ControllerNivelEsudios::class, 'show']);
});
Route::prefix('documentosbtenidos')->group(function () {
    Route::get('show', [ControllerDocumentoObtenido::class, 'show']);
});
Route::prefix('estatus')->group(function () {
    Route::get('show', [ControllerEstatus::class, 'show']);
});
/*
TODO DATOS DEL EMPLEO CARGO O COMISION QUE INICIA 
*/
Route::prefix('nombrentepublico')->group(function () {
    Route::get('show', [ControllerNombreEntePublico::class, 'show']);
});
/*
TODO PAGINA 6 DATOSPAREJA
*/
Route::prefix('sectores')->group(function () {
    Route::get('show', [ControllerSectores::class, 'show']);
});
Route::prefix('monedas')->group(function () {
    Route::get('show', [ControllerMonedas::class, 'show']);
});
Route::prefix('tipoinstrumento')->group(function () {
    Route::get("show", [ControllerTiposInstrumentos::class, 'show']);
});
Route::prefix('bienenajenacion')->group(function () {
    Route::get('show', [ControllerTipoBienEnajenacionBienes::class, 'show']);
});
Route::prefix('relacioncondeclarante')->group(function () {
    Route::get('show', [ControllerRelacionDeclarante::class, 'show']);
});
Route::prefix('ambitospublicos')->group(function () {
    Route::get('show', [ControllerAmbitoPublico::class, 'show']);
});
Route::prefix('nivelordengobierno')->group(function () {
    Route::get('show', [ControllerNivelOrdenGobierno::class, 'show']);
});
Route::prefix('titularbien')->group(function () {
    Route::get('show', [ControllerTitular::class, 'show']);
});
/*
TODO PAGINA 10 Bienes Inmuebles (Situación Actual)
*/
Route::prefix('tipoinmueble')->group(function () {
    Route::get('show', [ControllerTipoInmueble::class, 'show']);
});
Route::prefix('formadquisicion')->group(function () {
    Route::get('show', [ControllerFormaAdquisicion::class, 'show']);
});
Route::prefix('formapago')->group(function () {
    Route::get('show', [ControllerFormaPago::class, 'show']);
});
Route::prefix('valorconforme')->group(function () {
    Route::get('show', [ControllerValorConforme::class, 'show']);
});
Route::prefix('motivobaja')->group(function () {
    Route::get('show', [ControllerMotivosBaja::class, 'show']);
});
/*
TODO PAGINA 11 Vehiculos 
*/

Route::prefix('tipovehiculos')->group(function () {
    Route::get('show', [ControllerVehiculos::class, 'show']);
});


/*
! PAGINA 1
*/
Route::prefix('datosgenerales')->group(function () {
    Route::post('create', [ControllerDatosGenerales::class, 'create']);
});
/*
! PAGINA 2
*/
Route::prefix('domiciliodeclarante')->group(function () {
    Route::post("create", [ControllerDomicilioDeclarante::class, 'create']);
});
/*
! PAGINA 3
*/
Route::prefix('datoscurriculares')->group(function () {
    Route::post("create", [ControllerDatosCurriculares::class, 'create']);
});
/*
! PAGINA 4
*/
Route::prefix('datoscargoscomision')->group(function () {
    Route::post("create", [ControllerDatosEmpleoCargoComision::class, 'create']);
});
/*
! PAGINA 5
*/
Route::prefix('experiencialaboral')->group(function () {
    Route::post("create", [ControllerExperienciaLaboral::class, 'create']);
});
/*
! PAGINA 6
*/
Route::prefix('datospareja')->group(function () {
    Route::post("create", [ControllerDatosPareja::class, 'create']);
});
/*
! PAGINA 7
*/
Route::prefix('dependienteseconomicos')->group(function () {
    Route::post("create", [ControllerDependientesEconomicos::class, 'create']);
});
/*
! PAGINA 8
*/
Route::prefix('ingresos')->group(function () {
    Route::post("create", [ControllerIngresosNetos::class, 'create']);
});
/*
! PAGINA 9
*/
Route::prefix('servidorpublico')->group(function () {
    Route::post("create", [ControllerServidorPublico::class, 'create']);
});

/*
! PAGINA 10
*/
Route::prefix('bienesinmuebles')->group(function () {
    Route::post("create", [ControllerBienesInmuebles::class, 'create']);
});