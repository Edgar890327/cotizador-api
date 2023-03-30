<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\AdminModel;
use App\Models\MantenimientoModel;
use App\Models\NClientesModel;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use nusoap_client;
use SoapClient;
use SoapFault;
use Throwable;

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: *');

require_once __DIR__ . '/../../../vendor/autoload.php';


class GSController extends Controller
{
    protected $opts, $params, $urlAuth, $urlCotiza, $token, $urlCat, $carCatalogUrl, $urlCober;
    private $clientAuthGS, $clientCatGS, $clientColoniasGS, $clientCotiza, $clientCober;
    public function __construct()
    {

        $this->opts = array(
            'https' => array('header' => array('Content-Type:soap+xml; charset=utf-8'))
        );
        $this->urlAuth =    env('GS_AUTOS_BASE_URL') . 'autenticacionWS?wsdl';
        $this->urlCotiza =  env('GS_AUTOS_BASE_URL') . 'cotizacionEmisionWS';
        $this->urlCat =     env('GS_AUTOS_BASE_URL') . 'catalogosWS?wsdl';
        $this->carCatalogUrl = env('GS_AUTOS_BASE_URL') . 'catalogoAutosWS?wsdl';
        $this->urlCober =   env('GS_AUTOS_BASE_URL') . 'catalogoCoberturasWS?wsdl';

        try {
            $this->clientAuthGS = $this->getClient($this->urlAuth);
            $this->clientCatGS = $this->getClient($this->carCatalogUrl);
            $this->clientColoniasGS = $this->getClient($this->urlCat);
            $this->clientCotiza = $this->getClient($this->urlCotiza);
            $this->clientCober = $this->getClient($this->urlCober);
            $this->token = $this->getToken();
        } catch (Handler $fault) {
            // dd("Fallo",$fault);
        }
    }


    public function getClient($url)
    {
        try {
            $client = new nusoap_client(
                $url,
                true,
                false,
                false,
                false,
                false,
                0,
                12600,
            );
            return $client;
        } catch (SoapFault $error) {
            printf("Error: %s", $error->getMessage());
        }
    }

    public function getToken()
    {
        $soap = '<?xml version="1.0" encoding="utf-8"?>
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
            <Body>
                <obtenerToken xmlns="http://com.gs.gsautos.ws.autenticacion">
                    <arg0 xmlns="">
                        <usuario>' . env('GS_USER') . '</usuario>
                        <password>' . env('GS_SECRET') . '</password>
                    </arg0>
                </obtenerToken>
            </Body>
        </Envelope>';

        $clientAuthGS = $this->getClient($this->urlAuth);
        $result = $clientAuthGS->send($soap);
        $response = json_decode(json_encode($result), true);
        if ($response['return']['exito']) {
            return $response['return']['token'];
        } else {
            return null;
        }
    }

    public function getMarcas(Request $request)
    {
        try {
            $soap = '<?xml version="1.0" encoding="utf-8"?>
                <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                    <Body>
                        <wsListarMarcas xmlns="http://com.gs.gsautos.ws.catalogoAutos">
                            <arg0 xmlns="">
                                <token>' . $this->getToken() . '</token>
                            </arg0>
                        </wsListarMarcas>
                    </Body>
                </Envelope>';
            $clientCatGS = $this->getClient($this->carCatalogUrl);
            $result =  $clientCatGS->send($soap);
            return $this->responseJson('marcas', $result, 0);
        } catch (SoapFault $error) {
            printf("Error: %s", $error);
        }
    }

    public function getSubMarcas(Request $request)
    {
        try {
            $data = $request->json()->all();
            $id_brand = $request->id_brand;
            $soap = '<?xml version="1.0" encoding="utf-8"?>
                <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                    <Body>
                        <wsListarSubMarcas xmlns="http://com.gs.gsautos.ws.catalogoAutos">
                            <arg0 xmlns="">
                                <token>' . $this->token . '</token>
                                <idMarca>' . $id_brand . '</idMarca>
                            </arg0>
                        </wsListarSubMarcas>
                    </Body>
                </Envelope>';
            $clientCatGS = $this->getClient($this->carCatalogUrl);
            $result =  $clientCatGS->send($soap);
            return $this->responseJson('submarcas', $result, 0);
        } catch (SoapFault $error) {
            printf("Error: %s", $error);
        }
    }

