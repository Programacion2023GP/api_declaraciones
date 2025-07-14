<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\select;

class ControllerJsons extends Controller
{


    public function descargarJsonZip()
    {
        // set_time_limit(12000); // 300 segundos = 5 minutos
        // ini_set('memory_limit', '1024M');
        // $declaraciones = $this->obtenerDeclaraciones();

        // $zipFileName = 'declaraciones_json.zip';
        // $zipPath = storage_path("app/$zipFileName");

        // $zip = new ZipArchive;
        // if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        //     foreach ($declaraciones as $declaracion) {
        //         $json = $this->generarEstructuraJson($declaracion);
        //         $jsonData = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        //         $jsonFileName = $this->generarNombreArchivo($declaracion);

        //         $zip->addFromString($jsonFileName, $jsonData);
        //     }
        //     $zip->close();
        // } else {
        //     return response()->json(["error" => "No se pudo crear el archivo ZIP"], 500);
        // }

        // return response()->download($zipPath)->deleteFileAfterSend(true);


        set_time_limit(12000);
        ini_set('memory_limit', '1024M');
        $declaraciones = $this->obtenerDeclaraciones();

        // Crear un array que contendrá todas las declaraciones
        $jsonCompleto = [];

        foreach ($declaraciones as $declaracion) {
            // Generar la estructura JSON para cada declaración y añadirla al array
            $jsonCompleto[] = $this->generarEstructuraJson($declaracion);
        }

        // Convertir el array completo a JSON
        $jsonData = json_encode($jsonCompleto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Nombre del archivo único
        $jsonFileName = 'declaraciones_completas.json';
        $jsonPath = storage_path("app/$jsonFileName");

        // Guardar el JSON en un archivo temporal
        file_put_contents($jsonPath, $jsonData);

        // Descargar el archivo y eliminarlo después
        return response()->download($jsonPath)->deleteFileAfterSend(true);
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
            
            CASE
                WHEN TRY_CAST(h4.NivelEmpleoCargoComision AS INT) = 1 THEN '1 Presidente Municipal, Regidores, Tesorero, Contralor, Oficial Mayor'
                WHEN TRY_CAST(h4.NivelEmpleoCargoComision AS INT) = 2 THEN '2 Encargados, Jefes, Supervisores, Administradores, Sub Directores, Directores, Juez, Coordinadores'
                WHEN TRY_CAST(h4.NivelEmpleoCargoComision AS INT) = 3 THEN '3 Operativos (secretaría, auxiliares, limpieza, administrativos, veladores, chofer, intendencia, fajineros, etc.)'
                WHEN h4.NivelEmpleoCargoComision IS NULL THEN NULL
                ELSE h4.NivelEmpleoCargoComision
            END as 'h4 nivelEmpleoCargoComision',
            
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
            m4Municio.clave_geologica as 'h4 clave municipioAlcaldia',
            m4Municio.Municipio as 'h4 valor municipioAlcaldia',
            RIGHT('0' + CAST(m4Estado.Clave AS VARCHAR(2)), 2) AS 'h4 clave entidadFederativa',
            m4Estado.Estado as 'h4 valor entidadFederativa',
            h4Pais.Pais as 'h4 pais',
            h4.Aclaraciones as 'h4 aclaracionesObservaciones',
            ROW_NUMBER() OVER (PARTITION BY h4.Id_SituacionPatrimonial ORDER BY h4.FechaRegistro DESC) as rn_empleo
        FROM DECL_DatosEmpleoCargoComision as h4
        INNER JOIN NivelOrdenGobierno as h4NivelOrdenGobierno ON h4NivelOrdenGobierno.clave = h4.Id_NivelOrdenGobierno
        INNER JOIN AmbitoPublico as h4AmbitoPublico ON h4AmbitoPublico.clave = h4.Id_AmbitoPublico
        LEFT JOIN Municipio as m4Municio on m4Municio.Clave = h4.Id_MunicipioAlcaldia
        LEFT JOIN Estado as m4Estado on m4Estado.Clave = h4.Id_EntidadFederativa
        LEFT JOIN Pais as h4Pais ON h4Pais.Clave = h4.Id_Pais
        ),
        FilteredDatosEmpleo AS (
            SELECT * FROM DatosEmpleo WHERE rn_empleo = 1
        )
        
        SELECT 
            Declaracion.Id_SituacionPatrimonial,
            Declaracion.Id_User,
			Declaracion.FechaRegistro,
			Declaracion.EsSimplificada,
			CASE
    WHEN Declaracion.Id_Plazo =1  THEN 'INICIAL'
    WHEN Declaracion.Id_Plazo =2 THEN 'MODIFICACIÓN'
    WHEN Declaracion.Id_Plazo =3 THEN 'CONCLUSIÓN'
END as tipo,
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
			m2Municio.clave_geologica as 'h2 clave municipioAlcaldia',
			m2Municio.Municipio as 'h2 valor municipioAlcaldia',
    RIGHT('0' + CAST(m2Estado.Clave AS VARCHAR(2)), 2) AS 'h2 clave entidadFederativa',
			m2Estado.Estado as 'h2 valor entidadFederativa',
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
		LEFT JOIN Municipio as m2Municio on m2Municio.Clave =h2.Id_MunicipioAlcaldia
		LEFT JOIN Estado as m2Estado on m2Estado.Clave =h2.Id_EntidadFederativa

        INNER JOIN FilteredHoja3 ON FilteredHoja3.h3_Id_SituacionPatrimonial = Declaracion.Id_SituacionPatrimonial
        INNER JOIN FilteredDatosEmpleo ON FilteredDatosEmpleo.Id_SituacionPatrimonial = Declaracion.Id_SituacionPatrimonial
        WHERE Declaracion.EstaCompleta = 1 
        AND YEAR(Declaracion.FechaRegistro) >= YEAR(GETDATE()) - 1
        ORDER BY Declaracion.Id_SituacionPatrimonial DESC;
        ");
    }

