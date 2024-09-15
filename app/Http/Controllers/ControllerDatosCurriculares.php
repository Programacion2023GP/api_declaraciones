<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;

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
            $response->data["message"] = 'peticion satisfactoria | DATOS CURRICULARES guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $DatosCurriculares;
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial, 3);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosCurriculares', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, Request $request, $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {

            $data = DB::table('DECL_DatosCurriculares');
            if (!$id) {
                $data = $data->whereIn('Id_SituacionPatrimonial', $request->masiveIds);
                # code...
            } else {
                $data = $data->where('Id_SituacionPatrimonial', $id);
            }
            $data = $data->select('*') // Selecciona todas las columnas
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


            // Actualizar el registro
            DB::table('DECL_DatosCurriculares')
                ->where('Id_DatosCurriculares', $id)
                ->update([
                    'Id_SituacionPatrimonial' => $request->Id_SituacionPatrimonial,
                    'Id_Nivel' => $request->Id_Nivel,
                    'NombreInstitucionEducativa' => $request->NombreInstitucionEducativa,
                    'Id_UbicacionInstitucionEducativa' => $request->Id_UbicacionInstitucionEducativa,
                    'CarreraAreaConocimiento' => $request->CarreraAreaConocimiento,
                    'Id_Estatus' => $request->Id_Estatus,
                    'Id_DocumentoObtenido' => $request->Id_DocumentoObtenido,
                    'FechaObtencion' => $request->FechaObtencion,
                    'Aclaraciones' => $request->Aclaraciones ?? null,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria |  DATOS CURRICULARES actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosCurriculares', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
