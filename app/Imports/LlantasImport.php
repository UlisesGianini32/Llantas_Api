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
        // =============================
        // 1) Buscar encabezado real
        // =============================
        while ($rows->count() > 0) {
            $v = mb_strtolower(trim((string)($rows->first()[0] ?? '')));
            if ($v === 'codigo' || $v === 'código') break;
            $rows->shift();
        }

        // Si ya no hay filas, salir
        if ($rows->count() === 0) return;

        // Quitar encabezado
        $rows->shift();

        // =============================
        // 2) Recorrer filas
        // =============================
        foreach ($rows as $row) {

            // Excel:
            // 0 = Codigo
            // 1 = Descripcion (de aquí sacamos marca/medida + guardamos descripcion)
            // 2 = Existencia (stock)
            // 3 = Precio lista (costo)
            $sku   = trim((string)($row[0] ?? ''));
            $desc  = trim((string)($row[1] ?? ''));
            $stock = intval($row[2] ?? 0);
            $costo = floatval($row[3] ?? 0);

            if ($sku === '') continue;

            [$marca, $medida] = $this->parseDescripcion($desc);

            // =============================
            // 3) Crear o actualizar llanta
            // =============================
            $llanta = Llanta::where('sku', $sku)->first();

            if ($llanta) {

                // Detectar si el precio fue editado manualmente
                // (si se separa del auto calculado anterior, lo respetamos)
                $precioAutoViejo = (float) $llanta->costo * 1.5;
                $precioActual    = $llanta->precio_ML;

                $precioManual = !is_null($precioActual)
                    && abs((float)$precioActual - (float)$precioAutoViejo) > 0.01;

                // Actualizar datos del excel
                $llanta->update([
                    'stock' => $stock,
                    'costo' => $costo,
                ]);

                // Si NO era manual, recalcular precio llanta sola
                if (!$precioManual) {
                    $llanta->update([
                        'precio_ML' => $costo * 1.5,
                    ]);
                }

                // Asegurar texto (siempre puedes cambiar esto si NO quieres que se sobrescriba)
                $llanta->update([
                    'marca'            => $marca ?: ($llanta->marca ?? 'GENERICA'),
                    'medida'           => $medida ?: ($llanta->medida ?? 'N/A'),
                    'descripcion'      => $desc ?: ($llanta->descripcion ?? 'SIN DESCRIPCIÓN'),
                    'title_familyname' => trim(($marca ?: 'GENERICA') . ' ' . ($medida ?: 'N/A')),
                ]);

            } else {

                $llanta = Llanta::create([
                    'sku'              => $sku,
                    'marca'            => $marca ?: 'GENERICA',
                    'medida'           => $medida ?: 'N/A',
                    'descripcion'      => $desc ?: 'SIN DESCRIPCIÓN',
                    'costo'            => $costo,
                    'stock'            => $stock,

                    // Fórmula llanta sola
                    'precio_ML'        => $costo * 1.5,

                    'title_familyname' => trim(($marca ?: 'GENERICA') . ' ' . ($medida ?: 'N/A')),
                    'MLM'              => null,
                ]);
            }

            // =============================
            // 4) SIEMPRE crear/actualizar compuestos
            // =============================
            $this->syncCompuestosSiempre($llanta);
        }
    }

    /**
     * ✅ SIEMPRE crea PAR y JUEGO4 aunque stock sea 0 o 1
     * ✅ Respeta precio manual de compuestos (si ya lo editaste)
     * ✅ NO TOCA MLM
     */
    private function syncCompuestosSiempre(Llanta $llanta): void
    {
        // =============================
        // PAR (2)
        // =============================
        $compPar = ProductoCompuesto::where('llanta_id', $llanta->id)
            ->where('tipo', 'par')
            ->first();

        $precioAutoPar = ((float)$llanta->costo * 2) * 1.4;

        $precioParManual = $compPar && !is_null($compPar->precio_ML)
            && abs((float)$compPar->precio_ML - (float)$precioAutoPar) > 0.01;

        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'par'],
            [
                'sku'              => $llanta->sku . '-2',
                'stock'            => 2,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => (float)$llanta->costo * 2,

                // si fue manual, respetar; si no, calcular
                'precio_ML'        => $precioParManual
                    ? (float)$compPar->precio_ML
                    : (float)$precioAutoPar,

                // ❗ MLM NO SE TOCA
            ]
        );

        // =============================
        // JUEGO4 (4)
        // =============================
        $comp4 = ProductoCompuesto::where('llanta_id', $llanta->id)
            ->where('tipo', 'juego4')
            ->first();

        $precioAuto4 = ((float)$llanta->costo * 4) * 1.35;

        $precio4Manual = $comp4 && !is_null($comp4->precio_ML)
            && abs((float)$comp4->precio_ML - (float)$precioAuto4) > 0.01;

        ProductoCompuesto::updateOrCreate(
            ['llanta_id' => $llanta->id, 'tipo' => 'juego4'],
            [
                'sku'              => $llanta->sku . '-4',
                'stock'            => 4,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => (float)$llanta->costo * 4,

                'precio_ML'        => $precio4Manual
                    ? (float)$comp4->precio_ML
                    : (float)$precioAuto4,

                // ❗ MLM NO SE TOCA
            ]
        );
    }

    /**
     * Parser básico: saca medida y marca de la descripción
     */
    private function parseDescripcion(string $desc): array
    {
        $desc = strtoupper(trim($desc));

        // Medida típica: 205/55R16, 235/55R18, etc
        preg_match('/\d{3}\/\d{2}R\d{2}/', $desc, $m);
        $medida = $m[0] ?? 'N/A';

        $marcas = [
            'NEXEN','COOPER','HAIDA','MAXTREK','GLADIATOR','MICHELIN','PIRELLI',
            'GOODYEAR','CONTINENTAL','BRIDGESTONE','ATLAS'
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
