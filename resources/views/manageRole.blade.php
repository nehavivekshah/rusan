@extends('layout')
@section('title', request()->get('id') ? 'Edit Role - eseCRM' : 'Add Role - eseCRM')

@section('content')
@php
    $isEdit      = !empty(request()->get('id'));
    $features    = array_filter(explode(',', ($roles->features ?? '')));
    $permissions = array_filter(explode(',', ($roles->permissions ?? '')));

    // All modules the app supports — must match sidebar keys exactly
    $modules = [
        'leads'       => ['label' => 'Leads',          'icon' => 'bx-user-voice',        'color' => '#34a853'],
        'clients'     => ['label' => 'Customers',      'icon' => 'bx-briefcase',         'color' => '#1a73e8'],
        'projects'    => ['label' => 'Projects',        'icon' => 'bx-folder-open',       'color' => '#8e24aa'],
        'proposals'   => ['label' => 'Proposals',       'icon' => 'bx-notepad',           'color' => '#ea4335'],
        'invoice'     => ['label' => 'Invoices',        'icon' => 'bx-receipt',           'color' => '#34a853'],
        'contracts'   => ['label' => 'Contracts',       'icon' => 'bx-file-blank',        'color' => '#006666'],
        'recoveries'  => ['label' => 'Recovery',        'icon' => 'bx-alarm-exclamation', 'color' => '#d93025'],
        'tasks'       => ['label' => 'Tasks',           'icon' => 'bx-task',              'color' => '#f9a825'],
        'attendances' => ['label' => 'Attendances',     'icon' => 'bx-calendar-check',    'color' => '#0d47a1'],
        'campaigns'   => ['label' => 'Campaigns',       'icon' => 'bx-broadcast',         'color' => '#f57c00'],
        'automations' => ['label' => 'Automations',     'icon' => 'bx-git-branch',        'color' => '#00838f'],
        'reports'     => ['label' => 'Reports',         'icon' => 'bx-line-chart',        'color' => '#1565c0'],
        'support'     => ['label' => 'Support',         'icon' => 'bx-help-circle',       'color' => '#006666'],
        'users'       => ['label' => 'Users / Staff',   'icon' => 'bx-group',             'color' => '#4a148c'],
        'company'     => ['label' => 'Company Profile', 'icon' => 'bx-building',          'color' => '#37474f'],
        'smtp'        => ['label' => 'SMTP & Email',    'icon' => 'bx-envelope-open',     'color' => '#5f6368'],
        'settings'    => ['label' => 'Role Settings',   'icon' => 'bx-shield-quarter',    'color' => '#006666'],
    ];

    // Permission types per module
    $permTypes = [
        'assign' => ['label' => 'View/Assign', 'icon' => 'bx-user-plus'],
        'add'    => ['label' => 'Add',          'icon' => 'bx-plus-circle'],
        'edit'   => ['label' => 'Edit',         'icon' => 'bx-pencil'],
        'delete' => ['label' => 'Delete',       'icon' => 'bx-trash'],
        'export' => ['label' => 'Export',       'icon' => 'bx-download'],
        'import' => ['label' => 'Import',       'icon' => 'bx-upload'],
    ];

    // Which modules do NOT support import
    $noImport = ['proposals', 'company', 'smtp', 'settings', 'reports', 'automations', 'campaigns', 'support'];
    // Which modules do NOT support export
    $noExport = ['smtp', 'settings', 'automations', 'support'];
    // Which modules do NOT support assign
    $noAssign = ['company', 'smtp', 'settings', 'reports', 'automations', 'campaigns'];
@endphp

