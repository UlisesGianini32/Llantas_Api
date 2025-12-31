<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Llanta extends Model
{
    protected $fillable = [
        'sku',
        'marca',
        'medida',
        'descripcion',
        'costo',
        'precio_ML',         // 👈 editable
        'title_familyname',
        'MLM',               // 👈 editable
        'stock',
    ];

    public function compuestos()
    {
        return $this->hasMany(ProductoCompuesto::class);
    }

    /* ===========================
     | PRECIO ML REAL (EDITABLE)
     ===========================*/
    public function getPrecioMlRealAttribute()
    {
        return $this->precio_ML ?? 0;
    }

    /* ===========================
     | COSTO REAL
     ===========================*/
    public function getCostoRealAttribute()
    {
        return $this->costo ?? 0;
    }

    /* ===========================
     | TÍTULO REAL
     ===========================*/
    public function getTituloRealAttribute()
    {
        return $this->title_familyname ?? '—';
    }
}
