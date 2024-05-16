<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerErrors;

class ControllerApartados extends Controller
{
    public function create(int $situacionPatrimonial, int $hoja)
    {
        $response = new \stdClass();

        $response->data = ObjResponse::DefaultResponse();

        try {
            $datos = [
                'Id_SituacionPatrimonial' => $situacionPatrimonial,
                'Id_SituacionPatrimonialApartado' => $hoja
            ];

            DB::table('DECL_SPApartados')->insert($datos);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se inserto la hoja correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('Apartados', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function show(Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $apartado = DB::select("
            select max(DECL_SituacionPatrimonial.Id_SituacionPatrimonial) as folio,
   MD_Person.Name as Nombre,MD_Person.PaternalSurname as ApPaterno,MD_Person.MaternalSurname as ApMaterno,
   DECL_SPApartados.Id_SituacionPatrimonial as Declaracion,
    CASE
        WHEN  max(DECL_SPApartados.Id_SituacionPatrimonialApartado) =15 THEN 'Terminada'
		ELSE 'En proceso'
    END AS Status,
   max(DECL_SPApartados.Id_SituacionPatrimonialApartado) as Hoja,
   DECL_SituacionPatrimonial.FechaRegistro,
 CASE
        WHEN DECL_SituacionPatrimonial.Id_Plazo = 1 AND (DECL_SituacionPatrimonial.EsSimplificada = 0 OR DECL_SituacionPatrimonial.EsSimplificada = 1) THEN 'Inicial'
        WHEN DECL_SituacionPatrimonial.Id_Plazo = 2 AND (DECL_SituacionPatrimonial.EsSimplificada = 0 OR DECL_SituacionPatrimonial.EsSimplificada = 1) THEN 'Modificación'
        WHEN DECL_SituacionPatrimonial.Id_Plazo = 3 AND (DECL_SituacionPatrimonial.EsSimplificada = 0 OR DECL_SituacionPatrimonial.EsSimplificada = 1) THEN 'Conclusión'
    END AS Tipo_declaracion
   from DECL_SPApartados
   INNER JOIN DECL_SituacionPatrimonial ON DECL_SituacionPatrimonial.Id_SituacionPatrimonial = DECL_SPApartados.Id_SituacionPatrimonial
   INNER JOIN USR_User on USR_User.Id_User = DECL_SituacionPatrimonial.Id_User
   INNER JOIN MD_Person ON MD_Person.Id_Person = USR_User.Id_Person
   WHERE DECL_SituacionPatrimonial.Id_User =?
   group by DECL_SPApartados.Id_SituacionPatrimonial,MD_Person.Name,MD_Person.PaternalSurname,MD_Person.MaternalSurname,
   DECL_SituacionPatrimonial.Id_Plazo,DECL_SituacionPatrimonial.EsSimplificada,DECL_SituacionPatrimonial.FechaRegistro
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
}
