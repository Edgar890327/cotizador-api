<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    //
    public function cotizar(Request $request)
    {
        
        if ($request->header('key') == env('TOKEN')) {
            try {
                // call getCotizacion method in GSController with the request as parameter 
                $cotizacion = new GSController();
                $cotizacion = $cotizacion->getCotizacion($request);
                // return the response
                return response()->json([
                    "status" => "success",
                    "providers" => [
                        "gs" => $cotizacion
                    ]
                ]);
            } catch (\Throwable $e) {
                return response()->json(['error' => $e], 401);
            }
        }
    }
}
