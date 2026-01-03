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
        // Buscar encabezado real
        while ($rows->count() > 0) {
            $v = mb_strtolower(trim((string)($rows->first()[0] ?? '')));
            if ($v === 'codigo' || $v === 'código') break;
            $rows->shift();
        }

        // Quitar encabezado
        $rows->shift();

        foreach ($rows as $row) {

            $sku   = trim((string)($row[0] ?? ''));
            $desc  = trim((string)($row[1] ?? ''));
            $stock = intval($row[2] ?? 0);
            $costo = floatval($row[3] ?? 0);

            if ($sku === '') continue;

            [$marca, $medida] = $this->parseDescripcion($desc);

            $llanta = Llanta::where('sku', $sku)->first();

            // =============================
            // SKU EXISTENTE
            // =============================
            if ($llanta) {

                // detectar precio manual
                $precioAutoViejo = $llanta->costo * 1.5;
                $precioActual    = $llanta->precio_ML;

                $precioManual = !is_null($precioActual)
                    && abs($precioActual - $precioAutoViejo) > 0.01;

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

            // =============================
            // SKU NUEVO
            // =============================
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

    private function syncCompuestos(Llanta $llanta): void
    {
        /*
        SIEMPRE crear compuestos,
        sin importar stock real.
        NO tocar MLM.
        */

        // =============================
        // PAR (2)
        // =============================
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
                'precio_ML'        => $precioManual
                    ? $comp->precio_ML
                    : $precioAuto,
            ]
        );

        // =============================
        // JUEGO DE 4
        // =============================
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
                'precio_ML'        => $precioManual
                    ? $comp->precio_ML
                    : $precioAuto,
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