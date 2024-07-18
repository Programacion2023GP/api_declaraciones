<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
class ControllerTipoBeneficio extends Controller
{
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $integrante = DB::table('TipoBeneficio')
                ->select('valor as text', 'clave as id')
                // ->where('active', 1)
                // ->orderBy('id', 'desc') // Ordenar por ID en orden descendente (mayor a menor)
                ->get();

            // Convertir el ID a número
            $integrante = $integrante->map(function ($item) {
                $item->id = (int)$item->id;
                return $item;
            });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de adscripcion.';
            $response->data["alert_text"] = "usuarios adscripcion";
            $response->data["result"] = $integrante;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
