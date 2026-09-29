# Staff records assistant (Phases 1–3)

The staff layout includes a floating, read-only records panel. Staff select a patient by name; the list shows patient ID and birthdate so similar names stay separate. The patient profile preselects its current patient. The selected patient stays visible for follow-up questions, with separate switch and clear controls. Suggested questions cover balances, recent visits and treatments, payment receipts, and installment months without recorded receipts.

## Supervised pilot checklist

Keep `STAFF_ASSISTANT_PROVIDER=rules`. Before using an answer to contact a patient or record a payment, staff should:

1. Confirm the selected patient's ID and birthdate on the linked patient profile, especially when names are similar.
2. Open the linked visit, receipt, or installment plan and compare dates, procedures, actual charges, and recorded receipt amounts with the answer. For clinic totals, compare with the dashboard's **calculable** balance figure or the dated Payments ledger.
3. Treat any **incomplete** balance as unresolved. Follow the review link; check mixed procedures, plan ownership, and downpayments without receipts. Do not treat an entered downpayment as collected cash without a receipt.
4. For due installments, check the plan schedule and balance: a month with a partial receipt can appear paid in the schedule while the plan still has a balance. Report discrepancies to the clinic administrator; do not alter records through the assistant.

The dashboard's balance count is patients with a calculable outstanding balance plus patients whose balance is incomplete. Its peso total includes only fully calculable patient balances. The assistant's clinic balance answer uses the same source calculation. Both exclude unresolved patients from the numeric total and link to a source record for review. The patient-specific assistant can show a known partial balance for an incomplete patient, explicitly labeled incomplete; it is not a complete amount due.

The `/staff/assistant/patients` and `/staff/assistant/ask` endpoints inherit `auth`, `role:staff`, and the active-account check. Both use POST bodies, so names and questions do not appear in web-server query-string access logs. They are throttled and return `Cache-Control: private, no-store`. The browser keeps conversation text only in memory; the assistant routes are skipped by the activity logger. No database table, chat transcript, or detail logging is added. Laravel chooses specific read-only queries and builds all numeric and clinical answers; the model cannot execute SQL or write records. Balances use `FinancialService::visitBalance()` and `FinancialService::planBalance()` for ordinary visits and plans; mixed visits are handled as described below. The patient profile, visit detail, and payment detail pages display their recorded charges and payments for verification.

## Provider

The default `STAFF_ASSISTANT_PROVIDER=rules` needs no model, credential, subscription, or per-question fee. It recognizes the supported patient and clinic questions, including selected-patient identity. To use a server-reachable Ollama instance for a few additional generic aliases, set:

```
STAFF_ASSISTANT_PROVIDER=ollama
STAFF_ASSISTANT_OLLAMA_URL=http://127.0.0.1:11434
STAFF_ASSISTANT_OLLAMA_MODEL=llama3.2:3b
```

Install and pull the selected model in Ollama separately. Only fixed, generic aliases such as “Tell me about prior encounters” are sent for classification. Arbitrary staff text, patient names, amounts, medical notes, and generated answers are never forwarded. The returned label is accepted only if it names one of the existing lookups. If Ollama is unavailable or rate limited, the panel says so and the standard keyword questions still work. The assistant never stores a chat transcript; its routes are excluded from the application's activity logger.

On Render, `127.0.0.1` refers to the deployed web-service container and cannot reach a workstation's Ollama. The assistant detects this combination and reports a configuration problem without making the request. Keep `rules`, or run a reachable Ollama service on a private network and set its internal URL as a Render environment variable. Hosting a model requires adequate CPU/RAM and may incur compute charges; this implementation does not require any paid AI API. Do not expose an unauthenticated Ollama API publicly. If the service configuration is cached, rebuild it after changing environment variables. Live network reachability must be checked from the deployed service after configuration; source code cannot prove it.

## Limits

Phase 3 adds four clinic-wide read-only questions: patients with outstanding balances, installment months due or overdue, receipts received today or this week, and treatment visits within a chosen date range. Staff can ask these without selecting a patient. The visit count requires both date fields in the panel. Answers show counts, a date range or check date, relevant totals, and up to ten source links. Clinic-wide questions use Laravel's fixed intent rules and never send records to Ollama.

Balances use `FinancialService::visitBalance()` and `planBalance()`. Mixed-billing visits include their ordinary procedure balance when the financed procedure is uniquely identifiable; ambiguous old records are called out and excluded from a claimed complete total. A plan with an entered downpayment but no receipt is flagged: the existing balance formula counts it, but that is not proof it was collected. The result is a current recorded balance, not a historical snapshot. Collection totals use `FinancialService::collectedBetween()` on recorded payment dates, including ordinary and installment receipts; soft-deleted receipts are excluded. A receipt whose source visit or plan is archived remains in the existing collection total, but cannot have a working detail link. The clinic week is Monday through Sunday in `config('app.timezone')` (`Asia/Manila` currently).

For fixed-term plans, a month is due when its scheduled date is today and overdue when earlier; only months without any receipt are counted. This matches the existing plan screen, which marks a month paid as soon as it has a receipt, even if the amount is partial. The answer shows the *remaining balance across affected plans*, never an invented per-month amount. Open contracts, plans without usable dates, and completed plans with a remaining calculated balance are reported separately. At most 1,200 months per plan are scanned, matching the record-entry limit, and the answer warns if a plan exceeds that limit. Date-range visit counts match Staff > Visits > All Visits, including payment-linked visit rows and excluding soft-deleted visits. The dashboard's “Visits Recorded” tile uses a narrower treatment rule, so its number may differ. The project has no defined refund or cancelled-visit workflow, so the assistant does not infer net refunds or a cancellation status; staff should verify unusual historical records on their source pages.

This assistant is not a general chart interpreter. It lists at most five recent visit rows and ten recent receipts. Fixed-term installment output identifies months without a payment row and shows the calculated plan balance, but it does not assert a specific installment amount or that a month with a receipt is fully settled. Open contracts have no fixed due schedule. Soft-deleted receipt rows are omitted; a receipt with an archived parent can remain in the existing collections calculation and is explicitly flagged. Answers link to the source patient, visit, ordinary receipt, exact installment receipt anchor, or plan schedule for staff verification. If the database is unavailable during an assistant lookup, the endpoint returns a 503 message; if the whole staff page cannot load, the assistant cannot appear.
