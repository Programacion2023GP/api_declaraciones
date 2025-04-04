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
        set_time_limit(12000); // 300 segundos = 5 minutos
        ini_set('memory_limit', '1024M');
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
			LEFT JOIN Municipio as m4Municio on m4Municio.Clave =h4.Id_MunicipioAlcaldia
			LEFT JOIN Estado as m4Estado on m4Estado.Clave =h4.Id_EntidadFederativa

            LEFT JOIN Pais as h4Pais ON h4Pais.Clave = h4.Id_Pais
        ),
        FilteredDatosEmpleo AS (
            SELECT * FROM DatosEmpleo WHERE rn_empleo = 1
        )
        
        SELECT 
            Declaracion.Id_SituacionPatrimonial,
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
        ORDER BY Declaracion.Id_SituacionPatrimonial DESC;
        ");
    }

    protected function generarEstructuraJson($declaracion)
    {
        $situacionPatrimonial = [
            "datosGenerales" => $this->generarSeccionDatosGenerales($declaracion),
            "domicilioDeclarante" => $this->generarSeccionDomicilio($declaracion),
            "datosCurricularesDeclarante" => $this->generarSeccionDatosCurriculares($declaracion),
            "datosEmpleoCargoComision" => $this->generarSeccionDatosEmpleoCargoComision($declaracion),
            "experienciaLaboral" => $this->generarSeccionExperienciaLaboral($declaracion->{'Id_SituacionPatrimonial'}),
            "datosPareja" => $this->generarSeccionDatosPareja($declaracion->{'Id_SituacionPatrimonial'}),
            "datosDependienteEconomico" => $this->generarSeccionDatosDependientesEconomicos($declaracion->{'Id_SituacionPatrimonial'}),
            "ingresos" => $this->generarSeccionIngresos($declaracion->{'Id_SituacionPatrimonial'}),

        ];

        if ($declaracion->{'tipo'} != 'MODIFICACIÓN') {
            $situacionPatrimonial["actividadAnualAnterior"] = $this->generarSeccionActividadAnualAnterior($declaracion->{'Id_SituacionPatrimonial'});
        }
        $situacionPatrimonial["bienesInmuebles"] = $this->generarSeccionBienesInmuebles($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["vehiculos"] = $this->generarSeccionVehiculos($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["bienesMuebles"] = $this->generarSeccionBienesMuebles($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["inversionesCuentasValores"] = $this->generarSeccionInversiones($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["adeudos"] = $this->generarSeccionAdeudos($declaracion->{'Id_SituacionPatrimonial'});
        $situacionPatrimonial["prestamoOComodato"] = $this->generarSeccionPrestamosComodatos($declaracion->{'Id_SituacionPatrimonial'});
        return [
            "metaData" => $this->generarMetaData($declaracion),
            "declaracion" => [
                "situacionPatrimonial" => $situacionPatrimonial
            ],
            "intereses" => [
                "participacion" => [],
                "participacionTomaDecisiones" => [],
                "apoyos" => [],
                "representacion" => [],
                "clientesPrincipales" => [],
                "beneficiosPrivados" => [],
                "fideicomisos" => [],

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
                "municipioAlcaldia" => $declaracion->{"h2 Pais"} ? "" :  [
                    "clave" => $declaracion->{'h2 clave municipioAlcaldia'},
                    "valor" => $declaracion->{'h2 valor municipioAlcaldia'},

                ],
                "entidadFederativa" => $declaracion->{"h2 Pais"} ? "" :  [
                    "clave" => $declaracion->{'h2 clave entidadFederativa'},
                    "valor" => $declaracion->{'h2 valor entidadFederativa'},

                ],
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
                "municipioAlcaldia" => $declaracion->{"h4 pais"} ? "" :  [
                    "clave" => $declaracion->{'h4 clave municipioAlcaldia'},
                    "valor" => $declaracion->{'h4 valor municipioAlcaldia'},

                ],
                "entidadFederativa" => $declaracion->{"h2 Pais"} ? "" :  [
                    "clave" => $declaracion->{'h4 clave entidadFederativa'},
                    "valor" => $declaracion->{'h4 valor entidadFederativa'},

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
    protected function generarSeccionExperienciaLaboral($id)
    {
        $experienciasLaborales = DB::select("
            IF EXISTS (SELECT * FROM DECL_ExperienciaLaboral WHERE Id_SituacionPatrimonial = ?)
            BEGIN
                SELECT 
                    Ex.Id_AmbitoPublico,
                    Am.nombre AS ambitoSector_clave,
                    Am.valor AS ambitoSector_valor,
                    Nio.clave AS nivelOrdenGobierno, 
                    Ap.valor AS ambitoPublico,
                    Ex.nombreEntePublico,
                    Ex.areaAdscripcion,
                    Ex.empleoCargoComision,
                    Ex.funcionPrincipal,
                    Ex.NombreEmpresaSociedadAsociacion,
                    Ex.RFC,
                    Ex.Puesto,
                    S.Abreviatura AS sector_clave,
                    S.valor AS sector_valor,
                    CONVERT(VARCHAR(10), Ex.fechaIngreso, 120) AS fechaIngreso,
                    CONVERT(VARCHAR(10), Ex.FechaEngreso, 120) AS fechaEgreso,
                    IIF(Ex.FueEnMexico = 1, 'MX', 'EX') AS ubicacion,
                    Ex.Aclaraciones,
                    Ex.FueEnMexico
                FROM DECL_ExperienciaLaboral AS Ex 
                LEFT JOIN AmbitoSector AS Am ON Am.clave = Ex.Id_AmbitoSector
                LEFT JOIN NivelOrdenGobierno AS Nio ON Nio.clave = Ex.Id_NivelOrdenGobierno
                LEFT JOIN AmbitoPublico AS Ap ON Ap.clave = Ex.Id_AmbitoPublico
                LEFT JOIN Sector AS S ON S.clave = Ex.Id_Sector
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
                "aclaracionesObservaciones" => ""
            ];
        }

        // Procesar los resultados
        $experiencia = [];
        foreach ($experienciasLaborales as $item) {
            if ($item->Id_AmbitoPublico == 1) {
                // Experiencia en sector público
                $experiencia[] = [
                    "tipoOperacion" => 'AGREGAR',
                    "ambitoSector" => [
                        "clave" => $item->ambitoSector_clave ?? null,
                        "valor" => $item->ambitoSector_valor ?? null
                    ],
                    "nivelOrdenGobierno" => $item->nivelOrdenGobierno ?? null,
                    "ambitoPublico" => $item->ambitoPublico ?? null,
                    "nombreEntePublico" => $item->nombreEntePublico ?? null,
                    "areaAdscripcion" => $item->areaAdscripcion ?? null,
                    "empleoCargoComision" => $item->empleoCargoComision ?? null,
                    "funcionPrincipal" => $item->funcionPrincipal ?? null,
                    "fechaIngreso" => $item->fechaIngreso ?? null,
                    "fechaEgreso" => $item->fechaEgreso ?? null,
                    "ubicacion" => $item->ubicacion ?? null
                ];
            } else {
                // Experiencia en sector privado
                $experiencia[] = [
                    "tipoOperacion" => 'AGREGAR',
                    "ambitoSector" => [
                        "clave" => $item->ambitoSector_clave ?? null,
                        "valor" => $item->ambitoSector_valor ?? null
                    ],
                    "nombreEmpresaSociedadAsociacion" => $item->NombreEmpresaSociedadAsociacion ?? null,
                    "rfc" => $item->RFC ?? null,
                    "area" => $item->areaAdscripcion ?? null,
                    "puesto" => $item->Puesto ?? null,
                    "sector" => [
                        "clave" => $item->sector_clave ?? null,
                        "valor" => $item->sector_valor ?? null
                    ],
                    "ubicacion" => $item->ubicacion ?? null
                ];
            }
        }

        // Obtener aclaraciones (del primer registro)
        $aclaraciones = $experienciasLaborales[0]->Aclaraciones ?? '';

        return [
            "ninguno" => empty($experiencia),
            "experiencia" => $experiencia,
            "aclaracionesObservaciones" => $aclaraciones
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
                    "nombre" => $pareja->Nombre,
                    "primerApellido" => $pareja->PrimerApellido,
                    "segundoApellido" => $pareja->SegundoApellido,
                    "fechaNacimiento" => $pareja->FechaNacimiento,
                    "rfc" => $pareja->RfcPareja,
                    "relacionConDeclarante" => $pareja->relacionConDeclarante,
                    "ciudadanoExtranjero" => $pareja->EsCiudadanoExtranjero ? true : false,
                    "curp" => $pareja->Curp,
                    "esDependienteEconomico" => $pareja->EsDependienteEconomico ? true : false,
                    "habitaDomicilioDeclarante" => $pareja->HabitaDomicilioDeclarante ? true : false,
                    "lugarDondeReside" => $pareja->lugarDondeReside,
                    "domicilioMexico" => [
                        "calle" =>  $pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"Calle"} : "",
                        "numeroExterior" =>  $pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"NumeroExterior"} : "",
                        "numeroInterior" =>  $pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"NumeroInterior"} : "",
                        "coloniaLocalidad" =>  $pareja->{"lugarDondeReside"} == "MEXICO" ? $pareja->{"ColoniaLocalidad"} : "",
                        "municipioAlcaldia" => [
                            "clave" => $pareja->{'clave municipioAlcaldia'},
                            "valor" => $pareja->{'valor municipioAlcaldia'},

                        ],
                        "entidadFederativa" => [
                            "clave" => $pareja->{'clave entidadFederativa'},
                            "valor" => $pareja->{'valor entidadFederativa'},

                        ],
                        "codigoPostal" => $pareja->{"lugarDondeReside"} == "MEXICO" ? "" :  $pareja->{"CodigoPostal"},
                    ],
                    "domicilioExtranjero" => [
                        "calle" => $pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"Calle"} : "",
                        "numeroExterior" => $pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"NumeroExterior"} : "",
                        "numeroInterior" => $pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"NumeroInterior"} : "",
                        "ciudadLocalidad" => $pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"CiudadLocalidad"} : "",
                        "estadoProvincia" => $pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"EstadoProvincia"} : "",
                        "pais" => $pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"Pais"} : "",
                        "codigoPostal" => $pareja->{"lugarDondeReside"} == "EXTRANJERO" ? $pareja->{"CodigoPostal"} : "",
                    ],
                ];
        }
        if ($pareja->{'actividad valor'}) {
            $datosPareja["actividadLaboral"] = [
                "clave" => $pareja->{'actividad clave'},
                "valor" => $pareja->{'actividad valor'},
            ];
        }
        if ($pareja->{'actividad clave'} == 'PUB') {
            $datosPareja["actividadLaboralSectorPublico"] = [
                "nivelOrdenGobierno" => $pareja->nivelOrdenGobierno,
                "ambitoPublico" => $pareja->ambitoPublico,
                "nombreEntePublico" => $pareja->NombreEntePublico,
                "areaAdscripcion" => $pareja->AreaAdscripcion,
                "empleoCargoComision" => $pareja->EmpleoCargoComision,
                "funcionPrincipal" => $pareja->FuncionPrincipal,
                "salarioMensualNeto" => [
                    "monto" => [
                        "valor" =>  $pareja->{'monto valor'},
                        "moneda" =>  $pareja->Divisa,

                    ],
                    "fechaIngreso" => $pareja->FechaIngreso,
                ]
            ];
        }
        if ($pareja->{'actividad clave'} == 'PRI') {
            $datosPareja["actividadLaboralSectorPrivadoOtro"] = [
                "nombreEmpresaSociedadAsociacion" => $pareja->nombreEmpresaSociedadAsociacion,
                "empleoCargoComision" => $pareja->EmpleoCargoComision,
                "rfc" => $pareja->RfcEmpresa,
                "fechaIngreso" => $pareja->FechaIngreso,
                "sector" => [
                    "clave" => $pareja->{'sector clave'},
                    "valor" => $pareja->{'sector valor'},
                ],
                "salarioMensualNeto" =>
                [
                    "monto" => [
                        "valor" => $pareja->{'monto valor'},
                        "moneda" => $pareja->Divisa,

                    ]
                ],
                "proveedorContratistaGobierno" => false,


            ];
        }
        $datosPareja["aclaracionesObservaciones"] = $pareja->Aclaraciones;


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
            "aclaracionesObservaciones" => "",

        ];
        if (isset($dependientesEconomicos[0]->message)) {
            return $dependientes;
        }
        foreach ($dependientesEconomicos as $dep) {

            $dependiente = [
                "nombre" => $dep->Nombre,
                "primerApellido" => $dep->PrimerApellido,
                "segundoApellido" => $dep->SegundoApellido,
                "fechaNacimiento" => $dep->{'h3 FechaNacimiento'},
                "rfc" => $dep->RfcDependiente,
                "parentescoRelacion" => [
                    "clave" => $dep->{'clave parentescoRelacion'},
                    "valor" => $dep->{'valor parentescoRelacion'},
                ],
                "extranjero" => $dep->EsCiudadanoExtranjero ? true : false,
                "curp" => $dep->Curp,
                "habitaDomicilioDeclarante" => $dep->HabitaDomicilioDeclarante ? true : false,
                "lugarDondeReside" => $dep->lugarDondeReside,
                "domicilioMexico" => [
                    "calle" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"Calle"} : "",
                    "numeroExterior" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroExterior"} : "",
                    "numeroInterior" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroInterior"} : "",
                    "coloniaLocalidad" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"ColoniaLocalidad"} : "",
                    "municipioAlcaldia" => [
                        "clave" => $dep->{'clave municipioAlcaldia'},
                        "valor" => $dep->{'valor municipioAlcaldia'},

                    ],
                    "entidadFederativa" => [
                        "clave" => $dep->{'clave entidadFederativa'},
                        "valor" => $dep->{'valor entidadFederativa'},

                    ],
                    "codigoPostal" => $dep->{"lugarDondeReside"} == "MEXICO" ? "" :  $dep->{"CodigoPostal"},
                ],
                "domicilioExtranjero" => [
                    "calle" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Calle"} : "",
                    "numeroExterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroExterior"} : "",
                    "numeroInterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroInterior"} : "",
                    "ciudadLocalidad" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CiudadLocalidad"} : "",
                    "estadoProvincia" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"EstadoProvincia"} : "",
                    "pais" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Pais"} : "",
                    "codigoPostal" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CodigoPostal"} : "",
                ],


            ];
            if ($dep->{'actividad valor'}) {
                $dependiente["actividadLaboral"] = [
                    "clave" => $dep->{'actividad clave'},
                    "valor" => $dep->{'actividad valor'},
                ];
            }
            if ($dep->{'actividad clave'} == 'PUB') {
                $dependiente["actividadLaboralSectorPublico"] = [
                    "nivelOrdenGobierno" => $dep->nivelOrdenGobierno,
                    "ambitoPublico" => $dep->ambitoPublico,
                    "nombreEntePublico" => $dep->NombreEntePublico,
                    "areaAdscripcion" => $dep->AreaAdscripcion,
                    "empleoCargoComision" => $dep->EmpleoCargoComision,
                    "funcionPrincipal" => $dep->FuncionPrincipal,
                    "salarioMensualNeto" => [
                        "monto" => [
                            "valor" =>  $dep->{'monto valor'},
                            "moneda" =>  $dep->Divisa,

                        ],
                        "fechaIngreso" => $dep->FechaIngreso,
                    ]
                ];
            }
            if ($dep->{'actividad clave'} == 'PRI') {
                $dependiente["actividadLaboralSectorPrivadoOtro"] = [
                    "nombreEmpresaSociedadAsociacion" => $dep->nombreEmpresaSociedadAsociacion,
                    "empleoCargoComision" => $dep->EmpleoCargoComision,
                    "rfc" => $dep->RfcEmpresa,
                    "fechaIngreso" => $dep->FechaIngreso,
                    "proveedorContratistaGobierno" => false,
                    "sector" => [
                        "clave" => $dep->{'sector clave'},
                        "valor" => $dep->{'sector valor'},
                    ],
                    "salarioMensualNeto" =>
                    [
                        "monto" => [
                            "valor" => $dep->{'monto valor'},
                            "moneda" => $dep->Divisa,

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
                "valor" => $declaracion->{'h8 valor remuneracionMensualCargoPublico'},
                "moneda" => "MXN",
            ],
            "otrosIngresosMensualesTotal" => [
                "valor" => $declaracion->{'h8 valor otrosIngresosMensualesTotal'},
                "moneda" => "MXN",
            ],
            "actividadIndustrialComercialEmpresarial" => [
                "remuneracionTotal" => [
                    "monto" => [
                        "valor" => $declaracion->{'h8 valor actividadIndustrialComercialEmpresarial'},
                        "moneda" => "MXN",
                    ]
                ],
                "actividades" => [
                    [
                        "remuneracion" => [
                            "monto" => [
                                "valor" => $declaracion->{'h8 valor actividadIndustrialComercialEmpresarial'},
                                "moneda" => "MXN",
                            ],
                            "nombreRazonSocial" => $declaracion->{'h8 nombreRazonSocial'},
                            "tipoNegocio" => $declaracion->{'h8 TipoNegocio'},
                        ]
                    ]
                ],
            ],
            "actividadFinanciera" => [
                "remuneracionTotal" => [
                    "monto" => [
                        "valor" => $declaracion->{'h8 valor actividadFinanciera'},
                        "moneda" => "MXN",
                    ]
                ],
                "actividades" => [[
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $declaracion->{'h8 valor actividadFinanciera'},
                            "moneda" => "MXN",
                        ]
                    ],
                    "tipoInstrumento" => [
                        "clave" => $declaracion->{'h8 clave tipoInstrumento'},
                        "valor" => $declaracion->{'h8 valor tipoInstrumento'},
                    ]
                ]],

            ],
            "serviciosProfesionales" => [
                "remuneracionTotal" => [
                    "monto" => [
                        "valor" => $declaracion->{'h8 RemuneracionTotal'},
                        "moneda" => "MXN",
                    ]
                ],
                "servicios" => [
                    [
                        "remuneracion" => [
                            "monto" => [
                                "valor" => $declaracion->{'h8 RemuneracionTotal'},
                                "moneda" => "MXN",
                            ]
                        ],
                        "tipoServicio" => $declaracion->{'h8 servicios tipoServicio'},
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
                        "valor" => $declaracion->{'h8 valor otrosIngresosMensualesTotal'},
                        "moneda" => "MXN",
                    ]
                ],
                "ingresos" => [
                    [
                        "remuneracion" => [
                            "monto" => [
                                "valor" => $declaracion->{'h8 valor otrosIngresosMensualesTotal'},
                                "moneda" => "MXN",
                            ]
                        ],
                        "tipoIngreso" => "",
                    ]
                ]
            ],
            "ingresoMensualNetoDeclarante" => [
                "monto" => [
                    "valor" => $declaracion->{'h8 ingresos'},
                    "moneda" => "MXN",
                ]
            ],
            "ingresoMensualNetoParejaDependiente" => [
                "monto" => [
                    "valor" => $declaracion->{'h8 ingresoMensualNetoParejaDependiente'},
                    "moneda" => "MXN",
                ]
            ],
            "totalIngresosMensualesNetos" => [
                "monto" => [
                    "valor" => $declaracion->{'h8 totalIngresosMensualesNetos'},
                    "moneda" => "MXN",
                ]
            ],
            "aclaracionesObservaciones" => $declaracion->{'h8 Aclaraciones'},
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
    where i.Id_SituacionPatrimonial =?
    ",
            [$id]
        );
        $actividadAnual = [];
        $actividadAnual["servidorPublicoAnioAnterior"] = $declaracion ? true : false;
        if (!$declaracion) {
            return $actividadAnual;
        }
        $actividadAnual["fechaIngreso"] =  $declaracion->{'FechaInicio'};
        $actividadAnual["fechaConclusion"] = $declaracion->{'FechaConclusion'};
        $actividadAnual["remuneracionNetaCargoPublico"] = [
            "monto" => [
                "valor" => $declaracion->{'valor remuneracionMensualCargoPublico'},
                "moneda" => "MXN"
            ]
        ];
        $actividadAnual["otrosIngresosTotal"] = [
            "monto" => [
                "valor" => $declaracion->{'valor otrosIngresosMensualesTotal'},
                "moneda" => "MXN"
            ]
        ];
        $actividadAnual["actividadIndustrialComercialEmpresarial"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $declaracion->{'valor actividadIndustrialComercialEmpresarial'},
                    "moneda" => "MXN"
                ]
            ],
            "actividades" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $declaracion->{'valor actividadIndustrialComercialEmpresarial'},
                            "moneda" => "MXN"
                        ],

                    ],
                    "nombreRazonSocial" => $declaracion->{'nombreRazonSocial'},
                    "tipoNegocio" => $declaracion->{'TipoNegocio'},

                ]
            ]
        ];
        $actividadAnual["actividadFinanciera"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $declaracion->{'valor actividadFinanciera'},
                    "moneda" => "MXN"
                ]
            ],
            "actividades" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $declaracion->{'valor actividadFinanciera'},
                            "moneda" => "MXN"
                        ],

                    ],
                    "tipoInstrumento" => [
                        "clave" => $declaracion->{'clave tipoInstrumento'},
                        "valor" => $declaracion->{'valor tipoInstrumento'},


                    ],

                ]
            ]
        ];
        $actividadAnual["serviciosProfesionales"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $declaracion->{'RemuneracionTotal'},
                    "moneda" => "MXN",
                ]
            ],
            "servicios" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $declaracion->{'RemuneracionTotal'},
                            "moneda" => "MXN",
                        ]
                    ],
                    "tipoServicio" => $declaracion->{'servicios tipoServicio'},
                ]
            ]
        ];
        $actividadAnual["enajenacionBienes"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => "",
                    "moneda" => "MXN",
                ]
            ],
            "bienes" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => "",
                            "moneda" => "MXN",
                        ]
                    ],
                    "tipoBienEnajenado" => $declaracion->{'tipoBienEnajenado'},
                ]
            ]
        ];
        $actividadAnual["otrosIngresos"] = [
            "remuneracionTotal" => [
                "monto" => [
                    "valor" => $declaracion->{'ingresos'},
                    "moneda" => "MXN",
                ]
            ],
            "ingresos" => [
                [
                    "remuneracion" => [
                        "monto" => [
                            "valor" => $declaracion->{'ingresos'},
                            "moneda" => "MXN",
                        ]
                    ],
                    "tipoIngreso" => "",
                ]
            ]
        ];
        $actividadAnual["ingresoNetoAnualDeclarante"] = [
            "monto" => [
                "valor" => $declaracion->{'ingresos'},
                "moneda" => "MXN",
            ]
        ];
        $actividadAnual["ingresoNetoAnualParejaDependiente"] = [
            "monto" => [
                "valor" => $declaracion->{'ingresoMensualNetoParejaDependiente'},
                "moneda" => "MXN",
            ]
        ];
        $actividadAnual["totalIngresosNetosAnuales"] = [
            "monto" => [
                "valor" => $declaracion->{'totalIngresosMensualesNetos'},
                "moneda" => "MXN",
            ]
        ];
        $actividadAnual["aclaracionesObservaciones"] = $declaracion->{'Aclaraciones'};
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
        where bienes.Id_SituacionPatrimonial =?
        ", [$id]);

        $resultado = [
            "ninguno" => empty($declaracion),
            "bienInmueble" => [],
            "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }

        foreach ($declaracion as $dep) {
            $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $bien = [
                'tipoOperacion' => "AGREGAR",
                'tipoInmueble' => [
                    "clave" => $dep->{'clave TipoInmueble'},
                    "valor" => $dep->{'valor TipoInmueble'},
                ],
                'titular' => [
                    "titularBien" => [
                        [
                            "clave" => $dep->{'clave titular'},
                            "valor" => $dep->{'valor titular'},
                        ]
                    ]
                ],
                'porcentajePropiedad' => $dep->{'PorcentajePropiedad'},
                'superficieTerreno' => [
                    "superficie" => [
                        "valor" => $dep->{'SuperficieTerreno'},
                        "unidad" => "m2",
                    ]
                ],
                'superficieConstruccion' => [
                    "superficie" => [
                        "valor" => $dep->{'Superficieconstruncion'},
                        "unidad" => "m2",
                    ]
                ],
                'tercero' => [
                    [
                        "tipoPersona" => $dep->{'tercero tipo_persona'},
                        "nombreRazonSocial" => $dep->{'T_NombreRazonSocial'},
                    ]
                ],
                'transmisor' => [
                    [
                        "tipoPersona" => $dep->{'transmisor tipo_persona'},
                        "nombreRazonSocial" => $dep->{'TR_NombreRazonSocial'},
                        "rfc" => $dep->{'TR_Rfc'},
                        "relacion" => [
                            "parentescoRelacion" => [
                                "clave" => $dep->{'clave relacion'},
                                "valor" => $dep->{'valor relacion'},
                            ]
                        ],
                    ]
                ],
                'formaAdquisicion' => [
                    "clave" => $dep->{'clave FormaAdquisicion'},
                    "valor" => $dep->{'valor FormaAdquisicion'},
                ],
                "formaPago" => $dep->{'forma_pago'},
                'valorAdquisicion' => [
                    "monto" => [
                        "valor" => $dep->{'ValorAdquisicion'},
                        "moneda" => "MXN",
                    ]
                ],
                "fechaAdquisicion" => $dep->{'FechaAdquisicion'},
                "datoIdentificacion" => $dep->{'DatoIdentificacion'},
                "valorConformeA" => $dep->{'valor conformeA'},
                'domicilioMexico' => [
                    "calle" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"Calle"} : "",
                    "numeroExterior" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroExterior"} : "",
                    "numeroInterior" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroInterior"} : "",
                    "coloniaLocalidad" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"ColoniaLocalidad"} : "",
                    "municipioAlcaldia" => [
                        "clave" => $dep->{'clave municipioAlcaldia'},
                        "valor" => $dep->{'valor municipioAlcaldia'},
                    ],
                    "entidadFederativa" => [
                        "clave" => $dep->{'clave entidadFederativa'},
                        "valor" => $dep->{'valor entidadFederativa'},
                    ],
                    "codigoPostal" => $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"CodigoPostal"} : "",
                ],
                "domicilioExtranjero" => [
                    "calle" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Calle"} : "",
                    "numeroExterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroExterior"} : "",
                    "numeroInterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroInterior"} : "",
                    "ciudadLocalidad" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CiudadLocalidad"} : "",
                    "estadoProvincia" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"EstadoProvincia"} : "",
                    "pais" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Pais"} : "",
                    "codigoPostal" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CodigoPostal"} : "",
                ],
                'motivoBaja' => [
                    "clave" => $dep->{"clave motivo_baja"},
                    "valor" => $dep->{"valor motivo_baja"},
                ]
            ];

            $resultado['bienInmueble'][] = $bien;
        }

        return $resultado;
    }
    // protected function generarSeccionBienesInmuebles($id)
    // {
    //     $declaracion = DB::select("
    //     select ti.valor as 'valor TipoInmueble',ti.abreviatura as 'clave TipoInmueble',t.valor as 'valor titular',t.abreviatura as 'clave titular',bienes.PorcentajePropiedad,bienes.SuperficieTerreno,bienes.Superficieconstruncion,
    //     CASE
    //         WHEN bienes.TR_Id_TipoPersona = 1 or bienes.TR_Id_TipoPersona = 0 THEN 'FISICA'
    //         WHEN bienes.T_id_TipoPersona = 2 THEN 'MORAL'
    //     END AS 'tercero tipo_persona',bienes.T_NombreRazonSocial,bienes.T_Rfc,
    //     CASE
    //         WHEN bienes.TR_Id_TipoPersona = 1 or bienes.TR_Id_TipoPersona = 0 THEN 'FISICA'
    //         WHEN bienes.TR_Id_TipoPersona = 2 THEN 'MORAL'
    //     END AS 'transmisor tipo_persona',bienes.TR_NombreRazonSocial,bienes.TR_Rfc,
    //     rd.valor as 'valor relacion',
    //     rd.abreviatura as 'clave relacion',
    //     fd.valor as 'valor FormaAdquisicion',
    //     fd.abreviatura as 'clave FormaAdquisicion',
    //     fp.valor as 'forma_pago',
    //     bienes.ValorAdquisicion,
    //     bienes.FechaAdquisicion,
    //     bienes.DatoIdentificacion,
    //     vc.valor as 'valor conformeA',
    //     mb.valor as 'valor motivo_baja',
    //     mb.abreviatura as 'clave motivo_baja',
    //     bienes.Calle,
    //     bienes.NumeroExterior,
    //     bienes.NumeroInterior,
    //     bienes.ColoniaLocalidad,
    //     Municipio.clave_geologica as 'clave municipioAlcaldia',
    //     Municipio.Municipio as 'valor municipioAlcaldia',
    //     RIGHT('0' + CAST(Estado.Clave AS VARCHAR(2)), 2) AS 'clave entidadFederativa',
    //     Estado.Estado as 'valor entidadFederativa',
    //     bienes.CodigoPostal,
    //     bienes.CiudadLocalidad,
    //     bienes.EstadoProvincia,
    //     p.Pais,
    //     IIF(bienes.EsEnMexico = 1, 'MEXICO', 'EXTRANJERO') AS 'lugarDondeReside'
    //     from DECL_BienesInmuebles as bienes
    //     inner join TipoInmueble as ti on ti.clave = bienes.Id_TipoInmueble
    //     inner join Titular as t on t.clave = bienes.Id_Titular
    //     left join ParentescoRelacion as rd on rd.clave = bienes.Id_Relacion
    //     left join FormaAdquisicion as fd on fd.clave = bienes.Id_FormaAdquisicion
    //     left join FormaPago as fp on fp.clave = bienes.Id_FormaPago
    //     left join ValorConformeA as vc on vc.clave = bienes.Id_ValorConformeA
    //     left join MotivoBaja as mb on mb.clave = bienes.Id_MotivoBaja
    //     LEFT JOIN Municipio  on Municipio.Clave =bienes.Id_MunicipioAlcaldia
    //     LEFT JOIN Estado  on Estado.Clave =bienes.Id_EntidadFederativa
    //     left join Pais as p on p.Clave  = bienes.Id_Pais

    //         where bienes.Id_SituacionPatrimonial =?
    //     ", [$id]);
    //     $bienesInmuebles = [];
    //     $bienesInmuebles["ninguno"] = !empty($declaracion) ? false : true;
    //     if (empty($declaracion)) {
    //         return $bienesInmuebles;
    //     }
    //     foreach ($declaracion as $dep) {
    //         $bien = [];
    //         $bienesInmuebles['tipoOperacion'] = "AGREGAR";
    //         $bienesInmuebles['tipoInmueble'] = [
    //             "clave" => $dep->{'clave TipoInmueble'},
    //             "valor" => $dep->{'valor TipoInmueble'},

    //         ];
    //         $bienesInmuebles['titular'] = [
    //             "titularBien" => [
    //                 [
    //                     "clave" => $dep->{'valor titular'},
    //                     "valor" => $dep->{'valor titular'},

    //                 ]
    //             ]

    //         ];
    //         $bienesInmuebles['porcentajePropiedad'] = $dep->{'PorcentajePropiedad'};
    //         $bienesInmuebles['superficieTerreno'] = [
    //             "superficie" => [
    //                 "valor" => $dep->{'SuperficieTerreno'},
    //                 "unidad" => "m2",

    //             ]

    //         ];
    //         $bienesInmuebles['superficieConstruccion'] = [
    //             "superficie" => [
    //                 "valor" => $dep->{'Superficieconstruncion'},
    //                 "unidad" => "m2",
    //             ]
    //         ];
    //         $bienesInmuebles['tercero'] = [
    //             [
    //                 "tipoPersona" => $dep->{'tercero tipo_persona'},
    //                 "nombreRazonSocial" => $dep->{'T_NombreRazonSocial'},
    //             ]

    //         ];
    //         $bienesInmuebles['transmisor'] = [
    //             [
    //                 "tipoPersona" => $dep->{'transmisor tipo_persona'},
    //                 "nombreRazonSocial" => $dep->{'TR_NombreRazonSocial'},
    //                 "rfc" => $dep->{'TR_Rfc'},
    //                 "relacion" => [
    //                     "parentescoRelacion" => [
    //                         "clave" => $dep->{'clave relacion'},
    //                         "valor" => $dep->{'valor relacion'},

    //                     ]
    //                 ],

    //             ]
    //         ];
    //         $bienesInmuebles['formaAdquisicion'] = [

    //             "clave" => $dep->{'clave FormaAdquisicion'},
    //             "valor" => $dep->{'valor FormaAdquisicion'},


    //         ];
    //         $bienesInmuebles["formaPago"] = $dep->{'forma_pago'};
    //         $bienesInmuebles['valorAdquisicion'] = [
    //             "monto" => [
    //                 "valor" => $dep->{'ValorAdquisicion'},
    //                 "moneda" => "MXN",

    //             ]

    //         ];
    //         $bienesInmuebles["fechaAdquisicion"] = $dep->{'FechaAdquisicion'};
    //         $bienesInmuebles["datoIdentificacion"] = $dep->{'DatoIdentificacion'};
    //         $bienesInmuebles["valorConformeA"] = $dep->{'valor conformeA'};
    //         $bienesInmuebles["domicilioMexico"] = [
    //             "calle" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"Calle"} : "",
    //             "numeroExterior" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroExterior"} : "",
    //             "numeroInterior" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"NumeroInterior"} : "",
    //             "coloniaLocalidad" =>  $dep->{"lugarDondeReside"} == "MEXICO" ? $dep->{"ColoniaLocalidad"} : "",
    //             "municipioAlcaldia" => [
    //                 "clave" => $dep->{'clave municipioAlcaldia'},
    //                 "valor" => $dep->{'valor municipioAlcaldia'},

    //             ],
    //             "entidadFederativa" => [
    //                 "clave" => $dep->{'clave entidadFederativa'},
    //                 "valor" => $dep->{'valor entidadFederativa'},

    //             ],
    //             "codigoPostal" => $dep->{"lugarDondeReside"} == "MEXICO" ? "" :  $dep->{"CodigoPostal"},
    //         ];
    //         $bienesInmuebles["domicilioExtranjero"] = [
    //             "calle" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Calle"} : "",
    //             "numeroExterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroExterior"} : "",
    //             "numeroInterior" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"NumeroInterior"} : "",
    //             "ciudadLocalidad" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CiudadLocalidad"} : "",
    //             "estadoProvincia" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"EstadoProvincia"} : "",
    //             "pais" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"Pais"} : "",
    //             "codigoPostal" => $dep->{"lugarDondeReside"} == "EXTRANJERO" ? $dep->{"CodigoPostal"} : "",
    //         ];
    //         $bienesInmuebles['motivoBaja'] = [
    //             "clave" => $dep->{"clave motivo_baja"},
    //             "valor" => $dep->{"valor motivo_baja"},

    //         ];
    //         $bienesInmuebles['bienInmueble'][] = $bien;
    //     }
    //     $bienesInmuebles["aclaracionesObservaciones"] = "";

    //     return $bienesInmuebles;
    // }
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
    and vh.Id_SituacionPatrimonial =?

        
        ", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "vehiculo" => [],
            "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }

        foreach ($declaracion as $dep) {
            $vehiculo = [
                "tipoOperacion" => "AGREGAR",
                "tipoVehiculo" => [
                    "clave" => $dep->{'clave_vehiculo'},
                    "valor" => $dep->{'valor_vehiculo'},

                ],
                "titular" => [
                    "titularBien" => [
                        [

                            "clave" => $dep->{'clave_titular'},
                            "valor" => $dep->{'valor_titular'},
                        ]
                    ]

                ],
                "transmisor" => [
                    [
                        "tipoPersona" => $dep->{'transmisor_tipo_persona'},
                        "nombreRazonSocial" => $dep->{'TR_NombreRazonSocial'},
                        "rfc" => $dep->{'TR_Rfc'},
                        "relacion" => [
                            "parentescoRelacion" => [
                                "clave" => "OTRO",
                                "valor" => "OTRO"
                            ]
                        ],

                    ]
                ],
                "marca" => $dep->{'Marca'},
                "modelo" => $dep->{'Modelo'},
                "anio" => $dep->{'Anio'},
                "numeroSerieRegistro" => $dep->{'NumeroSerieRegistro'},
                "tercero" => [

                    [
                        "tipoPersona" => $dep->{'tercero_tipo_persona'},
                        "nombreRazonSocial" => $dep->{'T_NombreRazonSocial'},
                        "rfc" => $dep->{'T_Rfc'},

                    ]
                ],
                "lugarRegistro" => [
                    "pais" => "MX",
                    "entidadFederativa" => [
                        "clave" => $dep->{'clave_entidadFederativa'},
                        "valor" => $dep->{'valor_entidadFederativa'},

                    ]
                ],
                "formaAdquisicion" => [
                    "clave" => $dep->{'clave_adquisicion'},
                    "valor" => $dep->{'valor_adquisicion'},
                ],
                "formaPago" => $dep->{'forma_pago'},
                "valorAdquisicion" => [
                    "valor" => $dep->{'ValorAdquisicion'},
                    "moneda" => "MXN",
                ],
                "fechaAdquisicion" => $dep->{'FechaAdquisicion'},
                "motivoBaja" => [
                    "clave" => $dep->{'clave_motivo_baja'},
                    "valor" => $dep->{'valor_motivo_baja'},
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
        WHERE bm.Id_SituacionPatrimonial = ?
    ", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "bienMueble" => [],
            "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $bienMueble = [
                "tipoOperacion" => "AGREGAR",
                "titular" => [
                    "titularBien" => [
                        [
                            "clave" => $dep->{'clave_titular'},
                            "valor" => $dep->{'valor_titular'},
                        ]
                    ]

                ],
                "tipoBien" => [
                    "clave" => $dep->{'clave_bien'},
                    "valor" => $dep->{'valor_bien'},
                ],
                "transmisor" => [
                    [
                        "tipoPersona" => $dep->{'transmisor_tipo_persona'},
                        "nombreRazonSocial" => $dep->{'TR_NombreRazonSocial'},
                        "rfc" => $dep->{'TR_Rfc'},
                        "relacion" => [
                            "parentescoRelacion" => [
                                "clave" => "OTRO",
                                "valor" => "OTRO",
                            ]
                        ],
                    ]
                ],
                "tercero" => [
                    "tipoPersona" => $dep->{'tercero_tipo_persona'},
                    "nombreRazonSocial" => $dep->{'T_NombreRazonSocial'},
                    "rfc" => $dep->{'T_Rfc'},


                ],
                "descripcionGeneralBien" => $dep->{'DescripcionGeneralBien'},
                "formaAdquisicion" => [
                    "clave" => $dep->{'clave_formadquiscion'},
                    "valor" => $dep->{'valor_formadquiscion'},

                ],
                "formaPago" => $dep->{'valor_formapago'},
                "valorAdquisicion" => [
                    "valor" => $dep->{'ValorAdquisicion'},
                    "moneda" => "MXN",

                ],
                "fechaAdquisicion" => $dep->{'FechaAdquisicion'},
                "motivoBaja" => [
                    "clave" => $dep->{'clave_motivo_baja'},
                    "valor" => $dep->{'valor_motivo_baja'},

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
    where icv.Id_SituacionPatrimonial =?
        ;", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "inversion" => [],
            "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $inversion = [
                "tipoOperacion" => "AGREGAR",
                "tipoInversion" => [
                    "clave" => $dep->{'clave_tipoinversion'},
                    "valor" => $dep->{'valor_tipoinversion'},

                ],
                "subTipoInversion" => [
                    "clave" => $dep->{'clave_subtipoinversion'},
                    "valor" => $dep->{'valor_subtipoinversion'},

                ],
                "titular" => [
                    "titularBien" => [
                        "clave" => $dep->{'clave_titular'},
                        "valor" => $dep->{'titular_valor'},
                    ]

                ],
                "tercero" => [
                    [
                        "tipoPersona" => $dep->{'tercero_tipo_persona'},
                        "nombreRazonSocial" => $dep->{'T_NombreRazonSocial'},
                        "rfc" => $dep->{'T_Rfc'},

                    ]
                ],
                "numeroCuentaContrato" => $dep->{'NumeroCuentaContrato'},
                "localizacionInversion" => [

                    "pais" => $dep->{'code'},
                    "institucionRazonSocial" => $dep->{'InstitucionRazonSocial'},
                    "rfc" => $dep->{'RfcInstitucion'},


                ],
                "saldoSituacionActual" => [

                    "valor" => $dep->{'SaldoSituacionActual'},
                    "moneda" => "MXN",


                ],

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
        where adp.Id_SituacionPatrimonial = ?
        ;", [$id]);
        $resultado = [
            "ninguno" => empty($declaracion),
            "adeudo" => [],
            "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $adeudo = [
                "tipoOperacion" => "AGREGAR",
                "titular" => [
                    "titularBien" => [
                        [
                            "clave" => $dep->{'clave_titular'},
                            "valor" => $dep->{'valor_titular'},
                        ]

                    ]
                ],
                "tipoAdeudo" => [
                    "clave" => $dep->{'clave_adeudo'},
                    "valor" => $dep->{'valor_adeudo'},
                ],
                "numeroCuentaContrato" => $dep->{'NumeroCuentaContrato'},
                "fechaAdquisicion" => $dep->{'FechaAdquisicion'},
                "montoOriginal" => [
                    "monto" => [
                        "valor" => $dep->{'Monto'},
                        "moneda" => "MXN",
                    ]
                ],
                "saldoInsolutoSituacionActual" => [
                    "monto" => [
                        "valor" => $dep->{'SaldoInsolutoSituacionActual'},
                        "moneda" => "MXN",
                    ]
                ],
                "tercero" => [
                    [
                        "tipoPersona" => $dep->{'tercero tipo_persona'},
                        "nombreRazonSocial" => $dep->{'T_NombreRazonSocial'},
                        "rfc" => $dep->{'T_Rfc'},
                    ]

                ],
                "otorganteCredito" => [
                    "tipoPersona" => $dep->{'otorgante tipo_persona'},
                    "nombreInstitucion" => $dep->{'OC_NombreRazonSocial'},
                    "rfc" => $dep->{'OC_Rfc'},
                ],
                "localizacionAdeudo" =>
                [
                    "pais" => $dep->{'code'},
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
                    WHEN dp.V_Id_EntidadFederativa = 1 or dp.V_Id_EntidadFederativa = 0 THEN 'FISICA'
                    WHEN dp.V_Id_EntidadFederativa = 2 THEN 'MORAL'
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
            "aclaracionesObservaciones" => ""
        ];

        if (empty($declaracion)) {
            return $resultado;
        }
        foreach ($declaracion as $dep) {
            $resultado['aclaracionesObservaciones'] = $dep->{'Aclaraciones'};
            $prestamo = [
                "tipoOperacion" => "AGREGAR",
                "tipoBien" => [
                    "inmueble" => [
                        "tipoInmueble" => [
                            "clave" => $dep->{'clave_tipoinmueble'},
                            "valor" => $dep->{'valor_tipoinmueble'},

                        ],
                        "domicilioMexico" => [
                            "calle" => $dep->{'Calle'},
                            "numeroExterior" => $dep->{'NumeroExterior'},
                            "numeroInterior" => $dep->{'NumeroInterior'},
                            "coloniaLocalidad" => $dep->{'ColoniaLocalidad'},
                            "municipioAlcaldia" => [
                                "clave" => $dep->{'clave_municipioAlcaldia'},
                                "valor" => $dep->{'valor_municipioAlcaldia'},

                            ],
                            "entidadFederativa" => [
                                "clave" => $dep->{'clave_entidadFederativa'},
                                "valor" => $dep->{'valor_entidadFederativa'},
                            ]
                        ],
                        "domicilioExtranjero" => [
                            "calle" => $dep->{'Calle'},
                            "numeroExterior" => $dep->{'NumeroExterior'},
                            "numeroInterior" => $dep->{'NumeroInterior'},
                            "ciudadLocalidad" => $dep->{'CiudadLocalidad'},
                            "estadoProvincia" => $dep->{'EstadoProvincia'},
                            "pais" => $dep->{'Code'},
                            "codigoPostal" => $dep->{'CodigoPostal'},

                        ],

                    ],
                    "vehiculo" => [
                        "tipo" => [
                            "tipoVehiculo" => [
                                "clave" => $dep->{'clave_vehiculo'},
                                "valor" => $dep->{'valor_vehiculo'},

                            ]
                        ],
                        "marca" => $dep->{'Marca'},
                        "modelo" => $dep->{'Modelo'},
                        "anio" => $dep->{'Anio'},
                        "numeroSerieRegistro" => $dep->{'NumeroSerieRegistro'},
                        "lugarRegistro" => [
                            "pais" => $dep->{'vehiculo_code'},
                            "entidadFederativa" => [
                                "clave" => $dep->{'vehiculo_clave_entidadFederativa'},
                                "valor" => $dep->{'vehiculo_valor_entidadFederativa'},

                            ]
                        ],
                    ],

                ],
                "duenoTitular" => [
                    "tipoDuenoTitular" => $dep->{'tipoDuenoTitular'},
                    "nombreTitular" => $dep->{'Nombret'},
                    "rfc" => $dep->{'RfcTitular'},
                    "relacionConTitular" => $dep->{'relacion'},

                ]
            ];
            $resultado['prestamo'][] = $prestamo;
        }
        return $resultado;
    }
    protected function generarNombreArchivo($declaracion)
    {
        return "declaracion_" . $declaracion->{"Id_SituacionPatrimonial"} . $declaracion->{"h1 nombre"} . ".json";
    }
}
