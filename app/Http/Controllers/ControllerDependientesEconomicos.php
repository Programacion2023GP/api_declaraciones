<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerDependientesEconomicos extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            // // return  DB::table('DECL_DatosDependienteEconomico')
            DB::table('DECL_DatosDependienteEconomico')
            ->where('Id_SituacionPatrimonial',$request->Id_SituacionPatrimonial)
            ->delete();


            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['id']);
                // Insertar los datos en la tabla 'DECL_BienesInmuebles'
                DB::table('DECL_DatosDependienteEconomico')->insert($datos);
            }

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron los DEPENDIENTES ECONOMICOS.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->all()[0]['Id_SituacionPatrimonial'], 7);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DependientesEconomicos', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, Request $request, $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            if (!$id) {
                $masiveIds = implode(',', $request->masiveIds);
                $data = DB::select("SELECT * FROM DECL_DatosDependienteEconomico WHERE Id_SituacionPatrimonial IN ($masiveIds)");
                # code...
            } else {
                $data = DB::table('DECL_DatosDependienteEconomico');
                $data = $data->where('Id_SituacionPatrimonial', $id);
                $data = $data->select('*') // Selecciona todas las columnas
                ->get();
            }
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
            // return $id;

            DB::table('DECL_DatosDependienteEconomico')
                ->where('Id_SituacionPatrimonial', $id)
                ->delete();


            foreach ($request->all() as $datos) {
                // Eliminar el campo 'identificador' de los datos
                unset($datos['id']);
                // Insertar los datos en la tabla 'DECL_BienesInmuebles'
                DB::table('DECL_DatosDependienteEconomico')->insert($datos);
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | DEPENDIENTES ECONOMICOS actualizados correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DependientesEconomicos', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
