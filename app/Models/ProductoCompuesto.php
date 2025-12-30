<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoCompuesto extends Model
{
    protected $fillable = [
        'llanta_id',
        'tipo',
        'stock',
        'descripcion',
        'title_familyname',
        'MLM',
    ];

    protected $appends = ['stock_disponible'];

    public function llanta()
    {
        return $this->belongsTo(Llanta::class);
    }

    /**
     * 🔥 PROTEGIDO CONTRA DIVISIÓN ENTRE CERO
     */
    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta) {
            return 0;
        }

        if ($this->stock <= 0) {
            return 0;
        }

        if ($this->llanta->stock <= 0) {
            return 0;
        }

        return intdiv($this->llanta->stock, $this->stock);
    }
}
