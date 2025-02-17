<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerNombreEntePublico extends Controller
{
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $regimen = DB::table('dbo.Cat_NombresEntes')->select('valor as text', 'valor as id', 'clave as organismo')->get();

            // Convertir el ID a número
            // $regimen = $regimen->map(function ($item) {
            //     $item->id = (int)$item->id;
            //     return $item;
            // });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $regimen;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
