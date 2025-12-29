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
        'precio_ML',
        'title_familyname',
        'MLM',
        'stock',
    ];

    public function compuestos()
    {
        return $this->hasMany(ProductoCompuesto::class);
    }
}
