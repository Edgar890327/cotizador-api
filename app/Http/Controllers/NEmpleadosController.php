<?php

namespace App\Http\Controllers;

use App\Models\NEmpleadosModel;
use App\Models\NTareasModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class NEmpleadosController extends Controller
{
    //create a static array to store the data
    public static $data = [
        "Sucursal Soriana Cd. Hgo.",
        "Sucursal Tuxpan",
        "Sucursal Maravatio",
        "Sucursal Zitácuaro",
    ];

    //create a function to get all the data
    public function getSucursales(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            return response()->json(self::$data, 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //create a function to store a new empleado from NEmpleadosModel
    public function store(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //create an empleado
                    $empleado = NEmpleadosModel::create([
                        'admin_id' => $request->input('admin_id'),
                        'nombre' => $request->input('nombre'),
                        'apellido_paterno' => $request->input('apellido_paterno'),
                        'apellido_materno' => $request->input('apellido_materno'),
                        'direccion' => $request->input('direccion'),
                        'telefono' => $request->input('telefono'),
                        'email' => $request->input('email'),
                        'password' => Hash::make($request->input('password')),
                        'rfc' => $request->input('rfc'),
                        'genero' => $request->input('genero'),
                        'sucursal' => $request->input('sucursal'),
                    ]);
                    return response()->json($empleado, 200);
                } catch (Exception $e) {
                    return response()->json(['error' => $e], 401);
                }
            } else {
                //return response
                return response()->json(['error' => 'Unauthorized type of request'], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //function to get all empleados from NEmpleadosModel if token is valid
    public function getEmpleados(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            $empleados = NEmpleadosModel::all();

            //check if $empleados has a tareas with "estado" = "Asignada"
            foreach ($empleados as $empleado) {

                $empleado->tareas = NTareasModel::where('empleado_id', $empleado->empleado_id)->get();
                $empleado->tareas_pendientes = NTareasModel::where('empleado_id', $empleado->empleado_id)->where('estado', 'Asignada')->get();

                //check if $empleado has a tareas with "estado" = "Asignada"
                if ($empleado->tareas_pendientes->count() > 0) {
                    $empleado->tareas_estado = "Tareas pendientes";
                } else {
                    $empleado->tareas_estado = "Sin tareas";   
                }
            }

                //check if $empleado has a tareas with "estado" = "Asignada"

                //check if $empleado has a tareas with "estado" = "Asignada"

            return response()->json([
                "status" => "success",
                "count" => $empleados->count(),
                "body" => $empleados
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //function to update an empleado from NEmpleadosModel if token is valid
    public function update(Request $request, $empleado_id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {
                try {

                    //update an empleado
                    $empleado = NEmpleadosModel::find($empleado_id);

                    //check if $request has password then update password as Hash::make($request->input('password'))
                    if ($request->input('password') != null) {
                        $empleado->password = Hash::make($request->input('password'));
                    }

                    $empleado->update(
                        $request->all()
                    );
    
                    return response()->json([
                        "status" => "success",
                        "body" => $empleado
                    ], 200);
                } catch (Exception $e) {
                    return response()->json(['error' => $e], 401);
                }
            } else {
                //return response
                return response()->json(['error' => 'Unauthorized type of request'], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }
}
