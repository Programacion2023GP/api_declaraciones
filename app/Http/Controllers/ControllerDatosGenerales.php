<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;
use App\Http\Controllers\ControllerApartados;

class ControllerDatosGenerales extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $SituacionPatrimonialId = DB::table('DECL_SituacionPatrimonial')->insertGetId([
                'Id_User' => $request->Id_User,
                'ID_Plazo' => ($request->Id_Plazo >= 1 && $request->Id_Plazo <= 3) ?
                    $request->Id_Plazo : ($request->Id_Plazo - 3),
                'FechaInicioInforma' => now()->format('Y-m-d'),
                'FechaFinInforma' => now()->format('Y-m-d'),
                'FechaRegistro' => now()->format('Y-m-d H:i:s'),
                'EstaCompleta' => 0,
                'EsActivo' => 1,
                'EsSimplificada' => ($request->Id_Plazo >= 1 && $request->Id_Plazo <= 3) ? 1 : 0,
                'SeEnvioAcuse' => 0,
            ]);
            $datosInsercion = [
                'Id_SituacionPatrimonial' => $SituacionPatrimonialId,
                'Nombre' => $request->Nombre,
                'PrimerApellido' => $request->PrimerApellido,
                'SegundoApellido' => $request->SegundoApellido ?? null,
                'CorreoPersonal' => $request->CorreoPersonal ?? null,
                'Curp' => $request->Curp,
                'Rfc' => $request->Rfc,
                'Homoclave' => $request->Homoclave,
                'CorreoInstitucional' => $request->CorreoInstitucional,
                'TelefonoCasa' => $request->TelefonoCasa ?? null,
                'TelefonoCelularPersonal' => $request->TelefonoCelularPersonal ?? null,
                'Id_EstadoCivil' => $request->Id_EstadoCivil,
                'Id_RegimenMatrimonial' => $request->Id_RegimenMatrimonial ?? null,
                'Id_PaisNacimiento' => $request->Id_PaisNacimiento,
                'Id_Nacionalidad' => $request->Id_Nacionalidad,
                'Aclaraciones' => $request->Aclaraciones,
                'FueServidorPublicoAnioAnterior' => $request->FueServidorPublicoAnioAnterior ?? null,
                'FechaRegistro' => now()->format('Y-m-d H:i:s'),
                'EsActivo' => 1,
            ];
            $datosGenerales = DB::table('DECL_DatosGenerales')->insert($datosInsercion);




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Datos GENERALES del declarante guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $SituacionPatrimonialId;
            $apartado = new ControllerApartados();
            $apartado->create($SituacionPatrimonialId,1);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosGenerales', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
