<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class ControllerJsons extends Controller
{


    public function descargarJsonZip()
    {
        $declaraciones = $this->obtenerDeclaraciones();

        $zipFileName = 'declaraciones_json.zip';
        $zipPath = storage_path("app/$zipFileName");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($declaraciones as $declaracion) {
                $json = $this->generarEstructuraJson($declaracion);
                $jsonData = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $jsonFileName = $this->generarNombreArchivo($declaracion);

                $zip->addFromString($jsonFileName, $jsonData);
            }
            $zip->close();
        } else {
            return response()->json(["error" => "No se pudo crear el archivo ZIP"], 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    protected function obtenerDeclaraciones()
    {
        return DB::select("
        WITH Hoja3 AS (
            SELECT  
                h3.Id_SituacionPatrimonial as 'h3_Id_SituacionPatrimonial',
                Nivel.valor as 'h3 valor Nivel', 
                Nivel.abreviado as 'h3 clave Nivel',
                h3.NombreInstitucionEducativa as 'h3 nombre institucion',
                IIF(h3.Id_UbicacionInstitucionEducativa = 1, 'MX', 'EX') AS 'h3 ubicacion institucion',
                h3.CarreraAreaConocimiento as 'h3 carreraAreaConocimiento',
                Estatus.valor as 'h3 estatus',
                DocumentoObtenido.valor as 'h3 documentoObtenido',
                CONVERT(VARCHAR(10), h3.FechaObtencion, 120) AS 'h3 fechaObtencion',
                h3.Aclaraciones as 'h3 aclaracionesObservaciones',
                ROW_NUMBER() OVER (PARTITION BY h3.Id_SituacionPatrimonial ORDER BY h3.FechaObtencion DESC) as rn_hoja3
            FROM DECL_DatosCurriculares as h3
            INNER JOIN Nivel on Nivel.clave = h3.Id_Nivel
            INNER JOIN Estatus on Estatus.clave = h3.Id_Estatus
            INNER JOIN DocumentoObtenido on DocumentoObtenido.clave = h3.Id_DocumentoObtenido 
        ),
        FilteredHoja3 AS (
            SELECT * FROM Hoja3 WHERE rn_hoja3 = 1
        ),
        DatosEmpleo AS (
            SELECT
                h4.Id_SituacionPatrimonial,
                h4NivelOrdenGobierno.valor as 'h4 nivelOrdenGobierno',
                h4AmbitoPublico.valor as 'h4 ambitoPublico',
                h4.NombreEntePublico as 'h4 nombreEntePublico',
                h4.AreaAdscripcion as 'h4 areaAdscripcion',
                h4.EmpleoCargoComision as 'h4 empleoCargoComision',
                h4.ContratadoPorHonorarios as 'h4 contratadoPorHonorarios',
                h4.nivelEmpleoCargoComision as 'h4 nivelEmpleoCargoComision',
                h4.FuncionPrincipal as 'h4 funcionPrincipal',
                CONVERT(VARCHAR(10), h4.FechaRegistro, 120) AS 'h4 fechaObtencion',
                h4.TelefonoOficina as 'h4 telefono telefonoOficina',
                h4.ExtensionTelefonoOficina as 'h4 extension telefonoOficina',
                h4.Calle as 'h4 calle',
                h4.NumeroExterior as 'h4 NumeroExterior',
                h4.NumeroInterior as 'h4 NumeroInterior',
                h4.ColoniaLocalidad as 'h4 coloniaLocalidad',
                h4.CiudadLocalidad as 'h4 ciudadLocalidad',
                h4.EstadoProvincia as 'h4 estadoProvincia',
                h4.CodigoPostal as 'h4 codigoPostal',
                h4Pais.Pais as 'h4 pais',
                h4.Aclaraciones as 'h4 aclaracionesObservaciones',
                ROW_NUMBER() OVER (PARTITION BY h4.Id_SituacionPatrimonial ORDER BY h4.FechaRegistro DESC) as rn_empleo
            FROM DECL_DatosEmpleoCargoComision as h4
            INNER JOIN NivelOrdenGobierno as h4NivelOrdenGobierno ON h4NivelOrdenGobierno.clave = h4.Id_NivelOrdenGobierno
            INNER JOIN AmbitoPublico as h4AmbitoPublico ON h4AmbitoPublico.clave = h4.Id_AmbitoPublico
            LEFT JOIN Pais as h4Pais ON h4Pais.Clave = h4.Id_Pais
        ),
        FilteredDatosEmpleo AS (
            SELECT * FROM DatosEmpleo WHERE rn_empleo = 1
        )
        
        SELECT 
            Declaracion.Id_SituacionPatrimonial,
            h1.Nombre as 'h1 nombre',
            h1.PrimerApellido as 'h1 primerApellido',
            h1.SegundoApellido as 'h1 segundoApellido',
            h1.Curp as 'h1 curp',
            h1.Rfc as 'h1 rfc',
            h1.Homoclave as 'h1 homoClave',
            h1.CorreoInstitucional as 'h1 institucional',
            h1.CorreoPersonal as 'h1 personal',
            h1.TelefonoCasa as 'h1 casa',
            h1.TelefonoCelularPersonal as 'h1 celularPersonal',
            Estadocivil.clave as h1_clave_Estadocivil,
            Estadocivil.val as h1_valor_Estadocivil,
            RegimenMatrimonial.nombre as 'h1 clave regimenMatrimonial',
            RegimenMatrimonial.nombre as 'h1 valor regimenMatrimonial',
            Pais.Code as 'h1 Pais',
            Nacionalidad.Code as 'h1 Nacionalidad',
            h1.Aclaraciones as 'h1 aclaracionesObservaciones',
            h2.Calle as 'h2 calle',
            h2.NumeroExterior as 'h2 numeroExterior',
            h2.NumeroInterior as 'h2 numeroInterior',
            h2.ColoniaLocalidad as 'h2 coloniaLocalidad',
            h2.CiudadLocalidad as 'h2 ciudadLocalidad',
            h2.EstadoProvincia as 'h2 estadoProvincia',
            h2.CodigoPostal as 'h2 CodigoPostal',
            h2Pais.Code as 'h2 Pais',
            h2.Aclaraciones as 'h2 aclaracionesObservaciones',
            FilteredHoja3.*,
            FilteredDatosEmpleo.*
        FROM DECL_SituacionPatrimonial as Declaracion
        INNER JOIN DECL_DatosGenerales as h1 ON h1.Id_SituacionPatrimonial = Declaracion.Id_SituacionPatrimonial
        INNER JOIN Estadocivil ON Estadocivil.id = h1.Id_EstadoCivil
        INNER JOIN RegimenMatrimonial ON RegimenMatrimonial.clave = h1.Id_RegimenMatrimonial
        INNER JOIN Pais ON Pais.Clave = h1.Id_PaisNacimiento
        INNER JOIN Pais as Nacionalidad ON Nacionalidad.Clave = h1.Id_Nacionalidad
        INNER JOIN DECL_DomicilioDeclarante as h2 ON h2.Id_SituacionPatrimonial = Declaracion.Id_SituacionPatrimonial
        LEFT JOIN Pais as h2Pais ON h2Pais.Clave = h2.Id_Pais
        INNER JOIN FilteredHoja3 ON FilteredHoja3.h3_Id_SituacionPatrimonial = Declaracion.Id_SituacionPatrimonial
        INNER JOIN FilteredDatosEmpleo ON FilteredDatosEmpleo.Id_SituacionPatrimonial = Declaracion.Id_SituacionPatrimonial
        WHERE Declaracion.EstaCompleta = 1
        ORDER BY Declaracion.Id_SituacionPatrimonial DESC;
        ");
    }

    protected function generarEstructuraJson($declaracion)
    {
        return [
            "metaData" => [],
            "declaracion" => [
                "situacionPatrimonial" => [
                    "datosGenerales" => $this->generarSeccionDatosGenerales($declaracion),
                    "domicilioDeclarante" => $this->generarSeccionDomicilio($declaracion),
                    "datosCurricularesDeclarante" => $this->generarSeccionDatosCurriculares($declaracion),
                    "datosEmpleoCargoComision" => $this->generarSeccionDatosEmpleoCargoComision($declaracion),
                ]
            ]
        ];
    }

    protected function generarSeccionDatosGenerales($declaracion)
    {
        return [
            "nombre" => $declaracion->{"h1 nombre"},
            "primerApellido" => $declaracion->{"h1 primerApellido"},
            "segundoApellido" => $declaracion->{"h1 segundoApellido"},
            "curp" => $declaracion->{"h1 curp"},
            "rfc" => [
                "rfc" => $declaracion->{"h1 rfc"},
                "homoClave" => $declaracion->{"h1 homoClave"},
            ],
            "correoElectronico" => [
                "institucional" => $declaracion->{"h1 institucional"},
                "personal" => $declaracion->{"h1 personal"},
            ],
            "telefono" => [
                "casa" => $declaracion->{"h1 casa"},
                "celularPersonal" => $declaracion->{"h1 celularPersonal"},
            ],
            "situacionPersonalEstadoCivil" => [
                "clave" => $declaracion->h1_clave_Estadocivil,
                "valor" => $declaracion->h1_valor_Estadocivil,
            ],
            "regimenMatrimonial" => [
                "clave" => $declaracion->{'h1 clave regimenMatrimonial'},
                "valor" => $declaracion->{'h1 valor regimenMatrimonial'},
            ],
            "paisNacimiento" => $declaracion->{'h1 Pais'},
            "nacionalidad" => $declaracion->{'h1 Nacionalidad'},
            "aclaracionesObservaciones" => $declaracion->{'h1 aclaracionesObservaciones'} ? $declaracion->{'h1 aclaracionesObservaciones'} : "",
        ];
    }

    protected function generarSeccionDomicilio($declaracion)
    {
        return [
            "domicilioMexico" => [
                "calle" => $declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 calle"},
                "numeroExterior" => $declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 numeroExterior"},
                "numeroInterior" => $declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 numeroInterior"},
                "coloniaLocalidad" => $declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 coloniaLocalidad"},
                "municipioAlcaldia" => $declaracion->{"h2 Pais"} ? "" :  [],
                "entidadFederativa" => $declaracion->{"h2 Pais"} ? "" :  [],
                "codigoPostal" => $declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 CodigoPostal"},
            ],
            "domicilioExtranjero" => [
                "calle" => $declaracion->{"h2 Pais"} ? $declaracion->{"h2 calle"} : "",
                "numeroExterior" => $declaracion->{"h2 Pais"} ? $declaracion->{"h2 numeroExterior"} : "",
                "numeroInterior" => $declaracion->{"h2 Pais"} ? $declaracion->{"h2 numeroInterior"} : "",
                "ciudadLocalidad" => $declaracion->{"h2 Pais"} ? $declaracion->{"h2 ciudadLocalidad"} : "",
                "estadoProvincia" => $declaracion->{"h2 Pais"} ? $declaracion->{"h2 estadoProvincia"} : "",
                "pais" => $declaracion->{"h2 Pais"} ? $declaracion->{"h2 Pais"} : "",
                "codigoPostal" => $declaracion->{"h2 Pais"} ? $declaracion->{"h2 CodigoPostal"} : "",
            ],
            "aclaracionesObservaciones" => $declaracion->{"h2 aclaracionesObservaciones"},
        ];
    }

    protected function generarSeccionDatosCurriculares($declaracion)
    {

        return [
            "escolaridad" => [
                "tipoOperacion" => "AGREGAR",
                "nivel" => [
                    "clave" => $declaracion->{'h3 clave Nivel'},
                    "valor" => $declaracion->{'h3 valor Nivel'},
                ],
                "institucionEducativa" => [
                    "nombre" => $declaracion->{'h3 nombre institucion'},
                    "ubicacion" => $declaracion->{'h3 ubicacion institucion'},

                ],
                "carreraAreaConocimiento" => $declaracion->{'h3 carreraAreaConocimiento'},
                "estatus" => $declaracion->{'h3 estatus'},
                "documentoObtenido" => $declaracion->{'h3 documentoObtenido'},
                "fechaObtencion" => $declaracion->{'h3 fechaObtencion'},
            ],
            "aclaracionesObservaciones" => $declaracion->{'h3 aclaracionesObservaciones'},
        ];
    }
    protected function generarSeccionDatosEmpleoCargoComision($declaracion)
    {
        return [
            "tipoOperacion" => "AGREGAR",
            "nivelOrdenGobierno" => $declaracion->{'h4 nivelOrdenGobierno'},
            "ambitoPublico" => $declaracion->{'h4 ambitoPublico'},
            "nombreEntePublico" => $declaracion->{'h4 nombreEntePublico'},
            "areaAdscripcion" => $declaracion->{'h4 areaAdscripcion'},
            "empleoCargoComision" => $declaracion->{'h4 empleoCargoComision'},
            "contratadoPorHonorarios" => $declaracion->{'h4 contratadoPorHonorarios'} ? true : false, //boleano
            "nivelEmpleoCargoComision" => $declaracion->{'h4 nivelEmpleoCargoComision'},
            "funcionPrincipal" => $declaracion->{'h4 funcionPrincipal'},
            "fechaTomaPosesion" => $declaracion->{'h4 fechaObtencion'},
            "telefonoOficina" => [
                "telefono" => $declaracion->{'h4 telefono telefonoOficina'},
                "extension" => $declaracion->{'h4 extension telefonoOficina'},

            ],
            "domicilioMexico" => [
                "calle" => $declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 calle'},
                "numeroExterior" => $declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 NumeroExterior'},
                "numeroInterior" => $declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 NumeroInterior'},
                "coloniaLocalidad" => $declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 coloniaLocalidad'},
                "municipioAlcaldia" => [
                    // "clave" => $declaracion->{'h4 estatus'},
                    // "valor" => $declaracion->{'h4 estatus'},
                ],
                "entidadFederativa" => [
                    // "clave" => $declaracion->{'h4 estatus'},
                    // "valor" => $declaracion->{'h4 estatus'},
                ],
                "codigoPostal" =>  $declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 codigoPostal'},
            ],
            "domicilioExtranjero" => [
                "calle" => $declaracion->{'h4 pais'} ?  $declaracion->{'h4 calle'} : "",
                "numeroExterior" => $declaracion->{'h4 pais'} ? $declaracion->{'h4 NumeroExterior'} : "",
                "numeroInterior" => $declaracion->{'h4 pais'} ? $declaracion->{'h4 NumeroInterior'} : "",
                "ciudadLocalidad" => $declaracion->{'h4 pais'} ? $declaracion->{'h4 ciudadLocalidad'} : "",
                "estadoProvincia" => $declaracion->{'h4 pais'} ? $declaracion->{'h4 estadoProvincia'} : "",
                "pais" => $declaracion->{'h4 pais'} ? $declaracion->{'h4 pais'} : "",
                "codigoPostal" => $declaracion->{'h4 pais'} ? $declaracion->{'h4 codigoPostal'} : "",

            ],
            "aclaracionesObservaciones" => $declaracion->{'h4 aclaracionesObservaciones'},

        ];
    }
    protected function generarSeccionExperienciaLaboral($declaracion)
    {
        $experienciasLaborales =  DB::select("");
        $experiencia =[

        ];
        foreach ($experienciasLaborales as $key => $value) {
            array_push($experiencia,[
                    "tipoOperacion"=>'AGREGAR',
                    // "ambitoSector"=>"",
                    "ambitoPublico"=>"",
                    "nombreEntePublico"=>"",
                    "areaAdscripcion"=>"",
                    "empleoCargoComision"=>"",
                    "funcionPrincipal"=>"",
                    "fechaIngreso"=>"",
                    "fechaEgreso"=>"",
                    "ubicacion"=>"",
                    "ubicacion"=>"",

            ]);
        }
        return [
            "ninguno" => $experienciasLaborales[0]->{'Mensaje'}?false:true,
            "experienciaLaboral"=>$experiencia
    ];
    }
    protected function generarNombreArchivo($declaracion)
    {
        return "declaracion_" . $declaracion->{"Id_SituacionPatrimonial"} . $declaracion->{"h1 nombre"} . ".json";
    }
}
