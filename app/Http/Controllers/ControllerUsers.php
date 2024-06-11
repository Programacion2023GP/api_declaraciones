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
            ->join('MD_Person', 'USR_User.Id_Person', '=', 'MD_Person.Id_Person')

            ->select('USR_User.*', 'MD_Person.*', 'USR_UserRole.Id_Role')
            ->where('USR_User.Email', $Email)
            ->where('USR_User.Active', 1)
            ->first();

        // Verificar si se encontró el usuario y si la contraseña coincide
        if ($user && Hash::check($Password, $user->Password)) {
            $userObject = [
                'Id_User' => $user->Id_User,
                'Id_Person' => $user->Id_Person,
                'Id_Role' => $user->Id_Role,
                'Name' => $user->Name,
                'PaternalSurname' => $user->PaternalSurname,

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
            $maxIdUser = DB::table('USR_User')->max('Id_User');
            $existingUser = DB::table('USR_User')->where('Email', $request->Email)->first();

            if ($existingUser) {
                // Si el correo electrónico ya existe, retornar un error
                $response->data = ObjResponse::CatchResponse("El correo electrónico ya está en uso");
                return response()->json($response, $response->data["status_code"]);
            }
            $person = DB::table('MD_Person')->insertGetId([
                // 'Id_Person' => $maxPerson + 1,
                'Name' => $request->Name,
                'PaternalSurname' => $request->PaternalSurname,
                'MaternalSurname' => $request->MaternalSurname,
                'Id_TipoIntegrante' => $request->Id_TipoIntegrante,
                'ClaseNivelPuesto' => $request->ClaseNivelPuesto,
                'DenominacionPuesto' => $request->DenominacionPuesto,
                'DenominacionCargo' => $request->DenominacionCargo,
                'AreaAdscripcion' => $request->AreaAdscripcion,
                'Nomina' => $request->Nomina,
            ], 'Id_Person');
            $user = DB::table('USR_User')->insertGetId([
                'Email' => $request->Email,
                'password' => Hash::make($request->password),
                'Id_Person' => $person,

            ], 'Id_User');

            // Recuperar todos los datos del usuario recién creado
            // $user = DB::table('USR_User')->where('id', $user)->first();

            $role = DB::table('USR_UserRole')->insertGetId([
                'Id_User' => $user,
                'Id_Role' => $request->Id_Role,
            ]);


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | USUARIO guardado correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";

            // $response->data["result"] = $DatosCurriculares;
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('Usuarios', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function update(Response $response, Request $request, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe

            $existingUser = DB::table('USR_User')->where('Email', $request->Email)->first();

            if ($existingUser) {
                // Si el correo electrónico ya existe, retornar un error
                $response->data = ObjResponse::CatchResponse("El correo electrónico ya está en uso");
                return response()->json($response, $response->data["status_code"]);
            }
            // Actualizar el registro
            DB::table('USR_User')
                ->where('Id_User', $id)
                ->update([
                    'Email' => $request->Email,
                ]);

            // Obtiene el Id_Person del registro actualizado
            $idPerson = DB::table('USR_User')
                ->where('Id_User', $id)
                ->value('Id_Person');

            DB::table('MD_Person')
                ->where('Id_Person', $idPerson)
                ->update([
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
            DB::table('USR_UserRole')
                ->where('Id_User', $id)
                ->update([
                    'Id_Role' => $request->Id_Role,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | USUARIO actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del  USUARIO actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_tipoinmueble', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }

    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $usuarios = DB::table('MD_Person')
                ->select(
                    'USR_User.Id_User',
                    'Nomina',
                    'MD_Person.Name',
                    'PaternalSurname',
                    'MaternalSurname',
                    'USR_Role.Name as Rol',
                    'DenominacionPuesto',
                    'USR_User.Email',
                    'MD_Person.DenominacionCargo',
                    'USR_Role.Id_Role',
                    'MD_Person.Id_TipoIntegrante',
                    'MD_Person.ClaseNivelPuesto',
                    'MD_Person.AreaAdscripcion'

                )
                ->join('USR_User', 'USR_User.Id_Person', '=', 'MD_Person.Id_Person')
                ->join('USR_UserRole', 'USR_User.Id_User', '=', 'USR_UserRole.Id_User')
                ->join('USR_Role', 'USR_UserRole.Id_Role', '=', 'USR_Role.Id_Role')

                ->where('USR_User.Active', 1)
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
    public function delete(Response $response, Request $request, $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe


            // Actualizar el registro
            DB::table('USR_User')
                ->where('Id_User', $id)
                ->update([
                    'Active' => 0,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | USUARIO eliminado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del  USUARIO actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_tipoinmueble', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
