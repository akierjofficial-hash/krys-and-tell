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
            'return' => ['nullable', 'string'],
        ]);

        try {
            Excel::import(new PatientsImport, $request->file('file'));
        } catch (\Throwable $e) {
            return $this->ktRedirectToReturn($request, 'staff.patients.index')
                ->with('error', 'Import failed: Check Birthdate format. Allowed examples: 11/22/2008, 11/22/08, 2008-11-22, or Excel date format.');
        }

        return $this->ktRedirectToReturn($request, 'staff.patients.index')
            ->with('success', 'Patients imported successfully!');
    }
}
