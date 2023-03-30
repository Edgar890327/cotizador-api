<?php

namespace App\Http\Controllers;

use App\Models\AdminModel;
use App\Models\MantenimientoModel;
use App\Models\NClientesModel;
use Error;
use Illuminate\Http\Request;
use nusoap_client;
use PHPUnit\Util\Json;
use Symfony\Component\Console\Input\Input;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once __DIR__ . "/../../../vendor/autoload.php";

class ChubbController extends Controller
{

    public function __construct()
    {
        try {
            // $this->url = "https://www10qa.abaseguros.com/ws/api/v1/";
            $this->url = "https://www10.abaseguros.com/ws/api/v1/";
            $this->estructura = 7190;
            $this->agrupacionId = 225880;
            // $this->agrupacionId = 208802;
            $this->tarifaId = 360;
            // $this->tarifaId = 200;
            $this->negocioId = 5951;
            $this->agenteId = 292818;
            $this->consultarUrl = "http://www3.abaseguros.com/PersonaConnect/PCConsultas.svc";
            // $this->consultarUrl = "https://web.abaseguros.com/PersonaConnect/PCConsultas.svc";
            $this->registrarUrl = "http://www3.abaseguros.com/PersonaConnect/PCRegistro.svc";
            // $this->registrarUrl = "https://web.abaseguros.com/PersonaConnect/PCRegistro.svc";
            $this->providerCatalogo = 'https://web.abaseguros.com/AutoConnect/ACCatalogos.svc';
        } catch (\Exception $e) {
            return $e;
        }
    }


