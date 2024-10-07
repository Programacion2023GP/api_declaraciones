<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;

class ControllerUsers extends Controller
{

    public function login(Request $request, Response $response)
    {
        // Obtener las credenciales del usuario desde la solicitud
        $Email = $request->Email;
        $Password = $request->Password;

        // Verificar si se ha subido un archivo de certificado
        if (!$request->hasFile('certificate')) {
            $response->data = ObjResponse::CatchResponse('El archivo del certificado es obligatorio.');
            return response()->json($response, $response->data["status_code"]);
        }

        // Obtener el archivo del certificado
        $uploadedCertificate = $request->file('certificate');

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
            // Obtener el ID del usuario
            $userId = $user->Id_User;

            // Construir la ruta del certificado almacenado
            $storedCertificatePath = storage_path("app/certificates/{$userId}/{$userId}.key");

            // Verificar si el archivo del certificado almacenado existe
            if (!file_exists($storedCertificatePath)) {
                $response->data = ObjResponse::CatchResponse('Certificado no encontrado en el servidor.');
                return response()->json($response, $response->data["status_code"]);
            }

            // Leer el contenido del certificado almacenado
            $storedCertificateContent = file_get_contents($storedCertificatePath);

            // Leer el contenido del certificado recibido
            $uploadedCertificateContent = file_get_contents($uploadedCertificate->getRealPath());

            // Comparar el contenido del certificado recibido con el certificado almacenado
            if ($storedCertificateContent === $uploadedCertificateContent) {
                // Si los certificados coinciden, proceder
                $userObject = [
                    'Id_User' => $user->Id_User,
                    'Id_Person' => $user->Id_Person,
                    'Id_Role' => $user->Id_Role,
                    'Name' => $user->Name,
                    'Sexo' => $user->Gender,
                    'PaternalSurname' => $user->PaternalSurname,
                    'MaternalSurname' => $user->MaternalSurname,
                ];
                $response->data = ObjResponse::CorrectResponse();
                $response->data["message"] = 'Usuario logeado.';
                $response->data["result"]["user"] = $userObject;
                return response()->json($response, $response->data["status_code"]);
            } else {
                // Si los certificados no coinciden
                $response->data = ObjResponse::CatchResponse('El certificado recibido no coincide con el certificado registrado.');
                return response()->json($response, $response->data["status_code"]);
            }
        } else {
            $response->data = ObjResponse::CatchResponse('Credenciales incorrectas');
            return response()->json($response, $response->data["status_code"]);
        }
    }


    public function updatePassword(Request $request, Response $response)
    {
        // Obtener las credenciales del usuario desde la solicitud
        $response->data = ObjResponse::DefaultResponse();
        try {
            $user = DB::table('USR_User')
                ->where('Id_User', $request->Id_User)
                ->first();

            if (!$user) {
                // Si el usuario no existe, devuelve un error
                $response->data = ObjResponse::CatchResponse('Usuario no encontrado');
                return response()->json($response, $response->data["status_code"]);
            }

            $newPassword = Hash::make($request->Password);

            $updated = DB::table('USR_User')
                ->where('Id_User', $request->Id_User)
                ->update(['Password' => $newPassword]);

            if ($updated) {
                $response->data = ObjResponse::CorrectResponse();
                $response->data["message"] = 'Contraseña actualizada correctamente.';
                return response()->json($response, $response->data["status_code"]);
            } else {
                $response->data = ObjResponse::CatchResponse('No se pudo actualizar la contraseña');
                return response()->json($response, $response->data["status_code"]);
            }
        } catch (\Exception $e) {
            $response->data = ObjResponse::CatchResponse('Ocurrió un error al actualizar la contraseña');
            $response->data["error"] = $e->getMessage();
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
            $existingUser = DB::table('MD_Person')->where('Nomina', $request->Nomina)->first();
            if ($existingUser && $request->Nomina != 999999) {
                // Si el correo electrónico ya existe, retornar un error
                $response->data = ObjResponse::CatchResponse("ya esta registrado el numero de nomina");
                return response()->json($response, $response->data["status_code"]);
            }
            $person = DB::table('MD_Person')->insertGetId([
                // 'Id_Person' => $maxPerson + 1,
                'Name' => $request->Name,
                'PaternalSurname' => $request->PaternalSurname,
                'MaternalSurname' => $request->MaternalSurname,
                'Gender' => $request->Gender,
                'organismo' => $request->organismo,

                'Id_TipoIntegrante' => $request->Id_TipoIntegrante,
                'ClaseNivelPuesto' => $request->ClaseNivelPuesto,
                'DenominacionPuesto' => $request->DenominacionPuesto,
                'DenominacionCargo' => $request->DenominacionCargo,
                'AreaAdscripcion' => $request->AreaAdscripcion,
                'Nomina' => $request->Nomina,
            ], 'Id_Person');
            $user = DB::table('USR_User')->insertGetId([
                'Email' => $request->Email,
                'password' => Hash::make("123456"),
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
    public function admin(Response $response)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {
            $maxIdUser = DB::table('USR_User')->max('Id_User');

            $person = DB::table('MD_Person')->insertGetId([
                // 'Id_Person' => $maxPerson + 1,
                'Name' => 'admin',
                'PaternalSurname' => 'sistemas',
                'MaternalSurname' => 'sistemas',
                'Gender' => 'Masculino',

                'Id_TipoIntegrante' => 1,
                'ClaseNivelPuesto' => 1,
                'DenominacionPuesto' => 1,
                'DenominacionCargo' => 1,
                'AreaAdscripcion' => 1,
                'Nomina' => 99999999,
            ], 'Id_Person');
            $user = DB::table('USR_User')->insertGetId([
                'Email' => 'admin@gomezpalacio.gob.mx',
                'password' => Hash::make("desarollo"),
                'Id_Person' => $person,

            ], 'Id_User');

            // Recuperar todos los datos del usuario recién creado
            // $user = DB::table('USR_User')->where('id', $user)->first();

            $role = DB::table('USR_UserRole')->insertGetId([
                'Id_User' => $user,
                'Id_Role' => 10,
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
                    'Gender' => $request->Gender,
                    'organismo' => $request->organismo,

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
    public function gender(Response $response, Request $request)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Verificar si el registro existe


            // if ($existingUser) {
            //     // Si el correo electrónico ya existe, retornar un error
            //     $response->data = ObjResponse::CatchResponse("El correo electrónico ya está en uso");
            //     return response()->json($response, $response->data["status_code"]);
            // }
            // Actualizar el registro
            $user = DB::table('MD_Person')
                ->where('Id_Person', $request->Id_Person)
                ->update([
                    'Gender' => $request->Gender,
                ]);



            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | USUARIO actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $user; // Puedes devolver el ID del  USUARIO actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('catalogo_tipoinmueble', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function updatePasswords()
    {
        // Define la nueva contraseña
        $newPassword = Hash::make('123456');

        // Actualiza el campo 'password' en todos los registros de la tabla 'USR_User'
        DB::table('USR_User')->update([
            'password' => $newPassword,
        ]);

        return response()->json(['message' => 'All user passwords have been updated.']);
    }
    public function index(Response $response, int $idPerson = 0)
    {
        // Inicialización de la respuesta por defecto
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Selección de los datos de los usuarios
            $usuarios = DB::table('MD_Person')
                ->select(
                    'USR_User.Id_User',
                    'Nomina',
                    'MD_Person.Name',
                    'MD_Person.Gender',
                    'PaternalSurname',
                    'MaternalSurname',
                    DB::raw("CONCAT(MD_Person.Name, ' ', PaternalSurname, ' ', MaternalSurname) as NombreCompleto"),
                    'USR_Role.Name as Rol',
                    'DenominacionPuesto',
                    'USR_User.Email',
                    'MD_Person.DenominacionCargo',
                    'USR_Role.Id_Role',
                    'MD_Person.Id_TipoIntegrante',
                    'MD_Person.ClaseNivelPuesto',
                    'MD_Person.AreaAdscripcion',
                    'MD_Person.organismo'
                )
                ->join('USR_User', 'USR_User.Id_Person', '=', 'MD_Person.Id_Person')
                ->join('USR_UserRole', 'USR_User.Id_User', '=', 'USR_UserRole.Id_User')
                ->join('USR_Role', 'USR_UserRole.Id_Role', '=', 'USR_Role.Id_Role');

            // Seleccionar la persona si el ID fue proporcionado
            $person = DB::table('MD_Person')
                ->join('USR_User', 'USR_User.Id_Person', '=', 'MD_Person.Id_Person')
                ->join('USR_UserRole', 'USR_User.Id_User', '=', 'USR_UserRole.Id_User')
                ->where('MD_Person.Id_Person', $idPerson)
                ->first();

            // Si la persona existe y el rol es 4, filtrar por adscripción
            if ($person && $person->Id_Role == 4) {
                $usuarios = $usuarios->where('MD_Person.AreaAdscripcion', $person->AreaAdscripcion)
                    ->where('MD_Person.Id_Person', '<>', $idPerson);
            }

            // Aplicar filtro de usuarios activos
            $usuarios = $usuarios->where('USR_User.Active', 1)
                ->orderBy('MD_Person.Id_Person', 'desc') // Orden descendente
                ->get();

            $usuarios = $usuarios->map(function ($usuario) {
                $userId = $usuario->Id_User;

                // Crear un nuevo objeto con la propiedad `storedCertificatePath` primero
                $usuarioNuevo = (object) array_merge(
                    ['storedCertificatePath' => asset("storage/certificates/{$userId}/{$userId}.key")],
                    (array) $usuario // Convertir el objeto original a un array
                );

                return $usuarioNuevo;
            });


            // Luego puedes continuar aplicando otras transformaciones si es necesario


            // Respuesta exitosa
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | Lista de adscripción.';
            $response->data["alert_text"] = "Usuarios adscripción";
            $response->data["result"] = $usuarios;
        } catch (\Exception $ex) {
            // Manejo de errores
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        // Devolver la respuesta en formato JSON
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

    public function pasupdate(Request $request, Response $response)
    {
        // Obtener las credenciales del usuario desde la solicitud
        $response->data = ObjResponse::DefaultResponse();

        try {
            // Buscar el usuario en la base de datos por su Id_User
            $user = DB::table('USR_User')
                ->where('Id_User', $request->id_User)
                ->first();

            // Verificar si se encontró el usuario y si la contraseña coincide
            if ($user && Hash::check($request->password, $user->Password)) {
                $user->Password = bcrypt($request->newPassword); // Asegúrate de hashear la nueva contraseña
                DB::table('USR_User')
                    ->where('Id_User', $request->id_User)
                    ->update(['Password' => $user->Password]); // Usar update en lugar de save para la tabla DB

                $response->data = ObjResponse::CorrectResponse();
                $response->data["message"] = 'Contraseña actualizada correctamente.';
                return response()->json($response, $response->data["status_code"]);
            } else {
                $response->data = ObjResponse::CatchResponse('Credenciales incorrectas');
                return response()->json($response, $response->data["status_code"]);
            }
        } catch (\Exception $e) {
            $response->data = ObjResponse::CatchResponse('Ocurrió un error al actualizar la contraseña');
            $response->data["error"] = $e->getMessage();
            return response()->json($response, $response->data["status_code"]);
        }
    }
    public function generateCert($userId = null)
    {
        // Si se pasa un userId
        if ($userId) {
            // Verificar si el usuario existe en la base de datos
            $user = DB::table('USR_User')->where('Id_User', $userId)->first();

            if ($user) {
                // Generar certificado solo para el usuario especificado
                $this->generateCertForUser($user->Id_User, $user->Email);
                return response()->json(['message' => "Certificado generado para el usuario con ID: {$userId}."]);
            } else {
                // Usuario no encontrado
                return response()->json(['error' => "Usuario con ID {$userId} no encontrado."], 404);
            }
        } else {
            // Si no se pasa un userId, generar certificados para todos los usuarios
            $users = DB::table('USR_User')->get();

            foreach ($users as $user) {
                $this->generateCertForUser($user->Id_User, $user->Email);
            }

            return response()->json(['message' => 'Certificados generados para todos los usuarios.']);
        }
    }

    public function generateCertForUser($userId, $email)
    {
        // Crear el directorio para almacenar el certificado del usuario
        $certDir = storage_path("app/certificates/{$userId}");

        if (!file_exists($certDir)) {
            mkdir($certDir, 0755, true);
        }

        try {
            // Generar clave privada
            $privateKey = RSA::createKey(2048);
            $keyPath = "{$certDir}/{$userId}.key";
            file_put_contents($keyPath, $privateKey->toString('PKCS8'));

            // Crear el certificado X509
            $x509 = new X509();
            $subject = new X509();

            // Configurar el Distinguished Name (DN)
            $subject->setDNProp('id-at-commonName', $email);

            // Configurar la fecha de expiración
            $x509->setEndDate('+1 year');

            // Configurar la clave pública
            $x509->setPublicKey($privateKey->getPublicKey());

            // Configurar un número de serie aleatorio
            $x509->setSerialNumber(random_int(1000, 9999));

            // Crear un certificado autofirmado (emisor y sujeto son iguales)
            $cert = $x509->sign($subject, $privateKey);

            // Guardar el certificado
            $certPath = "{$certDir}/{$userId}.cert";
            file_put_contents($certPath, $x509->saveX509($cert));

            // Retorno de éxito (opcional)
            return response()->json(['message' => "Certificado generado para el usuario con ID: {$userId}."]);
        } catch (\Exception $e) {
            // Manejo de excepciones
            return response()->json(['error' => "Error al generar el certificado: {$e->getMessage()}"], 500);
        }
    }
}
//pasupdate