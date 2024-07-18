<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class ControllerRepresentaciones extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $firstRequestItem = $request->all()[0]; // Accede al primer elemento del array



            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['id']);
                // unset($datos['Id_User']);
                // unset($datos['Id_SituacionPatrimonial']);

                // Agregar el 'InteresId' a los datos
                $datos['Id_Intereses'] = $firstRequestItem['Id_Intereses'];

                // Insertar los datos en la tabla 'TipoParticipacion'
                DB::table('DECL_Representaciones')->insert($datos);
            }



            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron las REPRESENTACIONES';
            $response->data["alert_text"] = "regimenes encontrados";
            // $response->data["result"] = $InteresId;

            $apartado = new ControllerApartados();

            $apartado->interes($firstRequestItem['Id_Intereses'], 4);
            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DECL_Representaciones', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
