<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerReportes extends Controller
{
    public function trasparencia(Response $response, $ejercicio = null, $trimestre = 1)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Obtener el año actual si $ejercicio es null
            if (is_null($ejercicio)) {
                $ejercicio = date('Y');
            }

            $nivel = DB::table('VistaTrimestres')
                ->where('Ejercicio', $ejercicio)
                ->where('Trimestre', $trimestre)
                ->get();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | Lista de relaciones con declarante obtenidas.';
            $response->data["alert_text"] = 'Relaciones con declarante obtenidas';
            $response->data["result"] = $nivel;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
