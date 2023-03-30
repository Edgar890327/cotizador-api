<?php

namespace App\Http\Controllers;

use App\Models\NCursosModel;
use Exception;
use Illuminate\Http\Request;

class NCursosController extends Controller
{

    public static $data = [
        "Ventas",
        "Comunicación",
        "Atención al Cliente",
        "Autocotizador Villagómez",
        "Motivación y Efectividad",
        "Gestión de Personal",
        "Gestión de Proyectos",
        "Gestión de Recursos Humanos",
    ];

    //create a function to get all the data
    public function getCategorias(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            return response()->json(self::$data, 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //store curso from CursoModel if token is valid
    public function store(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //create a curso
                    $curso = NCursosModel::create([
                        'nombre' => $request->input('nombre'),
                        'descripcion' => $request->input('descripcion'),
                        'objetivo' => $request->input('objetivo'),
                        'categoria' => $request->input('categoria'),
                    ]);
                    return response()->json([
                        "status" => "success",
                        "body" => $curso
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

    //get all the cursos from NCursosModel if token is valid and paginate
    public function searchCursos(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //search like 'nombre' in the NCursosModel ordered by name and paginate
            $cursos = NCursosModel::where('nombre', 'like', '%' . $request->search . '%')->get();
           

            //return response
            return response()->json([
                'status' => 'success',
                'count' => count($cursos),
                'body' => $cursos,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //update a curso from NCursosModel if token is valid
    public function update(Request $request, $id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {
                try {
                    //get the curso   
                    $curso = NCursosModel::find($id);
                    //update the curso
                    $curso->update($request->all());
                    return response()->json($curso, 200);
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
