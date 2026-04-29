@extends('layout')
@section('title', 'Recoveries - Rusan')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        $totalCount = $recoveries->count();
        $paidCount = $recoveries->where('status', '1')->count();
        $unpaidCount = $recoveries->where('status', '0')->count();
        $totalPending = $recoveries->reduce(function ($carry, $item) {
            return $carry + max(0, $item->remaining_amount ?? 0);
        }, 0);
        $overdueCount = $recoveries->filter(function ($r) {
            return $r->status == '0' && !empty($r->reminder) &&
                date('Y-m-d', strtotime($r->reminder)) <= date('Y-m-d');
        })->count();
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Recoveries'])

        <div class="dash-container">

            {{-- ── Stat Cards ── --}}
            <div class="rv-stat-row mb-4">
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                        <i class="bx bx-receipt"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num">{{ $totalCount }}</div>
                        <div class="rv-stat-label">Total Entries</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#34a853;">{{ $paidCount }}</div>
                        <div class="rv-stat-label">Paid</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                        <i class="bx bx-time-five"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#ea4335;">{{ $unpaidCount }}</div>
                        <div class="rv-stat-label">Pending</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(251,188,4,0.12);color:#f9a825;">
                        <i class="bx bx-error-circle"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#f9a825;">{{ $overdueCount }}</div>
                        <div class="rv-stat-label">Overdue</div>
                    </div>
                </div>
                <div class="rv-stat-card rv-stat-wide">
                    <div class="rv-stat-icon" style="background:rgba(234,67,53,0.08);color:#ea4335;">
                        <i class="bx bx-rupee"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#ea4335;">₹{{ number_format($totalPending, 0) }}</div>
                        <div class="rv-stat-label">Total Pending Balance</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-receipt"></i>
                        {{ $totalCount }} {{ $totalCount == 1 ? 'Recovery' : 'Recoveries' }}
                    </span>
                    @if($overdueCount > 0)
                        <span class="rv-overdue-pill">
                            <i class="bx bx-error-circle"></i> {{ $overdueCount }} Overdue
                        </span>
                    @endif
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('recoveries_add', $roleArray) || in_array('All', $roleArray))
                        <button type="button" class="lb-btn lb-btn-primary open-recovery-modal"
                            data-url="/manage-recovery?ajax=1">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Recovery</span>
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
                                <th class="m-none" style="width:40px;">#</th>
                                <th class="m-none" style="width:80px;">Batch</th>
                                <th>Client & Project</th>
                                <th class="m-none">Company</th>
                                <th>Recovery Status (₹)</th>
                                <th class="m-none">Reminder</th>
                                <th class="m-none">Executive</th>
                                <th class="text-center position-sticky end-0" style="width:130px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recoveries as $k => $recovery)
                                @php
                                    $reminderTimes = strtotime($recovery->reminder ?? '') * 1000;
                                    $isPaid = $recovery->status == '1';
                                    $isOverdue = !$isPaid &&
                                        !empty($recovery->reminder) &&
                                        date('Y-m-d', strtotime($recovery->reminder)) <= date('Y-m-d');
                                    $isFullyPaid = ($recovery->remaining_amount ?? 0) <= 0;

                                    $totalAmt = $recovery->project_amount ?? 0;
                                    $pendingAmt = $recovery->remaining_amount ?? 0;
                                    $paidAmt = $totalAmt - $pendingAmt;
                                    $recPct = $totalAmt > 0 ? min(100, round(($paidAmt / $totalAmt) * 100)) : 0;
                                    $recColor = $recPct >= 80 ? '#34a853' : ($recPct >= 40 ? '#fbbc04' : '#ea4335');
                                @endphp
                                <tr class="lead-row-{{ $reminderTimes }}">
                                    <td class="m-none text-muted" style="font-size:0.78rem;">{{ $k + 1 }}</td>
                                    <td class="m-none">
                                        <span class="rv-batch">{{ $recovery->batchNo ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm"
                                                style="background:linear-gradient(135deg,#006666,#009688);color:#fff;flex-shrink:0;">
                                                {{ strtoupper(substr($recovery->name ?? 'R', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-600 text-truncate" style="max-width:180px;">
                                                    {{ $recovery->name ?? '—' }}</div>
                                                <div class="text-muted d-flex align-items-center gap-1"
                                                    style="font-size:0.72rem;">
                                                    <i class="bx bx-briefcase" style="font-size:0.8rem;"></i>
                                                    <span class="text-truncate">{{ $recovery->project ?? 'General' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none text-muted">{{ $recovery->company ?? '—' }}</td>
                                    <td>
                                        @if($isFullyPaid)
                                            <div class="fw-bold" style="color:#34a853; font-size:0.85rem;">
                                                ₹{{ number_format($totalAmt, 0) }}</div>
                                            <span class="rv-amount-badge rv-paid mt-1">
                                                <i class="bx bx-check-circle"></i> Cleared
                                            </span>
                                        @else
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="rv-amount text-danger"
                                                    style="font-size:0.85rem;">₹{{ number_format($pendingAmt, 0) }}</span>
                                                <span class="text-muted" style="font-size:0.65rem;">of
                                                    ₹{{ number_format($totalAmt, 0) }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <div
                                                    style="flex:1; height:4px; background:#f0f0f0; border-radius:2px; overflow:hidden;">
                                                    <div style="width:{{ $recPct }}%; height:100%; background:{{ $recColor }};">
                                                    </div>
                                                </div>
                                                <span
                                                    style="font-size:0.65rem; font-weight:700; color:{{ $recColor }};">{{ $recPct }}%</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="m-none">
                                        @if(!empty($recovery->reminder))
                                            <span class="rv-reminder {{ $isOverdue ? 'rv-reminder-overdue' : '' }}">
                                                <i class="bx bx-calendar"></i>
                                                {{ date('d M Y', strtotime($recovery->reminder)) }}
                                                @if($isOverdue)
                                                    <span class="rv-overdue-dot">Overdue</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="m-none text-muted" style="font-size:0.82rem;">{{ $recovery->poc ?? '—' }}</td>
                                    <td class="position-sticky end-0">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @if(!empty($recovery->id))
                                            {{-- Reminder --}}
                                            <button class="btn kb-action-btn reminder" data-id="{{ $recovery->id }}"
                                                data-type="Reminder" title="Set Reminder"
                                                style="background:rgba(251,188,4,0.10);color:#f9a825;">
                                                <i class="bx bx-alarm"></i>
                                            </button>
                                            {{-- Mark Received --}}
                                            <button class="btn kb-action-btn received" data-id="{{ $recovery->id }}"
                                                data-type="Received" title="Mark Received"
                                                style="background:rgba(52,168,83,0.10);color:#34a853;">
                                                <i class="bx bx-rupee"></i>
                                            </button>
                                            @endif
                                            {{-- WhatsApp --}}
                                            @php
                                                $waRaw = !empty($recovery->whatsapp) && $recovery->whatsapp !== '-' ? $recovery->whatsapp : $recovery->mob;
                                                $waNum = preg_replace('/[^0-9]/', '', $waRaw ?? '');
                                                if (strlen($waNum) == 10) {
                                                    $waNum = '91' . $waNum;
                                                }
                                            @endphp
                                            @if(!empty($waNum))
                                                <a href="https://wa.me/{{ $waNum }}" target="_blank" class="btn kb-action-btn"
                                                    title="WhatsApp" style="background:rgba(37,211,102,0.10);color:#25D366;"
                                                    onclick="event.stopPropagation();">
                                                    <i class="bx bxl-whatsapp"></i>
                                                </a>
                                            @endif
                                            {{-- Call --}}
                                            @if(!empty($recovery->mob))
                                                <a href="tel:+{{ $recovery->mob }}" class="btn kb-action-btn" title="Call"
                                                    style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                                    <i class="bx bx-phone"></i>
                                                </a>
                                            @endif
                                            {{-- Add Recovery (Edit icon) --}}
                                            <button type="button" class="btn kb-action-btn open-recovery-modal"
                                                data-url="/manage-recovery?project_id={{ $recovery->id ?? '' }}&ajax=1"
                                                title="Add Recovery" style="background:rgba(0,102,102,0.10);color:#006666;">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            {{-- Delete --}}
                                            @if(in_array('recoveries_delete', $roleArray) || in_array('All', $roleArray))
                                                <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete"
                                                    id="{{ $recovery->id ?? '' }}" data-page="recoveryProjectDelete" title="Delete"
                                                    style="background:rgba(234,67,53,0.10);color:#ea4335;">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($recoveries->isEmpty())
                    <div class="rv-empty">
                        <i class="bx bx-receipt"></i>
                        <span>No recoveries recorded yet.</span>
                        <button type="button" class="lb-btn lb-btn-primary mt-2 open-recovery-modal"
                            data-url="/manage-recovery?ajax=1">
                            <i class="bx bx-plus"></i> Add Recovery
                        </button>
                    </div>
                @endif
            </div>

        </div>
    </section>

    {{-- ── Shared Action Modal (Reminder / Received) ── --}}
    <div class="modal fade" id="recoveryModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;" id="recoveryModalContent">
                <div class="d-flex align-items-center justify-content-center" style="height:160px;">
                    <div class="spinner-border text-secondary" style="width:1.5rem;height:1.5rem;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Edit Recovery Modal ── --}}
    <div class="modal fade" id="manageRecoveryModal" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px; border:none;" id="manageRecoveryModalContent">
                <div class="d-flex align-items-center justify-content-center" style="height:200px;">
                    <div class="spinner-border text-secondary" style="width:1.5rem;height:1.5rem;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Shared cf-modal-header style (used by reminder/received/edit partials) --}}
    <style>
        .cf-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: linear-gradient(135deg, #005757, #007e7e);
            border-radius: 16px 16px 0 0;
        }

        .cf-modal-header-title {
            font-size: .975rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .cf-modal-header-sub {
            font-size: .73rem;
            color: rgba(255, 255, 255, .72);
            margin: 0;
        }

        .cf-modal-header .btn-close {
            filter: invert(1);
            opacity: .8;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Execute scripts found inside a container (innerHTML doesn't run them natively)
            function execScripts(container) {
                container.querySelectorAll('script').forEach(function (oldScript) {
                    var newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(function (attr) {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    newScript.textContent = oldScript.textContent;
                    document.body.appendChild(newScript);
                    oldScript.remove();
                });
            }

            function loadModal(modalEl, contentEl, url) {
                contentEl.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="height:160px;"><div class="spinner-border text-secondary" style="width:1.5rem;height:1.5rem;"></div></div>';
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                fetch(url)
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        contentEl.innerHTML = html;
                        execScripts(contentEl);  // ← run <script> blocks from the partial
                    })
                    .catch(function () {
                        contentEl.innerHTML = '<div class="p-4 text-center text-danger"><i class="bx bx-error" style="font-size:1.5rem;"></i><p class="mt-2">Failed to load. Please try again.</p></div>';
                    });
            }

            // ── Edit ──
            document.querySelectorAll('.open-recovery-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    loadModal(
                        document.getElementById('manageRecoveryModal'),
                        document.getElementById('manageRecoveryModalContent'),
                        this.dataset.url
                    );
                });
            });

            // ── Reminder & Received ──
            document.querySelectorAll('.reminder, .received').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const type = this.dataset.type;   // 'Reminder' or 'Received'
                    const url = '/recovery/' + id + '/' + type;
                    loadModal(
                        document.getElementById('recoveryModal'),
                        document.getElementById('recoveryModalContent'),
                        url
                    );
                });
            });



        });
    </script>

    <style>
        /* ── Stat Row ── */
        .rv-stat-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
        }

        @media (max-width: 900px) {
            .rv-stat-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 576px) {
            .rv-stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .rv-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .rv-stat-wide {
            grid-column: span 1;
        }

        .rv-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .rv-stat-num {
            font-size: 1.35rem;
            font-weight: 800;
            color: #202124;
            line-height: 1;
        }

        .rv-stat-label {
            font-size: 0.72rem;
            color: #80868b;
            margin-top: 3px;
            font-weight: 500;
        }

        /* ── Overdue pill in toolbar ── */
        .rv-overdue-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(234, 67, 53, 0.08);
            border: 1px solid rgba(234, 67, 53, 0.2);
            color: #ea4335;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.73rem;
            font-weight: 600;
        }

        /* ── Batch badge ── */
        .rv-batch {
            display: inline-block;
            background: #f1f3f4;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #5f6368;
        }

        /* ── Amount ── */
        .rv-amount {
            font-weight: 700;
            color: #202124;
            font-size: 0.875rem;
        }

        .rv-overdue-amount {
            color: #ea4335;
        }

        .rv-amount-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            border-radius: 20px;
            padding: 3px 9px;
        }

        .rv-paid {
            background: rgba(52, 168, 83, 0.08);
            color: #34a853;
        }

        /* ── Reminder ── */
        .rv-reminder {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.76rem;
            color: #5f6368;
        }

        .rv-reminder i {
            font-size: 0.9rem;
        }

        .rv-reminder-overdue {
            color: #ea4335;
        }

        .rv-overdue-dot {
            background: rgba(234, 67, 53, 0.10);
            color: #ea4335;
            border-radius: 20px;
            padding: 1px 7px;
            font-size: 0.68rem;
            font-weight: 700;
        }

        /* ── Empty state ── */
        .rv-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 50px 20px;
            color: #9aa0a6;
            text-align: center;
            gap: 8px;
        }

        .rv-empty i {
            font-size: 2.5rem;
        }

        .rv-empty span {
            font-size: 0.87rem;
        }
    </style>

@endsection
