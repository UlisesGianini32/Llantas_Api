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
        // ===============================
        // 1. PONER TODO EL STOCK EN 0
        // ===============================
        Llanta::query()->update(['stock' => 0]);

        // ===============================
        // 2. BUSCAR ENCABEZADO REAL
        // ===============================
        while ($rows->count() > 0) {
            $v = strtoupper(trim((string)($rows->first()[0] ?? '')));
            if ($v === 'CODIGO' || $v === 'CÓDIGO') break;
            $rows->shift();
        }

        // Quitar encabezado
        $rows->shift();

        // ===============================
        // 3. PROCESAR EXCEL
        // ===============================
        foreach ($rows as $row) {

            // Columnas reales del Excel
            $sku   = $this->cleanSku($row[0] ?? '');
            $desc  = trim((string)($row[1] ?? ''));
            $stock = intval($row[2] ?? 0);
            $costo = floatval($row[3] ?? 0);

            if ($sku === '') {
                continue;
            }

            [$marca, $medida] = $this->parseDescripcion($desc);

            $llanta = Llanta::where('sku', $sku)->first();

            // ===============================
            // SKU EXISTENTE
            // ===============================
            if ($llanta) {

                $precioAuto = $llanta->costo * 1.5;
                $precioManual = !is_null($llanta->precio_ML)
                    && abs($llanta->precio_ML - $precioAuto) > 0.01;

                $llanta->update([
                    'stock' => $stock,
                    'costo' => $costo,
                ]);

                if (!$precioManual) {
                    $llanta->update([
                        'precio_ML' => $costo * 1.5
                    ]);
                }

                $this->syncCompuestos($llanta);
                continue;
            }

            // ===============================
            // SKU NUEVO
            // ===============================
            $llanta = Llanta::create([
                'sku'              => $sku,
                'marca'            => $marca,
                'medida'           => $medida,
                'descripcion'      => $desc,
                'costo'            => $costo,
                'stock'            => $stock,
                'precio_ML'        => $costo * 1.5,
                'title_familyname' => "$marca $medida",
                'MLM'              => null,
            ]);

            $this->syncCompuestos($llanta);
        }
    }

    // ===============================
    // COMPUESTOS (SIEMPRE)
    // ===============================
    private function syncCompuestos(Llanta $llanta): void
    {
        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'par'],
            [
                'sku'              => $llanta->sku . '-2',
                'stock'            => 2,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 2,
                'precio_ML'        => ($llanta->costo * 2) * 1.4,
            ]
        );

        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'juego4'],
            [
                'sku'              => $llanta->sku . '-4',
                'stock'            => 4,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 4,
                'precio_ML'        => ($llanta->costo * 4) * 1.35,
            ]
        );
    }

    // ===============================
    // LIMPIAR SKU (EXCEL REAL)
    // ===============================
    private function cleanSku($value): string
    {
        return strtoupper(
            trim(
                preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', (string)$value)
            )
        );
    }

    // ===============================
    // PARSEAR DESCRIPCIÓN
    // ===============================
    private function parseDescripcion(string $desc): array
    {
        $desc = strtoupper($desc);

        preg_match('/\d{3}\/\d{2}R\d{2}|\d{2}-\d{2}\.?\d?/', $desc, $m);
        $medida = $m[0] ?? 'N/A';

        $marcas = [
            'NEXEN','COOPER','HAIDA','MAXTREK',
            'GLADIATOR','MICHELIN','PIRELLI',
            'GOODYEAR','CONTINENTAL','BRIDGESTONE'
        ];

        $marca = 'GENERICA';
        foreach ($marcas as $b) {
            if (str_contains($desc, $b)) {
                $marca = $b;
                break;
            }
        }

        return [$marca, $medida];
    }
}
