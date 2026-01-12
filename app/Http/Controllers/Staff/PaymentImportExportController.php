<?php

namespace App\Http\Controllers\Staff;

use App\Exports\CashPaymentsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\CashPaymentsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PaymentImportExportController extends Controller
{
    public function templateCash()
    {
        return Excel::download(new CashPaymentsTemplateExport, 'cash_payments_template.xlsx');
    }

    public function importCash(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new CashPaymentsImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('staff.payments.index')
                ->with('error', 'Import failed. Check your template + date formats (mm/dd/yy, mm/dd/yyyy, yyyy-mm-dd, or Excel date).');
        }

        $msg = "Imported {$import->inserted} cash payment(s).";
        if ($import->skipped > 0) $msg .= " Skipped {$import->skipped} row(s).";

        $redirect = redirect()->route('staff.payments.index', ['tab' => 'cash'])->with('success', $msg);

        if (!empty($import->errors)) {
            $redirect->with('import_warnings', array_slice($import->errors, 0, 12));
        }

        return $redirect;
    }
}
