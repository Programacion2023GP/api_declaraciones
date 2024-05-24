<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ControllerRoles extends Controller
{
    public function show(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $roles = DB::table('USR_Role')->select('Name as text', 'ID_Role as id')->get();

            // Convertir el ID a número
            $roles = $roles->map(function ($item) {
                $item->id = (int)$item->id;
                return $item;
            });

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de roles  obtenidas.';
            $response->data["alert_text"] = " roles con declarante obtenidas";
            $response->data["result"] = $roles;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
