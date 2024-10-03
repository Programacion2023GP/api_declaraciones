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
    public function interes(int $interes, int $hoja, int $borrar = 0, int $idUser = 0, int $crear = 1)
    {
        $response = new \stdClass();

        $response->data = ObjResponse::DefaultResponse();

        $InteresId = 0;
        if ($idUser > 0 && $hoja == 1 && $crear == 1) {
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

                    DB::table('DECL_Representaciones')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 5:

                    DB::table('DECL_ClientesPrincipales')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 6:

                    DB::table('DECL_BeneficiosPrivados')
                        ->where('Id_Intereses', $interes)
                        ->delete();
                    break;
                case 7:

                    DB::table('DECL_Fideicomisos')
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
            select max(DECL_Situacionpatrimonial.Id_SituacionPatrimonial) as Folio,
            max(DECL_SPApartados.Id_SituacionPatrimonialApartado) as Hoja,
            MD_Person.Name as Nombre,MD_Person.PaternalSurname as ApPaterno,MD_Person.MaternalSurname as ApMaterno,
            CASE
                 WHEN  DECL_Situacionpatrimonial.EsSimplificada = 0 THEN 'Completa'
                 WHEN  DECL_Situacionpatrimonial.EsSimplificada = 1 THEN 'Simplificada'
                END AS Declaracion,
             CASE
                 WHEN  DECL_Situacionpatrimonial.EstaCompleta =1 THEN 'Terminada'

                 ELSE 'En proceso'
             END AS Status,
        
            FORMAT(DECL_Situacionpatrimonial.FechaRegistro, 'dd/MM/yyyy') AS FechaRegistroFormateada,
          CASE
                 WHEN DECL_Situacionpatrimonial.Id_Plazo = 1 AND (DECL_Situacionpatrimonial.EsSimplificada = 0 OR DECL_Situacionpatrimonial.EsSimplificada = 1) THEN 'Inicial'
                 WHEN DECL_Situacionpatrimonial.Id_Plazo = 2 AND (DECL_Situacionpatrimonial.EsSimplificada = 0 OR DECL_Situacionpatrimonial.EsSimplificada = 1) THEN 'Modificación'
                 WHEN DECL_Situacionpatrimonial.Id_Plazo = 3 AND (DECL_Situacionpatrimonial.EsSimplificada = 0 OR DECL_Situacionpatrimonial.EsSimplificada = 1) THEN 'Conclusión'
             END AS Tipo_declaracion
            from DECL_SPApartados
            INNER JOIN DECL_Situacionpatrimonial ON DECL_Situacionpatrimonial.Id_SituacionPatrimonial = DECL_SPApartados.Id_SituacionPatrimonial
            INNER JOIN USR_User on USR_User.Id_User = DECL_Situacionpatrimonial.Id_User
            INNER JOIN MD_Person ON MD_Person.Id_Person = USR_User.Id_Person


            WHERE DECL_Situacionpatrimonial.Id_User =? and  DECL_Situacionpatrimonial.EsActivo =1  
			      group by DECL_SPApartados.Id_SituacionPatrimonial,MD_Person.Name,MD_Person.PaternalSurname,MD_Person.MaternalSurname,
            DECL_Situacionpatrimonial.Id_Plazo,DECL_Situacionpatrimonial.EsSimplificada,DECL_Situacionpatrimonial.FechaRegistro,DECL_Situacionpatrimonial.EstaCompleta

            UNION ALL



            select max (DECL_Intereses.Id_Intereses) as Folio, max (DECL_IApartados.Id_interesesApartado) as Hoja,
                        MD_Person.Name as Nombre,MD_Person.PaternalSurname as ApPaterno,MD_Person.MaternalSurname as ApMaterno, 'Interes' as Declaracion,
                        CASE
     WHEN  max(DECL_IApartados.Id_interesesApartado) =7 THEN 'Terminada'

     ELSE 'En proceso'
 END AS Status,
             FORMAT(DECL_Intereses.FechaInicioInforma, 'dd/MM/yyyy') AS FechaRegistroFormateada
             , 'Intereses' as Tipo_declaracion
            from DECL_Intereses 
            inner join DECL_IApartados on DECL_IApartados.Id_Intereses =DECL_Intereses.Id_Intereses
            INNER JOIN USR_User on USR_User.Id_User = DECL_Intereses.Id_User
                        INNER JOIN MD_Person ON MD_Person.Id_Person = USR_User.Id_Person
                                    WHERE DECL_Intereses.Id_User =? and  DECL_Intereses.EsActivo =1

            group by DECL_Intereses.Id_Intereses,MD_Person.Name,MD_Person.PaternalSurname,MD_Person.MaternalSurname,DECL_Intereses.FechaInicioInforma
                        ORDER BY folio DESC










            ", [$id, $id]);

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


            //     SELECT 
            //     MAX(DECL_Intereses.Id_Intereses) AS Folio,
            //     MD_Person.Name AS Nombre,
            //     MD_Person.PaternalSurname AS ApPaterno,
            //     MD_Person.MaternalSurname AS ApMaterno,
            //     'Intereses' AS Tipo_declaracion,
            //     'Interes' AS Declaracion,
            //     CASE
            //         WHEN MAX(DECL_IApartados.Id_interesesApartado) = 7 THEN 'Terminada'
            //         ELSE 'En proceso'
            //     END AS Tstatus,
            //     FORMAT(DECL_Intereses.FechaInicioInforma, 'dd/MM/yyyy') AS FechaRegistroFormateada
            // FROM DECL_Intereses
            // INNER JOIN DECL_IApartados ON DECL_IApartados.Id_Intereses = DECL_Intereses.Id_Intereses
            // INNER JOIN USR_User ON USR_User.Id_User = DECL_Intereses.Id_User
            // INNER JOIN MD_Person ON MD_Person.Id_Person = USR_User.Id_Person
            // WHERE DECL_Intereses.Id_User = 11321 AND DECL_Intereses.EsActivo = 1
            // GROUP BY 
            //     DECL_Intereses.Id_Intereses,
            //     MD_Person.Name,
            //     MD_Person.PaternalSurname,
            //     MD_Person.MaternalSurname,
            //     DECL_Intereses.FechaInicioInforma

            // UNION ALL
            $apartado = DB::select("
    
        
            SELECT 
            DSP.Id_SituacionPatrimonial AS Folio,
            MP.Gender,
            UC.fechaAlta as EmpleadoFechaAlta,
            MP.Name AS Nombre,
            MP.PaternalSurname AS ApPaterno,
            MP.MaternalSurname AS ApMaterno,
            CASE
                WHEN DSP.Id_Plazo = 1 THEN 'Inicial'
                WHEN DSP.Id_Plazo = 2 THEN 'Modificación'
                WHEN DSP.Id_Plazo = 3 THEN 'Conclusión'
            END AS Tipo_declaracion,
            CASE
                WHEN DSP.EsSimplificada = 1 THEN 'Simplificada'
                ELSE 'Completa'
            END AS Declaracion,
            CASE
                WHEN EXISTS (
                    SELECT 1 
                    FROM DECL_SPApartados DSA
                    WHERE DSA.Id_SituacionPatrimonial = DSP.Id_SituacionPatrimonial 
                      AND (
                          (DSP.EsSimplificada = 1 AND DSA.Id_SituacionPatrimonialApartado IN (1, 2, 3, 4, 5, 8)) OR
                          (DSP.EsSimplificada = 0 AND DSA.Id_SituacionPatrimonialApartado IN (1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15))
                      )
                ) THEN 'Terminada'
                ELSE 'En proceso'
            END AS Tstatus,
            FORMAT(DSP.FechaRegistro, 'dd/MM/yyyy') AS FechaRegistroFormateada,
             FORMAT(DSP.FechaTerminada, 'dd/MM/yyyy') AS FechaRegistroTerminada
        
        
        FROM DECL_Situacionpatrimonial DSP
        
        INNER JOIN USR_User UU ON UU.Id_User = DSP.Id_User
        
        INNER JOIN MD_Person MP ON MP.Id_Person = UU.Id_Person
        LEFT JOIN USR_Compaq UC ON UC.codigoEmpleado = MP.Nomina
        WHERE DSP.EsActivo = 1 and DSP.FechaTerminada is not null
        order by DSP.Id_SituacionPatrimonial desc;

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
            if ($id > 15) {
                $hoja = DB::select('SELECT MAX(DECL_SPApartados.Id_SituacionPatrimonialApartado) as Hoja FROM DECL_SPApartados
                WHERE DECL_SPApartados.Id_SituacionPatrimonial = ?', [$id]);
            } else {
                $hoja = DB::select('SELECT MAX(DECL_IApartados.Id_Intereses) as Hoja FROM DECL_Intereses
         WHERE DECL_Intereses.Id_Intereses = ?', [$id]);
            }
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | lista de AmbitoPublico.';
            $response->data["alert_text"] = "AmbitoPublico encontrados";
            $response->data["result"] = $hoja;
            error_log('Ocurrió un error: ' . $response);
        } catch (\Exception $ex) {
            error_log('Ocurrió un error: ' . $ex->getMessage());

            $response->data = ObjResponse::CatchResponse($ex);
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function exist(Response $response, $id, $hoja)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {

            $query = DB::table($hoja < 15 ? 'DECL_SPApartados' : 'DECL_IApartados')
                ->where($hoja < 15 ? 'Id_SituacionPatrimonial' : 'Id_Intereses', $id)
                ->where($hoja < 15 ? 'Id_SituacionPatrimonialApartado' : 'Id_interesesApartado', $hoja);

            // Imprime la consulta generada
            echo $query->toSql();

            // Imprime los parámetros utilizados en la consulta
            print_r($query->getBindings());

            // Ejecuta la consulta y obtiene el conteo
            $count = $query->count();

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
