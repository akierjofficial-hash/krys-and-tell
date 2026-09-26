@extends('layouts.staff')

@section('kt_live_scope', 'patients')
@section('kt_live_interval', 12000)

@push('styles')
<style>
.patients-page{color:var(--kt-text)}
.patients-head{margin-bottom:16px}
.patients-title{font-size:30px;font-weight:850;letter-spacing:-.5px;margin:0}.patients-subtitle{color:var(--kt-muted);margin:4px 0 0}
.patients-toolbar{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-top:15px}.patient-search{position:relative;width:min(330px,100%)}.action-cluster,.primary-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.patient-search>i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--kt-muted)}
.patient-search input,.patient-sort{min-height:42px;border:1px solid var(--kt-input-border);border-radius:10px;background:var(--kt-input-bg);color:var(--kt-text)}
.patient-search input{width:100%;padding:9px 38px}.patient-search .clear-search{position:absolute;right:7px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:var(--kt-muted);padding:6px}
.patient-sort{padding:9px 11px;min-width:174px}.pbtn{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 13px;border:1px solid var(--kt-border);border-radius:10px;background:var(--kt-surface);color:var(--kt-text);font-weight:750;text-decoration:none;white-space:nowrap}
.pbtn:hover{background:var(--kt-surface-2);color:var(--kt-text)}.pbtn.primary{background:#087cf0;border-color:#087cf0;color:#fff}.pbtn.strong{border-color:#087cf0;color:#087cf0}.pbtn.icon{width:40px;padding:0}.pbtn.danger{color:#dc3545}
.alphabet{display:flex;align-items:center;gap:4px;overflow-x:auto;padding:10px 2px 12px;scrollbar-width:thin;margin-bottom:5px}.alpha-link{flex:0 0 auto;width:34px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-size:12px;font-weight:800;color:var(--kt-muted);border:1px solid transparent}.alpha-link.all{width:44px}.alpha-link:hover{background:var(--kt-surface-2);color:#087cf0}.alpha-link.active{background:#087cf0;color:#fff}.alpha-link.disabled{opacity:.3;pointer-events:none}
.patient-panel{background:var(--kt-surface);border:1px solid var(--kt-border);border-radius:14px;box-shadow:var(--kt-shadow);overflow:visible}.patient-table-wrap{overflow:visible}.patient-table{width:100%;border-collapse:separate;border-spacing:0}.patient-table th{position:sticky;top:68px;z-index:5;background:var(--kt-surface-2);color:var(--kt-muted);font-size:11px;text-transform:uppercase;letter-spacing:.04em;padding:12px 15px;border-bottom:1px solid var(--kt-border);white-space:nowrap}.patient-table td{padding:13px 15px;border-bottom:1px solid var(--kt-border);vertical-align:middle}.patient-table tbody tr:last-child td{border-bottom:0}.patient-table tbody tr.patient-row:hover td{background:rgba(8,124,240,.035)}
.patient-person{display:flex;align-items:center;gap:11px;min-width:210px}.patient-avatar{width:38px;height:38px;border-radius:50%;background:#e5f1ff;color:#087cf0;display:grid;place-items:center;font-weight:850;flex:0 0 auto}.patient-name{font-weight:800;color:var(--kt-text);text-decoration:none}.patient-name:hover{color:#087cf0}.patient-meta,.cell-sub{font-size:12px;color:var(--kt-muted);margin-top:2px}.initial-divider td{padding:7px 15px!important;background:var(--kt-surface-2)!important;color:#087cf0;font-size:12px;font-weight:850;letter-spacing:.04em}.gender-pill{display:inline-flex;padding:5px 9px;border-radius:999px;background:var(--kt-surface-2);font-size:12px;font-weight:700}.row-actions{display:flex;justify-content:flex-end;gap:7px}.row-menu{position:relative}.row-menu .dropdown-menu{z-index:1080;min-width:205px}.dropdown-item i{width:20px}.dropdown-item.delete{color:#dc3545}.patient-empty{text-align:center;padding:55px 20px;color:var(--kt-muted)}.patient-empty i{font-size:28px;margin-bottom:10px;color:#8cbdf1}
.patients-footer{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:13px 15px;border-top:1px solid var(--kt-border)}.page-summary{color:var(--kt-muted);font-size:13px}.page-controls{display:flex;align-items:center;gap:13px;flex-wrap:wrap}.per-page{display:flex;align-items:center;gap:7px;color:var(--kt-muted);font-size:13px}.per-page select{border:1px solid var(--kt-border);border-radius:8px;background:var(--kt-surface-2);color:var(--kt-text);padding:6px 8px}.compact-pages{display:flex;align-items:center;gap:4px}.compact-pages a,.compact-pages span{min-width:33px;height:33px;padding:0 8px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--kt-border);border-radius:8px;text-decoration:none;color:var(--kt-text);font-size:13px}.compact-pages .active{background:#087cf0;border-color:#087cf0;color:#fff}.compact-pages .disabled{opacity:.4}
@media(max-width:1100px){.patients-toolbar{width:100%}.patient-search{flex:1;min-width:230px}.action-cluster{width:100%}.col-gender,.col-birth{display:none}}
@media(max-width:720px){.patients-title{font-size:26px}.patients-toolbar{display:grid;grid-template-columns:1fr 1fr}.patient-search{grid-column:1/-1;width:100%}.patient-sort{width:100%;min-width:0}.action-cluster{grid-column:1/-1;width:100%}.primary-actions{flex:1}.primary-actions .pbtn{flex:1}.patients-toolbar .pbtn{padding-inline:10px}.patient-table,.patient-table tbody,.patient-table tr,.patient-table td{display:block}.patient-table thead{display:none}.patient-table tr.patient-row{padding:13px 14px;border-bottom:1px solid var(--kt-border)}.patient-table tr.patient-row td{border:0;padding:5px 0;display:flex;justify-content:space-between;gap:14px}.patient-table tr.patient-row td:first-child{display:block;padding-bottom:10px}.patient-table tr.patient-row td[data-label]::before{content:attr(data-label);color:var(--kt-muted);font-size:12px;font-weight:700}.patient-table .col-gender,.patient-table .col-birth{display:flex}.initial-divider td{display:block!important;margin:0;padding:7px 14px!important}.row-actions{width:100%;justify-content:flex-end;padding-top:5px}.patients-footer{align-items:flex-start}.page-controls{width:100%;justify-content:space-between}.compact-pages .page-number{display:none}}
</style>
@endpush

@section('content')
@php
    $listUrl = url()->full();
    $isAlphabetical = in_array($sort, ['last_asc', 'last_desc'], true);
    $pageInitialCounts = $patients->getCollection()->countBy(fn ($patient) => strtoupper(substr(trim((string)$patient->last_name), 0, 1)) ?: '#');
    $availableSet = array_fill_keys($availableInitials, true);
    $queryWithoutPage = request()->except('page');
@endphp
<div class="patients-page">
    <header class="patients-head">
        <div><h1 class="patients-title">Patients</h1><p class="patients-subtitle">Manage patient records</p></div>
        <form class="patients-toolbar" method="GET" action="{{ route('staff.patients.index') }}">
            <div class="patient-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="q" value="{{ $q }}" placeholder="Search patients" aria-label="Search patients">
                @if($q !== '')<a class="clear-search" href="{{ route('staff.patients.index', request()->except(['q','page'])) }}" aria-label="Clear search"><i class="fa-solid fa-xmark"></i></a>@endif
            </div>
            <select class="patient-sort" name="sort" aria-label="Sort patients" onchange="if(event.isTrusted)this.form.submit()">
                <option value="last_asc" @selected($sort==='last_asc')>Last name A–Z</option><option value="last_desc" @selected($sort==='last_desc')>Last name Z–A</option>
                <option value="newest" @selected($sort==='newest')>Newest added</option><option value="oldest" @selected($sort==='oldest')>Oldest added</option><option value="recent_visit" @selected($sort==='recent_visit')>Most recent visit</option>
            </select>
            <input type="hidden" name="initial" value="{{ $initial }}"><input type="hidden" name="per_page" value="{{ $perPage }}">
            <div class="action-cluster"><a class="pbtn" href="{{ route('staff.patients.index') }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            <span class="primary-actions">
                <a class="pbtn strong" href="{{ route('staff.records.index', ['return'=>$listUrl]) }}"><i class="fa-solid fa-clock-rotate-left"></i> Past Records Entry</a>
                <a class="pbtn primary" href="{{ route('staff.patients.create', ['return'=>$listUrl]) }}"><i class="fa-solid fa-plus"></i> Add Patient</a>
            </div>
            <div class="dropdown">
                <button class="pbtn icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More patient actions"><i class="fa-solid fa-ellipsis"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" type="button" id="patientImportButton"><i class="fa-solid fa-cloud-arrow-up"></i> Import patients</button></li>
                    <li><a class="dropdown-item" href="{{ route('staff.patients.export') }}"><i class="fa-solid fa-file-export"></i> Export patients</a></li>
                </ul>
            </div>
            </span>
        </form>
        <form id="patientImportForm" action="{{ route('staff.patients.import') }}" method="POST" enctype="multipart/form-data" hidden>@csrf<input type="hidden" name="return" value="{{ $listUrl }}"><input id="patientImportFile" type="file" name="file" accept=".xlsx,.xls,.csv" required></form>
    </header>

    <nav class="alphabet" aria-label="Filter by last-name initial">
        <a class="alpha-link all {{ $initial===''?'active':'' }}" href="{{ route('staff.patients.index', array_merge(request()->except(['initial','page']), ['initial'=>''])) }}">All</a>
        @foreach(range('A','Z') as $letter)
            @if(isset($availableSet[$letter]))<a class="alpha-link {{ $initial===$letter?'active':'' }}" href="{{ route('staff.patients.index', array_merge(request()->except(['initial','page']), ['initial'=>$letter])) }}">{{ $letter }}</a>
            @else<span class="alpha-link disabled" aria-disabled="true">{{ $letter }}</span>@endif
        @endforeach
    </nav>

    <section class="patient-panel">
        @if($patients->isEmpty())
            <div class="patient-empty"><i class="fa-regular fa-folder-open"></i><h3>{{ $q!=='' || $initial!=='' ? 'No patients match your filters.' : 'No patients yet.' }}</h3><p>{{ $q!=='' || $initial!=='' ? 'Try another search or clear the selected initial.' : 'Add the first patient to begin.' }}</p>@if($q!=='' || $initial!=='')<a class="pbtn" href="{{ route('staff.patients.index') }}">Reset Filters</a>@else<a class="pbtn primary" href="{{ route('staff.patients.create',['return'=>$listUrl]) }}">Add Patient</a>@endif</div>
        @else
        <div class="patient-table-wrap"><table class="patient-table"><thead><tr><th>Patient</th><th class="col-gender">Gender</th><th class="col-birth">Birthdate / Age</th><th>Contact</th><th>Last Visit</th><th class="text-end">Action</th></tr></thead><tbody>
            @php
                $currentInitial = null;
            @endphp
            @foreach($patients as $patient)
                @php
                    $rowInitial = strtoupper(substr(trim((string)$patient->last_name), 0, 1)) ?: '#';
                    $birthdate = $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate) : null;
                    $firstInitial = strtoupper(substr(trim((string)$patient->first_name),0,1));
                    $lastInitial = strtoupper(substr(trim((string)$patient->last_name),0,1));
                @endphp
                @if($isAlphabetical && $currentInitial !== $rowInitial)
                    @php($currentInitial = $rowInitial)
                    <tr class="initial-divider"><td colspan="6">{{ $rowInitial }} · {{ $pageInitialCounts[$rowInitial] }} {{ $pageInitialCounts[$rowInitial]===1?'patient':'patients' }} on this page</td></tr>
                @endif
                <tr class="patient-row">
                    <td><div class="patient-person"><div class="patient-avatar">{{ $firstInitial }}{{ $lastInitial }}</div><div><a class="patient-name" href="{{ route('staff.patients.show', ['patient'=>$patient->id,'return'=>$listUrl]) }}">{{ $patient->last_name }}, {{ $patient->first_name }}{{ $patient->middle_name ? ' '.$patient->middle_name : '' }}</a><div class="patient-meta">Added {{ optional($patient->created_at)->format('M d, Y') ?? '—' }}</div></div></div></td>
                    <td class="col-gender" data-label="Gender"><span class="gender-pill">{{ $patient->gender ?: 'Not specified' }}</span></td>
                    <td class="col-birth" data-label="Birthdate"><div>{{ $birthdate?->format('M d, Y') ?? '—' }}@if($birthdate)<div class="cell-sub">{{ $birthdate->age }} years old</div>@endif</div></td>
                    <td data-label="Contact"><div>{{ $patient->contact_number ?: 'No contact number' }}@if($patient->email)<div class="cell-sub">{{ $patient->email }}</div>@endif</div></td>
                    <td data-label="Last Visit"><div>{{ $patient->visits_max_visit_date ? \Carbon\Carbon::parse($patient->visits_max_visit_date)->format('M d, Y') : 'No visits yet' }}</div></td>
                    <td><div class="row-actions"><a class="pbtn" href="{{ route('staff.patients.show',['patient'=>$patient->id,'return'=>$listUrl]) }}"><i class="fa-regular fa-eye"></i> View</a><div class="dropdown row-menu"><button class="pbtn icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for {{ $patient->first_name }} {{ $patient->last_name }}"><i class="fa-solid fa-ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('staff.patients.show',['patient'=>$patient->id,'return'=>$listUrl]) }}"><i class="fa-regular fa-eye"></i> View patient</a></li>
                        <li><a class="dropdown-item" href="{{ route('staff.patients.edit',['patient'=>$patient->id,'return'=>$listUrl]) }}"><i class="fa-regular fa-pen-to-square"></i> Edit details</a></li>
                        <li><a class="dropdown-item" href="{{ route('staff.records.index',['patient_id'=>$patient->id,'return'=>$listUrl]) }}"><i class="fa-solid fa-clock-rotate-left"></i> Enter past records</a></li>
                        <li><a class="dropdown-item" href="{{ route('staff.payments.index',['open_record'=>1,'patient_id'=>$patient->id,'return'=>$listUrl]) }}"><i class="fa-solid fa-receipt"></i> Record payment</a></li>
                        <li><hr class="dropdown-divider"></li><li><form id="delete-patient-{{ $patient->id }}" action="{{ route('staff.patients.destroy',$patient) }}" method="POST">@csrf @method('DELETE')<input type="hidden" name="return" value="{{ $listUrl }}"><button class="dropdown-item delete" type="button" data-confirm="Move this patient out of the active list?" data-confirm-title="Delete patient" data-confirm-yes="Delete" data-confirm-form="#delete-patient-{{ $patient->id }}"><i class="fa-regular fa-trash-can"></i> Delete patient</button></form></li>
                    </ul></div></div></td>
                </tr>
            @endforeach
        </tbody></table></div>
        <footer class="patients-footer"><div class="page-summary">Showing {{ number_format($patients->firstItem()) }}–{{ number_format($patients->lastItem()) }} of {{ number_format($patients->total()) }} patients</div><div class="page-controls">
            <form class="per-page" method="GET"><span>Rows per page</span>@foreach(request()->except(['per_page','page']) as $key=>$value)@if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<select name="per_page" onchange="if(event.isTrusted)this.form.submit()">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected($perPage===$size)>{{ $size }}</option>@endforeach</select></form>
            @if($patients->hasPages())<nav class="compact-pages" aria-label="Patient pages"><a class="{{ $patients->onFirstPage()?'disabled':'' }}" href="{{ $patients->previousPageUrl() ?: '#' }}" aria-label="Previous page"><i class="fa-solid fa-chevron-left"></i></a>@foreach($patients->getUrlRange(max(1,$patients->currentPage()-2),min($patients->lastPage(),$patients->currentPage()+2)) as $page=>$url)<a class="page-number {{ $page===$patients->currentPage()?'active':'' }}" href="{{ $url }}">{{ $page }}</a>@endforeach<a class="{{ !$patients->hasMorePages()?'disabled':'' }}" href="{{ $patients->nextPageUrl() ?: '#' }}" aria-label="Next page"><i class="fa-solid fa-chevron-right"></i></a></nav>@endif
        </div></footer>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const button=document.getElementById('patientImportButton'),file=document.getElementById('patientImportFile'),form=document.getElementById('patientImportForm');
    const scrollKey='kt-patients-scroll:'+window.location.href;
    try{const saved=JSON.parse(sessionStorage.getItem(scrollKey)||'null');if(saved&&Date.now()-saved.time<600000)requestAnimationFrame(()=>window.scrollTo(0,Number(saved.y)||0))}catch(e){}
    const remember=()=>{try{sessionStorage.setItem(scrollKey,JSON.stringify({y:window.scrollY,time:Date.now()}))}catch(e){}};
    document.querySelectorAll('a[href*="return="],button[data-confirm]').forEach(el=>el.addEventListener('click',remember));
    button?.addEventListener('click',()=>{remember();file?.click()});
    file?.addEventListener('change',()=>{if(file.files?.length)form.submit()});
    document.querySelectorAll('.row-menu').forEach(menu=>{const toggle=menu.querySelector('[data-bs-toggle="dropdown"]');menu.addEventListener('hidden.bs.dropdown',()=>toggle?.focus())});
});
</script>
@endpush
