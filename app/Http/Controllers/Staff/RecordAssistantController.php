<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\StaffRecordAssistant;
use App\Services\StaffClinicAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class RecordAssistantController extends Controller
{
    public function patients(Request $request, StaffRecordAssistant $assistant): JsonResponse
    {
        try {
            $data = $request->validate(['q' => 'required|string|min:2|max:100']);
            return response()->json(['patients' => $assistant->patients($data['q'])])->header('Cache-Control', 'private, no-store');
        } catch (QueryException) {
            return response()->json(['message' => 'Patient records are temporarily unavailable. Check the clinic database connection.'], 503)
                ->header('Cache-Control', 'private, no-store');
        }
    }

    public function ask(Request $request, StaffRecordAssistant $assistant, StaffClinicAssistant $clinic): JsonResponse
    {
        try {
            $data = $request->validate([
                'question' => 'required|string|min:3|max:500',
                'patient_id' => 'nullable|integer|exists:patients,id',
                'scope' => 'nullable|in:clinic,patient',
            ]);
            $clinicScope = ($data['scope'] ?? null) === 'clinic';
            $intent = $clinic->intent($data['question'], !empty($data['patient_id']) && !$clinicScope);
            if ($intent) {
                if ($intent === 'visits') {
                    $dates = $request->validate([
                        'date_from' => ['required', 'date_format:Y-m-d'],
                        'date_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
                    ]);
                }
                return response()->json($clinic->answer($intent, $data['question'], $dates['date_from'] ?? null, $dates['date_to'] ?? null))
                    ->header('Cache-Control', 'private, no-store');
            }
            if ($clinicScope || empty($data['patient_id'])) {
                if ($clinicScope || preg_match('/\b(clinic|overall|all|today|this week|patients|visits|payments|installments|receipts|collections)\b/i', $data['question'])) {
                    return response()->json(['message' => 'I could not match that to a supported clinic-wide record question. Try asking which patients have outstanding balances, which installments are due or overdue, what payments were received today or this week, or how many visits occurred between the selected dates. For one patient’s balance or history, choose that patient first.'])
                        ->header('Cache-Control', 'private, no-store');
                }
                return response()->json(['message' => 'Select a patient for individual records, or ask about clinic balances, due installments, payments received today or this week, or visits in a date range.'], 422)
                    ->header('Cache-Control', 'private, no-store');
            }
            $patient = Patient::findOrFail($data['patient_id']);
            return response()->json($assistant->answer($patient, $data['question']))->header('Cache-Control', 'private, no-store');
        } catch (QueryException) {
            return response()->json(['message' => 'Patient records are temporarily unavailable. Check the clinic database connection.'], 503)
                ->header('Cache-Control', 'private, no-store');
        }
    }
}
