<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerMonedas extends Controller
{
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $estado_civil = DB::table('Moneda')->select('Divisa as text', 'Clave as id')->get();

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
    } //
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $regimen = DB::table('Moneda')->select('Divisa as text', 'Clave as id')->where('active', 1)->orderBy('Clave', 'desc')
                ->get();

            // Convertir el ID a número
            $regimen = $regimen->map(function ($item) {
                $item->id = (int)$item->id;
                return $item;
            });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $regimen;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $maximo_id = DB::table('Moneda')->max('clave');

            $regimen_matrimonial = DB::table('Moneda')->insertGetId([
                'Clave' => $maximo_id + 1,
                'Divisa' => $request->Divisa,
            ]);



            // return "fff";
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria |  MONEDA guardada correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $regimen_matrimonial;
        } catch (\Exception $ex) {
            // $response->data = ObjResponse::CatchResponse($ex->getMessage());
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_Moneda', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function update(Response $response, Request $request, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe


            // Actualizar el registro
            DB::table('Moneda')
                ->where('Clave', $id)
                ->update([
                    'Divisa' => $request->Divisa,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria |  MONEDA actualizada correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   MONEDA actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_Moneda', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function delete(Response $response, Request $request, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe


            // Actualizar el registro
            DB::table('Moneda')
                ->where('Clave', $id)
                ->update([
                    'active' => 0,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria |  MONEDA eliminada correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   MONEDA actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_Moneda', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