    public function getModelos(Request $request)
    {
        try {
            $data = $request->json()->all();
            echo ($request->id_sub_brand);
            $id_sub_brand = $request->id_sub_brand;
            $soap = '<?xml version="1.0" encoding="utf-8"?>
                <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                    <Body>
                        <wsListarModelos xmlns="http://com.gs.gsautos.ws.catalogoAutos">
                            <arg0 xmlns="">
                                <token>' . $this->token . '</token>
                                <idSubmarca>' . $id_sub_brand . '</idSubmarca>
                            </arg0>
                        </wsListarModelos>
                    </Body>
                </Envelope>';
            $clientCatGS = $this->getClient($this->carCatalogUrl);
            $result =  $clientCatGS->send($soap);
            return $this->responseJson('modelos', $result, 0);
        } catch (SoapFault $error) {
            printf("Error: %s", $error);
        }
    }

    public function getVersions(Request $request)
    {

        try {
            $data = $request->json()->all();
            $id_sub_brand = $request->id_sub_brand;
            $model = $data['model'];
            $soap = '<?xml version="1.0" encoding="utf-8"?>
                <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                    <Body>
                        <wsListarVersiones xmlns="http://com.gs.gsautos.ws.catalogoAutos">
                            <arg0 xmlns="">
                                <token>' . $this->token . '</token>
                                <idSubmarca>' . $id_sub_brand . '</idSubmarca>
                                <modelo>' . $model . '</modelo>
                            </arg0>
                        </wsListarVersiones>
                    </Body>
                </Envelope>';
            $clientCatGS = $this->getClient($this->carCatalogUrl);
            $result =  $clientCatGS->send($soap);

            return $this->responseJson('versiones', $result, 0);
        } catch (SoapFault $error) {
            printf("Error: %s", $error);
        }
    }

    public function getColonias($cod_postal)
    {
        try {
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:com="http://com.gs.gsautos.ws.catalogos">
                <soapenv:Header/>
                <soapenv:Body>
                   <com:wsListarColonias>
                      <!--Optional:-->
                      <arg0>
                         <!--Optional:-->
                         <token>' . $this->token . '</token>
                         <codpos>' . $cod_postal . '</codpos>
                      </arg0>
                   </com:wsListarColonias>
                </soapenv:Body>
             </soapenv:Envelope>';
            $clientColoniasGS = $this->getClient($this->urlCat);
            $result =  $clientColoniasGS->send($soap);
            // return json_encode($result);
            return $this->responseJson('colonias', $result, 0);
        } catch (SoapFault $error) {
            printf("Error: %s", $error);
        }
    }


    public function getCotizacion(Request $request)
    {
        try {
            $data = $request->json()->all();
            $id_brand = $data['id_brand'];
            $model = $data['model'];
            $descripcion = $data['descripcion'];
            $postal_code = $data['postal_code'];

            try {
                $historial = new CotizacionHistorialController();
                $hc = $historial->store($request->marca, $request->descripcion, $request->submarca, $request->model, $request->cliente_id, $request->localidad, $request->postal_code);
            } catch (\Throwable $th) {
                return response()->json([
                    'error' => 'Error al guardar historial de cotizaciones.',
                    "message" => $th->getMessage(),
                ], 500);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al guardar historial de cotizaciones.',
                    "message" => $e->getMessage(),
                ], 500);
            } catch (\Error $e) {
                return response()->json([
                    'error' => 'Error al guardar historial de cotizaciones.',
                    "message" => $e->getMessage(),
                ], 500);
            }

            $descuento = MantenimientoModel::where('provider', 'general')->first()->descuento;
            if ($descuento == 1) {
                //check if $request->cliente_id is null
                if ($data['cliente_id'] == null) {
                    //get gs_discount from admin where admin_id = 1
                    $discount = AdminModel::where('admin_id', 1)->first()->gs_descuento;
                } else {
                    //get client with client_id = $request->cliente_id
                    $client = NClientesModel::where('cliente_id', $data['cliente_id'])->first();
                    //check if tipo_cliente is publico
                    if ($client->tipo_cliente == 'Público') {
                        //get gs_discount from admin where admin_id = 1
                        $discount = AdminModel::where('admin_id', 1)->first()->gs_descuento;
                    } else {
                        //get gs_discount from client where client_id = $request->cliente_id
                        $discount = $client->gs_descuento;
                    }
                }
            } else {
                $discount = 0;
            }

