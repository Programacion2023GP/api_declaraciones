<?php

use App\Http\Controllers\ControllerAdeudosPasivos;
use App\Http\Controllers\ControllerAdscripcion;
use App\Http\Controllers\ControllerAmbitoPublico;
use App\Http\Controllers\ControllerApartados;
use App\Http\Controllers\ControllerApoyos;
use App\Http\Controllers\ControllerBeneficiosPrivados;
use App\Http\Controllers\ControllerBienesInmuebles;
use App\Http\Controllers\ControllerBienesMuebles;
use App\Http\Controllers\ControllerClientes;
use App\Http\Controllers\ControllerCompaq;
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
use App\Http\Controllers\ControllerFideocomiso;
use App\Http\Controllers\ControllerFormaAdquisicion;
use App\Http\Controllers\ControllerFormaPago;
use App\Http\Controllers\ControllerFormaRecepcion;
use App\Http\Controllers\ControllerIngresosNetos;
use App\Http\Controllers\ControllerInstituciones;
use App\Http\Controllers\ControllerInversionesCuentasValores;
use App\Http\Controllers\ControllerMonedas;
use App\Http\Controllers\ControllerMotivosBaja;
use App\Http\Controllers\ControllerMunicipios;
use App\Http\Controllers\ControllerNivelEsudios;
use App\Http\Controllers\ControllerNivelOrdenGobierno;
use App\Http\Controllers\ControllerNombreEntePublico;
use App\Http\Controllers\ControllerPaises;
use App\Http\Controllers\ControllerParticipacionEmpresas;
use App\Http\Controllers\ControllerPrestamosComodatos;
use App\Http\Controllers\ControllerRegimemMatrimonial;
use App\Http\Controllers\ControllerRelacionDeclarante;
use App\Http\Controllers\ControllerRepresentaciones;
use App\Http\Controllers\ControllerRoles;
use App\Http\Controllers\ControllerSector;
use App\Http\Controllers\ControllerSectores;
use App\Http\Controllers\ControllerServidorPublico;
use App\Http\Controllers\ControllerSituacionPatrimonial;
use App\Http\Controllers\ControllerSituacionPersonalEstadoCivil;
use App\Http\Controllers\ControllerSubTipoInversion;
use App\Http\Controllers\ControllerTipoApoyo;
use App\Http\Controllers\ControllerTipoBeneficio;
use App\Http\Controllers\ControllerTipoBienEnajenacionBienes;
use App\Http\Controllers\ControllerTipoBienesMuebles;
use App\Http\Controllers\ControllerTipodeParticipacion;
use App\Http\Controllers\ControllerTipoDePersona;
use App\Http\Controllers\ControllerTipoDeRepresentacion;
use App\Http\Controllers\ControllerTipoFideocomiso;
use App\Http\Controllers\ControllerTipoInmueble;
use App\Http\Controllers\ControllerTipoIntegrante;
use App\Http\Controllers\ControllerTipoInversion;
use App\Http\Controllers\ControllerTiposAdeudos;
use App\Http\Controllers\ControllerTiposInstrumentos;
use App\Http\Controllers\ControllerTiposVehiculos;
use App\Http\Controllers\ControllerTitular;
use App\Http\Controllers\ControllerTitularVehiculos;
use App\Http\Controllers\ControllerTomaDecisiones;
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
Route::prefix('usuarios')->group(function () {
    Route::post("login", [ControllerUsers::class, 'login']);
    Route::post("create", [ControllerUsers::class, 'create']);
    Route::get("index", [ControllerUsers::class, 'index']);
    Route::delete('delete/{id}', [ControllerUsers::class, 'delete']);
    Route::put('update/{id}', [ControllerUsers::class, 'update']);
});
Route::prefix('intengrantes')->group(function () {

    Route::get("show", [ControllerTipoIntegrante::class, 'show']);
});
Route::prefix('adscripcion')->group(function () {

    Route::get('index', [ControllerAdscripcion::class, 'index']);
    Route::get('show', [ControllerAdscripcion::class, 'show']);
    Route::post('create', [ControllerAdscripcion::class, 'create']);
    Route::put('update/{id}', [ControllerAdscripcion::class, 'update']);
    Route::delete('delete/{id}', [ControllerAdscripcion::class, 'delete']);
});
Route::prefix('estadoCivil')->group(function () {
    Route::get('index', [ControllerSituacionPersonalEstadoCivil::class, 'index']);
    Route::get('show', [ControllerSituacionPersonalEstadoCivil::class, 'show']);
    Route::post('create', [ControllerSituacionPersonalEstadoCivil::class, 'create']);
    Route::put('update/{id}', [ControllerSituacionPersonalEstadoCivil::class, 'update']);
    Route::delete('delete/{id}', [ControllerSituacionPersonalEstadoCivil::class, 'delete']);
});
Route::prefix('regimenes')->group(function () {
    Route::get('index', [ControllerRegimemMatrimonial::class, 'index']);
    Route::get('show', [ControllerRegimemMatrimonial::class, 'show']);
    Route::post('create', [ControllerRegimemMatrimonial::class, 'create']);
    Route::put('update/{id}', [ControllerRegimemMatrimonial::class, 'update']);
    Route::delete('delete/{id}', [ControllerRegimemMatrimonial::class, 'delete']);
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
// Route::prefix('entidades')->group(function () {
//     Route::get('show', [ControllerEntidadFederativa::class, 'show']);
// });
Route::prefix('municipios')->group(function () {
    Route::get('show/{code?}', [ControllerMunicipios::class, 'show']);
});
/*
TODO DATOS CURRICULARES 
*/
Route::prefix('nivelestudios')->group(function () {
    Route::get('show', [ControllerNivelEsudios::class, 'show']);
    Route::get('index', [ControllerNivelEsudios::class, 'index']);
    Route::post('create', [ControllerNivelEsudios::class, 'create']);
    Route::put('update/{id}', [ControllerNivelEsudios::class, 'update']);
    Route::delete('delete/{id}', [ControllerNivelEsudios::class, 'delete']);
});
Route::prefix('documentosbtenidos')->group(function () {
    Route::get('show', [ControllerDocumentoObtenido::class, 'show']);
    Route::get('index', [ControllerDocumentoObtenido::class, 'index']);
    Route::post('create', [ControllerDocumentoObtenido::class, 'create']);
    Route::put('update/{id}', [ControllerDocumentoObtenido::class, 'update']);
    Route::delete('delete/{id}', [ControllerDocumentoObtenido::class, 'delete']);
});
Route::prefix('estatus')->group(function () {
    Route::get('show', [ControllerEstatus::class, 'show']);
    Route::get('index', [ControllerEstatus::class, 'index']);
    Route::get('show', [ControllerEstatus::class, 'show']);
    Route::post('create', [ControllerEstatus::class, 'create']);
    Route::put('update/{id}', [ControllerEstatus::class, 'update']);
    Route::delete('delete/{id}', [ControllerEstatus::class, 'delete']);
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
    Route::get('index', [ControllerSectores::class, 'index']);
    Route::post('create', [ControllerSectores::class, 'create']);
    Route::put('update/{id}', [ControllerSectores::class, 'update']);
    Route::delete('delete/{id}', [ControllerSectores::class, 'delete']);
});
Route::prefix('monedas')->group(function () {
    Route::get('show', [ControllerMonedas::class, 'show']);
    Route::get('index', [ControllerMonedas::class, 'index']);
    Route::post('create', [ControllerMonedas::class, 'create']);
    Route::put('update/{id}', [ControllerMonedas::class, 'update']);
    Route::delete('delete/{id}', [ControllerMonedas::class, 'delete']);
});
Route::prefix('tipoinstrumento')->group(function () {
    Route::get('show', [ControllerTiposInstrumentos::class, 'show']);
    Route::get('index', [ControllerTiposInstrumentos::class, 'index']);
    Route::post('create', [ControllerTiposInstrumentos::class, 'create']);
    Route::put('update/{id}', [ControllerTiposInstrumentos::class, 'update']);
    Route::delete('delete/{id}', [ControllerTiposInstrumentos::class, 'delete']);
});
Route::prefix('bienenajenacion')->group(function () {
    Route::get('show', [ControllerTipoBienEnajenacionBienes::class, 'show']);
    Route::get('index', [ControllerTipoBienEnajenacionBienes::class, 'index']);
    Route::post('create', [ControllerTipoBienEnajenacionBienes::class, 'create']);
    Route::put('update/{id}', [ControllerTipoBienEnajenacionBienes::class, 'update']);
    Route::delete('delete/{id}', [ControllerTipoBienEnajenacionBienes::class, 'delete']);
});
Route::prefix('relacioncondeclarante')->group(function () {
    Route::get('show', [ControllerRelacionDeclarante::class, 'show']);
    Route::get('index', [ControllerRelacionDeclarante::class, 'index']);
    Route::post('create', [ControllerRelacionDeclarante::class, 'create']);
    Route::put('update/{id}', [ControllerRelacionDeclarante::class, 'update']);
    Route::delete('delete/{id}', [ControllerRelacionDeclarante::class, 'delete']);
});
Route::prefix('ambitospublicos')->group(function () {
    Route::get('show', [ControllerAmbitoPublico::class, 'show']);
    Route::get('index', [ControllerAmbitoPublico::class, 'index']);
    Route::post('create', [ControllerAmbitoPublico::class, 'create']);
    Route::put('update/{id}', [ControllerAmbitoPublico::class, 'update']);
    Route::delete('delete/{id}', [ControllerAmbitoPublico::class, 'delete']);
});
Route::prefix('nivelordengobierno')->group(function () {
    Route::get('show', [ControllerNivelOrdenGobierno::class, 'show']);
    Route::get('index', [ControllerNivelOrdenGobierno::class, 'index']);
    Route::post('create', [ControllerNivelOrdenGobierno::class, 'create']);
    Route::put('update/{id}', [ControllerNivelOrdenGobierno::class, 'update']);
    Route::delete('delete/{id}', [ControllerNivelOrdenGobierno::class, 'delete']);
});

Route::prefix('titularbien')->group(function () {
    Route::get('show', [ControllerTitular::class, 'show']);
    Route::get('index', [ControllerTitular::class, 'index']);
    Route::post('create', [ControllerTitular::class, 'create']);
    Route::put('update/{id}', [ControllerTitular::class, 'update']);
    Route::delete('delete/{id}', [ControllerTitular::class, 'delete']);
});
/*
TODO PAGINA 10 Bienes Inmuebles (Situación Actual)
*/
Route::prefix('tipoinmueble')->group(function () {
    Route::get('show', [ControllerTipoInmueble::class, 'show']);
    Route::get('index', [ControllerTipoInmueble::class, 'index']);
    Route::post('create', [ControllerTipoInmueble::class, 'create']);
    Route::put('update/{id}', [ControllerTipoInmueble::class, 'update']);
    Route::delete('delete/{id}', [ControllerTipoInmueble::class, 'delete']);
});
Route::prefix('formadquisicion')->group(function () {
    Route::get('show', [ControllerFormaAdquisicion::class, 'show']);
    Route::get('index', [ControllerFormaAdquisicion::class, 'index']);
    Route::post('create', [ControllerFormaAdquisicion::class, 'create']);
    Route::put('update/{id}', [ControllerFormaAdquisicion::class, 'update']);
    Route::delete('delete/{id}', [ControllerFormaAdquisicion::class, 'delete']);
});
Route::prefix('formapago')->group(function () {
    Route::get('show', [ControllerFormaPago::class, 'show']);
    Route::get('index', [ControllerFormaPago::class, 'index']);
    Route::post('create', [ControllerFormaPago::class, 'create']);
    Route::put('update/{id}', [ControllerFormaPago::class, 'update']);
    Route::delete('delete/{id}', [ControllerFormaPago::class, 'delete']);
});
Route::prefix('valorconforme')->group(function () {
    Route::get('show', [ControllerValorConforme::class, 'show']);
});
Route::prefix('motivobaja')->group(function () {
    Route::get('show', [ControllerMotivosBaja::class, 'show']);
    Route::get('index', [ControllerMotivosBaja::class, 'index']);
    Route::post('create', [ControllerMotivosBaja::class, 'create']);
    Route::put('update/{id}', [ControllerMotivosBaja::class, 'update']);
    Route::delete('delete/{id}', [ControllerMotivosBaja::class, 'delete']);
});
/*
TODO PAGINA 11 Vehiculos 
*/

Route::prefix('tipovehiculos')->group(function () {
    Route::get('show', [ControllerVehiculos::class, 'show']);
    Route::get('index', [ControllerVehiculos::class, 'index']);
    Route::post('create', [ControllerVehiculos::class, 'create']);
    Route::put('update/{id}', [ControllerVehiculos::class, 'update']);
    Route::delete('delete/{id}', [ControllerVehiculos::class, 'delete']);
});

Route::prefix('titularvehiculos')->group(function () {
    Route::get('show', [ControllerTitularVehiculos::class, 'show']);
});
/*
TODO PAGINA 12 Vehiculos 
*/
Route::prefix('tiposbienesmuebles')->group(function () {
    Route::get('show', [ControllerTipoBienesMuebles::class, 'show']);
    Route::get('index', [ControllerTipoBienesMuebles::class, 'index']);
    Route::post('create', [ControllerTipoBienesMuebles::class, 'create']);
    Route::put('update/{id}', [ControllerTipoBienesMuebles::class, 'update']);
    Route::delete('delete/{id}', [ControllerTipoBienesMuebles::class, 'delete']);
});
/*
TODO PAGINA 13 CuentasValoresInversion 
*/
Route::prefix('tipoinversion')->group(function () {
    Route::get('show', [ControllerTipoInversion::class, 'show']);
    Route::get('index', [ControllerTipoInversion::class, 'index']);
    Route::post('create', [ControllerTipoInversion::class, 'create']);
    Route::put('update/{id}', [ControllerTipoInversion::class, 'update']);
    Route::delete('delete/{id}', [ControllerTipoInversion::class, 'delete']);
});
Route::prefix('subtiposinversion')->group(function () {
    Route::get('show/{code}', [ControllerSubTipoInversion::class, 'show']);
    Route::get('index', [ControllerSubTipoInversion::class, 'index']);
    Route::get('showAll', [ControllerSubTipoInversion::class, 'showAll']);

    Route::post('create', [ControllerSubTipoInversion::class, 'create']);
    Route::put('update/{id}', [ControllerSubTipoInversion::class, 'update']);
    Route::delete('delete/{id}', [ControllerSubTipoInversion::class, 'delete']);
    
});
/*
/*
TODO PAGINA 14 AdeudosPasivos
*/
Route::prefix('tiposadeudos')->group(function () {
    Route::get('show', [ControllerTiposAdeudos::class, 'show']);
    Route::get('index', [ControllerTiposAdeudos::class, 'index']);
    Route::post('create', [ControllerTiposAdeudos::class, 'create']);
    Route::put('update/{id}', [ControllerTiposAdeudos::class, 'update']);
    Route::delete('delete/{id}', [ControllerTiposAdeudos::class, 'delete']);
});

/*
TODO DECLARACION DE INTERES PARTICIPACION DE EMPRESAS E SOCIEDADES O ASOCION
*/
Route::prefix('tipoparticipacion')->group(function () {
    Route::get("show", [ControllerTipodeParticipacion::class, 'show']);
    // Route::get("index/{id}", [ControllerTipodeParticipacion::class, 'index']);
    // Route::post("update/{id}", [ControllerTipodeParticipacion::class, 'update']);
});

Route::prefix('sector')->group(function () {
    Route::get("show", [ControllerSector::class, 'show']);
    // Route::get("index/{id}", [ControllerTipodeParticipacion::class, 'index']);
    // Route::post("update/{id}", [ControllerTipodeParticipacion::class, 'update']);
});

/*
TODO Apoyos o Beneficios Públicos (Hasta los 2 últimos años)

*/


Route::prefix('tipoapoyos')->group(function () {
    Route::get("show", [ControllerTipoApoyo::class, 'show']);
});
Route::prefix('formarecepcion')->group(function () {
    Route::get("show", [ControllerFormaRecepcion::class, 'show']);
});
Route::prefix('tipobeneficios')->group(function () {
    Route::get("show", [ControllerTipoBeneficio::class, 'show']);
});
/*
/*
TODO REPRESENTACION (Hasta los 2 últimos años)

*/


Route::prefix('representacion')->group(function () {
    Route::get("show", [ControllerTipoDeRepresentacion::class, 'show']);
});
Route::prefix('tipopersona')->group(function () {
    Route::get("show", [ControllerTipoDePersona::class, 'show']);
});
/*
TODO FIDEOCOMISOS
*/

Route::prefix('tipofideocomisos')->group(function () {
    Route::get("show", [ControllerTipoFideocomiso::class, 'show']);
});
/*
! PAGINA 1
*/
Route::prefix('datosgenerales')->group(function () {
    Route::post('create', [ControllerDatosGenerales::class, 'create']);
    Route::get('index/{id}', [ControllerDatosGenerales::class, 'index']);
    Route::get('acuse/{id}', [ControllerDatosGenerales::class, 'acuse']);
    Route::post("update/{id}", [ControllerDatosGenerales::class, 'update']); //put 

});
/*
! PAGINA 2
*/
Route::prefix('domiciliodeclarante')->group(function () {
    Route::post("create", [ControllerDomicilioDeclarante::class, 'create']);
    Route::get("index/{id}", [ControllerDomicilioDeclarante::class, 'index']);
    Route::post("update/{id}", [ControllerDomicilioDeclarante::class, 'update']); //put 


});
/*
! PAGINA 3
*/
Route::prefix('datoscurriculares')->group(function () {
    Route::post("create", [ControllerDatosCurriculares::class, 'create']);
    Route::get("index/{id}", [ControllerDatosCurriculares::class, 'index']);
    Route::post("update/{id}", [ControllerDatosCurriculares::class, 'update']); //put 


});
/*
! PAGINA 4
*/
Route::prefix('datoscargoscomision')->group(function () {
    Route::post("create", [ControllerDatosEmpleoCargoComision::class, 'create']);
    Route::get("index/{id}", [ControllerDatosEmpleoCargoComision::class, 'index']);
    Route::post("update/{id}", [ControllerDatosEmpleoCargoComision::class, 'update']); //put 
});
/*
! PAGINA 5
*/
Route::prefix('experiencialaboral')->group(function () {
    Route::post("create", [ControllerExperienciaLaboral::class, 'create']);
    Route::get("index/{id}", [ControllerExperienciaLaboral::class, 'index']);
    Route::post("update/{id}", [ControllerExperienciaLaboral::class, 'update']); //put 


});
/*
! PAGINA 6
*/
Route::prefix('datospareja')->group(function () {
    Route::post("create", [ControllerDatosPareja::class, 'create']);
    Route::get("index/{id}", [ControllerDatosPareja::class, 'index']);
    Route::post("update/{id}", [ControllerDatosPareja::class, 'update']); //put 


});
/*
! PAGINA 7
*/
Route::prefix('dependienteseconomicos')->group(function () {
    Route::post("create", [ControllerDependientesEconomicos::class, 'create']);
    Route::get("index/{id}", [ControllerDependientesEconomicos::class, 'index']);
    Route::post("update/{id}", [ControllerDependientesEconomicos::class, 'update']); //put 

});
/*
! PAGINA 8
*/
Route::prefix('ingresos')->group(function () {
    Route::post("create", [ControllerIngresosNetos::class, 'create']);
    Route::get("index/{id}", [ControllerIngresosNetos::class, 'index']);
    Route::post("update/{id}", [ControllerIngresosNetos::class, 'update']);
});
/*
! PAGINA 9
*/
Route::prefix('servidorpublico')->group(function () {
    Route::post("create", [ControllerServidorPublico::class, 'create']);
    Route::get("index/{id}", [ControllerServidorPublico::class, 'index']);
    Route::post("update/{id}", [ControllerServidorPublico::class, 'update']);
});

/*
! PAGINA 10
*/
Route::prefix('bienesinmuebles')->group(function () {
    Route::post("create", [ControllerBienesInmuebles::class, 'create']);
    Route::get("index/{id}", [ControllerBienesInmuebles::class, 'index']);
    Route::post("update/{id}", [ControllerBienesInmuebles::class, 'update']);
});
/*
! PAGINA 11
*/
Route::prefix('vehiculos')->group(function () {
    Route::post("create", [ControllerTiposVehiculos::class, 'create']);
    Route::get("index/{id}", [ControllerTiposVehiculos::class, 'index']);
    Route::post("update/{id}", [ControllerTiposVehiculos::class, 'update']);
});

/*
! PAGINA 12
*/
Route::prefix('bienesmuebles')->group(function () {
    Route::post("create", [ControllerBienesMuebles::class, 'create']);
    Route::get("index/{id}", [ControllerBienesMuebles::class, 'index']);
    Route::post("update/{id}", [ControllerBienesMuebles::class, 'update']);
});
/*
! PAGINA 13
*/
Route::prefix('inversionescuentas')->group(function () {
    Route::post("create", [ControllerInversionesCuentasValores::class, 'create']);
    Route::get("index/{id}", [ControllerInversionesCuentasValores::class, 'index']);
    Route::post("update/{id}", [ControllerInversionesCuentasValores::class, 'update']);
});
/*
! PAGINA 14
*/
Route::prefix('adeudospasivos')->group(function () {
    Route::post("create", [ControllerAdeudosPasivos::class, 'create']);
    Route::get("index/{id}", [ControllerAdeudosPasivos::class, 'index']);
    Route::post("update/{id}", [ControllerAdeudosPasivos::class, 'update']);
});
/*
! PAGINA 15
*/
Route::prefix('prestamoscomodatos')->group(function () {
    Route::post("create", [ControllerPrestamosComodatos::class, 'create']);
    Route::get("index/{id}", [ControllerPrestamosComodatos::class, 'index']);
    Route::post("update/{id}", [ControllerPrestamosComodatos::class, 'update']);
});

/*
? DECLARACION DE INTERESES
*/
Route::prefix('participacionempresas')->group(function () {
    Route::post("create", [ControllerParticipacionEmpresas::class, 'create']);
    Route::get("index/{id}", [ControllerParticipacionEmpresas::class, 'index']);
    Route::post("update/{id}", [ControllerParticipacionEmpresas::class, 'update']);
});
Route::prefix('tomadecisiones')->group(function () {
    Route::post("create", [ControllerTomaDecisiones::class, 'create']);
    Route::get("index/{id}", [ControllerTomaDecisiones::class, 'index']);
    Route::post("update/{id}", [ControllerTomaDecisiones::class, 'update']);
});
Route::prefix('apoyos')->group(function () {
    Route::post("create", [ControllerApoyos::class, 'create']);
    Route::get("index/{id}", [ControllerApoyos::class, 'index']);
    Route::post("update/{id}", [ControllerApoyos::class, 'update']);
});
Route::prefix('representaciones')->group(function () {
    Route::post("create", [ControllerRepresentaciones::class, 'create']);
    Route::get("index/{id}", [ControllerRepresentaciones::class, 'index']);
    Route::post("update/{id}", [ControllerRepresentaciones::class, 'update']);
});

Route::prefix('clientesprincipales')->group(function () {
    Route::post("create", [ControllerClientes::class, 'create']);
    Route::get("index/{id}", [ControllerClientes::class, 'index']);
    Route::post("update/{id}", [ControllerClientes::class, 'update']);
});

Route::prefix('beneficiosprivados')->group(function () {
    Route::post("create", [ControllerBeneficiosPrivados::class, 'create']);
    // Route::get("index/{id}", [ControllerPrestamosComodatos::class, 'index']);
    // Route::post("update/{id}", [ControllerPrestamosComodatos::class, 'update']);
});
Route::prefix('fideocomisos')->group(function () {
    Route::post("create", [ControllerFideocomiso::class, 'create']);
    // Route::get("index/{id}", [ControllerPrestamosComodatos::class, 'index']);
    // Route::post("update/{id}", [ControllerPrestamosComodatos::class, 'update']);
});
/*
! INSERCCION DE CADA HOJA ES DECIR EN LA SITUACION 1 SE INSERTO LA HOJA 1 Y ASI
*/
Route::prefix('apartados')->group(function () {

    Route::get('all', [ControllerApartados::class, 'all']);
    Route::post("create/{situacionPatrimonial}/{hoja}/{borrar?}", [ControllerApartados::class, 'create']);
    Route::post("interes/{interes}/{hoja}/{borrar?}/{idUser?}/{crear?}", [ControllerApartados::class, 'interes']);

    Route::get('show/{id}', [ControllerApartados::class, 'show']);
    Route::get('hoja/{id}', [ControllerApartados::class, 'hoja']);
    Route::get('exist/{id}/{hoja}', [ControllerApartados::class, 'exist']);

    // Route::get('existeapartado/{id}/hoja', [ControllerApartados::class, 'hoja']);

});
/*
! CREACION DE USUARIOS
*/

/*
! COMPAQ
*/

Route::prefix('compaq')->group(function () {
    Route::get('show/{nomina}', [ControllerCompaq::class, 'show']);
});







/*
TODO PETICIONES PARA EL FORM DE USUARIOS
*/
Route::prefix('roles')->group(function () {
    Route::get("show", [ControllerRoles::class, 'show']);
});

Route::prefix('situacionpatrimonial')->group(function () {
    Route::get("index/{id}/{hoja}/{situacion?}", [ControllerSituacionPatrimonial::class, 'index']);
    Route::delete("delete/{id}", [ControllerSituacionPatrimonial::class, 'delete']); //put 
});


/*
TODO PETICIONES PARA EL FORM DE USUARIOS
*/
Route::prefix('tipoinstituciones')->group(function () {
    Route::get("show", [ControllerInstituciones::class, 'show']);
});



// Route::prefix('usuarios')->group(function () {
//     Route::post("create", [ControllerRoles::class, 'create']);
// });
