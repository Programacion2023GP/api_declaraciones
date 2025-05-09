<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

use function Laravel\Prompts\select;

class ControllerInteresPdf extends Controller
{
    public function participacion(int $id, Response $response)
    {
        try {
            $response->data = ObjResponse::DefaultResponse();
            $results = DB::table('DECL_Participacion as dp')
                ->select([
                    'p.valor as relacion',
                    'dp.NombreEmpresaSociedadAsociacion',
                    'dp.RfcEmpresa',
                    'dp.PorcentajeParticipacion',
                    'tp.valor as participacion',
                    'dp.EsEnMexico',
                    DB::raw("CASE WHEN dp.RecibeRemuneracion = 0 THEN 'no' ELSE 'si' END as RecibeRemuneracion"),
                    'dp.MontoMensual',
                    'pa.Pais',
                    'mu.Municipio',  // Fixed typo from 'Municipio' to 'Municipio'
                    'm.Divisa as tipomoneda',
                    's.valor as sector',
                    'dp.Aclaraciones'
                ])
                ->leftJoin('TipoRelacion as p', 'p.clave', '=', 'dp.Id_TipoRelacion')
                ->leftJoin('TipoParticipacion as tp', 'tp.clave', '=', 'dp.Id_TipoParticipacion')
                ->leftJoin('Moneda as m', 'm.Clave', '=', 'dp.Id_MonedaMontoMensual')
                ->leftJoin('Municipio as mu', 'mu.Clave', '=', 'dp.Id_EntidadFederativa')
                ->leftJoin('Sector as s', 's.clave', '=', 'dp.Id_Sector')
                ->leftJoin('Pais as pa', 'pa.Clave', '=', 'dp.Id_PaisUbicacion')

                ->where('dp.Id_Intereses', $id)
                ->get(); // This returns a Collection
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $results;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function participacionTomaDecision(int $id, Response $response)
    {
        try {
            $response->data = ObjResponse::DefaultResponse();
            $results  = DB::select("
        select p.valor as 'relacion',ti.valor as 'institucion',pt.NombreInstitucion,pt.RfcInstitucion,pt.PuestoRol,pt.FechaInicioParticipacion,CASE WHEN pt.RecibeRemuneracion = 0 THEN 'no' ELSE 'si' END as RecibeRemuneracion,pt.MontoMensual,pt.EsEnMexico,pa.Pais,m.Municipio from DECL_ParticipacionTomaDecisiones as pt 
        left join TipoRelacion as p on p.clave = pt.Id_TipoRelacion
        left join TipoInstitucion as ti on ti.clave = pt.Id_TipoInstitucion
        left join Pais as pa on pa.clave = pt.Id_PaisUbicacion
        left join Municipio as m on m.Clave = pt.Id_EntidadFederativa
        where pt.Id_Intereses = ? 
        
        ", [$id]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $results;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function apoyos(int $id, Response $response)
    {
        try {
            $response->data = ObjResponse::DefaultResponse();
            $results  = DB::select("
        select b.valor as 'beneficiario',a.NombrePrograma,a.InstitucionOtorgante,n.valor as 'nivelordengobierno',ta.valor as 'tipoapoyo',fr.valor as 'formarecepcion',a.MontoApoyoMensual,a.Aclaraciones from DECL_Apoyos as a 
        inner join BeneficiariosPrograma as b on b.clave = a.Id_BeneficiarioPrograma
        inner join NivelOrdenGobierno as n on n.clave = a.Id_NivelOrdenGobierno
        inner join TipoApoyo as ta on ta.clave = a.Id_TipoApoyo
        inner join FormaRecepcion as fr on fr.clave = a.Id_FormaRecepcion
        where a.Id_Intereses = ? 
        
        ", [$id]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $results;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function representacion(int $id, Response $response)
    {
        try {
            $response->data = ObjResponse::DefaultResponse();
            $results  = DB::select("
        select tp.valor as 'relacion',tr.valor as 'representacion',  FORMAT(r.FechaInicioRepresentacion, 'dd/MM/yyyy') AS FechaInicioRepresentacion,
        r.NombreRazonSocial,r.Rfc,
          CASE WHEN r.RecibeRemuneracion = 0 THEN 'no' ELSE 'si' END as RecibeRemuneracion,
             r.MontoMensual,
              CASE WHEN r.EsEnMexico = 0 THEN 'no' ELSE 'si' END as EsEnMexico,
              p.Pais,
              m.Municipio,
              r.Aclaraciones,
              CASE
              WHEN r.Id_TipoPersona = 1 or r.Id_TipoPersona = 0 THEN 'FISICA'
              WHEN r.Id_TipoPersona = 2 THEN 'MORAL'
          END AS 'tipo_persona',
          s.valor as sector

         from DECL_Representaciones as r 
          left join TipoRelacion as tp on tp.clave = r.Id_TipoRelacion
          left join TipoRepresentacion as tr on tr.clave = r.Id_TipoRepresentacion
          left join Pais as p on p.Clave = r.Id_PaisUbicacion
          left join Municipio as m on m.Clave = r.Id_EntidadFederativa
          left join Sector as s on s.clave = r.Id_Sector
        where r.Id_Intereses = ? 
        
        ", [$id]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $results;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    public function clientes(int $id, Response $response)
    {
        try {
            $response->data = ObjResponse::DefaultResponse();
            $results  = DB::select("
        select tp.valor as 'relacion',
        CASE WHEN  cl.RealizaActividadLucrativa = 0 THEN 'no' ELSE 'si' END as RealizaActividadLucrativa,
        cl.NombreEmpresa,
        cl.RfcEmpresa,
           CASE
                  WHEN cl.Id_TipoPersona = 1 or cl.Id_TipoPersona = 0 THEN 'FISICA'
                  WHEN cl.Id_TipoPersona = 2 THEN 'MORAL'
              END AS 'cliente_principal',
              cl.NombreRazonSocial,
              cl.RfcCliente,
              s.valor as sector,
              cl.MontoAproximadoGanancia as 'MontoMensual',
              m.Divisa as 'moneda',
               CASE WHEN cl.EsEnMexico = 0 THEN 'no' ELSE 'si' END as EsEnMexico,
               p.Pais,
               mu.Municipio,
               cl.Aclaraciones
     from DECL_ClientesPrincipales as cl 
        left join TipoRelacion as tp on tp.clave = cl.Id_TipoRelacion
                  left join Sector as s on s.clave =cl.Id_Sector
                  left join Moneda as m on m.Clave = cl.Id_MontoAproximadoGanancia
        left join Pais as p on p.Clave = cl.Id_PaisUbicacion
              left join Municipio as mu on mu.Clave = cl.Id_EntidadFederativa
        where cl.Id_Intereses = ? 
        
        ", [$id]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $results;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function beneficiariosprivados(int $id, Response $response)
    {
        try {
            $response->data = ObjResponse::DefaultResponse();
            $results  = DB::select("
            select b.nombre as 'beneficiario',tb.nombre as 'tipobeneficio',
            CASE
                          WHEN bp.Id_TipoPersona = 1 or bp.Id_TipoPersona = 0 THEN 'FISICA'
                          WHEN bp.Id_TipoPersona = 2 THEN 'MORAL'
                      END AS 'otorgante',
          bp.NombreRazonSocial,
          bp.RfcCliente,
          fr.valor as 'formarecepcion',
          bp.EspecifiqueBeneficio,
          bp.MontoMensualAproximado,
          m.Divisa as moneda,
          s.valor as sector,
          bp.Aclaraciones
          from DECL_BeneficiosPrivados as bp 
          left join BeneficiariosPrograma as b on b.clave = bp.Id_BeneficiarioPrograma
          left join TipoBeneficio as tb on tb.clave = bp.Id_TipoBeneficio
          left join FormaRecepcion as fr on fr.clave = bp.Id_FormaRecepcion
          left join Moneda as m on m.Clave = bp.Id_MontoMensualAproximado
          left join Sector as s on s.clave =bp.Id_Sector
          where bp.Id_Intereses =?
        
        ", [$id]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $results;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function fideicomisos(int $id, Response $response)
    {
        try {
            $response->data = ObjResponse::DefaultResponse();
            $results  = DB::select("
         
   select tr.valor as 'relacion',tf.valor as 'fideocomiso',tp.valor as 'participacion',fc.RfcFideicomiso,
   CASE
                         WHEN fc.Id_TipoPersonaFideicomitente = 1 or fc.Id_TipoPersonaFideicomitente = 0 THEN 'FISICA'
                         WHEN fc.Id_TipoPersonaFideicomitente = 2 THEN 'MORAL'
                     END AS 'tipo_fideicomitente',
                     fc.NombreRazonSocialFideicomitente,
                     fc.RfcFideicomitente,
                     CASE
                         WHEN fc.Id_TipoPersonaFideicomisario = 1 or fc.Id_TipoPersonaFideicomisario = 0 THEN 'FISICA'
                         WHEN fc.Id_TipoPersonaFideicomisario = 2 THEN 'MORAL'
                     END AS 'tipo_fideicomisario',
                     fc.NombreRazonSocialFideicomisario,
                     fc.RfcFideicomisario,
                     s.valor as sector,
                       CASE
                         WHEN fc.EsEnMexico = 1  THEN 'México'
                         WHEN fc.EsEnMexico = 0 THEN 'Extranjero'
                     END AS 'Pais'
 from DECL_Fideicomisos as fc
 left join TipoRelacion as tr on tr.clave = fc.Id_TipoRelacion
 left join TipoFideicomiso as tf on tf.clave = fc.Id_TipoFideicomiso
 left join TipoParticipacion as tp on tp.clave = fc.Id_TipoParticipacion
 left join Sector as s  on s.clave = fc.Id_Sector

          where fc.Id_Intereses =?
        
        ", [$id]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de regimenes.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $results;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
}