            $soap = '<?xml version="1.0" encoding="utf-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:com="http://com.gs.gsautos.ws.cotizacionEmision">
                            <soapenv:Header/>
                            <soapenv:Body>
                            <com:generarCotizacion>
                                <!--Optional:-->
                                <arg0>
                                    <!--Optional:-->
                                    <token>' . $this->token . '</token>
                                    <!--Optional:-->
                                    <configuracionProducto>RESIDENTE_INDIVIDUAL</configuracionProducto>
                                    <cp>' . $postal_code . '</cp>
                                    <descuento>' .  $discount . '</descuento>
                                    <!--Optional:-->
                                    <idNegocio></idNegocio>
                                    <!--Optional:-->
                                    <inciso>
                                        <claveGs>' . $id_brand . '</claveGs>
                                        <conductorMenor30>1</conductorMenor30>
                                        <!--Optional:-->
                                        <descripcionVehiculo>' . $descripcion . '</descripcionVehiculo>
                                        <!--Optional:-->
                                        <fechaFactura></fechaFactura>
                                        <modelo>' . $model . '</modelo>
                                        <ocupantes></ocupantes>
                                        <!--Optional:-->
                                        <tipoServicio>PARTICULAR</tipoServicio>
                                        <!--Optional:-->
                                        <tipoValor>VALOR_COMERCIAL</tipoValor>
                                        <!--Optional:-->
                                        <tipoVehiculo>AUTO_PICKUP</tipoVehiculo>
                                        <valorVehiculo></valorVehiculo>
                                    </inciso>
                                    <!--Optional:-->
                                    <vigencia>ANUAL</vigencia>
                                </arg0>
                            </com:generarCotizacion>
                            </soapenv:Body>
                        </soapenv:Envelope>';
            // return $soap;
            // ini_set('default_socket_timeout', 900);
            // $result =  $this->clientCotiza->send($soap);
            $client = new nusoap_client($this->urlCotiza, true, false, false, false, false, 0, 5600);
            ini_set('default_socket_timeout', 600);
            $result = $client->send(
                $soap,
                'http://com.gs.gsautos.ws.cotizacionEmision/CotizacionEmisionWS/generarCotizacionRequest',
                0,
                5600
            );
            // return $client;
            $response = json_decode(json_encode($result), true);
            $coberturas = $this->getCobertura($response['return']['idCotizacion']);
            if (isset($response['return']['exito'])) {
                return response()->json([
                    "data" => $response['return'],
                    "discount" => $discount,
                    "coberturas" => $coberturas,
                    "historial" => $hc
                ], 200);
            } else {

                //check if $request->count is null
                if (!isset($request->count)) {
                    $request->count = 1;
                }

                if ($request->count == 4) {
                    return response()->json([
                        "status" => "error",
                        "message" => $result,
                        "count_request" => $request->count
                    ]);
                } else {
                    //repeat the function recursively just try 3 times
                    //add count equal to 0 item to $request
                    $request->count = $request->count + 1;
                    return $this->getCotizacion($request);
                }
            }
        } catch (SoapFault $error) {
            printf("Error: %s", $error);
        }
    }

    public function getCobertura($id_cotizacion)
    {
        try {
            $soap = '<?xml version="1.0" encoding="utf-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:com="http://com.gs.gsautos.ws.catalogoCoberturas">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <com:wsObtenerCoberturasCotizacion>
                            <!--Optional:-->
                            <arg0>
                                <!--Optional:-->
                                <token>' . $this->token . '</token>
                                <cotizacion>' . $id_cotizacion . '</cotizacion>
                                <paquete>2</paquete>
                            </arg0>
                        </com:wsObtenerCoberturasCotizacion>
                    </soapenv:Body>
                </soapenv:Envelope>';
            // return $soap;

            $client = new nusoap_client($this->urlCober, true, false, false, false, false, 0, 300);
            ini_set('default_socket_timeout', 600);
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = false;
            $result = $client->send(
                $soap,
                'http://com.gs.gsautos.ws.catalogoCoberturas/CatalogoCoberturasWS/wsObtenerCoberturasCotizacionRequest'
            );
            // return $client;
            //result has a character that is not a valid json
            $response = json_decode(json_encode($result), true);

            //get xml success of client
            // return $result;
            //check if response has data
            if (isset($response['return']['coberturas'])) {
                return $response['return']['coberturas'];
            } else {
                return [];
            }

            return $response;
            // return $this->responseJson('coberturass', $result, 0);
            // return $this->responseJson('coberturas', $result, 0);
        } catch (\Error $error) {
            return response()->json(['error' => $error], 500);
        }
    }

    public function sendCotizacion(Request $request)
    {
        try {
            $colonias = $this->getColonias($request->cod_pos);
            // cast to array
            $colonias = json_decode(json_encode($colonias), true);
            // return $colonias['original']['colonias'];
            // {
            //     "return": {
            //         "exito": "true",
            //         "colonias": [
            //             {
            //                 "codpos": "58980",
            //                 "idcolonia": "73046",
            //                 "nomcolonia": "QUERENDARO"
            //             },
            //             {
            //                 "codpos": "58980",
            //                 "idcolonia": "73047",
            //                 "nomcolonia": "BENITO JUAREZ"
            //             },
            //             {
            //                 "codpos": "58980",
            //                 "idcolonia": "73048",
            //                 "nomcolonia": "LA CRUZ"
            //             },
            //             {
            //                 "codpos": "58980",
            //                 "idcolonia": "73049",
            //                 "nomcolonia": "EL CALVARIO"
            //             },
            //             {
            //                 "codpos": "58980",
            //                 "idcolonia": "73050",
            //                 "nomcolonia": "FRANCISCO J. MUJICA"
            //             },
            //             {
            //                 "codpos": "58980",
            //                 "idcolonia": "73051",
            //                 "nomcolonia": "MIRADOR DEL VALLE"
            //             },
            //             {
            //                 "codpos": "58980",
            //                 "idcolonia": "99467",
            //                 "nomcolonia": "CENTRO"
            //             }
            //         ]
            //     }
            // }
            // search in colonias if exist the request->colonia and return idcolonia
            $idcolonia = 0;
            foreach ($colonias['original']['colonias'] as $colonia) {
                if ($colonia['nomcolonia'] == $request->colonia) {
                    $idcolonia = $colonia['idcolonia'];
                }
            }
            // return $idcolonia;
            $data = $request->json()->all();
            $idCotizacion = $data['idCotizacion'];

            $fis_mor = $data['fis_mor'];

            $nom_cli = $data['nom_cli'];
            $ape_pat = $data['ape_pat'];
            $ape_mat = $data['ape_mat'];
            $raz_soc = $data['raz_soc'];
            $rfc_cli = $data['rfc_cli'];
            $cve_ele = $data['cve_ele'];
            $curpcli = $data['curpcli'];
            $sexocli = $data['sexocli'];
            $edo_civ = $data['edo_civ'];
            $cal_cli = $data['cal_cli'];
            $num_cli = $data['num_cli'];
            $cod_pos = $data['cod_pos'];
            $colonia = $data['colonia'];
            $fec_nac = $data['fec_nac'];
            $telefo1 = $data['telefo1'];
            $cor_ele = $data['cor_ele'];
            $pag_web = $data['pag_web'];
            $apo_cli = $data['apo_cli'];
            $adm_con = $data['adm_con'];
            $apo_cli_pat = $data['apo_cli_pat'];
            $apo_cli_mat = $data['apo_cli_mat'];
            $numeroMotor = $data['numeroMotor'];
            $numeroPlacas = $data['numeroPlacas'];
            $numeroSerie = $data['numeroSerie'];
            // set the mexico city timezone to udse carbon
            date_default_timezone_set('America/Mexico_City');
            $inicioVigencia = Carbon::now()->format('Y-m-d');
            $cliente_p = NClientesModel::where('cliente_id', $request->input('cliente_id'))->first();
            $descuento = MantenimientoModel::where('provider', "general")->first()->descuento;
            if ($descuento == 1) {
                if ($cliente_p->tipo_cliente == 'Público') {
                    //get gs_discount from admin where admin_id = 1
                    $discount = AdminModel::where('admin_id', 1)->first()->gs_descuento;
                } else {
                    //get gs_discount from client where client_id = $request->cliente_id
                    $discount = $cliente_p->gs_descuento;
                }
            } else {
                $discount = 0;
            }
            // cast $cliente_p->fecha_nacimiento to DateTime
            $fecha_nacimiento = strtotime($cliente_p->fecha_nacimiento);
            // get the first 2 characters of the cliente_p->fecha_nacimiento
            $day = substr($cliente_p->fecha_nacimiento, 0, 2);
            $month = substr($cliente_p->fecha_nacimiento, 3, 2);
            $year = substr($cliente_p->fecha_nacimiento, 6, 4);
            $newDate = $year . '-' . $month . '-' . $day;
            // return $newDate;



            $soap = '<?xml version="1.0" encoding="utf-8"?>
                    <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                    <Body>
                        <emitirCotizacion xmlns="http://com.gs.gsautos.ws.cotizacionEmision">
                            <arg0 xmlns="">
                                <token>' . $this->token . '</token>
                                <cliente>
                                    <cve_cli xmlns="KB_ObtenerClientes">0</cve_cli>
                                    <suc_emi xmlns="KB_ObtenerClientes">0</suc_emi>
                                    <fis_mor xmlns="KB_ObtenerClientes">' . $fis_mor . '</fis_mor>
                                    <nom_cli xmlns="KB_ObtenerClientes">' . $nom_cli . '</nom_cli>
                                    <ape_pat xmlns="KB_ObtenerClientes">' . $ape_pat . '</ape_pat>
                                    <ape_mat xmlns="KB_ObtenerClientes">' . $ape_mat . '</ape_mat>
                                    <raz_soc xmlns="KB_ObtenerClientes">' . $raz_soc . '</raz_soc>
                                    <ane_cli xmlns="KB_ObtenerClientes"></ane_cli>
                                    <rfc_cli xmlns="KB_ObtenerClientes">' . $rfc_cli . '</rfc_cli>
                                    <cve_ele xmlns="KB_ObtenerClientes">' . $cve_ele . '</cve_ele>
                                    <curpcli xmlns="KB_ObtenerClientes">' . $curpcli . '</curpcli>
                                    <sexocli xmlns="KB_ObtenerClientes">' . $sexocli . '</sexocli>
                                    <edo_civ xmlns="KB_ObtenerClientes">' . $edo_civ . '</edo_civ>
                                    <cal_cli xmlns="KB_ObtenerClientes">' . $cal_cli . '</cal_cli>
                                    <num_cli xmlns="KB_ObtenerClientes">' . $num_cli . '</num_cli>
                                    <cod_pos xmlns="KB_ObtenerClientes">' . $cod_pos . '</cod_pos>
                                    <colonia xmlns="KB_ObtenerClientes">' . $idcolonia . '</colonia>


                                    <municip xmlns="KB_ObtenerClientes"></municip>
                                    <poblaci xmlns="KB_ObtenerClientes"></poblaci>
                                    <cve_est xmlns="KB_ObtenerClientes"></cve_est>

                                    <fec_nac xmlns="KB_ObtenerClientes">' . $newDate . '</fec_nac>
                                    <nac_ext xmlns="KB_ObtenerClientes">1</nac_ext>
                                    <ocu_pro xmlns="KB_ObtenerClientes">356</ocu_pro>
                                    <act_gir xmlns="KB_ObtenerClientes">331</act_gir>
                                    <telefo1 xmlns="KB_ObtenerClientes">' . $telefo1 . '</telefo1>
                                    <telefo2 xmlns="KB_ObtenerClientes"></telefo2>
                                    <telefo3 xmlns="KB_ObtenerClientes"></telefo3>
                                    <cor_ele xmlns="KB_ObtenerClientes">' . $cor_ele . '</cor_ele>
                                    <pag_web xmlns="KB_ObtenerClientes">' . $pag_web . '</pag_web>
                                    <can_con xmlns="KB_ObtenerClientes">1</can_con>
                                    <fue_ing xmlns="KB_ObtenerClientes">servicios profesionales</fue_ing>
                                    <adm_con xmlns="KB_ObtenerClientes">' . $adm_con . '</adm_con>
                                    <car_pub xmlns="KB_ObtenerClientes">N</car_pub>
                                    <nom_car xmlns="KB_ObtenerClientes"></nom_car>
                                    <per_car xmlns="KB_ObtenerClientes"></per_car>
                                    <apo_cli xmlns="KB_ObtenerClientes">' . $apo_cli . '</apo_cli>
                                    <apo_cli_pat xmlns="KB_ObtenerClientes">' . $apo_cli_pat . '</apo_cli_pat>
                                    <apo_cli_mat xmlns="KB_ObtenerClientes">' . $apo_cli_mat . '</apo_cli_mat>
                                    <dom_ori xmlns="KB_ObtenerClientes"></dom_ori>
                                    <num_pas xmlns="KB_ObtenerClientes"></num_pas>
                                </cliente>
                                <datosIncisoEmision>
                                    <numeroMotor>' . $numeroMotor . '</numeroMotor>
                                    <numeroPlacas>' . $numeroPlacas . '</numeroPlacas>
                                    <numeroSerie>' . $numeroSerie . '</numeroSerie>
                                </datosIncisoEmision>
                                <idAgenteCompartido>0</idAgenteCompartido>
                                <idCliente>0</idCliente>
                                <idCotizacion>' . $idCotizacion . '</idCotizacion>
                                <idFormaPago>1</idFormaPago>
                                <idPaquete>2</idPaquete>
                                <inicioVigencia>' . $inicioVigencia . '</inicioVigencia>
                                <descuento>' . $discount . '</descuento>
                            </arg0>
                        </emitirCotizacion>
                    </Body>
                </Envelope>';

            // return $soap;



            $client = new nusoap_client($this->urlCotiza, false, false, false, false, false, 0, 90000000);
            //incrementar el timeout de la conexion a 5 minutos
            // ini_set('default_socket_timeout', 90000000);
            // ini_set('default_socket_timeout', true, false, false, false, false, 600, 600);
            ini_set('default_socket_timeout', 90000000);

            $result = $client->send(
                $soap,
                'http://com.gs.gsautos.ws.cotizacionEmision/CotizacionEmisionWS/emitirCotizacionRequest',
                0,
                90000000
            );
            // return $client;
            $responseXML = $client->responseData;
            // convert to UTF-8
            $responseXML = mb_convert_encoding($responseXML, 'UTF-8');
            // echo "mb cobert" . $responseXML;
            // Eliminamos los namespaces
            $xml_string = str_replace(array('S:', 'ns3:', 'ns4:', 'ns2:'), '', $responseXML);

            // Eliminamos los caracteres especiales
            $xml_string = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $xml_string);
            $simplexml = simplexml_load_string($xml_string);
            // echo "simplexml" . $simplexml;

            // cast responseXML to json
            $simplexml = json_encode($simplexml);
            // echo "json encode" . $simplexml;
            // convert to array documentor
            // cast json to array
            $simplexml = json_decode($simplexml, true);
            // get in a variable Body.emitirCotizacionResponse.return.listaDocumentos.SDTDocumentos.SDTDocumentosItem
            $documentos = [];
            if (isset($simplexml['Body']['emitirCotizacionResponse']['return']['listaDocumentos']['SDTDocumentos.SDTDocumentosItem'])) {
                $documentos = $simplexml['Body']['emitirCotizacionResponse']['return']['listaDocumentos']['SDTDocumentos.SDTDocumentosItem'];
            }
            // convert to UTF-8
            // $responseXML = mb_convert_encoding($responseXML, 'UTF-8');

            return response()->json([
                "response" => $simplexml,
                "documentos" => $documentos,
            ]);


            $xml = simplexml_load_string($responseXML);
            $json = json_encode($xml);
            $array = json_decode($json, true);
            return $array;
            // return $soap;
            // return response()->json([
            //     "result" => $result,
            // ]);
            // $json = json_encode($xml);
            // $array = json_decode($json, true);
            // return $array;
            // return response()->json([
            //     "response" => $array,
            // ]);
            $response = json_decode(json_encode($result), true);


            return response()->json([
                "result" => $result,
                "response" => json_encode($response),
            ]);

            // //if response is empty print $soap
            if (empty($response)) {
                return $soap;
            }

            if ($response['return']['exito']) {
                return $response['return'];
            } else {
                return null;
            }
            // dd($result);
            // return $this->responseJson('paquetes', $result);

        } catch (\Exception $error) {
            printf("Error: %s", $error);
        }
    }

    public function responseJson($key, $res, $discount)
    {
        $response = json_decode(json_encode($res), true);
        if ($response['return']['exito']) {
            if ($key == "paquetes" && $response['return']['idCotizacion']) {
                $value = $response['return'][$key];
                return response()->json([
                    'cotizacion_id' => $response['return']['idCotizacion'],
                    'descuento' => $discount,
                    $key => $value
                ]);
            } else {
                if (is_array($response['return'][$key]) && array_key_exists(0, $response['return'][$key])) {
                    $value = $response['return'][$key];
                    return response()->json([$key => $value]);
                } else {
                    $value = [$response['return'][$key]];
                    return response()->json([$key => $value]);
                }
            }
        }
    }
}
