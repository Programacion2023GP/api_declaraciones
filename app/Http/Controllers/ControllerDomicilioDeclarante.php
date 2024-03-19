<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

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




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Datos generales guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $datosDomicilio;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
