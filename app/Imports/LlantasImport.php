<?php

namespace App\Imports;

use App\Models\Llanta;
use App\Models\ProductoCompuesto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class LlantasImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $rows->shift(); // quitar encabezado

        foreach ($rows as $row) {

            $sku   = trim($row[0] ?? '');
            $marca = trim($row[1] ?? 'GENERICA');
            $medida = trim($row[2] ?? 'N/A');
            $stock = intval($row[3] ?? 0);
            $costo = floatval($row[4] ?? 0);
            $descripcion = trim($row[5] ?? 'SIN DESCRIPCIÓN');

            if (!$sku || $costo <= 0) continue;

            $llanta = Llanta::where('sku', $sku)->first();

            /* ===============================
             | SKU EXISTENTE → SOLO STOCK
             ===============================*/
            if ($llanta) {
                $llanta->update([
                    'stock' => $stock,
                ]);

                // ❗ NO recalculamos precios
                $this->actualizarStockCompuestos($llanta);
                continue;
            }

            /* ===============================
             | SKU NUEVO → CREAR CON FÓRMULA
             ===============================*/
            $llanta = Llanta::create([
                'sku'              => $sku,
                'marca'            => $marca,
                'medida'           => $medida,
                'descripcion'      => $descripcion,
                'costo'            => $costo,
                'stock'            => $stock,
                'precio_ML'        => $costo * 1.5,
                'title_familyname' => "$marca $medida",
            ]);

            $this->crearCompuestosIniciales($llanta);
        }
    }

    /* ===============================
     | SOLO ACTUALIZA STOCK
     ===============================*/
    private function actualizarStockCompuestos(Llanta $llanta)
    {
        ProductoCompuesto::where('llanta_id', $llanta->id)
            ->update(['updated_at' => now()]);
    }

    /* ===============================
     | CREACIÓN INICIAL CON FÓRMULAS
     ===============================*/
    private function crearCompuestosIniciales(Llanta $llanta)
    {
        // PAR
        if ($llanta->stock >= 2) {
            ProductoCompuesto::create([
                'llanta_id'  => $llanta->id,
                'sku'        => $llanta->sku . '-2',
                'tipo'       => 'par',
                'stock'      => 2,
                'costo'      => $llanta->costo * 2,
                'precio_ML'  => ($llanta->costo * 2) * 1.4,
                'descripcion'=> $llanta->descripcion,
            ]);
        }

        // JUEGO 4
        if ($llanta->stock >= 4) {
            ProductoCompuesto::create([
                'llanta_id'  => $llanta->id,
                'sku'        => $llanta->sku . '-4',
                'tipo'       => 'juego4',
                'stock'      => 4,
                'costo'      => $llanta->costo * 4,
                'precio_ML'  => ($llanta->costo * 4) * 1.35,
                'descripcion'=> $llanta->descripcion,
            ]);
        }
    }
}
