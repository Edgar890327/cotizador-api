<?php

namespace App\Http\Controllers;

use App\Models\CotizacionHistorialModel;
use Illuminate\Http\Request;

class CotizacionHistorialController extends Controller
{
    //store
    public function store($marca, $modelo, $submarca, $version, $cliente_id, $localidad, $codigo_postal)
    {
        // check if the cotizacion_historial already exists

        $cotizacion_historial = new CotizacionHistorialModel();
        $cotizacion_historial->cliente_id = $cliente_id;
        $cotizacion_historial->marca = $marca;
        $cotizacion_historial->modelo = $modelo;
        $cotizacion_historial->submarca = $submarca;
        $cotizacion_historial->version = $version;
        $cotizacion_historial->codigo_postal = $codigo_postal;
        $cotizacion_historial->localidad = $localidad;
        $cotizacion_historial->emitido = false;
        $cotizacion_historial->emitido_por = null;
        $cotizacion_historial->auto_id = null;
        $cotizacion_historial->prima = null;
        $cotizacion_historial->poliza_id = null;
        $cotizacion_historial->folio_gs = "000000";
        $cotizacion_historial->folio_mapfre = "000000";
        $cotizacion_historial->folio_banorte = "000000";
        $cotizacion_historial->folio_chubb = "000000";
        $cotizacion_historial->folio_ana = "000000";
        $cotizacion_historial->folio_qualitas = "000000";
        $cotizacion_historial->payment_link = "vacio";
        $cotizacion_historial->save();
        return response()->json([
            'status' => 'success',
            'message' => 'CotizacionHistorial created successfully',
            'data' => $cotizacion_historial
        ], 201);
    }

    //update emitir
    public function updateEmitir(Request $request, $id)
    {
        try {
            $cotizacion_historial = CotizacionHistorialModel::find($id);
            $cotizacion_historial->emitido = true;
            $cotizacion_historial->emitido_por = $request->input('emitido_por');
            $cotizacion_historial->auto_id = $request->input('auto_id');
            $cotizacion_historial->prima = $request->input('prima');
            $cotizacion_historial->poliza_id = $request->input('poliza_id');
            if ($request->has('payment_link')) {
                $cotizacion_historial->payment_link = $request->input('payment_link');
            }
            $cotizacion_historial->save();
            return response()->json([
                'status' => 'success',
                'message' => 'CotizacionHistorial updated successfully',
                'cotizacion_historial' => $cotizacion_historial
            ], 200);
        } catch (\Error $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating CotizacionHistorial',
                'error' => $th
            ], 500);
        }
    }

    // update folio
    public function updateFolio(Request $request, $id)
    {
        try {
            $cotizacion_historial = CotizacionHistorialModel::find($id);
            $cotizacion_historial->update($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'CotizacionHistorial updated successfully',
                'cotizacion_historial' => $cotizacion_historial
            ], 200);
        } catch (\Error $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating CotizacionHistorial',
                'error' => $th
            ], 500);
        }
    }

    // get all
    public function getAll()
    {
        $cotizacion_historial = CotizacionHistorialModel::all();
        return response()->json([
            'status' => 'success',
            'message' => 'CotizacionHistorials retrieved successfully',
            'cotizacion_historial' => $cotizacion_historial
        ], 200);
    }

    // get only not emitted
    public function getNotEmitted()
    {
        $cotizacion_historial = CotizacionHistorialModel::where('emitido', false)->get();
        return response()->json([
            'status' => 'success',
            'message' => 'CotizacionHistorials retrieved successfully',
            'cotizacion_historial' => $cotizacion_historial
        ], 200);
    }

    // get only emitted
    public function getEmitted()
    {
        $cotizacion_historial = CotizacionHistorialModel::where('emitido', true)->get();
        return response()->json([
            'status' => 'success',
            'message' => 'CotizacionHistorials retrieved successfully',
            'cotizacion_historial' => $cotizacion_historial
        ], 200);
    }

    // get by cliente_id
    public function getByClienteId(Request $request)
    {

        $cotizacion_historial = CotizacionHistorialModel::where('cliente_id', $request->cliente_id)->paginate($request->per_page);

        return response()->json([
            'status' => 'success',
            'message' => 'CotizacionHistorials retrieved successfully',
            'cotizacion_historial' => $cotizacion_historial
        ], 200);
    }
}
