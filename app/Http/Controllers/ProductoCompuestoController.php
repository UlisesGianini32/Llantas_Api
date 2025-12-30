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
                'stock' => $c->stock,
                'precio_ML' => $c->precio_ML,
                'costo' => $c->costo,
                'stock_disponible' => $c->stock_disponible,
                'llanta' => [
                    'sku' => $c->llanta->sku ?? null,
                    'stock_real' => $c->llanta->stock ?? 0,
                ],
            ];
        });
    }

    public function update(Request $request, $id)
    {
        $compuesto = ProductoCompuesto::findOrFail($id);

        $request->validate([
            'precio_ML' => 'sometimes|numeric|min:0',
            'costo'     => 'sometimes|numeric|min:0',
        ]);

        $compuesto->update(
            $request->only([
                'precio_ML',
                'costo',
            ])
        );

        return response()->json([
            'message' => 'Producto compuesto actualizado correctamente',
            'data' => [
                'id' => $compuesto->id,
                'sku' => $compuesto->sku,
                'precio_ML' => $compuesto->precio_ML,
                'costo' => $compuesto->costo,
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

        // 🔍 BÚSQUEDA POR SKU
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

    /**
     * 🔥 ACTUALIZADO:
     * - Edita precio del compuesto
     * - Edita marca, medida, descripcion y título
     * - ❌ NO toca stock
     */
    public function updateWeb(Request $request, $id)
    {
        $compuesto = ProductoCompuesto::with('llanta')->findOrFail($id);

        $request->validate([
            'precio_ML'        => 'required|numeric|min:0',
            'marca'            => 'required|string|max:255',
            'medida'           => 'required|string|max:255',
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'required|string|max:255',
        ]);

        // 👉 Actualiza compuesto
        $compuesto->update([
            'precio_ML'        => $request->precio_ML,
            'descripcion'      => $request->descripcion,
            'title_familyname' => $request->title_familyname,
        ]);

        // 👉 Actualiza llanta base (SIN tocar stock)
        if ($compuesto->llanta) {
            $compuesto->llanta->update([
                'marca'            => $request->marca,
                'medida'           => $request->medida,
                'descripcion'      => $request->descripcion,
                'title_familyname' => $request->title_familyname,
            ]);
        }

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto compuesto actualizado correctamente');
    }
}
