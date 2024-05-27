<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
class ControllerAdscripcion extends Controller
{
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $adscripcion = DB::table('Adscripcion')
                ->select('nombre as text', 'id')
                ->where('active', 1)
                // ->orderBy('id', 'desc') // Ordenar por ID en orden descendente (mayor a menor)
                ->get();

            // Convertir el ID a número
            $adscripcion = $adscripcion->map(function ($item) {
                $item->id = (int)$item->id;
                return $item;
            });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de adscripcion.';
            $response->data["alert_text"] = "usuarios adscripcion";
            $response->data["result"] = $adscripcion;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $regimen = DB::table('Adscripcion')->select('nombre as text', 'id as id')->where('active', 1)->orderBy('id', 'desc')
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

            $regimen_matrimonial = DB::table('Adscripcion')->insertGetId([
                'nombre' => $request->nombre,
            ]);



            // return "fff";
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria |  AEREA DE ADSCRIPCION guardado correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $regimen_matrimonial;
        } catch (\Exception $ex) {
            // $response->data = ObjResponse::CatchResponse($ex->getMessage());
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_adscripcion', $ex);
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
            DB::table('Adscripcion')
                ->where('id', $id)
                ->update([
                    'nombre' => $request->nombre,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria |  AEREA DE ADSCRIPCION actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   AEREA DE ADSCRIPCION actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_adscripcion', $ex);
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
            DB::table('Adscripcion')
                ->where('id', $id)
                ->update([
                    'active' => 0,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria |  AEREA DE ADSCRIPCION eliminado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   AEREA DE ADSCRIPCION actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_adscripcion', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
