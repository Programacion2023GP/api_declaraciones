<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Carbon\Carbon;

class ControllerReportes extends Controller
{
    public function trasparencia(Response $response, $ejercicio = null, $trimestre = 1)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Obtener el año actual si $ejercicio es null
            if (is_null($ejercicio)) {
                $ejercicio = date('Y');
            }

            $nivel = DB::table('VistaTrimestres')
                ->where('Ejercicio', $ejercicio)
                ->where('Trimestre', $trimestre)
                ->get();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | Lista de relaciones con declarante obtenidas.';
            $response->data["alert_text"] = 'Relaciones con declarante obtenidas';
            $response->data["result"] = $nivel;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function incumplimientos(Response $response, $plazo_id, $fecha_referencia = '2020-07-01')
    {
        $response->data = ObjResponse::DefaultResponse();
        try {

            $DIAS_HABILES_INICIO_CONCLUSION = 60;
            $DIAS_HABILES_MODIFICACION = 30;
            $DIAS_HABILES = $DIAS_HABILES_INICIO_CONCLUSION;

            $fecha_referencia = Carbon::parse($fecha_referencia)->format('Y-m-d');

            $query = "
            SELECT uc.codigoEmpleado,uc.Curp,uc.nombreE,uc.apellidoP,uc.apellidoM,uc.puesto, mdp.DenominacionCargo, mdp.DenominacionPuesto,mdp.AreaAdscripcion,uc.fechaAlta,sp.FechaRegistro,sp.FechaTerminada
            FROM USR_Compaq uc
            INNER JOIN DECL_DatosGenerales dg ON uc.Curp = dg.Curp
            INNER JOIN DECL_SituacionPatrimonial sp ON dg.Id_SituacionPatrimonial = sp.Id_SituacionPatrimonial 
            INNER JOIN DECL_Plazo p ON sp.Id_Plazo = p.Id_Plazo
            INNER JOIN MD_Person mdp ON uc.codigoEmpleado = mdp.Nomina
            WHERE sp.EstaCompleta = 1 AND sp.EsActivo = 1
            AND uc.fechaAlta >= '2020-07-01'
            ";

            if ($plazo_id === 'undefined') {
                $query .= "AND dg.Id_SituacionPatrimonial = (
                SELECT MAX(Id_SituacionPatrimonial)
                FROM DECL_DatosGenerales
                WHERE Curp = uc.Curp
            );";
            } else {
                $query .= "AND sp.Id_Plazo = {$plazo_id} ";
                $query .= "AND dg.Id_SituacionPatrimonial = (
                SELECT MAX(scdg.Id_SituacionPatrimonial)
                FROM DECL_DatosGenerales scdg
                INNER JOIN DECL_SituacionPatrimonial scsp ON scdg.Id_SituacionPatrimonial = scsp.Id_SituacionPatrimonial
                WHERE scsp.Id_Plazo = {$plazo_id} AND Curp = uc.Curp
            )";

                if ($plazo_id == 2 || $plazo_id == 3) {
                    if ($plazo_id == 2) {
                        $DIAS_HABILES = $DIAS_HABILES_MODIFICACION;
                    }

                    $query .= "AND sp.FechaTerminada >= '{$fecha_referencia}T00:00:00'";
                }
            }

            $resultados = DB::select($query);

            $listaIncumplimiento = [];
            foreach ($resultados as $registro) {
                $incumplimiento = (array) $registro;
                $incumplimiento['fechaAlta'] = $this->formatFecha($registro->fechaAlta);
                $incumplimiento['FechaRegistro'] = $this->formatFecha($registro->FechaRegistro);
                $incumplimiento['FechaTerminada'] = $this->formatFecha($registro->FechaTerminada);

                $incumplimiento['Incumplimiento'] = false;
                $incumplimiento['Notificacion'] = 'Todo OK.';
                $incumplimiento['Declaracion'] =
                    $plazo_id == 1 ? 'Inicial' : ($plazo_id == 2 ? 'Modificación' : "Conclusión ");
                $incumplimiento['DiasTranscurridos'] = $this->obtenerDiasTranscurridos($registro->FechaTerminada, $fecha_referencia);

                if ($plazo_id == 1) {
                    if ($incumplimiento['DiasTranscurridos'] > $DIAS_HABILES) {
                        $incumplimiento['Notificacion'] = " La declaración NO se inició dentro del período hábil de {$DIAS_HABILES} días después de su fecha de ingreso.";
                        $incumplimiento['Incumplimiento'] = true;
                    } else {
                        $incumplimiento['DiasTranscurridos'] = $this->obtenerDiasTranscurridos($registro->FechaTerminada, $registro->fechaAlta);
                        $incumplimiento['Notificacion'] = "La declaración se inició a tiempo, pero no se concluyó dentro del período hábil de {$DIAS_HABILES} días.";
                        $incumplimiento['Incumplimiento'] = true;
                    }
                } elseif ($plazo_id > 1) {
                    if ($incumplimiento['DiasTranscurridos'] > $DIAS_HABILES) {
                        $incumplimiento['Notificacion'] = "La declaración NO se concluyó dentro del período hábil de {$DIAS_HABILES} días después de su fecha de terminada.";
                        $incumplimiento['Incumplimiento'] = true;
                    }
                }

                if ($incumplimiento['Notificacion'] !== 'Todo OK.') {
                    $listaIncumplimiento[] = $incumplimiento;
                }
            }
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | Lista de relaciones con declarante obtenidas.';
            $response->data["alert_text"] = 'Relaciones con declarante obtenidas';
            $response->data["result"] = $listaIncumplimiento;
            // return response()->json([
            //     'STATUS' => [
            //         'Result' => 'SUCCESS',
            //         'Message' => 'Se encontraron correctamente los registros',
            //     ],
            //     'RESPONSE' => $listaIncumplimiento,
            // ]);
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }

    private function formatFecha($fecha)
    {
        return Carbon::parse($fecha)->format('d-m-Y H:i:s');
    }

    private function obtenerDiasTranscurridos($fecha, $fechaReferencia)
    {
        $fecha = Carbon::parse($fecha);
        $fechaReferencia = Carbon::parse($fechaReferencia);

        if ($fecha->greaterThan($fechaReferencia)) {
            $diasTranscurridos = $fecha->diffInDays($fechaReferencia);
        } else {
            $diasTranscurridos = $fechaReferencia->diffInDays($fecha);
        }

        return $diasTranscurridos;
    }
}
