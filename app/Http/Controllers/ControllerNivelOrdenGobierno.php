<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerNivelOrdenGobierno extends Controller
{
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $regimen = DB::table('NivelOrdenGobierno')->select('valor as text', 'clave as id')->where('active', 1)->get();

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
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $nivel = DB::table('NivelOrdenGobierno')->select('valor as text', 'clave as id')->where('active', 1)->orderBy('clave', 'desc')->get();

            // Convertir el ID a número
            $nivel = $nivel->map(function ($item) {
                $item->id = (int)$item->id;
                return $item;
            });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de adscripcion.';
            $response->data["alert_text"] = "usuarios adscripcion";
            $response->data["result"] = $nivel;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $maximo_id = DB::table('NivelOrdenGobierno')->max('clave');

            $regimen_matrimonial = DB::table('NivelOrdenGobierno')->insertGetId([
                'clave' => $maximo_id + 1,
                'valor' => $request->valor,
            ]);



            // return "fff";
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | NIVEL ORDEN DE GOBIERNO guardado correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $regimen_matrimonial;
        } catch (\Exception $ex) {
            // $response->data = ObjResponse::CatchResponse($ex->getMessage());
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_nivelordengobierno', $ex);
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
            DB::table('NivelOrdenGobierno')
                ->where('clave', $id)
                ->update([
                    'valor' => $request->valor,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | NIVEL ORDEN DE GOBIERNO actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del  NIVEL ORDEN DE GOBIERNO actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_nivelordengobierno', $ex);
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
            DB::table('NivelOrdenGobierno')
                ->where('clave', $id)
                ->update([
                    'active' => 0,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | NIVEL ORDEN DE GOBIERNO eliminado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del  NIVEL ORDEN DE GOBIERNO actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_nivelordengobierno', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
