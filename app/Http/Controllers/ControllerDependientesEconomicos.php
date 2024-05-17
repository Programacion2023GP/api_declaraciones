<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerDependientesEconomicos extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {

            $datos = $request->except('id');

            DB::table('DECL_DatosDependienteEconomico')->insert($datos);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron los dependientes economicos.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial, 7);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DependientesEconomicos', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
