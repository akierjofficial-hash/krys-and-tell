<?php

namespace App\Http\Controllers\Staff;

use App\Exports\InstallmentPaymentsTemplateExport;
use App\Exports\InstallmentPlansTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\InstallmentPaymentsImport;
use App\Imports\InstallmentPlansImport;
use App\Models\InstallmentPlan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InstallmentImportExportController extends Controller
{
    public function plansTemplate()
    {
        return Excel::download(new InstallmentPlansTemplateExport, 'installment_plans_template.xlsx');
    }

    public function importPlans(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new InstallmentPlansImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Installment plan import failed. Please check template and formats.');
        }

        $msg = "Installment plans import finished: {$import->created} created, {$import->skipped} skipped.";

        return !empty($import->errors)
            ? back()->with('success', $msg)->with('import_warnings', array_slice($import->errors, 0, 200))
            : back()->with('success', $msg);
    }

    public function paymentsTemplate(InstallmentPlan $plan)
    {
        $name = "installment_payments_plan_{$plan->id}_template.xlsx";
        return Excel::download(new InstallmentPaymentsTemplateExport($plan), $name);
    }

    public function importPayments(Request $request, InstallmentPlan $plan)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new InstallmentPaymentsImport($plan);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Installment payments import failed. Please check template and formats.');
        }

        $msg = "Payments import finished: {$import->created} created, {$import->updated} updated, {$import->skipped} skipped.";

        return !empty($import->errors)
            ? back()->with('success', $msg)->with('import_warnings', array_slice($import->errors, 0, 200))
            : back()->with('success', $msg);
    }
}
