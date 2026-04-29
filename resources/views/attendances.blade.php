@extends('layout')
@section('title', 'Attendances - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
        $canManage = in_array('All', $roleArray) || $isAdmin;
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Attendance Management'])

        <div class="dash-container">

            {{-- ── Stat Cards ── --}}
            <div class="rv-stat-row mb-4">
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                        <i class="bx bx-calendar-check"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num">{{ $summary['working_days'] }}</div>
                        <div class="rv-stat-label">Working Days</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                        <i class="bx bx-user-check"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#34a853;">{{ $summary['present'] }}</div>
                        <div class="rv-stat-label">Present</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                        <i class="bx bx-user-x"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#ea4335;">{{ $summary['absent'] }}</div>
                        <div class="rv-stat-label">Absent</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(251,188,4,0.10);color:#f9a825;">
                        <i class="bx bx-calendar-edit"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#f9a825;">{{ $summary['leaves'] }}</div>
                        <div class="rv-stat-label">Leaves</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                        <i class="bx bx-stopwatch"></i>
                    </div>
                    <div>
                        @php
                            $wh = $summary['worked_hours'];
                            $eh = $summary['expected_hours'];
                        @endphp
                        <div class="rv-stat-num" style="color:#1a73e8; font-size:1.05rem;">
                            {{ sprintf('%02d:%02d', floor($wh), round(($wh - floor($wh)) * 60)) }}<span class="rv-stat-sub">
                                / {{ sprintf('%02d:%02d', floor($eh), round(($eh - floor($eh)) * 60)) }}</span>
                        </div>
                        <div class="rv-stat-label">Hours (Worked / Exp)</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(26,115,232,0.06);color:#1a73e8;">
                        <i class="bx bx-calendar-star"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#1a73e8;">{{ $summary['holidays'] }}</div>
                        <div class="rv-stat-label">Holidays</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar & Filters ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form method="GET" id="filterForm" class="d-flex align-items-center gap-2 m-0 p-0">
                        @if($isAdmin)
                            <select name="user_id" id="user_id"
                                class="form-select form-select-sm border-0 bg-light fw-600 att-select"
                                onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Team Members</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <select name="range" id="range"
                            class="form-select form-select-sm border-0 bg-light fw-600 att-select"
                            onchange="document.getElementById('filterForm').submit()">
                            <option value="today" {{ $range == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="7days" {{ $range == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="month" {{ $range == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="last-month" {{ $range == 'last-month' ? 'selected' : '' }}>Last Month</option>
                            <option value="year" {{ $range == 'year' ? 'selected' : '' }}>This Year</option>
                        </select>

                        {{-- Record count badge --}}
                        <span class="lb-page-count">
                            <i class="bx bx-calendar"></i>
                            {{ count($final) }} {{ count($final) == 1 ? 'Record' : 'Records' }}
                        </span>
                    </form>
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button class="lb-icon-btn" onclick="exportAttendanceCSV()" title="Export to CSV">
                        <i class="bx bx-export"></i>
                    </button>
                    @if($canManage)
                        <button type="button" class="lb-btn lb-btn-primary open-attendance-modal"
                            data-url="/manage-attendance?ajax=1">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Attendance</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ── Table Card ── --}}
            <div class="dash-card mb-4">
                <div class="table-responsive">
                    <table id="lists" class="leads-table" style="width:100%;">
                        <thead>
                            <tr>
                                @if($isAdmin)
                                <th>Team Member</th>@endif
                                <th>Date &amp; Day</th>
                                <th>Timing <small>(In – Out)</small></th>
                                <th class="text-center">Method</th>
                                <th style="width:120px;">Status</th>
                                <th>Type</th>
                                <th>Hours <small>(Work/Exp)</small></th>
                                <th class="text-end">Balance</th>
                                <th>Remarks</th>
                                @if($canManage)
                                    <th class="text-center position-sticky end-0 bg-white"
                                        style="width:80px; border-left:1px solid #f1f3f4; box-shadow:-4px 0 8px rgba(0,0,0,0.02); z-index:10;">
                                Action</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($final as $r)
                                @php
                                    $workedHours = is_numeric($r['worked_hours']) ? (float) $r['worked_hours'] : 0;
                                    $expectedHours = is_numeric($r['expected_hours']) ? (float) $r['expected_hours'] : 0;
                                    $diff = 0;
                                    $diff_class = 'text-muted';

                                    if (($workedHours > 0 || $expectedHours > 0) && !in_array($r['status'], ['Holiday', 'Leave', 'Absent'])) {
                                        $diff = $workedHours - $expectedHours;
                                        $diff_class = $diff >= 0 ? 'text-success' : 'text-danger';
                                    }

                                    $workedFmt = sprintf('%02d:%02d', floor($workedHours), round(($workedHours - floor($workedHours)) * 60));
                                    $expectedFmt = sprintf('%02d:%02d', floor($expectedHours), round(($expectedHours - floor($expectedHours)) * 60));

                                    $absDiff = abs($diff);
                                    $diff_fmt = sprintf('%s%02d:%02d', $diff >= 0 ? '+' : '-', floor($absDiff), round(($absDiff - floor($absDiff)) * 60));

                                    $stStyle = match ($r['status']) {
                                        'Present' => ['#34a853', 'bx-check-circle'],
                                        'Leave' => ['#5f6368', 'bx-calendar-edit'],
                                        'Holiday' => ['#1a73e8', 'bx-calendar-star'],
                                        'Absent' => ['#ea4335', 'bx-user-x'],
                                        default => ['#dadce0', 'bx-help-circle']
                                    };

                                    // Build the edit URL for the modal
                                    $editUrl = '/manage-attendance?user_id=' . $r['user_id'] . '&date=' . $r['date'] . '&ajax=1';
                                    if (!empty($r['att_id'])) {
                                        $editUrl = '/manage-attendance?id=' . $r['att_id'] . '&ajax=1';
                                    }
                                @endphp
                                <tr>
                                    @if($isAdmin)
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="lb-avatar-sm"
                                                    style="background:rgba(0,102,102,0.1); color:#006666; border:none; font-weight:800;">
                                                    {{ substr($r['user'] ?? 'U', 0, 1) }}
                                                </div>
                                                <div class="fw-600 text-dark small">{{ $r['user'] }}</div>
                                            </div>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="fw-600 text-dark">{{ \Carbon\Carbon::parse($r['date'])->format('d M, Y') }}
                                        </div>
                                        <div class="text-muted small fw-500">{{ $r['day'] }}</div>
                                    </td>
                                    <td>
                                        @if($r['status'] === 'Present')
                                            <div class="d-flex flex-column">
                                                <span class="text-dark fw-600" style="font-size:0.85rem;">
                                                    <i class="bx bx-log-in-circle text-success small"></i>
                                                    {{ $r['check_in'] ?: '--:--' }}
                                                </span>
                                                <span class="text-muted small" style="font-size:0.75rem;">
                                                    <i class="bx bx-log-out-circle text-danger small"></i>
                                                    {{ $r['check_out'] ?: '--:--' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($r['method'] && $r['method'] !== '-')
                                            <span class="badge bg-light text-muted border px-2 py-1"
                                                style="font-size:0.65rem;">{{ $r['method'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="rv-status-pill"
                                            style="background:{{ $stStyle[0] }}15; color:{{ $stStyle[0] }};">
                                            <i class="bx {{ $stStyle[1] }}"></i> {{ $r['status'] }}
                                        </span>
                                    </td>
                                    <td><small
                                            class="fw-500 text-muted">{{ ($r['type'] && $r['type'] !== '-') ? $r['type'] : '—' }}</small>
                                    </td>
                                    <td class="{{ $diff_class }}">
                                        @if ($workedHours > 0 || $expectedHours > 0)
                                            <strong class="text-dark">{{ $workedFmt }}</strong>
                                            <span class="text-muted"> / {{ $expectedFmt }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-800 {{ $diff_class }}" style="font-size:0.9rem;">
                                        {{ !in_array($r['status'], ['Holiday', 'Leave', 'Absent']) && ($workedHours > 0 || $expectedHours > 0) ? $diff_fmt : '—' }}
                                    </td>
                                    <td>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width:150px;"
                                            title="{{ $r['remarks'] }}">{{ ($r['remarks'] && $r['remarks'] !== '-') ? $r['remarks'] : '—' }}</small>
                                    </td>
                                    @if($canManage)
                                        <td class="position-sticky end-0 bg-white"
                                            style="border-left:1px solid #f1f3f4; box-shadow:-4px 0 8px rgba(0,0,0,0.02); z-index:9;">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                {{-- Edit – opens modal --}}
                                                <button type="button" class="btn kb-action-btn open-attendance-modal"
                                                    data-url="{{ $editUrl }}" title="Edit"
                                                    style="background:rgba(0,102,102,0.10);color:#006666; border:none;">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                {{-- Delete --}}
                                                @if(!empty($r['att_id']))
                                                    <a href="javascript:void(0)" class="btn kb-action-btn attendance-delete"
                                                        id="{{ $r['att_id'] }}" title="Delete"
                                                        style="background:rgba(234,67,53,0.10);color:#ea4335; border:none;">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($isAdmin ? 1 : 0) + ($canManage ? 1 : 0) + 9 }}" class="text-center py-5">
                                        <div class="rv-empty">
                                            <i class="bx bx-calendar-x"></i>
                                            <span>No attendance records found for this selection.</span>
                                            @if($canManage)
                                                <button type="button" class="lb-btn lb-btn-primary mt-2 open-attendance-modal"
                                                    data-url="/manage-attendance?ajax=1" style="font-size:0.82rem;">
                                                    <i class="bx bx-plus"></i> Add First Record
                                                </button>
                                            @endif
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

    {{-- ══ Manage Attendance Modal ══ --}}
    <div class="modal fade" id="manageAttendanceModal" aria-labelledby="manageAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;" id="manageAttendanceModalContent">
                {{-- Content injected via AJAX --}}
            </div>
        </div>
    </div>

    <style>
        /* ── Page Layout ── */
        .dash-container {
            padding: 24px 24px 24px;
        }

        /* top margin added */

        /* ── Stat Cards (6-col grid) ── */
        .rv-stat-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
        }

        @media (max-width: 1200px) {
            .rv-stat-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .rv-stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .rv-stat-row {
                grid-template-columns: 1fr;
            }
        }

        .rv-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
        }

        .rv-stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
            border-color: #d2d4d7;
            transform: translateY(-1px);
        }

        .rv-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .rv-stat-num {
            font-size: 1.2rem;
            font-weight: 800;
            color: #202124;
            line-height: 1;
            white-space: nowrap;
        }

        .rv-stat-sub {
            font-size: 0.7em;
            font-weight: 500;
            color: #80868b;
        }

        .rv-stat-label {
            font-size: 0.7rem;
            color: #80868b;
            margin-top: 4px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ── Status Pills ── */
        .rv-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .rv-status-pill i {
            font-size: 0.88rem;
        }

        /* ── Empty State ── */
        .rv-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #9aa0a6;
            text-align: center;
            gap: 8px;
        }

        .rv-empty i {
            font-size: 3rem;
            color: #dadce0;
        }

        .rv-empty span {
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* ── Toolbar ── */
        .leads-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            padding: 8px 16px;
            min-height: 56px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .leads-toolbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .leads-toolbar-right {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 8px;
        }

        /* ── Filter Selects ── */
        .att-select {
            min-width: 140px;
            border-radius: 8px !important;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }

        .att-select:focus {
            border-color: #006666 !important;
            box-shadow: 0 0 0 3px rgba(0, 102, 102, 0.1) !important;
        }

        /* ── Action Buttons ── */
        .kb-action-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            padding: 0;
            transition: all 0.15s;
        }

        .kb-action-btn:hover {
            transform: scale(1.12);
        }

        /* ── Record count badge ── */
        .lb-page-count {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(0, 102, 102, 0.08);
            color: #006666;
            border-radius: 20px;
            padding: 12px 20px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 150px
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* ── AJAX Modal ── */
            function execScripts(container) {
                container.querySelectorAll('script').forEach(function (old) {
                    var s = document.createElement('script');
                    Array.from(old.attributes).forEach(a => s.setAttribute(a.name, a.value));
                    s.textContent = old.textContent;
                    document.body.appendChild(s);
                    old.remove();
                });
            }

            document.querySelectorAll('.open-attendance-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const url = this.dataset.url;
                    const content = document.getElementById('manageAttendanceModalContent');
                    const modalEl = document.getElementById('manageAttendanceModal');

                    content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#006666;"></i><p class="mt-2 text-muted">Loading form…</p></div>';

                    bootstrap.Modal.getOrCreateInstance(modalEl).show();

                    fetch(url)
                        .then(r => r.text())
                        .then(html => { content.innerHTML = html; execScripts(content); })
                        .catch(() => {
                            content.innerHTML = '<div class="p-5 text-center text-danger"><i class="bx bx-error" style="font-size:2rem;"></i><p>Could not load form. Please try again.</p></div>';
                        });
                });
            });

            /* ── Attendance Delete ── */
            document.querySelectorAll('.attendance-delete').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const selector = this;
                    const rowid = selector.getAttribute('id');

                    swal({
                        title: 'Are you sure?',
                        text: 'This attendance record will be permanently deleted.',
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(function (willDelete) {
                        if (!willDelete) { swal('Cancelled', '', 'info'); return; }
                        $.ajax({
                            type: 'GET',
                            url: '/delete-attendance',
                            data: { rowid: rowid, attendanceDelete: 'attendanceDelete' },
                            success: function (response) {
                                if (response.success) {
                                    selector.closest('tr').style.opacity = '0';
                                    selector.closest('tr').style.transition = 'opacity 0.3s';
                                    setTimeout(() => selector.closest('tr').remove(), 300);
                                    swal('Deleted!', 'Attendance record has been removed.', 'success');
                                } else {
                                    swal('Error', response.error || 'Could not delete the record.', 'error');
                                }
                            },
                            error: function () {
                                swal('Error', 'An error occurred. Please try again.', 'error');
                            }
                        });
                    });
                });
            });
        });

        /* ── CSV Export ── */
        function exportAttendanceCSV() {
            const table = document.getElementById('lists');
            if (!table) return;

            const hasAction = {{ $canManage ? 'true' : 'false' }};
            let csv = [];
            table.querySelectorAll('tr').forEach(function (row) {
                let cols = row.querySelectorAll('th, td');
                let rowData = [];
                cols.forEach(function (col) {
                    rowData.push('"' + col.innerText.replace(/"/g, '""').trim() + '"');
                });
                if (hasAction) rowData.pop(); // strip Action column
                csv.push(rowData.join(','));
            });

            const blob = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'attendance_{{ now()->format("Y-m-d") }}.csv';
            a.click();
            URL.revokeObjectURL(url);
        }

        /* ── Session Flash (SweetAlert) ── */
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', function () {
                swal("Saved!", "{{ session('success') }}", "success");
            });
        @endif
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', function () {
                swal("Error", "{{ session('error') }}", "error");
            });
        @endif
    </script>
@endsection