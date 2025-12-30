<?php

namespace App\Http\Controllers;

use App\Models\ProductoCompuesto;
use Illuminate\Http\Request;

class ProductoCompuestoController extends Controller
{
    /* ===========================
     | API
     ===========================*/

    public function index()
    {
        return ProductoCompuesto::with('llanta')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'sku' => $c->sku,
                'tipo' => $c->tipo,
                'consumo' => $c->stock,
                'stock_disponible' => $c->stock_disponible,
                'precio_ml' => $c->precio_ml_calculado,
                'costo' => $c->costo_calculado,
                'titulo' => $c->titulo_real,
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
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'nullable|string|max:255',
            'MLM'              => 'nullable|string|max:255',
        ]);

        $compuesto->update($request->only([
            'descripcion',
            'title_familyname',
            'MLM',
        ]));

        return response()->json(['ok' => true]);
    }

    /* ===========================
     | WEB
     ===========================*/

    public function indexWeb(Request $request)
    {
        $query = ProductoCompuesto::with('llanta')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $query->where('sku', 'like', "%{$request->search}%");
        }

        $compuestos = $query->paginate(15)->withQueryString();

        return view('compuestos.index', compact('compuestos'));
    }

    public function editWeb($id)
    {
        return view(
            'compuestos.edit',
            ['compuesto' => ProductoCompuesto::with('llanta')->findOrFail($id)]
        );
    }

    public function updateWeb(Request $request, $id)
    {
        $compuesto = ProductoCompuesto::findOrFail($id);

        $request->validate([
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'required|string|max:255',
            'MLM'              => 'nullable|string|max:255',
        ]);

        $compuesto->update($request->only([
            'descripcion',
            'title_familyname',
            'MLM',
        ]));

        return redirect()->route('productos.index')
            ->with('success', 'Producto compuesto actualizado');
    }
}
