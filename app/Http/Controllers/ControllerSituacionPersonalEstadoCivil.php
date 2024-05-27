<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerSituacionPersonalEstadoCivil extends Controller
{
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $estado_civil = DB::table('Estadocivil')
                ->select('val as text', 'id')
                ->where('active', 1)
                // ->orderBy('id', 'desc') // Ordenar por ID en orden descendente (mayor a menor)
                ->get();

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

    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $estado_civil = DB::table('Estadocivil')
                ->select('val as text', 'id')
                ->where('active', 1)
                ->orderBy('id', 'desc') // Ordenar por ID en orden descendente (mayor a menor)
                ->get();

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

    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $maximo_id = DB::table('Estadocivil')->max('id');

            $estado_civil = DB::table('Estadocivil')->insertGetId([
                'id' => $maximo_id + 1,
                'val' => $request->val,
            ]);



            // return "fff";
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | ESTADO CIVIL guardado correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $estado_civil;
        } catch (\Exception $ex) {
            // $response->data = ObjResponse::CatchResponse($ex->getMessage());
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_estadocivil', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function update(Response $response, Request $request, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe
            $estado_civil_existente = DB::table('Estadocivil')->find($id);


            // Actualizar el registro
            DB::table('Estadocivil')
                ->where('id', $id)
                ->update([
                    'val' => $request->val,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | ESTADO CIVIL actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del estado civil actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_estadocivil', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function delete(Response $response, Request $request, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe
            $estado_civil_existente = DB::table('Estadocivil')->find($id);


            // Actualizar el registro
            DB::table('Estadocivil')
                ->where('id', $id)
                ->update([
                    'active' => 0,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | ESTADO CIVIL eliminado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del estado civil actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_estadocivil', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
