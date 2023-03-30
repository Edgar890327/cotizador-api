<?php

namespace App\Http\Controllers;

use App\Models\NClientesModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

//require autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
// require_once __DIR__ . '/../../vendor/autoload.php';

use Mailgun\Mailgun;

class NClientesController extends Controller
{
    //store a cliente from NclienteModel if token is valid
    public function store(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {

                //check if the cliente already exists


                if (!NClientesModel::where('email', $request->input('email'))->first()) {
                    try {
                        //create a cliente
                        $cliente = NClientesModel::create([
                            'admin_id' => $request->input('admin_id'),
                            'nombre' => $request->input('nombre'),
                            'apellido_paterno' => $request->input('apellido_paterno'),
                            'apellido_materno' => $request->input('apellido_materno'),
                            'email' => $request->input('email'),
                            'password' => Hash::make($request->input('password')),
                            'nombre_compania' => $request->input('nombre_compania'),
                            'direccion' => $request->input('direccion'),
                            'telefono' => $request->input('telefono'),
                            'tipo_cliente' => $request->input('tipo_cliente'),
                            'genero' => $request->input('genero'),
                            'cod_estado' => $request->input('cod_estado'),
                            'cod_municipio' => $request->input('cod_municipio'),
                            'estado' => $request->input('estado'),
                            'municipio' => $request->input('municipio'),
                            'cod_postal' => $request->input('cod_postal'),
                            'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                            'rfc' => $request->input('rfc'),
                            'fis_mor' => $request->input('fis_mor'),
                            'chubb_person_id' => null,
                            'gs_descuento' => $request->input('gs_descuento'),
                            'qualitas_descuento' => $request->input('qualitas_descuento'),
                            'chubb_descuento' => $request->input('chubb_descuento'),
                            'mapfre_descuento' => $request->input('mapfre_descuento'),
                            'banorte_descuento' => $request->input('banorte_descuento'),
                            'ana_descuento' => $request->input('ana_descuento'),
                            'bloqueado' => false,
                        ]);

                        if ($cliente) {
                            try {
                                // $mail_body = array(
                                //     'first_name' => $cliente->nombre . ' ' . $cliente->apellido_paterno,
                                //     'email' => $cliente->email,
                                //     'email_from' => 'noreply_villagomezseguros@procelti.com'
                                // );

                                // $template = $this->build('emails.welcome', $mail_body);
                                // $mg = Mailgun::create(
                                //     env('MAILGUN_SECRET'),
                                //     'https://api.eu.mailgun.net'
                                // ); // For EU serverss
                                // $mg->messages()->send('procelti.com', [
                                //     'from'    => 'AutoCotizador Villagomez<' . $mail_body['email_from'] . '>',
                                //     'to'      => $cliente->email,
                                //     'subject' => 'Hola ' . $mail_body['first_name'],
                                //     'html'    => $template
                                // ]);

                                // Mail::send('emails.tarea', $mail_body, function ($message) use ($mail_body) {
                                //     $message->to($mail_body['email'], $mail_body['first_name'])->subject('Tarea ' . $mail_body['titulo']);
                                //     $message->from('app@villagomezseguros.com', 'Villagomez Seguros');
                                // });
                            } catch (Exception $e) {
                                return response()->json(['error' => $e->getMessage()], 500);
                            }
                        }



                        return response()->json($cliente, 200);
                    } catch (Exception $e) {
                        return response()->json(['error' => $e], 401);
                    }
                } else {
                    return response()->json(['error' => 'Cliente already exists'], 401);
                }
            } else {
                //return response
                return response()->json(['error' => 'Unauthorized type of request'], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //get all the clientes from NclienteModel if token is valid and paginate
    public function getClientes(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //get all the clientes ordered by name and paginate
            $clientes = NClientesModel::orderBy('nombre', 'asc')->paginate($request->per_page);
            //return response
            return response()->json([
                'status' => 'success',
                'body' => $clientes,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //search clientes like nombre from NclienteModel if token is valid and paginate
    public function searchClientes(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            // get from $request type
            $type = $request->input('type');
            if ($type == 0) {
                //get all the clientes ordered by name and paginate
                $clientes = NClientesModel::where('nombre', 'like', '%' . $request->search . '%')->orderBy('nombre', 'asc')->paginate($request->per_page);
                //return response
                return response()->json([
                    'status' => 'success',
                    'body' => $clientes,
                ]);
            } else if ($type == 1) {
                //get all the clientes ordered by name and paginate
                $clientes = NClientesModel::where('nombre_compania', 'like', '%' . $request->search . '%')->orderBy('nombre', 'asc')->paginate($request->per_page);
                //return response
                return response()->json([
                    'status' => 'success',
                    'body' => $clientes,
                ]);
            } else if ($type == 2) {
                //get all the clientes ordered by name and paginate
                $clientes = NClientesModel::where('email', 'like', '%' . $request->search . '%')->orderBy('nombre', 'asc')->paginate($request->per_page);
                //return response
                return response()->json([
                    'status' => 'success',
                    'body' => $clientes,
                ]);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //update a cliente from NclienteModel if token is valid
    public function update(Request $request, $id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {
                try {
                    //get the cliente
                    $cliente = NClientesModel::find($id);
                    //update the cliente
                    $cliente->update($request->all());
                    //check if $request has password then update password as Hash::make($request->input('password'))
                    if ($request->input('password') != null) {
                        $cliente->password = Hash::make($request->input('password'));
                    }
                    return response()->json($cliente, 200);
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

    //create function to delete a cliente from NclienteModel if token is valid

    public function delete(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {
                try {
                    //get the cliente
                    $cliente = NClientesModel::find($request->cliente_id);
                    //delete the cliente
                    $cliente->delete();
                    return response()->json([
                        'status' => 'success',
                        'body' => $cliente,
                    ]);
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

    //create a login of clientes and return the cliente if token is valid
    public function login(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is a json
            if ($request->isJson()) {
                try {
                    //get the cliente
                    $cliente = NClientesModel::where('email', $request->email)->first();
                    //check if the cliente exists
                    if ($cliente) {
                        //check if the password is correct
                        if ($cliente->bloqueado == false) {
                            if (Hash::check($request->password, $cliente->password)) {
                                //return the cliente
                                return response()->json([
                                    'status' => 'success',
                                    'tipo_cliente' => $cliente->tipo_cliente,
                                    'body' => $cliente,
                                ]);
                            }else{
                                //return error
                                return response()->json([
                                    'error' => 'Cliente bloqueado'
                                ], 401);
                            }
                        } else {
                            //return error
                            return response()->json(['error' => 'Password is incorrect'], 401);
                        }
                    } else {
                        //return error
                        return response()->json(['error' => 'Cliente not found'], 401);
                    }
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

    public function build(String $myView, array $mail_body)
    {
        return view($myView)->with([
            'first_name' => $mail_body['first_name'],
            'email' => $mail_body['email'],
            'email_from' => $mail_body['email_from']
        ])->render();
    }

    // get client by id
    public function getClienteById(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            // get the cliente
            $cliente = NClientesModel::where('cliente_id', $request->cliente_id)->first();
            // if cliente exists
            if ($cliente) {
                // return the cliente
                return response()->json([
                    'status' => 'success',
                    'body' => $cliente,
                ]);
            } else {
                // return error
                return response()->json(['error' => 'Cliente not found'], 404);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
