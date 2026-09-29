<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientUserLink;
use App\Models\User;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientAccountLinkController extends Controller
{
    public function index(Request $request, Patient $patient)
    {
        $q = trim((string) $request->query('q', ''));
        $links = $patient->accountLinks()->active()->with(['user', 'verifiedBy'])->latest('verified_at')->get();
        $accounts = collect();

        if ($q !== '') {
            $accounts = User::query()
                ->where('role', 'user')
                ->where('is_active', true)
                ->where(function ($query) use ($q) {
                    $query->whereLike('name', "%{$q}%")->orWhereLike('email', "%{$q}%");
                })
                ->withCount(['appointments', 'verifiedPatients'])
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString();
        }

        return view('staff.patients.account_links', compact('patient', 'links', 'accounts', 'q'));
    }

    public function store(Request $request, Patient $patient, AdminAuditService $audit)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'user')->where('is_active', true))],
            'relationship' => ['required', Rule::in(['self', 'parent', 'guardian', 'caregiver', 'other'])],
            'verification_note' => ['required', 'string', 'min:5', 'max:1000'],
            'confirm_identity' => ['accepted'],
        ]);

        $link = DB::transaction(function () use ($request, $patient, $data, $audit) {
            $link = PatientUserLink::query()->firstOrNew([
                'patient_id' => $patient->id,
                'user_id' => $data['user_id'],
            ]);
            if ($link->exists && $link->unlinked_at === null) {
                throw ValidationException::withMessages(['user_id' => 'This account is already linked to the patient.']);
            }

            $before = $link->exists ? $link->only(['relationship', 'verification_note', 'verified_at', 'unlinked_at']) : [];
            $link->fill([
                'relationship' => $data['relationship'],
                'verification_note' => $data['verification_note'],
                'verified_at' => now(),
                'verified_by_user_id' => $request->user()->id,
                'unlinked_at' => null,
                'unlinked_by_user_id' => null,
            ])->save();

            $audit->record($request->user(), 'patient_account.linked', $link,
                'Verified website account linked to patient #' . $patient->id . '.', $before,
                $link->only(['patient_id', 'user_id', 'relationship', 'verified_at']), $data['verification_note'], true);

            return $link;
        });

        return redirect()->route('staff.patients.account-links.index', $patient)
            ->with('success', 'Verified account link created.');
    }

    public function destroy(Request $request, Patient $patient, PatientUserLink $link, AdminAuditService $audit)
    {
        $data = $request->validate([
            'unlink_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'confirm_unlink' => ['accepted'],
        ]);
        if ((int) $link->patient_id !== (int) $patient->id) abort(404);
        if ($link->unlinked_at !== null) {
            return back()->with('error', 'This account link is already inactive.');
        }

        DB::transaction(function () use ($request, $link, $data, $audit) {
            $before = $link->only(['patient_id', 'user_id', 'relationship', 'verified_at']);
            $link->forceFill([
                'unlinked_at' => now(),
                'unlinked_by_user_id' => $request->user()->id,
            ])->save();
            $audit->record($request->user(), 'patient_account.unlinked', $link,
                'Website account unlinked from patient #' . $link->patient_id . '.', $before,
                $link->only(['unlinked_at', 'unlinked_by_user_id']), $data['unlink_reason'], true);
        });

        return back()->with('success', 'Account access to this patient record was removed.');
    }
}
