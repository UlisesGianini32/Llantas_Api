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
        'titulo_real',
    ];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    protected static function booted()
    {
        static::creating(function ($producto) {

            // Consumo por tipo
            if (!$producto->stock) {
                $producto->stock = $producto->tipo === 'par' ? 2 : 4;
            }

            // SKU automático
            if (empty($producto->sku)) {
                $llanta = Llanta::find($producto->llanta_id);
                if (!$llanta) return;

                $producto->sku =
                    $llanta->sku . ($producto->tipo === 'par' ? '-2' : '-4');
            }
        });
    }

    /* =====================
     | CALCULADOS
     |=====================*/

    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta) return 0;
        return intdiv((int) $this->llanta->stock, (int) $this->stock);
    }

    public function getPrecioMlCalculadoAttribute()
    {
        if (!$this->llanta || !$this->llanta->precio_ML) return 0;
        return $this->llanta->precio_ML * $this->stock;
    }

    public function getCostoCalculadoAttribute()
    {
        if (!$this->llanta) return 0;
        return $this->llanta->costo * $this->stock;
    }

    public function getTituloRealAttribute()
    {
        return $this->title_familyname
            ?? $this->llanta->title_familyname
            ?? '—';
    }
}
