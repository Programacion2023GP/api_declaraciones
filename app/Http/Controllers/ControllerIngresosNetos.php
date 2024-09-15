<?php

namespace App\Http\Controllers;


use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerIngresosNetos extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = $request->except('Id_DatosCurriculares');
            DB::table('DECL_Ingresos')->insert($data);


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se inserto correctamente los INGRESOS.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial, 8);
            DB::table('DECL_Situacionpatrimonial')
                ->where('Id_SituacionPatrimonial', $request->Id_SituacionPatrimonial)
                ->where('EsSimplificada', 1)
                ->update([
                    'EstaCompleta' => 1,
                    'FechaTerminada' => now()
                ]);



            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('IngresosNetos', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, Request $request, $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('DECL_Ingresos');
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

            DB::table('DECL_Ingresos')
                ->where('Id_Ingresos', $id)
                ->delete();

            DB::table('DECL_Ingresos')->insert($request->all());



            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | INGRESOS actualizados correctamente.';
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
