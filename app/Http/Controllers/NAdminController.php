<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\AdminModel;
use App\Models\MantenimientoModel;
use App\Models\NEmpleadosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class NAdminController extends Controller
{
    public function store(Request $request)
    {
        //check if request is a json
        if ($request->header('key') == env('TOKEN')) {
            if ($request->isJson()) {

                //create an admin
                $admin = AdminModel::create([
                    'nombre' => $request->input('name'),
                    'apellido_paterno' => $request->input('last_name'),
                    'email' => $request->input('email'),
                    'telefono' => $request->input('phone'),
                    'password' => Hash::make($request->input('password')),
                    'token' => Str::random(60),
                    'gs_descuento' => $request->input('gs_descuento'),
                    'qualitas_descuento' => $request->input('qualitas_descuento'),
                    'chubb_descuento' => $request->input('chubb_descuento'),
                    'mapfre_descuento' => $request->input('mapfre_descuento'),
                    'banorte_descuento' => $request->input('banorte_descuento'),
                    'ana_descuento' => $request->input('ana_descuento'),
                ]);
                return response()->json($admin, 200);
            } else {
                //return response
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function login(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            if ($request->isJson()) {
                try {
                    $data = $request->json()->all();
                    $admin = AdminModel::where('email', $data['email'])->first();
                    $empleado = NEmpleadosModel::where('email', $data['email'])->first();
                    if ($admin) {

                        if ($admin && Hash::check($data['password'], $admin->password)) {
                            return response()->json([
                                "status" => "success",
                                "user" => "admin",
                                "body" => $admin
                            ], 200);
                        } else {
                            return response()->json([
                                "status" => "error",
                                "user" => "no encontrado",
                                "body" => null
                            ], 401);
                        }
                    } else {
                        if ($empleado && Hash::check($data['password'], $empleado->password)) {
                            return response()->json([
                                "status" => "success",
                                "user" => "empleado",
                                "body" => $empleado
                            ], 200);
                        } else {
                            return response()->json([
                                "status" => "error",
                                "user" => "no encontrado",
                            ], 401);
                        }
                    }
                } catch (Throwable $e) {
                    return response()->json(['error' => $e], 401);
                }
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //update admins from NAdminModel if token is valid
    public function update(Request $request, $id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //update an admin
                    $admin = AdminModel::find($id);

                    $admin->update($request->all());
                    //check if $request->input('password') is not null
                    if ($request->input('password') != null) {
                        $admin->password = Hash::make($request->input('password'));
                        $admin->token = Str::random(60);

                        $admin->save();
                    }

                    //if the admin is updated return a json with the admin
                    return response()->json([
                        'status' => 'success',
                        'body' => $admin
                    ]);
                } catch (\Exception $e) {
                    //if the admin is not updated return a json with the error
                    return response()->json([
                        'status' => 'error',
                        'body' => $e->getMessage()
                    ]);
                }
            } else {
                //return response
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } else {

            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //get descuentos from NAdminModel if token is valid
    public function getDescuentos(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {
                try {
                    //find admin by admin_id = 1
                    $admin = AdminModel::find($request->admin_id);
                    //return a json with the admins
                    return response()->json([
                        'status' => 'success',
                        'body' => [
                            'gs_descuento' => $admin->gs_descuento,
                            'qualitas_descuento' => $admin->qualitas_descuento,
                            'chubb_descuento' => $admin->chubb_descuento,
                            'mapfre_descuento' => $admin->mapfre_descuento,
                            'banorte_descuento' => $admin->banorte_descuento,
                            'ana_descuento' => $admin->ana_descuento,
                        ]
                    ]);
                } catch (\Exception $e) {
                    //return a json with the error
                    return response()->json([
                        'status' => 'error',
                        'body' => $e->getMessage()
                    ]);
                }
            } else {
                //return response
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // get status of mantenimiento
    public function getMantenimiento($provider)
    {
        $mantenimiento = MantenimientoModel::where('provider',$provider)->first();
        if($mantenimiento){
            return response()->json([
                "status" => "ok",
                "content" => $mantenimiento
            ], 200);
        }else{
            return response()->json([
                "status" => "error",
                "message" => "mantenimiento not found"
            ], 404);
        }
    }

    // update mantenimiento status
    public function updateMantenimiento(Request $request,$provider)
    {
        $mantenimiento = MantenimientoModel::where('provider', $provider)->first();
        $mantenimiento->update($request->all());
        return response()->json([
            "status" => "ok",
            "content" => $mantenimiento
        ], 200);
    }
}
