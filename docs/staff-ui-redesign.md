# Staff UI redesign checklist

The authenticated Staff interface is scoped by `body.kt-staff` in
`resources/views/layouts/staff.blade.php`. Its visual tokens and reusable patterns
live in `public/css/staff-app.css`; Admin, public, patient portal, authentication,
OCR and printed patient information do not load that stylesheet.

## Route and view coverage

| Module | Screen routes checked | Blade views covered |
|---|---|---|
| Dashboard | `staff.dashboard`, calendar events and live snapshot | `staff/dashboard/index.blade.php` |
| Patients | index, create/store, show, edit/update, delete/restore, import/export, print, patient visits | `staff/patients/index`, `create`, `edit`, `show`, `print/patient_information` |
| Past records and Add Visit | selector, patient editor, draft/review/save/discard | `staff/records/entry.blade.php` |
| Visits | index, patient history, create/store, show, edit/update, delete/restore, import/template | `staff/visits/index`, `patient`, `create`, `show`, `edit` |
| Payments | index, choose arrangement, patient detail fragment, ordinary create/store/show/edit/update/delete/restore | `staff/payments/index`, `choose-plan`, `_cash_patient_details`, `create_cash`, `show`, `edit` |
| Installments | index, create/store, show, edit/update, pay, edit receipt, complete/reopen, delete/restore, plan and receipt imports/templates | `staff/payments/installment/create`, `show`, `edit`, `pay`, `edit-payment` |
| Appointments | index/calendar, create/store, show, edit/update, delete/restore | `staff/appointments/index`, `create`, `show`, `edit` |
| Booking requests | queue, widget, approve and decline | `staff/approvals/index.blade.php` and the Staff layout popover |
| Messages | inbox, detail, read, reply, delete/restore and widget | `staff/messages/index`, `show` |
| Dentist day-off | calendar/list, create, update and remove | `shared/dentist_unavailability/index.blade.php` with the Staff layout |
| Services | index, create/store, show/history, edit/update, delete/restore | `staff/services/index`, `create`, `edit`, `patients` |
| Treatment doctors | searchable assignment list and update | `shared/service-doctor-assignments/index.blade.php` with the Staff layout |

All Staff write routes retain the existing controllers, validation, middleware and
return URL behavior. The redesign changes presentation and navigation only. The
Past Records editor retains its separately tested draft, review, mixed billing,
installment and transactional-save behavior.

## Shared design primitives

- `x-staff.page-header`
- `x-staff.section-card`
- `x-staff.status-badge`
- `x-staff.icon-button`
- `x-staff.empty-state`
- Staff-scoped tokens for color, type, spacing, radii, controls and shadows
- Shared navigation, buttons, forms, tables, alerts, status pills, tabs, empty
  states, sticky actions, keyboard focus, validation and responsive rules

## Navigation

Daily clinic: Dashboard, Patients, Visits, Payments, Appointments, Booking Requests
and Messages. Clinic setup: Dentist Day-off, Services and Treatment Doctors.
Pending booking and unread message badges remain live. Quick actions for a patient
and appointment are available in the top bar, while account and logout remain at
the bottom of the sidebar.

## Validation

- `php artisan view:cache`
- `php artisan test`
- `node --check public/js/staff-record-entry.js`
- `node tests/Browser/staff-record-entry.cjs` after generating its Blade fixtures
- Desktop and tablet screenshots in `docs/screenshots/`
