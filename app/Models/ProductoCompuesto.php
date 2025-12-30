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

            // 🛡️ consumo seguro
            if (!$producto->stock || $producto->stock <= 0) {
                $producto->stock = match ($producto->tipo) {
                    'par'    => 2,
                    'juego4' => 4,
                    default  => 1,
                };
            }

            // 🛡️ SKU automático
            if (empty($producto->sku)) {
                $llanta = Llanta::find($producto->llanta_id);
                if (!$llanta) return;

                $producto->sku = match ($producto->tipo) {
                    'par'    => $llanta->sku . '-2',
                    'juego4' => $llanta->sku . '-4',
                    default  => $llanta->sku,
                };
            }
        });
    }

    /* ===============================
     | ATRIBUTOS CALCULADOS (SEGUROS)
     ===============================*/

    public function getStockDisponibleAttribute(): int
    {
        if (!$this->llanta) return 0;

        $consumo = max((int)$this->stock, 1);
        $stockLlanta = max((int)$this->llanta->stock, 0);

        return intdiv($stockLlanta, $consumo);
    }

    public function getPrecioMlCalculadoAttribute(): float
    {
        if (!$this->llanta) return 0;

        $consumo = max((int)$this->stock, 1);
        return (float)$this->llanta->precio_ML * $consumo;
    }

    public function getCostoCalculadoAttribute(): float
    {
        if (!$this->llanta) return 0;

        $consumo = max((int)$this->stock, 1);
        return (float)$this->llanta->costo * $consumo;
    }

    public function getTituloRealAttribute(): string
    {
        return $this->title_familyname
            ?: ($this->llanta->title_familyname ?? '—');
    }
}
