@extends('layout')
@section('title', isset($license->id) ? 'Edit License - Rusan' : 'Add New License - Rusan')

@section('content')
@php
    $sessionroles = session('roles');
    $roleArray    = explode(',', ($sessionroles->permissions ?? ''));
@endphp

<style>
    /* ── Design System (matching manageInvoice) ── */
    .ml-card { background:#fff; border:1px solid #e8eaed; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,.02); }
    .ml-card-header { padding:16px 20px; border-bottom:1px solid #f1f3f4; display:flex; align-items:center; gap:12px; }
    .ml-card-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; }
    .ml-card-title { font-size:1rem; font-weight:700; color:#202124; margin:0; }
    .ml-card-sub { font-size:.72rem; color:#80868b; display:block; margin-top:1px; font-weight:400; }
    .ml-card-body { padding:20px; }
    .ml-label { display:block; font-size:.75rem; font-weight:500; color:#5f6368; margin-bottom:6px; }

    /* cf- input boxes */
    .cf-input-box {
        display:flex; align-items:center;
        border:1.5px solid #d1d5db; border-radius:8px;
        background:#fff; overflow:hidden; height:42px;
        transition:border-color .15s, box-shadow .15s;
    }
    .cf-input-box:focus-within { border-color:#163f7a; box-shadow:0 0 0 3px rgba(22, 63, 122,.08); }
    .cf-icon {
        display:flex; align-items:center; justify-content:center;
        width:38px; min-width:38px; height:100%;
        color:#163f7a; font-size:1.05rem;
        border-right:1.5px solid #e8eaed; background:#f8fdfd; flex-shrink:0;
    }
    .cf-input-box input,
    .cf-input-box select {
        flex:1; border:none !important; outline:none !important;
        box-shadow:none !important; background:transparent;
        font-size:.875rem; color:#202124; padding:0 10px; height:100%;
        appearance:none; -webkit-appearance:none; width:100%; min-width:0;
    }
    .cf-input-box select {
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right 10px center; padding-right:28px;
    }
    .cf-input-box input::placeholder { color:#9aa0a6; }
    .cf-input-box.cf-textarea-box { height:auto; align-items:flex-start; }
    .cf-input-box.cf-textarea-box .cf-icon { height:42px; align-self:flex-start; }
    .cf-input-box.cf-textarea-box textarea {
        flex:1; border:none !important; outline:none !important;
        box-shadow:none !important; background:transparent;
        font-size:.875rem; color:#202124; padding:10px; resize:none; width:100%;
    }

    /* project select (full width, no selectpicker needed — it has data-live-search) */
    .cf-select-wrap {
        display:flex; align-items:center;
        border:1.5px solid #d1d5db; border-radius:8px;
        background:#fff; overflow:hidden; min-height:42px;
        transition:border-color .15s, box-shadow .15s;
    }
    .cf-select-wrap:focus-within { border-color:#163f7a; box-shadow:0 0 0 3px rgba(22, 63, 122,.08); }
    .cf-select-wrap .cf-icon { border-right:1.5px solid #e8eaed; height:42px; flex-shrink:0; }
    .cf-select-wrap select { flex:1; border:none !important; outline:none !important;
        box-shadow:none !important; background:transparent; font-size:.875rem;
        color:#202124; padding:0 10px; height:42px;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right 10px center; padding-right:28px;
        appearance:none; -webkit-appearance:none;
    }

    /* Bootstrap-Select overrides for project dropdown */
    .cf-select-wrap .bootstrap-select { flex:1; min-width:0; border:none !important; }
    .cf-select-wrap .bootstrap-select > .dropdown-toggle {
        border:none !important; background:transparent !important;
        box-shadow:none !important; outline:none !important;
        font-size:.875rem; color:#202124 !important;
        height:42px !important; width:100%; text-align:left;
        padding:0 10px; border-radius:0 !important;
    }
    .cf-select-wrap .bootstrap-select > .dropdown-toggle:focus { outline:none !important; box-shadow:none !important; }

    /* Key + generate row */
    .lic-key-row { display:flex; align-items:center; gap:8px; }
    .lic-key-row .cf-input-box { flex:1; }
    .btn-gen-key {
        height:42px; padding:0 16px; border-radius:8px;
        border:1.5px solid #163f7a; background:#163f7a; color:#fff;
        font-size:.82rem; font-weight:600; cursor:pointer; white-space:nowrap;
        display:flex; align-items:center; gap:5px; transition:background .15s;
    }
    .btn-gen-key:hover { background:#004e4e; }

    /* Action bar */
    .inv-btn-save {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 22px; border-radius:9px; font-size:.875rem; font-weight:600;
        border:none; background:#163f7a; color:#fff; cursor:pointer; transition:background .15s;
        text-decoration:none;
    }
    .inv-btn-save:hover { background:#004e4e; color:#fff; }
    .inv-btn-draft {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 18px; border-radius:9px; font-size:.875rem; font-weight:600;
        border:1.5px solid #d1d5db; background:#fff; color:#5f6368;
        cursor:pointer; transition:all .15s; text-decoration:none;
    }
    .inv-btn-draft:hover { background:#f5f5f5; border-color:#bbb; color:#202124; }

    /* Preload badge */
    .pv-preload-notice {
        background:linear-gradient(135deg,rgba(22, 63, 122,.07),rgba(0,163,163,.05));
        border:1px solid rgba(22, 63, 122,.2); border-radius:10px;
        padding:10px 16px; font-size:.82rem; color:#163f7a; font-weight:500;
        display:flex; align-items:center; gap:8px; margin-bottom:20px;
    }
</style>

<section class="task__section">
    @include('inc.header', ['title' => !empty($license->id) ? 'Edit License' : 'Add License'])

    <div class="dash-container">

        {{-- ── Toolbar ── --}}
        <div class="leads-toolbar mb-3">
            <div class="leads-toolbar-left gap-3">
                <a href="/licensing" class="btn kb-action-btn"
                   style="width:34px;height:34px;background:#f1f3f4;color:#5f6368;" title="Back">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <div>
                    <span class="lb-page-count">
                        <i class="bx {{ !empty($license->id) ? 'bx-edit' : 'bx-plus-circle' }}"></i>
                        {{ !empty($license->id) ? 'Edit License' : 'Add New License' }}
                    </span>
                    @if(!empty($preloadProject))
                        <span class="ms-2" style="font-size:.78rem;color:#163f7a;font-weight:600;">
                            <i class="bx bx-link-alt"></i> Pre-filled from: {{ $preloadProject->project_name }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="leads-toolbar-right gap-2">
                <a href="/licensing" class="inv-btn-draft">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" form="licenseForm" class="inv-btn-save">
                    <i class="bx bx-save"></i> Save License
                </button>
            </div>
        </div>

        <form id="licenseForm" action="{{ route('manageLicense') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $license->id ?? '' }}">
            <input type="hidden" name="previous_url" value="{{ $previous_url ?? url()->previous() }}">

            {{-- ── Project Link ── --}}
            <div class="ml-card mb-4">
                <div class="ml-card-header">
                    <div class="ml-card-icon" style="background:rgba(22, 63, 122,.10);color:#163f7a;">
                        <i class="bx bx-folder-open"></i>
                    </div>
                    <div>
                        <h6 class="ml-card-title">Linked Project</h6>
                        <span class="ml-card-sub">Select the project this license is assigned to</span>
                    </div>
                </div>
                <div class="ml-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="ml-label">Project <span class="text-danger">*</span></label>
                            <div class="cf-select-wrap">
                                <span class="cf-icon"><i class="bx bx-briefcase"></i></span>
                                <select class="selectpicker" id="project_id" name="project_id"
                                        data-live-search="true" data-width="100%" data-container="body">
                                    <option value="">— Search for a project —</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}"
                                                data-name="{{ $proj->client_name ?? '' }}"
                                                data-company="{{ $proj->company ?? '' }}"
                                                data-mobile="{{ $proj->mob ?? '' }}"
                                                data-email="{{ $proj->email ?? '' }}"
                                                data-projectname="{{ $proj->name ?? '' }}"
                                                data-type="{{ $proj->type ?? '' }}"
                                                data-cost="{{ $proj->amount ?? '' }}"
                                                data-website="{{ $proj->deployment_url ?? '' }}"
                                                data-note="{{ $proj->note ?? '' }}"
                                                @if(
                                                    old('project_id', $license->project_id ?? '') == $proj->id
                                                    || (!empty($preloadProject) && $preloadProject->project_id == $proj->id)
                                                ) selected @endif>
                                            {{ $proj->client_name ?? '—' }} — {{ $proj->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if(!empty($preloadProject))
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="pv-preload-notice w-100">
                                <i class="bx bx-info-circle fs-5"></i>
                                All fields below have been pre-filled from the selected project.
                                You can modify them before saving.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Client Details ── --}}
            <div class="ml-card mb-4">
                <div class="ml-card-header">
                    <div class="ml-card-icon" style="background:rgba(26,115,232,.10);color:#1a73e8;">
                        <i class="bx bx-user-pin"></i>
                    </div>
                    <div>
                        <h6 class="ml-card-title">Client Details</h6>
                        <span class="ml-card-sub">Auto-filled when a project is selected</span>
                    </div>
                </div>
                <div class="ml-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="ml-label">Name <span class="text-danger">*</span></label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-user"></i></span>
                                <input type="text" id="name" name="name"
                                       value="{{ old('name', $license->client_name ?? $preloadProject->client_name ?? '') }}"
                                       placeholder="Client Name" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="ml-label">Company</label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-building"></i></span>
                                <input type="text" id="company" name="company"
                                       value="{{ old('company', $license->company ?? $preloadProject->client_company ?? '') }}"
                                       placeholder="Company Name">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="ml-label">Mobile <span class="text-danger">*</span></label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-phone"></i></span>
                                <input type="text" id="mobile" name="mobile"
                                       value="{{ old('mobile', $license->mob ?? $preloadProject->client_mob ?? '') }}"
                                       placeholder="Mobile Number" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="ml-label">Email <span class="text-danger">*</span></label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-envelope"></i></span>
                                <input type="email" id="email" name="email"
                                       value="{{ old('email', $license->email ?? $preloadProject->client_email ?? '') }}"
                                       placeholder="Email Address" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Project Details ── --}}
            <div class="ml-card mb-4">
                <div class="ml-card-header">
                    <div class="ml-card-icon" style="background:rgba(52,168,83,.10);color:#163f7a;">
                        <i class="bx bx-git-repo-forked"></i>
                    </div>
                    <div>
                        <h6 class="ml-card-title">Project Details</h6>
                        <span class="ml-card-sub">Name, type, cost and deployment info</span>
                    </div>
                </div>
                <div class="ml-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="ml-label">Project Name <span class="text-danger">*</span></label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-folder"></i></span>
                                <input type="text" id="project_name" name="project_name"
                                       value="{{ old('project_name', $license->project_name ?? $preloadProject->project_name ?? '') }}"
                                       placeholder="Project Name" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="ml-label">Type</label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-category"></i></span>
                                <input type="text" id="type" name="type"
                                       value="{{ old('type', $license->type ?? $preloadProject->project_type ?? '') }}"
                                       placeholder="e.g. SaaS, Web">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="ml-label">Cost (₹)</label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-rupee"></i></span>
                                <input type="number" step="0.01" id="cost" name="cost"
                                       value="{{ old('cost', $license->amount ?? $preloadProject->project_amount ?? '') }}"
                                       placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="ml-label">Website / Deployment URL <span class="text-danger">*</span></label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-globe"></i></span>
                                <input type="url" id="website" name="website"
                                       value="{{ old('website', $license->deployment_url ?? $preloadProject->deployment_url ?? '') }}"
                                       placeholder="https://" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="ml-label">Note</label>
                            <div class="cf-input-box cf-textarea-box">
                                <span class="cf-icon"><i class="bx bx-note"></i></span>
                                <textarea id="note" name="note" rows="2"
                                          placeholder="Additional notes about this project or license">{{ old('note', $license->note ?? $preloadProject->project_note ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── License Details ── --}}
            <div class="ml-card mb-4">
                <div class="ml-card-header">
                    <div class="ml-card-icon" style="background:rgba(242,153,0,.10);color:#f29900;">
                        <i class="bx bx-key"></i>
                    </div>
                    <div>
                        <h6 class="ml-card-title">License Configuration</h6>
                        <span class="ml-card-sub">License key, technology stack, expiry &amp; status</span>
                    </div>
                </div>
                <div class="ml-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="ml-label">License Key <span class="text-danger">*</span></label>
                            <div class="lic-key-row">
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-lock-alt"></i></span>
                                    <input type="text" id="license_key" name="license_key"
                                           value="{{ old('license_key', $license->eselicense_key ?? '') }}"
                                           placeholder="Auto-generated or enter manually" required>
                                </div>
                                <button type="button" class="btn-gen-key" id="generate_license_key">
                                    <i class="bx bx-refresh"></i> Generate
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="ml-label">Technology Stack <span class="text-danger">*</span></label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-code-alt"></i></span>
                                <select id="technology_stack" name="technology_stack" required>
                                    <option value="">Select Stack…</option>
                                    @foreach(['Laravel','WordPress','Core PHP','React','Vue.js','Node.js','Flutter','Angular','Next.js','Other'] as $stack)
                                        <option value="{{ $stack }}"
                                            {{ old('technology_stack', $license->technology_stack ?? '') == $stack ? 'selected' : '' }}>
                                            {{ $stack }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="ml-label">Expiry Date <span class="text-danger">*</span></label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-calendar-check"></i></span>
                                <input type="date" id="expiry_date" name="expiry_date"
                                       value="{{ old('expiry_date', isset($license->expiry_date) ? \Carbon\Carbon::parse($license->expiry_date)->format('Y-m-d') : '') }}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="ml-label">Status</label>
                            <div class="cf-input-box">
                                <span class="cf-icon"><i class="bx bx-toggle-right"></i></span>
                                <select id="status" name="status">
                                    <option value="active"   {{ old('status', $license->status ?? 'active') == 'active'   ? 'selected' : '' }}>Active</option>
                                    <option value="blocked"  {{ old('status', $license->status ?? '')       == 'blocked'  ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Footer Actions ── --}}
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="/licensing" class="inv-btn-draft">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="inv-btn-save">
                    <i class="bx bx-save"></i> Save License
                </button>
            </div>

        </form>
    </div>{{-- /dash-container --}}
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Project select auto-fill on change ──────────────────────────
    const projectSelect = document.getElementById('project_id');

    function fillFromOption(opt) {
        if (!opt || !opt.value) return;
        const get = attr => opt.getAttribute(attr) || '';
        document.getElementById('name').value         = get('data-name');
        document.getElementById('company').value      = get('data-company');
        document.getElementById('mobile').value       = get('data-mobile');
        document.getElementById('email').value        = get('data-email');
        document.getElementById('project_name').value = get('data-projectname');
        document.getElementById('type').value         = get('data-type');
        document.getElementById('cost').value         = get('data-cost');
        document.getElementById('website').value      = get('data-website');
        document.getElementById('note').value         = get('data-note');
    }

    if (projectSelect) {
        projectSelect.addEventListener('change', function() {
            fillFromOption(this.options[this.selectedIndex]);
        });

        // ── Pre-populate on page load if project_id came from URL ──
        @if(!empty($preloadProject) && !empty($license) === false)
        setTimeout(() => {
            // selectpicker may wrap the native select — trigger Bootstrap-Select refresh
            if (typeof $.fn.selectpicker !== 'undefined') {
                $('#project_id').selectpicker('refresh');
                $('#project_id').next('.bootstrap-select').css({'flex':'1','min-width':'0','border':'none'});
            }
            // Auto-fill all fields from the pre-selected option
            const selectedOpt = projectSelect.options[projectSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                fillFromOption(selectedOpt);
            }
        }, 300);
        @else
        setTimeout(() => {
            if (typeof $.fn.selectpicker !== 'undefined') {
                $('#project_id').selectpicker('refresh');
                $('#project_id').next('.bootstrap-select').css({'flex':'1','min-width':'0','border':'none'});
            }
        }, 300);
        @endif
    }

    // ── License Key Generator ──────────────────────────────────────
    const licenseKeyInput = document.getElementById('license_key');
    const generateBtn     = document.getElementById('generate_license_key');

    function generateLicenseKey() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let key = '';
        for (let i = 0; i < 4; i++) {
            if (i > 0) key += '-';
            for (let j = 0; j < 4; j++) {
                key += chars.charAt(Math.floor(Math.random() * chars.length));
            }
        }
        licenseKeyInput.value = key;

        // Flash the generate button
        generateBtn.innerHTML = '<i class="bx bx-check"></i> Copied!';
        generateBtn.style.background = '#163f7a';
        generateBtn.style.borderColor = '#163f7a';
        setTimeout(() => {
            generateBtn.innerHTML = '<i class="bx bx-refresh"></i> Generate';
            generateBtn.style.background = '';
            generateBtn.style.borderColor = '';
        }, 1500);
    }

    generateBtn.addEventListener('click', generateLicenseKey);

    // Auto-generate key for NEW licenses (no existing key)
    if (!licenseKeyInput.value) {
        generateLicenseKey();
    }
});
</script>

@endsection
