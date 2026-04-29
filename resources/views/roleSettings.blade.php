@extends('layout')
@section('title', 'Role Settings - eseCRM')

@section('content')
@php
    $total    = count($roles);
    $active   = collect($roles)->where('status', '1')->count();
    $inactive = collect($roles)->whereNotIn('status', ['1'])->count();
@endphp

<section class="task__section">
    @include('inc.header', ['title' => 'Role Settings'])

    <div class="dash-container">

        {{-- ── Stat Cards ── --}}
        <div class="rv-stat-row mb-4">
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                    <i class="bx bx-shield-quarter"></i>
                </div>
                <div>
                    <div class="rv-stat-num">{{ $total }}</div>
                    <div class="rv-stat-label">Total Roles</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                    <i class="bx bx-check-shield"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#34a853;">{{ $active }}</div>
                    <div class="rv-stat-label">Active Roles</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                    <i class="bx bx-lock"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#ea4335;">{{ $inactive }}</div>
                    <div class="rv-stat-label">Inactive</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                    <i class="bx bx-user-check"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#1a73e8;">{{ collect($roles)->pluck('title')->unique()->count() }}</div>
                    <div class="rv-stat-label">Unique Roles</div>
                </div>
            </div>
        </div>

        {{-- ── Toolbar ── --}}
        <div class="leads-toolbar mb-3">
            <div class="leads-toolbar-left">
                <span class="lb-page-count">
                    <i class="bx bx-list-ul"></i>
                    {{ $total }} {{ $total == 1 ? 'Role' : 'Roles' }}
                </span>
            </div>
            <div class="leads-toolbar-right gap-2">
                <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                    <i class="bx bx-refresh"></i>
                </button>
                <a href="/manage-role-setting" class="lb-btn lb-btn-primary">
                    <i class="bx bx-plus"></i>
                    <span class="d-none d-sm-inline">Add Role</span>
                </a>
            </div>
        </div>

        {{-- ── Roles Card Grid ── --}}
        <div class="rs-card-grid mb-4">
            @forelse($roles as $role)
            @php
                $features    = ucwords(str_replace(',', ', ', ($role->features ?? '')));
                $isAllAccess = trim($role->features ?? '') === 'All' || $features === 'All';
                $permArr     = explode(',', ($role->permissions ?? ''));
                $permCount   = count(array_filter($permArr));
            @endphp
            <div class="rs-role-card">
                {{-- Header --}}
                <div class="rs-role-header">
                    <div class="rs-role-avatar" style="background:linear-gradient(135deg,#005757,#007e7e);">
                        {{ strtoupper(substr($role->title ?? 'R', 0, 1)) }}
                    </div>
                    <div class="rs-role-meta">
                        <div class="rs-role-title">{{ $role->title ?? '—' }}</div>
                        <div class="rs-role-sub">{{ $role->subtitle ?? 'No designation' }}</div>
                    </div>
                    @if($role->status == '1')
                        <span class="rv-status-pill" style="background:#34a85315;color:#34a853;">
                            <i class="bx bx-check-circle"></i> Active
                        </span>
                    @else
                        <span class="rv-status-pill" style="background:#ea433515;color:#ea4335;">
                            <i class="bx bx-pause-circle"></i> Inactive
                        </span>
                    @endif
                </div>

                {{-- Permissions preview --}}
                <div class="rs-perm-preview">
                    @if($isAllAccess)
                        <span class="rs-perm-chip rs-perm-all">
                            <i class="bx bx-infinite"></i> Full Access — All Modules
                        </span>
                    @else
                        <span class="rs-perm-chip">
                            <i class="bx bx-key"></i>
                            {{ $permCount > 0 ? $permCount . ' permission' . ($permCount > 1 ? 's' : '') : 'No permissions' }}
                        </span>
                        <div class="rs-feature-tags mt-2">
                            @foreach(array_slice(explode(',', $role->features ?? ''), 0, 4) as $feat)
                                @if(trim($feat))
                                    <span class="rs-feat-tag">{{ ucfirst(trim($feat)) }}</span>
                                @endif
                            @endforeach
                            @if(count(explode(',', $role->features ?? '')) > 4)
                                <span class="rs-feat-tag rs-feat-more">+{{ count(explode(',', $role->features ?? '')) - 4 }} more</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="rs-role-footer">
                    @if($isAllAccess)
                        <span class="rs-locked-hint"><i class="bx bx-lock-alt"></i> Master role — read-only</span>
                        <span class="rs-btn-edit rs-btn-disabled" title="Master roles cannot be edited">
                            <i class="bx bx-edit"></i> Edit
                        </span>
                    @else
                        <a href="/manage-role-setting?id={{ $role->id }}" class="rs-btn-edit">
                            <i class="bx bx-edit"></i> Edit Permissions
                        </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="rv-empty" style="grid-column:1/-1;">
                <i class="bx bx-shield-quarter"></i>
                <span>No roles defined yet.</span>
                <a href="/manage-role-setting" class="lb-btn lb-btn-primary mt-2">
                    <i class="bx bx-plus"></i> Create First Role
                </a>
            </div>
            @endforelse
        </div>

    </div>
