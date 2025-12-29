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
        foreach ($rows as $index => $row) {

            // ======================================
            // SALTAR ENCABEZADO (fila 0)
            // ======================================
            if ($index === 0) continue;

            // ======================================
            // COLUMNA A → SKU
            // ======================================
            $sku = trim($row[0] ?? '');
            if ($sku === '') continue;

            // ======================================
            // COLUMNA B → DESCRIPCIÓN
            // ======================================
            $descripcionRaw = trim($row[1] ?? '');
            if ($descripcionRaw === '') continue;

            // ======================================
            // EXTRAER MEDIDA DESDE DESCRIPCIÓN
            // ======================================
            preg_match(
                '/(\d{2,3}\/\d{2,3}[Rr]?\d{2,3})/',
                $descripcionRaw,
                $medidaMatch
            );
            $medida = $medidaMatch[0] ?? 'N/A';

            // ======================================
            // MARCA DESDE DESCRIPCIÓN
            // ======================================
            $marcas = [
                'MICHELIN', 'CONTINENTAL', 'PIRELLI', 'BRIDGESTONE',
                'GOODYEAR', 'YOKOHAMA', 'TOYO', 'HANKOOK',
                'FIRESTONE', 'BFGOODRICH', 'KUMHO', 'GENERAL',
                'GUTE', 'AMULET', 'NOVAMAX', 'MILEVER'
            ];

            $marca = 'GENERICA';
            foreach ($marcas as $m) {
                if (stripos($descripcionRaw, $m) !== false) {
                    $marca = ucfirst(strtolower($m));
                    break;
                }
            }

            // ======================================
            // LIMPIAR DESCRIPCIÓN
            // ======================================
            $descripcion = trim(preg_replace('/\s+/', ' ', $descripcionRaw));

            // ======================================
            // COLUMNA C → EXISTENCIA (STOCK)
            // Acepta 20+, 10, etc
            // ======================================
            $stockRaw = trim($row[2] ?? '');
            $stock = (int) preg_replace('/[^0-9]/', '', $stockRaw);

            // ======================================
            // COLUMNA D → COSTO
            // ======================================
            $costo = is_numeric($row[3] ?? null) ? (float) $row[3] : 0;
            if ($costo <= 0) continue;

            // ======================================
            // COLUMNA E → PRECIO ML (PROMOCIÓN)
            // ======================================
            $precioML = is_numeric($row[4] ?? null) ? (float) $row[4] : null;

            // ======================================
            // TITLE FAMILY
            // ======================================
            $titleFamily = "{$marca} {$medida}";

            // ======================================
            // CREAR / ACTUALIZAR LLANTA
            // ======================================
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

            // ======================================
            // SINCRONIZAR PRODUCTOS COMPUESTOS
            // ======================================
            $this->syncPaquetes($llanta);
        }
    }

    /**
     * ======================================
     * CREA / ACTUALIZA PAR Y JUEGO DE 4
     * ======================================
     */
    private function syncPaquetes(Llanta $llanta)
    {
        // -------- PAR --------
        ProductoCompuesto::updateOrCreate(
            [
                'llanta_id' => $llanta->id,
                'tipo'      => 'par',
            ],
            [
                'sku'    => $llanta->sku . '-2',
                'stock'  => intdiv($llanta->stock, 2),
                'costo'  => $llanta->costo * 2,
            ]
        );

        // -------- JUEGO DE 4 --------
        ProductoCompuesto::updateOrCreate(
            [
                'llanta_id' => $llanta->id,
                'tipo'      => 'juego4',
            ],
            [
                'sku'    => $llanta->sku . '-4',
                'stock'  => intdiv($llanta->stock, 4),
                'costo'  => $llanta->costo * 4,
            ]
        );
    }
}
