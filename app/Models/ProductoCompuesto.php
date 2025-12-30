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
        'stock',
        'descripcion',
        'title_familyname',
        'costo',
        'precio_ML',
        'MLM',
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
     |===========================*/
    public function getStockDisponibleAttribute()
    {
        $consumo = (int) $this->stock;
        $real    = (int) optional($this->llanta)->stock;

        if ($consumo <= 0 || $real <= 0) {
            return 0;
        }

        return intdiv($real, $consumo);
    }

    /* ===========================
     | PRECIO ML (editable)
     |===========================*/
    public function getPrecioMlRealAttribute()
    {
        // 👉 si el usuario editó precio, usarlo
        if (!is_null($this->precio_ML)) {
            return $this->precio_ML;
        }

        // 👉 fallback automático
        return optional($this->llanta)->precio_ML
            ? optional($this->llanta)->precio_ML * $this->stock
            : 0;
    }

    /* ===========================
     | COSTO (editable)
     |===========================*/
    public function getCostoRealAttribute()
    {
        if (!is_null($this->costo)) {
            return $this->costo;
        }

        return optional($this->llanta)->costo
            ? optional($this->llanta)->costo * $this->stock
            : 0;
    }

    /* ===========================
     | TÍTULO REAL
     |===========================*/
    public function getTituloRealAttribute()
    {
        return $this->title_familyname
            ?? optional($this->llanta)->title_familyname
            ?? '—';
    }
}
