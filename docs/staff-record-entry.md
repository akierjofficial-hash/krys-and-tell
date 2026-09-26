# Staff Past Records Entry

## Setup

Run from the project directory before opening the new page:

```powershell
php artisan migrate
php artisan view:clear
```

The additive migration `2026_09_25_000001_create_record_entry_batches_table.php`
creates `record_entry_batches`: UUID primary key, staff/patient ownership,
workflow mode, draft payload, revision, review hash, warnings and saved summary.
Rollback drops that table; it does **not** undo clinical records already saved,
and it removes the draft/retry history. No existing data is regenerated.

No npm build or new production package is required. The editor uses the existing
Staff Blade layout and `public/js/staff-record-entry.js`. No deployment or push
is included. The clinic database migration was not run during implementation;
PHP tests migrate an isolated SQLite memory database. The additive migration
`2026_09_25_000002_add_visit_procedure_id_to_payments_table.php` adds a nullable
procedure allocation to ordinary payments and leaves existing payments unchanged.

## Workflow

1. Open **Patients → Past Records Entry**, search for the patient, and continue.
   **Patient Profile → Enter Past Records** preselects and fixes the patient.
2. Set a default dentist, which fills unassigned rows and applies to new rows.
   Enter visit dates, dentists, notes and procedures. Treatment search filters the
   selector. The current service price is suggested only while charge is blank;
   changing the treatment never replaces an already-entered charge.
3. Add ordinary receipts or choose **Installment or mixed billing**. Treatment charge,
   receipts and remaining balance are separate. Blank receipt amounts create no
   payments; typed zero receipts and overpayments are rejected. With mixed billing,
   select the non-financed treatment that each ordinary receipt pays for.
4. For installments, select the financed treatment, then enter agreed cost,
   downpayment/date/method, start date and
   fixed term or open contract. Enter only actual historical receipts. Month 0
   is reserved for the downpayment and created once if positive. Receipt counts
   in the success summary include that downpayment. Additional receipts use
   distinct positive payment numbers within the fixed term, if applicable.
   Collection-only receipts have no visit ID. A receipt may instead link a real
   existing visit of this patient or another real visit in the batch.
5. **Save Draft** or allow autosave to finish. Drafts belong to the staff account,
   selected patient and workflow. The draft icon beside **Change patient** shows
   the number of unfinished batches and opens the draft list. **Continue** restores
   unfinished work; same-tab recovery also retains edits while autosave is pending.
   **Discard** clears unfinished data without deleting actual patient records.
6. **Review Records**, check totals and possible duplicates, and return to correct
   anything necessary. Explicitly acknowledge legitimate separate records that
   match existing entries before **Save All Records**.
7. One transaction saves the whole batch, then returns to that patient's Visits
   or Payments tab with counts of each record type created.

**Duplicate visit** copies treatment details and charges but clears receipts and
installment information so copying a visit does not duplicate received money.

**Patient Profile → Add Visit** uses the same editor for one visit, defaults its
date to today, and offers **Save Visit**, **Save Visit & Record Payment**, and
**Save Visit & Create Installment Plan**. These actions open the needed fields,
then lead through review before saving.

The profile also has contextual **Record Payment**, **Create Installment Plan**,
and **Book Appointment** links. Existing forms are scoped/preselected to the
patient and preserve the profile return destination. Record Payment handles
ordinary visit receipts; existing installment collections remain accessible
through the patient's plan history.

## Integrity and compatibility

- Reuses existing patients, doctors, services, visits, procedures, payments,
  installment plans/payments, relationships and soft-delete behavior.
- Existing Staff authentication/role middleware and CSRF protection cover the new
  routes. Server ownership checks prevent using another staff member's draft.
  A batch's patient and mode cannot change.
- Revision checks reject stale drafts from other tabs. Review hashes validated
  data, warnings and revision; editing invalidates the review.
