<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;
use App\Http\Controllers\ControllerApartados;

class ControllerBienesInmuebles extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['identificador']);
                unset($datos['tercero']);

                // Insertar los datos en la tabla 'DECL_BienesInmuebles'
                DB::table('DECL_BienesInmuebles')->insert($datos);
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron los BIENES INMUEBLES.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->all()[0]['Id_SituacionPatrimonial'], 10);
            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('BienesInmuebles', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, int $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('DECL_BienesInmuebles') // Selecciona la tabla DECL_Datosgenerales
                ->where('Id_SituacionPatrimonial', $id) // Agrega una condición where para filtrar por Id_SituacionPatrimonial
                ->select('*') // Selecciona todas las columnas
                ->get();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de tipo de adeudos.';
            $response->data["alert_text"] = "lista de inversion";
            $response->data["result"] = $data;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function update(Response $response, Request $request, $id)

    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe
            // return $id;

            DB::table('DECL_BienesInmuebles')
                ->where('Id_SituacionPatrimonial', $id)
                ->delete();


            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['id']);

                unset($datos['identificador']);
                // Insertar los datos en la tabla 'DECL_BienesInmuebles'
                DB::table('DECL_BienesInmuebles')->insert($datos);
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | BIENES INMUEBLES actualizados correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('BienesInmuebles', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede actualizar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
