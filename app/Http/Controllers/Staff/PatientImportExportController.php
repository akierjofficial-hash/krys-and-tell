<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PatientsExport;
use App\Imports\PatientsImport;

class PatientImportExportController extends Controller
{
    public function export()
    {
        return Excel::download(new PatientsExport, 'patients.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new PatientsImport, $request->file('file'));

        return redirect()->route('staff.patients.index')->with('success', 'Patients imported successfully!');
    }
}
