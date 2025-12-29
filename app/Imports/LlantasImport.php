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

            // ======================================
            // COLUMNA A → SKU
            // ======================================
            $sku = trim($row[0] ?? '');

            // 🔥 SALTAR FILAS QUE NO SON PRODUCTOS
            if (
                $sku === '' ||
                strtolower($sku) === 'codigo' ||
                strlen($sku) < 4
            ) {
                continue;
            }

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

            // ======================================
            // LIMPIAR DESCRIPCIÓN
            // ======================================
            $descripcion = trim(preg_replace('/\s+/', ' ', $descripcionRaw));

            // ======================================
            // COLUMNA C → EXISTENCIA
            // ======================================
            $stockRaw = trim($row[2] ?? '');
            $stock = (int) preg_replace('/[^0-9]/', '', $stockRaw);

            // ======================================
            // COLUMNA D → COSTO
            // (elimina símbolos $ y comas)
            // ======================================
            $costoRaw = str_replace(['$', ','], '', $row[3] ?? '');
            $costo = is_numeric($costoRaw) ? (float) $costoRaw : 0;
            if ($costo <= 0) continue;

            // ======================================
            // COLUMNA E → PRECIO ML
            // ======================================
            $precioRaw = str_replace(['$', ','], '', $row[4] ?? '');
            $precioML = is_numeric($precioRaw) ? (float) $precioRaw : null;

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

    private function syncPaquetes(Llanta $llanta)
    {
        // PAR
        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'par'],
            [
                'sku'   => $llanta->sku . '-2',
                'stock' => intdiv($llanta->stock, 2),
                'costo' => $llanta->costo * 2,
            ]
        );

        // JUEGO DE 4
        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'juego4'],
            [
                'sku'   => $llanta->sku . '-4',
                'stock' => intdiv($llanta->stock, 4),
                'costo' => $llanta->costo * 4,
            ]
        );
    }
}