</section>

<style>
.dash-container { padding: 24px; }

/* Stat Row */
.rv-stat-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
@media(max-width:991px){ .rv-stat-row{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:576px){ .rv-stat-row{ grid-template-columns:1fr; } }
.rv-stat-card { background:#fff; border:1px solid #e8eaed; border-radius:16px; padding:18px; display:flex; align-items:center; gap:14px; transition:all .2s; }
.rv-stat-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.05); }
.rv-stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
.rv-stat-num   { font-size:1.5rem; font-weight:800; color:#202124; line-height:1; }
.rv-stat-label { font-size:0.75rem; color:#80868b; margin-top:4px; font-weight:600; text-transform:uppercase; letter-spacing:.3px; }

/* Status pill */
.rv-status-pill { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:4px 12px; font-size:0.72rem; font-weight:700; white-space:nowrap; }

/* Role Card Grid */
.rs-card-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px,1fr)); gap:16px; }

.rs-role-card {
    background:#fff; border:1px solid #e8eaed; border-radius:18px;
    overflow:hidden; display:flex; flex-direction:column; transition:box-shadow .2s, transform .18s;
}
.rs-role-card:hover { box-shadow:0 8px 28px rgba(0,0,0,.08); transform:translateY(-2px); }

/* Card Header */
.rs-role-header {
    display:flex; align-items:center; gap:12px;
    padding:18px 18px 14px; border-bottom:1px solid #f1f3f4;
}
.rs-role-avatar {
    width:44px; height:44px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; font-weight:800; color:#fff;
}
.rs-role-meta { flex:1; min-width:0; }
.rs-role-title { font-size:0.95rem; font-weight:700; color:#202124; }
.rs-role-sub   { font-size:0.73rem; color:#80868b; }

/* Permissions Preview */
.rs-perm-preview { padding:14px 18px; flex:1; }
.rs-perm-chip {
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(0,102,102,0.07); color:#006666;
    border-radius:8px; padding:5px 12px; font-size:0.75rem; font-weight:700;
}
.rs-perm-all { background:rgba(26,115,232,0.08); color:#1a73e8; }

.rs-feature-tags { display:flex; flex-wrap:wrap; gap:6px; }
.rs-feat-tag {
    background:#f1f3f4; color:#5f6368; border-radius:6px;
    padding:3px 9px; font-size:0.70rem; font-weight:600;
    text-transform:capitalize;
}
.rs-feat-more { background:rgba(0,102,102,0.06); color:#006666; }

/* Card Footer */
.rs-role-footer {
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 18px; background:#f8fafb;
    border-top:1px solid #f1f3f4;
}
.rs-locked-hint { font-size:0.72rem; color:#9aa0a6; display:flex; align-items:center; gap:4px; }
.rs-btn-edit {
    display:inline-flex; align-items:center; gap:5px;
    background:#006666; color:#fff; border-radius:8px;
    padding:6px 14px; font-size:0.78rem; font-weight:700;
    text-decoration:none; border:none; cursor:pointer; transition:background .15s;
}
.rs-btn-edit:hover { background:#004e4e; color:#fff; }
.rs-btn-disabled { background:#e8eaed !important; color:#9aa0a6 !important; cursor:not-allowed; }

/* Empty */
.rv-empty { display:flex; flex-direction:column; align-items:center; padding:40px; color:#9aa0a6; }
.rv-empty i { font-size:3rem; margin-bottom:12px; color:#dadce0; }
.rv-empty span { font-size:0.95rem; font-weight:500; margin-bottom:15px; }
</style>

@if(session('success'))
<script>document.addEventListener('DOMContentLoaded',()=>{if(typeof swal!=='undefined')swal("Done!","{{ session('success') }}","success");});</script>
@endif
@endsection
