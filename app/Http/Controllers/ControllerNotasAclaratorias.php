<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerNotasAclaratorias extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = $request->all();

            // Añadir la fecha y hora actuales a 'Created_at'
            $data['Created_at'] = date('Y-m-d H:i:s');
            $data['Active'] = 1;

            // Insertar el nuevo registro y obtener el ID recién creado
            $newId = DB::table('Notas_Aclaratorias')->insertGetId($data);

            // Construir el Folio
            $year = date('Y');
            $month = date('m');
            $folio = "NA-{$year}{$month}{$newId}";

            // Actualizar el registro con el Folio
            DB::table('Notas_Aclaratorias')
                ->where('Id_nota', $newId)
                ->update(['Folio' => $folio]);



            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se inserto correctamente la NOTA ACLARATORIA.';
            $response->data["alert_tex  t"] = "regimenes encontrados";

            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('Notas_Aclaratorias', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function show(Response $response, $id = 0)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('Notas_Aclaratorias')
                // ->join('USR_UserRole', 'USR_UserRole.Id_User', '=', 'Notas_Aclaratorias.Id_User')
                ->join('USR_User', 'USR_User.Id_User', '=', 'Notas_Aclaratorias.Id_User')
                ->join('MD_Person', 'MD_Person.Id_Person', '=', 'USR_User.Id_Person')
                ->select(
                   
                    'Notas_Aclaratorias.Id_SituacionPatrimonial',
                    'Notas_Aclaratorias.Id_nota',
                    'Notas_Aclaratorias.Folio',
                    'MD_Person.Name',
                    'MD_Person.PaternalSurname',
                    'MD_Person.MaternalSurname',
                    DB::raw("FORMAT(Notas_Aclaratorias.Date, 'dd/MM/yyyy') as Date"),
                    'Notas_Aclaratorias.Title',
                    'Notas_Aclaratorias.Description',
                    'USR_User.Email',
                    'MD_Person.AreaAdscripcion'
                );
            if ($id > 0) {
                $data = $data->where('Notas_Aclaratorias.Id_User', $id);
            }
            $data =  $data->where('Notas_Aclaratorias.Active', 1)->orderBy('Notas_Aclaratorias.Folio', 'desc')->get();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de tipo de adeudos.';
            $response->data["alert_text"] = "lista de inversion";
            $response->data["result"] = $data;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function delete(Response $response, Request $request, $id)

    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            // Verificar si el registro existe
            // return $id;

            DB::table('Notas_Aclaratorias')
                ->where('Id_nota', $id)
                ->update(['Active' => 0]);





            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | Declaración eliminada correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            // $erros->handleException('BienesMuebles', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede actualizar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
