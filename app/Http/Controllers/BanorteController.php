<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\AdminModel;
use App\Models\MantenimientoModel;
use App\Models\NClientesModel;
use Error;
use Exception;
use Illuminate\Http\Request;
use nusoap_client;
use SoapFault;
use GuzzleHttp\Client;
use PhpParser\Node\Stmt\Catch_;

use function PHPUnit\Framework\isJson;

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: *');
require_once __DIR__ . '/../../../vendor/autoload.php';


class BanorteController extends Controller
{
    public function marcas()
    {
        try {
            //consume a GET request to the API Rest of Banorte and return the response n
            $service_url = 'https://api-pre.segurosbanorte.com/cotizadores/api/v1/producto/SEGURO%20DE%20AUTOM%C3%93VILES%20RESIDENTES/marca';
            $client = new \GuzzleHttp\Client();
            $credentials = base64_encode(env('BANORTE_USER') . ':' . env('BANORTE_SECRET'));

            $response = $client->request('GET', $service_url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/json',
                    'usuario' => 'BROKER',
                    'numOficina' => '0BB',
                    'nombreRamo' => 'Autos'
                ], 'json' => []
            ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                $response = $response->getBody();
                $response = json_decode($response);

                return response()->json(
                    $response->data->categorias[0]->marcas,
                    200
                );
            } else {
                $response = [
                    'status' => 'error',
                    'code' => $response->getStatusCode(),
                    'message' => 'Error al consumir el servicio'
                ];
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function submarcas($marca)
    {
        try {
            //consume a GET request to the API Rest of Banorte and return the response n
            $service_url = 'https://api-pre.segurosbanorte.com/cotizadores/api/v1/producto/SEGURO%20DE%20AUTOM%C3%93VILES%20RESIDENTES/marca/' . strtoupper($marca) . '/submarca';
            $client = new \GuzzleHttp\Client();
            $credentials = base64_encode(env('BANORTE_USER') . ':' . env('BANORTE_SECRET'));

            $response = $client->request('GET', $service_url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/json',
                    'usuario' => 'BROKER',
                    'numOficina' => '0BB',
                    'nombreRamo' => 'Autos'
                ], 'json' => []
            ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                $response = $response->getBody();
                $response = json_decode($response);


                return response()->json(
                    $response->data->categorias[0]->submarcas,
                    200
                );
            } else {
                $response = [
                    'status' => 'error',
                    'code' => $response->getStatusCode(),
                    'message' => 'Error al consumir el servicio'
                ];
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function vehiculos($marca, $submarca)
    {
        try {
            //consume a GET request to the API Rest of Banorte and return the response n
            $service_url = 'https://api-pre.segurosbanorte.com/cotizadores/api/v1/producto/SEGURO%20DE%20AUTOM%C3%93VILES%20RESIDENTES/vehiculo?nombreMarca=' . strtoupper($marca) . '&nombreSubmarca=' . strtoupper($submarca) . '';
            $client = new \GuzzleHttp\Client();
            $credentials = base64_encode(env('BANORTE_USER') . ':' . env('BANORTE_SECRET'));

            $response = $client->request('GET', $service_url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/json',
                    'usuario' => 'BROKER',
                    'numOficina' => '0BB',
                    'nombreRamo' => 'Autos'
                ], 'json' => []
            ]);

            //check if the response is ok
            if ($response->getStatusCode() == 200) {
                $response = $response->getBody();
                $response = json_decode($response);
                //check if $response->data->categorias[0]->modelosVehiculo, is not null
                return response()->json(
                    $response->data->categorias[0]->modelosVehiculo,
                    200
                );
            } else {
                $response = [
                    'status' => 'error',
                    'code' => $response->getStatusCode(),
                    'message' => 'Error al consumir el servicio'
                ];
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cotizar(Request $request)
    {
        //check is token is valid
        if ($request->header('key') == env('TOKEN')) {
            //check if the request is a POST request
            try {
                $brands = $this->marcas();
                //cast the response to json
                $brands = json_decode($brands->getContent());
                // return $brands;
                $brand_result = array();
                $c = 0;
                //search $request->marca in $brands
                foreach ($brands as $brand) {
                    if ($brand->nombre == $request->marca) {
                        $brand_result[$c] = $brand;
                    }
                }
                // return $brand_result;
                // return $brand_result[0]->nombre;
                $subbrands = $this->submarcas($brand_result[0]->nombre);


                //cast the response to json
                $subbrands = json_decode($subbrands->getContent());
                // return $subbrands;
                $subbrand_result = array();
                $c = 0;
                //search $request->marca in $brands
                foreach ($subbrands as $subbrand) {
                    if ($subbrand->nombre == $request->submarca) {
                        $subbrand_result[$c] = $subbrand;
                    }
                }
                // return $subbrand_result[0]->nombre;

                // return $brand_result[0]->nombre;
                $vehicles = $this->vehiculos($brand_result[0]->nombre, $subbrand_result[0]->nombre);

                //cast the response to json
                $vehicles = json_decode($vehicles->getContent());
                // return $vehicles;
                $vehicles_result = array();
                $c = 0;
                //search $request->marca in $brands
                foreach ($vehicles as $vehicle) {
                    //  if ($vehicle->nombre == $request->submarca) {
                    //      $vehicles_result[$c] = $vehicle;
                    //  }

                    if ($vehicle->anio == $request->model) {
                        //add all the vehicles to the array with percentage of similarity between the request->descripction and the vehicle->descripcion
                        $vehicles_result[$c] = $vehicle;
                        $vehicles_result[$c]->similarity = similar_text($request->descripcion, $vehicle->descripcion);
                        $c++;
                    }
                }

                //get vehicle of $vehicles_result with the highest percentage of similarity
                $vehicle_result = array();
                $c = 0;
                foreach ($vehicles_result as $vehicle) {
                    if ($c == 0) {
                        $vehicle_result[$c] = $vehicle;
                        $c++;
                    } else {
                        if ($vehicle_result[0]->similarity < $vehicle->similarity) {
                            $vehicle_result[0] = $vehicle;
                        }
                    }
                }
                // return $vehicle_result;
                $descuento = MantenimientoModel::where('provider', 'banorte')->first()->descuento;

                if ($descuento == 1) {
                    if ($request->cliente_id == null) {
                        //get gs_discount from admin where admin_id = 1
                        $discount = AdminModel::where('admin_id', 1)->first()->banorte_descuento;
                    } else {
                        //get client with client_id = $request->cliente_id
                        $client = NClientesModel::where('cliente_id', $request->cliente_id)->first();
                        //check if tipo_cliente is publico
                        if ($client->tipo_cliente == 'Público') {
                            //get gs_discount from admin where admin_id = 1
                            $discount = AdminModel::where('admin_id', 1)->first()->banorte_descuento;
                        } else {
                            //get gs_discount from client where client_id = $request->cliente_id
                            $discount = $client->banorte_descuento;
                        }
                    }
                } else {
                    $discount = 0;
                }
                // return $discount;

                $service_url = 'https://api-pre.segurosbanorte.com/cotizadores/api/v1/auto/cotizacion';
                $service_url_save_quote = "https://api-pre.segurosbanorte.com/cotizadores/api/v1/auto/guardar/cotizacion";
                $service_recalculate_url = "https://api-pre.segurosbanorte.com/cotizadores/api/v1/auto/recalcular/cotizacion";
                $client = new \GuzzleHttp\Client();
                $credentials = base64_encode(env('BANORTE_USER') . ':' . env('BANORTE_SECRET'));
                $data = array(
                    "nombreProducto" => "SEGURO DE AUTOMÓVILES RESIDENTES",
                    "claveIntermediario" => "9023",
                    // "claveIntermediario" => "12674",
                    "nombreCategoria" => "AUTOS RESIDENTES",
                    "codigoPostal" => $request->postal_code,
                    "municipio" => "",
                    "estado" => "",
                    "claveBanorte" => $vehicle_result[0]->claveBanorte,
                    "anio" => $request->model,
                    "nombreUso" => "PARTICULAR",
                    "nombreServicio" => "PARTICULAR",
                    "respuestas" => array(
                        array(
                            "enunciado" => "TIPO VALOR VEHICULO",
                            "valor" => "1",
                            "indice" => 0
                        ),
                        array(
                            "enunciado" => "INDIQUE EL SEXO DEL ASEGURADO",
                            "valor" => "MASCULINO",
                            "indice" => 10
                        ),
                        array(
                            "enunciado" => "¿DESEA COTIZAR PLANES COMPLEMENTARIOS?",
                            "valor" => "NO",
                            "indice" => 11
                        )
                    ),
                    "nombrePaquete" => "PREMIER"
                );
                // return response()->json($data);
                try {
                    $response = $client->request('POST', $service_url, [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Basic ' . $credentials,
                            'Content-Type' => 'application/json',
                            'usuario' => 'BROKER',
                            'numOficina' => '0BB',
                            'nombreRamo' => 'Autos'
                        ]
                        //send $data array as the request
                        , 'json' => $data
                    ]);
                } catch (\Throwable $th) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error al obtener cotización de Banorte',
                        'error' => $th->getMessage()
                    ]);
                } catch (\Exception $e) {
                    return response()->json($e->getMessage());
                }

                // return $client;

                //check if the response is ok
                if ($response->getStatusCode() == 200) {
                    $response = $response->getBody();
                    // return $response;
                    $response = json_decode($response);
                    // return response()->json($response);
                    // if response['operacion]['codigoOperacion] exists and is 0 return mensaje
                    if (isset($response->operacion->codigoOperacion) && $response->operacion->codigoOperacion == 0) {
                        // return $response->operacion->mensaje;
                        return response()->json([
                            'status' => 'error',
                            'message' => $response->operacion->mensaje
                        ], 403);
                    }

                    $data = $response->data->polizas[0]->certificado->items;
                    $items = array();
                    // foreach to data
                    foreach ($data as $item) {
                        $item_data = array(
                            "indice" => $item->indice,
                            "habilitada" => $item->habilitada,
                            "nombreTipoCobertura" => $item->nombreTipoCobertura,
                            "montoSumaAsegurada" => $item->montoSumaAsegurada,
                            "montoDeducible" => $item->montoDeducible,
                        );
                        // push array to items
                        array_push($items, $item_data);
                    }
                    // return response()->json($items);
                    $resumenCotizacion = $response->data->polizas[0]->resumenCotizacion;
                    $opcionesPago = $response->data->polizas[0]->conductosCobro;

                    // convert json to array
                    $data = json_decode(json_encode($data), true);
                    $resumenCotizacion = json_decode(json_encode($resumenCotizacion), true);
                    $opcionesPago = json_decode(json_encode($opcionesPago), true);

                    $client_save = new \GuzzleHttp\Client();
                    $credentials_save = base64_encode(env('BANORTE_USER') . ':' . env('BANORTE_SECRET'));

                    // crear un recalculo de la cotizacion con los datos del cliente

                    try {
                        $recalculate_quote_request = array(
                            "nombreProducto" => "SEGURO DE AUTOMÓVILES RESIDENTES",
                            "claveIntermediario" => "9023",
                            "nombreCategoria" => "AUTOS RESIDENTES",
                            "codigoPostal" => $request->postal_code,
                            "municipio" => "",
                            "estado" => "",
                            "claveBanorte" => $vehicle_result[0]->claveBanorte,
                            "anio" => $request->model,
                            "nombreUso" => "PARTICULAR",
                            "nombreServicio" => "PARTICULAR",
                            "respuestas" => array(
                                array(
                                    "enunciado" => "TIPO VALOR VEHICULO",
                                    "valor" => "1",
                                    "indice" => 0
                                ),
                                array(
                                    "enunciado" => "INDIQUE EL SEXO DEL ASEGURADO",
                                    "valor" => "MASCULINO",
                                    "indice" => 10
                                ),
                                array(
                                    "enunciado" => "¿DESEA COTIZAR PLANES COMPLEMENTARIOS?",
                                    "valor" => "NO",
                                    "indice" => 11
                                )
                            ),
                            "nombrePaquete" => "PREMIER",
                            "items" => $items,
                            "nombreVigencia" => "ANUAL",
                            "nombreFormaPago" => "PAGO ÚNICO",
                            "valorDescuento" => $discount,
                        );

                        $response_recalculate = $client_save->request('POST', $service_recalculate_url, [
                            'headers' => [
                                'Accept' => 'application/json',
                                'Content-Type' => 'application/json',
                                'Authorization' => 'Basic ' . $credentials_save,
                                'Content-Type' => 'application/json',
                                'usuario' => 'BROKER',
                                'numOficina' => '0BB',
                                'nombreRamo' => 'Autos'
                            ]
                            //send $data array as the request
                            , 'json' => $recalculate_quote_request
                        ]);

                        if ($response_recalculate->getStatusCode() == 200) {
                            $response_recalculate = $response_recalculate->getBody();
                            $response_recalculate = json_decode($response_recalculate);
                            $data = $response_recalculate->data->polizas[0]->certificado->items;
                            $items = array();
                            // foreach to data
                            foreach ($data as $item) {
                                $item_data = array(
                                    "indice" => $item->indice,
                                    "habilitada" => $item->habilitada,
                                    "nombreTipoCobertura" => $item->nombreTipoCobertura,
                                    "montoSumaAsegurada" => $item->montoSumaAsegurada,
                                    "montoDeducible" => $item->montoDeducible,
                                );
                                // push array to items
                                array_push($items, $item_data);
                            }
                            $resumenCotizacion = $response_recalculate->data->polizas[0]->resumenCotizacion;
                            // return response()->json($save_quote_request);

                            try {

                                $save_quote_request = array(
                                    "nombreProducto" => "SEGURO DE AUTOMÓVILES RESIDENTES",
                                    "claveIntermediario" => "9023",
                                    "nombreCategoria" => "AUTOS RESIDENTES",
                                    "estado" => $request->state,
                                    "municipio" => $request->city,
                                    "codigoPostal" => $request->postal_code,
                                    "claveBanorte" => $vehicle_result[0]->claveBanorte,
                                    "anio" => $request->model,
                                    "nombreUso" => "PARTICULAR",
                                    "nombreServicio" => "PARTICULAR",
                                    "respuestas" => array(
                                        array(
                                            "enunciado" => "TIPO VALOR VEHICULO",
                                            "valor" => "1",
                                            "indice" => 0
                                        ),
                                        array(
                                            "enunciado" => "INDIQUE EL SEXO DEL ASEGURADO",
                                            "valor" => "MASCULINO",
                                            "indice" => 10
                                        ),
                                        array(
                                            "enunciado" => "¿DESEA COTIZAR PLANES COMPLEMENTARIOS?",
                                            "valor" => "NO",
                                            "indice" => 11
                                        )
                                    ),
                                    "nombrePaquete" => "PREMIER",
                                    "items" => $items,
                                    "nombreVigencia" => "ANUAL",
                                    // "valorDescuento" => $discount,
                                    "nombreFormaPago" => "PAGO ÚNICO",
                                    "resumenCotizacion" => $resumenCotizacion,
                                    "prospecto" => array(
                                        "tipoPersona" => $request->tipoPersona,
                                        "titulo" => "ALMTE.",
                                        "apellidoPaterno" => $request->last_name,
                                        "apellidoMaterno" => $request->last_name2,
                                        "nombre" => $request->first_name,
                                        "razonSocial" => $request->razonSocial,
                                        "formasContacto" => array(
                                            array(
                                                "nombre" => "email",
                                                "valor" => $request->email
                                            ),
                                            array(
                                                "nombre" => "telefono",
                                                "valor" => $request->phone
                                            )
                                        ),
                                    ),
                                    "referencia" => "PRUEBA PARA BANORTE",
                                    "valorDescuento" => $discount,

                                );

                                // return response()->json([
                                //     'status' => 'data request',
                                //     'data' => $save_quote_request
                                // ], 200);
                                $response2 = $client_save->request('POST', $service_url_save_quote, [
                                    'headers' => [
                                        'Accept' => 'application/json',
                                        'Content-Type' => 'application/json',
                                        'Authorization' => 'Basic ' . $credentials_save,
                                        'Content-Type' => 'application/json',
                                        'usuario' => 'BROKER',
                                        'numOficina' => '0BB',
                                        'nombreRamo' => 'Autos'
                                    ]
                                    //send $data array as the request
                                    , 'json' => $save_quote_request
                                ]);
                                // return $client_save;
                                if ($response2->getStatusCode() == 200) {
                                    $response2 = $response2->getBody();
                                    $response2 = json_decode($response2);
                                    return response()->json(
                                        [
                                            "status" => "success",
                                            "data" => $response2,
                                            "opcionesPago" => $opcionesPago,
                                            "discount" => $discount,
                                        ],
                                        200
                                    );
                                } else {
                                    return response()->json(
                                        [
                                            "status" => "error",
                                            "data" => $response,
                                            "save_quote" => $response2,
                                            "discount" => $discount
                                        ],
                                        200
                                    );
                                }
                            } catch (\Error $e) {
                                return response()->json(
                                    [
                                        "status" => "error error",
                                        "data" => $response,
                                        "save_quote" => $e,
                                        "discount" => $discount
                                    ],
                                    200
                                );
                            }
                        } else {
                            return response()->json(
                                [
                                    "status" => "error",
                                    "data" => $response,
                                    "save_quote" => $response_recalculate,
                                    "discount" => $discount
                                ],
                                200
                            );
                        }
                    } catch (\Throwable $th) {
                        // return error message
                        return response()->json(
                            [
                                "status" => "error",
                                "data" => $th->getMessage(),
                            ],
                            200
                        );
                    }
                } else {
                    $response = [
                        'status' => 'error',
                        'code' => $response->getStatusCode(),
                        'message' => 'Error al consumir el servicio'
                    ];
                }
            } catch (\Error $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e
                ], 201);
            }
        } else {
            return response()->json(['error' => 'Token invalido'], 500);
        }
    }


    public function setEmision(Request $request)
    {
        if ($request->header("key") == env("TOKEN")) {
            try {
                // request de emision de una poliza sin tarheta de credito
                $service_url = "https://api-pre.segurosbanorte.com/cotizadores/api/v1/auto/poliza";
                $client = new \GuzzleHttp\Client();
                $credentials = base64_encode(env('BANORTE_USER') . ':' . env('BANORTE_SECRET'));
                $emision_request = array(
                    "numCotizacion" => $request->input("numCotizacion"),
                    "vehiculoEmision" => array(
                        "numPlaca" => $request->input("numPlaca"),
                        "numSerie" => $request->input("numSerie"),
                        "numMotor" => $request->input("numMotor"),
                        "nciRepuve" => $request->input("nciRepuve"), // opcional
                    ),
                    "nombreBeneficiario" => $request->input("nombreBeneficiario"), // opcional
                    "referencia" => 'PRUEBA PARA BANORTE', // opcional
                    "nombreServicio" => 'PARTICULAR',
                    "contratante" => array(
                        "tipoPersona" => $request->input("contratante_tipoPersona"), // obligatorio FISICA Ó MORAL
                        "rfc" => $request->input("contratante_rfc"),
                        "fechaNacimiento" => $request->input("contratante_fechaNacimiento"), // obligatorio patra persona FISICA formato: yyyy-mm-dd hh:mm:ss
                        "nombre" => $request->input("contratante_nombre"), // obligatorio para persona fisica
                        "apellidoPaterno" => $request->input("contratante_apellidoPaterno"), // obligatorio para persona fisica
                        "apellidoMaterno" => $request->input("contratante_apellidoMaterno"), //opcional
                        "estadoCivil" => $request->input("contratante_estadoCivil"), // obligatorio para persona FISICA, solo puede ser { SOLTERO, CASADO, DIVORCIADO, VIUDO, UNIÓN LIBRE}
                        "sexo" => $request->input("contratante_sexo"), // , obligatorio para persona FISICA, solo puede ser {FEMENINO o MASCULINO}
                        "razonSocial" => $request->input("contratante_razonSocial"), // obligatorio para persona Moral
                        "domicilio" => array(
                            "calle" => $request->input("contratante_calle"),
                            "numero" => $request->input("contratante_numero"),
                            "codigoPostal" => $request->input("contratante_codigoPostal"),
                            "estado" => $request->input("contratante_estado"),
                            "municipio" => $request->input("contratante_municipio"),
                            "colonia" => $request->input("contratante_colonia"),
                        ),
                        "formasContacto" => array(
                            array(
                                "nombre" => "email",
                                "valor" => $request->input("contratante_email"),
                            ),
                            array(
                                "nombre" => "telefono",
                                "valor" => $request->input("contratante_telefono"),
                            ),
                        ),
                    ),
                    "asegurado" => array(
                        "tipoPersona" => $request->input("asegurado_tipoPersona"), // obligatorio FISICA Ó MORAL
                        "rfc" => $request->input("asegurado_rfc"), // obligatorio
                        "fechaNacimiento" => $request->input("asegurado_fechaNacimiento"), // obligatorio patra persona FISICA formato: yyyy-mm-dd hh:mm:ss
                        "nombre" => $request->input("asegurado_nombre"), // obligatorio para persona fisica
                        "apellidoPaterno" => $request->input("asegurado_apellidoPaterno"), // obligatorio para persona fisica
                        "apellidoMaterno" => $request->input("asegurado_apellidoMaterno"), // opcional
                        "estadoCivil" => $request->input("asegurado_estadoCivil"), // obligatorio para persona FISICA, solo puede ser { SOLTERO, CASADO, DIVORCIADO, VIUDO, UNIÓN LIBRE}
                        "sexo" => $request->input("asegurado_sexo"), // , obligatorio para persona FISICA, solo puede ser {FEMENINO o MASCULINO}
                        "razonSocial" => $request->input("asegurado_razonSocial"), // obligatorio para persona Moral
                        "domicilio" => array(
                            "calle" => $request->input("asegurado_calle"),
                            "numero" => $request->input("asegurado_numero"),
                            "codigoPostal" => $request->input("asegurado_codigoPostal"),
                            "estado" => $request->input("asegurado_estado"),
                            "municipio" => $request->input("asegurado_municipio"),
                            "colonia" => $request->input("asegurado_colonia"),
                        ),
                        "sic" => $request->input("asegurado_sic"), // opcional
                        "formasContacto" => array(
                            array(
                                "nombre" => "email",
                                "valor" => $request->input("asegurado_email"),
                            ),
                            array(
                                "nombre" => "telefono",
                                "valor" => $request->input("asegurado_telefono"),
                            ),
                        ),
                    ),
                    "conductores" => array( // se requuere saber quien es el conductor principal
                        array(
                            "nombre" => $request->input("conductor_nombre"), // obligatorio
                            "apellidoPaterno" => $request->input("conductor_apellidoPaterno"), // obligatorio
                            "apellidoMaterno" => $request->input("conductor_apellidoMaterno"), // opcional
                            "sexo" => $request->input("conductor_sexo"),
                        ),
                    ),
                    "emision" => array(
                        "sucursal" => "", // opcional
                        "numEmpleado" => "", // opcional
                    ),
                    "conductoCobro" => array(
                        "conducto" => "EFECTIVO"
                        // "conducto" => $request->input("conducto"),
                        // "banco" => $request->input("banco"),
                        // "tipoTarjeta" => $request->input("tipoTarjeta"),
                        // "numConducto" => $request->input("numConducto"), // numero de tarjeta o cuenta
                        // "mesVencimiento" => $request->input("mesVencimiento"), // formato: mm solo para tarjeta de credito y debito
                        // "anioVencimiento" => $request->input("anioVencimiento"), // solo aplica para tarjeta de credito y debito formato: yy
                        // "codSeguridad" => $request->input("codSeguridad"),
                    ),
                );

                // return request in json
                // return response()->json($emision_request);

                $response = $client->request('POST', $service_url, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic ' . $credentials,
                        'Content-Type' => 'application/json',
                        'usuario' => 'BROKER',
                        'numOficina' => '0BB',
                        'nombreRamo' => 'Autos'
                    ]
                    //send $data array as the request
                    , 'json' => $emision_request
                ]);

                //check if the response is ok
                if ($response->getStatusCode() == 200) {
                    //get the response body
                    $body = $response->getBody();
                    //decode the json response
                    $data = json_decode($body);
                    //return the data
                    return response()->json(
                        [
                            "status" => "success",
                            "data" => $data,
                        ],
                        200
                    );
                } else {
                    //return error message
                    return response()->json(
                        [
                            "status" => "error",
                            "code" => $response->getStatusCode(),
                            "message" => "Error al obtener la cotizacion",
                        ],
                        500
                    );
                }
            } catch (\Throwable $th) {
                return response()->json(
                    [
                        "status" => "error",
                        "message" => $th->getMessage(),
                    ],
                    500
                );
            } catch (\Exception $e) {
                return response()->json(
                    [
                        "status" => "error",
                        "message" => $e->getMessage(),
                    ],
                    500
                );
            } catch (\Error $e) {
                return response()->json(
                    [
                        "status" => "error",
                        "message" => $e->getMessage(),
                    ],
                    500
                );
            }
        }
    }
}
