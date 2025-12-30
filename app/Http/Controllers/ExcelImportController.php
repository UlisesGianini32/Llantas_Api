<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LlantasImport;

class ExcelImportController extends Controller
{
    /**
     * Mostrar vista de importación
     */
    public function vista()
    {
        return view('excel.importar');
    }

    /**
     * Procesar archivo Excel
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new LlantasImport, $request->file('archivo'));

            return redirect()
                ->route('excel.vista')
                ->with('success', 'Archivo importado correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('excel.vista')
                ->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }
}
