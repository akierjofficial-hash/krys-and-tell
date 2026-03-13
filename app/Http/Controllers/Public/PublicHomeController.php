<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class PublicHomeController extends Controller
{
    private const CLINIC_ESTABLISHED_YEAR = 2022;
    private const HOME_TESTIMONIAL_LIMIT = 3;
    private const REVIEWS_PER_PAGE = 12;

    public function index()
    {
        $services = collect();
        $testimonials = collect();
        $testimonialsTotal = 0;

        try {
            if (Schema::hasTable('services')) {
                $serviceQuery = Service::query();
                if (Schema::hasColumn('services', 'is_active')) {
                    $serviceQuery->where('is_active', 1);
                }

                $services = $serviceQuery
                    ->orderBy('name')
                    ->get(['id', 'name', 'duration_minutes']);
            }
        } catch (\Throwable $e) {
            $services = collect();
        }

        try {
            $testimonialQuery = $this->makePublicTestimonialsQuery();
            if ($testimonialQuery) {
                $testimonialColumns = ['id', 'name', 'role', 'text', 'created_at'];
                if (Schema::hasColumn('testimonials', 'rating')) {
                    $testimonialColumns[] = 'rating';
                }

                $testimonialsTotal = (clone $testimonialQuery)->count();
                $testimonials = $testimonialQuery
                    ->limit(self::HOME_TESTIMONIAL_LIMIT)
                    ->get($testimonialColumns);
            }
        } catch (\Throwable $e) {
            $testimonials = collect();
            $testimonialsTotal = 0;
        }

        $patientCount = $this->resolvePatientCount();
        $avgRating = $this->resolveAverageRating($testimonials);
        $years = max(1, (int) now()->year - self::CLINIC_ESTABLISHED_YEAR + 1);
        $satisfaction = (int) max(0, min(100, round(($avgRating / 5) * 100)));

        $heroStats = [
            'patient_count' => $patientCount,
            'years' => $years,
            'satisfaction' => $satisfaction,
            'average_rating' => $avgRating,
            'happy_smiles' => number_format($patientCount) . '+',
        ];

        return view('public.home', compact('services', 'testimonials', 'heroStats', 'testimonialsTotal'));
    }

    public function reviews()
    {
        $testimonials = new LengthAwarePaginator([], 0, self::REVIEWS_PER_PAGE, 1, [
            'path' => request()->url(),
        ]);

        try {
            $testimonialQuery = $this->makePublicTestimonialsQuery();
            if ($testimonialQuery) {
                $testimonialColumns = ['id', 'name', 'role', 'text', 'created_at'];
                if (Schema::hasColumn('testimonials', 'rating')) {
                    $testimonialColumns[] = 'rating';
                }

                $testimonials = $testimonialQuery
                    ->paginate(self::REVIEWS_PER_PAGE, $testimonialColumns)
                    ->withQueryString();
            }
        } catch (\Throwable $e) {
            $testimonials = new LengthAwarePaginator([], 0, self::REVIEWS_PER_PAGE, 1, [
                'path' => request()->url(),
            ]);
        }

        return view('public.reviews', compact('testimonials'));
    }

    public function storeReview(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if (!Schema::hasTable('testimonials')) {
            return redirect()
                ->to(route('public.home') . '#testimonials')
                ->with('review_error', 'Reviews are not configured yet. Please try again later.');
        }

        $data = $request->validateWithBag('review', [
            'rating' => ['required', 'integer', 'between:1,5'],
            'text' => ['required', 'string', 'min:10', 'max:600'],
        ]);

        $payload = [
            'name' => trim((string) ($user->name ?? 'Patient')),
            'role' => 'Patient',
            'text' => trim((string) $data['text']),
            'is_active' => true,
            'sort_order' => 0,
        ];

        if (Schema::hasColumn('testimonials', 'rating')) {
            $payload['rating'] = (int) $data['rating'];
        }

        if (Schema::hasColumn('testimonials', 'user_id')) {
            Testimonial::updateOrCreate(
                ['user_id' => $user->id],
                $payload
            );
        } else {
            Testimonial::create($payload);
        }

        return redirect()
            ->to(route('public.home') . '#testimonials')
            ->with('review_success', 'Thanks for your review. It is now visible on our homepage.');
    }

    private function makePublicTestimonialsQuery()
    {
        if (!Schema::hasTable('testimonials')) {
            return null;
        }

        $query = Testimonial::query();
        if (Schema::hasColumn('testimonials', 'is_active')) {
            $query->where('is_active', 1);
        }

        if (Schema::hasColumn('testimonials', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        return $query->orderByDesc('id');
    }

    private function resolvePatientCount(): int
    {
        $identityKeys = collect();

        try {
            if (Schema::hasTable('patients')) {
                $patientRows = Patient::query()->get([
                    'id',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'email',
                    'contact_number',
                ]);

                foreach ($patientRows as $patient) {
                    $identityKeys->push($this->buildPatientIdentityKey(
                        $patient->id,
                        $patient->email,
                        $patient->contact_number,
                        trim(implode(' ', array_filter([
                            (string) ($patient->first_name ?? ''),
                            (string) ($patient->middle_name ?? ''),
                            (string) ($patient->last_name ?? ''),
                        ])))
                    ));
                }
            }
        } catch (\Throwable $e) {
        }

        try {
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
                $userQuery = User::query()->where('role', 'user');
                if (Schema::hasColumn('users', 'is_active')) {
                    $userQuery->where('is_active', true);
                }

                $userRows = $userQuery->get(['id', 'name', 'email']);
                foreach ($userRows as $user) {
                    $identityKeys->push($this->buildPatientIdentityKey(
                        $user->id,
                        $user->email,
                        null,
                        (string) ($user->name ?? '')
                    ));
                }
            }
        } catch (\Throwable $e) {
        }

        if ($identityKeys->isNotEmpty()) {
            return (int) $identityKeys
                ->filter(fn ($key) => $key !== '')
                ->unique()
                ->count();
        }

        try {
            if (Schema::hasTable('appointments')) {
                $appointmentQuery = Appointment::query();

                if (Schema::hasColumn('appointments', 'user_id')) {
                    $count = (int) $appointmentQuery->whereNotNull('user_id')->distinct('user_id')->count('user_id');
                    if ($count > 0) {
                        return $count;
                    }
                }

                if (Schema::hasColumn('appointments', 'patient_id')) {
                    $count = (int) Appointment::query()->whereNotNull('patient_id')->distinct('patient_id')->count('patient_id');
                    if ($count > 0) {
                        return $count;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return 0;
    }

    private function buildPatientIdentityKey($id, $email, $contactNumber, $name): string
    {
        $normalizedEmail = $this->normalizeEmail($email);
        if ($normalizedEmail !== '') {
            return 'email:' . $normalizedEmail;
        }

        $normalizedPhone = $this->normalizePhone($contactNumber);
        if ($normalizedPhone !== '') {
            return 'phone:' . $normalizedPhone;
        }

        $normalizedName = $this->normalizeName($name);
        if ($normalizedName !== '') {
            return 'name:' . $normalizedName;
        }

        return 'id:' . (string) $id;
    }

    private function normalizeEmail($email): string
    {
        return strtolower(trim((string) ($email ?? '')));
    }

    private function normalizePhone($phone): string
    {
        $digitsOnly = preg_replace('/\D+/', '', (string) ($phone ?? ''));
        return (string) ($digitsOnly ?? '');
    }

    private function normalizeName($name): string
    {
        $normalized = strtolower(trim((string) ($name ?? '')));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim((string) ($normalized ?? ''));
    }

    private function resolveAverageRating($testimonials): float
    {
        if (!$testimonials || $testimonials->isEmpty()) {
            return 0.0;
        }

        $ratings = $testimonials
            ->map(function ($t) {
                if (is_array($t)) {
                    return isset($t['rating']) ? (float) $t['rating'] : null;
                }

                return isset($t->rating) ? (float) $t->rating : null;
            })
            ->filter(fn ($r) => $r !== null && $r > 0)
            ->values();

        if ($ratings->isEmpty()) {
            return 0.0;
        }

        return (float) round($ratings->avg(), 1);
    }
}
