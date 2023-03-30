<?php

namespace App\Http\Controllers;

use App\Models\NSubClienteModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mailgun\Mailgun;

class NSubClientsController extends Controller
{

    // get all subclients paginated
    public function getSubClients(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //get all the clientes ordered by name and paginate
            $clientes = NSubClienteModel::orderBy('nombre', 'asc')->paginate($request->per_page);
            //return response
            return response()->json([
                'status' => 'success',
                'body' => $clientes,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // store a new subclient
    public function store(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            // check if request is json
            if ($request->isJson()) {
                // check if subclient already exists
                $subcliente = NSubClienteModel::where('email', $request->email)->first();
                if ($subcliente) {
                    return response()->json([
                        'status' => 'error',
                        'body' => 'El subcliente ya existe',
                    ]);
                } else {
                   try {
                     //create the cliente
                     $nsubcliente = NSubClienteModel::create([
                        'admin_id' => $request->input('admin_id'),
                        'cliente_id' => $request->input('cliente_id'),
                        'nombre' => $request->input('nombre'),
                        'apellido_paterno' =>  $request->input('apellido_paterno'),
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
                    ]);
                   } catch (Exception $e) {
                          return response()->json([
                            'status' => 'error',
                            'body' => $e->getMessage(),
                          ]);
                   }

                    if($subcliente){
                        try {
                            // $mail_body = array(
                            //     'first_name' => $subcliente->nombre . ' ' . $subcliente->apellido_paterno,
                            //     'email' => $subcliente->email,
                            //     'email_from' => 'noreply_villagomezseguros@procelti.com'
                            // );

                            // $template = $this->build('emails.welcome',$mail_body);
                            // $mg = Mailgun::create(
                            //     env('MAILGUN_SECRET'),
                            //     'https://api.eu.mailgun.net'
                            // ); // For EU serverss
                            // $mg->messages()->send('procelti.com', [
                            //     'from'    => 'AutoCotizador Villagomez<' . $mail_body['email_from'] . '>',
                            //     'to'      => $subcliente->email,
                            //     'subject' => 'Hola ' . $mail_body['first_name'],
                            //     'html'    => $template
                            // ]);
                        } catch (Exception $e) {
                            return response()->json(['error' => $e->getMessage()], 500);
                        }

                    }
                    //return response
                    return response()->json([
                        'status' => 'success',
                        'body' => $nsubcliente,
                    ]);
                }
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    // search subclients by name if the token is valid and paginated
    public function searchSubClients(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //get all the clientes ordered by name and paginate
            $clientes = NSubClienteModel::where('nombre', 'like', '%' . $request->search . '%')->orderBy('nombre', 'asc')->paginate($request->per_page);
            //return response
            return response()->json([
                'status' => 'success',
                'body' => $clientes,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // get a subclient by id if the token is valid
    public function getSubClientById(Request $request, $id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //get the cliente by id
            $cliente = NSubClienteModel::find($id);
            //return response
            return response()->json([
                'status' => 'success',
                'body' => $cliente,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // update a subclient by id if the token is valid
    public function updateSubClient(Request $request, $id)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is json
            if ($request->isJson()) {
                //get the cliente by id
                $cliente = NSubClienteModel::find($id);
                //check if cliente exists
                if ($cliente) {
                    try {
                        //update the cliente
                        $cliente->update($request->all());
                        // check if $request has password and if it is not empty and Hash it
                        if ($request->input('password') != null && $request->input('password') != '') {
                            $cliente->password = Hash::make($request->input('password'));
                        }
                        //save the cliente
                        $cliente->save();
                        //return response
                        return response()->json([
                            'status' => 'success',
                            'body' => $cliente,
                        ]);
                    } catch (Exception $e) {
                        return response()->json([
                            'status' => 'error',
                            'body' => $e->getMessage(),
                        ]);
                    }
                } else {
                    return response()->json(['error' => 'Cliente not found'], 404);
                }
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // delete a subclient by id if the token is valid
    public function deleteSubClient(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //get the cliente by id
            $cliente = NSubClienteModel::find($request->subcliente_id);
            //check if cliente exists
            if ($cliente) {
                try {
                    //delete the cliente
                    $cliente->delete();
                    //return response
                    return response()->json([
                        'status' => 'success',
                        'body' => 'Sub cliente eliminado',
                    ]);
                } catch (\Throwable $th) {
                    return response()->json([
                        'status' => 'error',
                        'body' => $th->getMessage(),
                    ]);
                }
            } else {
                return response()->json(['error' => 'Cliente not found'], 404);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // login a subclient if the token is valid
    public function loginSubClient(Request $request)
    {
        if ($request->header('key') == env('TOKEN')) {
            //check if request is json
            if ($request->isJson()) {
                //get the cliente by email
                $cliente = NSubClienteModel::where('email', $request->email)->first();
                //check if cliente exists
                if ($cliente) {
                    //check if password is correct
                    if (Hash::check($request->password, $cliente->password)) {
                        //return response
                        return response()->json([
                            'status' => 'success',
                            'body' => $cliente->with('cliente')->get(),
                        ]);
                    } else {
                        return response()->json(['error' => 'Password incorrect'], 401);
                    }
                } else {
                    return response()->json(['error' => 'Cliente not found'], 404);
                }
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
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
}
