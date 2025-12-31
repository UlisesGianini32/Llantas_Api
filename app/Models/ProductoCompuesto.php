<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoCompuesto extends Model
{
    protected $table = 'producto_compuestos';

    protected $fillable = [
        'llanta_id',
        'sku',
        'tipo',
        'stock',              // consumo: 2 o 4
        'descripcion',
        'title_familyname',
        'costo',              // 👈 editable
        'precio_ML',          // 👈 editable
        'MLM',                // 👈 editable
    ];

    protected $appends = [
        'stock_disponible',
        'precio_ml_real',
        'costo_real',
        'titulo_real',
    ];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    /* ===========================
     | STOCK DISPONIBLE
     ===========================*/
    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta || $this->stock <= 0) {
            return 0;
        }

        return intdiv($this->llanta->stock, $this->stock);
    }

    /* ===========================
     | PRECIO ML REAL (100% EDITABLE)
     ===========================*/
    public function getPrecioMlRealAttribute()
    {
        return $this->precio_ML ?? 0;
    }

    /* ===========================
     | COSTO REAL (100% EDITABLE)
     ===========================*/
    public function getCostoRealAttribute()
    {
        return $this->costo ?? 0;
    }

    /* ===========================
     | TÍTULO REAL
     ===========================*/
    public function getTituloRealAttribute()
    {
        return $this->title_familyname ?? '—';
    }
}
