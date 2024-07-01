<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerMunicipios extends Controller
{
    public function show(Response $response, int $code = 0)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $estado_civil = DB::table('Municipio')->select('Municipio as text', 'Clave as id');
            if ($code > 0) {
                $estado_civil = $estado_civil->where("CodeEstado", $code);
            }
            $estado_civil = $estado_civil->get();

            // Convertir el ID a número
            $estado_civil = $estado_civil->map(function ($item) {
                $item->id = (int)$item->id;
                return $item;
            });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de adscripcion.';
            $response->data["alert_text"] = "usuarios adscripcion";
            $response->data["result"] = $estado_civil;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
