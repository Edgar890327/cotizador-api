<?php

namespace App\Http\Controllers;

use App\Models\NVideosModel;
use Exception;
use Illuminate\Http\Request;

class NVideosController extends Controller
{
    //store video from NVideosModel if token is valid
    public function store(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //create a curso
                    $curso = NVideosModel::create([
                        'curso_id' => $request->input('curso_id'),
                        'nombre' => $request->input('nombre'),
                        'descripcion' => $request->input('descripcion'),
                        'url' => $request->input('url'),
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

    //search videos from NVideosModel if nombre is like request->nombre if token is valid
    public function searchVideos(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //search like 'nombre' in the NVideosModel and where curso_id is equals to request->curso_id ordered by name
            $videos = NVideosModel::where('nombre', 'like', '%' . $request->nombre . '%')->where('curso_id', $request->curso_id)->get();

            //return response
            return response()->json([
                'status' => 'success',
                'count' => count($videos),
                'body' => $videos,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //update a video from NVideosModel if token is valid
    public function update(Request $request, $id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                try {
                    //update a video
                    $video = NVideosModel::find($id);
                    $video->update($request->all());
                    return response()->json([
                        "status" => "success",
                        "body" => $video
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
