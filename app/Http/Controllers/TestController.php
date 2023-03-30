<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\AdminModel;
use App\Models\CotizacionHistorialModel;
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

class TestController extends Controller
{
    public function __construct()
    {

        $this->opts = array(
            'https' => array('header' => array('Content-Type:soap+xml; charset=utf-8'))
        );
        // $this->params = array('encoding' => 'UTF-8', 'trace' => true, 'keep_alive' => false, 'soap_version' => SOAP_1_1, 'stream_context' => stream_context_create($this->opts), 'Content-Length' => '0', 'SoapAction' => 'http://www.mapfre.com/ws/wsdl/MapfreWS/MapfreWS/getCotizacion');
        // $this->url = "https://negociosuat.mapfre.com.mx/mapfrews/catalogosautos/catalogosautos.asmx";
        $this->url = "http://gswas.com.mx/gsautos-ws/soap/autenticacionWS?wsdl";
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

    public function getTokenTest(Request $request)
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
        // return $soap;
        try {
            $client = new nusoap_client($this->url, true);
            //use HTTP/1.1
            $client->useHTTPPersistentConnection();
            $result = $client->send(
                $soap,
                'http://com.gs.gsautos.ws.autenticacion/AutenticacionWS/obtenerTokenRequest'
            );
            return $client;
            //return result and client
            $response = json_decode(json_encode($result), true);
            return response()->json([
                "status" => "success",
                "data" => $response,
            ]);

            // dd($result);
        } catch (SoapFault $th) {
            return response()->json([
                "status" => "error",
                "data" => $th->getMessage(),
            ]);
        } catch (Exception $th) {
            return response()->json([
                "status" => "error",
                "data" => $th->getMessage(),
            ]);
        } catch (Handler $th) {
            return response()->json([
                "status" => "error",
                "data" => $th,
            ]);
        } catch (SoapClient $th) {
            return response()->json([
                "status" => "error",
                "data" => $th->getMessage(),
            ]);
        }
    }
}

// https://negociosuat.mapfre.com.mx/VIPII/wImpresion/MarcoImpresions.aspx?Poliza=4012200003041&amp;Endoso=0&amp;Token=4aab85c8-20cb-470c-937a-5c53b5ed7757

// https://negociosuat.mapfre.com.mx/vip/emision/PolizaAlfaS.aspx?poli=4012200003041&amp;strEndoso=0&amp;NMI=1&amp;token=4aab85c8-20cb-470c-937a-5c53b5ed7757
