<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\AdminModel;
use App\Models\CotizacionHistorialModel;
use App\Models\MantenimientoModel;
use App\Models\NAutoModel;
use App\Models\NClientesModel;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use nusoap_client;
use SoapClient;
use SoapFault;

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: *');

//require autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
// require_once __DIR__ . '/../../vendor/autoload.php';

use Mailgun\Mailgun;

class MapfreController extends Controller
{
    public function __construct()
    {

        $this->opts = array(
            'https' => array('header' => array('Content-Type:soap+xml; charset=utf-8'))
        );
        // $this->params = array('encoding' => 'UTF-8', 'trace' => true, 'keep_alive' => false, 'soap_version' => SOAP_1_1, 'stream_context' => stream_context_create($this->opts), 'Content-Length' => '0', 'SoapAction' => 'http://www.mapfre.com/ws/wsdl/MapfreWS/MapfreWS/getCotizacion');
        // $this->url = "https://negociosuat.mapfre.com.mx/mapfrews/catalogosautos/catalogosautos.asmx";
        $this->url = "https://negocios.mapfre.com.mx/mapfrews/catalogosautos/catalogosautos.asmx";
        // $this->token = "dcc11f18-b748-4ff8-8f9d-622d4c6d4d9b";
        $this->token = "83a9ad98-caf0-49df-9609-89fe47f4c0be";


        try {
            $this->client = $this->getClient($this->url);
        } catch (Handler $fault) {
            // dd("Fallo",$fault);
        }
    }



    public function getClient($url)
    {
        try {
            // $client = new SoapClient($url);
            $client = new nusoap_client(
                $url,
                true
            );

            return $client;
        } catch (SoapFault $error) {
            //show error
            printf("Error: %s", $error->getMessage());
        }
    }

