<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use App\Http\Controllers\ControllerApartados;

class ControllerDatosPareja extends Controller
{
    public function create(Response $response, Request $request)
    {

        $response->data = ObjResponse::DefaultResponse();

        try {

            $datosInsercion = [
                'Id_SituacionPatrimonial' => $request->Id_SituacionPatrimonial,
                'Nombre' => $request->Nombre ?? null,
                'PrimerApellido' => $request->PrimerApellido ?? null,
                'SegundoApellido' => $request->SegundoApellido ?? null,
                'FechaNacimiento' => $request->FechaNacimiento ?? null,
                'RfcPareja' => $request->RfcPareja ?? null,
                'Homoclave' => $request->Homoclave ?? null,
                'Curp' => $request->Curp ?? null,
                'Id_RelacionDeclarante' => $request->Id_RelacionDeclarante ?? null,
                'EsCiudadanoExtranjero' => $request->EsCiudadanoExtranjero ?? null,
                'EsDependienteEconomico' => $request->EsDependienteEconomico ?? null,
                'HabitaDomicilioDeclarante' => $request->HabitaDomicilioDeclarante ?? null,
                'NumeroExterior' => $request->NumeroExterior ?? null,
                'Calle' => $request->Calle ?? null,
                'CodigoPostal' => $request->CodigoPostal ?? null,
                'ColoniaLocalidad' => $request->ColoniaLocalidad ?? null,
                'Id_EntidadFederativa' => $request->Id_EntidadFederativa ?? null,
                'Id_MunicipioAlcaldia' => $request->Id_MunicipioAlcaldia ?? null,
                'Id_Pais' => $request->Id_Pais ?? null,
                'EstadoProvincia' => $request->EstadoProvincia ?? null,
                'NumeroExterior' => $request->NumeroExterior ?? null,
                'Id_ActividadLaboral' => $request->Id_ActividadLaboral ?? null,
                'NombreEmpresaSociedadAsociacion' => $request->NombreEmpresaSociedadAsociacion ?? null,
                'RfcEmpresa' => $request->RfcEmpresa ?? null,
                'EmpleoCargoComision' => $request->EmpleoCargoComision ?? null,
                'Id_Sector' => $request->Id_Sector ?? null,
                'FechaIngreso' => $request->FechaIngreso ?? null,
                'EsProveedorContratistaGobierno' => $request->EsProveedorContratistaGobierno ?? null,
                'Id_MonedaSalarioMensualNeto' => $request->Id_MonedaSalarioMensualNeto ?? null,
                'Id_NivelOrdenGobierno' => $request->Id_NivelOrdenGobierno ?? null,
                'Id_AmbitoPublico' => $request->Id_AmbitoPublico ?? null,
                'NombreEntePublico' => $request->NombreEntePublico ?? null,
                'ValorSalarioMensualNeto' => $request->ValorSalarioMensualNeto ?? null,
                'AreaAdscripcion' => $request->AreaAdscripcion ?? null,
                'FuncionPrincipal' => $request->FuncionPrincipal ?? null,
                'Aclaraciones' => $request->Aclaraciones ?? null,
            ];

            $datosPareja = DB::table('DECL_DatosPareja')->insert($datosInsercion);




            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | DATOS DE PAREJA guardados correctamente.';
            $response->data["alert_text"] = "regimenes encontrados";
            $response->data["result"] = $datosPareja;
            $apartado = new ControllerApartados();
            $apartado->create($request->Id_SituacionPatrimonial, 6);
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosPareja', $ex);
            $response->data = ObjResponse::CatchResponse("Ocurrio un error no se puede registrar");
        }

        return response()->json($response, $response->data["status_code"]);
    }
    public function index(Response $response, int $id)
    {
        $response->data = ObjResponse::DefaultResponse();

        try {
            $data = DB::table('DECL_DatosPareja') // Selecciona la tabla DECL_Datosgenerales
                ->where('Id_SituacionPatrimonial', $id) // Agrega una condición where para filtrar por Id_SituacionPatrimonial
                ->select('*') // Selecciona todas las columnas
                ->get();

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


            // Actualizar el registro
            DB::table('DECL_DatosPareja')
                ->where('Id_DatosPareja', $id)
                ->update([
                    'Nombre' => $request->Nombre ?? null,
                    'PrimerApellido' => $request->PrimerApellido ?? null,
                    'SegundoApellido' => $request->SegundoApellido ?? null,
                    'FechaNacimiento' => $request->FechaNacimiento ?? null,
                    'RfcPareja' => $request->RfcPareja ?? null,
                    'Homoclave' => $request->Homoclave ?? null,
                    'Curp' => $request->Curp ?? null,
                    'Id_RelacionDeclarante' => $request->Id_RelacionDeclarante ?? null,
                    'EsCiudadanoExtranjero' => $request->EsCiudadanoExtranjero ?? null,
                    'EsDependienteEconomico' => $request->EsDependienteEconomico ?? null,
                    'HabitaDomicilioDeclarante' => $request->HabitaDomicilioDeclarante ?? null,
                    'NumeroExterior' => $request->NumeroExterior ?? null,
                    'Calle' => $request->Calle ?? null,
                    'CodigoPostal' => $request->CodigoPostal ?? null,
                    'ColoniaLocalidad' => $request->ColoniaLocalidad ?? null,
                    'Id_EntidadFederativa' => $request->Id_EntidadFederativa ?? null,
                    'Id_MunicipioAlcaldia' => $request->Id_MunicipioAlcaldia ?? null,
                    'Id_Pais' => $request->Id_Pais ?? null,
                    'EstadoProvincia' => $request->EstadoProvincia ?? null,
                    'NumeroExterior' => $request->NumeroExterior ?? null,
                    'Id_ActividadLaboral' => $request->Id_ActividadLaboral ?? null,
                    'NombreEmpresaSociedadAsociacion' => $request->NombreEmpresaSociedadAsociacion ?? null,
                    'RfcEmpresa' => $request->RfcEmpresa ?? null,
                    'EmpleoCargoComision' => $request->EmpleoCargoComision ?? null,
                    'Id_Sector' => $request->Id_Sector ?? null,
                    'FechaIngreso' => $request->FechaIngreso ?? null,
                    'EsProveedorContratistaGobierno' => $request->EsProveedorContratistaGobierno ?? null,
                    'Id_MonedaSalarioMensualNeto' => $request->Id_MonedaSalarioMensualNeto ?? null,
                    'Id_NivelOrdenGobierno' => $request->Id_NivelOrdenGobierno ?? null,
                    'Id_AmbitoPublico' => $request->Id_AmbitoPublico ?? null,
                    'NombreEntePublico' => $request->NombreEntePublico ?? null,
                    'ValorSalarioMensualNeto' => $request->ValorSalarioMensualNeto ?? null,
                    'AreaAdscripcion' => $request->AreaAdscripcion ?? null,
                    'FuncionPrincipal' => $request->FuncionPrincipal ?? null,
                    'Aclaraciones' => $request->Aclaraciones ?? null,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Petición satisfactoria | DATOS DE PAREJA actualizado correctamente.';
            $response->data["alert_text"] = "Regímenes encontrados";
            $response->data["result"] = $id; // Puedes devolver el ID del   REGIMEN MATRIMONIAL actualizado si lo necesitas
        } catch (\Exception $ex) {
            $erros = new ControllerErrors();
            $erros->handleException('DatosPareja', $ex);
            $response->data = ObjResponse::CatchResponse($ex);
        }

        return response()->json($response, $response->data["status_code"]);
    }
}
