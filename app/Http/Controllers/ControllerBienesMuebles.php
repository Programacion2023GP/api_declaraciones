<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;

class ControllerBienesMuebles extends Controller
{

    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {

            // Eliminar el campo 'identificador' de los datos
            $datos = $request->except('identificador');

            DB::table('DECL_BienesMuebles')->insert($datos);





            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron los bienes muebles.';
            $response->data["alert_text"] = "regimenes encontrados";
            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('BienesMuebles', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
