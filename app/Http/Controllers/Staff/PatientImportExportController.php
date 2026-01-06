<?php

namespace App\Http\Controllers\Staff;

use App\Exports\PatientsExport;
use App\Http\Controllers\Controller;
use App\Imports\PatientsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PatientImportExportController extends Controller
{
    public function export()
    {
        return Excel::download(new PatientsExport, 'patients.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            Excel::import(new PatientsImport, $request->file('file'));
        } catch (\Throwable $e) {
            // Optional: log for debugging in Render logs
            report($e);

            return redirect()
                ->route('staff.patients.index')
                ->with('error', 'Import failed: Birthdate must be a valid date. Preferred format is MM/DD/YY (e.g., 11/22/08) or MM/DD/YYYY (e.g., 11/22/2008). You can also use YYYY-MM-DD (e.g., 2008-11-22) or an Excel date cell.');
        }

        return redirect()
            ->route('staff.patients.index')
            ->with('success', 'Patients imported successfully!');
    }
}
