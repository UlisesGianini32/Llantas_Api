<?php

namespace App\Http\Controllers;

use App\Models\ProductoCompuesto;
use Illuminate\Http\Request;

class ProductoCompuestoController extends Controller
{
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
        $compuesto = ProductoCompuesto::with('llanta')->findOrFail($id);
        return view('compuestos.edit', compact('compuesto'));
    }

    public function updateWeb(Request $request, $id)
    {
        $compuesto = ProductoCompuesto::with('llanta')->findOrFail($id);

        $request->validate([
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'required|string|max:255',

            // ✅ ahora sí editables
            'costo'            => 'nullable|numeric|min:0',
            'precio_ML'        => 'nullable|numeric|min:0',
            'MLM'              => 'nullable|string|max:255',
        ]);

        $compuesto->update([
            'descripcion'      => $request->descripcion,
            'title_familyname' => $request->title_familyname,
            'costo'            => $request->costo,
            'precio_ML'        => $request->precio_ML,
            'MLM'              => $request->MLM,
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto compuesto actualizado correctamente');
    }
}
