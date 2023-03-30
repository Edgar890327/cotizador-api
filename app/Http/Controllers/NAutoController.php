<?php

namespace App\Http\Controllers;

use App\Models\NAutoModel;
use Illuminate\Http\Request;

class NAutoController extends Controller
{
    //store a auto from NAutoModel if token is valid
    public function store(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //create a auto
                    $auto = NAutoModel::create([
                        'cliente_id' => $request->input('cliente_id'),
                        'nombre' => $request->input('nombre'),
                        'apellido_paterno' => $request->input('apellido_paterno'),
                        'apellido_materno' => $request->input('apellido_materno'),
                        'telefono' => $request->input('telefono'),
                        'email' => $request->input('email'),
                        'rfc' => $request->input('rfc'),
                        'genero' => $request->input('genero'),
                        'curp' => $request->input('curp'),
                        'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                        'edo_civil' => $request->input('edo_civil'),
                        'fis_mor' => $request->input('fis_mor'),
                        'razon_social' => $request->input('razon_social'),
                        'nombre_comercial' => $request->input('nombre_comercial'),
                        'codigo_postal' => $request->input('codigo_postal'),

                        'estado' => $request->input('estado'),
                        'municipio' => $request->input('municipio'),
                        'colonia' => $request->input('colonia'),
                        'clave_estado' => $request->input('clave_estado'),
                        'clave_municipio' => $request->input('clave_municipio'),
                        'numero' => $request->input('numero'),
                        'marca' => $request->input('marca'),
                        'submarca' => $request->input('submarca'),
                        'descripcion' => $request->input('descripcion'),
                        'modelo' => $request->input('modelo'),
                        'placa' => $request->input('placa'),
                        'motor' => $request->input('motor'),
                        'serie' => $request->input('serie'),

                        'proveedor_id' => $request->input('proveedor_id'),
                        'prima' => $request->input('prima'),
                        'pago' => $request->input('pago'),
                        'link_pago' => $request->input('link_pago'),
                        'link_polisa' => $request->input('link_polisa'),
                        'fecha_vencimiento' => $request->input('fecha_vencimiento'),
                    ]);

                    //return a json with the auto
                    return response()->json([
                        'status' => 'success',
                        'body' => $auto
                    ]);
                } catch (\Exception $e) {
                    //return a json with the error
                    return response()->json([
                        'status' => 'error',
                        'body' => $e->getMessage()
                    ]);
                }
            } else {
                //return a json with the error
                return response()->json([
                    'status' => 'error',
                    'body' => 'Request not a json'
                ]);
            }
        } else {
            //return a json with the error
            return response()->json([
                'status' => 'error',
                'body' => 'Token not valid'
            ]);
        }
    }

    //update autos from NAutoModel if token is valid
    public function update(Request $request, $id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //update a auto
                    $auto = NAutoModel::find($id);
                    $auto->update($request->all());

                    //if the auto is updated return a json with the auto
                    return response()->json([
                        'status' => 'success',
                        'body' => $auto
                    ]);
                } catch (\Exception $e) {
                    //return a json with the error
                    return response()->json([
                        'status' => 'error',
                        'body' => $e->getMessage()
                    ]);
                }
            } else {
                //return a json with the error
                return response()->json([
                    'status' => 'error',
                    'body' => 'Request not a json'
                ]);
            }
        } else {
            //return a json with the error
            return response()->json([
                'status' => 'error',
                'body' => 'Token not valid'
            ]);
        }
    }


    //get autos by client from NAutoModel if token is valid and paginate
    public function getAutosByClient(Request $request, $cliente_id)
    {

        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //get autos by client
                    $autos = NAutoModel::where('cliente_id', $cliente_id)->paginate($request->per_page);

                    //if the autos are found return a json with the autos
                    return response()->json([
                        'status' => 'success',
                        'body' => $autos
                    ]);
                } catch (\Exception $e) {
                    //return a json with the error
                    return response()->json([
                        'status' => 'error',
                        'body' => $e->getMessage()
                    ]);
                }
            } else {
                //return a json with the error
                return response()->json([
                    'status' => 'error',
                    'body' => 'Request not a json'
                ]);
            }
        }
    }
}
