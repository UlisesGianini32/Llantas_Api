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
        // Quitar encabezado
        $rows->shift();

        foreach ($rows as $row) {

            /*
             * ESTRUCTURA DEL EXCEL (AJUSTA SI CAMBIA):
             * 0 => SKU
             * 1 => Marca
             * 2 => Medida
             * 3 => Descripción
             * 4 => Costo
             * 5 => Stock
             */

            $sku         = trim($row[0] ?? '');
            $marca       = trim($row[1] ?? 'GENERICA');
            $medida      = trim($row[2] ?? 'N/A');
            $descripcion = trim($row[3] ?? 'SIN DESCRIPCIÓN');
            $costo       = (float) ($row[4] ?? 0);
            $stock       = (int) ($row[5] ?? 0);

            if (!$sku || $costo <= 0) {
                continue;
            }

            /* =====================================================
             | LLANTA
             ===================================================== */

            $llanta = Llanta::where('sku', $sku)->first();

            if ($llanta) {
                // ✅ SI EXISTE → SOLO ACTUALIZA STOCK Y COSTO
                $llanta->update([
                    'stock' => $stock,
                    'costo' => $costo,
                ]);
            } else {
                // 🆕 NO EXISTE → CREAR
                $llanta = Llanta::create([
                    'sku'              => $sku,
                    'marca'            => $marca,
                    'medida'           => $medida,
                    'descripcion'      => $descripcion,
                    'costo'            => $costo,
                    'stock'            => $stock,
                    'precio_ML'        => $costo * 1.5, // llanta sola
                    'title_familyname' => "{$marca} {$medida}",
                ]);
            }

            /* =====================================================
             | PRODUCTOS COMPUESTOS
             | ⚠️ NO BORRAR / NO SOBREESCRIBIR
             ===================================================== */

            if ($stock >= 2) {
                $this->upsertCompuesto(
                    $llanta,
                    2,
                    ($costo * 2) * 1.4
                );
            }

            if ($stock >= 4) {
                $this->upsertCompuesto(
                    $llanta,
                    4,
                    ($costo * 4) * 1.35
                );
            }
        }
    }

    /* =====================================================
     | UPSERT PRODUCTO COMPUESTO
     ===================================================== */
    private function upsertCompuesto(Llanta $llanta, int $piezas, float $precioCalculado)
    {
        $skuCompuesto = $llanta->sku . '-' . $piezas;

        $compuesto = ProductoCompuesto::where('sku', $skuCompuesto)->first();

        if ($compuesto) {
            // ✅ EXISTE → SOLO STOCK (NO PRECIO, NO MLM, NO TITULO)
            $compuesto->update([
                'stock' => $piezas,
            ]);
        } else {
            // 🆕 CREAR
            ProductoCompuesto::create([
                'llanta_id'        => $llanta->id,
                'sku'              => $skuCompuesto,
                'tipo'             => $piezas === 2 ? 'par' : 'juego4',
                'stock'            => $piezas,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * $piezas,
                'precio_ML'        => $precioCalculado,
            ]);
        }
    }
}
