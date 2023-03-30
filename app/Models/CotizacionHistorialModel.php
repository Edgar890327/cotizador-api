<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionHistorialModel extends Model
{
    protected $table = 'cotizacion_historial';
    protected $primaryKey = 'id_cotizacion_historial';
    protected $fillable = [
        'cliente_id',
        'marca',
        'modelo',
        'submarca',
        'version',
        'codigo_postal',
        'localidad',
        'emitido',
        'emitido_por',
        'auto_id',
        'prima',
        'poliza_id',
        'folio_gs',
        'folio_mapfre',
        'folio_banorte',
        'folio_chubb',
        'folio_ana',
        'folio_qualitas',
        'payment_link'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}
