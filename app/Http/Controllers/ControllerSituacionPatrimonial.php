<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerSituacionPatrimonial extends Controller
{
    public function index(Response $response, int $id, int $hoja, int $situacion = 0)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table($hoja < 15 ? 'DECL_Situacionpatrimonial' : 'DECL_Intereses')
                ->where('Id_User', $id)->where('EsActivo', 1)
                ->orderBy($hoja < 15 ? 'Id_SituacionPatrimonial' : 'Id_Intereses', 'desc');

            if ($situacion > 0) {
                $data->skip(1);
            }
            $data = $data->first();

            // Verificar si el registro existe en la tabla apartados
            $existsInApartadosQuery = DB::table($hoja < 15 ? 'DECL_SPApartados' : 'DECL_IApartados')
                ->where($hoja < 15 ? 'Id_SituacionPatrimonial' : 'Id_Intereses', $situacion)
                ->where($hoja < 15 ? 'Id_SituacionPatrimonialApartado' : 'Id_interesesApartado', $hoja);

            // Obtener la consulta SQL generada y los valores vinculados
            $sql = $existsInApartadosQuery->toSql();
            $bindings = $existsInApartadosQuery->getBindings();

            // Interpolar los valores vinculados en la consulta SQL
            $fullSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

            // Imprimir la consulta completa
            error_log('Full SQL: ' . $fullSql);

            // Ejecutar la consulta y obtener el resultado
            $existsInApartados = $existsInApartadosQuery->exists();
            error_log('Record exists in apartados: ' . ($existsInApartados ? 'true' : 'false'));



            // Asignar el valor apropiado a result
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de tipo de adeudos.';
            $response->data["alert_text"] = "lista de inversion";
            $response->data["result"] = $existsInApartados ? 0 : $data;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }


        return response()->json($response, $response->data["status_code"]);
    }
    public function delete(Response $response, Request $request, $id)

    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe
            // return $id;

            DB::table('DECL_Situacionpatrimonial')
                ->where('Id_SituacionPatrimonial', $id)
                ->update(['EsActivo' => 0]);





            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | Declaración eliminada correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            // $erros->handleException('BienesMuebles', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede actualizar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function user(Response $response, int $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('DECL_Situacionpatrimonial')
                // ->join('USR_UserRole', 'USR_UserRole.Id_User', '=', 'Notas_Aclaratorias.Id_User')
                ->select(
                    'DECL_Situacionpatrimonial.Id_SituacionPatrimonial as text',

                    'DECL_Situacionpatrimonial.Id_SituacionPatrimonial as id',

                )->where('Id_User', $id)->where('EstaCompleta', 1)
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
}
