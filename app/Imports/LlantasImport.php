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

            $sku = trim($row[0] ?? '');
            if ($sku === '' || strtolower($sku) === 'codigo' || strlen($sku) < 4) {
                continue;
            }

            $descripcionRaw = trim($row[1] ?? '');
            if ($descripcionRaw === '') continue;

            preg_match('/(\d{2,3}\/\d{2,3}[Rr]?\d{2,3})/', $descripcionRaw, $m);
            $medida = $m[0] ?? 'N/A';

            $marca = 'GENERICA';
            foreach ([
                'MICHELIN','CONTINENTAL','PIRELLI','BRIDGESTONE','GOODYEAR',
                'YOKOHAMA','TOYO','HANKOOK','FIRESTONE'
            ] as $mrc) {
                if (stripos($descripcionRaw, $mrc) !== false) {
                    $marca = ucfirst(strtolower($mrc));
                    break;
                }
            }

            $descripcion = trim(preg_replace('/\s+/', ' ', $descripcionRaw));

            $stock = (int) preg_replace('/[^0-9]/', '', $row[2] ?? 0);

            $costoRaw = str_replace(['$', ','], '', $row[3] ?? '');
            $costo = is_numeric($costoRaw) ? (float) $costoRaw : 0;
            if ($costo <= 0) continue;

            $precioRaw = str_replace(['$', ','], '', $row[4] ?? '');
            $precioML = is_numeric($precioRaw) ? (float) $precioRaw : null;

            $titleFamily = "{$marca} {$medida}";

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

            $this->syncCompuestos($llanta);
        }
    }

    private function syncCompuestos(Llanta $llanta)
    {
        $llanta->compuestos()->delete();

        if ($llanta->stock < 2) {
            return;
        }

        ProductoCompuesto::create([
            'llanta_id'        => $llanta->id,
            'tipo'             => 'par',
            'stock'            => 2,
            'title_familyname' => $llanta->title_familyname,
        ]);

        if ($llanta->stock >= 4) {
            ProductoCompuesto::create([
                'llanta_id'        => $llanta->id,
                'tipo'             => 'juego4',
                'stock'            => 4,
                'title_familyname' => $llanta->title_familyname,
            ]);
        }
    }
}
