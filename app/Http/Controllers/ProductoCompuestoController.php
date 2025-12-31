<?php

namespace App\Http\Controllers;

use App\Models\ProductoCompuesto;
use Illuminate\Http\Request;

class ProductoCompuestoController extends Controller
{
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
        $compuesto = ProductoCompuesto::with('llanta')->findOrFail($id);
        return view('compuestos.edit', compact('compuesto'));
    }

    public function updateWeb(Request $request, $id)
    {
        $compuesto = ProductoCompuesto::with('llanta')->findOrFail($id);

        $request->validate([
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'required|string|max:255',
            'precio_ML'        => 'nullable|numeric|min:0',
            'MLM'              => 'nullable|string|max:255',

            // Datos de la llanta (solo informativos/edición básica)
            'marca'            => 'required|string|max:255',
            'medida'           => 'required|string|max:255',
        ]);

        /* ===========================
         | ACTUALIZAR PRODUCTO COMPUESTO
         ===========================*/
        $compuesto->update([
            'descripcion'      => $request->descripcion,
            'title_familyname' => $request->title_familyname,
            'precio_ML'        => $request->precio_ML,
            'MLM'              => $request->MLM,
        ]);

        /* ===========================
         | ACTUALIZAR LLANTA BASE
         ===========================*/
        if ($compuesto->llanta) {
            $compuesto->llanta->update([
                'marca'  => $request->marca,
                'medida' => $request->medida,
            ]);
        }

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto compuesto actualizado correctamente');
    }
}
