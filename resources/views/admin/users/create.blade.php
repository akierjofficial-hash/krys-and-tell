@extends('layouts.admin')

@push('styles')
<style>
    /* =========
       Page Skin
       ========= */
    .uwrap{
        padding: 12px 0 18px;
        background:
            radial-gradient(900px 420px at 12% -10%, rgba(37,99,235,.12), transparent 60%),
            radial-gradient(900px 420px at 92% 12%, rgba(124,58,237,.10), transparent 55%),
            radial-gradient(900px 520px at 40% 110%, rgba(34,197,94,.08), transparent 55%);
        border-radius: 18px;
    }

    .head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 8px 0 14px;
        padding: 0 2px;
    }
    .head h2{
        margin:0;
        font-weight: 950;
        letter-spacing: -.55px;
        font-size: 24px;
        color: var(--text);
        line-height: 1.1;
    }
    .sub{
        margin-top: 5px;
        color: var(--muted);
        font-weight: 900;
        font-size: 13px;
    }

    /* =========
       Glass Card
       ========= */
    .glass{
        position:relative;
        overflow:hidden;
        border-radius: 22px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,23,42,.10);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        backdrop-filter: blur(10px);
        transition: .18s ease;
    }
    .glass::before{
        content:"";
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(900px 260px at 18% 0%, rgba(37,99,235,.14), transparent 55%),
            radial-gradient(900px 260px at 82% 0%, rgba(124,58,237,.12), transparent 60%);
        opacity:.95;
        pointer-events:none;
    }
    .glass:hover{
        transform: translateY(-1px);
        box-shadow: 0 22px 44px rgba(15,23,42,.14);
    }
    html[data-theme="dark"] .glass{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.45);
    }
    html[data-theme="dark"] .glass:hover{
        box-shadow: 0 26px 60px rgba(0,0,0,.55);
    }
    .glass-inner{ position:relative; z-index:1; padding: 16px; }

    /* =========
       Buttons
       ========= */
    .btnx{
        border-radius: 14px;
        font-weight: 950;
        padding: 10px 14px;
        border: 1px solid rgba(148,163,184,.22);
        box-shadow: 0 14px 26px rgba(15, 23, 42, .08);
        transition: .15s ease;
        background: rgba(255,255,255,.72);
        color: var(--text);
    }
    html[data-theme="dark"] .btnx{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
        color: var(--text);
        box-shadow: 0 14px 28px rgba(0,0,0,.35);
    }
    .btnx:hover{ transform: translateY(-1px); }

    .btn-cta{
        border: 0 !important;
        background: linear-gradient(135deg, rgba(37,99,235,1), rgba(124,58,237,.95)) !important;
        color: #fff !important;
    }

    /* =========
       Sections
       ========= */
    .sec{
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(255,255,255,.70);
        padding: 14px;
        transition: .15s ease;
    }
    .sec:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 30px rgba(15,23,42,.10);
    }
    html[data-theme="dark"] .sec{
        background: rgba(2,6,23,.28);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 14px 28px rgba(0,0,0,.30);
    }

    .sec-title{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        margin-bottom: 10px;
        font-weight: 950;
        letter-spacing: -.25px;
        color: var(--text);
    }
    .sec-title .mini{
        font-size: 12px;
        font-weight: 950;
        color: var(--muted);
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.08);
        white-space:nowrap;
    }

    /* =========
       Form controls
       ========= */
    .label{
        font-weight: 950;
        font-size: 12px;
        letter-spacing: .2px;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .form-control,
    .form-select{
        border-radius: 14px !important;
        font-weight: 900 !important;
        border: 1px solid rgba(148,163,184,.22) !important;
        background: rgba(255,255,255,.78) !important;
        color: var(--text) !important;
        padding: 11px 12px !important;
        box-shadow: 0 12px 18px rgba(15,23,42,.06);
        transition: .15s ease;
    }
    .form-control:focus,
    .form-select:focus{
        box-shadow: 0 18px 30px rgba(37,99,235,.14) !important;
        border-color: rgba(37,99,235,.40) !important;
        outline: none !important;
    }
    html[data-theme="dark"] .form-control,
    html[data-theme="dark"] .form-select{
        background: rgba(2,6,23,.32) !important;
        border-color: rgba(148,163,184,.18) !important;
        color: var(--text) !important;
        box-shadow: 0 14px 24px rgba(0,0,0,.35);
    }

    .hint{
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
        font-weight: 900;
    }

    /* Password input group */
    .pw-wrap{ position: relative; }
    .pw-toggle{
        position:absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border-radius: 12px;
        border: 1px solid rgba(148,163,184,.20);
        background: rgba(148,163,184,.10);
        color: var(--text);
        font-weight: 950;
        padding: 6px 10px;
        cursor: pointer;
        transition: .15s ease;
        user-select:none;
    }
    .pw-toggle:hover{ transform: translateY(-50%) translateY(-1px); }
    html[data-theme="dark"] .pw-toggle{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.18);
    }

    /* Strength */
    .strength{
        margin-top: 10px;
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .strength-bar{
        flex: 1;
        height: 10px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.10);
        overflow:hidden;
        position:relative;
    }
    .strength-fill{
        height: 100%;
        width: 0%;
        border-radius: 999px;
        background: rgba(148,163,184,.50);
        transition: .18s ease;
    }
    .strength-label{
        font-size: 12px;
        font-weight: 950;
        color: var(--muted);
        min-width: 92px;
        text-align:right;
        white-space:nowrap;
    }

    /* Checkbox */
    .form-check{
        background: rgba(148,163,184,.08);
        border: 1px solid rgba(148,163,184,.18);
        padding: 12px 12px;
        border-radius: 16px;
        display:flex;
        align-items:center;
        gap:10px;
        min-height: 48px;
    }
    html[data-theme="dark"] .form-check{
        background: rgba(2,6,23,.28);
    }
    .form-check-input{
        width: 18px;
        height: 18px;
        margin-top: 0;
        cursor: pointer;
    }
    .form-check-label{
        font-weight: 950;
        color: var(--text);
        cursor: pointer;
    }

    /* =========
       Preview Card
       ========= */
    .preview{
        position: sticky;
        top: 18px;
    }
    @media (max-width: 991.98px){
        .preview{ position: static; }
    }

    .avatar{
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display:grid;
        place-items:center;
        font-weight: 950;
        letter-spacing: -.4px;
        color: #fff;
        background: linear-gradient(135deg, rgba(37,99,235,1), rgba(124,58,237,.95));
        box-shadow: 0 18px 30px rgba(37,99,235,.18);
        flex: 0 0 auto;
    }

    .role-chip{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.10);
        color: var(--text);
        white-space:nowrap;
    }
    .role-dot{
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: rgba(37,99,235,.95);
    }

    .krow{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:10px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(148,163,184,.18);
    }
    .krow:last-child{ border-bottom: 0; padding-bottom: 0; }
    .klabel{ font-size: 12px; color: var(--muted); font-weight: 950; }
    .kval{ font-weight: 950; color: var(--text); text-align:right; max-width: 70%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

    /* Alerts */
    .alertx{
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,.18);
        box-shadow: 0 12px 22px rgba(15,23,42,.08);
        font-weight: 900;
    }
    .alertx ul{ margin: 0; padding-left: 18px; }
    .alertx li{ font-weight: 800; }