- Saving locks the batch and patient, revalidates references and rechecks
  duplicates. All clinical/financial writes and the saved batch marker share one
  transaction. Retrying a successful reviewed batch returns its existing summary.
  A new duplicate arising after review requires another review.
- Duplicate warnings include archived visits and matches inside the batch,
  comparing patient/date/dentist/service/charge. Nothing is silently skipped,
  merged, overwritten or restored.
- Monetary comparisons use integer cents. Actual historical prices are stored
  per procedure. Ordinary receipts do not reduce the original treatment charge.
- Ordinary receipts can carry a nullable `visit_procedure_id`. New mixed-billing
  receipts use it to identify the paid procedure; legacy and whole-visit payments
  remain valid without it. Plans now use the explicitly selected financed
  procedure instead of assuming that the visit's first procedure is financed.
- New entries create no appointments or appointment notifications.
- Staff installment GET pages no longer create downpayments or save balances.
- The legacy visit editor now preserves retained procedure IDs and prices;
  only explicitly removed procedures are removed. New procedures in that editor
  retain its existing current-price behavior.
- Editing an installment receipt recalculates its balance without rewriting the
  linked clinical visit's date, dentist or notes. Balance refresh reads current
  receipts and preserves a manually completed plan's status.
- Contextual ordinary payments preserve partial balances for custom-priced
  services. The legacy standalone endpoint retains its custom-total behavior
  unless `preserve_charge` is set.
- Existing Visits/Payments URLs remain. `/staff/visits/create?patient_id=…` opens
  the new patient-context editor; standalone creation and legacy POST URLs remain.

## New routes

All routes use the existing authenticated Staff group.

| Method | URL | Route name |
|---|---|---|
| GET | `/staff/record-entry` | `staff.records.index` |
| PUT | `/staff/record-entry/{id}/draft` | `staff.records.draft` |
| POST | `/staff/record-entry/{id}/review` | `staff.records.review` |
| POST | `/staff/record-entry/{id}/save` | `staff.records.store` |
| DELETE | `/staff/record-entry/{id}/draft` | `staff.records.discard` |

GET accepts `patient_id` and `mode=past|visit`. `{id}` is a draft/batch UUID.

## Automated verification

```powershell
php artisan test --compact
node --check public/js/staff-record-entry.js
php artisan view:cache
git diff --check
```

There are 24 new feature tests covering multiple visits/procedures, historical
prices, multiple/partial/blank receipts, month-0 downpayments, fixed/open plans,
collection-only receipts and real visit links, rollback after an injected write
failure, idempotency, duplicate warnings, ownership, stale drafts/reviews, invalid
payment numbers, patient context, legacy URLs/edits and read-only installment GETs.

Local results: **48 tests passed, 286 assertions**. The headless Edge smoke test,
JavaScript syntax checks, Blade compilation, new-PHP-file formatting checks and
`git diff --check` passed. PHP emits the environment's existing duplicate OpenSSL
module warning; it does not fail these checks.

The optional browser test uses rendered Blade fixtures with synthetic data and
mocked save endpoints; PHP tests exercise real controllers/database writes. It
checks patient search and direct selection, the compact draft panel, price
preservation, keyboard focus, duplicate rows, refresh recovery, installment
totals, tablet scrolling, review/save and normal visit payment actions.
External CDN requests are blocked, so also check the fully styled running app.

With Playwright available and Microsoft Edge installed:

```powershell
$env:KT_BROWSER_FIXTURES='1'
php artisan test --filter=test_entry_templates_can_be_exported
Remove-Item Env:KT_BROWSER_FIXTURES
# Set PLAYWRIGHT_MODULE to an existing Playwright package directory if Node cannot resolve it.
node tests/Browser/staff-record-entry.cjs
```

## Manual acceptance checks

1. Enter two historical visits, with several procedures and edited charges. Check
   the review and saved history keep those amounts.