    public function getMarcas(Request $request)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                <soapenv:Header/>
                    <soapenv:Body>
                        <tem:WS_TW_Marcas>
                            <!--Optional:-->
                            <tem:xml><![CDATA[<XML><DATA><VALOR COD_RAMO="401" COD_ZONA_AGT="99" COD_TIP_VEHI="1" ANIO_FABRICA="' . $request->year . '"/></DATA></XML>]]></tem:xml>
                            <!--Optional:-->
                            <tem:token>' . $this->token . '</tem:token>
                        </tem:WS_TW_Marcas>
                    </soapenv:Body>
                </soapenv:Envelope>';
        // return $soap;
        try {
            $client = new nusoap_client($this->url, true);
            $result = $client->send(
                $soap,
                'http://tempuri.org/WS_TW_Marcas'
            );
            // return $client;
            //return result and client
            $response = json_decode(json_encode($result), true);
            return response()->json([
                "status" => "success",
                "data" => $response,
            ]);

            // dd($result);
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    public function getProvincias(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                <soapenv:Header/>
                    <soapenv:Body>
                        <tem:WS_TW_Poblaciones>
                            <!--Optional:-->
                            <tem:xml><![CDATA[<XML><DATA><VALOR COD_RAMO="401" COD_ESTADO="' . $request->cod_estado . '"/></DATA></XML>]]></tem:xml>
                            <tem:token>' . $this->token . '</tem:token>
                        </tem:WS_TW_Poblaciones>
                    </soapenv:Body>
                </soapenv:Envelope>';
            try {
                $client = new nusoap_client($this->url, true);
                $result = $client->send(
                    $soap,
                    'http://tempuri.org/WS_TW_Poblaciones'
                );
                $response = json_decode(json_encode($result), true);
                return response()->json([
                    "status" => "success",
                    "data" => $response,
                ]);
            } catch (SoapFault $th) {
                return response()->json([
                    "status" => "error",
                    "data" => $th,
                ]);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    public function getMarcasInt($year)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                <soapenv:Header/>
                    <soapenv:Body>
                        <tem:WS_TW_Marcas>
                            <!--Optional:-->
                            <tem:xml><![CDATA[<XML><DATA><VALOR COD_RAMO="401" COD_ZONA_AGT="99" COD_TIP_VEHI="1" ANIO_FABRICA="' . $year . '"/></DATA></XML>]]></tem:xml>
                            <!--Optional:-->
                            <tem:token>' . $this->token . '</tem:token>
                        </tem:WS_TW_Marcas>
                    </soapenv:Body>
                </soapenv:Envelope>';

        try {
            $client = new nusoap_client($this->url, true);
            $result = $client->send(
                $soap,
                'http://tempuri.org/WS_TW_Marcas'
            );

            // return $result;
            $response = json_decode(json_encode($result), true);
            return response()->json([
                "status" => "success",
                "data" => $response,
            ]);

            // dd($result);
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    public function getModelosInt($cod_marca, $year)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:WS_TW_Modelos>
                            <!--Optional:-->
                            <tem:xml><![CDATA[<XML><DATA><VALOR COD_RAMO="401" COD_MARCA="' . $cod_marca . '" COD_TIP_VEHI="1" COD_ZONA_AGT="99" ANIO_FABRICA="' . $year . '"/></DATA></XML>]]></tem:xml>
                            <!--Optional:-->
                            <tem:token>' . $this->token . '</tem:token>
                        </tem:WS_TW_Modelos>
                    </soapenv:Body>
                </soapenv:Envelope>';

        try {
            $client = new nusoap_client($this->url, true);
            $result = $client->send(
                $soap,
                'http://tempuri.org/WS_TW_Modelos'
            );

            // return $result;
            $response = json_decode(json_encode($result), true);
            return response()->json([
                "status" => "success",
                "data" => $response,
            ]);
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    public function getModelos(Request $request)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:WS_TW_Modelos>
                            <!--Optional:-->
                            <tem:xml><![CDATA[<XML><DATA><VALOR COD_RAMO="401" COD_MARCA="' . $request->cod . '" COD_TIP_VEHI="1" COD_ZONA_AGT="99" ANIO_FABRICA="' . $request->year . '"/></DATA></XML>]]></tem:xml>
                            <!--Optional:-->
                            <tem:token>' . $this->token . '</tem:token>
                        </tem:WS_TW_Modelos>
                    </soapenv:Body>
                </soapenv:Envelope>';

        try {
            $client = new nusoap_client($this->url, true);
            $result = $client->send(
                $soap,
                'http://tempuri.org/WS_TW_Modelos'
            );

            // return $result;
            $response = json_decode(json_encode($result), true);
            return response()->json([
                "status" => "success",
                "data" => $response,
            ]);
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    public function getEstados(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                        <soapenv:Header/>
                        <soapenv:Body>
                        <tem:WS_TW_Estados>
                            <!--Optional:-->
                            <tem:xml><![CDATA[<XML><DATA><VALOR COD_RAMO="401"/></DATA></XML>]]></tem:xml>
                            <!--Optional:-->
                            <tem:token>' . $this->token . '</tem:token>
                        </tem:WS_TW_Estados>
                        </soapenv:Body>
                    </soapenv:Envelope>';

            try {
                $client = new nusoap_client($this->url, true);
                $result = $client->send(
                    $soap,
                    'http://tempuri.org/WS_TW_Estados'
                );

                // return $result;
                $response = json_decode(json_encode($result), true);
                return response()->json([
                    "status" => "success",
                    "data" => $response,
                ]);
            } catch (SoapFault $th) {
                //throw $th;
                dd($th);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    public function setCotizacion(Request $request)
    {

        if ($request->header('key') == env('TOKEN')) {


            try {
                $marcas = $this->getMarcasInt($request->modelo);
                $marcas = json_decode($marcas->getContent(), true);
                $marcas = $marcas['data']['WS_TW_MarcasResult']['xml']['data']['lista'];



                // $search is equals to upercase of $smarca;
                $marca_result = array();
                $c = 0;

                //search $request->marca in $marcas
                foreach ($marcas as $marca) {

                    $marca_int = $request->marca;

                    if ($marca_int == "FORD") {
                        $marca_int = "FORD FR";
                    }

                    if (strtoupper($marca['NOM_MARCA']) == strtoupper($marca_int)) {
                        $marca_result[$c] = $marca;
                        $c++;
                    }
                }



                $modelos = $this->getModelosInt($marca_result[0]['COD_MARCA'], $request->modelo);
                $modelos = json_decode($modelos->getContent(), true);
                if ($modelos['data']['WS_TW_ModelosResult']['xml']['data'] == "") {
                    return response()->json([
                        "status" => "error",
                        "data" => "Modelo no encontrado",
                    ]);
                }
                $modelos = $modelos['data']['WS_TW_ModelosResult']['xml']['data']['lista'];

                $modelo_result = array();
                $c = 0;
                foreach ($modelos as $modelo) {
                    similar_text($modelo["NOM_MODELO"], $request->descripcion, $percent);
                    $modelo_result[$c] = array(
                        "modelo" => $modelo,
                        "percent" => $percent
                    );
                    $c++;
                }



                //get percent max and get modelo of modelo_result
                $modelo_max = array();
                $c = 0;
                foreach ($modelo_result as $modelo) {
                    if ($c == 0) {
                        $modelo_max = $modelo;
                        $c++;
                    } else {
                        if ($modelo['percent'] > $modelo_max['percent']) {
                            $modelo_max = $modelo;
                        }
                    }
                }

                // return response()->json([
                //     "status" => "success",
                //     "data" => $modelo_max['modelo'],
                //     "marca" => $marca_result[0]['NOM_MARCA']
                // ]);


                //get date with -5 hours of difference and format day/month/year
                $date = new DateTime();
                $date->sub(new DateInterval('PT5H'));

                //change - to / in date and remove h:i:s
                $date = str_replace('-', '/', $date->format('d-m-Y'));

                //get date with a year of difference and format day/month/year
                $date_year = new DateTime();
                $date_year->sub(new DateInterval('PT5H'));
                $date_year->add(new DateInterval('P1Y'));
                $date_year = str_replace('-', '/', $date_year->format('d-m-Y'));

                //create a $random number and string characters
                $random = rand(0, 99999);
                $random = str_pad($random, 5, '0', STR_PAD_LEFT);

                if ($request->cliente_id == null) {
                    //get gs_discount from admin where admin_id = 1
                    // $discount = AdminModel::where('admin_id', 1)->first()->mapfre_descuento;
                    //return message to login
                    return response()->json([
                        "status" => "error",
                        "data" => "No se ha encontrado el cliente, debes iniciar sesión para poder realizar una solicitud",
                    ], 201);
                } else {
                    $descuento = MantenimientoModel::where("provider", "mapfre")->first()->descuento;
                    if ($descuento == 1) {
                        //get client with client_id = $request->cliente_id
                        $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
                        //check if tipo_cliente is publico
                        if ($cliente->tipo_cliente == 'Público') {
                            //get gs_discount from admin where admin_id = 1
                            $discount = AdminModel::where('admin_id', 1)->first()->mapfre_descuento;
                        } else {
                            //get gs_discount from client where client_id = $request->cliente_id
                            $discount = $cliente->mapfre_descuento;
                        }
                    } else {
                        $discount = 0;
                    }
                }
                //check if $client->genero is MASCULINO then $genre = 1
                $genre = 0;
                if ($cliente->genero == 'MASCULINO') {
                    $genre = 1;
                } else {
                    $genre = 0;
                }
                $nombre = "";
                $apaterno = "";
                $amaterno = "";
                $mca_fisico = "S";
                if ($cliente->fis_mor == 'FISICA') {
                    $nombre = $cliente->nombre;
                    $apaterno = $cliente->apellido_paterno;
                    $amaterno = $cliente->apellido_materno;
                    $mca_fisico = "S";
                } else {
                    $mca_fisico = "N";
                    $nombre = $cliente->nombre_compania;
                    $genre = "";
                }

                // if client->cod_provincia < 100 then add a 0 in the left
                if ((int)$cliente->cod_municipio < 100) {
                    $cliente->cod_municipio = "0" . $cliente->cod_municipio;
                }

                // concat client cod_provincia and client cod_estado
                $codmovil = $cliente->cod_estado . $cliente->cod_municipio;
                // var_dump($codmovil);



                //clear "/" of $client->fecha_nacimiento and format day/month/year
                $fecha_nacimiento = str_replace('/', '', $cliente->fecha_nacimiento);
                $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                            <soapenv:Header/>
                            <soapenv:Body>
                                <tem:WS_TW_ACotiza>
                                    <!--Optional:-->
                                    <tem:xml><![CDATA[<XML><SEGURIDAD><USER ID="GMAC0106" PWD="MXMIFNLKYZAQH5PRPHRLLA=="/></SEGURIDAD><DATA><COTIZACION><DATOS_POLIZA ID_NEGOCIO="ZONA_ALIADOS" NUM_POLIZA_GRUPO="4SEGA05400001" NUM_CONTRATO="40020" COD_SECTOR="4" COD_RAMO="401" FEC_EFEC_POLIZA="' . $date . '" FEC_VCTO_POLIZA="' . $date_year . '" COD_FRACC_PAGO="1" COD_CUADRO_COM="1" COD_AGT="29709" COD_USR="GMAC0106" COD_NIVEL3_CAPTURA="0" TIP_DOCUM="RFC" COD_DOCUM="PRUEBA" COD_ESTADO="' . $cliente->cod_estado . '" COD_PROV="' . $cliente->cod_municipio . '" PCT_AGT="100" COD_GESTOR="29709" TIP_GESTOR="AG"/><DATOS_VARIABLES NUM_RIESGO="1" TIP_NIVEL="3"><CAMPO COD_CAMPO="COD_MATERIA" VAL_CAMPO="1"/></DATOS_VARIABLES><DATOS_VARIABLES19 NUM_RIESGO="1" TIP_NIVEL="2"><CAMPO9 COD_CAMPO="COD_MODALIDAD" VAL_CAMPO="40999"/><CAMPO11 COD_CAMPO="ANIO_FABRICA" VAL_CAMPO="' . $request->modelo . '"/><CAMPO12 COD_CAMPO="COD_MARCA" VAL_CAMPO="' . $marca_result[0]['COD_MARCA'] . '"/><CAMPO13 COD_CAMPO="COD_MODELO" VAL_CAMPO="' . $modelo_max['modelo']['COD_MODELO'] . '"/><CAMPO14 COD_CAMPO="COD_TIP_VEHI" VAL_CAMPO="1"/><CAMPO15 COD_CAMPO="COD_USO_VEHI" VAL_CAMPO="464"/><CAMPO16 COD_CAMPO="MCA_FACTURA" VAL_CAMPO="N"/><CAMPO17 COD_CAMPO="MCA_ACTUAL" VAL_CAMPO="N"/><CAMPO18 COD_CAMPO="MCA_COMERCIAL" VAL_CAMPO="S"/><CAMPO20 COD_CAMPO="MCA_COMERCIAL10" VAL_CAMPO="N"/><CAMPO21 COD_CAMPO="FEC_FACTURA" VAL_CAMPO="10052022"/><CAMPO22 COD_CAMPO="NUM_FACTURA" VAL_CAMPO="ABCD1234"/><CAMPO23 COD_CAMPO="VAL_FACTURA" VAL_CAMPO="350000"/><CAMPO24 COD_CAMPO="NUM_PASAJEROS" VAL_CAMPO="4"/><CAMPO25 COD_CAMPO="NUM_SERIE" VAL_CAMPO=""/></DATOS_VARIABLES19><DATOS_VARIABLES22 NUM_RIESGO="0" TIP_NIVEL="1"><CAMPO19 COD_CAMPO="COD_BONI_RECA" VAL_CAMPO="999"/><CAMPO21 COD_CAMPO="PCT_COD_REC_ESP" VAL_CAMPO="-23"/></DATOS_VARIABLES22><COBERTURAS><COBERTURA COD_COB="4000" SUMA_ASEG="C" COD_FRANQUICIA="5"/><COBERTURA24 COD_COB="4001" SUMA_ASEG="C" COD_FRANQUICIA="10"/><COBERTURA25 COD_COB="4010" SUMA_ASEG="1500000"/><COBERTURA26 COD_COB="4011" SUMA_ASEG="1500000"/><COBERTURA27 COD_COB="4006" SUMA_ASEG="200000"/><COBERTURA28 COD_COB="4003" SUMA_ASEG="1"/><COBERTURA29 COD_COB="4004" SUMA_ASEG="1"/><COBERTURA30 COD_COB="4012" SUMA_ASEG="0" COD_FRANQUICIA="0"/><COBERTURA32 COD_COB="4013" SUMA_ASEG="100000"/><COBERTURA33 COD_COB="4022" SUMA_ASEG="0" COD_FRANQUICIA="0"/><COBERTURA35 COD_COB="4024" SUMA_ASEG="0"/><COBERTURA37 COD_COB="4028" SUMA_ASEG="0" COD_FRANQUICIA="0"/><COBERTURA45 COD_COB="4068" SUMA_ASEG="0"/></COBERTURAS><DATOS_CH_TMV51> <CAMPO52 COD_CAMPO="MCA_SEXO" VAL_CAMPO="' . $genre . '" /><CAMPO53 COD_CAMPO="COD_ESTADO" VAL_CAMPO="' . $cliente->cod_estado . '" /><CAMPO54 COD_CAMPO="COD_PROV" VAL_CAMPO="' . $cliente->cod_municipio . '" /><CAMPO55 COD_CAMPO="FEC_NACIMIENTO" VAL_CAMPO="' . $fecha_nacimiento . '" /><CAMPO56 COD_CAMPO="COD_POSTAL" VAL_CAMPO="' . $cliente->cod_postal . '" /><CAMPO57 COD_CAMPO="RFC" VAL_CAMPO="' . $cliente->rfc . '" /><CAMPO58 COD_CAMPO="NOM_TERCERO" VAL_CAMPO="' . $nombre . '" /><CAMPO59 COD_CAMPO="APE1_TERCERO" VAL_CAMPO="' . $apaterno . '" /><CAMPO60 COD_CAMPO="APE2_TERCERO" VAL_CAMPO="' . $amaterno . '" /><CAMPO61 COD_CAMPO="MCA_FISICO" VAL_CAMPO="' . $mca_fisico . '" /></DATOS_CH_TMV51></COTIZACION></DATA></XML>]]></tem:xml>
                                    <!--Optional:-->
                                    <tem:token>' . $this->token . '</tem:token>
                                </tem:WS_TW_ACotiza>
                            </soapenv:Body>
                        </soapenv:Envelope>';
                // return $soap;
            } catch (\Exception $e) {
                return response()->json([
                    "status" => "error",
                    "data" => $e->getMessage(),
                ]);
            }

            try {
                $client = new nusoap_client($this->url, true, false, false, false, false, 0, 5600);
                ini_set('default_socket_timeout', 600);

                $result = $client->send(
                    $soap,
                    'http://tempuri.org/WS_TW_ACotiza',
                    0,
                    5600
                );

                // return $client;
                // cliente

                // print_r($result);
                // convert json to xml
                // return $client;

                // return $soap;
                $response = json_decode(json_encode($result), true);

                $data_response = null;

                if (isset($response['WS_TW_ACotizaResult']['xml']['data']['param']['result'])) {
                    $data_response = array(
                        'WS_TW_ACotizaResult' => array(
                            'xml' => array(
                                'param' => array(
                                    'result' => $response['WS_TW_ACotizaResult']['xml']['data']['param']['result'],
                                ),
                                'data' => array(
                                    'cotizacion' => $response['WS_TW_ACotizaResult']['xml']['data']['cotizacion'],

                                )
                            )
                        ),
                    );
                } else {
                    $data_response = array(
                        'WS_TW_ACotizaResult' => array(
                            'xml' => array(
                                'param' => array(
                                    'result' => $response['WS_TW_ACotizaResult']['xml']['param']['result'],
                                ),
                                'data' => array(
                                    'cotizacion' => $response['WS_TW_ACotizaResult']['xml']['data']['cotizacion'],

                                )
                            )
                        ),
                    );
                }

                // return $response;

                try {
                    // $mail_body = array(
                    //     'first_name' => $cliente->nombre . ' ' . $cliente->apellido_paterno,
                    //     'auto' => $request->marca,
                    //     'modelo' => $request->modelo,
                    //     'link' => '',
                    //     'email' => $cliente->email,
                    //     'email_from' => 'noreply_villagomezseguros@procelti.com'
                    // );

                    // $template = $this->build('emails.quote', $mail_body);
                    // $mg = Mailgun::create(
                    //     env('MAILGUN_SECRET'),
                    //     'https://api.eu.mailgun.net'
                    // ); // For EU serverss
                    // $mg->messages()->send('procelti.com', [
                    //     'from'    => 'Tenemos tu Cotización<' . $mail_body['email_from'] . '>',
                    //     'to'      => $cliente->email,
                    //     'subject' => 'Hola ' . $mail_body['first_name'] . ', tu cotización se ha generado',
                    //     'html'    => $template
                    // ]);

                    // Mail::send('emails.tarea', $mail_body, function ($message) use ($mail_body) {
                    //     $message->to($mail_body['email'], $mail_body['first_name'])->subject('Tarea ' . $mail_body['titulo']);
                    //     $message->from('app@villagomezseguros.com', 'Villagomez Seguros');
                    // });
                } catch (Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }

                return response()->json([
                    "status" => "success",
                    "descuento" => $discount,
                    "data" => $data_response,
                    "marca_code" => $marca_result[0]['COD_MARCA'],
                    "modelo_code" => $modelo_max['modelo']['COD_MODELO'],
                ]);
            } catch (\Exception $th) {
                return response()->json([
                    "status" => "error",
                    "data" => $th,
                ]);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }




    public function setEmision(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            try {

                $cliente = NClientesModel::find($request->input('cliente_id'));
                // $cot = $this->getCotizacion($request);
                //create NAutoModel from migration nautos
                if ($cliente->fis_mor == "FISICA") {
                    $auto = NAutoModel::create([
                        'cliente_id' => $request->input('cliente_id'),
                        'nombre' => $request->input('nombre'),
                        'apellido_paterno' => $request->input('apellido_p'),
                        'apellido_materno' => $request->input('apellido_m'),
                        'telefono' => $request->input('telefono'),
                        'email' => $request->input('email'),
                        'rfc' => $request->input('rfc'),
                        'genero' =>  $this->checkSexo($request->input('sexo')),
                        'curp' => "",
                        'fecha_nacimiento' => $cliente->fecha_nacimiento,
                        'edo_civil' => "",
                        'fis_mor' => $this->checkPersona($request->input('tipo_persona')),
                        'razon_social' => $cliente->nombre_compania,
                        'nombre_comercial' => "",
                        'codigo_postal' => $request->input('cod_postal'),
                        'estado' => $request->input('estado'),
                        'municipio' => $request->input('municipio'),
                        'direccion' => $request->input('direccion'),
                        'clave_estado' => $request->input('cod_estado'),
                        'clave_municipio' => $request->input('cod_provincia'),
                        'numero_pasajeros' => $request->input('num_pasajeros'),
                        'marca' => $request->input('marca'),
                        'submarca' => $request->input('descripcion'),
                        'modelo' => $request->input('modelo'),
                        'placa' => $request->input('num_matricula'),
                        'motor' => $request->input('num_motor'),
                        'serie' => $request->input('num_serie'),
                        // 'id_polisa' => $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['Poliza'],
                        'id_polisa' => $request->input('id_polisa'),
                        'provedor' => "MAPFRE",
                        'prima' => $request->input('prima'),
                        'pago' => false,
                        'link_pago' => "",
                        'link_polisa' => "",
                        // 'fecha_vencimiento' => $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['FechaTermino'],
                        'fecha_vencimiento' => "",
                        // 'benefi_cod_provincia' => $request->input('benefi_cod_provincia'),
                        'benefi_cod_provincia' => "",
                        // 'benefi_cod_estado' => $request->input('benefi_cod_estado'),
                        'benefi_cod_estado' => "",
                        // 'benefi_rfc' => $request->input('benefi_rfc'),
                        'benefi_rfc' => "",
                        // 'benefi_telefono1' => $request->input('benefi_telefono1'),
                        'benefi_telefono1' => "",
                        // 'benefi_correo' => $request->input('benefi_correo'),
                        'benefi_correo' => "",
                        // 'benefi_telefono2' => $request->input('benefi_telefono2'),
                        'benefi_telefono2' => "",
                        // 'benefi_cod_postal' => $request->input('benefi_cod_postal'),
                        'benefi_cod_postal' => "",
                        // 'benefi_fecha_nacimiento' => $request->input('benefi_fecha_nacimiento'),
                        'benefi_fecha_nacimiento' => "",
                        // 'benefi_sexo' => $this->checkSexo($request->input('benefi_sexo')),
                        'benefi_sexo' => "",
                        // 'benefi_direccion1' => $request->input('benefi_direccion1'),
                        'benefi_direccion1' => "",
                        // 'benefi_direccion2' => $request->input('benefi_direccion2'),
                        'benefi_direccion2' => "",
                        // 'benefi_nombre' => $request->input('benefi_nombre'),
                        'benefi_nombre' => "",
                        // 'benefi_apellido_p' => $request->input('benefi_apellido_p'),
                        'benefi_apellido_p' => "",
                        // 'benefi_apellido_m' => $request->input('benefi_apellido_m'),
                        'benefi_apellido_m' => "",
                        // 'benefi_tipo_persona' => $this->checkPersona($request->input('benefi_tipo_persona')),
                        'benefi_tipo_persona' => "",
                        'mapfre_marca_code' => $request->input('mapfre_marca_code'),
                        'mapfre_modelo_code' => $request->input('mapfre_modelo_code'),
                    ]);
                } else {
                    $auto = NAutoModel::create([
                        'cliente_id' => $request->input('cliente_id'),
                        'nombre' => $request->input('nombre'),
                        'apellido_paterno' => $request->input('apellido_p'),
                        'apellido_materno' => $request->input('apellido_m'),
                        'telefono' => $request->input('telefono'),
                        'email' => $request->input('email'),
                        'rfc' => $request->input('rfc'),
                        'genero' =>  $this->checkSexo($request->input('sexo')),
                        'curp' => "",
                        'fecha_nacimiento' => $cliente->fecha_nacimiento,
                        'edo_civil' => "",
                        'fis_mor' => $this->checkPersona($request->input('tipo_persona')),
                        'razon_social' => $cliente->nombre_compania,
                        'nombre_comercial' => "",
                        'codigo_postal' => $request->input('cod_postal'),
                        'estado' => $request->input('estado'),
                        'municipio' => $request->input('municipio'),
                        'direccion' => $request->input('direccion'),
                        'clave_estado' => $request->input('cod_estado'),
                        'clave_municipio' => $request->input('cod_provincia'),
                        'numero_pasajeros' => $request->input('num_pasajeros'),
                        'marca' => $request->input('marca'),
                        'submarca' => $request->input('descripcion'),
                        'modelo' => $request->input('modelo'),
                        'placa' => $request->input('num_matricula'),
                        'motor' => $request->input('num_motor'),
                        'serie' => $request->input('num_serie'),
                        // 'id_polisa' => $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['Poliza'],
                        'id_polisa' => $request->input('id_polisa'),
                        'provedor' => "MAPFRE",
                        'prima' => $request->input('prima'),
                        'pago' => false,
                        'link_pago' => "",
                        'link_polisa' => "",
                        // 'fecha_vencimiento' => $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['FechaTermino'],
                        'fecha_vencimiento' => "",
                        // 'benefi_cod_provincia' => $request->input('benefi_cod_provincia'),
                        'benefi_cod_provincia' => "",
                        // 'benefi_cod_estado' => $request->input('benefi_cod_estado'),
                        'benefi_cod_estado' => "",
                        // 'benefi_rfc' => $request->input('benefi_rfc'),
                        'benefi_rfc' => "",
                        // 'benefi_telefono1' => $request->input('benefi_telefono1'),
                        'benefi_telefono1' => "",
                        // 'benefi_correo' => $request->input('benefi_correo'),
                        'benefi_correo' => "",
                        // 'benefi_telefono2' => $request->input('benefi_telefono2'),
                        'benefi_telefono2' => "",
                        // 'benefi_cod_postal' => $request->input('benefi_cod_postal'),
                        'benefi_cod_postal' => "",
                        // 'benefi_fecha_nacimiento' => $request->input('benefi_fecha_nacimiento'),
                        'benefi_fecha_nacimiento' => "",
                        // 'benefi_sexo' => $this->checkSexo($request->input('benefi_sexo')),
                        'benefi_sexo' => "",
                        // 'benefi_direccion1' => $request->input('benefi_direccion1'),
                        'benefi_direccion1' => "",
                        // 'benefi_direccion2' => $request->input('benefi_direccion2'),
                        'benefi_direccion2' => "",
                        // 'benefi_nombre' => $request->input('benefi_nombre'),
                        'benefi_nombre' => "",
                        // 'benefi_apellido_p' => $request->input('benefi_apellido_p'),
                        'benefi_apellido_p' => "",
                        // 'benefi_apellido_m' => $request->input('benefi_apellido_m'),
                        'benefi_apellido_m' => "",
                        // 'benefi_tipo_persona' => $this->checkPersona($request->input('benefi_tipo_persona')),
                        'benefi_tipo_persona' => "",
                        'mapfre_marca_code' => $request->input('mapfre_marca_code'),
                        'mapfre_modelo_code' => $request->input('mapfre_modelo_code'),
                    ]);
                }

                try {
                    // $mail_body = array(
                    //     'first_name' => $auto->nombre . ' ' . $auto->apellido_paterno,
                    //     'email' => $auto->email,
                    //     'auto' => $auto->marca,
                    //     'modelo' => $auto->modelo,
                    //     'link' => 'https://api.whatsapp.com/send?phone=7861072022&text=Hola%2C%20tengo%20un%20auto%20emitido%20en%20la%20plataforma%20AutoCotizador%20con%20el%20ID%20y%20me%20interesa%20pagar.',
                    //     'email_from' => 'noreply_villagomezseguros@procelti.com'
                    // );

                    // $template = $this->build('emails.emision_nopay', $mail_body);
                    // $mg = Mailgun::create(
                    //     env('MAILGUN_SECRET'),
                    //     'https://api.eu.mailgun.net'
                    // ); // For EU serverss
                    // $mg->messages()->send('procelti.com', [
                    //     'from'    => 'Emisión Pendiente<' . $mail_body['email_from'] . '>',
                    //     'to'      => $auto->email,
                    //     'subject' => 'Hola ' . $mail_body['first_name'] . '!',
                    //     'html'    => $template
                    // ]);

                    // Mail::send('emails.tarea', $mail_body, function ($message) use ($mail_body) {
                    //     $message->to($mail_body['email'], $mail_body['first_name'])->subject('Tarea ' . $mail_body['titulo']);
                    //     $message->from('app@villagomezseguros.com', 'Villagomez Seguros');
                    // });
                } catch (Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }

                return response()->json([
                    "status" => "success",
                    "data" => "Auto creado correctamente",
                    "auto" => $auto
                ]);
            } catch (\Exception $e) {
                //return a json with the error
                return response()->json([
                    'status' => 'error',
                    'body' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    public function payEmision(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {

            //get NAutoModel where auto_id = $request->input('auto_id')
            $auto = NAutoModel::where('auto_id', $request->input('auto_id'))->first();

            if ($auto) {
                if ($auto->fis_mor == 'PERSONA FISICA') {
                    $genre = 0;

                    if ($auto->genero == "FEMENINO") {
                        $genre = 0;
                    } else {
                        $genre = 1;
                    }

                    // $search is equals to upercase of $smarca;
                    $marca_result = array();
                    $c = 0;

                    //$modelo is ($auto->modelo) parse to int
                    $modelo = (int)$auto->modelo;

                    //get date with -5 hours of difference and format day/month/year
                    $date = new DateTime();
                    $date->sub(new DateInterval('PT5H'));

                    //change - to / in date and remove h:i:s
                    $date = str_replace('-', '/', $date->format('d-m-Y'));

                    //get date as ddmmaaaa
                    $date_dma = str_replace('/', '', $date);

                    //get date with a year of difference and format day/month/year
                    $date_year = new DateTime();
                    $date_year->sub(new DateInterval('PT5H'));
                    $date_year->add(new DateInterval('P1Y'));
                    $date_year = str_replace('-', '/', $date_year->format('d-m-Y'));



                    //create a $random number and string characters
                    $random = rand(0, 99999);
                    $random = str_pad($random, 5, '0', STR_PAD_LEFT);

                    //random string with 2 characters
                    $random_string = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2) . $random;


                    $tipo_persona = $this->checkPersonaInverse($auto->fis_mor);
                    //emissions
                    $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                            <soapenv:Header/>
                            <soapenv:Body>
                                <tem:WS_TW_AEmite>
                                    <!--Optional:-->
                                    <tem:xml><![CDATA[<XML><SEGURIDAD><USER ID="GMAC0106" PWD="MXMIFNLKYZAQH5PRPHRLLA=="/></SEGURIDAD><DATA><POLIZA><DATOS_POLIZA ID_NEGOCIO="ZONA_ALIADOS" NUM_POLIZA_GRUPO="4SEGA05400001" NUM_CONTRATO="40020" COD_SECTOR="4" COD_RAMO="401" FEC_EFEC_POLIZA="' . $date . '" FEC_VCTO_POLIZA="' . $date_year . '" COD_FRACC_PAGO="1" COD_CUADRO_COM="1" COD_AGT="29709" COD_USR="GMAC0106" COD_NIVEL3_CAPTURA="0" TIP_DOCUM="RFC" COD_DOCUM="' . $auto->rfc . '" COD_ESTADO="' . $auto->clave_estado . '" COD_PROV="' . $auto->clave_municipio . '" PCT_AGT="100" COD_GESTOR="29709" TIP_GESTOR="AG"/><DATOS_VARIABLES NUM_RIESGO="1" TIP_NIVEL="3"><CAMPO COD_CAMPO="COD_MATERIA" VAL_CAMPO="1"/></DATOS_VARIABLES><DATOS_VARIABLES44 TIP_NIVEL="2" NUM_RIESGO="1"><CAMPO9 VAL_CAMPO="' . $auto->serie . '" COD_CAMPO="NUM_SERIE"/><CAMPO11 VAL_CAMPO="40999" COD_CAMPO="COD_MODALIDAD"/><CAMPO12 VAL_CAMPO="' . $auto->modelo . '" COD_CAMPO="ANIO_FABRICA"/><CAMPO13 VAL_CAMPO="' . $auto->mapfre_marca_code . '" COD_CAMPO="COD_MARCA"/><CAMPO14 VAL_CAMPO="' . $auto->mapfre_modelo_code . '" COD_CAMPO="COD_MODELO"/><CAMPO15 VAL_CAMPO="1" COD_CAMPO="COD_TIP_VEHI"/><CAMPO16 VAL_CAMPO="464" COD_CAMPO="COD_USO_VEHI"/><CAMPO17 VAL_CAMPO="N" COD_CAMPO="MCA_FACTURA"/><CAMPO18 VAL_CAMPO="N" COD_CAMPO="MCA_ACTUAL"/><CAMPO19 VAL_CAMPO="S" COD_CAMPO="MCA_COMERCIAL"/><CAMPO20 VAL_CAMPO="N" COD_CAMPO="MCA_COMERCIAL10"/><CAMPO21 VAL_CAMPO="' . $auto->placa . '" COD_CAMPO="NUM_MATRICULA"/><CAMPO22 VAL_CAMPO="' . $auto->motor . '" COD_CAMPO="NUM_MOTOR"/><CAMPO23 VAL_CAMPO="' . $auto->numero_pasajeros . '" COD_CAMPO="NUM_PASAJEROS"/><CAMPO24 VAL_CAMPO="350000" COD_CAMPO="VAL_FACTURA"/><CAMPO25 VAL_CAMPO="' . $random_string . '" COD_CAMPO="NUM_FACTURA"/><CAMPO26 VAL_CAMPO="' . $date_dma . '" COD_CAMPO="FEC_FACTURA"/><CAMPO27 VAL_CAMPO="0" COD_CAMPO="VAL_ACTUAL"/></DATOS_VARIABLES44><DATOS_VARIABLES49 TIP_NIVEL="1" NUM_RIESGO="0"><CAMPO44 VAL_CAMPO="999" COD_CAMPO="COD_BONI_RECA"/><CAMPO46 VAL_CAMPO="-23" COD_CAMPO="PCT_COD_REC_ESP"/><CAMPO47 VAL_CAMPO="100" COD_CAMPO="PCT_CESION_COM_AGT"/><CAMPO48 VAL_CAMPO="1" COD_CAMPO="MEDIO_CAPTACION"/><CAMPO70 VAL_CAMPO="EMPLE123" COD_CAMPO="NUM_EMPLEADO"/></DATOS_VARIABLES49><COBERTURAS><COBERTURA COD_FRANQUICIA="5" SUMA_ASEG="C" COD_COB="4000"/><COBERTURA51 COD_FRANQUICIA="10" SUMA_ASEG="C" COD_COB="4001"/><COBERTURA52 SUMA_ASEG="1500000" COD_COB="4010"/><COBERTURA53 SUMA_ASEG="1500000" COD_COB="4011"/><COBERTURA54 SUMA_ASEG="200000" COD_COB="4006"/><COBERTURA55 SUMA_ASEG="1" COD_COB="4003"/><COBERTURA56 SUMA_ASEG="1" COD_COB="4004"/><COBERTURA57 SUMA_ASEG="0" COD_COB="4012" COD_FRANQUICIA="0"/><COBERTURA59 SUMA_ASEG="100000" COD_COB="4013"/><COBERTURA60 SUMA_ASEG="0" COD_COB="4022" COD_FRANQUICIA="0"/><COBERTURA62 SUMA_ASEG="0" COD_COB="4024"/><COBERTURA64 SUMA_ASEG="0" COD_COB="4028" COD_FRANQUICIA="0"/><COBERTURA68 SUMA_ASEG="0" COD_COB="4068"/></COBERTURAS><TERCEROS><CONTRATANTE COD_PROV="' . $auto->clave_municipio . '" COD_ESTADO="' . $auto->clave_estado . '" COD_DOCUM="' . $auto->rfc . '" MODIFICADO="N" TLF_MOVIL="' . $auto->telefono . '" EMAIL="' . $auto->email . '" TLF_NUMERO="' . $auto->telefono . '" COD_POSTAL="' . $auto->codigo_postal . '" FEC_NACIMIENTO="' . $auto->fecha_nacimiento . '" MCA_SEXO="' . $genre . '" COD_LOCALIDAD="' . $auto->clave_municipio . '" NOM_DOMICILIO3="' . $auto->direccion . '" NOM_DOMICILIO1="' . $auto->municipio . '" NOM_TERCERO="' . $auto->nombre . '" APE2_TERCERO="' . $auto->apellido_paterno . '" APE1_TERCERO="' . $auto->apellido_materno . '" MCA_FISICO="' . $tipo_persona . '" /><CONDUCTOR COD_PROV="' . $auto->clave_municipio . '" COD_ESTADO="' . $auto->clave_estado . '" COD_DOCUM="' . $auto->rfc . '" MODIFICADO="N" TLF_MOVIL="' . $auto->telefono . '" EMAIL="' . $auto->email . '" TLF_NUMERO="' . $auto->telefono . '" COD_POSTAL="' . $auto->codigo_postal . '" FEC_NACIMIENTO="' . $auto->fecha_nacimiento . '" MCA_SEXO="' . $genre . '" COD_LOCALIDAD="' . $auto->clave_municipio . '" NOM_DOMICILIO3="' . $auto->direccion . '" NOM_DOMICILIO1="' . $auto->municipio . '" NOM_TERCERO="' . $auto->nombre . '" APE2_TERCERO="' . $auto->apellido_paterno . '" APE1_TERCERO="' . $auto->apellido_materno . '" MCA_FISICO="' . $tipo_persona . '"/></TERCEROS></POLIZA></DATA></XML>]]></tem:xml>
                                    <!--Optional:-->
                                    <tem:token>' . $this->token  . '</tem:token>
                                </tem:WS_TW_AEmite>
                            </soapenv:Body>
                        </soapenv:Envelope>';

                    // return $soap;


                    try {

                        $client = NClientesModel::where('cliente_id', $auto->cliente_id)->first();
                        // dd($client);
                        //check if tipo_cliente is publico
                        if ($client->tipo_cliente == 'Público') {
                            $client = new nusoap_client($this->url, true, false, false, false, false, 0, 9600);
                            $result = $client->send(
                                $soap,
                                'http://tempuri.org/WS_TW_AEmite',
                                0,
                                18600
                            );
                            // return $client;

                            // return $result;
                            $response = json_decode(json_encode($result), true);
                        }


                        try {
                            //update auto id_poliza from $response $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['Poliza']
                            $poliza = $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['Poliza'];
                            $auto->id_polisa = $poliza;
                            $historial = CotizacionHistorialModel::where('auto_id', $request->input('auto_id'))->first();
                            $historial->poliza_id = $poliza;
                            $historial->save();
                            //update prima $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['PrimaTotal']
                            $prima = "$" . $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['PrimaTotal'];
                            $auto->prima = $prima;

                            //update fecha termino $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['FechaTermino']
                            $fecha_termino = $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['FechaTermino'];
                            $auto->fecha_vencimiento = $fecha_termino;
                            $token = $this->getToken();

                            $auto->link_polisa = "https://negociosuat.mapfre.com.mx/vip/emision/PolizaAlfaS.aspx?poli=4012100088353&amp;strEndoso=0&amp;NMI=1&amp;token=$token";

                            //update pago to true
                            $auto->pago = true;
                            $auto->save();

                            try {
                                // $mail_body = array(
                                //     'first_name' => $auto->nombre . ' ' . $auto->apellido_paterno,
                                //     'email' => $auto->email,
                                //     'auto' => $auto->marca,
                                //     'modelo' => $auto->modelo,
                                //     'link' => '',
                                //     'email_from' => 'noreply_villagomezseguros@procelti.com'
                                // );

                                // $template = $this->build('emails.emision_pay', $mail_body);
                                // $mg = Mailgun::create(
                                //     env('MAILGUN_SECRET'),
                                //     'https://api.eu.mailgun.net'
                                // ); // For EU serverss
                                // $mg->messages()->send('procelti.com', [
                                //     'from'    => 'AutoCotizador Villagomez<' . $mail_body['email_from'] . '>',
                                //     'to'      => $auto->email,
                                //     'subject' => 'Hola ' . $mail_body['first_name'],
                                //     'html'    => $template
                                // ]);
                            } catch (Exception $e) {
                                return response()->json(['error' => $e->getMessage()], 500);
                            }

                            return response()->json([
                                "status" => "success",
                                "data" => $response,
                                "auto" => $auto
                            ]);
                        } catch (\Exception $e) {
                            //return a json with the error
                            return response()->json([
                                'status' => 'error',
                                'body' => $e->getMessage()
                            ]);
                        }
                        return response()->json([
                            "status" => "success",
                            "data" => $response,
                            "auto" => $auto
                        ]);
                    } catch (SoapFault $th) {
                        return response()->json([
                            "status" => "error",
                            // "token" => $this->token,
                            "data" => $th->getMessage()
                        ]);
                    }
                } else {


                    // $search is equals to upercase of $smarca;
                    $marca_result = array();
                    $c = 0;

                    //$modelo is ($auto->modelo) parse to int
                    $modelo = (int)$auto->modelo;

                    //get date with -5 hours of difference and format day/month/year
                    $date = new DateTime();
                    $date->sub(new DateInterval('PT5H'));

                    //change - to / in date and remove h:i:s
                    $date = str_replace('-', '/', $date->format('d-m-Y'));

                    //get date as ddmmaaaa
                    $date_dma = str_replace('/', '', $date);

                    //get date with a year of difference and format day/month/year
                    $date_year = new DateTime();
                    $date_year->sub(new DateInterval('PT5H'));
                    $date_year->add(new DateInterval('P1Y'));
                    $date_year = str_replace('-', '/', $date_year->format('d-m-Y'));



                    //create a $random number and string characters
                    $random = rand(0, 99999);
                    $random = str_pad($random, 5, '0', STR_PAD_LEFT);

                    //random string with 2 characters
                    $random_string = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2) . $random;


                    $tipo_persona = $this->checkPersonaInverse($auto->fis_mor);
                    $benefi_tipo_persona = $this->checkPersonaInverse($auto->benefi_tipo_persona);
                    $benefi_genre = 0;

                    if ($auto->benefi_sexo == 'MASCULINO') {
                        $benefi_genre = 1;
                    } else {
                        $benefi_genre = 0;
                    }

                    //emissions
                    $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                            <soapenv:Header/>
                            <soapenv:Body>
                                <tem:WS_TW_AEmite>
                                    <!--Optional:-->
                                    <tem:xml><![CDATA[<XML><SEGURIDAD><USER ID="GMAC0106" PWD="MXMIFNLKYZAQH5PRPHRLLA=="/></SEGURIDAD><DATA><POLIZA><DATOS_POLIZA ID_NEGOCIO="ZONA_ALIADOS" NUM_POLIZA_GRUPO="4SEGA05400001" NUM_CONTRATO="40020" COD_SECTOR="4" COD_RAMO="401" FEC_EFEC_POLIZA="' . $date . '" FEC_VCTO_POLIZA="' . $date_year . '" COD_FRACC_PAGO="1" COD_CUADRO_COM="1" COD_AGT="29709" COD_USR="GMAC0106" COD_NIVEL3_CAPTURA="0" TIP_DOCUM="RFC" COD_DOCUM="' . $auto->rfc . '" COD_ESTADO="' . $auto->clave_estado . '" COD_PROV="' . $auto->clave_municipio . '" PCT_AGT="100" COD_GESTOR="29709" TIP_GESTOR="AG"/><DATOS_VARIABLES NUM_RIESGO="1" TIP_NIVEL="3"><CAMPO COD_CAMPO="COD_MATERIA" VAL_CAMPO="1"/></DATOS_VARIABLES><DATOS_VARIABLES44 TIP_NIVEL="2" NUM_RIESGO="1"><CAMPO9 VAL_CAMPO="' . $auto->serie . '" COD_CAMPO="NUM_SERIE"/><CAMPO11 VAL_CAMPO="40999" COD_CAMPO="COD_MODALIDAD"/><CAMPO12 VAL_CAMPO="' . $auto->modelo . '" COD_CAMPO="ANIO_FABRICA"/><CAMPO13 VAL_CAMPO="' . $auto->mapfre_marca_code . '" COD_CAMPO="COD_MARCA"/><CAMPO14 VAL_CAMPO="' . $auto->mapfre_modelo_code . '" COD_CAMPO="COD_MODELO"/><CAMPO15 VAL_CAMPO="1" COD_CAMPO="COD_TIP_VEHI"/><CAMPO16 VAL_CAMPO="464" COD_CAMPO="COD_USO_VEHI"/><CAMPO17 VAL_CAMPO="N" COD_CAMPO="MCA_FACTURA"/><CAMPO18 VAL_CAMPO="N" COD_CAMPO="MCA_ACTUAL"/><CAMPO19 VAL_CAMPO="S" COD_CAMPO="MCA_COMERCIAL"/><CAMPO20 VAL_CAMPO="N" COD_CAMPO="MCA_COMERCIAL10"/><CAMPO21 VAL_CAMPO="' . $auto->placa . '" COD_CAMPO="NUM_MATRICULA"/><CAMPO22 VAL_CAMPO="' . $auto->motor . '" COD_CAMPO="NUM_MOTOR"/><CAMPO23 VAL_CAMPO="' . $auto->numero_pasajeros . '" COD_CAMPO="NUM_PASAJEROS"/><CAMPO24 VAL_CAMPO="350000" COD_CAMPO="VAL_FACTURA"/><CAMPO25 VAL_CAMPO="' . $random_string . '" COD_CAMPO="NUM_FACTURA"/><CAMPO26 VAL_CAMPO="' . $date_dma . '" COD_CAMPO="FEC_FACTURA"/><CAMPO27 VAL_CAMPO="0" COD_CAMPO="VAL_ACTUAL"/></DATOS_VARIABLES44><DATOS_VARIABLES49 TIP_NIVEL="1" NUM_RIESGO="0"><CAMPO44 VAL_CAMPO="999" COD_CAMPO="COD_BONI_RECA"/><CAMPO46 VAL_CAMPO="-23" COD_CAMPO="PCT_COD_REC_ESP"/><CAMPO47 VAL_CAMPO="100" COD_CAMPO="PCT_CESION_COM_AGT"/><CAMPO48 VAL_CAMPO="1" COD_CAMPO="MEDIO_CAPTACION"/><CAMPO70 VAL_CAMPO="EMPLE123" COD_CAMPO="NUM_EMPLEADO"/></DATOS_VARIABLES49><COBERTURAS><COBERTURA COD_FRANQUICIA="5" SUMA_ASEG="C" COD_COB="4000"/><COBERTURA51 COD_FRANQUICIA="10" SUMA_ASEG="C" COD_COB="4001"/><COBERTURA52 SUMA_ASEG="1500000" COD_COB="4010"/><COBERTURA53 SUMA_ASEG="1500000" COD_COB="4011"/><COBERTURA54 SUMA_ASEG="200000" COD_COB="4006"/><COBERTURA55 SUMA_ASEG="1" COD_COB="4003"/><COBERTURA56 SUMA_ASEG="1" COD_COB="4004"/><COBERTURA57 SUMA_ASEG="0" COD_COB="4012" COD_FRANQUICIA="0"/><COBERTURA59 SUMA_ASEG="100000" COD_COB="4013"/><COBERTURA60 SUMA_ASEG="0" COD_COB="4022" COD_FRANQUICIA="0"/><COBERTURA62 SUMA_ASEG="0" COD_COB="4024"/><COBERTURA64 SUMA_ASEG="0" COD_COB="4028" COD_FRANQUICIA="0"/><COBERTURA68 SUMA_ASEG="0" COD_COB="4068"/></COBERTURAS><TERCEROS><CONTRATANTE COD_PROV="' . $auto->clave_municipio . '" COD_ESTADO="' . $auto->clave_estado . '" COD_DOCUM="' . $auto->rfc . '" MODIFICADO="N" TLF_MOVIL="' . $auto->telefono . '" EMAIL="' . $auto->email . '" TLF_NUMERO="' . $auto->telefono . '" COD_POSTAL="' . $auto->codigo_postal . '" FEC_NACIMIENTO="' . $auto->fecha_nacimiento . '" MCA_SEXO="" COD_LOCALIDAD="' . $auto->clave_municipio . '" NOM_DOMICILIO3="' . $auto->direccion . '" NOM_DOMICILIO1="' . $auto->municipio . '" NOM_TERCERO="' . $auto->razon_social . '" APE2_TERCERO="' . $auto->apellido_paterno . '" APE1_TERCERO="' . $auto->apellido_materno . '" MCA_FISICO="' . $tipo_persona . '" /><CONDUCTOR COD_PROV="' . $auto->clave_municipio . '" COD_ESTADO="' . $auto->clave_estado . '" COD_DOCUM="' . $auto->rfc . '" MODIFICADO="N" TLF_MOVIL="' . $auto->telefono . '" EMAIL="' . $auto->email . '" TLF_NUMERO="' . $auto->telefono . '" COD_POSTAL="' . $auto->codigo_postal . '" FEC_NACIMIENTO="' . $auto->fecha_nacimiento . '" MCA_SEXO="" COD_LOCALIDAD="' . $auto->clave_municipio . '" NOM_DOMICILIO3="' . $auto->direccion . '" NOM_DOMICILIO1="' . $auto->municipio . '" NOM_TERCERO="' . $auto->razon_social . '" APE2_TERCERO="' . $auto->apellido_paterno . '" APE1_TERCERO="' . $auto->apellido_materno . '" MCA_FISICO="' . $tipo_persona . '"/></TERCEROS></POLIZA></DATA></XML>]]></tem:xml>
                                    <!--Optional:-->
                                    <tem:token>' . $this->token  . '</tem:token>
                                </tem:WS_TW_AEmite>
                            </soapenv:Body>
                        </soapenv:Envelope>';

                    // return $soap;


                    try {

                        $client = NClientesModel::where('cliente_id', $auto->cliente_id)->first();
                        //check if tipo_cliente is publico
                        if ($client->tipo_cliente == 'Público') {
                            $client = new nusoap_client($this->url, true, false, false, false, false, 0, 9600);
                            $result = $client->send(
                                $soap,
                                'http://tempuri.org/WS_TW_AEmite',
                                0,
                                18600
                            );
                            // return $client;

                            // return $result;
                            $response = json_decode(json_encode($result), true);
                        }


                        try {
                            //update auto id_poliza from $response $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['Poliza']
                            $poliza = $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['Poliza'];
                            $auto->id_polisa = $poliza;
                            $historial = CotizacionHistorialModel::where('auto_id', $request->input('auto_id'))->first();
                            $historial->poliza_id = $poliza;
                            $historial->save();
                            //update prima $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['PrimaTotal']
                            $prima = "$" . $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['PrimaTotal'];
                            $auto->prima = $prima;

                            //update fecha termino $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['FechaTermino']
                            $fecha_termino = $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['FechaTermino'];
                            $auto->fecha_vencimiento = $fecha_termino;
                            $token = $this->getToken();

                            $auto->link_polisa = "https://negociosuat.mapfre.com.mx/vip/emision/PolizaAlfaS.aspx?poli=4012100088353&amp;strEndoso=0&amp;NMI=1&amp;token=$token";

                            //update pago to true
                            $auto->pago = true;
                            $auto->save();

                            try {
                                // $mail_body = array(
                                //     'first_name' => $auto->nombre . ' ' . $auto->apellido_paterno,
                                //     'email' => $auto->email,
                                //     'auto' => $auto->marca,
                                //     'modelo' => $auto->modelo,
                                //     'link' => '',
                                //     'email_from' => 'noreply_villagomezseguros@procelti.com'
                                // );

                                // $template = $this->build('emails.emision_pay', $mail_body);
                                // $mg = Mailgun::create(
                                //     env('MAILGUN_SECRET'),
                                //     'https://api.eu.mailgun.net'
                                // ); // For EU serverss
                                // $mg->messages()->send('procelti.com', [
                                //     'from'    => 'AutoCotizador Villagomez<' . $mail_body['email_from'] . '>',
                                //     'to'      => $auto->email,
                                //     'subject' => 'Hola ' . $mail_body['first_name'],
                                //     'html'    => $template
                                // ]);
                            } catch (Exception $e) {
                                return response()->json(['error' => $e->getMessage()], 500);
                            }

                            return response()->json([
                                "status" => "success",
                                "data" => $response,
                                "auto" => $auto
                            ]);
                        } catch (\Exception $e) {
                            //return a json with the error
                            return response()->json([
                                'status' => 'error',
                                'body' => $e->getMessage()
                            ]);
                        }
                        return response()->json([
                            "status" => "success",
                            "data" => $response,
                            "auto" => $auto
                        ]);
                    } catch (SoapFault $th) {
                        return response()->json([
                            "status" => "error",
                            // "token" => $this->token,
                            "data" => $th->getMessage()
                        ]);
                    }
                }
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    public function getToken()
    {
        $user = 'SEGUROS AUTOS';
        $token_url = "https://negociosuat.mapfre.com.mx/wsexchange_s/apptoken.asmx ";

        $soap = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <ObtenToken_ws xmlns="http://tempuri.org/">
              <app_name>' . $user . '</app_name>
            </ObtenToken_ws>
          </soap:Body>
        </soap:Envelope>';

        $client = new nusoap_client($token_url, true, false, false, false, false, 0, 9600);
        $result = $client->send(
            $soap,
            'http://tempuri.org/ObtenToken_ws',
            0,
            18600
        );
        // return $client;

        $response = json_decode(json_encode($result), true);
        return $result["ObtenToken_wsResult"]["TokenGenerado"];
    }


    //fuction to emitir poliza from NAutoModel
    public function emitirPolizaFromNAutoModel(Request $request)
    {

        try {
        } catch (SoapFault $th) {
            return response()->json([
                "status" => "error",
                // "token" => $this->token,
                "data" => $th->getMessage()
            ]);
        }
    }

    //function to check a String if is "S" return "persona fisica" or is "N" return "persona moral"
    public function checkPersona($string)
    {
        if ($string == "S") {
            return "PERSONA FISICA";
        } else {
            return "PERSONA MORAL";
        }
    }

    //function to check a String if is "PERSONA FISICA" return "S" or is "PERSONA MORAL" return "N"
    public function checkPersonaInverse($string)
    {
        if ($string == "PERSONA FISICA") {
            return "S";
        } else {
            return "N";
        }
    }

    public function checkSexo($genero)
    {
        if ($genero == 0) {
            return "FEMENINO";
        } else {
            return "MASCULINO";
        }
    }


    public function setCotizacionXML(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {


            try {
                $marcas = $this->getMarcasInt($request->modelo);
                $marcas = json_decode($marcas->getContent(), true);
                $marcas = $marcas['data']['WS_TW_MarcasResult']['xml']['data']['lista'];



                // $search is equals to upercase of $smarca;
                $marca_result = array();
                $c = 0;

                //search $request->marca in $marcas
                foreach ($marcas as $marca) {

                    $marca_int = $request->marca;

                    if ($marca_int == "FORD") {
                        $marca_int = "FORD FR";
                    }

                    if (strtoupper($marca['NOM_MARCA']) == strtoupper($marca_int)) {
                        $marca_result[$c] = $marca;
                        $c++;
                    }
                }



                $modelos = $this->getModelosInt($marca_result[0]['COD_MARCA'], $request->modelo);
                $modelos = json_decode($modelos->getContent(), true);
                if ($modelos['data']['WS_TW_ModelosResult']['xml']['data'] == "") {
                    return response()->json([
                        "status" => "error",
                        "data" => "Modelo no encontrado",
                    ]);
                }
                $modelos = $modelos['data']['WS_TW_ModelosResult']['xml']['data']['lista'];

                $modelo_result = array();
                $c = 0;
                foreach ($modelos as $modelo) {
                    similar_text($modelo["NOM_MODELO"], $request->descripcion, $percent);
                    $modelo_result[$c] = array(
                        "modelo" => $modelo,
                        "percent" => $percent
                    );
                    $c++;
                }



                //get percent max and get modelo of modelo_result
                $modelo_max = array();
                $c = 0;
                foreach ($modelo_result as $modelo) {
                    if ($c == 0) {
                        $modelo_max = $modelo;
                        $c++;
                    } else {
                        if ($modelo['percent'] > $modelo_max['percent']) {
                            $modelo_max = $modelo;
                        }
                    }
                }


                //get date with -5 hours of difference and format day/month/year
                $date = new DateTime();
                $date->sub(new DateInterval('PT5H'));

                //change - to / in date and remove h:i:s
                $date = str_replace('-', '/', $date->format('d-m-Y'));

                //get date with a year of difference and format day/month/year
                $date_year = new DateTime();
                $date_year->sub(new DateInterval('PT5H'));
                $date_year->add(new DateInterval('P1Y'));
                $date_year = str_replace('-', '/', $date_year->format('d-m-Y'));

                //create a $random number and string characters
                $random = rand(0, 99999);
                $random = str_pad($random, 5, '0', STR_PAD_LEFT);

                if ($request->cliente_id == null) {
                    //get gs_discount from admin where admin_id = 1
                    // $discount = AdminModel::where('admin_id', 1)->first()->mapfre_descuento;
                    //return message to login
                    return response()->json([
                        "status" => "error",
                        "data" => "No se ha encontrado el cliente, debes iniciar sesión para poder realizar una solicitud",
                    ], 201);
                } else {
                    $descuento = MantenimientoModel::where("provider", "mapfre")->first()->descuento;
                    if ($descuento == 1) {
                        //get client with client_id = $request->cliente_id
                        $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
                        //check if tipo_cliente is publico
                        if ($cliente->tipo_cliente == 'Público') {
                            //get gs_discount from admin where admin_id = 1
                            $discount = AdminModel::where('admin_id', 1)->first()->mapfre_descuento;
                        } else {
                            //get gs_discount from client where client_id = $request->cliente_id
                            $discount = $cliente->mapfre_descuento;
                        }
                    } else {
                        $discount = 0;
                    }
                }
                //check if $client->genero is MASCULINO then $genre = 1
                $genre = 0;
                if ($client->genero == 'Masculino') {
                    $genre = 1;
                } else {
                    $genre = 0;
                }

                $mca_fisico = "S";
                if ($client->fis_mor == 'FISICA') {
                    $mca_fisico = "S";
                } else {
                    $mca_fisico = "N";
                }


                //clear "/" of $client->fecha_nacimiento and format day/month/year
                $fecha_nacimiento = str_replace('/', '', $client->fecha_nacimiento);
                $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                        <soapenv:Header/>
                        <soapenv:Body>
                            <tem:WS_TW_ACotiza>
                                <!--Optional:-->
                                <tem:xml><![CDATA[<XML><SEGURIDAD><USER ID="GMAC0106" PWD="MXMIFNLKYZAQH5PRPHRLLA=="/></SEGURIDAD><DATA><COTIZACION><DATOS_POLIZA ID_NEGOCIO="ZONA_ALIADOS" NUM_POLIZA_GRUPO="4SEGA05400001" NUM_CONTRATO="40020" COD_SECTOR="4" COD_RAMO="401" FEC_EFEC_POLIZA="' . $date . '" FEC_VCTO_POLIZA="' . $date_year . '" COD_FRACC_PAGO="1" COD_CUADRO_COM="1" COD_AGT="29709" COD_USR="GMAC0106" COD_NIVEL3_CAPTURA="0" TIP_DOCUM="RFC" COD_DOCUM="PRUEBA" COD_ESTADO="16" COD_PROV="16072" PCT_AGT="100" COD_GESTOR="29709" TIP_GESTOR="AG"/><DATOS_VARIABLES NUM_RIESGO="1" TIP_NIVEL="3"><CAMPO COD_CAMPO="COD_MATERIA" VAL_CAMPO="1"/></DATOS_VARIABLES><DATOS_VARIABLES19 NUM_RIESGO="1" TIP_NIVEL="2"><CAMPO9 COD_CAMPO="COD_MODALIDAD" VAL_CAMPO="40999"/><CAMPO11 COD_CAMPO="ANIO_FABRICA" VAL_CAMPO="' . $request->modelo . '"/><CAMPO12 COD_CAMPO="COD_MARCA" VAL_CAMPO="' . $marca_result[0]['COD_MARCA'] . '"/><CAMPO13 COD_CAMPO="COD_MODELO" VAL_CAMPO="' . $modelo_max['modelo']['COD_MODELO'] . '"/><CAMPO14 COD_CAMPO="COD_TIP_VEHI" VAL_CAMPO="1"/><CAMPO15 COD_CAMPO="COD_USO_VEHI" VAL_CAMPO="464"/><CAMPO16 COD_CAMPO="MCA_FACTURA" VAL_CAMPO="N"/><CAMPO17 COD_CAMPO="MCA_ACTUAL" VAL_CAMPO="N"/><CAMPO18 COD_CAMPO="MCA_COMERCIAL" VAL_CAMPO="S"/><CAMPO20 COD_CAMPO="MCA_COMERCIAL10" VAL_CAMPO="N"/><CAMPO21 COD_CAMPO="FEC_FACTURA" VAL_CAMPO="10052022"/><CAMPO22 COD_CAMPO="NUM_FACTURA" VAL_CAMPO="ABCD1234"/><CAMPO23 COD_CAMPO="VAL_FACTURA" VAL_CAMPO="350000"/><CAMPO24 COD_CAMPO="NUM_PASAJEROS" VAL_CAMPO="5"/><CAMPO25 COD_CAMPO="NUM_SERIE" VAL_CAMPO=""/></DATOS_VARIABLES19><DATOS_VARIABLES22 NUM_RIESGO="0" TIP_NIVEL="1"><CAMPO19 COD_CAMPO="COD_BONI_RECA" VAL_CAMPO="999"/><CAMPO21 COD_CAMPO="PCT_COD_REC_ESP" VAL_CAMPO="-23"/></DATOS_VARIABLES22><COBERTURAS><COBERTURA COD_COB="4000" SUMA_ASEG="C" COD_FRANQUICIA="5"/><COBERTURA24 COD_COB="4001" SUMA_ASEG="C" COD_FRANQUICIA="10"/><COBERTURA25 COD_COB="4010" SUMA_ASEG="1500000"/><COBERTURA26 COD_COB="4011" SUMA_ASEG="1500000"/><COBERTURA27 COD_COB="4006" SUMA_ASEG="200000"/><COBERTURA28 COD_COB="4003" SUMA_ASEG="1"/><COBERTURA29 COD_COB="4004" SUMA_ASEG="1"/><COBERTURA30 COD_COB="4012" SUMA_ASEG="0" COD_FRANQUICIA="0"/><COBERTURA32 COD_COB="4013" SUMA_ASEG="100000"/><COBERTURA33 COD_COB="4022" SUMA_ASEG="0" COD_FRANQUICIA="0"/><COBERTURA35 COD_COB="4024" SUMA_ASEG="0"/><COBERTURA37 COD_COB="4028" SUMA_ASEG="0" COD_FRANQUICIA="0"/><COBERTURA45 COD_COB="4068" SUMA_ASEG="0"/></COBERTURAS><DATOS_CH_TMV51> <CAMPO52 COD_CAMPO="MCA_SEXO" VAL_CAMPO="' . $genre . '" /><CAMPO53 COD_CAMPO="COD_ESTADO" VAL_CAMPO="' . $client->cod_estado . '" /><CAMPO54 COD_CAMPO="COD_PROV" VAL_CAMPO="' . $client->cod_municipio . '" /><CAMPO55 COD_CAMPO="FEC_NACIMIENTO" VAL_CAMPO="' . $fecha_nacimiento . '" /><CAMPO56 COD_CAMPO="COD_POSTAL" VAL_CAMPO="' . $client->cod_postal . '" /><CAMPO57 COD_CAMPO="RFC" VAL_CAMPO="' . $client->rfc . '" /><CAMPO58 COD_CAMPO="NOM_TERCERO" VAL_CAMPO="' . $client->nombre . '" /><CAMPO59 COD_CAMPO="APE1_TERCERO" VAL_CAMPO="' . $client->apellido_paterno . '" /><CAMPO60 COD_CAMPO="APE2_TERCERO" VAL_CAMPO="' . $client->apellido_materno . '" /><CAMPO61 COD_CAMPO="MCA_FISICO" VAL_CAMPO="' . $mca_fisico . '" /></DATOS_CH_TMV51></COTIZACION></DATA></XML>]]></tem:xml>
                                <!--Optional:-->
                                <tem:token>' . $this->token . '</tem:token>
                            </tem:WS_TW_ACotiza>
                        </soapenv:Body>
                    </soapenv:Envelope>';
                // return $soap;
            } catch (\Exception $e) {
                return response()->json([
                    "status" => "error",
                    "data" => $e->getMessage(),
                ]);
            }

            try {
                $client = new nusoap_client($this->url, true);
                $result = $client->send(
                    $soap,
                    'http://tempuri.org/WS_TW_ACotiza'
                );

                // return $soap;
                $response = json_decode(json_encode($result), true);

                return response()->json([
                    "status" => "success",
                    "descuento" => $discount,
                    "data" => $response,
                ]);
            } catch (\Exception $th) {
                return response()->json([
                    "status" => "error",
                    "data" => $th,
                ]);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    public function setEmisionXML(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            $marcas = $this->getMarcasInt($request->modelo);
            $marcas = json_decode($marcas->getContent(), true);
            $marcas = $marcas['data']['WS_TW_MarcasResult']['xml']['data']['lista'];

            // $search is equals to upercase of $smarca;
            $marca_result = array();
            $c = 0;

            // get cliente from DB
            $cliente = NClientesModel::find($request->cliente_id)->first();

            //check if $client->genero is MASCULINO then $genre = 1
            $genre = 0;
            if ($cliente->genero == 'Masculino') {
                $genre = 1;
            } else {
                $genre = 0;
            }

            //search $request->marca in $marcas
            foreach ($marcas as $marca) {

                $marca_int = $request->marca;

                if ($marca_int == "FORD") {
                    $marca_int = "FORD FR";
                }

                if (strtoupper($marca['NOM_MARCA']) == strtoupper($marca_int)) {
                    $marca_result[$c] = $marca;
                    $c++;
                }
            }

            $modelos = $this->getModelosInt($marca_result[0]['COD_MARCA'], $request->modelo);
            $modelos = json_decode($modelos->getContent(), true);
            if ($modelos['data']['WS_TW_ModelosResult']['xml']['data'] == "") {
                return response()->json([
                    "status" => "error",
                    "data" => "Modelo no encontrado",
                ]);
            }
            $modelos = $modelos['data']['WS_TW_ModelosResult']['xml']['data']['lista'];

            $modelo_result = array();
            $c = 0;
            foreach ($modelos as $modelo) {
                similar_text($modelo["NOM_MODELO"], $request->descripcion, $percent);
                $modelo_result[$c] = array(
                    "modelo" => $modelo,
                    "percent" => $percent
                );
                $c++;
            }

            //get percent max and get modelo of modelo_result
            $modelo_max = array();
            $c = 0;
            foreach ($modelo_result as $modelo) {
                if ($c == 0) {
                    $modelo_max = $modelo;
                    $c++;
                } else {
                    if ($modelo['percent'] > $modelo_max['percent']) {
                        $modelo_max = $modelo;
                    }
                }
            }

            //get date with -5 hours of difference and format day/month/year
            $date = new DateTime();
            $date->sub(new DateInterval('PT5H'));

            //change - to / in date and remove h:i:s
            $date = str_replace('-', '/', $date->format('d-m-Y'));

            //get date as ddmmaaaa
            $date_dma = str_replace('/', '', $date);

            //get date with a year of difference and format day/month/year
            $date_year = new DateTime();
            $date_year->sub(new DateInterval('PT5H'));
            $date_year->add(new DateInterval('P1Y'));
            $date_year = str_replace('-', '/', $date_year->format('d-m-Y'));



            //create a $random number and string characters
            $random = rand(0, 99999);
            $random = str_pad($random, 5, '0', STR_PAD_LEFT);

            //random string with 2 characters
            $random_string = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2) . $random;




            //emissions
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:WS_TW_AEmite>
                            <!--Optional:-->
                            <tem:xml><![CDATA[<XML><SEGURIDAD><USER ID="GMAC0106" PWD="MXMIFNLKYZAQH5PRPHRLLA=="/></SEGURIDAD><DATA><POLIZA><DATOS_POLIZA ID_NEGOCIO="ZONA_ALIADOS" NUM_POLIZA_GRUPO="4SEGA05400001" NUM_CONTRATO="40020" COD_SECTOR="4" COD_RAMO="401" FEC_EFEC_POLIZA="' . $date . '" FEC_VCTO_POLIZA="' . $date_year . '" COD_FRACC_PAGO="1" COD_CUADRO_COM="1" COD_AGT="29709" COD_USR="GMAC0106" COD_NIVEL3_CAPTURA="0" TIP_DOCUM="RFC" COD_DOCUM="' . $request->rfc . '" COD_ESTADO="' . $request->cod_estado . '" COD_PROV="' . $request->cod_provincia . '" PCT_AGT="100" COD_GESTOR="29709" TIP_GESTOR="AG"/><DATOS_VARIABLES NUM_RIESGO="1" TIP_NIVEL="3"><CAMPO COD_CAMPO="COD_MATERIA" VAL_CAMPO="1"/></DATOS_VARIABLES><DATOS_VARIABLES44 TIP_NIVEL="2" NUM_RIESGO="1"><CAMPO9 VAL_CAMPO="' . $request->num_serie . '" COD_CAMPO="NUM_SERIE"/><CAMPO11 VAL_CAMPO="40999" COD_CAMPO="COD_MODALIDAD"/><CAMPO12 VAL_CAMPO="' . $request->modelo . '" COD_CAMPO="ANIO_FABRICA"/><CAMPO13 VAL_CAMPO="' . $marca_result[0]['COD_MARCA'] . '" COD_CAMPO="COD_MARCA"/><CAMPO14 VAL_CAMPO="' . $modelo_max['modelo']['COD_MODELO'] . '" COD_CAMPO="COD_MODELO"/><CAMPO15 VAL_CAMPO="1" COD_CAMPO="COD_TIP_VEHI"/><CAMPO16 VAL_CAMPO="464" COD_CAMPO="COD_USO_VEHI"/><CAMPO17 VAL_CAMPO="N" COD_CAMPO="MCA_FACTURA"/><CAMPO18 VAL_CAMPO="N" COD_CAMPO="MCA_ACTUAL"/><CAMPO19 VAL_CAMPO="S" COD_CAMPO="MCA_COMERCIAL"/><CAMPO20 VAL_CAMPO="N" COD_CAMPO="MCA_COMERCIAL10"/><CAMPO21 VAL_CAMPO="' . $request->num_matricula . '" COD_CAMPO="NUM_MATRICULA"/><CAMPO22 VAL_CAMPO="' . $request->num_motor . '" COD_CAMPO="NUM_MOTOR"/><CAMPO23 VAL_CAMPO="' . $request->num_pasajeros . '" COD_CAMPO="NUM_PASAJEROS"/><CAMPO24 VAL_CAMPO="350000" COD_CAMPO="VAL_FACTURA"/><CAMPO25 VAL_CAMPO="' . $random_string . '" COD_CAMPO="NUM_FACTURA"/><CAMPO26 VAL_CAMPO="' . $date_dma . '" COD_CAMPO="FEC_FACTURA"/><CAMPO27 VAL_CAMPO="0" COD_CAMPO="VAL_ACTUAL"/></DATOS_VARIABLES44><DATOS_VARIABLES49 TIP_NIVEL="1" NUM_RIESGO="0"><CAMPO44 VAL_CAMPO="999" COD_CAMPO="COD_BONI_RECA"/><CAMPO46 VAL_CAMPO="-23" COD_CAMPO="PCT_COD_REC_ESP"/><CAMPO47 VAL_CAMPO="100" COD_CAMPO="PCT_CESION_COM_AGT"/><CAMPO48 VAL_CAMPO="1" COD_CAMPO="MEDIO_CAPTACION"/><CAMPO70 VAL_CAMPO="EMPLE123" COD_CAMPO="NUM_EMPLEADO"/></DATOS_VARIABLES49><COBERTURAS><COBERTURA COD_FRANQUICIA="5" SUMA_ASEG="C" COD_COB="4000"/><COBERTURA51 COD_FRANQUICIA="10" SUMA_ASEG="C" COD_COB="4001"/><COBERTURA52 SUMA_ASEG="1500000" COD_COB="4010"/><COBERTURA53 SUMA_ASEG="1500000" COD_COB="4011"/><COBERTURA54 SUMA_ASEG="200000" COD_COB="4006"/><COBERTURA55 SUMA_ASEG="1" COD_COB="4003"/><COBERTURA56 SUMA_ASEG="1" COD_COB="4004"/><COBERTURA57 SUMA_ASEG="0" COD_COB="4012" COD_FRANQUICIA="0"/><COBERTURA59 SUMA_ASEG="100000" COD_COB="4013"/><COBERTURA60 SUMA_ASEG="0" COD_COB="4022" COD_FRANQUICIA="0"/><COBERTURA62 SUMA_ASEG="0" COD_COB="4024"/><COBERTURA64 SUMA_ASEG="0" COD_COB="4028" COD_FRANQUICIA="0"/><COBERTURA68 SUMA_ASEG="0" COD_COB="4068"/></COBERTURAS><TERCEROS><CONTRATANTE COD_PROV="' . $request->cod_provincia . '" COD_ESTADO="' . $request->cod_estado . '" COD_DOCUM="' . $request->rfc . '" MODIFICADO="N" TLF_MOVIL="' . $request->telefono . '" EMAIL="' . $request->email . '" TLF_NUMERO="' . $request->telefono . '" COD_POSTAL="' . $request->cod_postal . '" FEC_NACIMIENTO="' . $cliente->fecha_nacimiento . '" MCA_SEXO="' . $genre . '" COD_LOCALIDAD="' . $request->cod_provincia . '" NOM_DOMICILIO3="' . $request->direccion . '" NOM_DOMICILIO1="' . $request->municipio . '" NOM_TERCERO="' . $request->nombre . '" APE2_TERCERO="' . $request->apellido_p . '" APE1_TERCERO="' . $request->apellido_m . '" MCA_FISICO="' . $request->tipo_persona . '" /><CONDUCTOR COD_PROV="' . $request->cod_provincia . '" COD_ESTADO="' . $request->cod_estado . '" COD_DOCUM="' . $request->rfc . '" MODIFICADO="N" TLF_MOVIL="' . $request->telefono . '" EMAIL="' . $request->email . '" TLF_NUMERO="' . $request->telefono . '" COD_POSTAL="' . $request->cod_postal . '" FEC_NACIMIENTO="' . $cliente->fecha_nacimiento . '" MCA_SEXO="' . $genre . '" COD_LOCALIDAD="' . $request->cod_provincia . '" NOM_DOMICILIO3="' . $request->direccion . '" NOM_DOMICILIO1="' . $request->municipio . '" NOM_TERCERO="' . $request->nombre . '" APE2_TERCERO="' . $request->apellido_p . '" APE1_TERCERO="' . $request->apellido_m . '" MCA_FISICO="' . $request->tipo_persona . '"/></TERCEROS></POLIZA></DATA></XML>]]></tem:xml>
                            <!--Optional:-->
                            <tem:token>' . $this->token  . '</tem:token>
                        </tem:WS_TW_AEmite>
                    </soapenv:Body>
                </soapenv:Envelope>';


            // return $soap;

            try {

                $client = NClientesModel::where('cliente_id', $request->cliente_id)->first();
                //check if tipo_cliente is publico
                if ($client->tipo_cliente == 'Público') {
                    $client = new nusoap_client($this->url, true, false, false, false, false, 0, 9600);
                    $result = $client->send(
                        $soap,
                        'http://tempuri.org/WS_TW_AEmite',
                        0,
                        18600
                    );

                    // return $result;
                    $response = json_decode(json_encode($result), true);
                }

                try {
                    //create NAutoModel from migration nautos
                    $auto = NAutoModel::create([
                        'cliente_id' => $request->input('cliente_id'),
                        'nombre' => $request->input('nombre'),
                        'apellido_paterno' => $request->input('apellido_p'),
                        'apellido_materno' => $request->input('apellido_m'),
                        'telefono' => $request->input('telefono'),
                        'email' => $request->input('email'),
                        'rfc' => $request->input('rfc'),
                        'genero' =>  $this->checkSexo($request->input('sexo')),
                        'curp' => "",
                        'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                        'edo_civil' => "",
                        'fis_mor' => $this->checkPersona($request->input('tipo_persona')),
                        'razon_social' => "",
                        'nombre_comercial' => "",
                        'codigo_postal' => $request->input('cod_postal'),
                        'estado' => $request->input('estado'),
                        'municipio' => $request->input('municipio'),
                        'direccion' => $request->input('direccion'),
                        'clave_estado' => $request->input('cod_estado'),
                        'clave_municipio' => $request->input('cod_provincia'),
                        'numero_pasajeros' => $request->input('num_pasajeros'),
                        'marca' => $request->input('marca'),
                        'submarca' => "",
                        'modelo' => $request->input('modelo'),
                        'placa' => $request->input('num_matricula'),
                        'motor' => $request->input('num_motor'),
                        'serie' => $request->input('num_serie'),
                        'id_polisa' => $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['Poliza'],
                        'provedor' => "MAPFRE",
                        'prima' => $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['PrimaTotal'],
                        'pago' => false,
                        'link_pago' => "",
                        'link_polisa' => "",
                        'fecha_vencimiento' => $response['WS_TW_AEmiteResult']['xml']['data']['Recibos']['Recibo']['FechaTermino'],
                        'benefi_cod_provincia' => $request->input('benefi_cod_provincia'),
                        'benefi_cod_estado' => $request->input('benefi_cod_estado'),
                        'benefi_rfc' => $request->input('benefi_rfc'),
                        'benefi_telefono1' => $request->input('benefi_telefono1'),
                        'benefi_correo' => $request->input('benefi_correo'),
                        'benefi_telefono2' => $request->input('benefi_telefono2'),
                        'benefi_cod_postal' => $request->input('benefi_cod_postal'),
                        'benefi_fecha_nacimiento' => $request->input('benefi_fecha_nacimiento'),
                        'benefi_sexo' => $this->checkSexo($request->input('benefi_sexo')),
                        'benefi_direccion1' => $request->input('benefi_direccion1'),
                        'benefi_direccion2' => $request->input('benefi_direccion2'),
                        'benefi_nombre' => $request->input('benefi_nombre'),
                        'benefi_apellido_p' => $request->input('benefi_apellido_p'),
                        'benefi_apellido_m' => $request->input('benefi_apellido_m'),
                        'benefi_tipo_persona' => $this->checkPersona($request->input('benefi_tipo_persona')),
                    ]);
                } catch (\Exception $e) {
                    //return a json with the error
                    return response()->json([
                        'status' => 'error',
                        'body' => $e->getMessage()
                    ]);
                }
                return response()->json([
                    "status" => "success",
                    "data" => $response,
                    "auto" => $auto
                ]);
            } catch (SoapFault $th) {
                return response()->json([
                    "status" => "error",
                    // "token" => $this->token,
                    "data" => $th->getMessage()
                ]);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    public function build(String $myView, array $mail_body)
    {
        return view($myView)->with([
            'first_name' => $mail_body['first_name'],
            'email' => $mail_body['email'],
            'email_from' => $mail_body['email_from'],
            //auto
            'auto' => $mail_body['auto'],
            //modelo
            'modelo' => $mail_body['modelo'],
            //link
            'link' => $mail_body['link'],
        ])->render();
    }
}

// https://negociosuat.mapfre.com.mx/VIPII/wImpresion/MarcoImpresions.aspx?Poliza=4012200003041&amp;Endoso=0&amp;Token=4aab85c8-20cb-470c-937a-5c53b5ed7757

// https://negociosuat.mapfre.com.mx/vip/emision/PolizaAlfaS.aspx?poli=4012200003041&amp;strEndoso=0&amp;NMI=1&amp;token=4aab85c8-20cb-470c-937a-5c53b5ed7757