    protected function generarEstructuraJson($declaracion)
    {

        $situacionPatrimonial = [
            "datosGenerales" => $this->generarSeccionDatosGenerales($declaracion),
            // "domicilioDeclarante" => $this->generarSeccionDomicilio($declaracion),
            "datosCurricularesDeclarante" => $this->generarSeccionDatosCurriculares($declaracion),
            "datosEmpleoCargoComision" => $this->generarSeccionDatosEmpleoCargoComision($declaracion),
            "experienciaLaboral" => $this->generarSeccionExperienciaLaboral($declaracion->{'Id_SituacionPatrimonial'}),
            // "datosPareja" => $this->generarSeccionDatosPareja($declaracion->{'Id_SituacionPatrimonial'}),
            // "datosDependienteEconomico" => $this->generarSeccionDatosDependientesEconomicos($declaracion->{'Id_SituacionPatrimonial'}),
            "ingresos" => $this->generarSeccionIngresos($declaracion->{'Id_SituacionPatrimonial'}),
        ];

            $situacionPatrimonial["actividadAnualAnterior"] = $this->generarSeccionActividadAnualAnterior($declaracion->{'Id_SituacionPatrimonial'});
        
        $situacionPatrimonial["bienesInmuebles"] = $this->generarSeccionBienesInmuebles($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["vehiculos"] = $this->generarSeccionVehiculos($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["bienesMuebles"] = $this->generarSeccionBienesMuebles($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["inversionesCuentasValores"] = $this->generarSeccionInversiones($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["adeudos"] = $this->generarSeccionAdeudos($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["prestamoOComodato"] = $this->generarSeccionPrestamosComodatos($declaracion->{'Id_SituacionPatrimonial'});
        $interes = DB::selectOne("select * from DECL_Intereses where EstaCompleta = 1
        and Id_user = ? and   YEAR(FechaRegistro) = YEAR(?);
        
           ", [$declaracion->{'Id_User'}, $declaracion->{'FechaRegistro'}]);

        return [
            "metadata" => $this->generarMetaData($declaracion),
            "declaracion" => [
                "situacionPatrimonial" => $situacionPatrimonial
            ],
            "intereses" => [
                "participacion" => $interes ? $this->generarSeccionParticipacion($interes->{'Id_Intereses'}) : ["ninguno" => true],
                "participacionTomaDecisiones" => $interes ? $this->generarSeccionParticipacionTomaDecisiones($interes->{'Id_Intereses'}) : ["ninguno" => true],
                "apoyos" => $interes ? $this->generarSeccionApoyos($interes->{'Id_Intereses'}) : ["ninguno" => true],
                "representacion" => $interes ? $this->generarSeccionRepresentaciones($interes->{'Id_Intereses'}) : ["ninguno" => true],
                "clientesPrincipales" => $interes ? $this->generarSeccionClientesPrincipales($interes->{'Id_Intereses'}) : ["ninguno" => true],
                "beneficiosPrivados" => $interes ? $this->generarSeccionBeneficiosPrivados($interes->{'Id_Intereses'}) : ["ninguno" => true],
                "fideicomisos" => $interes ? $this->generarSeccionFideocomisos($interes->{'Id_Intereses'})  : ["ninguno" => true],

            ],
        ];
    }
    protected function generarMetaData($declaracion)
    {
        return [
            "actualizacion" => $declaracion->{'FechaRegistro'},
            "institucion" => 'R. AYUNTAMIENTO DE GOMEZ PALACIO',
            "tipo" => $declaracion->{'tipo'},
            "declaracionCompleta" => !$declaracion->{'EsSimplificada'},
            "actualizacionConflictoInteres" => false,
        ];
    }
    protected function  limitedString($texto, $limite)
    {
        if (strlen($texto) > $limite) {
            return substr($texto, 0, $limite);
        }
        return $texto;
    }
    /**
     * Evalúa condicionalmente si debe incluir un campo basado en el tipo de persona
     */
    protected function conditionalField($condition, $key, $value)
    {
        return $condition ? [$key => $value] : [];
    }
    protected function nullConvert($value, $type = "string")
    {
        // Manejo de valores vacíos (null, '', false, array vacío, etc.)
        if (empty($value) && $value !== 0 && $value !== '0') {
            switch ($type) {
                case 'string':
                    return "";
                case 'number':
                case 'integer':
                    return 0;
                default:
                    return $value;
            }
        }

        // Conversión según el tipo especificado
        switch ($type) {
            case 'string':
                return (string)$value;
            case 'number':
                return is_numeric($value) ? $value + 0 : 0; // float o int según el valor
            case 'integer':
                return is_numeric($value) ? intval($value) : 0; // fuerza un entero (sin decimales)
            default:
                return $value;
        }
    }
    protected function defaultValue($value, $default)
    {
        $text = $value;
        if (!$text) {
            $text = $default;
        }
        return $text;
    }

    protected function generarSeccionDatosGenerales($declaracion)
    {
        return [
            "nombre" => $this->nullConvert($declaracion->{"h1 nombre"}),
            "primerApellido" => $this->nullConvert($declaracion->{"h1 primerApellido"}),
            "segundoApellido" => $this->nullConvert($declaracion->{"h1 segundoApellido"}),
            // "curp" => $this->nullConvert($declaracion->{"h1 curp"}),
            // "rfc" => [
            //     "rfc" => $this->limitedString($declaracion->{"h1 rfc"}, 10),
            //     "homoClave" => $this->limitedString($declaracion->{"h1 homoClave"}, 3),
            // ],
            "correoElectronico" => [
                "institucional" => $this->nullConvert($declaracion->{"h1 institucional"}),
                // "personal" => $this->nullConvert($declaracion->{"h1 personal"}),
            ],
            // "telefono" => [
            //     "casa" => $this->nullConvert($declaracion->{"h1 casa"}),
            //     "celularPersonal" => $this->nullConvert($declaracion->{"h1 celularPersonal"}),
            // ],
            // "situacionPersonalEstadoCivil" => [
            //     "clave" => $this->nullConvert($declaracion->h1_clave_Estadocivil),
            //     "valor" => $this->nullConvert($declaracion->h1_valor_Estadocivil),
            // ],
            // "regimenMatrimonial" => [
            //     "clave" => $this->nullConvert($declaracion->{'h1 clave regimenMatrimonial'}),
            //     "valor" => $this->nullConvert($declaracion->{'h1 valor regimenMatrimonial'}),
            // ],
            // "paisNacimiento" => $this->nullConvert($declaracion->{'h1 Pais'}),
            // "nacionalidad" => $this->nullConvert($declaracion->{'h1 Nacionalidad'}),
            // "aclaracionesObservaciones" => $this->nullConvert($declaracion->{'h1 aclaracionesObservaciones'}),
        ];
    }

    protected function generarSeccionDomicilio($declaracion)
    {
        return [
            "domicilioMexico" => [
                "calle" => $this->nullConvert($declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 calle"}),
                "numeroExterior" => $this->nullConvert($declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 numeroExterior"}),
                "numeroInterior" => $this->nullConvert($declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 numeroInterior"}),
                "coloniaLocalidad" => $this->nullConvert($declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 coloniaLocalidad"}),
                "municipioAlcaldia" => $declaracion->{"h2 Pais"} ? "" :  [
                    "clave" => $declaracion->{'h2 clave municipioAlcaldia'},
                    "valor" => $declaracion->{'h2 valor municipioAlcaldia'},

                ],
                "entidadFederativa" => $declaracion->{"h2 Pais"} ? "" :  [
                    "clave" => $this->nullConvert($declaracion->{'h2 clave entidadFederativa'}),
                    "valor" => $this->nullConvert($declaracion->{'h2 valor entidadFederativa'}),

                ],
                "codigoPostal" => $this->nullConvert($declaracion->{"h2 Pais"} ? "" :  $declaracion->{"h2 CodigoPostal"}),
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
            "aclaracionesObservaciones" => $declaracion->{"h2 aclaracionesObservaciones"} ? $declaracion->{"h2 aclaracionesObservaciones"} : "",
        ];
    }

    protected function generarSeccionDatosCurriculares($declaracion)
    {

        return [
            "escolaridad" => [[
                "tipoOperacion" => "AGREGAR",
                "nivel" => [
                    "clave" => $this->nullConvert($declaracion->{'h3 clave Nivel'}),
                    "valor" => $this->nullConvert($declaracion->{'h3 valor Nivel'}),
                ],
                "institucionEducativa" => [
                    "nombre" => $this->nullConvert($declaracion->{'h3 nombre institucion'}),
                    "ubicacion" => $this->nullConvert($declaracion->{'h3 ubicacion institucion'}),

                ],
                "carreraAreaConocimiento" => $this->nullConvert($declaracion->{'h3 carreraAreaConocimiento'}),
                "estatus" => $this->nullConvert($declaracion->{'h3 estatus'}),
                "documentoObtenido" => $this->nullConvert($declaracion->{'h3 documentoObtenido'}),
                "fechaObtencion" => $this->nullConvert($declaracion->{'h3 fechaObtencion'}),
            ]],
            // "aclaracionesObservaciones" => $declaracion->{'h3 aclaracionesObservaciones'} ? $declaracion->{'h3 aclaracionesObservaciones'} : "",
        ];
    }
    protected function generarSeccionDatosEmpleoCargoComision($declaracion)
    {
        return [
            "tipoOperacion" => "AGREGAR",
            "nivelOrdenGobierno" => $this->nullConvert($declaracion->{'h4 nivelOrdenGobierno'}),
            "ambitoPublico" => $this->nullConvert($declaracion->{'h4 ambitoPublico'}),
            "nombreEntePublico" => $this->nullConvert($declaracion->{'h4 nombreEntePublico'}),
            "areaAdscripcion" => $this->nullConvert($declaracion->{'h4 areaAdscripcion'}),
            "empleoCargoComision" => $this->nullConvert($declaracion->{'h4 empleoCargoComision'}),
            "contratadoPorHonorarios" => $declaracion->{'h4 contratadoPorHonorarios'} ? true : false, //boleano
            "nivelEmpleoCargoComision" => $this->nullConvert($declaracion->{'h4 nivelEmpleoCargoComision'}),
            "funcionPrincipal" => $this->nullConvert($declaracion->{'h4 funcionPrincipal'}),
            "fechaTomaPosesion" => $this->nullConvert($declaracion->{'h4 fechaObtencion'}),
            "telefonoOficina" => [
                "telefono" => $this->nullConvert($declaracion->{'h4 telefono telefonoOficina'}),
                "extension" => $this->nullConvert($declaracion->{'h4 extension telefonoOficina'}),

            ],
            "domicilioMexico" => [
                "calle" => $this->nullConvert($declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 calle'}),
                "numeroExterior" => $this->nullConvert($declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 NumeroExterior'}),
                "numeroInterior" => $this->nullConvert($declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 NumeroInterior'}),
                "coloniaLocalidad" => $this->nullConvert($declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 coloniaLocalidad'}),
                "municipioAlcaldia" => $declaracion->{"h4 pais"} ? "" :  [
                    "clave" => $this->nullConvert($declaracion->{'h4 clave municipioAlcaldia'}),
                    "valor" => $this->nullConvert($declaracion->{'h4 valor municipioAlcaldia'}),

                ],
                "entidadFederativa" => $declaracion->{"h2 Pais"} ? "" :  [
                    "clave" => $this->nullConvert($declaracion->{'h4 clave entidadFederativa'}),
                    "valor" => $this->nullConvert($declaracion->{'h4 valor entidadFederativa'}),

                ],
                "codigoPostal" =>  $this->nullConvert($declaracion->{'h4 pais'} ? "" : $declaracion->{'h4 codigoPostal'}),
            ],
            "domicilioExtranjero" => [
                "calle" => $this->nullConvert($declaracion->{'h4 pais'} ?  $declaracion->{'h4 calle'} : ""),
                "numeroExterior" => $this->nullConvert($declaracion->{'h4 pais'} ? $declaracion->{'h4 NumeroExterior'} : ""),
                "numeroInterior" => $this->nullConvert($declaracion->{'h4 pais'} ? $declaracion->{'h4 NumeroInterior'} : ""),
                "ciudadLocalidad" => $this->nullConvert($declaracion->{'h4 pais'} ? $declaracion->{'h4 ciudadLocalidad'} : ""),
                "estadoProvincia" => $this->nullConvert($declaracion->{'h4 pais'} ? $declaracion->{'h4 estadoProvincia'} : ""),
                "pais" => $this->nullConvert($declaracion->{'h4 pais'} ? $declaracion->{'h4 pais'} : ""),
                "codigoPostal" => $this->nullConvert($declaracion->{'h4 pais'} ? $declaracion->{'h4 codigoPostal'} : ""),

            ],
            // "aclaracionesObservaciones" => $this->nullConvert($declaracion->{'h4 aclaracionesObservaciones'}),

        ];
    }
    protected function generarSeccionExperienciaLaboral($id)
    {
        $experienciasLaborales = DB::select("
        IF EXISTS (SELECT * FROM DECL_ExperienciaLaboral WHERE Id_SituacionPatrimonial = ?)
        BEGIN
            SELECT 
                Ex.Id_AmbitoPublico,
                Am.nombre AS ambitoSector_clave,
                Am.valor AS ambitoSector_valor,
                   CASE
            WHEN TRY_CAST(h4.NivelEmpleoCargoComision AS INT) = 1 THEN '1 Presidente Municipal, Regidores, Tesorero, Contralor, Oficial Mayor'
            WHEN TRY_CAST(h4.NivelEmpleoCargoComision AS INT) = 2 THEN '2 Encargados, Jefes, Supervisores, Administradores, Sub Directores, Directores, Juez, Coordinadores'
            WHEN TRY_CAST(h4.NivelEmpleoCargoComision AS INT) = 3 THEN '3 Operativos (secretaría, auxiliares, limpieza, administrativos, veladores, chofer, intendencia, fajineros, etc.)'
            WHEN h4.NivelEmpleoCargoComision IS NULL THEN NULL
            ELSE h4.NivelEmpleoCargoComision
        END as 'nivelOrdenGobierno',
        
                Ap.valor AS ambitoPublico,
                Ex.nombreEntePublico,
                Ex.areaAdscripcion,
                Ex.empleoCargoComision,
                Ex.Id_AmbitoSector,
                Ex.funcionPrincipal,
                Ex.NombreEmpresaSociedadAsociacion,
                Ex.RFC,
                Ex.Puesto,
                case
                when EX.Id_Sector = 1 then 'PRV'
                when EX.Id_Sector = 2 then 'PUB'
                when EX.Id_Sector = 0 then 'OTR'
                end as sector_clave,
                case
                when EX.Id_Sector = 1 then 'Privado'
                when EX.Id_Sector = 2 then 'Público'
                when EX.Id_Sector = 0 then 'Otro'
                end as sector_valor,
                CONVERT(VARCHAR(10), Ex.fechaIngreso, 120) AS fechaIngreso,
                CONVERT(VARCHAR(10), Ex.FechaEngreso, 120) AS fechaEgreso,
                IIF(Ex.FueEnMexico = 1, 'MX', 'EX') AS ubicacion,
                Ex.Aclaraciones,
                Ex.FueEnMexico
            FROM DECL_ExperienciaLaboral AS Ex 
            LEFT JOIN AmbitoSector AS Am ON Am.clave = Ex.Id_AmbitoSector
            LEFT JOIN AmbitoPublico AS Ap ON Ap.clave = Ex.Id_AmbitoPublico
            left join DECL_DatosEmpleoCargoComision as h4 on h4.Id_SituacionPatrimonial = ex.Id_SituacionPatrimonial
            WHERE Ex.Id_SituacionPatrimonial = ?;
        END
        ELSE 
        BEGIN
            SELECT 'No existen registros' AS message;
        END

        ", [$id, $id]);

        // Verificar si no hay registros
        if (isset($experienciasLaborales[0]->message)) {
            return [
                "ninguno" => true,
                "experienciaLaboral" => [],
                // "aclaracionesObservaciones" => ""
            ];
        }

        // Procesar los resultados
        $experiencia = [];
        foreach ($experienciasLaborales as $item) {
            if ($item->Id_AmbitoSector == 1) {
                // Experiencia en sector público
                $experiencia[] = [
                    "tipoOperacion" => 'AGREGAR',
                    "ambitoSector" => [
                        "clave" => $this->nullConvert($item->ambitoSector_clave ?? null),
                        "valor" => $this->nullConvert($item->ambitoSector_valor ?? null)
                    ],
                    "nivelOrdenGobierno" => $this->nullConvert($item->nivelOrdenGobierno ?? null),
                    "ambitoPublico" => $this->nullConvert($item->ambitoPublico ?? null),
                    "nombreEntePublico" => $this->nullConvert($item->nombreEntePublico ?? null),
                    "areaAdscripcion" => $this->nullConvert($item->areaAdscripcion ?? null),
                    "empleoCargoComision" => $this->nullConvert($item->empleoCargoComision ?? null),
                    "funcionPrincipal" => $this->nullConvert($item->funcionPrincipal ?? null),
                    "fechaIngreso" => $this->nullConvert($item->fechaIngreso ?? null),
                    "fechaEgreso" => $this->nullConvert($item->fechaEgreso ?? null),
                    "ubicacion" => $this->nullConvert($item->ubicacion ?? null)
                ];
            } else {
                // Experiencia en sector privado
                $experiencia[] = [
                    "tipoOperacion" => 'AGREGAR',
                    "ambitoSector" => [
                        "clave" => $this->nullConvert($item->ambitoSector_clave ?? null),
                        "valor" => $this->nullConvert($item->ambitoSector_valor ?? null)
                    ],
                    "nombreEmpresaSociedadAsociacion" => $this->nullConvert($item->NombreEmpresaSociedadAsociacion ?? null),
                    "rfc" => $this->nullConvert($item->RFC ?? null),
                    "area" => $this->nullConvert($item->areaAdscripcion ?? null),
                    "puesto" => $this->nullConvert($item->Puesto ?? null),
                    "sector" => [
                        "clave" => $this->nullConvert($item->sector_clave ?? null),
                        "valor" => $this->nullConvert($item->sector_valor ?? null)
                    ],
                    "ubicacion" => $this->nullConvert($item->ubicacion ?? null)
                ];
            }
        }

        // Obtener aclaraciones (del primer registro)
        // $aclaraciones = $experienciasLaborales[0]->Aclaraciones ?? '';

        return [
            "ninguno" => empty($experiencia),
            "experiencia" => $experiencia,
            // "aclaracionesObservaciones" => $aclaraciones
        ];
    }
    protected function generarSeccionDatosPareja($id)
    {
        $pareja = DB::selectOne("
        IF EXISTS (SELECT * FROM DECL_DatosPareja WHERE Id_SituacionPatrimonial = ?)
        BEGIN
        SELECT 
        p.Nombre,PrimerApellido,SegundoApellido,CONVERT(VARCHAR(10), FechaNacimiento, 120) AS 'FechaNacimiento',RfcPareja,rc.valor as 'relacionConDeclarante',  p.EsCiudadanoExtranjero,p.Curp,p.EsDependienteEconomico,p.HabitaDomicilioDeclarante,
          IIF(p.Id_LugarDondeReside = 1, 'MEXICO', 'EXTRANJERO') AS 'lugarDondeReside',p.Calle,p.NumeroExterior,p.NumeroInterior,p.ColoniaLocalidad,p.CodigoPostal,ac.valor as 'actividad valor',ac.nombre as 'actividad clave',Nio.valor as 'nivelOrdenGobierno',
          ap.valor as 'ambitoPublico',p.NombreEntePublico,p.AreaAdscripcion,p.EmpleoCargoComision,p.FuncionPrincipal,p.ValorSalarioMensualNeto as 'monto valor',m.Divisa,p.NombreEmpresaSociedadAsociacion as 'nombreEmpresaSociedadAsociacion',p.RfcEmpresa,
          CONVERT(VARCHAR(10), FechaIngreso, 120) AS 'FechaIngreso',s.valor as 'sector clave',s.Abreviatura as 'sector valor',p.EsProveedorContratistaGobierno,p.CiudadLocalidad,p.EstadoProvincia,p.Aclaraciones,  Pa.Pais,
		  Municipio.clave_geologica as 'clave municipioAlcaldia',Municipio.Municipio as 'valor municipioAlcaldia',
		  RIGHT('0' + CAST(Estado.Clave AS VARCHAR(2)), 2) AS 'clave entidadFederativa',
		  Estado.Estado as 'valor entidadFederativa'
		  	
		
        
          from DECL_DatosPareja as p
          left join ParentescoRelacion as rc on rc.clave = p.Id_RelacionDeclarante
          left join ActividadLaboral as ac on ac.clave = p.Id_ActividadLaboral
          left join NivelOrdenGobierno as Nio on Nio.clave = p.Id_NivelOrdenGobierno
          left join AmbitoPublico as ap on ap.clave = p.Id_AmbitoPublico
          left join Moneda as m on m.Clave = p.Id_MonedaSalarioMensualNeto
          left join Sector as s on s.clave = p.Id_Sector
          left join Pais as pa on pa.Clave = p.Id_Pais
		  LEFT JOIN Municipio  on Municipio.Clave =p.Id_MunicipioAlcaldia
		LEFT JOIN Estado  on Estado.Clave =p.Id_EntidadFederativa
                WHERE Id_SituacionPatrimonial = ?

        END
        ELSE 
        BEGIN
            SELECT 'No existen registros' AS message;
        END



    
        ", [$id, $id]);
        $datosPareja = [
            "ninguno" => isset($pareja->message) ? true : false,
        ];
        if (isset($pareja->message)) {
            return $datosPareja;
        }
        if (!isset($pareja->message)) {
            # code.. .
            $datosPareja  =
                [
                    "ninguno" => isset($pareja->message) ? true : false,

                    "tipoOperacion" => 'AGREGAR',
                    "nombre" => $this->nullConvert($pareja->Nombre),
                    "primerApellido" => $this->nullConvert($pareja->PrimerApellido),
                    "segundoApellido" => $this->nullConvert($pareja->SegundoApellido),
                    "fechaNacimiento" => $this->nullConvert($pareja->FechaNacimiento),
                    "rfc" => $this->nullConvert($pareja->RfcPareja),
                    "relacionConDeclarante" => $this->nullConvert($pareja->relacionConDeclarante),
                    "ciudadanoExtranjero" => $pareja->EsCiudadanoExtranjero ? true : false,
                    "curp" => $this->nullConvert($pareja->Curp),
                    "esDependienteEconomico" => $pareja->EsDependienteEconomico ? true : false,
                    "habitaDomicilioDeclarante" => $pareja->HabitaDomicilioDeclarante ? true : false,
                    "lugarDondeReside" => $this->nullConvert($pareja->lugarDondeReside),
                    "domicilioMexico" => [
                        "calle" =>  $this->nullConvert($pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"Calle"} : ""),
                        "numeroExterior" =>  $this->nullConvert($pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"NumeroExterior"} : ""),
                        "numeroInterior" =>  $this->nullConvert($pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"NumeroInterior"} : ""),
                        "coloniaLocalidad" =>  $this->nullConvert($pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"ColoniaLocalidad"} : ""),
                        "municipioAlcaldia" => [
                            "clave" => $this->nullConvert($pareja->{'clave municipioAlcaldia'}),
                            "valor" => $this->nullConvert($pareja->{'valor municipioAlcaldia'}),

                        ],
                        "entidadFederativa" => [
                            "clave" => $this->nullConvert($pareja->{'clave entidadFederativa'}),
                            "valor" => $this->nullConvert($pareja->{'valor entidadFederativa'}),

                        ],
                        "codigoPostal" => $this->nullConvert($pareja->{"lugarDondeReside"} == "MEXICO" ? "" :  $pareja->{"CodigoPostal"}),
                    ],
                    "domicilioExtranjero" => [
                        "calle" => $this->nullConvert($pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"Calle"} : ""),
                        "numeroExterior" => $this->nullConvert($pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"NumeroExterior"} : ""),
                        "numeroInterior" => $this->nullConvert($pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"NumeroInterior"} : ""),
                        "ciudadLocalidad" => $this->nullConvert($pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"CiudadLocalidad"} : ""),
                        "estadoProvincia" => $this->nullConvert($pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"EstadoProvincia"} : ""),
                        "pais" => $this->nullConvert($pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"Pais"} : ""),
                        "codigoPostal" => $this->nullConvert($pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"CodigoPostal"} : ""),
                    ],
                ];
        }
        if ($pareja->{'actividad valor'}) {
            $datosPareja["actividadLaboral"] = [
                "clave" => $this->nullConvert($pareja->{'actividad clave'}),
                "valor" => $this->nullConvert($pareja->{'actividad valor'}),
            ];
        }
        if ($pareja->{'actividad clave'} == 'PUB') {
            $datosPareja["actividadLaboralSectorPublico"] = [
                "nivelOrdenGobierno" => $this->nullConvert($pareja->nivelOrdenGobierno),
                "ambitoPublico" => $this->nullConvert($pareja->ambitoPublico),
                "nombreEntePublico" => $this->nullConvert($pareja->NombreEntePublico),
                "areaAdscripcion" => $this->nullConvert($pareja->AreaAdscripcion),
                "empleoCargoComision" => $this->nullConvert($pareja->EmpleoCargoComision),
                "funcionPrincipal" => $this->nullConvert($pareja->FuncionPrincipal),
                "salarioMensualNeto" => [
                    "monto" => [
                        "valor" =>  $this->nullConvert($pareja->{'monto valor'}),
                        "moneda" =>  $this->nullConvert($pareja->Divisa),

                    ],
                    "fechaIngreso" => $this->nullConvert($pareja->FechaIngreso),
                ]
            ];
        }
        if ($pareja->{'actividad clave'} == 'PRI') {
            $datosPareja["actividadLaboralSectorPrivadoOtro"] = [
                "nombreEmpresaSociedadAsociacion" => $this->nullConvert($pareja->nombreEmpresaSociedadAsociacion),
                "empleoCargoComision" => $this->nullConvert($pareja->EmpleoCargoComision),
                "rfc" => $this->nullConvert($pareja->RfcEmpresa),
                "fechaIngreso" => $this->nullConvert($pareja->FechaIngreso),
                "sector" => [
                    "clave" => $this->nullConvert($pareja->{'sector clave'}),
                    "valor" => $this->nullConvert($pareja->{'sector valor'}),
                ],
                "salarioMensualNeto" =>
                [
                    "monto" => [
                        "valor" => $this->nullConvert($pareja->{'monto valor'}),
                        "moneda" => $this->nullConvert($pareja->Divisa),

                    ]
                ],
                "proveedorContratistaGobierno" => false,


            ];
        }
        $datosPareja["aclaracionesObservaciones"] = $this->nullConvert($pareja->Aclaraciones);


        return $datosPareja;
    }
    protected function generarSeccionDatosDependientesEconomicos($id)
    {
        $dependientesEconomicos = DB::select(
            "
            IF EXISTS (SELECT * FROM DECL_DatosDependienteEconomico WHERE Id_SituacionPatrimonial = ?)
            BEGIN
            SELECT 
           dp.Nombre,dp.PrimerApellido,dp.SegundoApellido,CONVERT(VARCHAR(10), dp.FechaNacimiento, 120) AS 'h3 FechaNacimiento',dp.RfcDependiente,
           pr.abreviatura as 'clave parentescoRelacion',pr.valor as 'valor parentescoRelacion',dp.EsCiudadanoExtranjero,dp.Curp,dp.HabitaDomicilioDeclarante,IIF(dp.Id_LugarDondeReside = 1, 'MEXICO', 'EXTRANJERO') AS 'lugarDondeReside',
           dp.Calle,dp.NumeroExterior,dp.NumeroInterior,dp.ColoniaLocalidad,dp.CodigoPostal,
              Municipio.clave_geologica as 'clave municipioAlcaldia',Municipio.Municipio as 'valor municipioAlcaldia',
              RIGHT('0' + CAST(Estado.Clave AS VARCHAR(2)), 2) AS 'clave entidadFederativa',
              Estado.Estado as 'valor entidadFederativa',dp.CiudadLocalidad,dp.EstadoProvincia  	
              ,p.Pais,ac.valor as 'actividad valor',ac.nombre as 'actividad clave'
              ,Nio.valor as 'nivelOrdenGobierno',ap.valor as 'ambitoPublico'
              ,dp.NombreEntePublico,dp.AreaAdscripcion,dp.EmpleoCargoComision,dp.FuncionPrincipal,dp.ValorSalarioMensualNeto as 'monto valor',
              m.Divisa,CONVERT(VARCHAR(10), dp.FechaIngreso, 120) AS 'FechaIngreso',dp.NombreEmpresaSociedadAsociacion as 'nombreEmpresaSociedadAsociacion',dp.RfcEmpresa,s.valor as 'sector clave',s.Abreviatura as 'sector valor',dp.EsProveedorContratistaGobierno,dp.Aclaraciones
              from DECL_DatosDependienteEconomico as dp 
              inner join ParentescoRelacion as pr on pr.clave = dp.Id_ParentescoRelacion
              LEFT JOIN Municipio  on Municipio.Clave =dp.Id_MunicipioAlcaldia
              LEFT JOIN Estado  on Estado.Clave =dp.Id_EntidadFederativa
              left join Pais as p on p.Clave = dp.Id_Pais
              left join ActividadLaboral as ac on ac.clave = dp.Id_ActividadLaboral
              left join NivelOrdenGobierno as Nio on Nio.clave = dp.Id_NivelOrdenGobierno
              left join AmbitoPublico as ap on ap.clave = dp.Id_AmbitoPublico
              left join Moneda as m on m.Clave = dp.Id_MonedaSalarioMensualNeto
              left join Sector as s on s.clave = dp.Id_Sector
    
              WHERE Id_SituacionPatrimonial = ?
    
            END
            ELSE 
            BEGIN
                SELECT 'No existen registros' AS message;
            END
    
    
    
    
            ",
            [$id, $id]
        );
        $dependientes = [];
        $dependientes = [
            "ninguno" => isset($dependientesEconomicos[0]->message) ? true : false,
        ];
        $dependientes = [
            "ninguno" => isset($dependientesEconomicos[0]->message) ? true : false,
            "dependienteEconomico" => [],
            // "aclaracionesObservaciones" => "",

        ];
        if (isset($dependientesEconomicos[0]->message)) {
            return $dependientes;
        }
        foreach ($dependientesEconomicos as $dep) {

            $dependiente = [
                "tipoOperacion" => "AGREGAR",
                "nombre" => $this->nullConvert($dep->Nombre),
                "primerApellido" => $this->nullConvert($dep->PrimerApellido),
                "segundoApellido" => $this->nullConvert($dep->SegundoApellido),
                "fechaNacimiento" => $this->nullConvert($dep->{'h3 FechaNacimiento'}),
                "rfc" => $this->nullConvert($dep->RfcDependiente),
                "parentescoRelacion" => [
                    "clave" => $this->nullConvert($dep->{'clave parentescoRelacion'}),
                    "valor" => $this->nullConvert($dep->{'valor parentescoRelacion'}),
                ],
                "extranjero" => $dep->EsCiudadanoExtranjero ? true : false,
                "curp" => $this->nullConvert($dep->Curp),
                "habitaDomicilioDeclarante" => $dep->HabitaDomicilioDeclarante ? true : false,
                "lugarDondeReside" => $this->nullConvert($dep->lugarDondeReside),
                "domicilioMexico" => [
                    "calle" =>  $this->nullConvert($dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"Calle"} : ""),
                    "numeroExterior" =>  $this->nullConvert($dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroExterior"} : ""),
                    "numeroInterior" =>  $this->nullConvert($dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroInterior"} : ""),
                    "coloniaLocalidad" =>  $this->nullConvert($dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"ColoniaLocalidad"} : ""),
                    "municipioAlcaldia" => [
                        "clave" => $this->nullConvert($dep->{'clave municipioAlcaldia'}),
                        "valor" => $this->nullConvert($dep->{'valor municipioAlcaldia'}),

                    ],
                    "entidadFederativa" => [
                        "clave" => $this->nullConvert($dep->{'clave entidadFederativa'}),
                        "valor" => $this->nullConvert($dep->{'valor entidadFederativa'}),

                    ],
                    "codigoPostal" => $this->nullConvert($dep->{"lugarDondeReside"} == "MEXICO" ? "" :  $dep->{"CodigoPostal"}),
                ],
                "domicilioExtranjero" => [
                    "calle" => $this->nullConvert($dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Calle"} : ""),
                    "numeroExterior" => $this->nullConvert($dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroExterior"} : ""),
                    "numeroInterior" => $this->nullConvert($dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroInterior"} : ""),
                    "ciudadLocalidad" => $this->nullConvert($dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CiudadLocalidad"} : ""),
                    "estadoProvincia" => $this->nullConvert($dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"EstadoProvincia"} : ""),
                    "pais" => $this->nullConvert($dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Pais"} : ""),
                    "codigoPostal" => $this->nullConvert($dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CodigoPostal"} : ""),
                ],


            ];
            if ($dep->{'actividad valor'}) {
                $dependiente["actividadLaboral"] = [
                    "clave" => $this->nullConvert($dep->{'actividad clave'}),
                    "valor" => $this->nullConvert($dep->{'actividad valor'}),
                ];
            }
            if ($dep->{'actividad clave'} == 'PUB') {
                $dependiente["actividadLaboralSectorPublico"] = [
                    "nivelOrdenGobierno" => $this->nullConvert($dep->nivelOrdenGobierno),
                    "ambitoPublico" => $this->nullConvert($dep->ambitoPublico),
                    "nombreEntePublico" => $this->nullConvert($dep->NombreEntePublico),
                    "areaAdscripcion" => $this->nullConvert($dep->AreaAdscripcion),
                    "empleoCargoComision" => $this->nullConvert($dep->EmpleoCargoComision),
                    "funcionPrincipal" => $this->nullConvert($dep->FuncionPrincipal),
                    "salarioMensualNeto" => [
                        "monto" => [
                            "valor" =>  $this->nullConvert($dep->{'monto valor'}),
                            "moneda" =>  $this->nullConvert($dep->Divisa),

                        ],
                        "fechaIngreso" => $this->nullConvert($dep->FechaIngreso),
                    ]
                ];
            }
            if ($dep->{'actividad clave'} == 'PRI') {
                $dependiente["actividadLaboralSectorPrivadoOtro"] = [
                    "nombreEmpresaSociedadAsociacion" => $this->nullConvert($dep->nombreEmpresaSociedadAsociacion),
                    "empleoCargoComision" => $this->nullConvert($dep->EmpleoCargoComision),
                    "rfc" => $this->nullConvert($dep->RfcEmpresa),
                    "fechaIngreso" => $this->nullConvert($dep->FechaIngreso),
                    "proveedorContratistaGobierno" => false,
                    "sector" => [
                        "clave" => $this->nullConvert($dep->{'sector clave'}),
                        "valor" => $this->nullConvert($dep->{'sector valor'}),
                    ],
                    "salarioMensualNeto" =>
                    [
                        "monto" => [
                            "valor" => $this->nullConvert($dep->{'monto valor'}),
                            "moneda" => $this->nullConvert($dep->Divisa),

                        ]
                    ],


                ];
            }
            $dependientes["dependienteEconomico"][] = $dependiente;
        }

        return $dependientes;
    }
    protected function generarSeccionIngresos($id)
    {
        $declaracion = DB::selectOne(
            "select  i.Id_SituacionPatrimonial,i.RemuneracionMensualAnualConclusionCargoPublico as 'h8 valor remuneracionMensualCargoPublico'
            ,i.OtrosIngresosMensualesAnualesConclusionTotal as 'h8 valor otrosIngresosMensualesTotal'
            ,i.AICE_Id_RemuneracionTotal as 'h8 valor actividadIndustrialComercialEmpresarial',
            i.AICE_NombreRazonSocial as 'h8 nombreRazonSocial',
            i.AICE_TipoNegocio as 'h8 TipoNegocio',
            i.AF_RemuneracionTotal as 'h8 valor actividadFinanciera',
            ti.abreviatura as 'h8 clave tipoInstrumento',
            ti.valor as 'h8 valor tipoInstrumento',
            i.SP_RemuneracionTotal as 'h8 RemuneracionTotal',
            SP_TipoServicioPrestado as 'h8 servicios tipoServicio',
            i.IngresoMensualAnualConclusionNeto as 'h8 ingresos',
            i.IngresoNetoParejaDependiente as 'h8 ingresoMensualNetoParejaDependiente',
            i.TotalIngresosNetos as 'h8 totalIngresosMensualesNetos',
            i.Aclaraciones as 'h8 Aclaraciones'
            from DECL_Ingresos as i
    left join TipoInstrumento as ti on ti.clave = i.AF_Id_TipoInstrumento
    where i.Id_SituacionPatrimonial =?
    ",
            [$id]
        );
        if (!$declaracion) {
            return;
        }
        return [
            "remuneracionMensualCargoPublico" => [
                "valor" => $this->nullConvert($declaracion->{'h8 valor remuneracionMensualCargoPublico'}, 'number'),
                "moneda" => "MXN",
            ],
            "otrosIngresosMensualesTotal" => [
                "valor" => $this->nullConvert($declaracion->{'h8 valor otrosIngresosMensualesTotal'}, 'number'),
                "moneda" => "MXN",
            ],
            "actividadIndustrialComercialEmpresarial" => [
                "remuneracionTotal" => [
                    "monto" => [
                        "valor" => $this->nullConvert($declaracion->{'h8 valor actividadIndustrialComercialEmpresarial'}, 'number'),
                        "moneda" => "MXN",
                    ]
                ],
                "actividades" => [
                    [
                        "remuneracion" => [
                            "monto" => [
                                "valor" => $this->nullConvert($declaracion->{'h8 valor actividadIndustrialComercialEmpresarial'}, 'number'),
                                "moneda" => "MXN",
                            ],
                            "nombreRazonSocial" => $this->nullConvert($declaracion->{'h8 nombreRazonSocial'}),
                            "tipoNegocio" => $this->nullConvert($declaracion->{'h8 TipoNegocio'}),
                        ]
                    ]
                ],
            ],
            "actividadFinanciera" => [
                "remuneracionTotal" => [
                    "monto" => [
                        "valor" => $this->nullConvert($declaracion->{'h8 valor actividadFinanciera'}, 'number'),
                        "moneda" => "MXN",
                    ]
                ],
                "actividades" => (
                    in_array($declaracion->{'h8 clave tipoInstrumento'}, ["CAP", "FIN", "OPR", "SSI", "VBU", "BON", "OTRO"])
                    ? [[
                        "remuneracion" => [
                            "monto" => [
                                "valor" => $this->nullConvert($declaracion->{'h8 valor actividadFinanciera'}, 'number'),
                                "moneda" => "MXN",
                            ]
                        ],
                        "tipoInstrumento" => [
                            "clave" => $this->nullConvert($declaracion->{'h8 clave tipoInstrumento'}),
                            "valor" => $this->nullConvert($declaracion->{'h8 valor tipoInstrumento'}),
                        ]
                    ]]
                    : []
                ),

            ],
            "serviciosProfesionales" => [
                "remuneracionTotal" => [
                    "monto" => [
                        "valor" => $this->nullConvert($declaracion->{'h8 RemuneracionTotal'}, 'number'),
                        "moneda" => "MXN",
                    ]
                ],
                "servicios" => [
                    [
                        "remuneracion" => [
                            "monto" => [
                                "valor" => $this->nullConvert($declaracion->{'h8 RemuneracionTotal'}, 'number'),
                                "moneda" => "MXN",
                            ]
                        ],
                        "tipoServicio" => $this->nullConvert($declaracion->{'h8 servicios tipoServicio'}),
                    ]
                ]
            ],
            // "serviciosProfesionales" => [
            //     "remuneracionTotal" => [
            //         "monto" => [
            //             "valor" => "",
            //             "moneda" => "MXN",
            //         ]
            //     ],
            //     "servicios" => [
            //         [
            //             "remuneracion" => [
            //                 "monto" => [
            //                     "valor" => "",
            //                     "moneda" => "MXN",
            //                 ]
            //             ],
            //             "tipoServicio" => "",
            //         ]
            //     ]
            // ],
            "otrosIngresos" => [
                "remuneracionTotal" => [
                    "monto" => [
                        "valor" => $this->nullConvert($declaracion->{'h8 valor otrosIngresosMensualesTotal'}, 'number'),
                        "moneda" => "MXN",
                    ]
                ],
                "ingresos" => [
                    [
                        "remuneracion" => [
                            "monto" => [
                                "valor" => $this->nullConvert($declaracion->{'h8 valor otrosIngresosMensualesTotal'}, 'number'),
                                "moneda" => "MXN",
                            ]
                        ],
                        "tipoIngreso" => "",
                    ]
                ]
            ],
            "ingresoMensualNetoDeclarante" => [
                "monto" => [
                    "valor" => $this->nullConvert($declaracion->{'h8 ingresos'}, 'number'),
                    "moneda" => "MXN",
                ]
            ],
            // "ingresoMensualNetoParejaDependiente" => [
            //     "monto" => [
            //         "valor" => $this->nullConvert($declaracion->{'h8 ingresoMensualNetoParejaDependiente'}),
            //         "moneda" => "MXN",
            //     ]
            // ],
            "totalIngresosMensualesNetos" => [
                "monto" => [
                    "valor" => $this->nullConvert($declaracion->{'h8 totalIngresosMensualesNetos'}, 'number'),
                    "moneda" => "MXN",
                ]
            ],
            // "aclaracionesObservaciones" => $this->nullConvert($declaracion->{'h8 Aclaraciones'}),
        ];
    }
    protected function generarSeccionActividadAnualAnterior($id)
    {
        $declaracion = DB::selectOne(
            "select  
			i.FechaInicio,
			i.FechaConclusion,
			i.RemuneracionNetaCargoPublico as 'valor remuneracionMensualCargoPublico'
            ,i.OtrosIngresosTotal as 'valor otrosIngresosMensualesTotal'
            ,i.AICE_Id_RemuneracionTotal as 'valor actividadIndustrialComercialEmpresarial',
            i.AICE_NombreRazonSocial as 'nombreRazonSocial',
            i.AICE_TipoNegocio as 'TipoNegocio',
            i.AF_RemuneracionTotal as 'valor actividadFinanciera',
            ti.abreviatura as 'clave tipoInstrumento',
            ti.valor as 'valor tipoInstrumento',
            i.SP_RemuneracionTotal as 'RemuneracionTotal',
            SP_TipoServicioPrestado as 'servicios tipoServicio',
            i.IngresoMensualConclusionNeto as 'ingresos',
            i.IngresoNetoParejaDependiente as 'ingresoMensualNetoParejaDependiente',
            i.TotalIngresosNetos as 'totalIngresosMensualesNetos',
			tb.clave as 'tipoBienEnajenado',
            i.Aclaraciones
            from DECL_ActividadAnualAnterior as i
    left join TipoInstrumento as ti on ti.clave = i.AF_Id_TipoInstrumento
	left join TipoBienEnajenacionBienes as tb on tb.clave = i.EB_Id_TipoBienEnajenado
    where tb.clave is not null and i.Id_SituacionPatrimonial =?
    ",
            [$id]
        );
        $actividadAnual = [];
        $actividadAnual["servidorPublicoAnioAnterior"] = $declaracion ? true : false;
        if (!$declaracion) {
            return $actividadAnual;
        }
        $actividadAnual["fechaIngreso"] =  $this->nullConvert($declaracion->{'FechaInicio'});
        $actividadAnual["fechaConclusion"] = $this->nullConvert($declaracion->{'FechaConclusion'});
        $actividadAnual["remuneracionNetaCargoPublico"] = [
            "monto" => [
                "valor" => $this->nullConvert($declaracion->{'valor remuneracionMensualCargoPublico'}, 'number'),
                "moneda" => "MXN"
            ]
        ];
        $actividadAnual["otrosIngresosTotal"] = [
            "monto" => [
                "valor" => $this->nullConvert($declaracion->{'valor otrosIngresosMensualesTotal'}, 'number'),
                "moneda" => "MXN"
            ]
        ];
        $actividadAnual["actividadIndustrialComercialEmpresarial"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $this->nullConvert($declaracion->{'valor actividadIndustrialComercialEmpresarial'}, 'number'),
                    "moneda" => "MXN"
                ]
            ],
            "actividades" => (
                in_array($declaracion->{'clave tipoInstrumento'}, ["CAP", "FIN", "OPR", "SSI", "VBU", "BON", "OTRO"])
                ?    [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $this->nullConvert($declaracion->{'valor actividadIndustrialComercialEmpresarial'}, 'number'),
                            "moneda" => "MXN"
                        ],

                    ],
                    "nombreRazonSocial" => $this->nullConvert($declaracion->{'nombreRazonSocial'}),
                    "tipoNegocio" => $this->nullConvert($declaracion->{'TipoNegocio'}),

                ]
                : []
            ),
        ];
        $actividadAnual["actividadFinanciera"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $this->nullConvert($declaracion->{'valor actividadFinanciera'}, 'number'),
                    "moneda" => "MXN"
                ]
            ],
            "actividades" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $this->nullConvert($declaracion->{'valor actividadFinanciera'}, 'number'),
                            "moneda" => "MXN"
                        ],

                    ],
                    "tipoInstrumento" => [
                        "clave" => $this->nullConvert($declaracion->{'clave tipoInstrumento'}),
                        "valor" => $this->nullConvert($declaracion->{'valor tipoInstrumento'}),


                    ],

                ]
            ]
        ];
        $actividadAnual["serviciosProfesionales"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $this->nullConvert($declaracion->{'RemuneracionTotal'} . 'number'),
                    "moneda" => "MXN",
                ]
            ],
            "servicios" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $this->nullConvert($declaracion->{'RemuneracionTotal'}, 'number'),
                            "moneda" => "MXN",
                        ]
                    ],
                    "tipoServicio" => $this->nullConvert($declaracion->{'servicios tipoServicio'}),
                ]
            ]
        ];
        $actividadAnual["enajenacionBienes"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => 0,
                    "moneda" => "MXN",
                ]
            ],
            "bienes" => [
              
                    [
                        "remuneracion" => [
                            "monto" => [
                                "valor" => 0,
                                "moneda" => "MXN",
                            ]
                        ]
                    ],
               "tipoBienEnajenado" => $this->nullConvert($declaracion->{'tipoBienEnajenado'})
                   
                
            ]
        ];
        $actividadAnual["otrosIngresos"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $this->nullConvert($declaracion->{'ingresos'}, 'number'),
                    "moneda" => "MXN",
                ]
            ],
            "ingresos" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $this->nullConvert($declaracion->{'ingresos'}, 'number'),
                            "moneda" => "MXN",
                        ]
                    ],
                    "tipoIngreso" => "",
                ]
            ]
        ];
        $actividadAnual["ingresoNetoAnualDeclarante"] = [
            "monto" => [
                "valor" => $this->nullConvert($declaracion->{'ingresos'}, 'number'),
                "moneda" => "MXN",
            ]
        ];
        // $actividadAnual["ingresoNetoAnualParejaDependiente"] = [
        //     "monto" => [
        //         "valor" => $this->nullConvert($declaracion->{'ingresoMensualNetoParejaDependiente'}),
        //         "moneda" => "MXN",
        //     ]
        // ];
        $actividadAnual["totalIngresosNetosAnuales"] = [
            "monto" => [
                "valor" => $this->nullConvert($declaracion->{'totalIngresosMensualesNetos'}, 'number'),
                "moneda" => "MXN",
            ]
        ];
        // $actividadAnual["aclaracionesObservaciones"] = $this->nullConvert($declaracion->{'Aclaraciones'});
        return $actividadAnual;
    }
    protected function generarSeccionBienesInmuebles($id)
    {
        $declaracion = DB::select("
        select ti.valor as 'valor TipoInmueble',ti.abreviatura as 'clave TipoInmueble',t.valor as 'valor titular',t.abreviatura as 'clave titular',bienes.PorcentajePropiedad,bienes.SuperficieTerreno,bienes.Superficieconstruncion,
        CASE
            WHEN bienes.T_id_TipoPersona = 1 or bienes.T_id_TipoPersona = 0 THEN 'FISICA'
            WHEN bienes.T_id_TipoPersona = 2 THEN 'MORAL'
        END AS 'tercero tipo_persona',bienes.T_NombreRazonSocial,bienes.T_Rfc,
        CASE
            WHEN bienes.TR_Id_TipoPersona = 1 or bienes.TR_Id_TipoPersona = 0 THEN 'FISICA'
            WHEN bienes.TR_Id_TipoPersona = 2 THEN 'MORAL'
        END AS 'transmisor tipo_persona',bienes.TR_NombreRazonSocial,bienes.TR_Rfc,
        rd.valor as 'valor relacion',
        rd.abreviatura as 'clave relacion',
        fd.valor as 'valor FormaAdquisicion',
        fd.abreviatura as 'clave FormaAdquisicion',
        fp.valor as 'forma_pago',
        bienes.ValorAdquisicion,
        bienes.FechaAdquisicion,
        bienes.DatoIdentificacion,
        vc.valor as 'valor conformeA',
        mb.valor as 'valor motivo_baja',
        mb.abreviatura as 'clave motivo_baja',
        bienes.Calle,
        bienes.NumeroExterior,
        bienes.NumeroInterior,
        bienes.ColoniaLocalidad,
        Municipio.clave_geologica as 'clave municipioAlcaldia',
        Municipio.Municipio as 'valor municipioAlcaldia',
        RIGHT('0' + CAST(Estado.Clave AS VARCHAR(2)), 2) AS 'clave entidadFederativa',
        Estado.Estado as 'valor entidadFederativa',
        bienes.CodigoPostal,
        bienes.CiudadLocalidad,
        bienes.EstadoProvincia,
		bienes.Aclaraciones,
        p.Pais,
        IIF(bienes.EsEnMexico = 1, 'MEXICO', 'EXTRANJERO') AS 'lugarDondeReside'
        from DECL_BienesInmuebles as bienes
        inner join TipoInmueble as ti on ti.clave = bienes.Id_TipoInmueble
        inner join Titular as t on t.clave = bienes.Id_Titular
        left join ParentescoRelacion as rd on rd.clave = bienes.Id_Relacion
        left join FormaAdquisicion as fd on fd.clave = bienes.Id_FormaAdquisicion
        left join FormaPago as fp on fp.clave = bienes.Id_FormaPago
        left join ValorConformeA as vc on vc.clave = bienes.Id_ValorConformeA
        left join MotivoBaja as mb on mb.clave = bienes.Id_MotivoBaja
        LEFT JOIN Municipio  on Municipio.Clave =bienes.Id_MunicipioAlcaldia
        LEFT JOIN Estado  on Estado.Clave =bienes.Id_EntidadFederativa
        left join Pais as p on p.Clave  = bienes.Id_Pais
        where t.clave = 1 and  vc.clave is not null and bienes.Id_SituacionPatrimonial =?
        ", [$id]);

        $resultado = [
            "ninguno" => empty($declaracion),
            "bienInmueble" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }

        foreach ($declaracion as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $bien = [
                'tipoOperacion' => "AGREGAR",
                'tipoInmueble' => [
                    "clave" => $this->nullConvert($dep->{'clave TipoInmueble'}),
                    "valor" => $this->nullConvert($dep->{'valor TipoInmueble'}),
                ],
                'titular' => [
                    [
                        "titularBien" => [
                            [
                                "clave" => $this->nullConvert($dep->{'clave titular'}),
                                "valor" => $this->nullConvert($dep->{'valor titular'}),
                            ]
                        ]
                    ]
                ],
                'porcentajePropiedad' => $this->nullConvert($dep->{'PorcentajePropiedad'}, 'integer'),
                'superficieTerreno' => [
                    "superficie" => [
                        "valor" => $this->nullConvert($dep->{'SuperficieTerreno'}, 'integer'),
                        "unidad" => "m2",
                    ]
                ],
                'superficieConstruccion' => [
                    "superficie" => [
                        "valor" => $this->nullConvert($dep->{'Superficieconstruncion'}, 'integer'),
                        "unidad" => "m2",
                    ]
                ],
                'tercero' => [
                    [
                        "tipoPersona" => $this->nullConvert($dep->{'tercero tipo_persona'}),
                        "nombreRazonSocial" => $this->nullConvert($dep->{'T_NombreRazonSocial'}),
                    ]
                ],
                'transmisor' => [
                    [
                        "tipoPersona" => $this->nullConvert($dep->{'transmisor tipo_persona'}),
                        ...$this->conditionalField(
                            $dep->{'transmisor tipo_persona'} == 'MORAL',
                            'nombreRazonSocial',
                            $this->nullConvert($dep->{'TR_NombreRazonSocial'})
                        ),
                        ...$this->conditionalField(
                            $dep->{'transmisor tipo_persona'} == 'MORAL',
                            'rfc',
                            $this->nullConvert($dep->{'TR_Rfc'})
                        ),
                        // "nombreRazonSocial" =>    $this->nullConvert($dep->{'TR_NombreRazonSocial'}),
                        // "rfc" => $this->nullConvert($dep->{'TR_Rfc'}),
                        // "relacion" => [
                        //     "parentescoRelacion" => [
                        //         "clave" => $this->nullConvert($dep->{'clave relacion'}),
                        //         "valor" => $this->nullConvert($dep->{'valor relacion'}),
                        //     ]
                        // ],
                    ]
                ],
                'formaAdquisicion' => [
                    "clave" => $this->nullConvert($dep->{'clave FormaAdquisicion'}),
                    "valor" => $this->nullConvert($dep->{'valor FormaAdquisicion'}),
                ],
                "formaPago" => $this->nullConvert($dep->{'forma_pago'}),
                'valorAdquisicion' => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'ValorAdquisicion'}, 'number'),
                        "moneda" => "MXN",
                    ]
                ],
                "fechaAdquisicion" => $this->nullConvert($dep->{'FechaAdquisicion'}),
                // "datoIdentificacion" => $this->nullConvert($dep->{'DatoIdentificacion'}),
                "valorConformeA" => $this->nullConvert($dep->{'valor conformeA'}),
                // 'domicilioMexico' => [
                //     "calle" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"Calle"} : "",
                //     "numeroExterior" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroExterior"} : "",
                //     "numeroInterior" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroInterior"} : "",
                //     "coloniaLocalidad" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"ColoniaLocalidad"} : "",
                //     "municipioAlcaldia" => [
                //         "clave" => $dep->{'clave municipioAlcaldia'},
                //         "valor" => $dep->{'valor municipioAlcaldia'},
                //     ],
                //     "entidadFederativa" => [
                //         "clave" => $dep->{'clave entidadFederativa'},
                //         "valor" => $dep->{'valor entidadFederativa'},
                //     ],
                //     "codigoPostal" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"CodigoPostal"} : "",
                // ],
                // "domicilioExtranjero" => [
                //     "calle" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Calle"} : "",
                //     "numeroExterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroExterior"} : "",
                //     "numeroInterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroInterior"} : "",
                //     "ciudadLocalidad" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CiudadLocalidad"} : "",
                //     "estadoProvincia" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"EstadoProvincia"} : "",
                //     "pais" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Pais"} : "",
                //     "codigoPostal" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CodigoPostal"} : "",
                // ],
                "motivoBaja" => (
                    in_array($dep->{'clave motivo_baja'}, ["VNT", "DNC", "SNT", "OTRO"])
                    ?    [
                        "clave" => $this->nullConvert($dep->{"clave motivo_baja"}),
                        "valor" => $this->nullConvert($dep->{"valor motivo_baja"}),
    
                    ]
                    : []
                ),
          
            ];

            $resultado['bienInmueble'][] = $bien;
        }

        return $resultado;
    }

    protected function generarSeccionVehiculos($id)
    {
        $declaracion = DB::select("
        WITH VehiculosUnicos AS (
            SELECT 
                Id_Vehiculos,
                Id_SituacionPatrimonial,
                Id_TipoVehiculo,
                Id_Titular,
                TR_Id_TipoPersona,
                TR_NombreRazonSocial,
                TR_Rfc,
                Marca,
                Modelo,
                Anio,
                NumeroSerieRegistro,
                T_Id_TipoPersona,
                T_NombreRazonSocial,
                T_Rfc,
                Id_EntidadFederativa,
                Id_FormaAdquisicion,
                Id_FormaPago,
                ValorAdquisicion,
                FechaAdquisicion,
                Id_MotivoBaja,
                ROW_NUMBER() OVER (PARTITION BY Id_Vehiculos ORDER BY Id_SituacionPatrimonial DESC) AS RowNum
            FROM DECL_Vehiculos
        )
        SELECT 
            vh.Id_Vehiculos,
            vh.Id_SituacionPatrimonial,
            tv.valor AS valor_vehiculo,
            tv.abreviatura AS clave_vehiculo,
            t.valor AS valor_titular, 
            t.abreviatura AS clave_titular,  
            CASE
                WHEN vh.TR_Id_TipoPersona IN (0, 1) THEN 'FISICA'
                WHEN vh.TR_Id_TipoPersona = 2 THEN 'MORAL'
            END AS transmisor_tipo_persona,
            vh.TR_NombreRazonSocial,
            vh.TR_Rfc,
            vh.Marca,
            vh.Modelo,
            vh.Anio,
            vh.NumeroSerieRegistro,
            CASE
                WHEN vh.T_Id_TipoPersona IN (0, 1) THEN 'FISICA'
                WHEN vh.T_Id_TipoPersona = 2 THEN 'MORAL'
            END AS tercero_tipo_persona,
            vh.T_NombreRazonSocial,
            vh.T_Rfc,
            RIGHT('0' + CAST(e.Clave AS VARCHAR(2)), 2) AS clave_entidadFederativa,
            e.Estado AS valor_entidadFederativa,
            fa.valor AS valor_adquisicion, 
            fa.abreviatura AS clave_adquisicion,
            fp.valor AS forma_pago,
            vh.ValorAdquisicion,
            vh.FechaAdquisicion,
            mb.valor AS valor_motivo_baja,
            mb.abreviatura AS clave_motivo_baja
        FROM 
            VehiculosUnicos vh
            LEFT JOIN TipoVehiculo tv ON tv.clave = vh.Id_TipoVehiculo
            LEFT JOIN Titular t ON t.clave = vh.Id_Titular
            LEFT JOIN Estado e ON e.Clave = vh.Id_EntidadFederativa
            LEFT JOIN FormaAdquisicion fa ON fa.clave = vh.Id_FormaAdquisicion
            LEFT JOIN FormaPago fp ON fp.clave = vh.Id_FormaPago  -- Corregido: estaba fa.clave = vh.Id_FormaPago
            LEFT JOIN MotivoBaja mb ON mb.clave = vh.Id_MotivoBaja
        WHERE 
            vh.RowNum = 1  
            AND t.clave =1
    and vh.Id_SituacionPatrimonial =?

        
        ", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "vehiculo" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }

        foreach ($declaracion as $dep) {
            $vehiculo = [
                "tipoOperacion" => "AGREGAR",
                "tipoVehiculo" => [
                    "clave" => $this->nullConvert($dep->{'clave_vehiculo'}),
                    "valor" => $this->nullConvert($dep->{'valor_vehiculo'}),

                ],
                "titular" => [
                    [
                        "titularBien" => [
                            [

                                "clave" => $this->nullConvert($dep->{'clave_titular'}),
                                "valor" => $this->nullConvert($dep->{'valor_titular'}),
                            ]
                        ]
                    ]

                ],
                "transmisor" => [
                    [
                        "tipoPersona" => $this->nullConvert($dep->{'transmisor_tipo_persona'}),
                        // "nombreRazonSocial" => $this->nullConvert($dep->{'TR_NombreRazonSocial'}),
                        ...$this->conditionalField(
                            $dep->{'transmisor_tipo_persona'} == 'MORAL',
                            'nombreRazonSocial',
                            $this->nullConvert($dep->{'TR_NombreRazonSocial'})
                        ),
                        ...$this->conditionalField(
                            $dep->{'transmisor_tipo_persona'} == 'MORAL',
                            'rfc',
                            $this->nullConvert($dep->{'TR_Rfc'})
                        ),
                        // "rfc" => $this->nullConvert($dep->{'TR_Rfc'}),
                        // "relacion" => [
                        //     "parentescoRelacion" => [
                        //         "clave" => "OTRO",
                        //         "valor" => "OTRO"
                        //     ]
                        // ],

                    ]
                ],
                "marca" => $this->nullConvert($dep->{'Marca'}),
                "modelo" => $this->nullConvert($dep->{'Modelo'}),
                "anio" => $this->nullConvert($dep->{'Anio'}),
                // "numeroSerieRegistro" => $this->nullConvert($dep->{'NumeroSerieRegistro'}),
                "tercero" => [

                    [
                        "tipoPersona" => $this->nullConvert($dep->{'tercero_tipo_persona'}),
                        "nombreRazonSocial" => $this->nullConvert($dep->{'T_NombreRazonSocial'}),
                        "rfc" => $this->nullConvert($dep->{'T_Rfc'}),

                    ]
                ],
                // "lugarRegistro" => [
                //     "pais" => "MX",
                //     "entidadFederativa" => [
                //         "clave" => $this->nullConvert($dep->{'clave_entidadFederativa'}),
                //         "valor" => $this->nullConvert($dep->{'valor_entidadFederativa'}),

                //     ]
                // ],
                "formaAdquisicion" => [
                    "clave" => $this->nullConvert($dep->{'clave_adquisicion'}),
                    "valor" => $this->nullConvert($dep->{'valor_adquisicion'}),
                ],
                "formaPago" => $this->nullConvert($dep->{'forma_pago'}),
                "valorAdquisicion" => [
                    "valor" => $this->nullConvert($dep->{'ValorAdquisicion'}, 'number'),
                    "moneda" => "MXN",
                ],
                "fechaAdquisicion" => $this->nullConvert($dep->{'FechaAdquisicion'}),
                "motivoBaja" => [
                    "clave" => $this->nullConvert($dep->{'clave_motivo_baja'}),
                    "valor" => $this->nullConvert($dep->{'valor_motivo_baja'}),
                ],

            ];
            $resultado['vehiculo'][] = $vehiculo;
        }
        return $resultado;
    }
    protected function generarSeccionBienesMuebles($id)
    {
        $declaracion = DB::select("
        SELECT 
            t.valor as 'valor_titular',
            t.abreviatura as 'clave_titular',
            tbm.valor as 'valor_bien',
            tbm.abreviatura as 'clave_bien',
            CASE
                WHEN bm.TR_Id_TipoPersona IN (0, 1) THEN 'FISICA'
                WHEN bm.TR_Id_TipoPersona = 2 THEN 'MORAL'
            END AS transmisor_tipo_persona,
            bm.TR_NombreRazonSocial,
            bm.TR_Rfc,
            CASE
                WHEN bm.T_Id_TipoPersona IN (0, 1) THEN 'FISICA'
                WHEN bm.T_Id_TipoPersona = 2 THEN 'MORAL'
            END AS tercero_tipo_persona,
            bm.T_NombreRazonSocial,
            bm.T_Rfc,
            bm.DescripcionGeneralBien,
            fa.valor as 'valor_formadquiscion',
            fa.abreviatura as 'clave_formadquiscion',
            fp.valor as 'valor_formapago',
            bm.ValorAdquisicion,
            bm.FechaAdquisicion,
            mb.valor as 'valor_motivo_baja',
            mb.abreviatura as 'clave_motivo_baja',
            bm.Aclaraciones
        FROM DECL_BienesMuebles as bm 
        LEFT JOIN Titular as t ON t.clave = bm.Id_Titular
        LEFT JOIN TipoBienBienesMuebles as tbm ON tbm.clave = bm.Id_TipoBien
        LEFT JOIN FormaAdquisicion as fa ON fa.clave = bm.Id_FormaAdquisicion
        LEFT JOIN FormaPago as fp ON fp.clave = bm.Id_FormaPago
        LEFT JOIN MotivoBaja as mb ON mb.clave = bm.Id_MotivoBaja
        WHERE 
        t.clave = 1 and
        bm.Id_SituacionPatrimonial = ?
    ", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "bienMueble" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $bienMueble = [
                "tipoOperacion" => "AGREGAR",
                "titular" => [
                    [
                        "titularBien" => [
                            [
                                "clave" => $this->nullConvert($dep->{'clave_titular'}),
                                "valor" => $this->nullConvert($dep->{'valor_titular'}),
                            ]
                        ]
                    ]

                ],
                "tipoBien" => [
                    "clave" => $this->nullConvert($dep->{'clave_bien'}),
                    "valor" => $this->nullConvert($dep->{'valor_bien'}),
                ],
                "transmisor" => [
                    [
                        "tipoPersona" => $this->nullConvert($dep->{'transmisor_tipo_persona'}),
                        // "nombreRazonSocial" => $this->nullConvert($dep->{'TR_NombreRazonSocial'}),
                        ...$this->conditionalField(
                            $dep->{'transmisor_tipo_persona'} == 'MORAL',
                            'nombreRazonSocial',
                            $this->nullConvert($dep->{'TR_NombreRazonSocial'})
                        ),
                        ...$this->conditionalField(
                            $dep->{'transmisor_tipo_persona'} == 'MORAL',
                            'rfc',
                            $this->nullConvert($dep->{'TR_Rfc'})
                        ),
                        // "rfc" => $this->nullConvert($dep->{'TR_Rfc'}),
                        // "relacion" => [
                        //     "parentescoRelacion" => [
                        //         "clave" => "OTRO",
                        //         "valor" => "OTRO",
                        //     ]
                        // ],
                    ]
                ],
                "tercero" => [
                    "tipoPersona" => $this->nullConvert($dep->{'tercero_tipo_persona'}),
                    "nombreRazonSocial" => $this->nullConvert($dep->{'T_NombreRazonSocial'}),
                    "rfc" => $this->nullConvert($dep->{'T_Rfc'}),


                ],
                "descripcionGeneralBien" => $this->nullConvert($dep->{'DescripcionGeneralBien'}),
                "formaAdquisicion" => [
                    "clave" => $this->nullConvert($dep->{'clave_formadquiscion'}),
                    "valor" => $this->nullConvert($dep->{'valor_formadquiscion'}),

                ],
                "formaPago" => $this->nullConvert($dep->{'valor_formapago'}),
                "valorAdquisicion" => [
                    "valor" => $this->nullConvert($dep->{'ValorAdquisicion'}, 'number'),
                    "moneda" => "MXN",

                ],
                "fechaAdquisicion" => $this->nullConvert($dep->{'FechaAdquisicion'}),
                "motivoBaja" => [
                    "clave" => $this->nullConvert($dep->{'clave_motivo_baja'}),
                    "valor" => $this->nullConvert($dep->{'valor_motivo_baja'}),

                ],
            ];

            $resultado['bienMueble'][] = $bienMueble;
        }
        return $resultado;
    }
    protected function generarSeccionInversiones($id)
    {
        $declaracion = DB::select("
        SELECT 
        ti.valor AS 'valor_tipoinversion',
        ti.abreviatura AS 'clave_tipoinversion',
        sti.valor AS 'valor_subtipoinversion',
        sti.abreviatura AS 'clave_subtipoinversion',
        t.valor AS 'titular_valor',
        t.abreviatura AS 'clave_titular', 
        CASE
            WHEN icv.T_Id_TipoPersona IN (0, 1) THEN 'FISICA'
            WHEN icv.T_Id_TipoPersona = 2 THEN 'MORAL'
        END AS tercero_tipo_persona,
        icv.T_NombreRazonSocial,
        icv.T_Rfc,
        icv.NumeroCuentaContrato,
        icv.SaldoSituacionActual,
        icv.Aclaraciones,
        p.code,
        icv.InstitucionRazonSocial,
        icv.RfcInstitucion
    FROM 
        DECL_InversionesCuentasValores AS icv
    LEFT JOIN 
        TipoInversion AS ti ON ti.clave = icv.Id_TipoInversion
    LEFT JOIN 
        SubTipoInversion AS sti ON sti.clave = icv.Id_SubtipoInversion
    LEFT JOIN 
        Titular AS t ON t.clave = icv.Id_Titular
    left join 
        Pais as p on p.Clave = icv.Id_Pais
    where
    t.clave = 1 and
    icv.Id_SituacionPatrimonial =?
        ;", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "inversion" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $inversion = [
                "tipoOperacion" => "AGREGAR",
                "tipoInversion" => [
                    "clave" => $this->nullConvert($dep->{'clave_tipoinversion'}),
                    "valor" => $this->nullConvert($dep->{'valor_tipoinversion'}),

                ],
                "subTipoInversion" => [
                    "clave" => $this->nullConvert($dep->{'clave_subtipoinversion'}),
                    "valor" => $this->nullConvert($dep->{'valor_subtipoinversion'}),

                ],
                "titular" => [
                    ["titularBien" => [
                        "clave" => $this->nullConvert($dep->{'clave_titular'}),
                        "valor" => $this->nullConvert($dep->{'titular_valor'}),
                    ]]


                ],
                "tercero" => [
                    [
                        "tipoPersona" => $this->nullConvert($dep->{'tercero_tipo_persona'}),
                        "nombreRazonSocial" => $this->nullConvert($dep->{'T_NombreRazonSocial'}),
                        "rfc" => $this->nullConvert($dep->{'T_Rfc'}),

                    ]
                ],
                // "numeroCuentaContrato" => $this->nullConvert($dep->{'NumeroCuentaContrato'}),
                "localizacionInversion" => [

                    "pais" => $this->nullConvert($dep->{'code'}),
                    "institucionRazonSocial" => $this->nullConvert($dep->{'InstitucionRazonSocial'}),
                    "rfc" => $this->nullConvert($dep->{'RfcInstitucion'}),


                ],
                // "saldoSituacionActual" => [

                //     "valor" => $this->nullConvert($dep->{'SaldoSituacionActual'}),
                //     "moneda" => "MXN",


                // ],

            ];
            $resultado['inversion'][] = $inversion;
        }
        return $resultado;
    }
    protected function generarSeccionAdeudos($id)
    {
        $declaracion = DB::select("
        select 
        t.valor as valor_titular,
        t.abreviatura clave_titular,
        ta.valor valor_adeudo,
        ta.abreviatura clave_adeudo,
        adp.NumeroCuentaContrato,
        adp.FechaAdquisicion,
        adp.Monto,
        adp.SaldoInsolutoSituacionActual,
           CASE
                    WHEN adp.T_id_TipoPersona = 1 or adp.T_id_TipoPersona = 0 THEN 'FISICA'
                    WHEN adp.T_id_TipoPersona = 2 THEN 'MORAL'
                END AS 'tercero tipo_persona',adp.T_NombreRazonSocial,adp.T_Rfc,
                   CASE
                    WHEN adp.OC_Id_TipoPersona = 1 or adp.OC_Id_TipoPersona = 0 THEN 'FISICA'
                    WHEN adp.OC_Id_TipoPersona = 2 THEN 'MORAL'
                END AS 'otorgante tipo_persona',
                adp.OC_NombreRazonSocial,adp.OC_Rfc,
                    IIF(p.Code IS NULL, 'MX',p.Code) AS code,
                    adp.Aclaraciones
        
                
                
        from DECL_AdeudosPasivos AS adp
        left join Titular as t on t.clave = adp.Id_Titular
        left join TipoAdeudo as ta on ta.clave = adp.Id_TipoAdeudo
        left join pais as p on p.Clave = adp.Id_Pais
        where 
        t.clave = 1 and
        adp.Id_SituacionPatrimonial = ?
        ;", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "adeudo" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $adeudo = [
                "tipoOperacion" => "AGREGAR",
                "titular" => [
                    [
                        "titularBien" => [
                            [
                                "clave" => $this->nullConvert($dep->{'clave_titular'}),
                                "valor" => $this->nullConvert($dep->{'valor_titular'}),
                            ]

                        ]
                    ]
                ],
                "tipoAdeudo" => [
                    "clave" => $this->nullConvert($dep->{'clave_adeudo'}),
                    "valor" => $this->nullConvert($dep->{'valor_adeudo'}),
                ],
                // "numeroCuentaContrato" => $this->nullConvert($dep->{'NumeroCuentaContrato'}),
                "fechaAdquisicion" => $this->nullConvert($dep->{'FechaAdquisicion'}),
                "montoOriginal" => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'Monto'}, 'number'),
                        "moneda" => "MXN",
                    ]
                ],
                "saldoInsolutoSituacionActual" => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'SaldoInsolutoSituacionActual'}, 'number'),
                        "moneda" => "MXN",
                    ]
                ],
                "tercero" => [
                    [
                        "tipoPersona" => $this->nullConvert($dep->{'tercero tipo_persona'}),
                        "nombreRazonSocial" => $this->nullConvert($dep->{'T_NombreRazonSocial'}),
                        "rfc" => $this->nullConvert($dep->{'T_Rfc'}),
                    ]

                ],
                "otorganteCredito" => [
                    "tipoPersona" => $this->nullConvert($dep->{'otorgante tipo_persona'}),
                    ...$this->conditionalField(
                        $dep->{'otorgante tipo_persona'} == 'MORAL',
                        'nombreInstitucion',
                        $this->nullConvert($dep->{'OC_NombreRazonSocial'})
                    ),
                    // "nombreInstitucion" => $this->nullConvert($dep->{'OC_NombreRazonSocial'}),
                    // "rfc" => $this->nullConvert($dep->{'OC_Rfc'}),
                    ...$this->conditionalField(
                        $dep->{'otorgante tipo_persona'} == 'MORAL',
                        'rfc',
                        $this->nullConvert($dep->{'OC_Rfc'})
                    ),
                ],
                "localizacionAdeudo" =>
                [
                    "pais" => $this->nullConvert($dep->{'code'}),
                ],
            ];
            $resultado['adeudo'][] = $adeudo;
        }
        return $resultado;
    }
    protected function generarSeccionPrestamosComodatos($id)
    {
        $declaracion = DB::select("
        select ti.abreviatura as clave_tipoinmueble,
        ti.valor as valor_tipoinmueble,
        dp.Calle,dp.NumeroExterior,
        dp.NumeroInterior,
        dp.ColoniaLocalidad,
        dp.CodigoPostal,
        m4Municio.clave_geologica as 'clave_municipioAlcaldia',
        m4Municio.Municipio as 'valor_municipioAlcaldia',
        RIGHT('0' + CAST(m4Estado.Clave AS VARCHAR(2)), 2) AS 'clave_entidadFederativa',
        m4Estado.Estado as 'valor_entidadFederativa',
        p.Code,
        tv.valor as 'valor_vehiculo',
        tv.abreviatura as clave_vehiculo,
        dp.Marca,
        dp.Modelo,
        dp.Anio,
        dp.CiudadLocalidad,
        dp.EstadoProvincia,
        dp.NumeroSerieRegistro,
        pvh.Code as vehiculo_code,
         CASE
                    WHEN dp.Id_TipoDuenoTitular = 1 or dp.Id_TipoDuenoTitular = 0 THEN 'FISICA'
                    WHEN dp.Id_TipoDuenoTitular = 2 THEN 'MORAL'
                END AS 'tipoDuenoTitular',
                dp.NombreTitular as 'Nombret',
                dp.RfcTitular,
                pr.valor as 'relacion',
				  RIGHT('0' + CAST(evh.Clave AS VARCHAR(2)), 2) AS 'vehiculo_clave_entidadFederativa',
        evh.Estado as 'vehiculo_valor_entidadFederativa',
        dp.Aclaraciones
        
        from DECL_PrestamoComodato as dp
        inner join TipoInmueble as ti on ti.clave = dp.Id_TipoInmueble
        LEFT JOIN Municipio as m4Municio on m4Municio.Clave =dp.Id_MunicipioAlcaldia
        LEFT JOIN Estado as m4Estado on m4Estado.Clave =dp.Id_EntidadFederativa
        left join pais as p on p.Clave = dp.Id_Pais
        left join TipoVehiculo as tv on tv.clave = dp.Id_TipoVehiculo
        left join pais as pvh on pvh.Clave = dp.V_Id_Pais
        left join Estado as evh on evh.Clave = dp.V_Id_EntidadFederativa
        left join ParentescoRelacion as pr on pr.clave = dp.Id_Relacion
        where dp.Id_SituacionPatrimonial = ?
        
        ;", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "prestamo" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $prestamo = [
                "tipoOperacion" => "AGREGAR",
                "tipoBien" => [
                    "inmueble" => [
                        "tipoInmueble" => [
                            "clave" => $this->nullConvert($dep->{'clave_tipoinmueble'}),
                            "valor" => $this->nullConvert($dep->{'valor_tipoinmueble'}),

                        ],
                        // "domicilioMexico" => [
                        //     "calle" => $this->nullConvert($dep->{'Calle'}),
                        //     "numeroExterior" => $this->nullConvert($dep->{'NumeroExterior'}),
                        //     "numeroInterior" => $this->nullConvert($dep->{'NumeroInterior'}),
                        //     "coloniaLocalidad" => $this->nullConvert($dep->{'ColoniaLocalidad'}),
                        //     "municipioAlcaldia" => [
                        //         "clave" => $this->nullConvert($dep->{'clave_municipioAlcaldia'}),
                        //         "valor" => $this->nullConvert($dep->{'valor_municipioAlcaldia'}),

                        //     ],
                        //     "entidadFederativa" => [
                        //         "clave" => $this->nullConvert($dep->{'clave_entidadFederativa'}),
                        //         "valor" => $this->nullConvert($dep->{'valor_entidadFederativa'}),
                        //     ]
                        // ],
                        // "domicilioExtranjero" => [
                        //     "calle" => $this->nullConvert($dep->{'Calle'}),
                        //     "numeroExterior" => $this->nullConvert($dep->{'NumeroExterior'}),
                        //     "numeroInterior" => $this->nullConvert($dep->{'NumeroInterior'}),
                        //     "ciudadLocalidad" => $this->nullConvert($dep->{'CiudadLocalidad'}),
                        //     "estadoProvincia" => $this->nullConvert($dep->{'EstadoProvincia'}),
                        //     "pais" => $this->nullConvert($dep->{'Code'}),
                        //     "codigoPostal" => $this->nullConvert($dep->{'CodigoPostal'}),

                        // ],

                    ],
                    "vehiculo" => [
                        "tipo" => [
                            "tipoVehiculo" => [
                                "clave" => $this->nullConvert($dep->{'clave_vehiculo'}),
                                "valor" => $this->nullConvert($dep->{'valor_vehiculo'}),

                            ]
                        ],
                        "marca" => $this->nullConvert($dep->{'Marca'}),
                        "modelo" => $this->nullConvert($dep->{'Modelo'}),
                        "anio" => $this->nullConvert($dep->{'Anio'}),
                        // "numeroSerieRegistro" => $this->nullConvert($dep->{'NumeroSerieRegistro'}),
                        // "lugarRegistro" => [
                        //     "pais" => $this->nullConvert($dep->{'vehiculo_code'}),
                        //     "entidadFederativa" => [
                        //         "clave" => $this->nullConvert($dep->{'vehiculo_clave_entidadFederativa'}),
                        //         "valor" => $this->nullConvert($dep->{'vehiculo_valor_entidadFederativa'}),

                        //     ]
                        // ],
                    ],

                ],
                "duenoTitular" => [
                    "tipoDuenoTitular" => $this->nullConvert($dep->{'tipoDuenoTitular'}),
                    // "nombreTitular" =>  $this->nullConvert($dep->{'Nombret'}),
                    ...$this->conditionalField(
                        $dep->{'tipoDuenoTitular'} == 'MORAL',
                        'nombreTitular',
                        $this->nullConvert($dep->{'Nombret'})
                    ),
                    ...$this->conditionalField(
                        $dep->{'tipoDuenoTitular'} == 'MORAL',
                        'rfc',
                        $this->nullConvert($dep->{'RfcTitular'})
                    ),
                    // "rfc" => $this->nullConvert($dep->{'RfcTitular'}),
                    ...$this->conditionalField(
                        $dep->{'tipoDuenoTitular'} == 'MORAL',
                        'relacionConTitular',
                        $this->nullConvert($dep->{'relacion'})
                    ),
                    // "relacionConTitular" => $this->nullConvert($dep->{'relacion'}),

                ]
            ];
            $resultado['prestamo'][] = $prestamo;
        }
        return $resultado;
    }
    protected function generarSeccionParticipacion($id)
    {
        $interes = DB::select("
        select rc.valor as 'relacion',p.NombreEmpresaSociedadAsociacion,p.RfcEmpresa,p.PorcentajeParticipacion,p.RecibeRemuneracion,p.MontoMensual,
        tp.valor as 'valor_tipoparticipacion',tp.abreviatura as 'clave_tipoparticipacion',
        RIGHT('0' + CAST(e.Clave AS VARCHAR(2)), 2) AS 'clave_entidadFederativa',
            e.Estado as 'valor_entidadFederativa',
			s.valor as 'valor_sector',
			s.Abreviatura as 'clave_sector',
            p.Aclaraciones

        from DECL_Participacion  as p
        LEFT join TipoRelacion as rc on rc.clave = p.Id_TipoRelacion
        LEFT JOIN Estado as e on e.Clave = p.Id_EntidadFederativa
        left join Sector as s on s.clave = p.Id_Sector
        left join TipoParticipacion as tp on tp.clave = p.Id_TipoParticipacion

        where rc.clave = 1  and Id_Intereses = ?
      ", [$id]);
        $resultado = [
            "ninguno" => empty($interes),
            "participacion" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($interes)) {
            return $resultado;
        }

        foreach ($interes as $dep) {
            // $resultado['aclaracionesObservaciones'] = $this->nullConvert($dep->{'Aclaraciones'});
            $participacion = [
                "tipoOperacion" => "AGREGAR",
                "tipoRelacion" => "DEPENDIENTE_ECONOMICO",
                "nombreEmpresaSociedadAsociacion" => $this->nullConvert($dep->{'NombreEmpresaSociedadAsociacion'}),
                "rfc" => $this->nullConvert($dep->{'RfcEmpresa'}),
                "porcentajeParticipacion" => $this->nullConvert($dep->{'PorcentajeParticipacion'}, 'integer'),
                "tipoParticipacion" => [
                    "clave" => $this->nullConvert($dep->{'clave_tipoparticipacion'}),
                    "valor" => $this->nullConvert($dep->{'valor_tipoparticipacion'}),

                ],
                "recibeRemuneracion" => $dep->{'RecibeRemuneracion'} == 0 ? false : true,
                "montoMensual" => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'MontoMensual'}),
                        "moneda" => "MXN",

                    ]
                ],
                "ubicacion" => [
                    "pais" => "MX",
                    "entidadFederativa" => [
                        "clave" => $this->nullConvert($dep->{'clave_entidadFederativa'}),
                        "valor" => $this->nullConvert($dep->{'valor_entidadFederativa'}),

                    ]
                ],
                "sector" => [
                    "clave" => $this->nullConvert($dep->{'clave_sector'}),
                    "valor" => $this->nullConvert($dep->{'valor_sector'}),

                ]
            ];
            $resultado['participacion'][] = $participacion;
        }

        return $resultado;
    }
    protected function generarSeccionParticipacionTomaDecisiones($id)
    {
        $interes = DB::select("
        select rc.valor as 'relacion',
        ts.valor as 'valor_tipoinstitucion',
        ts.abreviatura as 'clave_tipoinstitucion',
        p.NombreInstitucion,
        p.RfcInstitucion,
        p.PuestoRol,
        p.FechaInicioParticipacion,
        p.RecibeRemuneracion,
        p.MontoMensual,
        p.Aclaraciones,

         RIGHT('0' + CAST(e.Clave AS VARCHAR(2)), 2) AS 'clave_entidadFederativa',
                    e.Estado as 'valor_entidadFederativa'
                    
        from DECL_ParticipacionTomaDecisiones  as p
        inner join TipoRelacion as rc on rc.clave = p.Id_TipoRelacion
         LEFT JOIN Estado as e on e.Clave = p.Id_EntidadFederativa
         LEFT JOIN TipoInstitucion as ts on ts.clave = p.Id_TipoInstitucion
        where rc.clave = 1 and Id_Intereses = ?

      ", [$id]);
        $resultado = [
            "ninguno" => empty($interes),
            "participacion" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($interes)) {
            return $resultado;
        }
        foreach ($interes as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $participacion = [
                "tipoOperacion" => "AGREGAR",
                "tipoRelacion" => "DEPENDIENTE_ECONOMICO",
                "tipoInstitucion" => [
                    "clave" => $this->nullConvert($dep->{'clave_tipoinstitucion'}),
                    "valor" => $this->nullConvert($dep->{'valor_tipoinstitucion'}),

                ],
                // "nombreInstitucion" => $dep->{'NombreInstitucion'},
                // "rfc" => $dep->{'RfcInstitucion'},
                "puestoRol" => $this->nullConvert($dep->{'PuestoRol'}),
                "fechaInicioParticipacion" => $this->nullConvert($dep->{'FechaInicioParticipacion'}),
                "recibeRemuneracion" => $dep->{'RecibeRemuneracion'} == 0 ? false : true,
                "montoMensual" => [
                    "valor" => $this->nullConvert($dep->{'MontoMensual'}, 'number'),
                    "moneda" => "MXN",

                ],

                "ubicacion" => [
                    "pais" => "MX",
                    "entidadFederativa" => [
                        "clave" => $this->nullConvert($dep->{'clave_entidadFederativa'}),
                        "valor" => $this->nullConvert($dep->{'valor_entidadFederativa'}),

                    ]
                ],
            ];
            $resultado['participacion'][] = $participacion;
        }
        return $resultado;
    }
    protected function generarSeccionApoyos($id)
    {
        $interes = DB::select("
        select 
        pr.valor as 'valor_beneficiarioprograma',
        pr.abreviatura as 'clave_beneficiarioprograma',
        p.NombrePrograma,
        p.InstitucionOtorgante,
        ng.valor as 'nivelOrdenGobierno',
        ta.valor as 'valor_tipoapoyo',
        ta.abreviatura as 'clave_tipoapoyo',
        fr.valor as 'formaRecepcion',
        p.MontoApoyoMensual,
        p.EspecifiqueApoyo,
        p.Aclaraciones
        
        from DECL_Apoyos  as p
        inner join ParentescoRelacion as pr on pr.clave = p.Id_BeneficiarioPrograma
        left join NivelOrdenGobierno as ng on ng.clave = p.Id_NivelOrdenGobierno
        left join TipoApoyo as ta on ta.clave = p.Id_TipoApoyo
        left join FormaRecepcion as fr on fr.clave = p.Id_FormaRecepcion
        where Id_Intereses  = ?

      ", [$id]);
        $resultado = [
            "ninguno" => empty($interes),
            "apoyo" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($interes)) {
            return $resultado;
        }
        foreach ($interes as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $participacion = [
                "tipoOperacion" => "AGREGAR",
                "tipoPersona" => "FISICA",
                // ...$this->conditionalField(
                //     $dep->{'tipoPersona'} == 'MORAL',
                //     'beneficiarioPrograma',
                //     [
                //         "clave" => $this->nullConvert($dep->{'clave_beneficiarioprograma'}),
                //         "valor" => $this->nullConvert($dep->{'valor_beneficiarioprograma'}),

                //     ]
                // ),
                // "beneficiarioPrograma" => [
                //     "clave" => $this->nullConvert($dep->{'clave_beneficiarioprograma'}),
                //     "valor" => $this->nullConvert($dep->{'valor_beneficiarioprograma'}),

                // ],
                "nombrePrograma" => $this->nullConvert($dep->{'NombrePrograma'}),
                "institucionOtorgante" => $this->nullConvert($dep->{'InstitucionOtorgante'}),
                "nivelOrdenGobierno" => $this->nullConvert($dep->{'nivelOrdenGobierno'}),
                "tipoApoyo" => [
                    "clave" => $this->nullConvert($dep->{'clave_tipoapoyo'}),
                    "valor" => $this->nullConvert($dep->{'valor_tipoapoyo'}),

                ],
                "formaRecepcion" => $this->nullConvert($dep->{'formaRecepcion'}),
                "montoApoyoMensual" => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'MontoApoyoMensual'}, 'number'),
                        "moneda" => "MXN",
                    ]

                ],
                "especifiqueApoyo" => $this->nullConvert($dep->{'EspecifiqueApoyo'}),


            ];
            $resultado['participacion'][] = $participacion;
        }
        return $resultado;
    }
    protected function generarSeccionRepresentaciones($id)
    {
        $interes = DB::select("
        select 
        tr.valor as 'tipoRepresentacion',
		
        r.FechaInicioRepresentacion,
        r.NombreRazonSocial,
        r.Rfc,
		CASE
                    WHEN r.Id_TipoPersona = 1 or r.Id_TipoPersona = 0 THEN 'FISICA'
                    WHEN r.Id_TipoPersona = 2 THEN 'MORAL'
                END AS 'tipopersona',
        r.RecibeRemuneracion,
        r.MontoMensual,
        s.valor as 'valor_sector',
        s.Abreviatura as 'clave_sector',
        r.Aclaraciones
        from DECL_Representaciones  as r
        inner join TipoRepresentacion as tr on tr.clave = r.Id_Representaciones
        inner join Sector as s on s.clave = r.Id_Sector
		where r.Id_TipoRelacion = 1
        and Id_Intereses = ?

      ", [$id]);
        $resultado = [
            "ninguno" => empty($interes),
            "representacion" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($interes)) {
            return $resultado;
        }
        foreach ($interes as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $participacion = [
                "tipoOperacion" => "AGREGAR",
                "tipoRelacion" => "DEPENDIENTE_ECONOMICO ",
                "tipoRepresentacion" => $dep->{'tipoRepresentacion'},
                "fechaInicioRepresentacion" => $dep->{'FechaInicioRepresentacion'},
                "tipoPersona" => $dep->{'tipopersona'},
                ...$this->conditionalField(
                    $dep->{'tipopersona'} == 'MORAL',
                    'nombreRazonSocial',
                    $this->nullConvert($dep->{'NombreRazonSocial'})
                ),
                ...$this->conditionalField(
                    $dep->{'tipopersona'} == 'MORAL',
                    'rfc',
                    $this->nullConvert($dep->{'Rfc'})
                ),
                // "nombreRazonSocial" => $dep->{'NombreRazonSocial'},
                // "rfc" => $dep->{'Rfc'},
                "recibeRemuneracion" => $dep->{'RecibeRemuneracion'} == 0 ? false : true,
                "montoMensual" => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'MontoMensual'}, 'number'),
                        "moneda" => "MXN",
                    ]

                ],
                "ubicacion" => [
                    "pais" => "MX",
                    "entidadFederativa" => [
                        "clave" => $this->nullConvert($dep->{'clave_entidadFederativa'}),
                        "valor" => $this->nullConvert($dep->{'valor_entidadFederativa'}),

                    ]
                ],
                "sector" => [
                    "clave" => $this->nullConvert($dep->{'clave_sector'}),
                    "valor" =>  $this->nullConvert($dep->{'valor_sector'}),
                ]


            ];
            $resultado['participacion'][] = $participacion;
        }
        return $resultado;
    }
    protected function generarSeccionClientesPrincipales($id)
    {
        $interes = DB::select("
        select 
        c.RealizaActividadLucrativa,
        c.NombreEmpresa,
        c.RfcEmpresa,
        c.Id_TipoPersona,
        CASE
                    WHEN c.Id_TipoPersona = 1 or c.Id_TipoPersona = 0 THEN 'FISICA'
                    WHEN c.Id_TipoPersona = 2 THEN 'MORAL'
                END AS 'tipo_persona',
        c.NombreRazonSocial,
        c.RfcCliente,
        s.valor as 'sector_valor',
        s.Abreviatura as 'sector_abreviatura',
        c.MontoAproximadoGanancia,
         RIGHT('0' + CAST(e.Clave AS VARCHAR(2)), 2) AS clave_entidadFederativa,
                    e.Estado AS valor_entidadFederativa,
        c.Aclaraciones
        
        from DECL_ClientesPrincipales  as c
        inner join Sector as s on s.clave = c.Id_Sector
        inner JOIN Estado e ON e.Clave = c.Id_EntidadFederativa
        where 
		c.Id_TipoRelacion = 1 
        and Id_Intereses = ?

      ", [$id]);
        $resultado = [
            "ninguno" => empty($interes),
            "cliente" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($interes)) {
            return $resultado;
        }
        foreach ($interes as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $participacion = [
                "tipoOperacion" => "AGREGAR",
                "realizaActividadLucrativa" => $dep->{'RealizaActividadLucrativa'} == 0 ? false : true,
                "tipoRelacion" => "DEPENDIENTE_ECONOMICO",
                "empresa" => [
                    "nombreEmpresaServicio" => $dep->{'NombreEmpresa'},
                    "rfc" => $dep->{'RfcEmpresa'},
                ],
                "clientePrincipal" => [
                    "tipoPersona" => $dep->{'tipo_persona'},
                    ...$this->conditionalField(
                        $dep->{'tipo_persona'} == 'MORAL',
                        'nombreRazonSocial',
                        $this->nullConvert($dep->{'NombreRazonSocial'})
                    ),
                    ...$this->conditionalField(
                        $dep->{'tipo_persona'} == 'MORAL',
                        'rfc',
                        $this->nullConvert($dep->{'RfcCliente'})
                    ),
                    // "nombreRazonSocial" => $dep->{'NombreRazonSocial'},
                    // "rfc" => $dep->{'RfcCliente'},

                ],
                "sector" => [
                    "clave" => $this->nullConvert($dep->{'sector_abreviatura'}),
                    "valor" => $this->nullConvert($dep->{'sector_valor'}),
                ],
                "montoAproximadoGanancia" => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'MontoAproximadoGanancia'}, 'number'),
                        "moneda" => "MXN",

                    ]
                ],
                "ubicacion" => [
                    "pais" => "MX",
                    "entidadFederativa" => [
                        "clave" => $this->nullConvert($dep->{'clave_entidadFederativa'}),
                        "valor" => $this->nullConvert($dep->{'valor_entidadFederativa'}),

                    ]
                ],
            ];
            $resultado['participacion'][] = $participacion;
        }
        return $resultado;
    }
    protected function generarSeccionBeneficiosPrivados($id)
    {
        $interes = DB::select("
        select 
        CASE
          WHEN bp.Id_TipoPersona = 1 or bp.Id_TipoPersona = 0 THEN 'FISICA'
          WHEN bp.Id_TipoPersona = 2 THEN 'MORAL'
        END AS 'tipo_persona',
        tb.valor as 'valor_tipobeneficio',
        tb.nombre as 'clave_tipobeneficio',
        bep.valor as 'valor_beneficiarioprograma',
        bep.nombre as 'clave_beneficiarioprograma',
        bp.NombreRazonSocial,
        bp.RfcCliente,
        fr.valor as 'formaRecepcion',
        bp.EspecifiqueBeneficio,
        bp.MontoMensualAproximado,
        s.valor as 'valor_sector',
        s.Abreviatura as 'clave_sector',
        bp.Aclaraciones
     from DECL_BeneficiosPrivados  as bp
     inner join TipoBeneficio as tb on tb.clave = bp.Id_TipoBeneficio
     inner join BeneficiariosPrograma as bep on bep.clave  = bp.Id_BeneficiarioPrograma
     inner join FormaRecepcion as fr on fr.clave = bp.Id_FormaRecepcion
     inner join Sector as s on s.clave = bp.Id_Sector
     where Id_Intereses  = ?

      ", [$id]);
        $resultado = [
            "ninguno" => empty($interes),
            "beneficio" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($interes)) {
            return $resultado;
        }
        foreach ($interes as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $beneficio = [
                "tipoOperacion" => "AGREGAR",
                "tipoPersona" => $this->nullConvert($dep->{'tipo_persona'}),
                "tipoBeneficio" => [
                    "clave" => $this->nullConvert($dep->{'clave_tipobeneficio'}),
                    "valor" => $this->nullConvert($dep->{'valor_tipobeneficio'}),

                ],
                ...$this->conditionalField(
                    $dep->{'tipo_persona'} == 'MORAL',
                    'beneficiario',
                    [
                        "beneficiariosPrograma" => [
                            "clave" => $this->nullConvert($dep->{'clave_beneficiarioprograma'}),
                            "valor" => $this->nullConvert($dep->{'valor_beneficiarioprograma'}),

                        ]
                    ],
                ),
                // "beneficiario" => [
                //     [
                //         "beneficiariosPrograma" => [
                //             "clave" => $dep->{'clave_beneficiarioprograma'},
                //             "valor" => $dep->{'valor_beneficiarioprograma'},

                //         ]
                //     ],
                // ],
                "otorgante" => [
                    "tipoPersona" => $dep->{'tipo_persona'},
                    ...$this->conditionalField(
                        $dep->{'tipo_persona'} == 'MORAL',
                        'nombreRazonSocial',
                        $this->nullConvert($dep->{'NombreRazonSocial'})
                    ),
                    // "nombreRazonSocial" => $dep->{'NombreRazonSocial'},
                    ...$this->conditionalField(
                        $dep->{'tipo_persona'} == 'MORAL',
                        'rfc',
                        $this->nullConvert($dep->{'RfcCliente'})
                    ),
                    // "rfc" => $dep->{'RfcCliente'},

                ],
                "formaRecepcion" => $dep->{'formaRecepcion'},
                "especifiqueBeneficio" => $dep->{'EspecifiqueBeneficio'},
                "montoMensualAproximado" => [
                    "monto" => [
                        "valor" => $this->nullConvert($dep->{'MontoMensualAproximado'}, 'number'),
                        "moneda" => "MXN",

                    ]
                ],
                "sector" => [
                    "clave" => $this->nullConvert($dep->{'clave_sector'}),
                    "valor" => $this->nullConvert($dep->{'valor_sector'}),

                ]
            ];

            $resultado['beneficio'][] = $beneficio;
        }
        return $resultado;
    }
    protected function generarSeccionFideocomisos($id)
    {
        $interes = DB::select("
        select  
        pr.valor as 'relacion',
        tf.valor as 'fideocomiso',
        tp.valor as 'tipoparticipacion',
        fc.RfcFideicomiso,
            CASE
                    WHEN fc.Id_TipoPersonaFideicomitente = 1 or fc.Id_TipoPersonaFideicomitente = 0 THEN 'FISICA'
                    WHEN fc.Id_TipoPersonaFideicomitente = 2 THEN 'MORAL'
                END AS 'tipoPersona fideicomitente',
        fc.NombreRazonSocialFideicomitente,
        fc.RfcFideicomitente,
        fc.NombreRazonSocialFideicomisario,
        fc.RfcFideicomisario,
         CASE
                    WHEN fc.Id_TipoPersonaFideicomisario = 1 or fc.Id_TipoPersonaFideicomisario = 0 THEN 'FISICA'
                    WHEN fc.Id_TipoPersonaFideicomisario = 2 THEN 'MORAL'
                END AS 'tipoPersona fideicomisario',
        fc.NombreRazonSocialFideicomisario,
        fc.RfcFideicomisario,
        s.valor 'valor_sector',
        s.Abreviatura 'clave_sector',
        IIF(fc.EsEnMexico=1,'MX','EX') 'extranjero',
        fc.Aclaraciones
        from DECL_Fideicomisos  as fc
        inner join ParentescoRelacion as pr on pr.clave = fc.Id_TipoRelacion
        inner join TipoFideicomiso as tf on tf.clave = fc.Id_TipoFideicomiso
        inner join TipoParticipacion as tp on tp.clave = fc.Id_TipoParticipacion
        inner join Sector as s on s.clave = fc.Id_Sector
        
             where pr.valor is null and Id_Intereses  = ?

      ", [$id]);
        $resultado = [
            "ninguno" => empty($interes),
            "fideicomiso" => [],
            // "aclaracionesObservaciones" => ""
        ];

        if (empty($interes)) {
            return $resultado;
        }
        foreach ($interes as $dep) {
            // $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $fideicomiso = [
                "tipoOperacion" => "AGREGAR",
                "tipoRelacion" => "DEPENDIENTE_ECONOMICO",
                "tipoFideicomiso" => $this->nullConvert($dep->{'fideocomiso'}),
                "tipoParticipacion" => $this->nullConvert($dep->{'tipoparticipacion'}),
                "rfcFideicomiso" => $this->nullConvert($dep->{'RfcFideicomiso'}),
                "fideicomitente" => [
                    "tipoPersona" => $this->nullConvert($dep->{'tipoPersona fideicomitente'}),
                    "nombreRazonSocial" => $this->nullConvert($dep->{'NombreRazonSocialFideicomitente'}),
                    "rfc" => $this->nullConvert($dep->{'RfcFideicomitente'}),

                ],
                "fiduciario" => [
                    "nombreRazonSocial" => $this->nullConvert($dep->{'NombreRazonSocialFideicomisario'}),
                    "rfc" => $this->nullConvert($dep->{'RfcFideicomisario'}),
                ],
                "fideicomisario" => [
                    "tipoPersona" => $this->nullConvert($dep->{'tipoPersona fideicomisario'}),
                    "nombreRazonSocial" => $this->nullConvert($dep->{'NombreRazonSocialFideicomisario'}),
                    "rfc" => $this->nullConvert($dep->{'RfcFideicomisario'}),
                ],
                "sector" => [
                    "clave" => $this->nullConvert($dep->{'clave_sector'}),
                    "valor" => $this->nullConvert($dep->{'valor_sector'}),

                ],
                "extranjero" => $this->nullConvert($dep->{'extranjero'}),
            ];

            $resultado['fideicomiso'][] = $fideicomiso;
        }
        return $resultado;
    }
    protected function generarNombreArchivo($declaracion)
    {
        return "declaracion_" . $declaracion->{"Id_SituacionPatrimonial"} . $declaracion->{"h1 nombre"} . ".json";
    }
}
