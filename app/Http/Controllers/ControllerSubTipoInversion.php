<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
class ControllerSubTipoInversion extends Controller
{
    public function show(Response $response, int $code)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $estado_civil = DB::table('SubTipoInversion')->select('valor as text', 'clave as id')->where("tipoInversion", $code)->get();

            // Convertir el ID a número
            $estado_civil = $estado_civil->map(function ($item) {
                $item->id = (int)$item->id;
                return $item;
            });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de subtipos inversion.';
            $response->data["alert_text"] = "usuarios subtipos inversion";
            $response->data["result"] = $estado_civil;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
