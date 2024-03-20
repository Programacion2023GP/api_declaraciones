<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerDatosCurriculares extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {

            $datosInsercion = [
                'Id_SituacionPatrimonial' => $request->Id_SituacionPatrimonial,
                'Id_Nivel' => $request->Id_Nivel,
                'NombreInstitucionEducativa' => $request->NombreInstitucionEducativa,
                'Id_UbicacionInstitucionEducativa' => $request->Id_UbicacionInstitucionEducativa,
                'CarreraAreaConocimiento' => $request->CarreraAreaConocimiento,
                'Id_Estatus' => $request->Id_Estatus,
                'Id_DocumentoObtenido' => $request->Id_DocumentoObtenido,
                'FechaObtencion' => $request->FechaObtencion,
                'Aclaraciones' => $request->Aclaraciones ?? null,
                // 'FechaRegistro' => $request->FechaRegistro,
                'EsActivo' => 1,

            ];
            $DatosCurriculares = DB::table('DECL_DatosCurriculares')->insert($datosInsercion);




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Datos generales guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
