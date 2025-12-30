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
        foreach ($rows as $row) {

            // =============================
            // COLUMNA A → SKU
            // =============================
            $sku = trim($row[0] ?? '');

            if (
                $sku === '' ||
                strtolower($sku) === 'codigo' ||
                strlen($sku) < 4
            ) {
                continue;
            }

            // =============================
            // COLUMNA B → DESCRIPCIÓN
            // =============================
            $descripcionRaw = trim($row[1] ?? '');
            if ($descripcionRaw === '') continue;

            // =============================
            // EXTRAER MEDIDA
            // =============================
            preg_match(
                '/(\d{2,3}\/\d{2,3}[Rr]?\d{2,3})/',
                $descripcionRaw,
                $medidaMatch
            );
            $medida = $medidaMatch[0] ?? 'N/A';

            // =============================
            // MARCA
            // =============================
            $marcas = [
                'MICHELIN','CONTINENTAL','PIRELLI','BRIDGESTONE',
                'GOODYEAR','YOKOHAMA','TOYO','HANKOOK','FIRESTONE',
                'BFGOODRICH','KUMHO','GENERAL','GUTE','AMULET',
                'NOVAMAX','MILEVER'
            ];

            $marca = 'GENERICA';
            foreach ($marcas as $m) {
                if (stripos($descripcionRaw, $m) !== false) {
                    $marca = ucfirst(strtolower($m));
                    break;
                }
            }

            // =============================
            // LIMPIAR DESCRIPCIÓN
            // =============================
            $descripcion = trim(preg_replace('/\s+/', ' ', $descripcionRaw));

            // =============================
            // COLUMNA C → STOCK REAL
            // =============================
            $stockRaw = trim($row[2] ?? '');
            $stock = (int) preg_replace('/[^0-9]/', '', $stockRaw);

            // =============================
            // COLUMNA D → COSTO
            // =============================
            $costoRaw = str_replace(['$', ','], '', $row[3] ?? '');
            $costo = is_numeric($costoRaw) ? (float) $costoRaw : 0;
            if ($costo <= 0) continue;

            // =============================
            // COLUMNA E → PRECIO ML
            // =============================
            $precioRaw = str_replace(['$', ','], '', $row[4] ?? '');
            $precioML = is_numeric($precioRaw) ? (float) $precioRaw : null;

            // =============================
            // TITLE FAMILY
            // =============================
            $titleFamily = "{$marca} {$medida}";

            // =============================
            // CREAR / ACTUALIZAR LLANTA
            // =============================
            $llanta = Llanta::updateOrCreate(
                ['sku' => $sku],
                [
                    'descripcion'      => $descripcion,
                    'marca'            => $marca,
                    'medida'           => $medida,
                    'stock'            => $stock,
                    'costo'            => $costo,
                    'precio_ML'        => $precioML,
                    'title_familyname' => $titleFamily,
                    'MLM'              => null,
                ]
            );

            // =============================
            // SINCRONIZAR COMPUESTOS
            // =============================
            $this->syncPaquetes($llanta);
        }
    }

    /**
     * 🔥 REGLA DE ORO:
     * - stock <= 0 → NO HAY COMPUESTOS
     * - stock > 0 → consumo fijo 2 y 4
     */
    private function syncPaquetes(Llanta $llanta)
    {
        // 🔴 Si no hay stock → eliminar compuestos
        if ((int) $llanta->stock <= 0) {
            ProductoCompuesto::where('llanta_id', $llanta->id)->delete();
            return;
        }

        // 🟢 PAR (consumo 2)
        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'par'],
            [
                'stock'            => 2, // 👈 CONSUMO
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'MLM'              => $llanta->MLM,
            ]
        );

        // 🟢 JUEGO DE 4 (consumo 4)
        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'juego4'],
            [
                'stock'            => 4, // 👈 CONSUMO
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'MLM'              => $llanta->MLM,
            ]
        );
    }
}
