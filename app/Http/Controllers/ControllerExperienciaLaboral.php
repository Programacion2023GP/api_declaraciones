<?php

namespace App\Http\Controllers;


use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerExperienciaLaboral extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
        
            // $response->data["result"] = $DatosCurriculares;

            $datos = $request->except('identificador');

            DB::table('DECL_ExperienciaLaboral')->insert($datos);




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron las experiencias laborales.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial,5);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('ExperienciaLaboral', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
