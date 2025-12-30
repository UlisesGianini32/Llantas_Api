<?php

namespace App\Http\Controllers;

use App\Models\ProductoCompuesto;
use Illuminate\Http\Request;

class ProductoCompuestoController extends Controller
{
    /* ======================================================
     | API METHODS (JSON)
     |======================================================*/

    public function index()
    {
        return ProductoCompuesto::with('llanta')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'sku' => $c->sku,
                'tipo' => $c->tipo,
                'consumo' => $c->stock, // 👈 consumo
                'precio_ML_calculado' => $c->precio_ml_calculado,
                'costo_calculado' => $c->costo_calculado,
                'stock_disponible' => $c->stock_disponible,
                'llanta' => [
                    'sku' => $c->llanta->sku ?? null,
                    'stock_real' => $c->llanta->stock ?? 0,
                    'precio_ML' => $c->llanta->precio_ML ?? 0,
                ],
            ];
        });
    }

    public function update(Request $request, $id)
    {
        $compuesto = ProductoCompuesto::findOrFail($id);

        // ✅ Ya no actualizamos costo/precio ML en compuesto (son derivados)
        $request->validate([
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'nullable|string|max:255',
            'MLM'              => 'nullable|string|max:255',
        ]);

        $compuesto->update($request->only([
            'descripcion',
            'title_familyname',
            'MLM',
        ]));

        return response()->json([
            'message' => 'Producto compuesto actualizado correctamente',
            'data' => [
                'id' => $compuesto->id,
                'sku' => $compuesto->sku,
                'precio_ML_calculado' => $compuesto->precio_ml_calculado,
                'costo_calculado' => $compuesto->costo_calculado,
                'stock_disponible' => $compuesto->stock_disponible,
            ]
        ]);
    }

    public function destroy($id)
    {
        $compuesto = ProductoCompuesto::findOrFail($id);
        $compuesto->delete();

        return response()->json([
            'message' => 'Producto compuesto eliminado correctamente'
        ]);
    }

    /* ======================================================
     | WEB METHODS (BLADE)
     |======================================================*/

    public function indexWeb(Request $request)
    {
        $query = ProductoCompuesto::with('llanta')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $query->where('sku', 'like', '%' . $request->search . '%');
        }

        $compuestos = $query->paginate(15)->withQueryString();

        return view('compuestos.index', compact('compuestos'));
    }

    public function editWeb($id)
    {
        $compuesto = ProductoCompuesto::with('llanta')->findOrFail($id);
        return view('compuestos.edit', compact('compuesto'));
    }

    public function updateWeb(Request $request, $id)
    {
        $compuesto = ProductoCompuesto::with('llanta')->findOrFail($id);

        // ✅ NO pedimos precio_ML aquí (ya es calculado)
        // ✅ marca/medida se editan desde LLANTAS, no desde compuestos
        $request->validate([
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'required|string|max:255',
            'MLM'              => 'nullable|string|max:255',
        ]);

        $compuesto->update([
            'descripcion'      => $request->descripcion,
            'title_familyname' => $request->title_familyname,
            'MLM'              => $request->MLM,
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto compuesto actualizado correctamente');
    }
}
