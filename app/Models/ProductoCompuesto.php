<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoCompuesto extends Model
{
    protected $table = 'producto_compuestos';

    protected $fillable = [
        'llanta_id',
        'sku',
        'descripcion',
        'tipo',
        'stock', // consumo (2 o 4)
        'title_familyname',
        'MLM',
    ];

    protected $appends = [
        'stock_disponible',
        'precio_ml_calculado',
        'costo_calculado',
    ];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    protected static function booted()
    {
        static::creating(function ($producto) {

            if (!$producto->stock) {
                $producto->stock = $producto->tipo === 'juego4' ? 4 : 2;
            }

            if (empty($producto->sku)) {
                $llanta = Llanta::find($producto->llanta_id);
                if ($llanta) {
                    $producto->sku = $llanta->sku . ($producto->stock === 4 ? '-4' : '-2');
                }
            }
        });
    }

    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta || $this->stock <= 0) return 0;

        return intdiv((int)$this->llanta->stock, (int)$this->stock);
    }

    public function getPrecioMlCalculadoAttribute()
    {
        if (!$this->llanta || !$this->llanta->precio_ML || $this->stock <= 0) return 0;

        return (float)$this->llanta->precio_ML * (int)$this->stock;
    }

    public function getCostoCalculadoAttribute()
    {
        if (!$this->llanta || $this->stock <= 0) return 0;

        return (float)$this->llanta->costo * (int)$this->stock;
    }
}
