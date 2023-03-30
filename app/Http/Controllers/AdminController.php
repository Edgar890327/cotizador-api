<?php

namespace App\Http\Controllers;

use App\Models\AdminModel;
use App\Models\MantenimientoModel;
use App\Models\NEmpleadosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: *');
//header strict-origin-when-cross-origin

//autoload composer
require_once __DIR__ . '/../../../vendor/autoload.php';

class AdminController extends Controller
{
    //create a Admin from AdminModel
    public function create(Request $request)
    {
        return "Hola";
        // //check if request is a json
        // if ($request->isJson()) {


        //     //create an admin
        //     $admin = AdminModel::create([
        //         'name' => $request->input('name'),
        //         'last_name' => $request->input('last_name'),
        //         'email' => $request->input('email'),
        //         'phone' => $request->input('phone'),
        //         'photo' => null,
        //         'password' => Hash::make($request->input('password')),
        //         'token' => Str::random(60),
        //     ]);
        //     return response()->json($admin, 201);
        // } else {
        //     //return response
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }
    }

    public function test()
    {
        return response()->json([
            "dir" => __DIR__,
            "path_requitre" => __DIR__ . "/../../../vendor/autoload.php",
        ]);
    }

    //create a function to login an Admin from AdminModel by email and password
    public function login(Request $request)
    {
        if ($request->isJson()) {
            try {
                $data = $request->json()->all();
                $admin = AdminModel::where('email', $data['email'])->first();

                if ($admin) {
                    if ($admin && Hash::check($data['password'], $admin->password)) {
                        return response()->json([
                            "status" => "success",
                            "user" => "admin",
                            "body" => $admin
                        ], 200);
                    }
                } else {
                    $empleado = NEmpleadosModel::where('email', $data['email'])->first();
                    if ($empleado && Hash::check($data['password'], $admin->password)) {
                        return response()->json([
                            "status" => "success",
                            "user" => "empleado",
                            "body" => $empleado
                        ], 200);
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e], 401);
            }
        }
    }

    //create a function to update an Admin from AdminModel by admin_id
    public function update(Request $request)
    {
        if ($request->isJson()) {
            $admin = AdminModel::where('token', $request->header('token'))->first();
            if ($admin) {
                if ($request->name) {
                    $request->merge([
                        'first_name' => ucwords(str_replace('\' ', '\'', ucwords(str_replace('\'', '\' ', strtolower($request->first_name)))), " ")
                    ]);
                }
                if ($request->last_name) {
                    $request->merge([
                        'last_name' => ucwords(str_replace('\' ', '\'', ucwords(str_replace('\'', '\' ', strtolower($request->last_name)))))
                    ]);
                }

                if ($request->photo) {
                    return response()->json(
                        [
                            "status" => "error",
                            "message" => "use /admin/profile to update this column"
                        ],
                        201
                    );
                }

                if ($request->password) {
                    $request->merge([
                        'password' => Hash::make($request->password),
                        "token" => Str::random(80)
                    ]);
                }

                $admin->update(
                    $request->all()
                );

                return response()->json(
                    [
                        "status" => "ok",
                        "message" => "admin updated",
                        "content" => $admin
                    ],
                    200
                );
            }
        }
    }

    //get an image request and save it to the folder
    public function upload(Request $request)
    {
        $file = $request->file('photo');
        $name = time() . $file->getClientOriginalName();
        // Storage::disk('local')->put($name, file_get_contents($file->getRealPath()));
        $disk = Storage::build([
            'driver' => 'local',
            'root' => '/path/to/root',
        ]);

        $disk->put('image.jpg', $file);
    }

    //recieve and save image with Storage local into the public folder
    public function uploadImage(Request $request)
    {
        //check if request has file image

        if ($request->hasFile('photo')) {
            $admin = AdminModel::where('token', $request->header('token'))->first();
            if ($admin) {
                try {
                    //delete old image from $admin->photo with unlink
                    if ($admin->photo) {
                        unlink(base_path('/public/img/') . $admin->photo);
                    }
                    $file = $request->file('photo');
                    //set name to time() + file name
                    $name = Str::random(20) . time() . '.' . $file->getClientOriginalExtension();

                    //move file to the folder
                    $file->move(base_path('/public/img/'), $name);
                    //check if $file exists

                    if ($file) {
                        //update the photo column in the database
                        $admin->update([
                            'photo' => $name
                        ]);
                        return response()->json(
                            [
                                "status" => "ok",
                                "message" => "photo admin updated",
                                "content" => $admin
                            ],
                            200
                        );
                    } else {
                        return response()->json(
                            [
                                "status" => "error",
                                "message" => "file not found"
                            ],
                            401
                        );
                    }

                    //if file is moved return the name
                    return response()->json(['name' => $name], 200);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            } else {
                return response()->json(
                    [
                        "status" => "error",
                        "message" => "unauthorized"
                    ],
                    401
                );
            }
        } else {
            //return response
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //get image, move it to the folder, update the image in photo column of the AdminModel
    public function updateImage(Request $request, $admin_id)
    {
        $admin = AdminModel::find($admin_id);
        if ($admin) {
            $file = $request->file('photo');
            //set name to time() + file name
            $name = Str::random(20) . time() . '.' . $file->getClientOriginalExtension();

            //move file to the folder
            $file->move(base_path('/public/img/') . '', $name);

            //remove old image if $admin->photo is not empty
            if ($admin->photo != "") {
                unlink(base_path('/public/img/') . $admin->photo);
            }


            //if file is moved update the image in photo column of the AdminModel
            $admin->photo = $name;
            $admin->save();
            return response()->json($admin, 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
