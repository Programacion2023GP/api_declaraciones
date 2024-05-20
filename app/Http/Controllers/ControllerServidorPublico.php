<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerServidorPublico extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            DB::table('DECL_ActividadAnualAnterior')->insert($request->all());


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se inserto correctamente sus datos de servidor publico.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial,9);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('ServidorPublico', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
