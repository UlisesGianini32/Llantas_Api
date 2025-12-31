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
        /**
         * Saltar filas hasta encontrar encabezado real
         */
        while ($rows->count() > 0) {
            $first = $rows->first();
            $v0 = isset($first[0]) ? trim(mb_strtolower((string)$first[0])) : '';
            if ($v0 === 'codigo' || $v0 === 'código') {
                break;
            }
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

            // =========================
            // EXTRAER MARCA Y MEDIDA
            // =========================
            [$marca, $medida] = $this->extraerMarcaMedida($desc);

            // =========================
            // 🔥 BORRAR TODO DEL SKU
            // =========================
            $llantaVieja = Llanta::where('sku', $sku)->first();
            if ($llantaVieja) {
                ProductoCompuesto::where('llanta_id', $llantaVieja->id)->delete();
                $llantaVieja->delete();
            }

            // =========================
            // CREAR LLANTA LIMPIA
            // =========================
            $llanta = Llanta::create([
                'sku'              => $sku,
                'marca'            => $marca,
                'medida'           => $medida,
                'descripcion'      => $desc,
                'costo'            => $costo,
                'stock'            => $stock,
                'precio_ML'        => $costo * 1.5,
                'title_familyname' => trim("$marca $medida"),
                'MLM'              => null,
            ]);

            // =========================
            // PAR
            // =========================
            if ($stock >= 2) {
                ProductoCompuesto::create([
                    'llanta_id'        => $llanta->id,
                    'sku'              => $sku . '-2',
                    'tipo'             => 'par',
                    'stock'            => 2,
                    'descripcion'      => $desc,
                    'title_familyname' => trim("$marca $medida"),
                    'costo'            => $costo * 2,
                    'precio_ML'        => ($costo * 2) * 1.4,
                    'MLM'              => null,
                ]);
            }

            // =========================
            // JUEGO DE 4
            // =========================
            if ($stock >= 4) {
                ProductoCompuesto::create([
                    'llanta_id'        => $llanta->id,
                    'sku'              => $sku . '-4',
                    'tipo'             => 'juego4',
                    'stock'            => 4,
                    'descripcion'      => $desc,
                    'title_familyname' => trim("$marca $medida"),
                    'costo'            => $costo * 4,
                    'precio_ML'        => ($costo * 4) * 1.35,
                    'MLM'              => null,
                ]);
            }
        }
    }

    // =========================
    // PARSEO SIMPLE
    // =========================
    private function extraerMarcaMedida(string $desc): array
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
