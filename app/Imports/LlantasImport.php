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

            // =========================
            // SALTAR ENCABEZADO
            // =========================
            if ($index === 0) continue;

            // =========================
            // COLUMNA A - SKU
            // =========================
            $sku = trim($row[0] ?? '');
            if ($sku === '') continue;

            // =========================
            // COLUMNA B - DESCRIPCIÓN
            // =========================
            $descripcionRaw = trim($row[1] ?? '');
            if ($descripcionRaw === '') continue;

            // =========================
            // EXTRAER MEDIDA (fallback)
            // =========================
            preg_match(
                '/(\d{2,3}([\/\-Xx]\d{2,3})?[Rr]?\d{2,3}(\.\d+)?)/',
                $descripcionRaw,
                $medidaMatch
            );

            // =========================
            // COLUMNA D - MEDIDA (si existe)
            // =========================
            $medida = trim($row[3] ?? '') ?: ($medidaMatch[0] ?? 'N/A');

            // =========================
            // MARCA (desde descripción si no viene)
            // =========================
            $marcaCol = trim($row[2] ?? '');

            if ($marcaCol !== '') {
                $marca = $marcaCol;
            } else {
                $marcasConocidas = [
                    'MICHELIN', 'CONTINENTAL', 'PIRELLI', 'BRIDGESTONE',
                    'GOODYEAR', 'YOKOHAMA', 'TOYO', 'HANKOOK',
                    'BFGOODRICH', 'FIRESTONE', 'KUMHO', 'GENERAL'
                ];

                $marca = 'GENERICA';
                foreach ($marcasConocidas as $m) {
                    if (stripos($descripcionRaw, $m) !== false) {
                        $marca = ucfirst(strtolower($m));
                        break;
                    }
                }
            }

            // =========================
            // LIMPIAR DESCRIPCIÓN
            // =========================
            $descripcion = trim(preg_replace('/\s+/', ' ', $descripcionRaw));

            // =========================
            // COLUMNA C - EXISTENCIA (STOCK)
            // Limpia valores como 20+, 10 uds
            // =========================
            $stockRaw = trim($row[2] ?? '');
            $stockLimpio = preg_replace('/[^0-9]/', '', $stockRaw);
            $stock = $stockLimpio !== '' ? (int) $stockLimpio : 0;

            // =========================
            // COLUMNA D - PRECIO LISTA (COSTO)
            // =========================
            $costo = is_numeric($row[3] ?? null) ? (float) $row[3] : 0;

            // ❌ No crear SKU nuevo sin costo
            if ($costo <= 0) continue;

            // =========================
            // COLUMNA E - PROMOCIÓN (PRECIO ML)
            // =========================
            $precioML = is_numeric($row[4] ?? null) ? (float) $row[4] : null;

            // =========================
            // TITLE FAMILY
            // =========================
            $titleFamily = $marca . ' ' . $medida;

            // =========================
            // MLM (si no existe, null)
            // =========================
            $mlm = trim($row[5] ?? '') ?: null;

            // =========================
            // BUSCAR LLANTA EXISTENTE
            // =========================
            $llanta = Llanta::where('sku', $sku)->first();

            if ($llanta) {

                // =========================
                // SKU EXISTENTE → SOLO STOCK
                // =========================
                $llanta->update([
                    'stock' => max(0, $stock),
                ]);

            } else {

                // =========================
                // SKU NUEVO → CREAR COMPLETO
                // =========================
                $llanta = Llanta::create([
                    'sku'               => $sku,
                    'descripcion'       => $descripcion,
                    'marca'             => $marca,
                    'medida'            => $medida,
                    'stock'             => max(0, $stock),
                    'costo'             => $costo,
                    'precio_ML'         => $precioML,
                    'title_familyname'  => $titleFamily,
                    'MLM'               => $mlm,
                ]);
            }

            // =========================
            // SINCRONIZAR COMPUESTOS
            // =========================
            $this->syncPaquetes($llanta);
        }
    }

    /**
     * =========================
     * SINCRONIZA PAR Y JUEGO DE 4
     * (SOLO ACTUALIZA STOCK)
     * =========================
     */
    private function syncPaquetes(Llanta $llanta)
    {
        // PAR
        ProductoCompuesto::updateOrCreate(
            [
                'llanta_id' => $llanta->id,
                'tipo'      => 'par',
            ],
            [
                'piezas' => 2,
                'sku'    => $llanta->sku . '-2',
                'stock'  => floor($llanta->stock / 2),
            ]
        );

        // JUEGO DE 4
        ProductoCompuesto::updateOrCreate(
            [
                'llanta_id' => $llanta->id,
                'tipo'      => 'juego4',
            ],
            [
                'piezas' => 4,
                'sku'    => $llanta->sku . '-4',
                'stock'  => floor($llanta->stock / 4),
            ]
        );
    }
}
