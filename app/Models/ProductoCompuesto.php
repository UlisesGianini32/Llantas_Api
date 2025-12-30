<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoCompuesto extends Model
{
    protected $table = 'producto_compuestos';

    protected $fillable = [
        'llanta_id',
        'tipo',              // par | juego4
        'stock',             // consumo (2 o 4)
        'title_familyname',
        'MLM',
    ];

    protected $appends = ['stock_disponible'];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    /**
     * Stock disponible = stock_real_llanta / consumo
     * ✅ Blindado contra ceros
     */
    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta) {
            return 0;
        }

        $consumo = (int) $this->stock;         // 2 o 4
        $real    = (int) $this->llanta->stock; // stock real

        if ($consumo <= 0 || $real <= 0) {
            return 0;
        }

        return intdiv($real, $consumo);
    }
}
