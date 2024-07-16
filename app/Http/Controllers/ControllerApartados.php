<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;
use App\Models\VistaDeclaracionesModel;

class ControllerApartados extends Controller
{
    public function create(int $situacionPatrimonial, int $hoja, int $borrar = 0)
    {
        $response = new \stdClass();

        $response->data = ObjResponse::DefaultResponse();

        try {
            if ($borrar == 1) {
                switch ($hoja) {
                    case 5:

                        DB::table('DECL_ExperienciaLaboral')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 6:

                        DB::table('DECL_DatosPareja')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 7:

                        DB::table('DECL_DatosDependienteEconomico')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 9:

                        DB::table('DECL_ActividadAnualAnterior')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 10:

                        DB::table('DECL_BienesInmuebles')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 11:

                        DB::table('DECL_Vehiculos')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 12:

                        DB::table('DECL_BienesMuebles')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 13:

                        DB::table('DECL_InversionesCuentasValores')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 14:

                        DB::table('DECL_AdeudosPasivos')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;
                    case 15:

                        DB::table('DECL_PrestamoComodato')
                            ->where('Id_SituacionPatrimonial', $situacionPatrimonial)
                            ->delete();
                        break;

                    default:

                        break;
                }
            }

            $datos = [
                'Id_SituacionPatrimonial' => $situacionPatrimonial,
                'Id_SituacionPatrimonialApartado' => $hoja
            ];

            DB::table('DECL_SPApartados')->insert($datos);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Continuemos llenando la declaración.';
            $response->data["alert_text"] = "regimenes encontrados";
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('Apartados', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function interes(int $interes, int $hoja, int $borrar = 0, int $idUser = 0)
    {
        $response = new \stdClass();

        $response->data = ObjResponse::DefaultResponse();

        $InteresId = 0;
        if ($idUser > 0 && $hoja ==1) {
            $InteresId = DB::table('DECL_Intereses')->insertGetId([
                'Id_User' => $idUser,
                'ID_Plazo' => 2,
                'FechaInicioInforma' => now()->format('Y-m-d'),
                'FechaFinInforma' => now()->format('Y-m-d'),
                'FechaRegistro' => now()->format('Y-m-d H:i:s'),
                'EstaCompleta' => 0,
                'EsActivo' => 1,
                // 'EsSimplificada' => ($request->Id_Plazo >= 1 && $request->Id_Plazo <= 3) ? 1 : 0,
                // 'SeEnvioAcuse' => 0,
            ]);
        }
        $datos = [
            'Id_Intereses' => $InteresId > 0 ? $InteresId : $interes,
            'Id_interesesApartado' => $hoja
        ];
        if ($borrar == 1) {
            switch ($hoja) {
                case 1:

                    DB::table('DECL_Participacion')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 2:

                    DB::table('DECL_ParticipacionTomaDecisiones')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 3:

                    DB::table('DECL_Apoyos')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 4:

                    DB::table('DECL_ActividadAnualAnterior')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 5:

                    DB::table('DECL_BienesInmuebles')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 6:

                    DB::table('DECL_Vehiculos')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 7:

                    DB::table('DECL_BienesMuebles')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;


                default:

                    break;
            }
        }
        DB::table('DECL_IApartados')->insert($datos);

        $response->data = ObjResponse::CorrectResponse();
        $response->data["message"] = 'Continuemos llenando la declaración.';
        $response->data["alert_text"] = "regimenes encontrados";
        $response->data["result"] = $InteresId;
        return response()->json($response, $response->data["status_code"]);
    }

    public function show(Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $apartado = DB::select("
            select max(DECL_SituacionPatrimonial.Id_SituacionPatrimonial) as Folio,
            max(DECL_SPApartados.Id_SituacionPatrimonialApartado) as Hoja,
            MD_Person.Name as Nombre,MD_Person.PaternalSurname as ApPaterno,MD_Person.MaternalSurname as ApMaterno,
            CASE
                 WHEN  DECL_SituacionPatrimonial.EsSimplificada = 1 THEN 'Completa'
                 WHEN  DECL_SituacionPatrimonial.EsSimplificada = 0 THEN 'Simplificada'
                END AS Declaracion,
             CASE
                 WHEN  (max(DECL_SPApartados.Id_SituacionPatrimonialApartado) =15 and DECL_SituacionPatrimonial.EsSimplificada = 1 OR 
				 max(DECL_SPApartados.Id_SituacionPatrimonialApartado) =8 and DECL_SituacionPatrimonial.EsSimplificada = 0 ) THEN 'Terminada'
                 ELSE 'En proceso'
             END AS Status,
        
            FORMAT(DECL_SituacionPatrimonial.FechaRegistro, 'dd/MM/yyyy') AS FechaRegistroFormateada,
          CASE
                 WHEN DECL_SituacionPatrimonial.Id_Plazo = 1 AND (DECL_SituacionPatrimonial.EsSimplificada = 0 OR DECL_SituacionPatrimonial.EsSimplificada = 1) THEN 'Inicial'
                 WHEN DECL_SituacionPatrimonial.Id_Plazo = 2 AND (DECL_SituacionPatrimonial.EsSimplificada = 0 OR DECL_SituacionPatrimonial.EsSimplificada = 1) THEN 'Modificación'
                 WHEN DECL_SituacionPatrimonial.Id_Plazo = 3 AND (DECL_SituacionPatrimonial.EsSimplificada = 0 OR DECL_SituacionPatrimonial.EsSimplificada = 1) THEN 'Conclusión'
             END AS Tipo_declaracion
            from DECL_SPApartados
            INNER JOIN DECL_SituacionPatrimonial ON DECL_SituacionPatrimonial.Id_SituacionPatrimonial = DECL_SPApartados.Id_SituacionPatrimonial
            INNER JOIN USR_User on USR_User.Id_User = DECL_SituacionPatrimonial.Id_User
            INNER JOIN MD_Person ON MD_Person.Id_Person = USR_User.Id_Person
            WHERE DECL_SituacionPatrimonial.Id_User =? and  DECL_SituacionPatrimonial.EsActivo =1
            group by DECL_SPApartados.Id_SituacionPatrimonial,MD_Person.Name,MD_Person.PaternalSurname,MD_Person.MaternalSurname,
            DECL_SituacionPatrimonial.Id_Plazo,DECL_SituacionPatrimonial.EsSimplificada,DECL_SituacionPatrimonial.FechaRegistro
            ORDER BY folio DESC

            ", [$id]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | lista de AmbitoPublico.';
            $response->data["alert_text"] = "AmbitoPublico encontrados";
            $response->data["result"] = $apartado;
        } catch (\Exception $ex) {

            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function all(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $apartado = DB::select("
                        SELECT *
                        FROM Declaraciones
                        ORDER BY Folio DESC;
            ");

            // $apartado = VistaDeclaracionesModel::orderBy('Folio', 'DESC')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | lista de AmbitoPublico.';
            $response->data["alert_text"] = "AmbitoPublico encontrados";
            $response->data["result"] = $apartado;
        } catch (\Exception $ex) {

            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function Hoja(Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $hoja = DB::select('SELECT MAX(DECL_SPApartados.Id_SituacionPatrimonialApartado) as Hoja FROM DECL_SPApartados
         WHERE DECL_SPApartados.Id_SituacionPatrimonial = ?', [$id]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | lista de AmbitoPublico.';
            $response->data["alert_text"] = "AmbitoPublico encontrados";
            $response->data["result"] = $hoja;
        } catch (\Exception $ex) {

            $response->data = ObjResponse::CatchResponse($ex);
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function exist(Response $response, $id, $hoja)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $count = DB::table('DECL_SPApartados')
                ->where('Id_SituacionPatrimonial', $id)
                ->where('Id_SituacionPatrimonialApartado', $hoja)
                ->count();



            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | lista de AmbitoPublico.';
            $response->data["alert_text"] = "AmbitoPublico encontrados";
            $response->data["result"] = $count > 0 ? true : false;
        } catch (\Exception $ex) {

            $response->data = ObjResponse::CatchResponse($ex);
        }
        return response()->json($response, $response->data["status_code"]);
    }
}
