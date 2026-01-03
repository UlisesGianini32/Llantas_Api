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
        $skusEnExcel = [];

        // =========================
        // Buscar encabezado real
        // =========================
        while ($rows->count() > 0) {
            $v = mb_strtolower(trim((string) ($rows->first()[0] ?? '')));
            if ($v === 'codigo' || $v === 'código') break;
            $rows->shift();
        }

        // Quitar encabezado
        $rows->shift();

        foreach ($rows as $row) {

            // =========================
            // LIMPIEZA FUERTE DE SKU
            // =========================
            $sku = (string) ($row[0] ?? '');
            $sku = trim($sku);
            $sku = preg_replace('/\s+/', '', $sku);

            if ($sku === '') continue;

            $desc  = trim((string) ($row[1] ?? ''));
            $stock = intval($row[2] ?? 0);
            $costo = floatval($row[3] ?? 0);

            $skusEnExcel[] = $sku;

            [$marca, $medida] = $this->parseDescripcion($desc);

            // =========================
            // LLANTA (UPDATE OR CREATE)
            // =========================
            $llanta = Llanta::updateOrCreate(
                ['sku' => $sku],
                [
                    'marca'            => $marca,
                    'medida'           => $medida,
                    'descripcion'      => $desc,
                    'costo'            => $costo,
                    'stock'            => $stock,
                    'precio_ML'        => $costo * 1.5,
                    'title_familyname' => "$marca $medida",
                ]
            );

            $this->syncCompuestos($llanta);
        }

        // =========================
        // SKUs que NO vinieron → stock 0
        // =========================
        Llanta::whereNotIn('sku', array_unique($skusEnExcel))
            ->where('stock', '>', 0)
            ->update(['stock' => 0]);
    }

    /**
     * SIEMPRE crea PAR y JUEGO4
     * Respeta precio manual
     * NO toca MLM
     */
    private function syncCompuestos(Llanta $llanta): void
    {
        // =========================
        // PAR
        // =========================
        $comp = ProductoCompuesto::where('llanta_id', $llanta->id)
            ->where('tipo', 'par')
            ->first();

        $precioAuto = ($llanta->costo * 2) * 1.4;
        $precioManual = $comp && !is_null($comp->precio_ML)
            && abs($comp->precio_ML - $precioAuto) > 0.01;

        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'par'],
            [
                'sku'              => $llanta->sku . '-2',
                'stock'            => 2,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 2,
                'precio_ML'        => $precioManual ? $comp->precio_ML : $precioAuto,
            ]
        );

        // =========================
        // JUEGO4
        // =========================
        $comp = ProductoCompuesto::where('llanta_id', $llanta->id)
            ->where('tipo', 'juego4')
            ->first();

        $precioAuto = ($llanta->costo * 4) * 1.35;
        $precioManual = $comp && !is_null($comp->precio_ML)
            && abs($comp->precio_ML - $precioAuto) > 0.01;

        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'juego4'],
            [
                'sku'              => $llanta->sku . '-4',
                'stock'            => 4,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 4,
                'precio_ML'        => $precioManual ? $comp->precio_ML : $precioAuto,
            ]
        );
    }

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
