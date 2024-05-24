<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class ControllerUsers extends Controller
{

    public function login(Request $request, Response $response)
    {
        // Obtener las credenciales del usuario desde la solicitud
        $Email = $request->Email;
        $Password = $request->Password;

        // Buscar el usuario en la base de datos por su correo electrónico
        $user = DB::table('USR_User')
            ->join('USR_UserRole', 'USR_User.Id_User', '=', 'USR_UserRole.Id_User')
            ->select('USR_User.*', 'USR_UserRole.Id_Role')
            ->where('USR_User.Email', $Email)
            ->where('USR_User.Active', 1)
            ->first();

        // Verificar si se encontró el usuario y si la contraseña coincide
        if ($user && Hash::check($Password, $user->Password)) {
            $userObject = [
                'Id_User' => $user->Id_User,
                'Id_Person' => $user->Id_Person,
                'Id_Role' => $user->Id_Role,
            ];
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Usuario logeado.';
            $response->data["result"]["user"] = $userObject;
            return response()->json($response, $response->data["status_code"]);
        } else {
            $response->data = ObjResponse::CatchResponse('Credenciales incorrectas');

            return response()->json($response, $response->data["status_code"]);
        }
    }


    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $user = DB::table('USR_User')->insertGetId([
                'Email' => $request->Email,
                'password' => Hash::make($request->password),
            ]);

            // Recuperar todos los datos del usuario recién creado
            $user = DB::table('USR_User')->where('id', $user)->first();

            $person = DB::table('MD_Person')->insertGetId([
                'Id_Person' => $user->Id_Person,
                'Name' => $request->Name,
                'PaternalSurname' => $request->PaternalSurname,
                'MaternalSurname' => $request->MaternalSurname,
                'Id_TipoIntegrante' => $request->Id_TipoIntegrante,
                'ClaseNivelPuesto' => $request->ClaseNivelPuesto,
                'DenominacionPuesto' => $request->DenominacionPuesto,
                'DenominacionCargo' => $request->DenominacionCargo,
                'AreaAdscripcion' => $request->AreaAdscripcion,
                'Nomina' => $request->Nomina,
            ]);
            $role = DB::table('USR_UserRole')->insertGetId([
                'Id_User' => $user->Id_User,
                'Id_Role' => $request->Id_Role,
            ]);


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Se insertaron los prestamos comodatos.';
            $response->data["alert_text"] = "regimenes encontrados";
            $apartado = new ControllerApartados();
            $apartado->create($request->all()[0]['Id_SituacionPatrimonial'], 15);
            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('Usuarios', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $usuarios = DB::table('MD_Person')
                ->select('MD_Person.Id_Person','Nomina','PaternalSurname','MaternalSurname','USR_Role.Name','DenominacionPuesto')
                ->join('USR_User', 'USR_User.Id_Person', '=', 'MD_Person.Id_Person')
                ->join('USR_UserRole', 'USR_User.Id_User', '=', 'USR_UserRole.Id_User')
                ->join('USR_Role', 'USR_UserRole.Id_Role', '=', 'USR_Role.Id_Role')

                ->where('MD_Person.active', 1)
                ->orderBy('MD_Person.Id_Person', 'desc') // Ordenar por ID en orden descendente (mayor a menor)
                ->get();



            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de adscripcion.';
            $response->data["alert_text"] = "usuarios adscripcion";
            $response->data["result"] = $usuarios;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
