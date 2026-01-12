<?php

namespace App\Http\Controllers\Staff;

use App\Exports\VisitsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\VisitsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VisitImportExportController extends Controller
{
    public function template()
    {
        return Excel::download(new VisitsTemplateExport(), 'visits_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new VisitsImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Visit import failed. Check the template + date formats.');
        }

        $msg = "Imported {$import->visitsCreated} visit(s) and {$import->proceduresCreated} procedure(s).";
        if ($import->skipped > 0) $msg .= " Skipped {$import->skipped} row(s).";

        $redirect = redirect()->back()->with('success', $msg);

        if (!empty($import->errors)) {
            $redirect->with('import_warnings', array_slice($import->errors, 0, 15));
        }

        return $redirect;
    }
}