    function getToken()
    {


        try {
            //consume a GET request to the API Rest of Banorte and return the response n
            $token_service_url = "https://chubbnetlogin.chubblatinamerica.com/SecurityProxy/api/v1/oauth2/token";
            // $token_service_url = "https://sit-chubbnetlogin.chubblatinamerica.com/SecurityProxy/api/v1/oauth2/token";
            $client = new \GuzzleHttp\Client();

            $response = $client->request("POST", $token_service_url, [
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                ], "json" => [
                    // "client_id" => env("CHUBB_USER"),
                    "client_id" => "292818",
                    "client_secret" => "8516.VGND",
                    "grant_type" => "client_credential",
                    "scope" => "SEMI"
                ]
            ]);


            // $response = $client->request("POST", $token_service_url, [
            //     "headers" => [
            //         "Accept" => "application/json",
            //         "Content-Type" => "application/json",
            //     ], "json" => [
            //         // "client_id" => env("CHUBB_USER"),
            //         "client_id" => "292818",
            //         "client_secret" => "VG0609.N18",
            //         "grant_type" => "client_credential",
            //         "scope" => "SEMI"
            //     ]
            // ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
            } else {
                $response = [
                    "status" => "error",
                    "code" => $response->getStatusCode(),
                    "message" => "Error al consumir el servicio"
                ];
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        } catch (\Error $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function getAgentes($token)
    {
        $token_service_url = $this->url . "Catalogo/Agentes?Estructura=" . $this->estructura;
        $client = new \GuzzleHttp\Client();
        $response = $client->request("GET", $token_service_url, [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . $token
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            $response = [
                "status" => "error",
                "code" => $response->getStatusCode(),
                "message" => "Error al consumir el servicio"
            ];
        }
    }


    function getEstados($token)
    {

        try {
            //consume a GET request to the API Rest of Banorte and return the response n
            $token_service_url = $this->url . "Catalogos/Ubicacion/Estados" . "?Estructura=" . $this->estructura . "&AgrupacionId=" . $this->agrupacionId . "&TarifaId=" . $this->tarifaId;
            $client = new \GuzzleHttp\Client();
            $response = $client->request("GET", $token_service_url, [
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    //ADD BEARER TOKEN from $request->header("token")
                    "Authorization" => "Bearer " . $token
                ]
            ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
            } else {
                $response = [
                    "status" => "error",
                    "code" => $response->getStatusCode(),
                    "message" => "Error al consumir el servicio"
                ];
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    function getMunicipios($estado_id, $token)
    {

        try {
            //consume a GET request to the API Rest of Banorte and return the response n

            $token_service_url = $this->url . "Catalogos/Ubicacion/Municipios" . "?EstadoId=" . $estado_id . "&Estructura=" . $this->estructura . "&AgrupacionId=" . $this->agrupacionId . "&TarifaId=" . $this->tarifaId;
            $client = new \GuzzleHttp\Client();
            $response = $client->request("GET", $token_service_url, [
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    //ADD BEARER TOKEN from $request->header("token")
                    "Authorization" => "Bearer " . $token
                ]
            ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
            } else {
                $response = [
                    "status" => "error",
                    "code" => $response->getStatusCode(),
                    "message" => "Error al consumir el servicio"
                ];
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    function getMarcas($token)
    {
        try {
            //consume a GET request to the API Rest of Banorte and return the response n
            $token_service_url = $this->url . "Catalogos/Vehiculo/Marcas" . "?Estructura=" . $this->estructura . "&AgrupacionId=" . $this->agrupacionId . "&TarifaId=" . $this->tarifaId;
            $client = new \GuzzleHttp\Client();

            $response = $client->request("GET", $token_service_url, [
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    //ADD BEARER TOKEN from $request->header("token")
                    "Authorization" => "Bearer " . $token
                ]
            ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
            } else {
                $response = [
                    "status" => "error",
                    "code" => $response->getStatusCode(),
                    "message" => "Error al consumir el servicio"
                ];
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    function getSubMarcas($token, $marca)
    {
        try {
            //consume a GET request to the API Rest of Banorte and return the response n
            $token_service_url = $this->url . "Catalogos/Vehiculo/SubMarca?Estructura=" . $this->estructura . "&MarcaId=" . $marca . "&AgrupacionId=" . $this->agrupacionId . "&TarifaId=" . $this->tarifaId;
            $client = new \GuzzleHttp\Client();

            $response = $client->request("GET", $token_service_url, [
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    //ADD BEARER TOKEN from $request->header("token")
                    "Authorization" => "Bearer " . $token
                ]
            ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
            } else {
                $response = [
                    "status" => "error",
                    "code" => $response->getStatusCode(),
                    "message" => "Error al consumir el servicio"
                ];
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    function getTipoVehiculo($token, $submarcaId)
    {
        $token_service_url = $this->url . "Catalogos/Vehiculo/Tipos?Estructura=" . $this->estructura . "&AgrupacionId=" . $this->agrupacionId . "&TarifaId=" . $this->tarifaId . "&SubMarcaId=" . $submarcaId;
        $client = new \GuzzleHttp\Client();
        $response = $client->request("GET", $token_service_url, [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                //ADD BEARER TOKEN from $request->header("token")
                "Authorization" => "Bearer " . $token
            ]
        ]);
        //check if the response is ok
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            $response = [
                "status" => "error",
                "code" => $response->getStatusCode(),
                "message" => "Error al consumir el servicio"
            ];
        }
    }

    function getVehiculo($token, $tipo_vehiculo)
    {
        $token_service_url = $this->url . "Catalogos/Vehiculo/Vehiculos?Estructura=" . $this->estructura . "&TipoVehiculoId=" . $tipo_vehiculo . "&AgrupacionId=" . $this->agrupacionId . "&TarifaId=" . $this->tarifaId . "&Detalle=true";
        $client = new \GuzzleHttp\Client();
        $response = $client->request("GET", $token_service_url, [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                //ADD BEARER TOKEN from $request->header("token")
                "Authorization" => "Bearer " . $token
            ]
        ]);
        //check if the response is ok
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents());
        } else {
            $response = [
                "status" => "error",
                "code" => $response->getStatusCode(),
                "message" => "Error al consumir el servicio"
            ];
        }
    }



    public function getMoneda($token)
    {
        $token_service_url = $this->url . "Catalogo/Moneda?Estructura=" . $this->estructura;
        $client = new \GuzzleHttp\Client();
        $response = $client->request("GET", $token_service_url, [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                //ADD BEARER TOKEN from $request->header("token")
                "Authorization" => "Bearer " . $token
            ]
        ]);
    }

    public function setQuote(Request $request)
    {
        if ($request->header("key") == env("TOKEN")) {
            try {
                $token = $this->getToken();
                // return $token;
                // $token = env("CHUBB_TOKEN");

                //get client by id in the request->cliente_id
                // $client = NClientesModel::find($request->cliente_id)->get()->first();
                $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
                $descuento = MantenimientoModel::where('provider', 'chubb')->first()->descuento;
                if ($descuento == 1) {
                    //check if tipo_cliente is publico
                    if ($cliente->tipo_cliente == 'Público') {
                        //get gs_discount from admin where admin_id = 1
                        $discount = AdminModel::where('admin_id', 1)->first()->chubb_descuento;
                    } else {
                        //get gs_discount from client where client_id = $request->cliente_id
                        $discount = $cliente->chubb_descuento;
                    }
                } else {
                    $discount = 0;
                }

                $agentes = $this->getAgentes($token);

                $brands = $this->getMarcas($token);
                // return $brands->responseData;
                $brand_result = array();

                $c = 0;
                //search $request->marca in $brands
                foreach ($brands->responseData as $brand) {
                    if ($brand->descripcion == $request->marca) {
                        $brand_result[$c] = $brand;
                    }
                }
                // return $brand_result;

                $states = $this->getEstados($token);
                // return $states->responseData;
                $state_result = array();

                $c = 0;
                //search $request->estado in $states
                foreach ($states->responseData as $state) {
                    if ($state->descripcion == $request->estado) {
                        $state_result[$c] = $state;
                    }
                }
                // return $state_result;

                $sub_brands = $this->getSubMarcas($token, $brand_result[0]->id);
                // return $sub_brands->responseData;
                $sub_brand_result = array();

                $c = 0;
                //search $request->submarca in $sub_brands
                foreach ($sub_brands->responseData as $sub_brand) {
                    if ($sub_brand->descripcion == $request->submarca) {
                        $sub_brand_result[$c] = $sub_brand;
                    }
                }
                // return $sub_brand_result;


                $municipalities = $this->getMunicipios($state_result[0]->id, $token);

                // return $municipalities->responseData;
                $municipality_result = array();
                $c = 0;
                //search $request->municipio in $municipalities
                foreach ($municipalities->responseData as $municipality) {
                    // if similar_text
                    similar_text($municipality->descripcion, $request->input('municipio'), $percent);
                    $municipality_result[$c] = array(
                        'id' => $municipality->id,
                        'descripcion' => $municipality->descripcion,
                        'percent' => $percent
                    );
                    $c++;
                }
                // return $municipality_result;
                // get the max percent
                $municipality_max = 0;
                $c = 0;
                foreach ($municipality_result as $municipality) {
                    if ($c == 0) {
                        $municipality_max = $municipality;
                        $c++;
                    } else {
                        if ($municipality['percent'] > $municipality_max['percent']) {
                            $municipality_max = $municipality;
                        }
                    }
                }
                // return $municipality_max;

                $tipo = $this->getTipoVehiculo($token, $sub_brand_result[0]->id);
                $tipo_result = array();
                $c = 0;
                //search $request->tipo in $tipo
                foreach ($tipo->responseData as $tipo) {
                    // if similar_text
                    similar_text($tipo->descripcion, $request->input('descripcion'), $percent);
                    $tipo_result[$c] = array(
                        'id' => $tipo->id,
                        'descripcion' => $tipo->descripcion,
                        'percent' => $percent
                    );
                    $c++;
                }

                //get percent max and get modelo of tipo_result
                $tipo_max = 0;
                $c = 0;
                foreach ($tipo_result as $tipo) {
                    if ($c == 0) {
                        $tipo_max = $tipo;
                        $c++;
                    } else {
                        if ($tipo['percent'] > $tipo_max['percent']) {
                            $tipo_max = $tipo;
                        }
                    }
                }
                // return $tipo_max;

                $vehiculos = $this->getVehiculo($token, $tipo_max['id']);
                $vehiculo_result = array();
                $c = 0;

                foreach ($vehiculos->responseData as $vehiculo) {
                    // if similar_text
                    similar_text($vehiculo->descripcion, $request->input('descripcion_larga'), $percent);
                    $vehiculo_result[$c] = array(
                        'id' => $vehiculo->id,
                        'clave_vehicular' => $vehiculo->detalle->claveVehicular,
                        'descripcion' => $vehiculo->descripcion,
                        'percent' => $percent
                    );
                    $c++;
                }

                $vehiculo_max = 0;
                $c = 0;
                foreach ($vehiculo_result as $vehiculo) {
                    if ($c == 0) {
                        $vehiculo_max = $vehiculo;
                        $c++;
                    } else {
                        if ($vehiculo['percent'] > $vehiculo_max['percent']) {
                            $vehiculo_max = $vehiculo;
                        }
                    }
                }

                // return $vehiculo_max;


                $clientgh = new \GuzzleHttp\Client();
                $quote_service_url = $this->url . "Cotizar";




                $request_body = array(
                    "CotizacionId" => 0,
                    "VersionId" => 0,
                    "DatosGenerales" => array(
                        "NegocioId" => $this->negocioId,
                        "AgenteId" => $this->agenteId,
                        "ConductoId" => 0,
                        "TarifaId" => $this->tarifaId,
                        "InicioVigencia" => $request->input("fecha_inicio"),
                        "FinVigencia" => $request->input("fecha_fin"),
                        "ProductoId" => $request->input("producto_id"),
                        "AgrupacionId" => $this->agrupacionId,
                        "TipoCalculoId" => 1,
                        "FormasPago" => array(
                            array(
                                "Id" => 1
                            ),
                        ),
                        "MonedaId" => 1,
                        "DiasGracia" => 1
                    ),
                    "Incisos" => array(
                        array(
                            "TipoRiesgoId" => 1,
                            "NumeroConsecutivo" => 1,
                            "PorcentajeDescuento" => $discount,
                            "Vehiculo" => array(
                                "ClaveVehicular" => $vehiculo_max['clave_vehicular'],
                                "VehiculoDescripcion" => $vehiculo_max['descripcion'],
                                "Modelo" => $request->input("modelo"),
                                "EsNuevo" => null,
                                "CodigoPostal" => $request->input("codigo_postal"),
                                "UsoId" => 1,
                                "TipoSumaAseguradaId" => 1,
                            ),
                            "Paquetes" => array(
                                array(
                                    "Id" => 1,
                                ),
                            )
                        ),


                    ),
                );
                // return $request_body;

                // "SumaAsegurada" => $request->input("suma_asegurada"),

                // "EstadoId" => $state_result[0]->id,
                // "MunicipioId" => $municipality_result[0]->id,

                $response = $clientgh->request("POST", $quote_service_url, [
                    "headers" => [
                        "Accept" => "application/json",
                        "Content-Type" => "application/json",
                        //ADD BEARER TOKEN from $request->header("token")
                        "Authorization" => "Bearer " . $token
                    ],
                    "json" => $request_body
                ]);

                // return $clientgh;





                return response()->json([
                    "status" => "success",
                    "discount" => $discount,
                    "data" => json_decode($response->getBody()->getContents())
                ]);
            } catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()], 500);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                return response()->json(["error" => $e->getMessage()], 500);
            } catch (\Error $e) {
                return response()->json(["error" => $e->getMessage()], 500);
            } catch (\Throwable $e) {
                return response()->json(["error" => $e->getMessage()], 500);
            }
        } else {
            return response()->json(["error" => "Unauthorized."], 401);
        }
    }

    public function setEmitir(Request $request)
    {
        $json = null;

        $cliente = NClientesModel::where('cliente_id', $request->input('cliente_id'))->first();
        if ($cliente->chubb_person_id != null) {
            $client = new nusoap_client($this->consultarUrl, true);
            // put charset to utf8
            $client->soap_defencoding = 'UTF-8';
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:abas="http://schemas.datacontract.org/2004/07/Abaseguros.PersonaConnect">
            <soapenv:Header>
                <tem:strEntrada><![CDATA[<XML><DP><TP>0</TP><PID>' . $cliente->chubb_person_id . '</PID></DP></XML>]]></tem:strEntrada>
           <tem:Token>
              <!--Optional:-->
              <abas:password>VG0609.N18</abas:password>
              <!--Optional:-->
              <abas:referencia></abas:referencia>
              <!--Optional:-->
              <abas:usuario>292818</abas:usuario>
           </tem:Token>
            </soapenv:Header>
            <soapenv:Body>
               <tem:Entrada/>
            </soapenv:Body>
         </soapenv:Envelope>
         ';
            try {
                $result = $client->send(
                    $soap,
                    'http://tempuri.org/IPCConsultas/ConsultaDireccionesPersona'
                );
                // get the xml from $result['strSalida']
                $xml = simplexml_load_string($result['strSalida']);
                $json = json_encode($xml);
                // var_dump($xml);
                // use dumper to see the result
                // var_dump($result['strSalida']);
                // $result1 = json_encode($result);
                // return $result;
                // return response()->json([
                //     "status" => "success",
                //     "data" => json_decode($result->strSalida, true)
                // ]);
            } catch (\Exception $e) {
                return response()->json([
                    "error" => $e->getMessage(),
                    "message" => "Error al insertar datos de cliente."
                ], 500);
            }
        } else {
            $client = new nusoap_client($this->registrarUrl, true);
            // put charset to utf8
            $client->soap_defencoding = 'UTF-8';
            $rfc = "";
            $homoclave = "";

            // get the first two caracters of a phone number and the last eigth from phone
            $lada = mb_substr($request->input('telefono'), 0, 2);
            $telefono = mb_substr($request->input('telefono'), 2, 8);

            // separe rfc in rfc and homoclave
            if (strlen($request->input('rfc')) > 10) {
                $rfc = mb_substr($request->input('rfc'), 0, 10);
                $homoclave = mb_substr($request->input('rfc'), 10, 3);
            } else {
                $rfc = mb_substr($request->input('rfc'), 0, 10);
                $homoclave = "";
            }

            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:abas="http://schemas.datacontract.org/2004/07/Abaseguros.PersonaConnect">
                    <soapenv:Header>
                        <tem:strEntrada><![CDATA[<XML><DP><TP>0</TP><FISICA><RFC>' . $rfc . '</RFC><HCVE>' . $homoclave . '</HCVE><PNOM>' . $request->input("nombre") . '</PNOM><SNOM>' . $request->input('segundo_nombre') . '</SNOM><APP>' . $request->input('apellido_paterno') . '</APP><APM>' . $request->input('apellido_materno') . '</APM><SEXO>' . $request->input('sexo') . '</SEXO><EDOCIVIL>3</EDOCIVIL></FISICA><DOMICILIO><TIPODIR>1</TIPODIR><CALLE>' . $request->input('calle') . '</CALLE><NUMEXT>' . $request->input('numero_ext') . '</NUMEXT><NUMINT></NUMINT><COL>' . $request->input('colonia') . '</COL><CP>' . $request->input('cod_postal') . '</CP><POB>' . $request->municipio . '</POB></DOMICILIO><TELEFONO><LADA>52</LADA><NUMERO>00000000</NUMERO></TELEFONO><CELULAR><LADA>' . $lada . '</LADA><NUMERO>' . $telefono . '</NUMERO></CELULAR><CORREO>' . $request->input('email') . '</CORREO></DP></XML>]]></tem:strEntrada>
                    <tem:Token>
                        <!--Optional:-->
                        <abas:password>VG0609.N18</abas:password>
                        <!--Optional:-->
                        <abas:referencia></abas:referencia>
                        <!--Optional:-->
                        <abas:usuario>292818</abas:usuario>
                    </tem:Token>
                    </soapenv:Header>
                    <soapenv:Body>
                        <tem:Entrada/>
                    </soapenv:Body>
                </soapenv:Envelope>';
            // return $soap;
            try {
                $result = $client->send(
                    $soap,
                    'http://tempuri.org/IPCRegistro/ConsultaRegistraPersona'
                );
                // return $result;
                // return $client;
                // get the xml from $result['strSalida']
                $xml = simplexml_load_string($result['strSalida']);
                $json = json_encode($xml);
                // return $json;
                $arreglo = json_decode($json, true);
                // convert to int $arreglo['PERSONA']['PID'] and update the cliente
                // if $arreglo['PERSONA']['PID'] exist, update the cliente
                $person_id = null;
                if (isset($arreglo['PERSONA']['PID'])) {
                    // $cliente->chubb_person_id = $arreglo['PERSONA']['PID'];
                    $person_id = $arreglo['PERSONA']['PID'];
                    // $cliente->save();
                } else if (isset($arreglo['PERSONA']['TPID'])) {
                    // $cliente->chubb_person_id = $arreglo['PERSONA']['TPID'];
                    $person_id = $arreglo['PERSONA']['TPID'];
                    // $cliente->save();
                }
                $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:abas="http://schemas.datacontract.org/2004/07/Abaseguros.PersonaConnect">
            <soapenv:Header>
                <tem:strEntrada><![CDATA[<XML><DP><TP>0</TP><PID>' . (int)$person_id . '</PID></DP></XML>]]></tem:strEntrada>
           <tem:Token>
              <!--Optional:-->
              <abas:password>VG0609.N18</abas:password>
              <!--Optional:-->
              <abas:referencia></abas:referencia>
              <!--Optional:-->
              <abas:usuario>292818</abas:usuario>
           </tem:Token>
            </soapenv:Header>
            <soapenv:Body>
               <tem:Entrada/>
            </soapenv:Body>
         </soapenv:Envelope>
         ';
                //  return $soap;
                try {
                    $client1 = new nusoap_client($this->consultarUrl, true);
                    $client1->soap_defencoding = 'UTF-8';
                    $result = $client1->send(
                        $soap,
                        'http://tempuri.org/IPCConsultas/ConsultaDireccionesPersona'
                    );
                    // return $client1;
                    // get the xml from $result['strSalida']
                    $xml = simplexml_load_string($result['strSalida']);
                    $json = json_encode($xml);
                    // return $json;

                    // var_dump($xml);
                    // use dumper to see the result
                    // var_dump($result['strSalida']);
                    // $result1 = json_encode($result);
                    // return $result;
                    // return response()->json([
                    //     "status" => "success",
                    //     "data" => json_decode($result->strSalida, true)
                    // ]);
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            "error" => $e->getMessage(),
                            "message" => "Error al consultar la dirección"
                        ],
                        500
                    );
                }
                // return $arreglo;
                // $array_1 = json_decode($json, true);
                // $cliente->chubb_person_id = $array_1['PERSONA']['TPID'];
                // var_dump($xml);
                // use dumper to see the result
                // var_dump($result['strSalida']);
                // $result1 = json_encode($result);
                // return $result;
                // return response()->json([
                //     "status" => "success",
                //     "data" => json_decode($result->strSalida, true)
                // ]);
            } catch (\Exception $e) {
                return response()->json([
                    "error" => $e->getMessage(),
                    "message" => "Error al consultar la persona 2"
                ], 500);
            }
        }


        try {
            // get an array from the json
            $array = json_decode($json, true);

            $emision_request = array(
                "cotizacionId" => $request->input("cotizacion_id"),
                "versionId" => $request->input("version_id"),
                "formaPagoId" => 1,
                "asegurado" => array(
                    "tranId" => (int)$array['TRANSACCION']['TRANID'],
                    "aseguradoId" => (int)$array['PERSONA']['PID'],
                    "direccionId" => (int)$array['DIRECCIONES']['DIR']['DIRID'],
                ),
                "incisos" => array(
                    array(
                        "numeroInciso" => 1,
                        "paqueteId" => 1,
                        "vehiculo" => array(
                            "serie" => $request->input("serie"),
                            "placa" => $request->input("placa"),
                            "motor" => $request->input("motor"),
                            "referencia" => $request->input("referencia"),
                        ),
                        "propietario" => array(
                            "tranId" => (int)$array['TRANSACCION']['TRANID'],
                            "propietarioId" => (int)$array['PERSONA']['PID'],
                            "direccionId" => (int)$array['DIRECCIONES']['DIR']['DIRID'],
                        ),
                        "beneficiario" => array(
                            "tranId" => (int)$array['TRANSACCION']['TRANID'],
                            "beneficiarioId" => (int)$array['PERSONA']['PID'],
                        ),
                    ),
                ),
                "facturacion" => array(
                    "personalidadJuridicaId" => 1,
                    "rfc" => mb_substr($request->input("rfc"), 0, 10),
                    "nombre" => $request->input("nombre"),
                    "codigoPostal" => $request->input("cod_postal"),
                    "regimenFiscalId" => 1,
                    "usoCFDIId" => 1,
                    "email" => $request->input("email"),
                    "emailComplementoPago" => $request->input("email_complemento_pago"),
                    "comentarios" => $request->input("comentarios"),
                    "ordenCompra" => $request->input("orden_compra"),
                ),
            );
            // return $emision_request;



            $token = $this->getToken();

            // return response()->json([
            //     "status" => "success",
            //     "data" => $emision_request,
            //     "token" => $token,
            //     "json" => json_decode($json)
            // ]);
            // return json_encode($emision_request);

            $clientgh = new \GuzzleHttp\Client();
            $quote_service_url = "https://www10.abaseguros.com/ws/api/v1/Emitir";
            // $response = $clientgh->request("POST", $quote_service_url, [
            //     "headers" => [
            //         "Accept" => "application/json",
            //         "Content-Type" => "application/json",
            //         "Authorization" => "Bearer " . $token
            //     ],
            //     "json" => $emision_request
            // ]);
            // $json_request = json_encode($emision_request);
            $response = $clientgh->request("POST", $quote_service_url, [
                "headers" => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer " . $token
                ],
                "json" => $emision_request
            ]);
            // return $clientgh->getError();
            // return response()->json([
            //     "status" => "success",
            //     "token" => $token,
            //     "body" => $emision_request,
            //     "data" => json_decode($response->getBody()->getContents())
            // ]);
            return response()->json([
                "status" => "success",
                "data" => json_decode($response->getBody()->getContents())
            ]);
        } catch (\Exception $e) {
            return response()->json(["error emision:" => $e->getMessage()], 500);
        }
    }

    public function getEstadosByCp($cp)
    {
        $token = $this->getToken();


        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:abas="http://schemas.datacontract.org/2004/07/Abaseguros.AutoConnect">
        <soapenv:Header>
           <tem:Token>
              <!--Optional:-->
              <abas:password>VG0609.N18</abas:password>
              <!--Optional:-->
              <abas:referencia></abas:referencia>
              <!--Optional:-->
              <abas:usuario>292818</abas:usuario>
           </tem:Token>
        </soapenv:Header>
        <soapenv:Body>
           <tem:Entrada>
              <!--Optional:-->
              <tem:strEntrada><![CDATA[<CAT><NEG>5951</NEG><CP>' . $cp . '</CP></CAT>]]></tem:strEntrada>
           </tem:Entrada>
        </soapenv:Body>
     </soapenv:Envelope>';

        $client1 = new nusoap_client($this->providerCatalogo, true);
        $client1->soap_defencoding = 'UTF-8';
        $result = $client1->send(
            $soap,
            'http://tempuri.org/IPCConsultas/ConsultaDireccionesPersona'
        );
    }
}
