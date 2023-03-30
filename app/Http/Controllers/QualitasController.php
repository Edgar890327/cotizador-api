<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\AdminModel;
use App\Models\MantenimientoModel;
use App\Models\NClientesModel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use SimpleXMLElement;
use SoapClient;
use SoapFault;
use nusoap_client;
use PhpParser\Node\Stmt\Else_;

class QualitasController extends Controller
{
    public function __construct()
    {
        $this->urlTarifas = "http://qbcenter.qualitas.com.mx/wsTarifa/wsTarifa.asmx";
        // $this->urlWACotizacionEmision = "https://qa.qualitas.com.mx:8443/WsEmision/WsEmision.asmx";
        $this->urlWACotizacionEmision = "http://sio.qualitas.com.mx/WsEmision/WsEmision.asmx";
        // try {
        //     $this->clientTarifas = $this->getClient($this->urlTarifas);
        $this->usuario = env("QUALITAS_USER");
        $this->tarifa = env("QUALITAS_TARIFA");
        // } catch (Handler $fault) {
        //     dd("Fallo", $fault);
        // }
    }

    public function getClient($url)
    {
        try {
            $client = new nusoap_client(
                $url,
                true,
            );


            return $client;
        } catch (SoapFault $error) {
            //show error
            printf("Error: %s", $error->getMessage());
        }
    }

    public function getMarcas()
    {

        try {
            $client = $this->getClient($this->urlTarifas);
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:qbc="http://tempuri.org/WSQBC/QBCDE">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <qbc:listaMarcas>
                            <qbc:cUsuario>' . $this->usuario . '</qbc:cUsuario>
                            <qbc:cTarifa>' . $this->tarifa . '</qbc:cTarifa>
                        </qbc:listaMarcas>
                    </soapenv:Body>
                </soapenv:Envelope>';

            $result = $client->send(
                $soap,
                'http://tempuri.org/WSQBC/QBCDE/listaMarcas'
            );

            $response = json_decode(json_encode($result), true);
            $response = $response['listaMarcasResult']['salida']['datos']['Elemento'];
            return response()->json([
                "marcas" => $response
            ]);
        } catch (SoapFault $fault) {
            dd("Fallo", $fault);
        }
    }

    public function getAMIS($marca, $modelo, $tipo)
    {
        try {
            $client = $this->getClient($this->urlTarifas);
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:qbc="http://tempuri.org/WSQBC/QBCDE">
                        <soapenv:Header/>
                        <soapenv:Body>
                            <qbc:listaTarifas>
                                <qbc:cUsuario>' . $this->usuario . '</qbc:cUsuario>
                                <qbc:cTarifa>' . $this->tarifa . '</qbc:cTarifa>
                                <qbc:cMarca>' . $marca . '</qbc:cMarca>
                                <qbc:cTipo>' . $tipo . '</qbc:cTipo>
                                <!--Optional:-->
                                <qbc:cVersion></qbc:cVersion>
                                <qbc:cModelo>' . $modelo . '</qbc:cModelo>
                                <!--Optional:-->
                                <qbc:cCAMIS></qbc:cCAMIS>
                                <!--Optional:-->
                                <qbc:cCategoria></qbc:cCategoria>
                                <!--Optional:-->
                                <qbc:cNvaAMIS></qbc:cNvaAMIS>
                            </qbc:listaTarifas>
                        </soapenv:Body>
                    </soapenv:Envelope>';

            $result = $client->send(
                $soap,
                'http://tempuri.org/WSQBC/QBCDE/listaTarifas'
            );

            $response = json_decode(json_encode($result), true);
            $response = $response['listaTarifasResult']['salida']['datos']['Elemento'];
            return response()->json([
                "tarifas" => $response
            ]);
        } catch (SoapFault $fault) {
            dd("Fallo", $fault);
        }
    }


