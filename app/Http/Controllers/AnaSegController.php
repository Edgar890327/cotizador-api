<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\AdminModel;
use App\Models\ClientsModel;
use App\Models\MantenimientoModel;
use App\Models\NClientesModel;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use nusoap_client;
use Psy\Exception\ThrowUpException;
use SimpleXMLElement;
use SimpleXMLIterator;
use SoapClient;
use SoapFault;
use Throwable;

use function PHPUnit\Framework\throwException;

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: *');

class AnaSegController extends Controller
{
    public $client;
    public $client2;
    public $url;
    public $url2;
    public $negocio;
    public $usuario;
    public $password;


    public function __construct()
    {
        $this->url = "https://server.anaseguros.com.mx/ananetws/service.asmx?wsdl";
        $this->url2 = "https://server.anaseguros.com.mx/ananetws/service.asmx";
        try {
            $this->client = $this->getClient($this->url);
            $this->client2 = $this->getClient($this->url2);
            $this->negocio = env("ANA_NEGOCIO");
            $this->usuario = env("ANA_USER");
            $this->password = env("ANA_SECRET");
        } catch (Handler $fault) {
            dd("Fallo", $fault);
        }
    }

    public function getClient($url)
    {
        try {
            // $client = new SoapClient($url);
            $client = new nusoap_client(
                $url,
                true,
            );

            // return $client;
        } catch (SoapFault $error) {
            //show error
            printf("Error: %s", $error->getMessage());
        }
    }

    // function getModelos()
    // {
    //     try {
    //         $client = new SoapClient($this->url);
    //         $modelosXML = $client->Modelo(["Negocio" => $this->negocio, "Usuario" => $this->usuario, "Clave" => $this->password]);
    //         $modelosResp = json_decode(json_encode(simplexml_load_string($modelosXML->ModeloResult)), true);
    //         // dd($modelosXML);
    //         if ($modelosResp['modelo']) {
    //             return response()->json(['modelos' => $modelosResp['modelo']], 200);
    //         } else {
    //             return response()->json(['error' => "Modelos no encontrados"], 404);
    //         }
    //     } catch (SoapFault $fault) {
    //         dd($fault);
    //     }
    // }

    function getModelos()
    {

        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
        <soapenv:Header/>
        <soapenv:Body>
           <tem:Modelo>
           <tem:Negocio>' . $this->negocio . '</tem:Negocio>
           <tem:Usuario>' . $this->usuario . '</tem:Usuario>
           <tem:Clave>' . $this->password . '</tem:Clave>
           </tem:Modelo>
        </soapenv:Body>
     </soapenv:Envelope>';
        try {

            $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
            $result = $client1->send(
                $soap,
                'http://tempuri.org/Modelo'
            );
            // return $result;
            $response = json_decode(json_encode($result), true);
            $response = $response['ModeloResult'];
            $modelosResp = json_decode(json_encode(simplexml_load_string($response)), true);
            // dd($modelosXML);
            if ($modelosResp['modelo']) {
                return response()->json(['modelos' => $modelosResp['modelo']], 200);
            } else {
                return response()->json(['error' => "Modelos no encontrados"], 404);
            }
        } catch (SoapFault $th) {
            //throw $th;
        }
    }

