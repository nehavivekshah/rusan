@extends('layout')
@section('title', 'Email Templates - Rusan')

@section('content')
@php
    $total    = $templates->count();
    $active   = $templates->where('is_active', 1)->count();
    $inactive = $templates->where('is_active', 0)->count();
    $modules  = $templates->pluck('module')->unique()->count();
@endphp

<section class="task__section">
    @include('inc.header', ['title' => 'Email Templates'])

    <div class="dash-container">

        {{-- ── Stat Cards ── --}}
        <div class="rv-stat-row mb-4">
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(22, 63, 122,0.1);color:#163f7a;">
                    <i class="bx bx-envelope"></i>
                </div>
                <div>
                    <div class="rv-stat-num">{{ $total }}</div>
                    <div class="rv-stat-label">Total Templates</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(52,168,83,0.1);color:#163f7a;">
                    <i class="bx bx-check-circle"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#163f7a;">{{ $active }}</div>
                    <div class="rv-stat-label">Active</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                    <i class="bx bx-pause-circle"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#ea4335;">{{ $inactive }}</div>
                    <div class="rv-stat-label">Inactive</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                    <i class="bx bx-layer"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#1a73e8;">{{ $modules }}</div>
                    <div class="rv-stat-label">Modules Used</div>
                </div>
            </div>
        </div>

        {{-- ── Toolbar ── --}}
        <div class="leads-toolbar mb-3">
            <div class="leads-toolbar-left">
                <span class="lb-page-count">
                    <i class="bx bx-list-ul"></i>
                    {{ $total }} {{ $total == 1 ? 'Template' : 'Templates' }}
                </span>
            </div>
            <div class="leads-toolbar-right gap-2">
                <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                    <i class="bx bx-refresh"></i>
                </button>
                <a href="{{ route('email-templates.create') }}" class="lb-btn lb-btn-primary">
                    <i class="bx bx-plus"></i>
                    <span class="d-none d-sm-inline">New Template</span>
                </a>
            </div>
        </div>

        {{-- ── Table Card ── --}}
        <div class="dash-card mb-4">
            <div class="table-responsive">
                <table id="lists" class="leads-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th class="m-none" style="width:40px;">#</th>
                            <th>Module</th>
                            <th>Event</th>
                            <th class="m-none">Subject</th>
                            <th class="m-none" style="width:150px;">Reminder Days</th>
                            <th style="width:110px;">Status</th>
                            <th class="text-center position-sticky end-0 bg-white" style="width:120px; border-left:1px solid #f1f3f4; box-shadow:-4px 0 8px rgba(0,0,0,0.02); z-index:10;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $k => $tpl)
                        <tr id="tpl-row-{{ $tpl->id }}">
                            <td class="m-none text-muted" style="font-size:0.78rem;">{{ $k + 1 }}</td>
                            <td>
                                @php
                                    $modColor = ['contracts'=>['#1a73e8','bx-file-blank'], 'invoices'=>['#163f7a','bx-receipt'], 'proposals'=>['#f9a825','bx-notepad'], 'recovery'=>['#ea4335','bx-alarm-exclamation']][$tpl->module] ?? ['#80868b','bx-layer'];
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="lb-avatar-sm" style="background:{{ $modColor[0] }}15; color:{{ $modColor[0] }}; border:none;">
                                        <i class="bx {{ $modColor[1] }}"></i>
                                    </div>
                                    <span class="fw-600">{{ ucfirst($tpl->module) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="et-event-tag">{{ ucfirst($tpl->event) }}</span>
                            </td>
                            <td class="m-none">
                                <div class="fw-500 text-dark" style="max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    {{ $tpl->subject }}
                                </div>
                            </td>
                            <td class="m-none">
                                @if(!empty($tpl->reminder_days))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($tpl->reminder_days as $day)
                                            <span class="et-day-badge">{{ $day }}d</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($tpl->is_active)
                                    <span class="rv-status-pill" style="background:#163f7a15;color:#163f7a;">
                                        <i class="bx bx-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="rv-status-pill" style="background:#ea433515;color:#ea4335;">
                                        <i class="bx bx-pause-circle"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="position-sticky end-0 bg-white" style="border-left:1px solid #f1f3f4; box-shadow:-4px 0 8px rgba(0,0,0,0.02); z-index:9;">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="{{ route('email-templates.edit', $tpl->id) }}"
                                       class="btn kb-action-btn kb-action-edit" title="Edit"
                                       style="background:rgba(22, 63, 122,0.08);color:#163f7a;">
                                        <i class="bx bx-pencil"></i>
                                    </a>

                                    <form action="{{ route('email-templates.toggle', $tpl->id) }}" method="POST" class="d-inline toggle-form">
                                        @csrf
                                        <button type="submit" title="{{ $tpl->is_active ? 'Deactivate' : 'Activate' }}"
                                                class="btn kb-action-btn"
                                                style="background:{{ $tpl->is_active ? 'rgba(234,67,53,0.08)' : 'rgba(52,168,83,0.08)' }};color:{{ $tpl->is_active ? '#ea4335' : '#163f7a' }};">
                                            <i class="bx {{ $tpl->is_active ? 'bx-power-off' : 'bx-bolt-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="rv-empty">
                                    <i class="bx bx-envelope"></i>
                                    <span>No email templates found.</span>
                                    <a href="{{ route('email-templates.create') }}" class="lb-btn lb-btn-primary mt-2">
                                        <i class="bx bx-plus"></i> Create First Template
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<style>
.dash-container { padding: 24px; }

/* Stat Row */
.rv-stat-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
@media(max-width:991px){ .rv-stat-row { grid-template-columns:repeat(2,1fr); } }
@media(max-width:576px){ .rv-stat-row { grid-template-columns:1fr; } }
.rv-stat-card { background:#fff; border:1px solid #e8eaed; border-radius:16px; padding:18px; display:flex; align-items:center; gap:14px; transition:all .2s; }
.rv-stat-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.05); }
.rv-stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
.rv-stat-num { font-size:1.5rem; font-weight:800; color:#202124; line-height:1; }
.rv-stat-label { font-size:0.75rem; color:#80868b; margin-top:4px; font-weight:600; text-transform:uppercase; letter-spacing:.3px; }

/* Status pills */
.rv-status-pill { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:4px 12px; font-size:0.75rem; font-weight:700; }

/* Event tag */
.et-event-tag { background:rgba(26,115,232,0.08); color:#1a73e8; border-radius:6px; padding:3px 10px; font-size:0.75rem; font-weight:700; display:inline-block; }

/* Reminder day badge */
.et-day-badge { background:rgba(249,168,37,0.1); color:#f9a825; border-radius:6px; padding:2px 8px; font-size:0.72rem; font-weight:700; }

/* Action buttons */
.kb-action-btn { width:34px; height:34px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; border:none; transition:transform .1s; }
.kb-action-btn:hover { transform:scale(1.08); }

/* Empty */
.rv-empty { display:flex; flex-direction:column; align-items:center; padding:40px; color:#9aa0a6; }
.rv-empty i { font-size:3rem; margin-bottom:12px; color:#dadce0; }
.rv-empty span { font-size:0.95rem; font-weight:500; margin-bottom:15px; }
</style>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof swal !== 'undefined') swal("Done!", "{{ session('success') }}", "success");
});
</script>
@endif
@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof swal !== 'undefined') swal("Error", "{{ session('error') }}", "error");
});
</script>
@endif

@endsection