<section class="task__section">
    @include('inc.header', ['title' => $isEdit ? 'Edit Role' : 'Add New Role'])

    <div class="dash-container">
        <div class="dash-card rs-form-card">

            {{-- Card Header --}}
            <div class="rs-form-header">
                <div>
                    <p class="rs-form-header-title">
                        <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
                        {{ $isEdit ? 'Edit Role & Permissions' : 'Define New Role & Permissions' }}
                    </p>
                    <p class="rs-form-header-sub">
                        {{ $isEdit ? 'Modify what this role can access across the CRM' : 'Choose a name, then tick module permissions for this role' }}
                    </p>
                </div>
                <a href="/role-settings" class="rs-back-btn">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
            </div>

            <div class="rs-form-body">

                @if ($errors->any())
                <div class="rs-alert rs-alert-error mb-4">
                    <i class="bx bx-error-circle fs-5"></i>
                    <div>@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
                @endif

                <form action="manage-role-setting" method="POST" id="roleForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ request()->get('id') ?? '' }}">

                    {{-- ── ROLE IDENTITY ── --}}
                    <div class="rs-section-title">Role Identity</div>
                    <div class="row g-3 mb-2">
                        <div class="col-md-4 rs-field">
                            <label>Role Name <span class="req">*</span></label>
                            <div class="rs-input-box">
                                <span class="rs-icon"><i class="bx bx-shield-quarter"></i></span>
                                <input type="text" name="role" id="role" required
                                       placeholder="e.g. Sales Manager"
                                       value="{{ old('role', $roles->title ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-4 rs-field">
                            <label>Designation</label>
                            <div class="rs-input-box">
                                <span class="rs-icon"><i class="bx bx-id-card"></i></span>
                                <input type="text" name="subrole"
                                       placeholder="e.g. Field Sales Executive"
                                       value="{{ old('subrole', $roles->subtitle ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-4 rs-field">
                            <label>Status <span class="req">*</span></label>
                            <div class="rs-input-box">
                                <span class="rs-icon"><i class="bx bx-toggle-right"></i></span>
                                <select name="status" required>
                                    <option value="1" {{ (($roles->status ?? '1') == '1') ? 'selected' : '' }}>Active</option>
                                    <option value="2" {{ (($roles->status ?? '') == '2') ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── PERMISSIONS MATRIX ── --}}
                    <div class="rs-section-title d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span>Module Permissions</span>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="rs-bulk-btn" id="checkAll">
                                <i class="bx bx-check-double"></i> Select All
                            </button>
                            <button type="button" class="rs-bulk-btn rs-bulk-danger" id="uncheckAll">
                                <i class="bx bx-x"></i> Clear All
                            </button>
                        </div>
                    </div>

                    <div class="rs-legend mb-3">
                        <span><i class="bx bx-info-circle"></i></span>
                        <span>Greyed checkboxes indicate that action is not applicable for that module.</span>
                        <span class="rs-legend-sep">|</span>
                        <span><strong>View/Assign</strong> = user can see the list & assign records to others.</span>
                    </div>

                    {{-- Permission Table --}}
                    <div class="rs-perm-table">
                        {{-- Header --}}
                        <div class="rs-perm-head">
                            <div class="rs-col-module">Module</div>
                            @foreach($permTypes as $type => $meta)
                            <div class="rs-col-perm text-center">
                                <i class="bx {{ $meta['icon'] }}"></i>
                                <span>{{ $meta['label'] }}</span>
                            </div>
                            @endforeach
                        </div>

                        @foreach($modules as $key => $mod)
                        @php
                            $rowCheckedAll = true;
                            foreach(array_keys($permTypes) as $t) {
                                $disabled = ($t === 'import' && in_array($key, $noImport))
                                         || ($t === 'export' && in_array($key, $noExport))
                                         || ($t === 'assign' && in_array($key, $noAssign));
                                if (!$disabled && !in_array("{$key}_{$t}", $permissions)) {
                                    $rowCheckedAll = false; break;
                                }
                            }
                        @endphp
                        <div class="rs-perm-row" id="row-{{ $key }}">
                            <div class="rs-col-module">
                                <div class="rs-mod-info">
                                    <div class="rs-mod-dot" style="background:{{ $mod['color'] }}18; color:{{ $mod['color'] }};border:1px solid {{ $mod['color'] }}30;">
                                        <i class="bx {{ $mod['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="rs-mod-label">{{ $mod['label'] }}</div>
                                        <button type="button"
                                                class="rs-row-toggle {{ $rowCheckedAll ? 'rs-row-toggle-on' : '' }}"
                                                data-module="{{ $key }}"
                                                onclick="toggleRow('{{ $key }}', this)">
                                            {{ $rowCheckedAll ? 'All On' : 'Select All' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @foreach($permTypes as $type => $meta)
                            @php
                                $permKey  = "{$key}_{$type}";
                                $disabled = ($type === 'import' && in_array($key, $noImport))
                                         || ($type === 'export' && in_array($key, $noExport))
                                         || ($type === 'assign' && in_array($key, $noAssign));
                                $checked = in_array($permKey, $permissions) || in_array('All', $permissions);
                            @endphp
                            <div class="rs-col-perm">
                                @if($disabled)
                                    <span class="rs-check-na" title="Not applicable">—</span>
                                @else
                                    <label class="rs-check-wrap">
                                        <input type="checkbox"
                                               class="rs-check-input perm-{{ $key }}"
                                               name="permissions[{{ $key }}][]"
                                               value="{{ $type }}"
                                               {{ $checked ? 'checked' : '' }}>
                                        <span class="rs-check-box"><i class="bx bx-check"></i></span>
                                    </label>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach

                    </div>{{-- end rs-perm-table --}}

                    {{-- ── FOOTER ── --}}
                    <div class="rs-form-footer mt-4">
                        <a href="/role-settings" class="rs-btn-cancel">Cancel</a>
                        <button type="reset" class="rs-btn-cancel">Reset</button>
                        <button type="submit" class="rs-btn-save">
                            <i class="bx bx-check"></i>
                            {{ $isEdit ? 'Update Role' : 'Create Role' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<style>
.dash-container { padding: 24px; }

/* ── Form Card ── */
.rs-form-card   { border-radius: 18px; border: 1px solid #e8eaed; overflow: hidden; }

.rs-form-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px; background: linear-gradient(135deg, #005757, #007e7e);
}
.rs-form-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.rs-form-header-sub   { font-size: 0.74rem; color: rgba(255,255,255,.72); margin: 4px 0 0; }
.rs-back-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
    color: #fff; border-radius: 10px; padding: 7px 14px;
    font-size: 0.82rem; font-weight: 600; text-decoration: none; transition: background .15s;
}
.rs-back-btn:hover { background: rgba(255,255,255,.25); color: #fff; }

.rs-form-body { padding: 28px; background: #f4fbfb; }

.rs-section-title {
    font-size: 0.72rem; font-weight: 700; color: #006666;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 24px 0 14px; padding-bottom: 5px;
    border-bottom: 1.5px solid rgba(0,102,102,.12);
}
.rs-section-title:first-child { margin-top: 0; }

.rs-field { display: flex; flex-direction: column; }
.rs-field label { font-size: 0.78rem; color: #5f6368; margin-bottom: 5px; font-weight: 500; }
.rs-field .req  { color: #ea4335; }

.rs-input-box {
    display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 10px; background: #fff;
    overflow: hidden; transition: border-color .15s, box-shadow .15s; height: 44px;
}
.rs-input-box:focus-within { border-color: #006666; box-shadow: 0 0 0 3px rgba(0,102,102,.08); }
.rs-icon {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 100%; flex-shrink: 0;
    color: #006666; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.rs-input-box input,
.rs-input-box select {
    flex: 1; border: none !important; outline: none !important; box-shadow: none !important;
    background: transparent; font-size: 0.875rem; color: #202124;
    padding: 0 12px; height: 100%; appearance: none;
}
.rs-input-box input::placeholder { color: #9aa0a6; }
.rs-input-box select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}

/* Bulk buttons */
.rs-bulk-btn {
    display: inline-flex; align-items: center; gap: 4px;
    border: 1.5px solid rgba(0,102,102,.2); background: rgba(0,102,102,.05);
    color: #006666; border-radius: 8px; padding: 5px 12px;
    font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all .15s;
}
.rs-bulk-btn:hover { background: rgba(0,102,102,.1); }
.rs-bulk-danger { border-color: rgba(234,67,53,.2); background: rgba(234,67,53,.05); color: #ea4335; }
.rs-bulk-danger:hover { background: rgba(234,67,53,.1); }

/* Legend */
.rs-legend {
    display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
    font-size: 0.72rem; color: #80868b; background: rgba(0,102,102,0.04);
    border: 1px solid rgba(0,102,102,0.1); border-radius: 10px;
    padding: 9px 14px;
}
.rs-legend i { color: #006666; font-size: 0.88rem; }
.rs-legend-sep { color: #dadce0; }

/* Permissions Table */
.rs-perm-table {
    background: #fff; border: 1px solid #e8eaed; border-radius: 14px; overflow: hidden;
    overflow-x: auto;
}

.rs-perm-head,
.rs-perm-row {
    display: grid;
    grid-template-columns: 210px repeat(6, 1fr);
    min-width: 700px;
    align-items: center;
}

.rs-perm-head {
    background: linear-gradient(135deg, #005757, #007e7e);
    padding: 11px 16px; gap: 4px; position: sticky; top: 0; z-index: 5;
}
.rs-perm-head .rs-col-module { color: rgba(255,255,255,.9); font-size: 0.78rem; font-weight: 700; }
.rs-perm-head .rs-col-perm   {
    display: flex; flex-direction: column; align-items: center; gap: 2px;
    color: rgba(255,255,255,.85); font-size: 0.60rem; font-weight: 700; text-transform: uppercase;
}
.rs-perm-head .rs-col-perm i { font-size: 1.05rem; }

.rs-perm-row {
    padding: 10px 16px; gap: 4px;
    border-bottom: 1px solid #f1f3f4; transition: background .12s;
}
.rs-perm-row:last-child { border-bottom: none; }
.rs-perm-row:nth-child(even) { background: #fafbfc; }
.rs-perm-row:hover { background: rgba(0,102,102,0.03); }

.rs-col-module { padding-right: 8px; }
.rs-col-perm   { display: flex; align-items: center; justify-content: center; }

.rs-mod-info { display: flex; align-items: center; gap: 10px; }
.rs-mod-dot  {
    width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 0.95rem;
}
.rs-mod-label { font-size: 0.80rem; font-weight: 600; color: #202124; }
.rs-row-toggle {
    display: inline-block; font-size: 0.62rem; color: #80868b;
    background: none; border: none; padding: 0; cursor: pointer;
    text-decoration: underline; transition: color .12s; margin-top: 1px;
}
.rs-row-toggle:hover { color: #006666; }
.rs-row-toggle-on { color: #006666; font-weight: 700; }

/* Custom Checkboxes */
.rs-check-wrap { display: inline-flex; cursor: pointer; margin: 0; }
.rs-check-wrap input[type="checkbox"] { display: none; }
.rs-check-box {
    width: 22px; height: 22px; border-radius: 6px;
    border: 2px solid #d1d5db; background: #fff;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s; color: #fff; font-size: 0.9rem;
}
.rs-check-wrap input:checked + .rs-check-box { background: #006666; border-color: #006666; }
.rs-check-wrap:hover input:not(:checked) + .rs-check-box {
    border-color: #006666; background: rgba(0,102,102,.05);
}
.rs-check-na { color: #dadce0; font-size: 1.1rem; font-weight: 600; line-height: 1; }

/* Alert */
.rs-alert { display:flex; align-items:flex-start; gap:10px; border-radius:10px; padding:12px 16px; font-size:0.85rem; font-weight:500; }
.rs-alert-error { background:rgba(234,67,53,.08); border:1px solid rgba(234,67,53,.25); color:#ea4335; }

/* Footer */
.rs-form-footer {
    display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap;
    padding-top: 20px; border-top: 1px solid #e8eaed;
}
.rs-btn-cancel {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.85rem; padding: 9px 20px; border-radius: 10px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; text-decoration: none; transition: background .15s;
}
.rs-btn-cancel:hover { background: #f5f5f5; color: #444; }
.rs-btn-save {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.85rem; font-weight: 700; padding: 9px 24px; border-radius: 10px;
    border: none; background: #006666; color: #fff; cursor: pointer; transition: background .15s;
}
.rs-btn-save:hover { background: #004e4e; }

@media (max-width: 768px) {
    .rs-form-body   { padding: 16px; }
    .rs-form-header { padding: 16px 18px; flex-direction: column; align-items: flex-start; gap: 10px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Select All ── */
    document.getElementById('checkAll')?.addEventListener('click', () => {
        document.querySelectorAll('.rs-check-input').forEach(cb => cb.checked = true);
        document.querySelectorAll('.rs-row-toggle').forEach(b => {
            b.textContent = 'All On'; b.classList.add('rs-row-toggle-on');
        });
    });

    /* ── Clear All ── */
    document.getElementById('uncheckAll')?.addEventListener('click', () => {
        document.querySelectorAll('.rs-check-input').forEach(cb => cb.checked = false);
        document.querySelectorAll('.rs-row-toggle').forEach(b => {
            b.textContent = 'Select All'; b.classList.remove('rs-row-toggle-on');
        });
    });

    /* ── Row Toggle ── */
    window.toggleRow = function (mod, btn) {
        const boxes      = document.querySelectorAll('.perm-' + mod);
        const allChecked = [...boxes].every(cb => cb.checked);
        boxes.forEach(cb => cb.checked = !allChecked);
        btn.textContent = allChecked ? 'Select All' : 'All On';
        btn.classList.toggle('rs-row-toggle-on', !allChecked);
    };
});
</script>

@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => { if(typeof swal !== 'undefined') swal("Saved!", "{{ session('success') }}", "success"); });</script>
@endif
@endsection
