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
        'stock', // 👈 CONSUMO (2 o 4)
        'title_familyname',
        'MLM',
    ];

    protected $appends = [
        'stock_disponible',
        'precio_ml_calculado',
        'costo_calculado',
        'titulo_real',
    ];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    protected static function booted()
    {
        static::creating(function ($producto) {
            // set consumo por tipo si no viene
            if (!$producto->stock) {
                if ($producto->tipo === 'par') $producto->stock = 2;
                if ($producto->tipo === 'juego4') $producto->stock = 4;
            }

            // sku autogenerado si no viene
            if (empty($producto->sku)) {
                $llanta = Llanta::find($producto->llanta_id);
                if (!$llanta) return;

                if ($producto->tipo === 'par') $producto->sku = $llanta->sku . '-2';
                if ($producto->tipo === 'juego4') $producto->sku = $llanta->sku . '-4';
            }
        });
    }

    // ✅ Stock disponible = stock_real / consumo
    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta) return 0;

        $consumo = (int) ($this->stock ?: 0);
        if ($consumo <= 0) return 0;

        return intdiv((int)$this->llanta->stock, $consumo);
    }

    public function getPrecioMlCalculadoAttribute()
    {
        if (!$this->llanta || !$this->llanta->precio_ML) return 0;

        $consumo = (int) ($this->stock ?: 0);
        if ($consumo <= 0) return 0;

        return (float)$this->llanta->precio_ML * $consumo;
    }

    public function getCostoCalculadoAttribute()
    {
        if (!$this->llanta) return 0;

        $consumo = (int) ($this->stock ?: 0);
        if ($consumo <= 0) return 0;

        return (float)$this->llanta->costo * $consumo;
    }

    // ✅ Título real: si compuesto no tiene, usa el de la llanta
    public function getTituloRealAttribute()
    {
        return $this->title_familyname
            ?? $this->llanta->title_familyname
            ?? '—';
    }
}
