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
        'stock',              // consumo: 2 o 4
        'descripcion',
        'title_familyname',
        'costo',
        'precio_ML',
        'MLM',
    ];

    protected $appends = ['stock_disponible'];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    /**
     * stock disponible = stock real / consumo
     * nunca divide entre 0
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
