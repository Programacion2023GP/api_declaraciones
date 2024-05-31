<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerDomicilioDeclarante extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $datosInsercion = [
                'Id_SituacionPatrimonial' => $request->Id_SituacionPatrimonial,
                'EsEnMexico' => $request->EsEnMexico,
                'Calle' => $request->Calle,
                'NumeroExterior' => $request->NumeroExterior,
                'NumeroInterior' => $request->NumeroInterior ?? null,
                'CodigoPostal' => $request->CodigoPostal,
                'ColoniaLocalidad' => $request->ColoniaLocalidad,
                'Aclaraciones' => $request->Aclaraciones ?? null,
                'EstadoProvincia' => $request->has('EstadoProvincia') ? $request->EstadoProvincia : null,
                'Id_Pais' => $request->Id_Pais,
                'Id_EntidadFederativa' => $request->Id_EntidadFederativa,
                'Id_MunicipioAlcaldia' => $request->Id_MunicipioAlcaldia,

            ];
            $datosDomicilio = DB::table('DECL_DomicilioDeclarante')->insert($datosInsercion);




            // return "fff";
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Datos del DOMICILIO DEL DECLARANTE guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $datosDomicilio;
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial, 2);
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, int $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('DECL_DomicilioDeclarante') // Selecciona la tabla DECL_DatosGenerales
                ->where('Id_SituacionPatrimonial', $id) // Agrega una condición where para filtrar por Id_SituacionPatrimonial
                ->select('*') // Selecciona todas las columnas
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
            DB::table('DECL_DomicilioDeclarante')
                ->where('Id_DomicilioDeclarante', $id)
                ->update([
                    'EsEnMexico' => $request->EsEnMexico,
                    'Calle' => $request->Calle,
                    'NumeroExterior' => $request->NumeroExterior,
                    'NumeroInterior' => $request->NumeroInterior ?? null,
                    'CodigoPostal' => $request->CodigoPostal,
                    'ColoniaLocalidad' => $request->ColoniaLocalidad,
                    'Aclaraciones' => $request->Aclaraciones ?? null,
                    'EstadoProvincia' => $request->has('EstadoProvincia') ? $request->EstadoProvincia : null,
                    'Id_Pais' => $request->Id_Pais,
                    'Id_EntidadFederativa' => $request->Id_EntidadFederativa,
                    'Id_MunicipioAlcaldia' => $request->Id_MunicipioAlcaldia,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria |  REGIMEN MATRIMONIAL actualizado correctamente.';
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
