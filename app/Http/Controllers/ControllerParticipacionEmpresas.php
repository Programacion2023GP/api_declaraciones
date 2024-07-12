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
                unset($datos['id']);
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
            $erros = new ControllerErrors();
            $erros->handleException('DECL_Participacion', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
