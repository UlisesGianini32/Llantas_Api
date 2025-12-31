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
        // Saltar filas vacías/encabezados "bonitos" hasta encontrar la fila real:
        // Código | Descripcion | Existencia | Precio lista | Promocion | Remate
        // En tu archivo real empieza en la fila 6.
        // Con ToCollection ya llega como array 0-based, así que aquí quitamos encabezado real:
        // Detectamos si la primera fila contiene "Código".
        while ($rows->count() > 0) {
            $first = $rows->first();
            $v0 = isset($first[0]) ? trim((string)$first[0]) : '';
            if (mb_strtolower($v0) === 'código' || mb_strtolower($v0) === 'codigo') {
                break;
            }
            $rows->shift();
        }

        // Quitar encabezado real
        $rows->shift();

        foreach ($rows as $row) {

            // Excel real:
            // 0 Código
            // 1 Descripcion
            // 2 Existencia
            // 3 Precio lista   (ESTE ES TU COSTO)
            // 4 Promocion
            // 5 Remate

            $sku         = trim((string)($row[0] ?? ''));
            $descExcel   = trim((string)($row[1] ?? ''));
            $stock       = intval($row[2] ?? 0);
            $costo       = floatval($row[3] ?? 0);

            if ($sku === '') continue;

            // Parse simple desde descripcion:
            // "235/55R18 NEXEN ...", "10-16.5-10C FORERUNNER ...", etc.
            $parts = preg_split('/\s+/', $descExcel);
            $medida = $parts[0] ?? 'N/A';
            $marca  = $parts[1] ?? 'GENERICA';

            // Título default: "MARCA MEDIDA"
            $tituloDefault = trim($marca . ' ' . $medida);

            $llanta = Llanta::where('sku', $sku)->first();

            // ==========
            // SKU EXISTE
            // ==========
            if ($llanta) {

                // ¿Precio actual es "auto" (costo viejo * 1.5)? Si sí, lo actualizamos al nuevo.
                $precioAutoViejo = floatval($llanta->costo) * 1.5;
                $precioActual    = $llanta->precio_ML;

                $dejarPrecioManual = false;
                if (!is_null($precioActual)) {
                    // Si NO es cercano al auto viejo => fue manual
                    $dejarPrecioManual = abs(floatval($precioActual) - $precioAutoViejo) > 0.01;
                }

                $llanta->update([
                    // Solo lo que sí quieres sobreescribir
                    'stock' => $stock,
                    'costo' => $costo,

                    // Marca/medida/descripcion/titulo:
                    // Yo recomiendo NO pisarlos si ya existían (para evitar perder ediciones).
                    // Si sí quieres que SIEMPRE se actualicen desde Excel, cambia esto a update normal.
                ]);

                // Si no era manual, recalculamos precio llanta sola
                if (!$dejarPrecioManual) {
                    $llanta->update([
                        'precio_ML' => $costo * 1.5,
                    ]);
                }

                // Si título está vacío, ponemos default
                if (empty($llanta->title_familyname)) {
                    $llanta->update(['title_familyname' => $tituloDefault]);
                }

                // Si descripcion vacía, ponemos la del excel
                if (empty($llanta->descripcion) && $descExcel !== '') {
                    $llanta->update(['descripcion' => $descExcel]);
                }

                // Si marca/medida vacías, ponemos parse
                if (empty($llanta->marca)) {
                    $llanta->update(['marca' => $marca]);
                }
                if (empty($llanta->medida)) {
                    $llanta->update(['medida' => $medida]);
                }

                $this->sincronizarCompuestosConFormulas($llanta);
                continue;
            }

            // ==========
            // SKU NUEVO
            // ==========
            $llanta = Llanta::create([
                'sku'              => $sku,
                'marca'            => $marca ?: 'GENERICA',
                'medida'           => $medida ?: 'N/A',
                'descripcion'      => $descExcel ?: 'SIN DESCRIPCIÓN',
                'costo'            => $costo,
                'stock'            => $stock,

                // Fórmula llanta sola
                'precio_ML'        => $costo * 1.5,

                'title_familyname' => $tituloDefault,
                'MLM'              => null,
            ]);

            $this->sincronizarCompuestosConFormulas($llanta);
        }
    }

    private function sincronizarCompuestosConFormulas(Llanta $llanta): void
    {
        // Creamos/actualizamos PAR y JUEGO4
        // OJO: no tocamos MLM (porque cada publicación tiene su MLM distinto)

        // =========================
        // PAR (2)
        // =========================
        if ($llanta->stock >= 2) {

            $comp = ProductoCompuesto::where('llanta_id', $llanta->id)
                ->where('tipo', 'par')
                ->first();

            // Detectar si precio del compuesto fue manual
            $precioAutoCompViejo = floatval($llanta->costo) * 2 * 1.4;
            $precioCompActual    = $comp?->precio_ML;

            $dejarPrecioCompManual = false;
            if ($comp && !is_null($precioCompActual)) {
                $dejarPrecioCompManual = abs(floatval($precioCompActual) - $precioAutoCompViejo) > 0.01;
            }

            $data = [
                'sku'              => $llanta->sku . '-2',
                'stock'            => 2,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,

                // costo del paquete (2 piezas)
                'costo'            => floatval($llanta->costo) * 2,
            ];

            // precio calculado solo si no era manual
            if (!$dejarPrecioCompManual) {
                $data['precio_ML'] = (floatval($llanta->costo) * 2) * 1.4;
            }

            ProductoCompuesto::updateOrCreate(
                ['llanta_id' => $llanta->id, 'tipo' => 'par'],
                $data
            );
        }

        // =========================
        // JUEGO4 (4)
        // =========================
        if ($llanta->stock >= 4) {

            $comp = ProductoCompuesto::where('llanta_id', $llanta->id)
                ->where('tipo', 'juego4')
                ->first();

            $precioAutoCompViejo = floatval($llanta->costo) * 4 * 1.35;
            $precioCompActual    = $comp?->precio_ML;

            $dejarPrecioCompManual = false;
            if ($comp && !is_null($precioCompActual)) {
                $dejarPrecioCompManual = abs(floatval($precioCompActual) - $precioAutoCompViejo) > 0.01;
            }

            $data = [
                'sku'              => $llanta->sku . '-4',
                'stock'            => 4,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => floatval($llanta->costo) * 4,
            ];

            if (!$dejarPrecioCompManual) {
                $data['precio_ML'] = (floatval($llanta->costo) * 4) * 1.35;
            }

            ProductoCompuesto::updateOrCreate(
                ['llanta_id' => $llanta->id, 'tipo' => 'juego4'],
                $data
            );
        }
    }
}
