<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\LlantasImport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportController extends Controller
{
    public function importar(Request $request)
{
    dd('SI ENTRE AL IMPORT'); // 👈 prueba dura
}

}
