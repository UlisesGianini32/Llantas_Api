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
        'stock', // consumo
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
        static::creating(function ($p) {
            if (!$p->stock) {
                $p->stock = $p->tipo === 'par' ? 2 : 4;
            }

            if (!$p->sku) {
                $llanta = Llanta::find($p->llanta_id);
                if ($llanta) {
                    $p->sku = $llanta->sku . ($p->tipo === 'par' ? '-2' : '-4');
                }
            }
        });
    }

    public function getStockDisponibleAttribute()
    {
        if (!$this->llanta || $this->stock <= 0) return 0;
        return intdiv($this->llanta->stock, $this->stock);
    }

    public function getPrecioMlCalculadoAttribute()
    {
        if (!$this->llanta) return 0;
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
