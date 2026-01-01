<?php

namespace App\Http\Controllers;

use App\Models\Llanta;
use App\Models\ProductoCompuesto;
use Illuminate\Http\Request;

class LlantaController extends Controller
{
    /* ===========================
     | API
     |===========================*/

    public function index()
    {
        return Llanta::with('compuestos')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku'    => 'required|unique:llantas',
            'marca'  => 'required|string',
            'medida' => 'required|string',
            'costo'  => 'required|numeric|min:1',
            'stock'  => 'required|integer|min:0',
        ]);

        $llanta = Llanta::create([
            'sku'              => $request->sku,
            'marca'            => $request->marca,
            'medida'           => $request->medida,
            'descripcion'      => $request->descripcion ?? 'SIN DESCRIPCIÓN',
            'costo'            => $request->costo,
            'precio_ML'        => $request->precio_ML,
            'title_familyname' => $request->title_familyname ?? ($request->marca.' '.$request->medida),
            'MLM'              => $request->MLM,
            'stock'            => $request->stock,
        ]);

        $this->sincronizarCompuestos($llanta);

        return response()->json([
            'message' => 'Llanta creada correctamente',
            'data'    => $llanta->load('compuestos'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $llanta = Llanta::findOrFail($id);

        $llanta->update($request->only([
            'marca',
            'medida',
            'descripcion',
            'costo',
            'precio_ML',
            'stock',
            'MLM',
            'title_familyname',
        ]));

        $this->sincronizarCompuestos($llanta);

        return response()->json([
            'message' => 'Llanta actualizada correctamente',
            'data'    => $llanta->load('compuestos'),
        ]);
    }

    /* ===========================
     | WEB
     |===========================*/

    public function indexWeb(Request $request)
    {
        $llantas = Llanta::when($request->search, function ($q) use ($request) {
                $q->where('sku', 'like', "%{$request->search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('llantas.index', compact('llantas'));
    }

    public function editWeb($id)
    {
        $llanta = Llanta::findOrFail($id);
        return view('llantas.edit', compact('llanta'));
    }

    public function updateWeb(Request $request, $id)
    {
        $llanta = Llanta::findOrFail($id);

        $request->validate([
            'marca'            => 'required|string|max:255',
            'medida'           => 'required|string|max:255',
            'descripcion'      => 'nullable|string',
            'title_familyname' => 'required|string|max:255',
            'precio_ML'        => 'required|numeric|min:0',
            'stock'            => 'required|integer|min:0',
            'MLM'              => 'nullable|string|max:255', // ✅ NUEVO
        ]);

        $llanta->update([
            'marca'            => $request->marca,
            'medida'           => $request->medida,
            'descripcion'      => $request->descripcion,
            'title_familyname' => $request->title_familyname,
            'precio_ML'        => $request->precio_ML,
            'stock'            => $request->stock,
            'MLM'              => $request->MLM, // ✅ SE GUARDA MLM
        ]);

        // 🔄 Regenera pares y juegos (propaga MLM)
        $this->sincronizarCompuestos($llanta);

        return redirect()
            ->route('llantas.index')
            ->with('success', 'Llanta y productos compuestos actualizados');
    }


    /* ===========================
     | HELPERS
     |===========================*/

    private function sincronizarCompuestos(Llanta $llanta)
    {
        // =========================
        // PAR
        // =========================
        ProductoCompuesto::updateOrCreate(
            [
                'llanta_id' => $llanta->id,
                'tipo'      => 'par',
            ],
            [
                'sku'              => $llanta->sku . '-2',
                'stock'            => 2,
                'descripcion'      => $llanta->descripcion,
                'title_familyname' => $llanta->title_familyname,
                'costo'            => $llanta->costo * 2,
                'precio_ML'        => $llanta->precio_ML !== null
                                        ? $llanta->precio_ML * 2
                                        : null,
                // ❗ MLM NO SE TOCA
            ]
        );

        // =========================
        // JUEGO DE 4
        // =========================
        if ($llanta->stock >= 4) {
            ProductoCompuesto::updateOrCreate(
                [
                    'llanta_id' => $llanta->id,
                    'tipo'      => 'juego4',
                ],
                [
                    'sku'              => $llanta->sku . '-4',
                    'stock'            => 4,
                    'descripcion'      => $llanta->descripcion,
                    'title_familyname' => $llanta->title_familyname,
                    'costo'            => $llanta->costo * 4,
                    'precio_ML'        => $llanta->precio_ML !== null
                                            ? $llanta->precio_ML * 4
                                            : null,
                    // ❗ MLM NO SE TOCA
                ]
            );
        }
        
    }

    public function agotadasWeb(Request $request)
    {
        $llantas = Llanta::where('stock', '<=', 0)
            ->when($request->search, function ($q) use ($request) {
                $q->where('sku', 'like', "%{$request->search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('llantas.agotadas', compact('llantas'));
    }
}
