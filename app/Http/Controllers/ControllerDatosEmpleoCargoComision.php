<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;

class ControllerDatosEmpleoCargoComision extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {

            $datosInsercion = [
                'Id_NivelOrdenGobierno' => $request->Id_NivelOrdenGobierno,
                'Id_AmbitoPublico' => $request->Id_AmbitoPublico,
                'NombreEntePublico' => $request->NombreEntePublico,
                'EsEnMexico' => $request->EsEnMexico,
                'AreaAdscripcion' => $request->AreaAdscripcion,
                'EmpleoCargoComision' => $request->EmpleoCargoComision,
                'NivelEmpleoCargoComision' => $request->NivelEmpleoCargoComision,
                'ContratadoPorHonorarios' => $request->ContratadoPorHonorarios,
                'FuncionPrincipal' => $request->FuncionPrincipal,
                'FechaTomaConclusionPosesion' => $request->FechaTomaConclusionPosesion,
                'ExtensionTelefonoOficina' => $request->ExtensionTelefonoOficina,
                'TelefonoOficina' => $request->TelefonoOficina,
                'Calle' => $request->Calle,
                'CodigoPostal' => $request->CodigoPostal,
                'NumeroExterior' => $request->NumeroExterior,
                'NumeroInterior' => $request->NumeroInterior,
                'ColoniaLocalidad' => $request->ColoniaLocalidad,
                'Id_MunicipioAlcaldia' => $request->Id_MunicipioAlcaldia,
                'Id_EntidadFederativa' => $request->Id_EntidadFederativa,
                'Id_SituacionPatrimonial' => $request->Id_SituacionPatrimonial,
                'Aclaraciones' => $request->Aclaraciones,
                'CuentaConOtroCargoPublico'=>0,
            ];
            
            $DatosCurriculares = DB::table('DECL_DatosEmpleoCargoComision')->insert($datosInsercion);




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Datos generales guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosEmpleoCargoComision', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
