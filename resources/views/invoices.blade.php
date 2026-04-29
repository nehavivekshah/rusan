@extends('layout')
@section('title', 'Invoices - Rusan')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        $total = $invoices->count();
        $paidCount = $invoices->where('status', 'paid')->count();
        $unpaidCount = $invoices->where('status', 'unpaid')->count();
        $partialCount = $invoices->whereNotIn('status', ['paid', 'unpaid'])->count();
        $totalValue = $invoices->sum('total_amount');
        $paidValue = $invoices->where('status', 'paid')->sum('total_amount');

        $statusConfig = [
            'paid' => ['#163f7a', 'Paid', 'bx bx-check-circle'],
            'unpaid' => ['#ea4335', 'Unpaid', 'bx bx-x-circle'],
            'partial' => ['#f29900', 'Partial', 'bx bx-time-five'],
        ];
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Invoices'])

        <div class="dash-container">

            {{-- ── Stat Cards ── --}}
        <div class="inv-stat-row mb-4">
            @foreach([
                ['Total Invoices', $total,        '#163f7a', 'bx bx-file-blank'],
                ['Paid',           $paidCount,   '#163f7a', 'bx bx-check-circle'],
                ['Unpaid',         $unpaidCount, '#ea4335', 'bx bx-x-circle'],
                ['Total Value',    '₹'.number_format($totalValue, 0), '#163f7a', 'bx bx-rupee'],
                ['Collected',      '₹'.number_format($paidValue, 0), '#163f7a', 'bx bx-trending-up'],
            ] as [$label, $count, $color, $icon])
                @php
                    $fVal = ($label == 'Total Invoices' || $label == 'Total Value' || $label == 'Collected') ? 'all' : strtolower($label);
                @endphp
                <div class="inv-stat-card pr-filter-trigger" data-filter="{{ $fVal }}" style="cursor:pointer;">
                    <div class="inv-stat-icon" style="background:{{ $color }}15;color:{{ $color }};">
                        <i class="{{ $icon }}"></i>
                    </div>
                    <div>
                        <div class="inv-stat-num" style="color:{{ in_array($label, ['Paid','Collected']) ? '#163f7a' : (in_array($label, ['Unpaid']) ? '#ea4335' : '#202124') }};">{{ $count }}</div>
                        <div class="inv-stat-label">{{ $label }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Toolbar ── --}}
        <div class="leads-toolbar mb-4">
            <div class="leads-toolbar-left gap-3">
                <span class="lb-page-count">
                    <i class="bx bx-file-blank"></i>
                    {{ $total }} {{ $total == 1 ? 'Invoice' : 'Invoices' }}
                </span>
                <span class="ok-pipeline-total">
                    <i class="bx bx-rupee"></i>
                    {{ number_format($totalValue, 2) }}
                    <span class="ok-pipeline-label">Total Value</span>
                </span>
            </div>
            <div class="leads-toolbar-right gap-2">
                {{-- Status filter pills --}}
                <div class="pr-filter-group" id="statusFilterGroup">
                    <button class="pr-filter active" data-filter="all">All</button>
                    <button class="pr-filter" data-filter="paid" style="--pr-color:#163f7a;"><i class="bx bx-check-circle"></i> Paid</button>
                    <button class="pr-filter" data-filter="unpaid" style="--pr-color:#ea4335;"><i class="bx bx-x-circle"></i> Unpaid</button>
                    <button class="pr-filter" data-filter="partial" style="--pr-color:#f29900;"><i class="bx bx-time-five"></i> Partial</button>
                </div>
                {{-- Invoice Type Filter --}}
                <div class="lb-filter-wrapper">
                    <select class="form-select" id="typeFilter" style="min-width:180px; height:36px !important; border-radius:20px; font-size:0.85rem; padding:0 15px !important;">
                        <option value="">All Types</option>
                        @foreach($availableTypes as $type)
                            <option value="{{ $type }}" {{ $currentType == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button class="lb-icon-btn" onclick="location.replace('/invoices')" title="Refresh">
                    <i class="bx bx-refresh"></i>
                </button>
                @if(in_array('invoice_add', $roleArray) || in_array('All', $roleArray))
                    <a href="/manage-invoice" class="lb-btn lb-btn-primary">
                        <i class="bx bx-plus"></i>
                        <span class="d-none d-sm-inline">New Invoice</span>
                    </a>
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
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th class="m-none">Company</th>
                                <th>Amount</th>
                                <th class="m-none">Type</th>
                                <th class="m-none">Date</th>
                                <th class="m-none">Due Date</th>
                                <th style="width:80px;">Status</th>
                                <th class="text-center position-sticky end-0" style="width:120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($invoices as $k => $invoice)
                            @php
                                $st = $invoice->status ?? 'unpaid';
                                $isPaid = $st == 'paid';
                                $isUnpaid = $st == 'unpaid';
                                $isOverdue = !$isPaid && !empty($invoice->due_date) &&
                                    \Carbon\Carbon::parse($invoice->due_date)->isPast();
                                [$stColor, $stIcon] = [
                                    'paid' => ['#163f7a', 'bx bx-check-circle'],
                                    'unpaid' => ['#ea4335', 'bx bx-x-circle'],
                                    'partial' => ['#f29900', 'bx bx-time-five']
                                ][$st] ?? ['#80868b', 'bx bx-file'];
                            @endphp
                            <tr class="inv-row" data-status="{{ $st }}">
                                <td class="m-none text-muted" style="font-size:0.78rem;">{{ $k + 1 }}</td>
                                <td>
                                    <span class="inv-number">INV-{{ $invoice->invoice_number }}</span>
                                </td>
                                <td>
                                    <span class="pr-client-cell">
                                        <span class="pr-client-avatar">
                                            {{ strtoupper(substr($invoice->client_name ?? 'C', 0, 1)) }}
                                        </span>
                                        <div>
                                            <a href="javascript:void(0)" class="fw-600 view-client-details text-decoration-none" id="{{ $invoice->client_id }}" style="color:inherit;">
                                                {{ $invoice->client_name ?? '—' }}
                                            </a>
                                            <div class="text-muted small m-none">{{ $invoice->client_email ?? '' }}</div>
                                        </div>
                                    </span>
                                </td>
                                <td class="m-none text-muted small">{{ substr($invoice->client_company ?? '—', 0, 22) }}</td>
                                <td>
                                    <span class="pr-amount">
                                        <i class="bx bx-rupee"></i>
                                        {{ number_format($invoice->total_amount ?? 0, 0) }}
                                    </span>
                                </td>
                                <td class="m-none">
                                    <span class="inv-type-pill">{{ $invoice->invoice ?? '—' }}</span>
                                </td>
                                <td class="m-none text-muted small">
                                    {{ !empty($invoice->date) ? date_format(date_create($invoice->date), 'd M, Y') : '—' }}
                                </td>
                                <td class="m-none small">
                                    @if(!empty($invoice->due_date))
                                        <span class="{{ $isOverdue && !$isPaid ? 'text-danger fw-600' : 'text-muted' }}">
                                            {{ date_format(date_create($invoice->due_date), 'd M, Y') }}
                                            @if($isOverdue && !$isPaid)
                                                <span class="inv-overdue-dot">Overdue</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td data-search="{{ $st }}" data-order="{{ $st }}">
                                    <span class="pr-status-pill" style="background:{{ $stColor }}15;color:{{ $stColor }};">
                                        <i class="{{ $stIcon }}"></i>
                                        {{ ucfirst($st) }}
                                    </span>
                                </td>
                                <td class="position-sticky end-0">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        {{-- View PDF --}}
                                        <a href="/invoices/pdf/preview/{{ $invoice->id }}" class="btn kb-action-btn"
                                            target="_blank" title="View PDF"
                                            style="background:rgba(26,115,232,0.08);color:#1a73e8;">
                                            <i class="bx bx-file"></i>
                                        </a>
                                        {{-- Edit --}}
                                        @if(in_array('invoice_edit', $roleArray) || in_array('All', $roleArray))
                                            <a href="/manage-invoice?id={{ $invoice->id }}" class="btn kb-action-btn"
                                                title="Edit" style="background:rgba(22, 63, 122,0.08);color:#163f7a;">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        @endif
                                        {{-- Send Email --}}
                                        <a href="mailto:{{ $invoice->client_email ?? '' }}?subject=Invoice INV-{{ $invoice->invoice_number }}&body=Please find attached invoice."
                                            class="btn kb-action-btn" title="Send Email"
                                            style="background:rgba(251,188,4,0.08);color:#f9a825;">
                                            <i class="bx bx-envelope"></i>
                                        </a>
                                        {{-- Delete --}}
                                        @if(in_array('invoice_delete', $roleArray) || in_array('All', $roleArray))
                                            <a href="javascript:void(0)" class="btn kb-action-btn delete"
                                                id="{{ $invoice->id }}" data-page="invoiceDelete" title="Delete"
                                                style="background:rgba(234,67,53,0.08);color:#ea4335;">
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

                @if($invoices->isEmpty())
                    <div class="rv-empty">
                        <i class="bx bx-file-blank"></i>
                        <span>No invoices created yet.</span>
                        @if(in_array('invoice_add', $roleArray) || in_array('All', $roleArray))
                            <a href="/manage-invoice" class="lb-btn lb-btn-primary mt-2">
                                <i class="bx bx-plus"></i> Create Invoice
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </section>

    @include('inc.client-modal')

    <style>
        /* ── Stat Row ── */
        .inv-stat-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
        }

        @media (max-width: 900px) {
            .inv-stat-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 576px) {
            .inv-stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .inv-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow 0.15s;
        }

        .inv-stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .inv-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .inv-stat-num {
            font-size: 1.35rem;
            font-weight: 800;
            color: #202124;
            line-height: 1;
        }

        .inv-stat-label {
            font-size: 0.72rem;
            color: #80868b;
            margin-top: 3px;
            font-weight: 500;
        }

        /* ── Invoice Number ── */
        .inv-number {
            font-size: 0.82rem;
            font-weight: 700;
            color: #163f7a;
            font-family: 'Courier New', monospace;
        }

        /* ── Type pill ── */
        .inv-type-pill {
            display: inline-block;
            background: #f1f3f4;
            border-radius: 20px;
            padding: 2px 9px;
            font-size: 0.71rem;
            font-weight: 600;
            color: #5f6368;
        }

        /* ── Status pill ── */
        .inv-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            border-radius: 20px;
            padding: 3px 9px;
            font-size: 0.71rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .inv-status-pill i {
            font-size: 0.85rem;
        }

        /* ── Overdue dot ── */
        .inv-overdue-dot {
            display: inline-block;
            background: rgba(234, 67, 53, 0.10);
            color: #ea4335;
            border-radius: 20px;
            padding: 1px 6px;
            font-size: 0.65rem;
            font-weight: 700;
            margin-left: 3px;
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

@push('scripts')
    <script>
        $(document).ready(function () {
            // Ensure DataTable is initialized and get instance
            const table = $('#lists').DataTable({
                retrieve: true,
                order: [[1, 'desc']], // Default sort by Invoice # (Index 1) DESC
                columnDefs: [
                    { targets: [0, 9], orderable: false } // Disable sort for # and Action
                ],
                pageLength: 50, // Display more rows by default
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search invoices..."
                }
            });

            function applyFilter(f) {
                // Sync the pills UI
                $('.pr-filter').removeClass('active');
                $(`.pr-filter[data-filter="${f}"]`).addClass('active');

                if (f === 'all') {
                    table.column(8).search('').draw();
                } else {
                    // Exact match using regex to prevent partial matches
                    table.column(8).search('^' + f + '$', true, false).draw();
                }
            }

            // Type filter change logic
            $('#typeFilter').on('change', function () {
                const type = $(this).val();
                let url = new URL(window.location.href);
                if (type) {
                    url.searchParams.set('type', type);
                } else {
                    url.searchParams.delete('type');
                }
                window.location.href = url.toString();
            });

            // Status filter pills logic
            $('#statusFilterGroup').on('click', '.pr-filter', function () {
                applyFilter($(this).data('filter'));
            });

            // Stat cards row logic
            $('.inv-stat-row').on('click', '.pr-filter-trigger', function () {
                applyFilter($(this).data('filter'));
            });
        });
    </script>
@endpush

@endsection
