<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerCompaq extends Controller
{
    public function show(Response $response, int $nomina)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            
            $usuario = DB::table('USR_Compaq')->select('*')->where("codigoEmpleado", $nomina)->get();

            // Convertir el ID a número
            // $usuario = $usuario->map(function ($item) {
            //     $item->id = (int)$item->id;
            //     return $item;
            // });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Usuario encontrado.';
            $response->data["alert_text"] = "usuarios adscripcion";
            $response->data["result"] = $usuario;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
