<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\LlantasImport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportController extends Controller
{
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new LlantasImport, $request->file('archivo'));

        return redirect()
            ->route('dashboard')
            ->with('success', 'Excel importado correctamente');
    }
}
