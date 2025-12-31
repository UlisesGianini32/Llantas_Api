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
         * El Excel viene con encabezados "bonitos".
         * Buscamos la fila que tenga "codigo" y desde ahí empezamos.
         */
        while ($rows->count() > 0) {
            $first = $rows->first();
            $v0 = isset($first[0]) ? trim(mb_strtolower((string)$first[0])) : '';
            if ($v0 === 'codigo' || $v0 === 'código') {
                break;
            }
            $rows->shift();
        }

        // quitamos la fila de encabezado real
        $rows->shift();

        foreach ($rows as $row) {

            // =========================
            // COLUMNAS FIJAS
            // =========================
            $sku   = trim((string)($row[0] ?? ''));
            $desc  = trim((string)($row[1] ?? ''));
            $stock = intval($row[2] ?? 0);
            $costo = floatval($row[3] ?? 0);

            if ($sku === '') {
                continue;
            }

            // =========================
            // EXTRAER MARCA Y MEDIDA
            // =========================
            [$marca, $medida] = $this->extraerMarcaMedida($desc);
            $tituloDefault = trim($marca . ' ' . $medida);

            $llanta = Llanta::where('sku', $sku)->first();

            // =========================
            // SKU EXISTENTE
            // =========================
            if ($llanta) {

                // ¿precio manual?
                $precioAutoViejo = floatval($llanta->costo) * 1.5;
                $precioActual   = $llanta->precio_ML;
                $precioEsManual = !is_null($precioActual)
                    && abs($precioActual - $precioAutoViejo) > 0.01;

                // SOLO actualizamos costo y stock
                $llanta->update([
                    'stock' => $stock,
                    'costo' => $costo,
                ]);

                // Recalcular precio solo si no era manual
                if (!$precioEsManual) {
                    $llanta->update([
                        'precio_ML' => $costo * 1.5,
                    ]);
                }

                // Completar datos solo si están vacíos
                if (!$llanta->descripcion && $desc) {
                    $llanta->update(['descripcion' => $desc]);
                }
                if (!$llanta->marca) {
                    $llanta->update(['marca' => $marca]);
                }
                if (!$llanta->medida) {
                    $llanta->update(['medida' => $medida]);
                }
                if (!$llanta->title_familyname) {
                    $llanta->update(['title_familyname' => $tituloDefault]);
                }

                $this->sincronizarCompuestos($llanta);
                continue;
            }

            // =========================
            // SKU NUEVO
            // =========================
            $llanta = Llanta::create([
                'sku'              => $sku,
                'marca'            => $marca ?: 'GENERICA',
                'medida'           => $medida ?: 'N/A',
                'descripcion'      => $desc ?: 'SIN DESCRIPCIÓN',
                'costo'            => $costo,
                'stock'            => $stock,
                'precio_ML'        => $costo * 1.5,
                'title_familyname' => $tituloDefault,
                'MLM'              => null,
            ]);

            $this->sincronizarCompuestos($llanta);
        }
    }

    // =========================
    // COMPUESTOS
    // =========================
    private function sincronizarCompuestos(Llanta $llanta): void
    {
        // -------- PAR --------
        if ($llanta->stock >= 2) {

            $comp = ProductoCompuesto::where('llanta_id', $llanta->id)
                ->where('tipo', 'par')
                ->first();

            $precioAuto = ($llanta->costo * 2) * 1.4;
            $precioManual = $comp && !is_null($comp->precio_ML)
                && abs($comp->precio_ML - $precioAuto) > 0.01;

            $data = [
                'sku'              => $llanta->sku . '-2',
                'stock'            => 2,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 2,
            ];

            if (!$precioManual) {
                $data['precio_ML'] = $precioAuto;
            }

            ProductoCompuesto::updateOrCreate(
                ['llanta_id' => $llanta->id, 'tipo' => 'par'],
                $data
            );
        }

        // -------- JUEGO 4 --------
        if ($llanta->stock >= 4) {

            $comp = ProductoCompuesto::where('llanta_id', $llanta->id)
                ->where('tipo', 'juego4')
                ->first();

            $precioAuto = ($llanta->costo * 4) * 1.35;
            $precioManual = $comp && !is_null($comp->precio_ML)
                && abs($comp->precio_ML - $precioAuto) > 0.01;

            $data = [
                'sku'              => $llanta->sku . '-4',
                'stock'            => 4,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 4,
            ];

            if (!$precioManual) {
                $data['precio_ML'] = $precioAuto;
            }

            ProductoCompuesto::updateOrCreate(
                ['llanta_id' => $llanta->id, 'tipo' => 'juego4'],
                $data
            );
        }
    }

    // =========================
    // PARSEO DESCRIPCIÓN
    // =========================
    private function extraerMarcaMedida(string $desc): array
    {
        $desc = strtoupper($desc);

        // MEDIDA
        preg_match('/\d{3}\/\d{2}R\d{2}|\d{2}-\d{2}\.?\d?/', $desc, $m);
        $medida = $m[0] ?? 'N/A';

        $marcas = [
            'NEXEN','COOPER','HAIDA','MAXTREK',
            'GLADIATOR','MICHELIN','PIRELLI',
            'GOODYEAR','CONTINENTAL','BRIDGESTONE'
        ];

        $marca = 'GENERICA';
        foreach ($marcas as $m) {
            if (str_contains($desc, $m)) {
                $marca = $m;
                break;
            }
        }

        return [$marca, $medida];
    }
}
