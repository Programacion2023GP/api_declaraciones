<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;
use App\Http\Controllers\ControllerApartados;

class ControllerPrestamosComodatos extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {



            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['identificador']);
                // Insertar los datos en la tabla 'DECL_BienesInmuebles'
                DB::table('DECL_PrestamoComodato')->insert($datos);
            }

            DB::table('DECL_Situacionpatrimonial')
                ->where('Id_SituacionPatrimonial', $request->all()[0]['Id_SituacionPatrimonial'])
                ->update([
                    'EstaCompleta' => 1,
                    'SeEnvioAcuse' => 1,

                    'FechaTerminada' => now()
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron los PRESTAMOS COMODATOS.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();

            $apartado->create($request->all()[0]['Id_SituacionPatrimonial'], 15);


            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('PrestamosComadatos', $ex);
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
                $data = DB::select("SELECT * FROM DECL_PrestamoComodato WHERE Id_SituacionPatrimonial IN ($masiveIds)");
                # code...
            } else {
                $data = DB::table('DECL_PrestamoComodato');
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

            DB::table('DECL_PrestamoComodato')
                ->where('Id_SituacionPatrimonial', $id)
                ->delete();
            $apartado = new ControllerApartados();

            $apartado->create($request->all()[0]['Id_SituacionPatrimonial'], 15);

            DB::table('DECL_Situacionpatrimonial')
                ->where('Id_SituacionPatrimonial', $request->all()[0]['Id_SituacionPatrimonial'])
                ->where('EsSimplificada', 0)
                ->update([
                    'EstaCompleta' => 1,
                    'FechaTerminada' => now()
                ]);
            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['identificador']);
                // Insertar los datos en la tabla 'DECL_Vehiculos'
                DB::table('DECL_PrestamoComodato')->insert($datos);
            }

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | PRESTAMOS COMODATOS actualizados correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('AdeudosPasivos', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede actualizar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