2. Enter two partial ordinary receipts. Verify the charge, received total and
   outstanding balance. Repeat with a blank payment amount.
3. Enter a plan costing 1,000, downpayment 200 and receipts of 200 and 100. Expect
   balance 500, three receipts including month 0, and only real treatment visits.
   Repeat for an open contract and optional related-visit links.
4. Save, refresh and continue a draft. Try the same draft in two tabs; a stale
   version must be rejected. Discard it without affecting actual records.
5. Enter a matching date/dentist/service/charge. Check the duplicate warning,
   return-to-edit action and explicit acknowledgment requirement.
6. Try all three patient-context Add Visit actions. Save without payment, with a
   payment, and with a plan; confirm the correct patient profile tab opens.
7. Check patient scope and return location on payment/plan/appointment forms.
   A partial contextual payment must not discount a custom-priced treatment.
8. Edit historical visit notes and verify procedure IDs/prices remain. Edit a
   linked installment receipt and verify its treatment visit is unchanged.
9. Check desktop/tablet scrolling and light/dark themes. Admin and patient accounts
   must not access the new Staff workflow.

## Remaining boundaries

- Limits: 100 visits per batch, 100 procedures per visit, 200 ordinary receipts per
  visit or receipts per installment plan, 2 MB per draft. Future dates are rejected.
- This creates new historical plans on new historical visits. It does not append
  batches to existing plans or merge existing visits.
- The existing unique plan/month constraint remains; separate receipts cannot
  share a payment number.
- Existing status storage stays compatible: a plan with a balance uses
  `Partially Paid`; the editor distinguishes an unpaid plan visually.
- Existing installment collection/import workflows retain their legacy automatic
  visit behavior. The new entry workflow creates no such visits.
- Services exclude soft-deleted records. Dentists include inactive entries for
  historical assignments.
- Same-tab recovery clears on save/discard. No new retention/pruning policy or
  cross-staff sharing is introduced for server drafts.
- Database tests use SQLite. Production MySQL concurrency was not exercised;
  transaction locking and UUID uniqueness use Laravel database APIs.

## Complete changed-file manifest

New:

- `app/Http/Controllers/Staff/RecordEntryController.php`
- `app/Models/RecordEntryBatch.php`
- `app/Services/RecordEntryService.php`
- `database/migrations/2026_09_25_000001_create_record_entry_batches_table.php`
- `database/migrations/2026_09_25_000002_add_visit_procedure_id_to_payments_table.php`
- `public/js/staff-record-entry.js`
- `resources/views/staff/records/entry.blade.php`
- `tests/Feature/StaffRecordEntryTest.php`
- `tests/Browser/staff-record-entry.cjs`
- `tests/Browser/.gitignore`
- `docs/staff-record-entry.md`

Modified:

- `routes/web.php`
- `app/Http/Controllers/Staff/AppointmentController.php`
- `app/Http/Controllers/Staff/VisitController.php`
- `app/Http/Controllers/Staff/PaymentController.php`
- `app/Http/Controllers/Staff/InstallmentPlanController.php`
- `app/Http/Controllers/Staff/InstallmentPaymentController.php`
- `app/Http/Controllers/Staff/PatientController.php`
- `app/Models/Payment.php`
- `resources/views/staff/patients/index.blade.php`
- `resources/views/staff/patients/show.blade.php`
- `resources/views/staff/appointments/create.blade.php`
- `resources/views/staff/visits/edit.blade.php`
- `resources/views/staff/payments/create_cash.blade.php`
- `resources/views/staff/payments/_cash_patient_details.blade.php`
- `resources/views/staff/payments/edit.blade.php`
- `resources/views/staff/payments/show.blade.php`
- `resources/views/staff/payments/installment/create.blade.php`
- `resources/views/staff/payments/installment/edit-payment.blade.php`

Generated synthetic browser HTML fixtures are ignored in `tests/Browser/.fixtures/`.
