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
            if ($sku === '' || strtolower($sku) === 'codigo') {
                continue;
            }

            $descripcion = trim($row[1] ?? '');
            if ($descripcion === '') continue;

            preg_match('/(\d{2,3}\/\d{2,3}[Rr]?\d{2,3})/', $descripcion, $m);
            $medida = $m[0] ?? 'N/A';

            $marca = 'GENERICA';
            foreach (['MICHELIN','CONTINENTAL','PIRELLI','BRIDGESTONE','GOODYEAR','FIRESTONE'] as $mrc) {
                if (stripos($descripcion, $mrc) !== false) {
                    $marca = ucfirst(strtolower($mrc));
                    break;
                }
            }

            $stock = (int) preg_replace('/[^0-9]/', '', $row[2] ?? 0);

            $costo = (float) str_replace(['$', ','], '', $row[3] ?? 0);
            $precioML = (float) str_replace(['$', ','], '', $row[4] ?? 0);

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
                ]
            );

            $this->syncCompuestos($llanta);
        }
    }

    private function syncCompuestos(Llanta $llanta)
    {
        $llanta->compuestos()->delete();

        if ($llanta->stock < 2) return;

        ProductoCompuesto::create([
            'llanta_id'        => $llanta->id,
            'sku'              => $llanta->sku . '-2',
            'tipo'             => 'par',
            'stock'            => 2,
            'descripcion'      => $llanta->descripcion,
            'title_familyname' => $llanta->title_familyname,
            'costo'            => $llanta->costo * 2,
            'precio_ML'        => $llanta->precio_ML * 2,
        ]);

        if ($llanta->stock >= 4) {
            ProductoCompuesto::create([
                'llanta_id'        => $llanta->id,
                'sku'              => $llanta->sku . '-4',
                'tipo'             => 'juego4',
                'stock'            => 4,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 4,
                'precio_ML'        => $llanta->precio_ML * 4,
            ]);
        }
    }
}
