<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerTiposVehiculos extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $datos = $request->all();


            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['identificador']);
                // Insertar los datos en la tabla 'DECL_Vehiculos'
                DB::table('DECL_Vehiculos')->insert($datos);
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se inserto correctamente sus datos de sus VEHICULOS.';
            $response->data["alert_tex  t"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->all()[0]['Id_SituacionPatrimonial'], 11);
            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('Vehiculos', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, Request $request, $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            if (!$id) {
                $masiveIds = implode(',', $request->masiveIds);
                $data = DB::select("SELECT * FROM DECL_Vehiculos WHERE Id_SituacionPatrimonial IN ($masiveIds)");
                # code...
            } else {
                $data = DB::table('DECL_Vehiculos');
                $data = $data->where('Id_SituacionPatrimonial', $id);
                $data = $data->select('*') // Selecciona todas las columnas
                    ->get();
            }
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

            DB::table('DECL_Vehiculos')
                ->where('Id_SituacionPatrimonial', $id)
                ->delete();


            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['id']);

                unset($datos['identificador']);
                // Insertar los datos en la tabla 'DECL_BienesInmuebles'
                DB::table('DECL_Vehiculos')->insert($datos);
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | VEHICULOS actualizados correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DependientesEconomicos', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
