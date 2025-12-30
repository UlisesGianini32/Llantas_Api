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
        'stock', // ← aquí defines cuántas llantas consume
        'costo',
        'precio_ML',
        'title_familyname',
        'MLM',
    ];

    protected $appends = [
        'stock_disponible',
    ];

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
                $producto->stock = 2; // 🔥 CLAVE
            }

            if ($producto->tipo === 'juego4') {
                $producto->sku = $llanta->sku . '-4';
                $producto->stock = 4; // 🔥 CLAVE
            }
        });
    }

    /**
     * Stock disponible REAL
     */
    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta || $this->stock <= 0) {
            return 0;
        }

        return intdiv($this->llanta->stock, $this->stock);
    }
}
