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

    /**
     * SKU automático
     */
    protected static function booted()
    {
        static::creating(function ($producto) {
            if (!empty($producto->sku)) return;

            $llanta = Llanta::find($producto->llanta_id);
            if (!$llanta) return;

            // Si no viene stock consumo, lo definimos por tipo
            if (!$producto->stock) {
                if ($producto->tipo === 'par') $producto->stock = 2;
                if ($producto->tipo === 'juego4') $producto->stock = 4;
            }

            // SKU por tipo
            if ($producto->tipo === 'par') {
                $producto->sku = $llanta->sku . '-2';
            }

            if ($producto->tipo === 'juego4') {
                $producto->sku = $llanta->sku . '-4';
            }
        });
    }

    /**
     * Stock disponible (derivado)
     */
    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta || $this->stock <= 0) return 0;
        return intdiv($this->llanta->stock, $this->stock);
    }

    /**
     * Precio ML calculado (derivado)
     */
    public function getPrecioMlCalculadoAttribute()
    {
        if (!$this->llanta || !$this->llanta->precio_ML) return 0;
        return $this->llanta->precio_ML * $this->stock;
    }

    /**
     * Costo calculado (derivado)
     */
    public function getCostoCalculadoAttribute()
    {
        if (!$this->llanta) return 0;
        return $this->llanta->costo * $this->stock;
    }
}
