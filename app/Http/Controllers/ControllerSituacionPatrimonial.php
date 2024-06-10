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
            $data = DB::table('DECL_SituacionPatrimonial')
                ->where('Id_User', $id)
                ->orderBy('Id_SituacionPatrimonial', 'desc');

            if ($situacion > 0) {
                $data->skip(1);
            }

            $data = $data->first();

            // Verificar si el registro existe en la tabla apartados
            $existsInApartados = DB::table('DECL_SPApartados')
                ->where('Id_SituacionPatrimonial', $situacion)
                ->where("Id_SituacionPatrimonialApartado", $hoja)
                ->exists();

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

            DB::table('DECL_SituacionPatrimonial')
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
}
