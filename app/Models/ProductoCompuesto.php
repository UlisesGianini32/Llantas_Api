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
     * Stock disponible = stock real / consumo
     * 🔒 nunca divide entre 0
     */
    public function getStockDisponibleAttribute()
    {
        $consumo = (int) $this->stock;
        $real    = (int) optional($this->llanta)->stock;

        if ($consumo <= 0 || $real <= 0) {
            return 0;
        }

        return intdiv($real, $consumo);
    }
}
