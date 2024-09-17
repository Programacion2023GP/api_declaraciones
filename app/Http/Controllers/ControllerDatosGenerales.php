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
            $SituacionPatrimonialId = DB::table('DECL_Situacionpatrimonial')->insertGetId([
                'Id_User' => $request->Id_User,
                'ID_Plazo' => ($request->Id_Plazo >= 1 && $request->Id_Plazo <= 3) ?
                    $request->Id_Plazo : ($request->Id_Plazo - 3),
                'FechaInicioInforma' => now()->format('Y-m-d'),
                'FechaFinInforma' => now()->format('Y-m-d'),
                'FechaRegistro' => now()->format('Y-m-d H:i:s'),
                'EstaCompleta' => 0,
                'EsActivo' => 1,
                'EsSimplificada' => ($request->Id_Plazo >= 1 && $request->Id_Plazo <= 3) ? 0 : 1,
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
            $datosGenerales = DB::table('DECL_Datosgenerales')->insert($datosInsercion);




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Datos GENERALES del declarante guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $SituacionPatrimonialId;
            $apartado = new ControllerApartados();
            $apartado->create($SituacionPatrimonialId, 1);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosGenerales', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }


    public function index(Response $response, Request $request, $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {

            if (!$id) {
                // $masiveIds = explode(',', $request->masiveIds);
                // $masiveIds = implode(',', $request->masiveIds);
                // $masiveIds = implode(',', $request->masiveIds);
                $chuckMasiveIds = array_chunk($request->masiveIds,1000);
                foreach ($chuckMasiveIds as $masiveId) {
                    $masiveIds = implode(',', $masiveId);
                    $data = DB::select("SELECT * FROM DECL_Datosgenerales WHERE Id_SituacionPatrimonial IN ($masiveIds)");
                   $response->data["result"] = array_merge($response->data["result"], $data);
                }

            } else {
                // Si solo se proporciona un ID, conviértelo a entero y úsalo
                $data = DB::table('DECL_Datosgenerales');
                $id = (int)$id;
                $data = $data->where('Id_SituacionPatrimonial', $id);
                $data = $data->select('*')->get();
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | lista de tipo de adeudos.';
            $response->data["alert_text"] = "Lista de inversión";
            $response->data["result"] = $data;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }


    public function acuse(Response $response, int $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('DECL_Datosgenerales')
                ->select(
                    'DECL_Datosgenerales.*',
                    'dbo.Cat_NombresEntes.valor',
                    'DECL_DatosEmpleoCargoComision.EmpleoCargoComision',
                    'DECL_DatosEmpleoCargoComision.AreaAdscripcion'
                )
                ->join('DECL_DatosEmpleoCargoComision', 'DECL_DatosEmpleoCargoComision.Id_SituacionPatrimonial', '=', 'DECL_Datosgenerales.Id_SituacionPatrimonial')
                // Realiza el JOIN solo si NombreEntePublico es un número
                ->leftJoin('dbo.Cat_NombresEntes', function ($join) {
                    $join->on(DB::raw('TRY_CONVERT(int, DECL_DatosEmpleoCargoComision.NombreEntePublico)'), '=', 'dbo.Cat_NombresEntes.clave');
                })
                ->where('DECL_Datosgenerales.Id_SituacionPatrimonial', $id)
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
            DB::table('DECL_Datosgenerales')
                ->where('Id_DatosGenerales', $id)
                ->update([
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
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | DATOS GENERALES actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del  TIPO DE ADEUDOS actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosGenerales', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
