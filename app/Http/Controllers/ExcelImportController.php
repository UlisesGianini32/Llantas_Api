<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\LlantasImport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportController extends Controller
{
    public function importar(Request $request)
{
    return redirect()->route('dashboard');
}

}
