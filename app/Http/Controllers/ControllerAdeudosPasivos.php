<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;

class ControllerAdeudosPasivos extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {

            // Eliminar el campo 'identificador' de los datos
            $datos = $request->except('indentificador');

            DB::table('DECL_AdeudosPasivos')->insert($datos);





            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron las inversiones de cuenta.';
            $response->data["alert_text"] = "regimenes encontrados";
            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('AdeudosPasivos', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
