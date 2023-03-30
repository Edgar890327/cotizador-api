<?php

namespace App\Http\Controllers;

use App\Models\NEmpleadosModel;
use App\Models\NTareasModel;
use Carbon\Carbon;
use ErrorException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

//require autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
// require_once __DIR__ . '/../../vendor/autoload.php';

use Mailgun\Mailgun;

class NTareasController extends Controller
{
    //create a new tarea from model NTareasModel
    public function store(Request $request)
    {
        //check if request is a json
        if ($request->header('key') == env('TOKEN')) {
            if ($request->isJson()) {
                try {
                    //create a tarea
                    $tarea = NTareasModel::create([
                        'titulo' => $request->input('titulo'),
                        'descripcion' => $request->input('descripcion'),
                        'fecha_inicio' => $request->input('fecha_inicio'),
                        'fecha_entrega' => $request->input('fecha_entrega'),
                        'prioridad' => $request->input('prioridad'),
                        'estado' => $request->input('estado'),
                        'empleado_id' => $request->input('empleado_id')
                    ]);

                    //get email of empleado where empleado_id is the same as $request->input('empleado_id')

                    $empleado = NEmpleadosModel::where('empleado_id', $request->empleado_id)->first();

                    try {
                        // $mail_body = array(
                        //     'first_name' => $empleado->nombre . ' ' . $empleado->apellido_paterno,
                        //     'titulo' => $tarea->titulo,
                        //     'descripcion' => $tarea->descripcion,
                        //     'fecha_inicio' => $tarea->fecha_inicio,
                        //     'fecha_entrega' => $tarea->fecha_entrega,
                        //     'prioridad' => $tarea->prioridad,
                        //     'email' => $empleado->email,
                        //     'email_from' => 'noreply_villagomezseguros@procelti.com'
                        // );

                        // $template = $this->build('emails.tarea',$mail_body);
                        // $mg = Mailgun::create(
                        //     env('MAILGUN_SECRET'),
                        //     'https://api.eu.mailgun.net'
                        // ); // For EU serverss
                        // $mg->messages()->send('procelti.com', [
                        //     'from'    => 'VillagómezSeguros App<' . $mail_body['email_from'] . '>',
                        //     'to'      => $empleado->email,
                        //     'subject' => 'Nueva asignación, tarea: ' . $mail_body['titulo'],
                        //     'html'    => $template
                        // ]);

                        // Mail::send('emails.tarea', $mail_body, function ($message) use ($mail_body) {
                        //     $message->to($mail_body['email'], $mail_body['first_name'])->subject('Tarea ' . $mail_body['titulo']);
                        //     $message->from('app@villagomezseguros.com', 'Villagomez Seguros');
                        // });
                    } catch (Exception $e) {
                        return response()->json(['error' => $e->getMessage()], 500);
                    }

                    return response()->json($tarea, 200);
                } catch (ErrorException $e) {
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

    //get all tareas from model NTareasModel by empleado_id
    public function getTareasByEmpleado(Request $request)
    {
        //check if request is a json
        if ($request->header('key') == env('TOKEN')) {
            if ($request->isJson()) {
                try {
                    //get all tareas by empleado_id and date today
                    $tareas = NTareasModel::where('empleado_id', $request->input('empleado_id'))->whereDate('fecha_inicio', $request->input('day'))->get();

                    if ($request->input('day') == null) {
                        $tareas = NTareasModel::where('empleado_id', $request->input('empleado_id'))->get();
                    }

                    return response()->json([
                        'status' => 'success',
                        'total' => count($tareas),
                        'body' => $tareas
                    ], 200);
                } catch (ErrorException $e) {
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

    //delete tarea from model NTareasModel by id
    public function deleteTarea(Request $request)
    {
        //check if request is a json
        if ($request->header('key') == env('TOKEN')) {
            if ($request->isJson()) {
                try {
                    //delete tarea by id
                    $tarea = NTareasModel::find($request->input('tarea_id'));
                    $tarea->delete();

                    return response()->json(['status' => 'success'], 200);
                } catch (ErrorException $e) {
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

    //update estado of tarea from model NTareasModel by id
    public function updateEstado(Request $request)
    {
        //check if request is a json
        if ($request->header('key') == env('TOKEN')) {
            if ($request->isJson()) {
                try {
                    //update estado of tarea by id
                    $tarea = NTareasModel::find($request->input('tarea_id'));
                    $tarea->estado = $request->input('estado');
                    $tarea->save();

                    return response()->json(['status' => 'success'], 200);
                } catch (ErrorException $e) {
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

      //dear copilot, I need your help, please help me to build a function to
      public function build(String $myView, array $mail_body)
      {
          return view($myView)->with([
              'first_name' => $mail_body['first_name'],
              'titulo' => $mail_body['titulo'],
              'descripcion' => $mail_body['descripcion'],
              'fecha_inicio' => $mail_body['fecha_inicio'],
              'fecha_entrega' => $mail_body['fecha_entrega'],
              'prioridad' => $mail_body['prioridad'],
              'email' => $mail_body['email'],
              'email_from' => $mail_body['email_from']
          ])->render();
      }
}
