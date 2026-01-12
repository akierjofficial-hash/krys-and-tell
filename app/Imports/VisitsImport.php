<?php

namespace App\Imports;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class VisitsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $visitsCreated = 0;
    public int $proceduresCreated = 0;
    public int $skipped = 0;
    /** @var string[] */
    public array $errors = [];

    public function collection(Collection $rows)
    {
        // group rows by visit_group
        $groups = [];
        foreach ($rows as $i => $row) {
            $rowNo = $i + 2; // heading row = 1

            $g = trim((string)($row['visit_group'] ?? ''));
            if ($g === '') {
                $this->skipped++;
                $this->errors[] = "Row {$rowNo}: visit_group is required (used to group multiple services into 1 visit).";
                continue;
            }

            $groups[$g][] = ['rowNo' => $rowNo, 'row' => $row];
        }

        foreach ($groups as $groupKey => $items) {
            DB::transaction(function () use ($groupKey, $items) {
                $firstRow = $items[0]['row'];

                // Optional: attach procedures to an existing visit if visit_id is provided
                $visitId = $this->pick($items, 'visit_id');
                $visit = null;

                if ($visitId !== null && $visitId !== '' && is_numeric($visitId)) {
                    $visit = Visit::find((int)$visitId);
                    if (!$visit) {
                        $this->skipped++;
                        $this->errors[] = "Group {$groupKey}: visit_id {$visitId} not found (skipped).";
                        return;
                    }
                }

                // Resolve patient
                $patient = null;
                $patientId = $this->pick($items, 'patient_id');

                if ($patientId !== null && $patientId !== '' && is_numeric($patientId)) {
                    $patient = Patient::find((int)$patientId);
                } else {
                    $last  = trim((string)($this->pick($items, 'patient_last_name') ?? ''));
                    $first = trim((string)($this->pick($items, 'patient_first_name') ?? ''));
                    if ($last !== '' && $first !== '') {
                        $patient = Patient::query()
                            ->whereRaw('LOWER(last_name) = ?', [mb_strtolower($last)])
                            ->whereRaw('LOWER(first_name) = ?', [mb_strtolower($first)])
                            ->orderByDesc('id')
                            ->first();
                    }
                }

                if (!$patient && !$visit) {
                    $this->skipped++;
                    $this->errors[] = "Group {$groupKey}: patient not found (use patient_id OR patient_last_name+patient_first_name).";
                    return;
                }

                // Resolve doctor
                $doctor = null;
                $doctorId = $this->pick($items, 'doctor_id');

                if ($doctorId !== null && $doctorId !== '' && is_numeric($doctorId)) {
                    $doctor = Doctor::find((int)$doctorId);
                } else {
                    $dentistName = trim((string)($this->pick($items, 'dentist_name') ?? ''));
                    if ($dentistName !== '') {
                        $doctor = Doctor::query()
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower($dentistName)])
                            ->orderByDesc('id')
                            ->first();
                    }
                }

                if (!$doctor && !$visit) {
                    $this->skipped++;
                    $this->errors[] = "Group {$groupKey}: doctor not found (use doctor_id OR dentist_name).";
                    return;
                }

                // Visit date
                $visitDate = $this->parseDate($this->pick($items, 'visit_date'));
                if (!$visitDate && !$visit) {
                    $this->skipped++;
                    $this->errors[] = "Group {$groupKey}: visit_date is required (mm/dd/yy, mm/dd/yyyy, yyyy-mm-dd, or Excel date).";
                    return;
                }

                $visitNotes = trim((string)($this->pick($items, 'visit_notes') ?? ''));
                $visitNotes = $visitNotes !== '' ? $visitNotes : null;

                // Build procedures payload first (so we don’t create an empty visit)
                $procedurePayloads = [];

                foreach ($items as $it) {
                    $rowNo = $it['rowNo'];
                    $row   = $it['row'];

                    $service = null;
                    $serviceId = $row['service_id'] ?? null;

                    if ($serviceId !== null && $serviceId !== '' && is_numeric($serviceId)) {
                        $service = Service::find((int)$serviceId);
                    } else {
                        $serviceName = trim((string)($row['service_name'] ?? ''));
                        if ($serviceName !== '') {
                            $service = Service::query()
                                ->whereRaw('LOWER(name) = ?', [mb_strtolower($serviceName)])
                                ->orderByDesc('id')
                                ->first();
                        }
                    }

                    if (!$service) {
                        $this->errors[] = "Row {$rowNo}: service not found (use service_id OR service_name).";
                        $this->skipped++;
                        continue;
                    }

                    $override = $this->toMoney($row['procedure_price'] ?? null);
                    $price = $override !== null ? $override : (float)($service->base_price ?? 0);

                    $procedurePayloads[] = [
                        'service_id'   => $service->id,
                        'tooth_number' => $this->nullIfEmpty($row['tooth_number'] ?? null),
                        'surface'      => $this->nullIfEmpty($row['surface'] ?? null),
                        'shade'        => $this->nullIfEmpty($row['shade'] ?? null),
                        'price'        => $price,
                        'notes'        => $this->nullIfEmpty($row['procedure_notes'] ?? null),
                    ];
                }

                if (count($procedurePayloads) < 1) {
                    $this->skipped++;
                    $this->errors[] = "Group {$groupKey}: no valid service rows (nothing imported).";
                    return;
                }

                // Create visit if needed
                if (!$visit) {
                    $visit = Visit::create([
                        'patient_id'   => $patient->id,
                        'doctor_id'    => $doctor?->id,
                        'dentist_name' => $doctor?->name,
                        'visit_date'   => $visitDate,
                        'notes'        => $visitNotes,
                        // status defaults to pending (migration default)
                    ]);

                    $visitTotalDue = $this->toMoney($this->pick($items, 'visit_total_due'));
                    if ($visitTotalDue !== null) {
                        $visit->price = $visitTotalDue;
                        $visit->save();
                    }

                    $this->visitsCreated++;
                }

                foreach ($procedurePayloads as $pp) {
                    $visit->procedures()->create($pp);
                    $this->proceduresCreated++;
                }
            });
        }
    }

    private function pick(array $items, string $key): mixed
    {
        foreach ($items as $it) {
            $row = $it['row'];
            $v = $row[$key] ?? null;
            if ($v === null) continue;
            if (is_string($v) && trim($v) === '') continue;
            return $v;
        }
        return null;
    }

    private function nullIfEmpty($v): ?string
    {
        $s = trim((string)($v ?? ''));
        return $s === '' ? null : $s;
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
        }

        $s = trim((string)$value);
        if ($s === '') return null;

        $formats = ['m/d/Y', 'm/d/y', 'Y-m-d', 'm-d-Y', 'm-d-y', 'd/m/Y', 'd/m/y'];
        foreach ($formats as $fmt) {
            try { return Carbon::createFromFormat($fmt, $s)->format('Y-m-d'); }
            catch (\Throwable $e) {}
        }

        try { return Carbon::parse($s)->toDateString(); }
        catch (\Throwable $e) { return null; }
    }

    private function toMoney($value): ?float
    {
        if ($value === null || $value === '') return null;
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return null;
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        if (!is_numeric($value)) return null;
        return (float)$value;
    }
}
