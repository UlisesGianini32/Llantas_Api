<?php

namespace App\Imports;

use App\Models\Llanta;
use App\Models\ProductoCompuesto;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LlantasImport implements ToCollection, WithHeadingRow
{
    /**
     * Aquí guardamos los SKUs que SÍ vienen en el Excel
     */
    protected array $skusEnExcel = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            // =============================
            // 1️⃣ LEER Y LIMPIAR DATOS
            // =============================
            $skuRaw = $row['codigo'] ?? null;
            if (!$skuRaw) {
                continue;
            }

            $sku = $this->limpiarSku($skuRaw);

            $descripcion = trim((string)($row['descripcion'] ?? ''));
            $stock = $this->limpiarNumero($row['existencia'] ?? 0);
            $costo = $this->limpiarNumero($row['precio lista'] ?? 0);

            if ($sku === '') {
                continue;
            }

            // Guardamos que este SKU sí vino en el Excel
            $this->skusEnExcel[] = $sku;

            [$marca, $medida] = $this->parseDescripcion($descripcion);

            // =============================
            // 2️⃣ CREAR O ACTUALIZAR LLANTA
            // =============================
            $llanta = Llanta::where('sku', $sku)->first();

            if ($llanta) {

                // detectar si el precio fue editado manualmente
                $precioAutoViejo = $llanta->costo * 1.5;
                $precioActual   = $llanta->precio_ML;

                $precioEsManual = !is_null($precioActual)
                    && abs($precioActual - $precioAutoViejo) > 0.01;

                $llanta->update([
                    'stock' => $stock,
                    'costo' => $costo,
                ]);

                if (!$precioEsManual) {
                    $llanta->update([
                        'precio_ML' => $costo * 1.5,
                    ]);
                }

            } else {

                $llanta = Llanta::create([
                    'sku'              => $sku,
                    'marca'            => $marca,
                    'medida'           => $medida,
                    'descripcion'      => $descripcion,
                    'stock'            => $stock,
                    'costo'            => $costo,
                    'precio_ML'        => $costo * 1.5, // fórmula intacta
                    'title_familyname' => trim("$marca $medida"),
                    'MLM'              => null,
                ]);
            }

            // =============================
            // 3️⃣ SIEMPRE SINCRONIZAR COMPUESTOS
            // =============================
            $this->syncCompuestos($llanta);
        }

        // =============================
        // 4️⃣ SKUS QUE NO VINIERON → STOCK 0
        // =============================
        Llanta::whereNotIn('sku', $this->skusEnExcel)
            ->update(['stock' => 0]);
    }

    // ======================================================
    // 🔁 COMPUESTOS (SIEMPRE SE CREAN)
    // ======================================================
    private function syncCompuestos(Llanta $llanta): void
    {
        // -------- PAR --------
        $this->crearOActualizarCompuesto(
            $llanta,
            'par',
            2,
            1.4
        );

        // -------- JUEGO DE 4 --------
        $this->crearOActualizarCompuesto(
            $llanta,
            'juego4',
            4,
            1.35
        );
    }

    private function crearOActualizarCompuesto(
        Llanta $llanta,
        string $tipo,
        int $cantidad,
        float $factor
    ): void {
        $comp = ProductoCompuesto::where('llanta_id', $llanta->id)
            ->where('tipo', $tipo)
            ->first();

        $precioAuto = ($llanta->costo * $cantidad) * $factor;

        $precioEsManual = $comp && !is_null($comp->precio_ML)
            && abs($comp->precio_ML - $precioAuto) > 0.01;

        ProductoCompuesto::updateOrCreate(
            [
                'llanta_id' => $llanta->id,
                'tipo'      => $tipo,
            ],
            [
                'sku'              => $llanta->sku . '-' . $cantidad,
                'stock'            => $cantidad,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * $cantidad,
                'precio_ML'        => $precioEsManual
                    ? $comp->precio_ML
                    : $precioAuto,
                // ❗ MLM jamás se toca
            ]
        );
    }

    // ======================================================
    // 🧼 HELPERS
    // ======================================================
    private function limpiarSku(string $sku): string
    {
        return strtoupper(trim(preg_replace('/\s+/u', '', $sku)));
    }

    private function limpiarNumero($valor): float
    {
        return (float) preg_replace('/[^0-9.]/', '', (string)$valor);
    }

    private function parseDescripcion(string $desc): array
    {
        $desc = strtoupper($desc);

        preg_match('/\d{3}\/\d{2}R\d{2}|\d{2}-\d{2}\.?\d?/', $desc, $m);
        $medida = $m[0] ?? 'N/A';

        $marcas = [
            'NEXEN','COOPER','HAIDA','MAXTREK','GLADIATOR',
            'MICHELIN','PIRELLI','GOODYEAR','CONTINENTAL','BRIDGESTONE'
        ];

        $marca = 'GENERICA';
        foreach ($marcas as $b) {
            if (Str::contains($desc, $b)) {
                $marca = $b;
                break;
            }
        }

        return [$marca, $medida];
    }
}
