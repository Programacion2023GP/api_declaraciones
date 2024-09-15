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
                'Id_Pais' => $request->Id_Pais,
                'EstadoProvincia' => $request->EstadoProvincia,

                'CuentaConOtroCargoPublico' => 0,
            ];

            $DatosCurriculares = DB::table('DECL_DatosEmpleoCargoComision')->insert($datosInsercion);




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | DATOS DEL EMPLEO guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $DatosCurriculares;
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial, 4);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosEmpleoCargoComision', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, Request $request, $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {

            $data = DB::table('DECL_DatosEmpleoCargoComision');
            if (!$id) {
                $data = $data->whereIn('Id_SituacionPatrimonial', $request->masiveIds);
                # code...
            } else {
                $data = $data->where('Id_SituacionPatrimonial', $id);
            }
            $data = $data->select('*') // Selecciona todas las columnas
                ->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de tipo de adeudos.';
            $response->data["alert_text"] = "lista de inversion";
            $response->data["result"] = $data;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function update(Response $response, Request $request, $id)

    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe


            // Actualizar el registro
            DB::table('DECL_DatosEmpleoCargoComision')
                ->where('Id_DatosEmpleoCargoComision', $id)
                ->update([
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
                    'Aclaraciones' => $request->Aclaraciones,
                    'Id_Pais' => $request->Id_Pais,
                    'EstadoProvincia' => $request->EstadoProvincia,
                    'CuentaConOtroCargoPublico' => 0,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | DATOS DEL EMPLEO actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_regimenmatrimonial', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
