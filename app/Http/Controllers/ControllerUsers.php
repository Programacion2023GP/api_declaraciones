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
}
