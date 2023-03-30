<?php

namespace App\Http\Controllers;

use App\Models\SepomexModel;
use Illuminate\Http\Request;

class SepomexController extends Controller
{
    // get d_estado from the table 'sepomex' grouped by d_estado
    public function getEstados()
    {
        //check if header('key') has a env('TOKEN')
        if (request()->header('key') == env('TOKEN')) {
            $estados = SepomexModel::select('d_estado')->groupBy('d_estado')->get();
            return response()->json([
                'status' => 'success',
                'data' => $estados
            ]);
        } else {
            // return error
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    public function getMunicipios($d_estado)
    {
        //check if header('key') has a env('TOKEN')
        if (request()->header('key') == env('TOKEN')) {
            $municipios = SepomexModel::select('D_mnpio')->where('d_estado', $d_estado)->groupBy('D_mnpio')->get();
            return response()->json([
                'status' => 'success',
                'data' => $municipios
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }
    }

    public function getColonias($d_codigo)
    {

        //check if header('key') has a env('TOKEN')
        if (request()->header('key') == env('TOKEN')) {
            // select d_codigo as codigo_postal, d_asenta as colonia, d_tipo_asenta as tipo_asenta, D_mnpio as municipio, d_estado as estado, d_ciudad as ciudad, d_CP as codigo_postal, c_estado as clave_estado, c_oficina as clave_oficina, c_CP as clave_codigo_postal, c_tipo_asenta as clave_tipo_asenta, c_mnpio as clave_municipio, id_asenta_cpcons as id_asenta_cpcons, d_zona as zona from sepomex where d_codigo = '01000'
            $colonias = SepomexModel::select('d_codigo as codigo_postal', 'd_asenta as localifdad', 'd_tipo_asenta as tipo_asentamiento', 'D_mnpio as municipio', 'd_estado as estado', 'd_ciudad as ciudad', 'd_CP as codigo_postal', 'c_estado as clave_estado', 'c_oficina as clave_oficina', 'c_CP as clave_codigo_postal', 'c_tipo_asenta as clave_tipo_asenta', 'c_mnpio as cod_municipio', 'id_asenta_cpcons as cod_colonia', 'd_zona as zona')->where('d_codigo', $d_codigo)->get();
            return response()->json([
                'status' => 'success',
                'data' => $colonias
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }
    }
}