    public function getMarcas($model)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:Marca>
                            <tem:Modelo>' . $model . '</tem:Modelo>
                            <tem:Categoria>100</tem:Categoria>
                            <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                            <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                            <tem:Clave>' . $this->password . '</tem:Clave>
                        </tem:Marca>
                    </soapenv:Body>
                </soapenv:Envelope>';
        try {
            $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
            $result = $client1->send(
                $soap,
                'http://tempuri.org/Marca'
            );
            // return $result;
            $response = json_decode(json_encode($result), true);
            $response = $response['MarcaResult'];

            $marcas = $this->convertXMLtoArray(simplexml_load_string($response)->marca);
            if ($marcas) {
                return response()->json(['marcas' => $marcas], 200);
            } else {
                return response()->json(['error' => "Marcas no encontradas"], 404);
            }
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    public function getSubMarca($marca, $modelo)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:SubMarca>
                            <!--Optional:-->
                            <tem:Marca>' . $marca . '</tem:Marca>
                            <tem:Modelo>' . $modelo . '</tem:Modelo>
                            <tem:Categoria>100</tem:Categoria>
                            <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                            <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                            <tem:Clave>' . $this->password . '</tem:Clave>
                        </tem:SubMarca>
                    </soapenv:Body>
                </soapenv:Envelope>';
        try {
            $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
            $result = $client1->send(
                $soap,
                'http://tempuri.org/SubMarca'
            );
            // return $result;
            $response = json_decode(json_encode($result), true);
            $response = $response['SubMarcaResult'];

            // cast $response to json
            $submarcas = json_decode(json_encode(simplexml_load_string($response)), true);
            if ($submarcas) {
                return response()->json($submarcas['submarca'], 200);
            } else {
                return response()->json(['error' => "Submarcas no encontradas"], 404);
            }
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    public function getLocations(Request $request)
    {

        // return response()->json([
        //    [
        //     "id_municipio" => "1",
        //     "colonia" => "CENTRO",
        //    ],
        //    [
        //     "id_municipio" => "1",
        //     "colonia" => "LA CRUZ",
        //    ],
        //    [
        //     "id_municipio" => "1",
        //     "colonia" => "EL CALVARIO",
        //    ],
        //    [
        //     "id_municipio" => "1",
        //     "colonia" => "VALLE DE LAS FLORES",
        //    ], [
        //     "id_municipio" => "1",
        //     "colonia" => "PUEBLO VIEJO",
        //    ]
        // ], 200);

        if ($request->header('key') == env('TOKEN')) {
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:ColxCP>
                            <tem:CP> ' . $request->codigo_postal . '</tem:CP>
                            <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                            <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                            <tem:Clave>' . $this->password . '</tem:Clave>
                        </tem:ColxCP>
                    </soapenv:Body>
                </soapenv:Envelope>';
            try {
                $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
                $result = $client1->send(
                    $soap,
                    'http://tempuri.org/ColxCP'
                );
                // return $result;
                $response = json_decode(json_encode($result), true);
                $response = $response['ColxCPResult'];

                // cast $response to json
                $col = $this->convertXMLtoArrayCol(simplexml_load_string($response)->colonias);

                if ($col) {
                    return response()->json($col, 200);
                } else {
                    return response()->json(['error' => "Colonias no encontradas"], 404);
                }
            } catch (SoapFault $th) {
                //throw $th;
                dd($th);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    public function getCol($codigo_postal)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:ColxCP>
                            <tem:CP> ' . $codigo_postal . '</tem:CP>
                            <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                            <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                            <tem:Clave>' . $this->password . '</tem:Clave>
                        </tem:ColxCP>
                    </soapenv:Body>
                </soapenv:Envelope>';
        try {
            $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
            $result = $client1->send(
                $soap,
                'http://tempuri.org/ColxCP'
            );
            // return $result;
            $response = json_decode(json_encode($result), true);
            $response = $response['ColxCPResult'];

            // cast $response to json
            $col = $this->convertXMLtoArrayCol(simplexml_load_string($response)->colonias);
            dd($response, $col);
            if ($col) {
                return response()->json($col, 200);
            } else {
                return response()->json(['error' => "Colonias no encontradas"], 404);
            }
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    //get bancos
    public function getBanks(Request $request)
    {
        //check if token is valid
        if ($request->header('key') == env('TOKEN')) {
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:Bancos>
                            <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                            <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                            <tem:Clave>' . $this->password . '</tem:Clave>
                        </tem:Bancos>
                    </soapenv:Body>
                </soapenv:Envelope>';
            try {
                $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
                $result = $client1->send(
                    $soap,
                    'http://tempuri.org/Bancos'
                );

                $response = json_decode(json_encode($result), true);
                $response = $response['BancosResult'];

                //encoding to utf8
                $response = mb_convert_encoding($response, 'UTF-8', 'UTF-8');

                $arrayResp = json_decode(json_encode(simplexml_load_string($response)), true);

                //return response()->json($arrayResp, 200);
                //for each bank get the name and id that has separated by a score
                $banks = [];
                foreach ($arrayResp['bancos'] as $bank) {
                    $bank = explode('-', $bank);
                    $banks[] = [
                        'id' => $bank[0],
                        'name' => $bank[1]
                    ];
                }
                return response()->json($banks, 200);

                return response()->json($arrayResp);

                // cast $response to json
                $bancos = json_decode(json_encode(simplexml_load_string($response)), true);
                if ($bancos) {
                    return response()->json($bancos['banco'], 200);
                } else {
                    return response()->json(['error' => "Bancos no encontrados"], 404);
                }
            } catch (Exception $th) {
                //throw $th;
                dd($th);
            }
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    // get colors of cars
    public function getColors(Request $request)
    {
        //check if token is valid
        if ($request->header('key') == env('TOKEN')) {

            $jayParsedAry = [
                "transacciones" => [
                    "color" => [
                        [
                            "id" => "5",
                            "text" => "AMARILLO"
                        ],
                        [
                            "id" => "34",
                            "text" => "AMATISTA"
                        ],
                        [
                            "id" => "55",
                            "text" => "AQUAMARINA"
                        ],
                        [
                            "id" => "20",
                            "text" => "ARENA"
                        ],
                        [
                            "id" => "2",
                            "text" => "AZUL"
                        ],
                        [
                            "id" => "35",
                            "text" => "AZUL ACERO"
                        ],
                        [
                            "id" => "38",
                            "text" => "AZUL ADRIATICO"
                        ],
                        [
                            "id" => "19",
                            "text" => "AZUL CLARO"
                        ],
                        [
                            "id" => "89",
                            "text" => "AZUL GRAFITO"
                        ],
                        [
                            "id" => "14",
                            "text" => "AZUL MARINO"
                        ],
                        [
                            "id" => "24",
                            "text" => "AZUL METALICO"
                        ],
                        [
                            "id" => "25",
                            "text" => "AZUL OSCURO"
                        ],
                        [
                            "id" => "75",
                            "text" => "AZUL TURQUESA"
                        ],
                        [
                            "id" => "97",
                            "text" => "AZUL ULTRAMAR"
                        ],
                        [
                            "id" => "15",
                            "text" => "BEIGE"
                        ],
                        [
                            "id" => "95",
                            "text" => "BEIGE METALICO"
                        ],
                        [
                            "id" => "1",
                            "text" => "BLANCO"
                        ],
                        [
                            "id" => "22",
                            "text" => "BLANCO C/AMARILLO"
                        ],
                        [
                            "id" => "91",
                            "text" => "BLANCO C/NEGRO"
                        ],
                        [
                            "id" => "94",
                            "text" => "BRONCE"
                        ],
                        [
                            "id" => "12",
                            "text" => "CAFE"
                        ],
                        [
                            "id" => "73",
                            "text" => "CALYPSO METALICO"
                        ],
                        [
                            "id" => "65",
                            "text" => "CANELA"
                        ],
                        [
                            "id" => "71",
                            "text" => "CASTA�O METALICO"
                        ],
                        [
                            "id" => "39",
                            "text" => "CEREZA"
                        ],
                        [
                            "id" => "17",
                            "text" => "CHAMPANGE"
                        ],
                        [
                            "id" => "58",
                            "text" => "CHICLE"
                        ],
                        [
                            "id" => "60",
                            "text" => "COBRE"
                        ],
                        [
                            "id" => "62",
                            "text" => "CORAL"
                        ],
                        [
                            "id" => "48",
                            "text" => "CREMA"
                        ],
                        [
                            "id" => "16",
                            "text" => "DORADO"
                        ],
                        [
                            "id" => "70",
                            "text" => "DUSTY"
                        ],
                        [
                            "id" => "23",
                            "text" => "ECOLOGICO"
                        ],
                        [
                            "id" => "42",
                            "text" => "GRAFITO"
                        ],
                        [
                            "id" => "59",
                            "text" => "GRANITO PERLADO"
                        ],
                        [
                            "id" => "3",
                            "text" => "GRIS"
                        ],
                        [
                            "id" => "10",
                            "text" => "GRIS ACERO"
                        ],
                        [
                            "id" => "26",
                            "text" => "GRIS ARENA"
                        ],
                        [
                            "id" => "63",
                            "text" => "GRIS C/ ROJO"
                        ],
                        [
                            "id" => "85",
                            "text" => "GRIS CLARO"
                        ],
                        [
                            "id" => "92",
                            "text" => "GRIS METALICO"
                        ],
                        [
                            "id" => "93",
                            "text" => "GRIS OSCURO"
                        ],
                        [
                            "id" => "36",
                            "text" => "GRIS OXFORD"
                        ],
                        [
                            "id" => "6",
                            "text" => "GRIS PERLA"
                        ],
                        [
                            "id" => "44",
                            "text" => "GRIS PLATA"
                        ],
                        [
                            "id" => "90",
                            "text" => "GRIS TITANIO"
                        ],
                        [
                            "id" => "31",
                            "text" => "GUINDA"
                        ],
                        [
                            "id" => "98",
                            "text" => "INDIGO"
                        ],
                        [
                            "id" => "81",
                            "text" => "LILA"
                        ],
                        [
                            "id" => "82",
                            "text" => "MALVA"
                        ],
                        [
                            "id" => "50",
                            "text" => "MARFIL"
                        ],
                        [
                            "id" => "7",
                            "text" => "MARRON"
                        ],
                        [
                            "id" => "49",
                            "text" => "MARRON METALICO"
                        ],
                        [
                            "id" => "64",
                            "text" => "METALICO"
                        ],
                        [
                            "id" => "21",
                            "text" => "MOKA"
                        ],
                        [
                            "id" => "83",
                            "text" => "MORA"
                        ],
                        [
                            "id" => "32",
                            "text" => "MORADO"
                        ],
                        [
                            "id" => "87",
                            "text" => "MORADO OSCURO"
                        ],
                        [
                            "id" => "47",
                            "text" => "NARANJA"
                        ],
                        [
                            "id" => "8",
                            "text" => "NEGRO"
                        ],
                        [
                            "id" => "88",
                            "text" => "OPALO"
                        ],
                        [
                            "id" => "76",
                            "text" => "OPORTO"
                        ],
                        [
                            "id" => "78",
                            "text" => "ORO"
                        ],
                        [
                            "id" => "40",
                            "text" => "ORO METALICO"
                        ],
                        [
                            "id" => "53",
                            "text" => "PEWTER METALICO"
                        ],
                        [
                            "id" => "18",
                            "text" => "PLATA"
                        ],
                        [
                            "id" => "41",
                            "text" => "PLATA METALICO"
                        ],
                        [
                            "id" => "56",
                            "text" => "PLATA OLIVO"
                        ],
                        [
                            "id" => "67",
                            "text" => "PLATA TORNASOL"
                        ],
                        [
                            "id" => "37",
                            "text" => "PLATEADO"
                        ],
                        [
                            "id" => "69",
                            "text" => "PLATINO"
                        ],
                        [
                            "id" => "99",
                            "text" => "PLATINO"
                        ],
                        [
                            "id" => "51",
                            "text" => "PURPURA"
                        ],
                        [
                            "id" => "4",
                            "text" => "ROJO"
                        ],
                        [
                            "id" => "79",
                            "text" => "ROJO BRAVIO"
                        ],
                        [
                            "id" => "84",
                            "text" => "ROJO CEREZA"
                        ],
                        [
                            "id" => "57",
                            "text" => "ROJO GRANATE"
                        ],
                        [
                            "id" => "77",
                            "text" => "ROJO METALICO"
                        ],
                        [
                            "id" => "54",
                            "text" => "ROJO PERLADO"
                        ],
                        [
                            "id" => "33",
                            "text" => "ROJO TORNADO"
                        ],
                        [
                            "id" => "86",
                            "text" => "ROJO VERONA"
                        ],
                        [
                            "id" => "13",
                            "text" => "ROSA METALICO"
                        ],
                        [
                            "id" => "28",
                            "text" => "SHEDRON"
                        ],
                        [
                            "id" => "0",
                            "text" => "SIN COLOR"
                        ],
                        [
                            "id" => "46",
                            "text" => "TERRACOTA"
                        ],
                        [
                            "id" => "61",
                            "text" => "TITANIO"
                        ],
                        [
                            "id" => "72",
                            "text" => "TURQUESA"
                        ],
                        [
                            "id" => "45",
                            "text" => "UVA"
                        ],
                        [
                            "id" => "29",
                            "text" => "VERDE"
                        ],
                        [
                            "id" => "66",
                            "text" => "VERDE AGUA"
                        ],
                        [
                            "id" => "80",
                            "text" => "VERDE C/BLANCO"
                        ],
                        [
                            "id" => "96",
                            "text" => "VERDE CIPRES"
                        ],
                        [
                            "id" => "43",
                            "text" => "VERDE ESMERALDA"
                        ],
                        [
                            "id" => "52",
                            "text" => "VERDE GRAFITO"
                        ],
                        [
                            "id" => "27",
                            "text" => "VERDE METALICO"
                        ],
                        [
                            "id" => "30",
                            "text" => "VERDE OSCURO"
                        ],
                        [
                            "id" => "9",
                            "text" => "VERDE PERLADO"
                        ],
                        [
                            "id" => "11",
                            "text" => "VINO"
                        ],
                        [
                            "id" => "68",
                            "text" => "VIOLETA"
                        ],
                        [
                            "id" => "74",
                            "text" => "ZAFIRO"
                        ]
                    ],
                    "error" => "",
                    "xmlns" => ""
                ]
            ];

            return response()->json(
                [
                    'data' => $jayParsedAry,
                    'status' => 200,
                    'message' => 'success'
                ]
            );
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    }

    // get ocupations list
    public function getOcupations(Request $request)
    {
        // retun thar information in json fromat
        // {
        //     "transacciones": {
        //       "ocupacion": [
        //         "0":{"id":"A1","text":"Asesor"},
        //         "0":{"id":"D2","text":"Docente"},
        //         "0":{"id":"D1","text":"Doctor"},
        //         "0":{"id":"E1","text":"Estudiante"},
        //         "0":{"id":"I1","text":"Ingeniero"},
        //         "0":{"id":"L1","text":"Licenciado"},
        //         "0":{"id":"O1","text":"Otro"}
        //       ],
        //       "error": ""
        //     }
        //   }

        $data = [
            'transacciones' => [
                'ocupacion' => [
                    [
                        'id' => 'A1',
                        'text' => 'Asesor'
                    ],
                    [
                        'id' => 'D2',
                        'text' => 'Docente'
                    ],
                    [
                        'id' => 'D1',
                        'text' => 'Doctor'
                    ],
                    [
                        'id' => 'E1',
                        'text' => 'Estudiante'
                    ],
                    [
                        'id' => 'I1',
                        'text' => 'Ingeniero'
                    ],
                    [
                        'id' => 'L1',
                        'text' => 'Licenciado'
                    ],
                    [
                        'id' => 'O1',
                        'text' => 'Otro'
                    ]
                ],
                'error' => ''
            ]
        ];

        // return
        return response()->json(
            [
                'data' => $data,
                'status' => 200,
                'message' => 'success'
            ]
        );
    }

    // get nacionalities list
    public function getNacionalities(Request $request)
    {
        // return thar information in json fromat
        // {
        //     "transacciones": {
        //       "nacionalidad": [
        // //         0:{"C1","Canadiense"},
        // 1:{"C1","Estadounidense"},
        // 2:{"C1","Mexicana"},
        // 3:{"C1","Otros"}
        //       ],
        //       "error": ""
        //     }
        //   }
        $data = [
            'transacciones' => [
                'nacionalidad' => [
                    [
                        'C1',
                        'Canadiense'
                    ],
                    [
                        'E1',
                        'Estadounidense'
                    ],
                    [
                        'M1',
                        'Mexicana'
                    ],
                    [
                        'O1',
                        'Otros'
                    ]
                ],
                'error' => ''
            ]
        ];

        // return
        return response()->json(
            [
                'data' => $data,
                'status' => 200,
                'message' => 'success'
            ]
        );
    }

    public function getAMIS($modelo)
    {
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:CatVeh>
                            <tem:ModeloMin>' . $modelo . '</tem:ModeloMin>
                            <tem:ModeloMax>' . $modelo . '</tem:ModeloMax>
                            <tem:Categ>100</tem:Categ>
                            <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                            <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                            <tem:Clave>' . $this->password . '</tem:Clave>
                        </tem:CatVeh>
                    </soapenv:Body>
                </soapenv:Envelope>';
        try {
            $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
            $result = $client1->send(
                $soap,
                'http://tempuri.org/CatVeh'
            );
            // return $result;
            $response = json_decode(json_encode($result), true);
            $response = $response['CatVehResult'];

            //iterate $response by <vehiculo> and return array
            $vehiculos = $this->convertXMLtoArrayCat(simplexml_load_string($response)->vehiculo);
            // dd($vehiculos);

            //get $response with attributes of $response and convert to json
            // $cat = json_decode(json_encode(simplexml_load_string($response)), true);
            // dd($response, $cat);
            if ($vehiculos) {
                return response()->json($vehiculos, 200);
            } else {
                return response()->json(['error' => "Submarcas no encontradas"], 404);
            }
        } catch (SoapFault $th) {
            //throw $th;
            dd($th);
        }
    }

    public function setCotizacion(Request $request)
    {
        // return response()->json($request->all(), 200);
        $discount = 0;
        $vehiculos = $this->getAMIS($request->modelo);
        $vehiculos = json_decode($vehiculos->getContent(), true);
        // $search is equals to upercase of $smarca;
        $marca_result = array();
        $c = 0;
        try {
            $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 404);
        }
        $descuento = MantenimientoModel::where('provider', "ana")->first();
        $discount = $descuento->descuento;
        if ($discount == 1) {
            if ($cliente->tipo_cliente == 'Público') {
                // get ana_descuento de admin 1
                $admin = AdminModel::where('admin_id', 1)->first();
                $discount = $admin->ana_descuento;
            } else {
                // get ana_descuento del cliente
                $discount = $cliente->ana_descuento;
            }
        } else {
            $discount = 0;
        }

        //search in $vehiculos array by marca and submarca and save in $marca_result
        foreach ($vehiculos as $vehiculo) {
            if (strpos($vehiculo["marca"], strtoupper($request->marca)) !== false) {
                if (strpos($vehiculo["submarca"], strtoupper($request->submarca)) !== false) {

                    //compare $vehiculo["descripcion"] with $request->descripcion and save the more similar in $marca_result
                    similar_text($vehiculo["descripcion"], $request->descripcion, $percent);
                    $marca_result[$c] = array(
                        "marca" => $vehiculo["marca"],
                        "id_marca" => $vehiculo["id_marca"],
                        "submarca" => $vehiculo["submarca"],
                        "id_submarca" => $vehiculo["id_submarca"],
                        "descripcion" => $vehiculo["descripcion"],
                        "cveamis" => $vehiculo["cveamis"],
                        "percent" => $percent
                    );
                    $c++;
                }
            }
        }

        //item of $vehiculo with the more similar $request->descripcion
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
        $tipo_persona = ($request->tipo_persona == "F") ? 1 : 2;
        $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header/>
                    <soapenv:Body>
                        <tem:Transaccion>
                            <tem:XML>
                            <transacciones xmlns=""><transaccion version="1" tipotransaccion="C" cotizacion="" negocio="' . $this->negocio . '" tiponegocio=""><vehiculo id="1" amis="' . $vehiculo['cveamis'] . '" modelo="' . $request->modelo . '" descripcion="" uso="1" servicio="1" plan="1" motor="" serie="" repuve="" placas="" conductor="" conductorliciencia="" conductorfecnac="" conductorocupacion="" estado="' . $request->estado . '" poblacion="' . $request->poblacion . '" color="01" dispositivo="" fecdispositivo="" tipocarga="" tipocargadescripcion=""><cobertura id="02" desc="" sa="" tipo="3" ded="5" pma="" /><cobertura id="04" desc="" sa="" tipo="3" ded="10" pma="" /><cobertura id="06" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="07" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="08" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="09" desc="" sa="Auto Sustituto" tipo="" ded="" pma="" /><cobertura id="10" desc="" sa="" tipo="B" ded="" pma="" /><cobertura id="13" desc="" sa="2" tipo="" ded="" pma="" /><cobertura id="18" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="23" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="24" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="25" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="26" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="27" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="28" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="29" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="33" desc="" sa="30000" tipo="" ded="25" pma="" /><cobertura id="34" desc="" sa="2000000" tipo="" ded="" pma="" /> <cobertura id="35" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="38" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="39" desc="" sa="" tipo="" ded="" pma="" /> <cobertura id="40" desc="" sa="" tipo="" ded="50" pma="" /></vehiculo><asegurado id="" nombre="' . $request->nombre . '" paterno="' . $request->apellido_paterno . '" materno="' . $request->apellido_materno . '" calle="" numerointerior="" numeroexterior="" colonia="" poblacion="" estado="' . $request->estado . '" cp="' . $request->codigo_postal . '" pais="" tipopersona="' . $tipo_persona . '"/><poliza id="" tipo="A" endoso="" fecemision="" feciniciovig="' . $request->inicio_vig . '" fecterminovig="' . $request->fin_vig . '" moneda="0" bonificacion="' . $discount . '" formapago="C" agente="' . $this->usuario . '" tarifacuotas="2201" tarifavalores="2201" tarifaderechos="2201" beneficiario="" politicacancelacion="1"/><prima primaneta="" derecho="" recargo="" impuesto="" primatotal="" comision=""/><recibo id="" feciniciovig="" fecterminovig="" primaneta="" derecho="" recargo="" impuesto="" primatotal="" comision="" cadenaoriginal="" sellodigital="" fecemision="" serie="" folio="" horaemision="" numeroaprobacion="" anoaprobacion="" numseriecertificado=""/><error/></transaccion></transacciones>
                            </tem:XML>
                            <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                            <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                            <tem:Clave>' . $this->password . '</tem:Clave>
                        </tem:Transaccion>
                    </soapenv:Body>
                </soapenv:Envelope>';
        try {
            // return $soap;
            $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
            $result = $client1->send(
                $soap,
                'http://tempuri.org/Transaccion'
            );

            // return $client1;


            $response = json_decode(json_encode($result), true);
            $response = $result['TransaccionResult'];
            // return $response;
            // $arrayResp = json_decode(json_encode($response),true);

            //serialize the xml response to array

            //encoding the xml response to utf-8
            $response = mb_convert_encoding($response, 'UTF-8', 'UTF-8');


            //convert the xml response to array
            $arrayResp = json_decode(json_encode(simplexml_load_string($response)), true);
            // return $arrayResp;

            // return $arrayResp;
            $array = array(
                "transaccion" => $arrayResp['transaccion']['@attributes'],
                "vehiculo" => $arrayResp['transaccion']['vehiculo']['@attributes'],
                "coberturas" => array(
                    "cobertura1" => $arrayResp['transaccion']['vehiculo']['cobertura'][0]['@attributes'],
                    "cobertura2" => $arrayResp['transaccion']['vehiculo']['cobertura'][1]['@attributes'],
                    "cobertura3" => $arrayResp['transaccion']['vehiculo']['cobertura'][2]['@attributes'],
                    "cobertura4" => $arrayResp['transaccion']['vehiculo']['cobertura'][3]['@attributes'],
                    "cobertura5" => $arrayResp['transaccion']['vehiculo']['cobertura'][4]['@attributes'],
                    "cobertura6" => $arrayResp['transaccion']['vehiculo']['cobertura'][5]['@attributes'],
                    "cobertura7" => $arrayResp['transaccion']['vehiculo']['cobertura'][6]['@attributes'],
                    "cobertura8" => $arrayResp['transaccion']['vehiculo']['cobertura'][7]['@attributes'],
                    "cobertura9" => $arrayResp['transaccion']['vehiculo']['cobertura'][8]['@attributes'],
                    "cobertura10" => $arrayResp['transaccion']['vehiculo']['cobertura'][9]['@attributes'],
                    "cobertura11" => $arrayResp['transaccion']['vehiculo']['cobertura'][10]['@attributes'],
                    "cobertura12" => $arrayResp['transaccion']['vehiculo']['cobertura'][11]['@attributes'],
                    "cobertura13" => $arrayResp['transaccion']['vehiculo']['cobertura'][12]['@attributes'],
                    "cobertura14" => $arrayResp['transaccion']['vehiculo']['cobertura'][13]['@attributes'],
                    "cobertura15" => $arrayResp['transaccion']['vehiculo']['cobertura'][14]['@attributes'],
                    "cobertura16" => $arrayResp['transaccion']['vehiculo']['cobertura'][15]['@attributes'],
                    "cobertura17" => $arrayResp['transaccion']['vehiculo']['cobertura'][16]['@attributes'],
                    "cobertura18" => $arrayResp['transaccion']['vehiculo']['cobertura'][17]['@attributes'],
                    "cobertura19" => $arrayResp['transaccion']['vehiculo']['cobertura'][18]['@attributes'],
                    "cobertura20" => $arrayResp['transaccion']['vehiculo']['cobertura'][19]['@attributes'],
                    "cobertura21" => $arrayResp['transaccion']['vehiculo']['cobertura'][20]['@attributes'],
                    "cobertura22" => $arrayResp['transaccion']['vehiculo']['cobertura'][21]['@attributes'],
                    "cobertura23" => $arrayResp['transaccion']['vehiculo']['cobertura'][22]['@attributes'],
                    "cobertura24" => $arrayResp['transaccion']['vehiculo']['cobertura'][23]['@attributes'],
                    "cobertura25" => $arrayResp['transaccion']['vehiculo']['cobertura'][24]['@attributes'],
                ),
                "asegurado" => $arrayResp['transaccion']['asegurado']['@attributes'],
                "poliza" => $arrayResp['transaccion']['poliza']['@attributes'],
                "prima" => $arrayResp['transaccion']['prima']['@attributes'],
                "recibo" => $arrayResp['transaccion']['recibo']['@attributes']
            );


            return response()->json([
                'status' => 'success',
                'data' => $array,
                'desuento' => $discount,
            ], 200);
            //cast $response xml to array json
            $cotizacion = $this->convertXMLtoArrayCotizacion(simplexml_load_string($response));

            //iterate $response by <vehiculo> and return array
            // $cotizacion = $this->convertXMLtoArrayCat(simplexml_load_string($response)->vehiculo);
            // dd($vehiculos);

            //get $response with attributes of $response and convert to json
            // $cat = json_decode(json_encode(simplexml_load_string($response)), true);
            // dd($response, $cat);
            if ($vehiculos) {
                return response()->json($vehiculos, 200);
            } else {
                return response()->json(['error' => "Submarcas no encontradas"], 404);
            }
        } catch (Exception $th) {
            //throw $th;
            return response()->json(['error' => $th->getMessage()], 500);
        } catch (SoapFault $fault) {
            return response()->json(['error' => $fault->getMessage()], 500);
        } catch (\Error $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // create a emition function
    public function setEmition(Request $request)
    {
        $discount = 0;
        try {
            $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 404);
        }
        $descuento = MantenimientoModel::where('provider', "ana")->first();
        $discount = $descuento->descuento;
        if ($discount == 1) {
            if ($cliente->tipo_cliente == 'Público') {
                // get ana_descuento de admin 1
                $admin = AdminModel::where('admin_id', 1)->first();
                $discount = $admin->ana_descuento;
            } else {
                // get ana_descuento del cliente
                $discount = $cliente->ana_descuento;
            }
        } else {
            $discount = 0;
        }
        $vehiculos = $this->getAMIS($request->modelo);
        $vehiculos = json_decode($vehiculos->getContent(), true);
        // $search is equals to upercase of $smarca;
        $marca_result = array();
        $c = 0;

        //search in $vehiculos array by marca and submarca and save in $marca_result
        foreach ($vehiculos as $vehiculo) {
            if (strpos($vehiculo["marca"], strtoupper($request->marca)) !== false) {
                if (strpos($vehiculo["submarca"], strtoupper($request->submarca)) !== false) {

                    //compare $vehiculo["descripcion"] with $request->descripcion and save the more similar in $marca_result
                    similar_text($vehiculo["descripcion"], $request->descripcion, $percent);
                    $marca_result[$c] = array(
                        "marca" => $vehiculo["marca"],
                        "id_marca" => $vehiculo["id_marca"],
                        "submarca" => $vehiculo["submarca"],
                        "id_submarca" => $vehiculo["id_submarca"],
                        "descripcion" => $vehiculo["descripcion"],
                        "cveamis" => $vehiculo["cveamis"],
                        "percent" => $percent
                    );
                    $c++;
                }
            }
        }

        //item of $vehiculo with the more similar $request->descripcion
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
        $tipo_persona = ($request->tipo_persona == "F") ? 1 : 2;

        $soap = "";

        if ($request->tipo_persona == "F") {
            $genero_asegurado = ($request->genero_asegurado == "H") ? 1 : 2;
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
            <soapenv:Header/>
            <soapenv:Body>
                <tem:Transaccion>
                    <tem:XML>
                     <transacciones xmlns=""><transaccion version="1" tipotransaccion="E" cotizacion="' . $request->cotizacionNum . '" negocio="' . $this->negocio . '" tiponegocio=""><vehiculo id="1" amis="' . $vehiculo['cveamis'] . '" modelo="' . $request->modelo . '" descripcion="" uso="1" servicio="1" plan="1" motor="' . $request->motor . '" serie="' . $request->serie . '" repuve="" placas="' . $request->placas . '" conductor="' . $request->nombre_completo_conductor . '" conductorliciencia="' . $request->licencia_conductor . '" conductorfecnac="' . $request->fechanac_conductor . '" conductorocupacion="' . $request->ocupacion_conductor . '" estado="' . $request->estado_conductor . '" poblacion="' . $request->poblacion_conductor . '" color="' . $request->clave_color . '" dispositivo="' . $request->dispositivo . '" fecdispositivo="' . $request->fecha_dispositivo . '" tipocarga="' . $request->tipo_carga . '" tipocargadescripcion="' . $request->carga_descripcion . '"><cobertura id="02" desc="" sa="" tipo="3" ded="5" pma="" /><cobertura id="04" desc="" sa="" tipo="3" ded="10" pma="" /><cobertura id="06" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="07" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="08" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="09" desc="" sa="Auto Sustituto" tipo="" ded="" pma="" /><cobertura id="10" desc="" sa="" tipo="B" ded="" pma="" /><cobertura id="13" desc="" sa="2" tipo="" ded="" pma="" /><cobertura id="18" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="23" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="24" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="25" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="26" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="27" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="28" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="29" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="33" desc="" sa="30000" tipo="" ded="25" pma="" /><cobertura id="34" desc="" sa="2000000" tipo="" ded="" pma="" /> <cobertura id="35" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="38" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="39" desc="" sa="" tipo="" ded="" pma="" /> <cobertura id="40" desc="" sa="" tipo="" ded="50" pma="" /></vehiculo><asegurado id="" nombre="' . $request->nombre . '" paterno="' . $request->apellido_paterno . '" materno="' . $request->apellido_materno . '" calle="' . $request->calle . '" numerointerior="" numeroexterior="" colonia="' . $request->colonia . '" poblacion="' . $request->poblacion . '" estado="' . $request->estado . '" cp="' . $request->codigo_postal . '" pais="' . $request->pais . '" tipopersona="' . $tipo_persona . '"><argumento id="2" tipo="" campo="" valor="' . $request->correo_asegurado . '"/><argumento id="3" tipo="" campo="" valor="' . $request->telefono_asegurado . '"/><argumento id="4" tipo="" campo="" valor="' . $request->rfc_asegurado . '"/><argumento id="5" tipo="" campo="" valor="' . $request->curp_asegurado . '"/><argumento id="6" tipo="" campo="" valor="' . $request->nacionalidad_asegurado . '"/><argumento id="7" tipo="" campo="" valor="' . $request->tipo_identificacion_asegurado . '"/><argumento id="8" tipo="" campo="" valor="' . $request->numero_identificacion_asegurado . '"/><argumento id="9" tipo="" campo="" valor="' . $request->ocupacion_asegurado . '"/><argumento id="17" tipo="" campo="" valor="' . $genero_asegurado . '"/><argumento id="18" tipo="" campo="" valor="' . $request->certificado_fiel_asegurado . '"/><argumento id="19" tipo="" campo="" valor="' . $request->fecha_nacimiento_asegurado . '"/><argumento id="20" tipo="" campo="" valor="' . $request->pais_nacimiento_asegurado . '"/></asegurado><poliza id="" tipo="A" endoso="" fecemision="" feciniciovig="' . $request->fecha . '" fecterminovig="' . $request->fecha_fin . '" moneda="0" bonificacion="' . $discount . '" formapago="C" agente="' . $this->usuario . '" tarifacuotas="2201" tarifavalores="2201" tarifaderechos="2201" beneficiario="" politicacancelacion="1"/><prima primaneta="" derecho="" recargo="" impuesto="" primatotal="" comision=""/><recibo id="" feciniciovig="" fecterminovig="" primaneta="" derecho="" recargo="" impuesto="" primatotal="" comision="" cadenaoriginal="" sellodigital="" fecemision="" serie="" folio="" horaemision="" numeroaprobacion="" anoaprobacion="" numseriecertificado=""/><tarjetacredito cliente="' . $request->tarjeta_cliente . '" numero="' . $request->tarjeta_numero_cliente . '" vencimiento="' . $request->vencimiento_mmdd . '" codigoseguridad="' . $request->cvc . '"/><domiciliacion banco="' . $request->cod_banco . '" direcciontarjetahabiente="' . $request->direccion_tarjeta_habiente . '" envio="N" rfc="' . $request->rfc_opcional . '" fiscal="' . $request->fiscal_ns . '"/><error/></transaccion></transacciones>
                    </tem:XML>
                    <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                    <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                    <tem:Clave>' . $this->password . '</tem:Clave>
                </tem:Transaccion>
            </soapenv:Body>
        </soapenv:Envelope>';
            // return $soap;
        } else {

            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
            <soapenv:Header/>
            <soapenv:Body>
                <tem:Transaccion>
                    <tem:XML>
                    <transacciones xmlns=""><transaccion version="1" tipotransaccion="E" cotizacion="' . $request->cotizacionNum . '" negocio="' . $this->negocio . '" tiponegocio=""><vehiculo id="1" amis="' . $vehiculo['cveamis'] . '" modelo="' . $request->modelo . '" descripcion="" uso="1" servicio="1" plan="1" motor="' . $request->motor . '" serie="' . $request->serie . '" repuve="" placas="' . $request->placas . '" conductor="' . $request->nombre_completo_conductor . '" conductorliciencia="' . $request->licencia_conductor . '" conductorfecnac="' . $request->fechanac_conductor . '" conductorocupacion="' . $request->ocupacion_conductor . '" estado="' . $request->estado_conductor . '" poblacion="' . $request->poblacion_conductor . '" color="' . $request->clave_color . '" dispositivo="' . $request->dispositivo . '" fecdispositivo="' . $request->fecha_dispositivo . '" tipocarga="' . $request->tipo_carga . '" tipocargadescripcion="' . $request->carga_descripcion . '"><cobertura id="02" desc="" sa="" tipo="3" ded="5" pma="" /><cobertura id="04" desc="" sa="" tipo="3" ded="10" pma="" /><cobertura id="06" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="07" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="08" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="09" desc="" sa="Auto Sustituto" tipo="" ded="" pma="" /><cobertura id="10" desc="" sa="" tipo="B" ded="" pma="" /><cobertura id="13" desc="" sa="2" tipo="" ded="" pma="" /><cobertura id="18" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="23" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="24" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="25" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="26" desc="" sa="500000" tipo="" ded="" pma="" /><cobertura id="27" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="28" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="29" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="33" desc="" sa="30000" tipo="" ded="25" pma="" /><cobertura id="34" desc="" sa="2000000" tipo="" ded="" pma="" /> <cobertura id="35" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="38" desc="" sa="" tipo="" ded="" pma="" /><cobertura id="39" desc="" sa="" tipo="" ded="" pma="" /> <cobertura id="40" desc="" sa="" tipo="" ded="50" pma="" /></vehiculo><asegurado id="" nombre="' . $request->nombre . '" paterno="' . $request->apellido_paterno . '" materno="' . $request->apellido_materno . '" calle="" numerointerior="" numeroexterior="" colonia="" poblacion="" estado="' . $request->estado . '" cp="' . $request->codigo_postal . '" pais="" tipopersona="' . $tipo_persona . '"><argumento id="2" tipo="" campo="" valor="' . $request->correo_asegurado . '"/><argumento id="3" tipo="" campo="" valor="' . $request->telefono_asegurado . '"/><argumento id="4" tipo="" campo="" valor="' . $request->rfc_asegurado . '"/><argumento id="10" tipo="" campo="" valor="' . $request->giro_asegurado . '"/><argumento id="11" tipo="" campo="" valor="' . $request->administrador . '"/><argumento id="12" tipo="" campo="" valor="' . $request->nacionalidad_admon . '"/><argumento id="13" tipo="" campo="" valor="' . $request->representante . '"/><argumento id="14" tipo="" campo="" valor="' . $request->nacionalidad . '"/></asegurado><poliza id="" tipo="A" endoso="" fecemision="" feciniciovig="' . $request->fecha . '" fecterminovig="' . $request->fecha_fin . '" moneda="0" bonificacion="0" formapago="C" agente="' . $this->usuario . '" tarifacuotas="2201" tarifavalores="2201" tarifaderechos="2201" beneficiario="" politicacancelacion="1"/><prima primaneta="" derecho="" recargo="" impuesto="" primatotal="" comision=""/><recibo id="" feciniciovig="" fecterminovig="" primaneta="" derecho="" recargo="" impuesto="" primatotal="" comision="" cadenaoriginal="" sellodigital="" fecemision="" serie="" folio="" horaemision="" numeroaprobacion="" anoaprobacion="" numseriecertificado=""/><tarjetacredito cliente="' . $request->tarjeta_cliente . '" numero="' . $request->tarjeta_numero_cliente . '" vencimiento="' . $request->vencimiento_mmdd . '" codigoseguridad="' . $request->cvc . '"/><domiciliacion banco="' . $request->cod_banco . '" direcciontarjetahabiente="' . $request->direccion_tarjeta_habiente . '" envio="N" rfc="' . $request->rfc_opcional . '" fiscal="' . $request->fiscal_ns . '"/><error/></transaccion></transacciones>
                    </tem:XML>
                    <tem:Negocio>' . $this->negocio . '</tem:Negocio>
                    <tem:Usuario>' . $this->usuario . '</tem:Usuario>
                    <tem:Clave>' . $this->password . '</tem:Clave>
                </tem:Transaccion>
            </soapenv:Body>
        </soapenv:Envelope>';
            // return $soap;
        }







        try {
            $client = $this->client2;
            $result = $client1->send(
                $soap,
                'http://tempuri.org/Transaccion'
            );
            // return $client;


            $response = json_decode(json_encode($result), true);
            $response = $result['TransaccionResult'];
            // return the response as an array
            // return $response;
            // $arrayResp = json_decode(json_encode($response),true);

            //serialize the xml response to array

            //encoding the xml response to utf-8
            $response = mb_convert_encoding($response, 'UTF-8', 'UTF-8');


            //convert the xml response to array
            $arrayResp = json_decode(json_encode(simplexml_load_string($response)), true);

            // return $arrayResp;
            $array = array(
                "transaccion" => $arrayResp['transaccion']['@attributes'],
                "vehiculo" => $arrayResp['transaccion']['vehiculo']['@attributes'],
                "coberturas" => array(
                    "cobertura1" => $arrayResp['transaccion']['vehiculo']['cobertura'][0]['@attributes'],
                    "cobertura2" => $arrayResp['transaccion']['vehiculo']['cobertura'][1]['@attributes'],
                    "cobertura3" => $arrayResp['transaccion']['vehiculo']['cobertura'][2]['@attributes'],
                    "cobertura4" => $arrayResp['transaccion']['vehiculo']['cobertura'][3]['@attributes'],
                    "cobertura5" => $arrayResp['transaccion']['vehiculo']['cobertura'][4]['@attributes'],
                    "cobertura6" => $arrayResp['transaccion']['vehiculo']['cobertura'][5]['@attributes'],
                    "cobertura7" => $arrayResp['transaccion']['vehiculo']['cobertura'][6]['@attributes'],
                    "cobertura8" => $arrayResp['transaccion']['vehiculo']['cobertura'][7]['@attributes'],
                    "cobertura9" => $arrayResp['transaccion']['vehiculo']['cobertura'][8]['@attributes'],
                    "cobertura10" => $arrayResp['transaccion']['vehiculo']['cobertura'][9]['@attributes'],
                    "cobertura11" => $arrayResp['transaccion']['vehiculo']['cobertura'][10]['@attributes'],
                    "cobertura12" => $arrayResp['transaccion']['vehiculo']['cobertura'][11]['@attributes'],
                    "cobertura13" => $arrayResp['transaccion']['vehiculo']['cobertura'][12]['@attributes'],
                    "cobertura14" => $arrayResp['transaccion']['vehiculo']['cobertura'][13]['@attributes'],
                    "cobertura15" => $arrayResp['transaccion']['vehiculo']['cobertura'][14]['@attributes'],
                    "cobertura16" => $arrayResp['transaccion']['vehiculo']['cobertura'][15]['@attributes'],
                    "cobertura17" => $arrayResp['transaccion']['vehiculo']['cobertura'][16]['@attributes'],
                    "cobertura18" => $arrayResp['transaccion']['vehiculo']['cobertura'][17]['@attributes'],
                    "cobertura19" => $arrayResp['transaccion']['vehiculo']['cobertura'][18]['@attributes'],
                    "cobertura20" => $arrayResp['transaccion']['vehiculo']['cobertura'][19]['@attributes'],
                    "cobertura21" => $arrayResp['transaccion']['vehiculo']['cobertura'][20]['@attributes'],
                    "cobertura22" => $arrayResp['transaccion']['vehiculo']['cobertura'][21]['@attributes'],
                    "cobertura23" => $arrayResp['transaccion']['vehiculo']['cobertura'][22]['@attributes'],
                    "cobertura24" => $arrayResp['transaccion']['vehiculo']['cobertura'][23]['@attributes'],
                    "cobertura25" => $arrayResp['transaccion']['vehiculo']['cobertura'][24]['@attributes'],
                ),
                "asegurado" => $arrayResp['transaccion']['asegurado']['@attributes'],
                "poliza" => $arrayResp['transaccion']['poliza']['@attributes'],
                "prima" => $arrayResp['transaccion']['prima']['@attributes'],
                "recibo" => $arrayResp['transaccion']['recibo']['@attributes']
            );


            return response()->json($array);
            //cast $response xml to array json
            $cotizacion = $this->convertXMLtoArrayCotizacion(simplexml_load_string($response));
            return $cotizacion;
            //iterate $response by <vehiculo> and return array
            // $cotizacion = $this->convertXMLtoArrayCat(simplexml_load_string($response)->vehiculo);
            // dd($vehiculos);

            //get $response with attributes of $response and convert to json
            // $cat = json_decode(json_encode(simplexml_load_string($response)), true);
            // dd($response, $cat);
            if ($vehiculos) {
                return response()->json($vehiculos, 200);
            } else {
                return response()->json(['error' => "Submarcas no encontradas"], 404);
            }
        } catch (Exception $th) {
            //throw $th;
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //function to emition a quote in Ana Seguros


    //function to cast a xml with attributes to array
    public function xml2array($xml)
    {
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);
        return $array;
    }

    //setCotizacion
    protected function convertXMLtoArray($xmlelements)
    {
        $array = [];
        foreach ($xmlelements as $value) {
            $object = json_decode(json_encode($value), true);
            array_push($array, ['id' => $object['@attributes']['id'], 'marca' => $object[0]]);
        }
        return $array;
    }

    protected function convertXMLtoArrayCat($xmlelements)
    {
        $array = [];
        foreach ($xmlelements as $value) {
            $object = json_decode(json_encode($value), true);
            array_push($array, [
                'marca' => $object['@attributes']['armadora'],
                'submarca' => $object['@attributes']['submarca'],
                'id_marca' => $object['@attributes']['cvearmadora'],
                'id_submarca' => $object['@attributes']['cvesubmarca'],
                'cveamis' => $object['@attributes']['cveamis'],
                'descripcion' => $object[0]
            ]);
        }
        return $array;
    }

    protected function convertXMLtoArrayCol($xmlelements)
    {
        $array = [];
        foreach ($xmlelements as $value) {
            $object = json_decode(json_encode($value), true);
            array_push($array, [
                'id_estado' => $object['@attributes']['IdEstado'],
                'estado' => $object['@attributes']['Estado'],
                'id_municipio' => $object['@attributes']['IdDelMun'],
                'municipio' => $object['@attributes']['DelMun'],
                'colonia' => $object[0]
            ]);
        }
        return $array;
    }

    protected function convertXMLtoArrayCotizacion($xmlelements)
    {
        try {
            $array = [];
            foreach ($xmlelements as $value) {
                $object = json_decode(json_encode($value), true);
                array_push($array, [
                    'marca' => $object['@attributes']['marca'],
                    'submarca' => $object['@attributes']['submarca'],
                    'id_marca' => $object['@attributes']['cvearmadora'],
                    'id_submarca' => $object['@attributes']['cvesubmarca'],
                    'cveamis' => $object['@attributes']['cveamis'],
                    'descripcion' => $object[0]
                ]);
            }
            return $array;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}

    // public function getModelos()
    // {
    //     $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
    //                 <soapenv:Header/>
    //                 <soapenv:Body>
    //                     <tem:Modelo>
    //                         <tem:Negocio>' . $this->negocio . '</tem:Negocio>
    //                         <!--Optional:-->
    //                         <tem:Usuario>' . $this->usuario . '</tem:Usuario>
    //                         <!--Optional:-->
    //                         <tem:Clave>' . $this->password . '</tem:Clave>
    //                     </tem:Modelo>
    //                 </soapenv:Body>
    //             </soapenv:Envelope>';
    //     try {
    //         $client1 = new nusoap_client($this->url, false, false, false, false, false, 0, 96000);
    //         $result = $client1->send(
    //             $soap,
    //             'http://tempuri.org/Modelo'
    //         );
    //         // return $result;
    //         $response = json_decode(json_encode($result), true);
    //         $response = $response['ModeloResult'];

    //         // cast xml string into $response to array json
    //         $response = json_decode(json_encode(simplexml_load_string($response)), true);
    //         return response()->json([
    //             "status" => "success",
    //             "ModeloResult" => $response,
    //         ]);

    //         // dd($result);
    //     } catch (SoapFault $th) {
    //         //throw $th;
    //         dd($th);
    //     }
    // }

    // public function setCotizacion(Request $request)
    // {
    //     $marcas = $this->getMarcas(2022);
    //     //cast $marcas to array
    //     $marcas = json_decode($marcas->getContent(), true);
    //     // $search is equals to upercase of $smarca;
    //     $search = strtoupper($request->marca);
    //     $result = array();
    //     $c = 0;
    //     //search in $marcas array by item like "descripcion" and show coincidences
    //     foreach ($marcas["MarcaResult"] as $item) {
    //            if (strpos($item["descripcion"], $search) !== false) {
    //             array_push($result, $item);
    //             $c++;
    //         }
    //     }
    //     return response()->json(['marcas'=>$result],200);
    // }

    // public function getMarca($modelo)
    // {
    //     try {
    //         $client = new SoapClient($this->url);
    //         $marcasXML = $client->Marca(["Modelo" => $modelo, "Categoria" => 100, "Negocio" => $this->negocio, "Usuario" => $this->usuario, "Clave" => $this->password]);
    //         $marcas = $this->convertXMLtoArray(simplexml_load_string($marcasXML->MarcaResult)->marca);
    //         if ($marcas) {
    //             return response()->json(['marcas' => $marcas], 200);
    //         } else {
    //             return response()->json(['error' => "Marcas no encontradas"], 404);
    //         }
    //     } catch (SoapFault $fault) {
    //         dd($fault);
    //     }
    // }
