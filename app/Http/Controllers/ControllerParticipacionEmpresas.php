<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;
use App\Http\Controllers\ControllerApartados;

class ControllerParticipacionEmpresas extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $firstRequestItem = $request->all()[0]; // Accede al primer elemento del array
            $InteresId = DB::table('DECL_Intereses')->insertGetId([
                'Id_User' => $firstRequestItem['Id_User'],
                'ID_Plazo' => 2,
                'FechaInicioInforma' => now()->format('Y-m-d'),
                'FechaFinInforma' => now()->format('Y-m-d'),
                'FechaRegistro' => now()->format('Y-m-d H:i:s'),
                'EstaCompleta' => 0,
                'EsActivo' => 1,
                // 'EsSimplificada' => ($request->Id_Plazo >= 1 && $request->Id_Plazo <= 3) ? 1 : 0,
                // 'SeEnvioAcuse' => 0,
            ]);


            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['identificador']);
                unset($datos['Id_User']);
                unset($datos['Id_SituacionPatrimonial']);

                // Agregar el 'InteresId' a los datos
                $datos['Id_Intereses'] = $InteresId;

                // Insertar los datos en la tabla 'TipoParticipacion'
                DB::table('DECL_Participacion')->insert($datos);
            }



            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron los PARTICIPACIÓN EN EMPRESAS, SOCIEDADES O ASOCIACIONES.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $InteresId;

            $apartado = new ControllerApartados();

            $apartado->interes($InteresId, 1);
            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            error_log($ex);
            $erros = new ControllerErrors();

            $erros->handleException('DECL_Participacion', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, int $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('DECL_Participacion') // Selecciona la tabla DECL_Datosgenerales
                ->where('Id_Intereses', $id) // Agrega una condición where para filtrar por Id_SituacionPatrimonial
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

            DB::table('DECL_Participacion')
                ->where('Id_Intereses', $id)
                ->delete();

            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['identificador']);
                unset($datos['id']);
                unset($datos['Id_User']);
                unset($datos['Id_SituacionPatrimonial']);
                // Eliminar el campo 'Id_Participacion' de los datos si existe
                unset($datos['Id_Participacion']);

                // Insertar los datos en la tabla 'DECL_Participacion'
                DB::table('DECL_Participacion')->insert($datos);
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | PARTICIPACIÓN EN EMPRESAS, SOCIEDADES O ASOCIACIONES actualizadas correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            error_log('Error' . $ex);
            $erros->handleException('DECL_Participacion', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede actualizar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