    public function setQuote(Request $request)
    {

        $marcas = $this->getMarcas();
        $marcas = json_decode($marcas->getContent(), true);
        // return $marcas;
        $marca = "";

        if ($request->cliente_id == null) {
            //get gs_discount from admin where admin_id = 1
            // $discount = AdminModel::where('admin_id', 1)->first()->mapfre_descuento;
            //return message to login
            return response()->json([
                "status" => "error",
                "data" => "No se ha encontrado el cliente, debes iniciar sesión para poder realizar una solicitud",
            ], 201);
        } else {
            $descuento = MantenimientoModel::where("provider", "qualitas")->first()->descuento;
            if ($descuento == 1) {
                //get client with client_id = $request->cliente_id
                $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
                //check if tipo_cliente is publico
                if ($cliente->tipo_cliente == 'Público') {
                    //get gs_discount from admin where admin_id = 1
                    $discount = AdminModel::where('admin_id', 1)->first()->qualitas_descuento;
                } else {
                    //get gs_discount from client where client_id = $request->cliente_id
                    $discount = $cliente->qualitas_descuento;
                }
            } else {
                $discount = 0;
            }
        }

        //search $marca in $marcas and get content
        foreach ($marcas['marcas'] as $key => $value) {
            if ($value['cMarcaLarga'] == strtoupper($request->marca)) {
                if ($value['cMarcaLarga'] == 'NISSAN') {
                    $marca = 'NN';
                } else {
                    $marca = $value['cMarca'];
                }
            }
        }
        // return $marca;

        //get first word before " " of $request->tipo
        $tipos = explode(" ", $request->tipo);
        $tipo = $tipos[0];
        if ($tipo == "NUEVO") {
            $tipo = $tipos[1];
        }
        // return $tipo;

        $amis = $this->getAMIS($marca, $request->modelo, $tipo);
        $amis = json_decode($amis->getContent(), true);
        // return $amis;

        $marca_result = array();
        $c = 0;
        $percent = 0.0;
        //search in $vehiculos array by marca and submarca and save in $marca_result
        foreach ($amis['tarifas'] as $vehiculo) {
            //compare $vehiculo["descripcion"] with $request->descripcion and save the more similar in $marca_result
            similar_text($vehiculo["cVersion"], $request->descripcion, $percent);
            if ($percent >= 30.0) {
                $marca_result[$c] = array(
                    "cTarifa" => $vehiculo["cTarifa"],
                    "cMarcaLarga" => $vehiculo["cMarcaLarga"],
                    "cMarca" => $vehiculo["cMarca"],
                    "cTipo" => $vehiculo["cTipo"],
                    "cVersion" => $vehiculo["cVersion"],
                    "cModelo" => $vehiculo["cModelo"],
                    "CAMIS" => $vehiculo["CAMIS"],
                    "cNvaAMIS" => $vehiculo["cNvaAMIS"],
                    "percent" => $percent
                );
                $c++;
            }
        }

        //check if $marca_result is empty
        if (empty($marca_result)) {
            return response()->json([
                "error" => "No se encontró coincidencia para el modelo en Qualitas"
            ]);
        }

        // return $marca_result;


        $max = 0;
        $max_key = 0;
        foreach ($marca_result as $key => $value) {
            if ($value["percent"] > $max) {
                $max = $value["percent"];
                $max_key = $key;
            }
        }

        //get $vehiculo with the more similar $request->descripcion
        $vehiculo = $marca_result[$max_key];
        // return $vehiculo;

        $dv = $this->getDigitoVerificador($vehiculo["CAMIS"]);

        // get actual date yyyy-mm-dd
        $date = Carbon::now()->format('Y-m-d');
        // add 1 year to actual date
        $date_fin = Carbon::parse($date)->addYear(1)->format('Y-m-d');



        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:qual="http://qualitas.com.mx/">
    <soapenv:Header />
    <soapenv:Body>
        <qual:obtenerNuevaEmision>
            <!--Optional:-->
            <qual:xmlEmision>
                <![CDATA[<Movimientos>
                                    <Movimiento TipoMovimiento="2" NoPoliza="" NoCotizacion="" NoEndoso="" TipoEndoso="" NoOTra="" NoNegocio="07135">
                                        <DatosAsegurado NoAsegurado="">
                                            <Nombre />
                                            <Direccion />
                                            <Colonia />
                                            <Poblacion />
                                            <Estado>' . $request->estado_clave . '</Estado>
                                            <CodigoPostal>' . $request->cod_postal . '</CodigoPostal>
                                            <NoEmpleado />
                                            <Agrupador />
                                        </DatosAsegurado>
                                        <DatosVehiculo NoInciso="1">
                                            <ClaveAmis>' . $vehiculo["CAMIS"] . '</ClaveAmis>
                                            <Modelo>' . $vehiculo["cModelo"] . '</Modelo>
                                            <DescripcionVehiculo />
                                            <Uso>1</Uso>
                                            <Servicio>1</Servicio>
                                            <Paquete>1</Paquete>
                                            <Motor />
                                            <Serie />
                                            <Coberturas NoCobertura="1">
                                                <SumaAsegurada>0</SumaAsegurada>
                                                <TipoSuma>0</TipoSuma>
                                                <Deducible>3</Deducible>
                                                <Prima />
                                            </Coberturas>
                                            <Coberturas NoCobertura="3">
                                                <SumaAsegurada>0</SumaAsegurada>
                                                <TipoSuma>0</TipoSuma>
                                                <Deducible>5</Deducible>
                                                <Prima />
                                            </Coberturas>
                                            <Coberturas NoCobertura="4">
                                                <SumaAsegurada>3000000</SumaAsegurada>
                                                <TipoSuma>0</TipoSuma>
                                                <Deducible>0</Deducible>
                                                <Prima />
                                            </Coberturas>
                                            <Coberturas NoCobertura="5">
                                                <SumaAsegurada>250000</SumaAsegurada>
                                                <TipoSuma>0</TipoSuma>
                                                <Deducible>0</Deducible>
                                                <Prima />
                                            </Coberturas>
                                            <Coberturas NoCobertura="6">
                                                <SumaAsegurada>100000</SumaAsegurada>
                                                <TipoSuma>0</TipoSuma>
                                                <Deducible>0</Deducible>
                                                <Prima />
                                            </Coberturas>
                                        </DatosVehiculo>
                                        <DatosGenerales>
                                            <FechaEmision>' . $request->fecha_inicio . '</FechaEmision>
                                            <FechaInicio>' . $request->fecha_inicio . '</FechaInicio>
                                            <FechaTermino>' . $request->fecha_fin . '</FechaTermino>
                                            <Moneda>0</Moneda>
                                            <Agente>61273</Agente>
                                            <FormaPago>C</FormaPago>
                                            <TarifaValores>LINEA</TarifaValores>
                                            <TarifaCuotas>LINEA</TarifaCuotas>
                                            <TarifaDerechos>LINEA</TarifaDerechos>
                                            <Plazo />
                                            <Agencia />
                                            <Contrato />
                                            <PorcentajeDescuento>' . $discount . '</PorcentajeDescuento>
                                            <ConsideracionesAdicionalesDG NoConsideracion="1">
                                                <TipoRegla>0</TipoRegla>
                                                <ValorRegla>' . $dv . '</ValorRegla>
                                            </ConsideracionesAdicionalesDG>
                                            <ConsideracionesAdicionalesDG NoConsideracion="4">
                                                <TipoRegla>1</TipoRegla>
                                                <ValorRegla>0</ValorRegla>
                                            </ConsideracionesAdicionalesDG>
                                            <ConsideracionesAdicionalesDG NoConsideracion="5">
                                                <TipoRegla>0</TipoRegla>
                                                <ValorRegla>14</ValorRegla>
                                            </ConsideracionesAdicionalesDG>
                                        </DatosGenerales>
                                        <Primas>
                                            <PrimaNeta />
                                            <Derecho>520</Derecho>
                                            <Recargo />
                                            <Impuesto />
                                            <PrimaTotal />
                                            <Comision />
                                        </Primas>
                                        <CodigoError />
                                    </Movimiento>
                                </Movimientos>]]>
            </qual:xmlEmision>
        </qual:obtenerNuevaEmision>
    </soapenv:Body>
</soapenv:Envelope>';
        // return $soap;
        try {
            $client = $this->getClient($this->urlWACotizacionEmision);
            $response = $client->send(
                $soap,
                "http://qualitas.com.mx/obtenerNuevaEmision"
            );
            // return $client;
            // return $response["obtenerNuevaEmisionResult"];
            $result = json_decode(json_encode(simplexml_load_string($response["obtenerNuevaEmisionResult"])), true);

            return response()->json([
                "status" => "success",
                "data" => $result,
                "discount" => $discount,
                "cotizacion" => $result["Movimiento"]["@attributes"]["NoCotizacion"],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => $th->getMessage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        } catch (\SoapFault $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        } catch (\Error $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }


    // emision con qualitas
    public function setEmision(Request $request)
    {

        $marcas = $this->getMarcas();
        $marcas = json_decode($marcas->getContent(), true);
        $marca = "";

        if ($request->cliente_id == null) {
            //get gs_discount from admin where admin_id = 1
            // $discount = AdminModel::where('admin_id', 1)->first()->mapfre_descuento;
            //return message to login
            return response()->json([
                "status" => "error",
                "data" => "No se ha encontrado el cliente, debes iniciar sesión para poder realizar una solicitud",
            ], 201);
        } else {
            $descuento = MantenimientoModel::where("provider", "qualitas")->first()->descuento;
            if ($descuento == 1) {
                //get client with client_id = $request->cliente_id
                $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
                //check if tipo_cliente is publico
                if ($cliente->tipo_cliente == 'Público') {
                    //get gs_discount from admin where admin_id = 1
                    $discount = AdminModel::where('admin_id', 1)->first()->qualitas_descuento;
                } else {
                    //get gs_discount from client where client_id = $request->cliente_id
                    $discount = $cliente->qualitas_descuento;
                }
            } else {
                $discount = 0;
            }
        }

        //search $marca in $marcas and get content
        foreach ($marcas['marcas'] as $key => $value) {
            if ($value['cMarcaLarga'] == strtoupper($request->marca)) {
                $marca = $value['cMarca'];
            }
        }

        //get first word before " " of $request->tipo
        $tipo = explode(" ", $request->tipo);
        $tipo = $tipo[0];

        $amis = $this->getAMIS($marca, $request->modelo, $tipo);
        $amis = json_decode($amis->getContent(), true);
        // return $amis;

        $marca_result = array();
        $c = 0;
        $percent = 0.0;
        //search in $vehiculos array by marca and submarca and save in $marca_result
        foreach ($amis['tarifas'] as $vehiculo) {
            //compare $vehiculo["descripcion"] with $request->descripcion and save the more similar in $marca_result
            similar_text($vehiculo["cVersion"], $request->descripcion, $percent);
            if ($percent >= 30.0) {
                $marca_result[$c] = array(
                    "cTarifa" => $vehiculo["cTarifa"],
                    "cMarcaLarga" => $vehiculo["cMarcaLarga"],
                    "cMarca" => $vehiculo["cMarca"],
                    "cTipo" => $vehiculo["cTipo"],
                    "cVersion" => $vehiculo["cVersion"],
                    "cModelo" => $vehiculo["cModelo"],
                    "CAMIS" => $vehiculo["CAMIS"],
                    "cNvaAMIS" => $vehiculo["cNvaAMIS"],
                    "percent" => $percent
                );
                $c++;
            }
        }

        //check if $marca_result is empty
        if (empty($marca_result)) {
            return response()->json([
                "error" => "No se encontró coincidencia para el modelo en Qualitas"
            ]);
        }

        // return $marca_result;


        $max = 0;
        $max_key = 0;
        foreach ($marca_result as $key => $value) {
            if ($value["percent"] > $max) {
                $max = $value["percent"];
                $max_key = $key;
            }
        }

        //get $vehiculo with the more similar $request->descripcion
        $vehiculo = $marca_result[$max_key];
        // return $vehiculo;

        $dv = $this->getDigitoVerificador($vehiculo["CAMIS"]);

        if ($request->tipo_persona == 1) {
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:qual="http://qualitas.com.mx/">
                            <soapenv:Header />
                            <soapenv:Body>
                                <qual:obtenerNuevaEmision>
                                    <!--Optional:-->
                                    <qual:xmlEmision>
                                        <![CDATA[<Movimientos>
                                                            <Movimiento TipoMovimiento="3" NoPoliza="" NoCotizacion="' . $request->numCotizacion . '" NoEndoso="" TipoEndoso="" NoOTra="" NoNegocio="07135">
                                                                <DatosAsegurado NoAsegurado="">
                                                                    <Nombre>' . $request->nombre . ' ' . $request->apaterno . ' ' . $request->amaterno . '</Nombre>
                                                                    <Direccion>' . $request->direccion . '</Direccion>
                                                                    <Colonia>' . $request->colonia . '</Colonia>
                                                                    <Poblacion>' . $request->poblacion . '</Poblacion>
                                                                    <Estado>' . $request->estado_clave . '</Estado>
                                                                    <CodigoPostal>' . $request->cod_postal . '</CodigoPostal>
                                                                    <NoEmpleado />
                                                                    <Agrupador />
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>1</TipoRegla>
                                                                        <ValorRegla>' . $request->num_exterior . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>2</TipoRegla>
                                                                        <ValorRegla>30</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>4</TipoRegla>
                                                                        <ValorRegla>' . $request->nombre . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>5</TipoRegla>
                                                                        <ValorRegla>' . $request->apaterno . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>6</TipoRegla>
                                                                        <ValorRegla>' . $request->amaterno . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>7</TipoRegla>
                                                                        <ValorRegla>' . $request->cod_municipio . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>8</TipoRegla>
                                                                        <ValorRegla>' . $request->cod_colonia . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>19</TipoRegla>
                                                                        <ValorRegla>' . $request->tipo_persona . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>20</TipoRegla>
                                                                        <ValorRegla>' . $request->fecha_nacimiento . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>21</TipoRegla>
                                                                        <ValorRegla>' . $request->nacionalidad . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>22</TipoRegla>
                                                                        <ValorRegla>' . $request->pais_nacimiento . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>23</TipoRegla>
                                                                        <ValorRegla>' . $request->ocupacion . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>24</TipoRegla>
                                                                        <ValorRegla>' . $request->giro_comercial . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>25</TipoRegla>
                                                                        <ValorRegla>' . $request->profesion . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>26</TipoRegla>
                                                                        <ValorRegla>' . $request->correo . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>27</TipoRegla>
                                                                        <ValorRegla>' . $request->curp . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>28</TipoRegla>
                                                                        <ValorRegla>' . $request->rfc . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>56</TipoRegla>
                                                                        <ValorRegla>' . $request->genero . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>57</TipoRegla>
                                                                        <ValorRegla>N</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>59</TipoRegla>
                                                                        <ValorRegla>N</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>62</TipoRegla>
                                                                        <ValorRegla>' . $request->tipo_identificacion . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>63</TipoRegla>
                                                                        <ValorRegla>' . $request->numero_credencial . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>70</TipoRegla>
                                                                        <ValorRegla>' . $request->asegurado_telefono . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>78</TipoRegla>
                                                                        <ValorRegla>' . $request->entidad_federativa_nacimiento . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>86</TipoRegla>
                                                                        <ValorRegla>' . $request->celular . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                </DatosAsegurado>
                                                                <DatosVehiculo NoInciso="1">
                                                                    <ClaveAmis>' . $vehiculo["CAMIS"] . '</ClaveAmis>
                                                                    <Modelo>' . $vehiculo["cModelo"] . '</Modelo>
                                                                    <DescripcionVehiculo />
                                                                    <Uso>1</Uso>
                                                                    <Servicio>1</Servicio>
                                                                    <Paquete>1</Paquete>
                                                                    <Motor>' . $request->motor . '</Motor>
                                                                    <Serie>' . $request->serie . '</Serie>
                                                                    <Coberturas NoCobertura="1">
                                                                        <SumaAsegurada>0</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>3</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="3">
                                                                        <SumaAsegurada>0</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>5</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="4">
                                                                        <SumaAsegurada>3000000</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>0</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="5">
                                                                        <SumaAsegurada>250000</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>0</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="6">
                                                                        <SumaAsegurada>100000</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>0</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                </DatosVehiculo>
                                                                <DatosGenerales>
                                                                <FechaEmision>' . $request->fecha_inicio . '</FechaEmision>
                                                                <FechaInicio>' . $request->fecha_inicio . '</FechaInicio>
                                                                <FechaTermino>' . $request->fecha_fin . '</FechaTermino>
                                                                    <Moneda>0</Moneda>
                                                                    <Agente>61273</Agente>
                                                                    <FormaPago>C</FormaPago>
                                                                    <TarifaValores>LINEA</TarifaValores>
                                                                    <TarifaCuotas>LINEA</TarifaCuotas>
                                                                    <TarifaDerechos>LINEA</TarifaDerechos>
                                                                    <Plazo />
                                                                    <Agencia />
                                                                    <Contrato />
                                                                    <PorcentajeDescuento>' . $discount . '</PorcentajeDescuento>
                                                                    <ConsideracionesAdicionalesDG NoConsideracion="1">
                                                                        <TipoRegla>0</TipoRegla>
                                                                        <ValorRegla>' . $dv . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDG>
                                                                    <ConsideracionesAdicionalesDG NoConsideracion="4">
                                                                        <TipoRegla>1</TipoRegla>
                                                                        <ValorRegla>0</ValorRegla>
                                                                    </ConsideracionesAdicionalesDG>
                                                                    <ConsideracionesAdicionalesDG NoConsideracion="5">
                                                                        <TipoRegla>0</TipoRegla>
                                                                        <ValorRegla>14</ValorRegla>
                                                                    </ConsideracionesAdicionalesDG>
                                                                </DatosGenerales>
                                                                <Primas>
                                                                    <PrimaNeta />
                                                                    <Derecho>520</Derecho>
                                                                    <Recargo />
                                                                    <Impuesto />
                                                                    <PrimaTotal />
                                                                    <Comision />
                                                                </Primas>
                                                                <CodigoError />
                                                            </Movimiento>
                                                        </Movimientos>]]>
                                    </qual:xmlEmision>
                                </qual:obtenerNuevaEmision>
                            </soapenv:Body>
                        </soapenv:Envelope>';
        } else {
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:qual="http://qualitas.com.mx/">
                            <soapenv:Header />
                            <soapenv:Body>
                                <qual:obtenerNuevaEmision>
                                    <!--Optional:-->
                                    <qual:xmlEmision>
                                        <![CDATA[<Movimientos>
                                                            <Movimiento TipoMovimiento="3" NoPoliza="" NoCotizacion="' . $request->numCotizacion . '" NoEndoso="" TipoEndoso="" NoOTra="" NoNegocio="07135">
                                                                <DatosAsegurado NoAsegurado="">
                                                                    <Nombre>' . $request->nombre . '</Nombre>
                                                                    <Direccion>' . $request->direccion . '</Direccion>
                                                                    <Colonia>' . $request->colonia . '</Colonia>
                                                                    <Poblacion>' . $request->poblacion . '</Poblacion>
                                                                    <Estado>' . $request->estado_clave . '</Estado>
                                                                    <CodigoPostal>' . $request->cod_postal . '</CodigoPostal>
                                                                    <NoEmpleado />
                                                                    <Agrupador />
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>1</TipoRegla>
                                                                        <ValorRegla>' . $request->num_exterior . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>2</TipoRegla>
                                                                        <ValorRegla>30</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>4</TipoRegla>
                                                                        <ValorRegla>' . $request->nombre . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>7</TipoRegla>
                                                                        <ValorRegla>' . $request->cod_municipio . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>8</TipoRegla>
                                                                        <ValorRegla>' . $request->cod_colonia . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>19</TipoRegla>
                                                                        <ValorRegla>' . $request->tipo_persona . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>21</TipoRegla>
                                                                        <ValorRegla>' . $request->nacionalidad . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>24</TipoRegla>
                                                                        <ValorRegla>' . $request->giro_comercial . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>28</TipoRegla>
                                                                        <ValorRegla>' . $request->rfc . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>29</TipoRegla>
                                                                        <ValorRegla>' . $request->actividad . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>31</TipoRegla>
                                                                        <ValorRegla>' . $request->correo . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>32</TipoRegla>
                                                                        <ValorRegla>' . $request->fecha_constitucion . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>34</TipoRegla>
                                                                        <ValorRegla>' . $request->apoderado_legal . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>35</TipoRegla>
                                                                        <ValorRegla>' . $request->pais_sociedad . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>57</TipoRegla>
                                                                        <ValorRegla>N</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>59</TipoRegla>
                                                                        <ValorRegla>N</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>62</TipoRegla>
                                                                        <ValorRegla>' . $request->tipo_identificacion_apoderado . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>63</TipoRegla>
                                                                        <ValorRegla>' . $request->numero_credencial_apoderado . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>65</TipoRegla>
                                                                        <ValorRegla>' . $request->fecha_nacimiento_apoderado . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>66</TipoRegla>
                                                                        <ValorRegla>' . $request->entidad_federativa_apoderado . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>70</TipoRegla>
                                                                        <ValorRegla>' . $request->asegurado_telefono . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>72</TipoRegla>
                                                                        <ValorRegla>' . $request->folio_mercantil . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>76</TipoRegla>
                                                                        <ValorRegla>' . $request->fideicomiso . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                    <ConsideracionesAdicionalesDA NoConsideracion="40">
                                                                        <TipoRegla>86</TipoRegla>
                                                                        <ValorRegla>' . $request->celular . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDA>
                                                                </DatosAsegurado>
                                                                <DatosVehiculo NoInciso="1">
                                                                    <ClaveAmis>' . $vehiculo["CAMIS"] . '</ClaveAmis>
                                                                    <Modelo>' . $vehiculo["cModelo"] . '</Modelo>
                                                                    <DescripcionVehiculo />
                                                                    <Uso>1</Uso>
                                                                    <Servicio>1</Servicio>
                                                                    <Paquete>1</Paquete>
                                                                    <Motor>' . $request->motor . '</Motor>
                                                                    <Serie>' . $request->serie . '</Serie>
                                                                    <Coberturas NoCobertura="1">
                                                                        <SumaAsegurada>0</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>3</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="3">
                                                                        <SumaAsegurada>0</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>5</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="4">
                                                                        <SumaAsegurada>3000000</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>0</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="5">
                                                                        <SumaAsegurada>250000</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>0</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                    <Coberturas NoCobertura="6">
                                                                        <SumaAsegurada>100000</SumaAsegurada>
                                                                        <TipoSuma>0</TipoSuma>
                                                                        <Deducible>0</Deducible>
                                                                        <Prima />
                                                                    </Coberturas>
                                                                </DatosVehiculo>
                                                                <DatosGenerales>
                                                                <FechaEmision>' . $request->fecha_inicio . '</FechaEmision>
                                                                <FechaInicio>' . $request->fecha_inicio . '</FechaInicio>
                                                                <FechaTermino>' . $request->fecha_fin . '</FechaTermino>
                                                                    <Moneda>0</Moneda>
                                                                    <Agente>61273</Agente>
                                                                    <FormaPago>C</FormaPago>
                                                                    <TarifaValores>LINEA</TarifaValores>
                                                                    <TarifaCuotas>LINEA</TarifaCuotas>
                                                                    <TarifaDerechos>LINEA</TarifaDerechos>
                                                                    <Plazo />
                                                                    <Agencia />
                                                                    <Contrato />
                                                                    <PorcentajeDescuento>' . $discount . '</PorcentajeDescuento>
                                                                    <ConsideracionesAdicionalesDG NoConsideracion="1">
                                                                        <TipoRegla>0</TipoRegla>
                                                                        <ValorRegla>' . $dv . '</ValorRegla>
                                                                    </ConsideracionesAdicionalesDG>
                                                                    <ConsideracionesAdicionalesDG NoConsideracion="4">
                                                                        <TipoRegla>1</TipoRegla>
                                                                        <ValorRegla>0</ValorRegla>
                                                                    </ConsideracionesAdicionalesDG>
                                                                    <ConsideracionesAdicionalesDG NoConsideracion="5">
                                                                        <TipoRegla>0</TipoRegla>
                                                                        <ValorRegla>14</ValorRegla>
                                                                    </ConsideracionesAdicionalesDG>
                                                                </DatosGenerales>
                                                                <Primas>
                                                                    <PrimaNeta />
                                                                    <Derecho>520</Derecho>
                                                                    <Recargo />
                                                                    <Impuesto />
                                                                    <PrimaTotal />
                                                                    <Comision />
                                                                </Primas>
                                                                <CodigoError />
                                                            </Movimiento>
                                                        </Movimientos>]]>
                                    </qual:xmlEmision>
                                </qual:obtenerNuevaEmision>
                            </soapenv:Body>
                        </soapenv:Envelope>';
        }




        // return $soap;
        $client = $this->getClient($this->urlWACotizacionEmision);
        $response = $client->send(
            $soap,
            "http://qualitas.com.mx/obtenerNuevaEmision"
        );
        // return $response["obtenerNuevaEmisionResult"];
        $result = json_decode(json_encode(simplexml_load_string($response["obtenerNuevaEmisionResult"])), true);
        // $result = json_decode(json_encode($response["obtenerNuevaEmisionResult"]), true);
        return response()->json([
            "status" => "success",
            "data" => $result,
            "discount" => $discount
        ]);
    }

    public function getExample(Request $request)
    {
        $soap = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Body>
            <obtenerNuevaEmision xmlns="http://qualitas.com.mx/">
                <xmlEmision>
                <Movimientos><Movimiento TipoMovimiento="2" NoPoliza="" NoCotizacion="" NoEndoso="" TipoEndoso="" NoOTra="" NoNegocio="07135"><DatosAsegurado NoAsegurado=""><Nombre/><Direccion/><Colonia/><Poblacion/><Estado>16</Estado><CodigoPostal>58980</CodigoPostal><NoEmpleado/><Agrupador/></DatosAsegurado><DatosVehiculo NoInciso="1"><ClaveAmis>00235</ClaveAmis><Modelo>2015</Modelo><DescripcionVehiculo/><Uso>1</Uso><Servicio>1</Servicio><Paquete>1</Paquete><Motor/><Serie/><Coberturas NoCobertura="1"><SumaAsegurada>0</SumaAsegurada><TipoSuma>0</TipoSuma><Deducible>3</Deducible><Prima/></Coberturas><Coberturas NoCobertura="3"><SumaAsegurada>0</SumaAsegurada><TipoSuma>0</TipoSuma><Deducible>5</Deducible><Prima/></Coberturas><Coberturas NoCobertura="4"><SumaAsegurada>3000000</SumaAsegurada><TipoSuma>0</TipoSuma><Deducible>0</Deducible><Prima/></Coberturas><Coberturas NoCobertura="5"><SumaAsegurada>250000</SumaAsegurada><TipoSuma>0</TipoSuma><Deducible>0</Deducible><Prima/></Coberturas><Coberturas NoCobertura="6"><SumaAsegurada>100000</SumaAsegurada><TipoSuma>0</TipoSuma><Deducible>0</Deducible><Prima/></Coberturas><Coberturas NoCobertura="7"><SumaAsegurada>0</SumaAsegurada><TipoSuma>0</TipoSuma><Deducible>0</Deducible><Prima/></Coberturas><Coberturas NoCobertura="14"><SumaAsegurada/><TipoSuma>0</TipoSuma><Deducible>0</Deducible><Prima/></Coberturas></DatosVehiculo><DatosGenerales><FechaEmision>2022-07-13</FechaEmision><FechaInicio>2022-07-13</FechaInicio><FechaTermino>2023-07-13</FechaTermino><Moneda>0</Moneda><Agente>61273</Agente><FormaPago>C</FormaPago><TarifaValores>LINEA</TarifaValores><TarifaCuotas>LINEA</TarifaCuotas><TarifaDerechos>LINEA</TarifaDerechos><Plazo/><Agencia/><Contrato/><PorcentajeDescuento>25</PorcentajeDescuento><ConsideracionesAdicionalesDG NoConsideracion="1"><TipoRegla>0</TipoRegla><ValorRegla>6</ValorRegla></ConsideracionesAdicionalesDG><ConsideracionesAdicionalesDG NoConsideracion="4"><TipoRegla>0</TipoRegla><ValorRegla>0</ValorRegla></ConsideracionesAdicionalesDG><ConsideracionesAdicionalesDG NoConsideracion="5"><TipoRegla>0</TipoRegla><ValorRegla>14</ValorRegla></ConsideracionesAdicionalesDG></DatosGenerales><Primas><PrimaNeta/><Derecho>520</Derecho><Recargo/><Impuesto/><PrimaTotal/><Comision/></Primas><CodigoError/></Movimiento></Movimientos>
                </xmlEmision>
            </obtenerNuevaEmision>
            </soap:Body>
        </soap:Envelope>';
        // return $soap;
        try {
            $client = new nusoap_client('https://qa.qualitas.com.mx:8443/WsEmision/WsEmision.asmx?WSDL', true);
            //add header "Accept: text/xml"
            $client->setHeaders('Accept: text/xml');
            //charset=utf-8
            $client->soap_defencoding = 'utf-8';
            $result = $client->send(
                $soap,
                "http://qualitas.com.mx/obtenerNuevaEmision"
            );
            //return result and client
            $response = json_decode(json_encode($result), true);
            return response()->json([
                "status" => "success",
                "data" => $client,
            ]);

            // dd($result);
        } catch (SoapFault $th) {
            //throw $th;
            return response()->json([
                "status" => "erroor",
                "data" => $th,
            ]);
        }
    }

    // crear el digito verificador de la clave amis para cotizacion y emision qualitas
    public function getDigitoVerificador($claveAmis)
    {
        // si el amis es menor a 5 digitos se le agrega un 0 al inicio
        if (strlen($claveAmis) < 5) {
            $claveAmis = str_pad($claveAmis, 5, "0", STR_PAD_LEFT);
        }
        // comenzar desde la izquierda, sumar todos los caracteres ubicados en las posiciones impares.
        $sumaImpares = 0;
        for ($i = 0; $i < strlen($claveAmis); $i++) {
            if ($i % 2 == 0) {
                $sumaImpares += $claveAmis[$i];
            }
        }

        // multiplicar la suma de los caracteres ubicados en las posiciones impares por 3.
        $multiplicacionImpares = $sumaImpares * 3;

        // Comenzar desde la izquierda, sumar todos los caracteres que están ubicados en las posiciones pares.
        $sumaPares = 0;
        for ($i = 0; $i < strlen($claveAmis); $i++) {
            if ($i % 2 != 0) {
                $sumaPares += $claveAmis[$i];
            }
        }

        // sumar el resultado de la multiplicación por 3 con la suma de los caracteres ubicados en las posiciones pares.
        $sumaTotal = $multiplicacionImpares + $sumaPares;

        // Buscar el menor número que sumado al resultado obtenido en la etapa 4 dé un número múltiplo de 10 36 +4 = 40
        $menorNumero = 0;
        for ($i = 0; $i < 10; $i++) {
            if (($sumaTotal + $i) % 10 == 0) {
                $menorNumero = $i;
                break;
            }
        }

        // El dígito verificador es el número encontrado en la etapa 5.
        return $menorNumero;
    }
}
