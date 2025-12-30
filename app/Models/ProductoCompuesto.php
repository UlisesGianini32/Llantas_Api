<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Llanta;

class ProductoCompuesto extends Model
{
    protected $table = 'producto_compuestos';

    protected $fillable = [
        'llanta_id',
        'sku',
        'descripcion',
        'tipo',
        'stock',
        'costo',
        'precio_ML',
        'title_familyname',
        'MLM',
        // stock existe en BD pero NO se usa
    ];

    protected $appends = ['stock_disponible'];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    /**
     * SKU automático
     */
    protected static function booted()
    {
        static::creating(function ($producto) {
            if (!empty($producto->sku)) return;

            $llanta = Llanta::find($producto->llanta_id);
            if (!$llanta) return;

            if ($producto->tipo === 'par') {
                $producto->sku = $llanta->sku . '-2';
            }

            if ($producto->tipo === 'juego4') {
                $producto->sku = $llanta->sku . '-4';
            }
        });
    }

    /**
     * Stock calculado
     */
    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta || $this->piezas <= 0) return 0;
        return intdiv($this->llanta->stock, $this->piezas);
    }
}
