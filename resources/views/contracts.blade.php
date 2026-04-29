@extends('layout')
@section('title','Contracts - eseCRM')

@section('content')
    @php
        use Carbon\Carbon;
        // Retrieve role permissions from session
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        $totalCount = $contracts->count();
        $totalValue = $contracts->sum(function($c) {
            return (float) $c->value;
        });

        $activeCount = 0;
        $expiredCount = 0;
        $expiringSoonCount = 0;

        foreach($contracts as $c) {
            if (!empty($c->end_date)) {
                $endDate = Carbon::parse($c->end_date);
                $diffInDays = Carbon::today()->diffInDays($endDate, false);

                if ($diffInDays < 0) {
                    $expiredCount++;
                } elseif ($diffInDays <= 15) {
                    $expiringSoonCount++;
                    $activeCount++;
                } else {
                    $activeCount++;
                }
            } else {
                $activeCount++;
            }
        }
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Contracts'])

        <div class="dash-container">
            {{-- ── Stat Cards ── --}}
            <div class="rv-stat-row mb-4">
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                        <i class="bx bx-file"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num">{{ $totalCount }}</div>
                        <div class="rv-stat-label">Total Contracts</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                        <i class="bx bx-check-shield"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#34a853;">{{ $activeCount }}</div>
                        <div class="rv-stat-label">Active</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                        <i class="bx bx-x-circle"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#ea4335;">{{ $expiredCount }}</div>
                        <div class="rv-stat-label">Expired</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(251,188,4,0.12);color:#f9a825;">
                        <i class="bx bx-time"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#f9a825;">{{ $expiringSoonCount }}</div>
                        <div class="rv-stat-label">Expiring Soon</div>
                    </div>
                </div>
                <div class="rv-stat-card rv-stat-wide">
                    <div class="rv-stat-icon" style="background:rgba(26,115,232,0.08);color:#1a73e8;">
                        <i class="bx bx-rupee"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#1a73e8;">₹{{ number_format($totalValue, 0) }}</div>
                        <div class="rv-stat-label">Total Value</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-file"></i>
                        {{ $totalCount }} {{ $totalCount == 1 ? 'Contract' : 'Contracts' }}
                    </span>
                    @if($expiringSoonCount > 0)
                        <span class="rv-overdue-pill">
                            <i class="bx bx-time"></i> {{ $expiringSoonCount }} Expiring Soon
                        </span>
                    @endif
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('contracts_add', $roleArray) || in_array('All', $roleArray))
                        <button type="button" class="lb-btn lb-btn-primary open-contract-modal" data-url="/manage-contract?ajax=1">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">New Contract</span>
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
                                <th>Subject</th>
                                <th style="min-width: 180px;">Customer</th>
                                <th class="m-none">Type</th>
                                <th style="min-width: 100px;">Amount (₹)</th>
                                <th class="m-none" style="min-width: 110px;">Started</th>
                                <th class="m-none" style="min-width: 110px;">Expires</th>
                                @if(in_array('contracts_edit',$roleArray) || in_array('contracts_delete',$roleArray) || in_array('All',$roleArray))
                                <th class="text-center position-sticky end-0 bg-white" style="min-width:130px; background-color:#ffffff !important; background-clip: padding-box; border-left: 1px solid #f1f3f4; box-shadow: -4px 0 8px rgba(0,0,0,0.02); z-index: 10;">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="contracts">
                            @foreach($contracts as $k => $contract)
                                @php
                                    $isExpired = false;
                                    $isExpiringSoon = false;
                                    if (!empty($contract->end_date)) {
                                        $endDate = Carbon::parse($contract->end_date);
                                        $diffInDays = Carbon::today()->diffInDays($endDate, false);
                                        $isExpired = $diffInDays < 0;
                                        $isExpiringSoon = ($diffInDays >= 0 && $diffInDays <= 15);
                                    }
                                @endphp
                                <tr>
                                    <td class="m-none text-muted" style="font-size:0.78rem;">{{ $k+1 }}</td>
                                    <td>
                                        <div class="fw-500">{{ $contract->subject ?? '—' }}</div>
                                        @if($isExpired)
                                            <span style="font-size:0.7rem;color:#ea4335;background:rgba(234,67,53,0.1);padding:2px 6px;border-radius:4px;font-weight:600;">Expired</span>
                                        @elseif($isExpiringSoon)
                                            <span style="font-size:0.7rem;color:#f9a825;background:rgba(251,188,4,0.1);padding:2px 6px;border-radius:4px;font-weight:600;">Expiring Soon</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm" style="background:linear-gradient(135deg,#006666,#009688);color:#fff; cursor:pointer;" onclick="$('#{{ $contract->id }}').trigger('dblclick')">
                                                {{ strtoupper(substr($contract->name ?? 'C', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-500 view-client-details" data-id="{{ $contract->client_id }}" style="cursor:pointer; color: #1a73e8;">{{ $contract->name ?? '' }}</div>
                                                <div class="text-muted small">{{ $contract->company ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none text-muted" style="font-size:0.85rem;">{{ $contract->contract_type ?? '—' }}</td>
                                    <td>
                                        <span class="rv-amount">₹{{ number_format((float)($contract->value ?? 0), 0) }}</span>
                                    </td>
                                    <td class="m-none">
                                        <span class="text-muted" style="font-size:0.82rem;">{!! !empty($contract->start_date) ? date_format(date_create($contract->start_date),'d M, Y') : '—' !!}</span>
                                    </td>
                                    <td class="m-none">
                                        @if(!empty($contract->end_date))
                                        <span class="rv-reminder {{ $isExpired ? 'rv-reminder-overdue' : ($isExpiringSoon ? 'rv-reminder-warning' : '') }}">
                                            <i class="bx bx-calendar"></i>
                                            {!! date_format(date_create($contract->end_date),'d M, Y') !!}
                                        </span>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    @if(in_array('contracts_edit',$roleArray) || in_array('contracts_delete',$roleArray) || in_array('All',$roleArray))
                                    <td class="position-sticky end-0 bg-white" style="background-color:#ffffff !important; background-clip: padding-box; border-left: 1px solid #f1f3f4; box-shadow: -4px 0 8px rgba(0,0,0,0.02); z-index: 9;">
                                        <div class="d-flex align-items-center justify-content-center gap-1" style="flex-wrap: nowrap;">
                                            @php
                                                $phone = $contract->whatsapp ?: $contract->mob;
                                                $waUrl = "";
                                                if ($phone) {
                                                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                                                    
                                                    // Basic validation: ensure the number has at least 10 digits
                                                    if (strlen($cleanPhone) >= 10) {
                                                        $message = "Hi " . ($contract->name ?? 'Customer') . ",\n\n" .
                                                                  "This is a reminder regarding your contract: *" . ($contract->subject ?? 'N/A') . "*\n\n" .
                                                                  "Details:\n" .
                                                                  "- Type: " . ($contract->contract_type ?? 'N/A') . "\n" .
                                                                  "- Value: " . number_format((float)($contract->value ?? 0), 2) . "\n" .
                                                                  "- Expiry Date: " . (!empty($contract->end_date) ? date('d M, Y', strtotime($contract->end_date)) : 'N/A') . "\n\n" .
                                                                  "Please let us know if you have any questions.\n\n" .
                                                                  "Thank you!";
                                                        $waUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($message);
                                                    }
                                                }
                                            @endphp

                                            @if($waUrl)
                                            <a href="{{ $waUrl }}" target="_blank" class="btn kb-action-btn" title="Send WhatsApp Reminder" style="background:rgba(37,211,102,0.1);color:#25d366; border:none;">
                                                <i class="bx bxl-whatsapp"></i>
                                            </a>
                                            @endif

                                            @if(in_array('contracts_edit',$roleArray) || in_array('All',$roleArray))
                                            <button type="button" class="btn kb-action-btn open-contract-modal" data-url="/manage-contract?id={{ $contract->id }}&ajax=1" title="Edit" style="background:rgba(0,102,102,0.10);color:#006666; border:none;">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @endif
                                            @if(in_array('contracts_delete',$roleArray) || in_array('All',$roleArray))
                                            <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete" id="{{ $contract->id }}" data-page="contractDelete" title="Delete" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                            @endif
                                        </div>    
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($contracts->isEmpty())
                    <div class="rv-empty">
                        <i class="bx bx-file"></i>
                        <span>No contracts found.</span>
                        @if(in_array('contracts_add', $roleArray) || in_array('All', $roleArray))
                        <button type="button" class="lb-btn lb-btn-primary mt-2 open-contract-modal" data-url="/manage-contract?ajax=1">
                            <i class="bx bx-plus"></i> Add Contract
                        </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

    <style>
        /* ── Stat Row ── */
        .rv-stat-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; }
        @media (max-width: 900px) { .rv-stat-row { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 576px) { .rv-stat-row { grid-template-columns: repeat(2, 1fr); } }

        .rv-stat-card { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; padding: 16px; display: flex; align-items: center; gap: 14px; }
        .rv-stat-wide { grid-column: span 1; }
        .rv-stat-icon { width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .rv-stat-num { font-size: 1.35rem; font-weight: 800; color: #202124; line-height: 1; }
        .rv-stat-label { font-size: 0.72rem; color: #80868b; margin-top: 3px; font-weight: 500; }

        /* ── Action pills ── */
        .rv-overdue-pill { display: inline-flex; align-items: center; gap: 5px; background: rgba(251,188,4,0.08); border: 1px solid rgba(251,188,4,0.2); color: #f9a825; border-radius: 20px; padding: 3px 10px; font-size: 0.73rem; font-weight: 600; }
        .rv-amount { font-weight: 700; color: #202124; font-size: 0.875rem; }

        /* ── Reminder / Dates ── */
        .rv-reminder { display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem; color: #5f6368; }
        .rv-reminder-overdue { color: #ea4335; font-weight: 600; }
        .rv-reminder-warning { color: #f9a825; font-weight: 600; }

        /* ── Empty state ── */
        .rv-empty { display: flex; flex-direction: column; align-items: center; padding: 50px 20px; color: #9aa0a6; text-align: center; gap: 8px; }
        .rv-empty i { font-size: 2.5rem; }
        .rv-empty span { font-size: 0.87rem; }
    </style>

    {{-- Manage Contract Modal --}}
    <div class="modal fade" id="manageContractModal" aria-labelledby="manageContractModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;" id="manageContractModalContent">
                <!-- Content injected via AJAX -->
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

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

            document.querySelectorAll('.open-contract-modal').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url     = this.dataset.url;
                    const content = document.getElementById('manageContractModalContent');
                    const modalEl = document.getElementById('manageContractModal');

                    content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#006666;"></i><p class="mt-2 text-muted">Loading form...</p></div>';

                    bootstrap.Modal.getOrCreateInstance(modalEl).show();

                    fetch(url)
                        .then(function (r) { return r.text(); })
                        .then(function (html) {
                            content.innerHTML = html;
                            execScripts(content);  // ← run <script> blocks from the partial
                        })
                        .catch(function () {
                            content.innerHTML = '<div class="p-5 text-center text-danger"><i class="bx bx-error" style="font-size:2rem;"></i><p>Could not load form. Please try again.</p></div>';
                        });
                });
            });
        });
    </script>
    @include('inc.client-modal')
@endsection
