<?php

namespace App\Http\Controllers;

use App\Models\Llanta;
use App\Models\ProductoCompuesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        return view('dashboard', [

            // ======================
            // TOTALES (NO se filtran)
            // ======================
            'totalLlantas' => Llanta::count(),
            'totalCompuestos' => ProductoCompuesto::count(),
            'existenciasLlantas' => Llanta::sum('stock'),
            'llantasSinStock' => Llanta::where('stock', 0)->count(),

            // ======================
            // VALORES
            // ======================
            'valorInventarioLlantas' => Llanta::sum(DB::raw('costo * stock')),
            'valorInventarioCompuestos' => ProductoCompuesto::sum(
                DB::raw('costo * piezas')
            ),

            // ======================
            // LLANTAS (FILTRABLE)
            // ======================
            'llantas' => Llanta::when($search, function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%");
                })
                ->orderBy('id', 'desc')
                ->paginate(10),

            // ======================
            // PRODUCTOS COMPUESTOS (FILTRABLE)
            // ======================
            'productosCompuestos' => ProductoCompuesto::when($search, function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%");
                })
                ->orderBy('id', 'desc')
                ->paginate(10),

            // ======================
            // STOCK BAJO (FILTRABLE)
            // ======================
            'stockBajo' => Llanta::where('stock', '<=', 4)
                ->when($search, function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%");
                })
                ->select(
                    'sku',
                    'marca',
                    'medida',
                    'descripcion',
                    'costo',
                    'precio_ML',
                    'title_familyname',
                    'MLM',
                    'stock'
                )
                ->orderBy('stock', 'asc')
                ->paginate(10),
        ]);
    }

    public function stats()
    {
        return response()->json([
            'totales' => [
                'llantas' => Llanta::count(),
                'compuestos' => ProductoCompuesto::count(),
                'existencias_llantas' => Llanta::sum('stock'),
            ],
            'valores' => [
                'llantas' => Llanta::sum(DB::raw('costo * stock')),
                'pares' => ProductoCompuesto::where('tipo', 'par')
                    ->sum(DB::raw('costo * piezas')),
                'juego4' => ProductoCompuesto::where('tipo', 'juego4')
                    ->sum(DB::raw('costo * piezas')),
            ],
        ]);
    }
}