</style>
@endpush

@section('content')
<div class="uwrap">

    <div class="head">
        <div>
            <h2>Create User</h2>
            <div class="sub">Add a new staff or admin account</div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.users.index') }}" class="btn btnx">
                <i class="fa fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alertx">
            <div style="font-weight:950;">Please fix the following:</div>
            <ul class="mt-2">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        {{-- LEFT: FORM --}}
        <div class="col-lg-8">
            <div class="glass">
                <div class="glass-inner">

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="sec mb-3">
                            <div class="sec-title">
                                <span><i class="fa fa-id-card me-2"></i> Account Details</span>
                                <span class="mini">Required</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="label">Name</div>
                                    <input
                                        id="nameInput"
                                        class="form-control"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="e.g., Kier Dela Cruz"
                                        required
                                    >
                                    <div class="hint">This is the display name used across admin & staff panels.</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="label">Email</div>
                                    <input
                                        id="emailInput"
                                        class="form-control"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="e.g., kier@email.com"
                                        required
                                    >
                                    <div class="hint">Used for login and audit logs.</div>
                                </div>
                            </div>
                        </div>

                        <div class="sec mb-3">
                            <div class="sec-title">
                                <span><i class="fa fa-shield-halved me-2"></i> Access & Security</span>
                                <span class="mini">Set role + password</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="label">Password</div>
                                    <div class="pw-wrap">
                                        <input
                                            id="passwordInput"
                                            class="form-control"
                                            type="password"
                                            name="password"
                                            placeholder="Create a strong password"
                                            required
                                        >
                                        <span class="pw-toggle" id="pwToggle">
                                            <i class="fa fa-eye me-1"></i> Show
                                        </span>
                                    </div>

                                    <div class="strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="pwFill"></div>
                                        </div>
                                        <div class="strength-label" id="pwLabel">—</div>
                                    </div>

                                    <div class="hint">Tip: use letters, numbers, and symbols.</div>
                                </div>

                                <div class="col-md-3">
                                    <div class="label">Role</div>
                                    <select id="roleSelect" class="form-select" name="role" required>
                                        <option value="staff" @selected(old('role') === 'staff')>Staff</option>
                                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                    </select>
                                    <div class="hint" id="roleHint">Staff can manage daily operations.</div>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="form-check w-100">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            id="active"
                                            @checked(old('is_active', 1))
                                        >
                                        <label class="form-check-label" for="active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btnx btn-cta" type="submit">
                                <i class="fa fa-save me-2"></i> Create User
                            </button>

                            <a href="{{ route('admin.users.index') }}" class="btn btnx">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- RIGHT: LIVE PREVIEW --}}
        <div class="col-lg-4">
            <div class="preview">
                <div class="glass">
                    <div class="glass-inner">
                        <div class="sec">
                            <div class="sec-title" style="margin-bottom:12px;">
                                <span><i class="fa fa-user me-2"></i> Preview</span>
                                <span class="mini">Live</span>
                            </div>

                            <div class="d-flex align-items-center gap-12" style="gap:12px;">
                                <div class="avatar" id="avatarText">KT</div>
                                <div style="min-width:0;">
                                    <div style="font-weight:950; font-size:16px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" id="pvName">
                                        {{ old('name') ?: 'New User' }}
                                    </div>
                                    <div class="sub" style="margin-top:2px;" id="pvEmail">
                                        {{ old('email') ?: 'email@domain.com' }}
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                <span class="role-chip" id="pvRoleChip">
                                    <span class="role-dot" id="pvRoleDot"></span>
                                    <span id="pvRoleText">{{ old('role') ?: 'staff' }}</span>
                                </span>

                                <span class="role-chip" id="pvStatusChip">
                                    <span class="role-dot" style="background: rgba(34,197,94,.95);"></span>
                                    <span id="pvStatusText">{{ old('is_active', 1) ? 'Active' : 'Inactive' }}</span>
                                </span>
                            </div>

                            <div style="margin-top:12px;">
                                <div class="krow">
                                    <div class="klabel">Access Level</div>
                                    <div class="kval" id="pvAccess">Staff Access</div>
                                </div>
                                <div class="krow">
                                    <div class="klabel">Created in</div>
                                    <div class="kval">Admin Panel</div>
                                </div>
                                <div class="krow">
                                    <div class="klabel">Security</div>
                                    <div class="kval" id="pvSecurity">—</div>
                                </div>
                            </div>

                            <div class="hint" style="margin-top:12px;">
                                <i class="fa fa-circle-info me-1"></i>
                                This preview is only UI — actual permissions follow your middleware/role rules.
                            </div>
                        </div>

                        <div class="sec mt-3">
                            <div class="sec-title" style="margin-bottom:10px;">
                                <span><i class="fa fa-lightbulb me-2"></i> Best Practices</span>
                                <span class="mini">Recommended</span>
                            </div>

                            <div class="krow">
                                <div class="klabel">Use strong password</div>
                                <div class="kval"><i class="fa fa-check"></i></div>
                            </div>
                            <div class="krow">
                                <div class="klabel">Set correct role</div>
                                <div class="kval"><i class="fa fa-check"></i></div>
                            </div>
                            <div class="krow">
                                <div class="klabel">Deactivate old accounts</div>
                                <div class="kval"><i class="fa fa-check"></i></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function(){
    const nameInput = document.getElementById('nameInput');
    const emailInput = document.getElementById('emailInput');
    const roleSelect = document.getElementById('roleSelect');
    const activeBox  = document.getElementById('active');
    const pwInput    = document.getElementById('passwordInput');

    const pvName = document.getElementById('pvName');
    const pvEmail = document.getElementById('pvEmail');
    const avatarText = document.getElementById('avatarText');
    const pvRoleText = document.getElementById('pvRoleText');
    const pvRoleDot  = document.getElementById('pvRoleDot');
    const pvAccess   = document.getElementById('pvAccess');
    const roleHint   = document.getElementById('roleHint');

    const pvStatusText = document.getElementById('pvStatusText');

    const pwToggle = document.getElementById('pwToggle');
    const pwFill = document.getElementById('pwFill');
    const pwLabel = document.getElementById('pwLabel');
    const pvSecurity = document.getElementById('pvSecurity');

    function initials(name){
        name = (name || '').trim();
        if (!name) return 'KT';
        const parts = name.split(/\s+/).filter(Boolean);
        const first = parts[0]?.[0] || '';
        const last  = parts.length > 1 ? (parts[parts.length - 1]?.[0] || '') : (parts[0]?.[1] || '');
        const out = (first + last).toUpperCase();
        return out || 'KT';
    }

    function setRoleUI(role){
        const isAdmin = role === 'admin';
        pvRoleText.textContent = role || 'staff';
        pvAccess.textContent = isAdmin ? 'Admin Access' : 'Staff Access';

        // admin = violet, staff = blue
        pvRoleDot.style.background = isAdmin ? 'rgba(124,58,237,.95)' : 'rgba(37,99,235,.95)';
        roleHint.textContent = isAdmin
            ? 'Admin can view/monitor everything in admin side.'
            : 'Staff can manage daily operations (patients, visits, appointments).';
    }

    function setStatusUI(){
        pvStatusText.textContent = activeBox?.checked ? 'Active' : 'Inactive';
    }

    function scorePassword(pw){
        // simple strength scoring (client-side UI only)
        if (!pw) return 0;
        let s = 0;
        if (pw.length >= 8) s += 1;
        if (pw.length >= 12) s += 1;
        if (/[A-Z]/.test(pw)) s += 1;
        if (/[a-z]/.test(pw)) s += 1;
        if (/\d/.test(pw)) s += 1;
        if (/[^A-Za-z0-9]/.test(pw)) s += 1;
        return Math.min(s, 6);
    }

    function applyStrength(){
        const pw = pwInput?.value || '';
        const s = scorePassword(pw);

        const pct = (s / 6) * 100;
        pwFill.style.width = pct + '%';

        let label = '—';
        let color = 'rgba(148,163,184,.55)';

        if (s <= 1) { label = 'Weak'; color = 'rgba(239,68,68,.85)'; }
        else if (s <= 3) { label = 'Okay'; color = 'rgba(245,158,11,.90)'; }
        else if (s <= 5) { label = 'Strong'; color = 'rgba(34,197,94,.85)'; }
        else { label = 'Very Strong'; color = 'rgba(37,99,235,.90)'; }

        pwFill.style.background = color;
        pwLabel.textContent = label;
        pvSecurity.textContent = label;
    }

    // Live bindings
    nameInput?.addEventListener('input', () => {
        pvName.textContent = nameInput.value || 'New User';
        avatarText.textContent = initials(nameInput.value);
    });

    emailInput?.addEventListener('input', () => {
        pvEmail.textContent = emailInput.value || 'email@domain.com';
    });

    roleSelect?.addEventListener('change', () => setRoleUI(roleSelect.value));
    activeBox?.addEventListener('change', setStatusUI);

    pwInput?.addEventListener('input', applyStrength);

    // Show/hide password
    pwToggle?.addEventListener('click', () => {
        const isPw = pwInput.type === 'password';
        pwInput.type = isPw ? 'text' : 'password';
        pwToggle.innerHTML = isPw
            ? '<i class="fa fa-eye-slash me-1"></i> Hide'
            : '<i class="fa fa-eye me-1"></i> Show';
    });

    // Initial paint
    avatarText.textContent = initials(nameInput?.value || '');
    setRoleUI(roleSelect?.value || 'staff');
    setStatusUI();
    applyStrength();
})();
</script>
@endpush
