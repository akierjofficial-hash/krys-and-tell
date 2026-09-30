<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RecordEntryBatch;
use App\Models\Service;
use App\Models\Visit;
use App\Services\RecordEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecordEntryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['patient_id' => ['nullable', 'integer', Rule::exists('patients', 'id')->whereNull('deleted_at')], 'mode' => ['nullable', Rule::in(['past', 'visit'])]]);
        $mode = $request->input('mode', 'past');
        $patient = $request->filled('patient_id') ? Patient::findOrFail($request->patient_id) : null;
        $config = [
            'mode' => $mode, 'userId' => $request->user()->id, 'patientId' => $patient?->id,
            'selectedPatient' => $patient?->only(['id', 'first_name', 'last_name']),
            'patientSearchUrl' => route('staff.records.patients'),
            'doctors' => Doctor::orderBy('name')->get(['id', 'name', 'is_active']),
            'services' => Service::orderBy('name')->get(['id', 'name', 'base_price']),
            'existingVisits' => $patient ? Visit::where('patient_id', $patient->id)->orderByDesc('visit_date')->get(['id', 'visit_date', 'dentist_name']) : [],
            'drafts' => $patient ? RecordEntryBatch::where('user_id', $request->user()->id)->where('patient_id', $patient->id)->where('mode', $mode)->whereIn('status', ['draft', 'reviewed'])->latest('updated_at')->get() : [],
            'baseUrl' => route('staff.records.index'), 'today' => today()->toDateString(),
            'csrf' => csrf_token(), 'profileUrl' => $patient ? route('staff.patients.show', $patient) : null,
            'visitUrlTemplate' => route('staff.visits.show', ['visit' => '__VISIT_ID__']),
        ];

        return view('staff.records.entry', compact('config', 'patient', 'mode'));
    }

    public function patients(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim((string) $request->query('q', ''));
        $query = Patient::query();
        if ($search !== '') {
            $parts = preg_split('/[\s,]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $query->where(function ($match) use ($search, $parts) {
                $match->where(function ($names) use ($parts) {
                    foreach ($parts as $part) {
                        $names->where(fn ($word) => $word->whereLike('first_name', "%{$part}%")
                            ->orWhereLike('last_name', "%{$part}%")
                            ->orWhereLike('middle_name', "%{$part}%"));
                    }
                })->orWhereLike('contact_number', "%{$search}%");
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                    $match->orWhereDate('birthdate', $search);
                }
                if (ctype_digit($search)) $match->orWhereKey((int) $search);
            });
        }

        return response()->json(['patients' => $query->orderBy('last_name')->orderBy('first_name')
            ->limit(20)->get(['id', 'first_name', 'last_name', 'birthdate', 'contact_number'])]);
    }

    private function writeDraft(Request $request, string $id): RecordEntryBatch
    {
        $request->merge(['batch_id' => $id]);
        $data = $request->validate([
            'batch_id' => ['required', 'uuid'], 'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')->whereNull('deleted_at')],
            'mode' => ['required', Rule::in(['past', 'visit'])], 'version' => ['required', 'integer', 'min:0'],
            'payload' => ['required', 'array'],
        ]);
        if (strlen(json_encode($data['payload'])) > 2000000) {
            throw ValidationException::withMessages(['payload' => 'Split this entry into smaller batches (maximum draft size 2 MB).']);
        }
        // Unique primary key handles concurrent first saves; row locks handle retries.
        DB::table('record_entry_batches')->insertOrIgnore([
            'id' => $id, 'user_id' => $request->user()->id, 'patient_id' => $data['patient_id'], 'mode' => $data['mode'],
            'status' => 'draft', 'version' => 0, 'payload' => '{}', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $batch = RecordEntryBatch::whereKey($id)->lockForUpdate()->firstOrFail();
        $this->authorizeBatch($request, $batch);
        abort_unless($batch->patient_id == $data['patient_id'] && $batch->mode === $data['mode'], 409, 'A draft cannot be moved to another patient or workflow.');
        abort_if(in_array($batch->status, ['saved', 'discarded']), 409, 'This batch is already saved or discarded. Reload the patient profile.');
        abort_unless($batch->version == $data['version'], 409, 'This draft changed in another tab. Reload and continue the latest draft.');
        $batch->update(['payload' => $data['payload'], 'version' => $batch->version + 1, 'status' => 'draft', 'review_hash' => null, 'warnings' => null]);

        return $batch;
    }

    private function authorizeBatch(Request $request, RecordEntryBatch $batch): void
    {
        abort_unless((int) $batch->user_id === (int) $request->user()->id, 403);
    }

    public function draft(Request $request, string $id)
    {
        $batch = DB::transaction(fn () => $this->writeDraft($request, $id), 3);

        return response()->json(['version' => $batch->version, 'message' => 'Draft saved.']);
    }

    public function review(Request $request, string $id, RecordEntryService $service)
    {
        return DB::transaction(function () use ($request, $id, $service) {
            $batch = $this->writeDraft($request, $id);
            $data = $service->validate($batch->payload, $batch->patient_id, $batch->mode);
            $warnings = $service->warnings($data, $batch->patient_id);
            $hash = hash('sha256', json_encode([$data, $warnings, $batch->version]));
            $batch->update(['payload' => $data, 'status' => 'reviewed', 'warnings' => $warnings, 'review_hash' => $hash]);

            return response()->json(['version' => $batch->version, 'review_hash' => $hash, 'warnings' => $warnings, 'payload' => $data]);
        }, 3);
    }

    public function store(Request $request, string $id, RecordEntryService $service)
    {
        $request->validate(['review_hash' => ['required', 'string', 'size:64'], 'acknowledge_duplicates' => ['required', 'boolean']]);
        $batch = DB::transaction(function () use ($request, $id, $service) {
            $batch = RecordEntryBatch::whereKey($id)->lockForUpdate()->firstOrFail();
            $this->authorizeBatch($request, $batch);
            abort_unless(hash_equals($batch->review_hash ?? '', $request->review_hash), 409, 'Review the latest draft before saving.');
            if ($batch->status === 'saved') {
                return $batch;
            }
            abort_unless($batch->status === 'reviewed', 409, 'Review this draft first.');
            // Serialize new batches for the same patient before checking duplicates again.
            Patient::whereKey($batch->patient_id)->lockForUpdate()->firstOrFail();
            $data = $service->validate($batch->payload, $batch->patient_id, $batch->mode);
            $warnings = $service->warnings($data, $batch->patient_id);
            if ($warnings !== $batch->warnings) {
                throw ValidationException::withMessages(['duplicates' => 'Patient records changed since review. Return to entry and review again.']);
            }
            if ($warnings && ! $request->boolean('acknowledge_duplicates')) {
                throw ValidationException::withMessages(['duplicates' => 'Review and acknowledge the possible duplicates, or return to correct them.']);
            }
            $summary = $service->create($data, $batch->patient_id);
            $batch->update(['status' => 'saved', 'summary' => $summary]);

            return $batch;
        }, 3);
        $summary = collect($batch->summary)->map(fn ($count, $label) => $count.' '.str_replace('_', ' ', $label).' created')->implode(', ');
        $request->session()->flash('success', ucfirst($summary).'.');
        $billing = $batch->summary['ordinary_payments'] || $batch->summary['installment_plans'];

        return response()->json(['summary' => $batch->summary, 'redirect' => route('staff.patients.show', ['patient' => $batch->patient_id, 'tab' => $billing ? 'tab-payments' : 'tab-visits'])]);
    }

    public function discard(Request $request, string $id)
    {
        DB::transaction(function () use ($request, $id) {
            $batch = RecordEntryBatch::whereKey($id)->lockForUpdate()->firstOrFail();
            $this->authorizeBatch($request, $batch);
            abort_if($batch->status === 'saved', 409, 'Saved records cannot be discarded as a draft.');
            $batch->update(['status' => 'discarded', 'payload' => [], 'review_hash' => null, 'warnings' => null]);
        });

        return response()->json(['message' => 'Draft discarded.']);
    }
}
