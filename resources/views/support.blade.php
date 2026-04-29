@extends('layout')

@section('title', 'Customer Support - Rusan')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Customer Support'])

        <div class="dash-container">

            {{-- ── Stat Cards Row ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-support"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['total'] }}</div>
                        <div class="pj-stat-label">Total Tickets</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(255,193,7,0.1);color:#ffc107;">
                        <i class="bx bx-time-five"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#ffc107;">{{ $stats['open'] }}</div>
                        <div class="pj-stat-label">Open / New</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(22, 63, 122,0.1);color:#163f7a;">
                        <i class="bx bx-cog"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#163f7a;">{{ $stats['processed'] }}</div>
                        <div class="pj-stat-label">In Progress</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52,168,83,0.1);color:#163f7a;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#163f7a;">{{ $stats['closed'] }}</div>
                        <div class="pj-stat-label">Resolved</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <div class="d-flex align-items-center gap-2">
                        <select id="statusFilter" class="form-select" style="width: auto; min-width: 140px;">
                            <option value="all">All Status</option>
                            <option value="0">Open</option>
                            <option value="1">Processing</option>
                            <option value="2">Resolved</option>
                        </select>
                        @if(Auth::user()->role == 'master')
                            <select id="companyFilter" class="form-select" style="width: auto; min-width: 180px;">
                                <option value="all">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <div class="input-group search-box d-none d-md-flex" style="width: 200px;">
                            <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="ticketSearch" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <div class="leads-toolbar-right gap-2">
                    <div class="pj-view-toggle">
                        <button class="pj-view-btn" id="cardViewBtn" title="Card View" onclick="setView('card')">
                            <i class="bx bx-grid-alt"></i>
                        </button>
                        <button class="pj-view-btn active" id="tableViewBtn" title="Table View" onclick="setView('table')">
                            <i class="bx bx-list-ul"></i>
                        </button>
                    </div>
                    @if(in_array('support_add', $roleArray) || in_array('All', $roleArray))
                    <button class="btn btn-teal px-3 open-support-modal"
                        style="background:#163f7a; color:white; border-radius:20px; font-size: 0.85rem;"
                        data-url="/manage-support">
                        <i class="bx bx-plus me-1"></i> New Ticket
                    </button>
                    @endif
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>

            {{-- ── Card View ── --}}
            <div id="cardView" class="pj-card-grid mb-4" style="display:none;">
                @forelse($tickets as $ticket)
                    <div class="pj-card ticket-card-wrapper open-support-modal" data-url="/manage-support?id={{ $ticket->id }}"
                        data-status="{{ $ticket->status }}"
                        data-company-id="{{ $ticket->cid }}"
                        data-search="{{ strtolower($ticket->ticket_no . ' ' . $ticket->subject . ' ' . ($ticket->company->name ?? '')) }}">

                        <div class="pj-card-accent"
                            style="background: @if($ticket->status == 0)#ffc107 @elseif($ticket->status == 1)#163f7a @else#163f7a @endif;">
                        </div>

                        <div class="pj-card-header">
                            <div class="pj-card-avatar" style="background: linear-gradient(135deg, #163f7a, #1a73e8);">
                                <i class="bx bx-news"></i>
                            </div>
                            <div class="pj-card-meta">
                                <div class="pj-card-name">{{ $ticket->subject }}</div>
                                <div class="pj-card-id">{{ $ticket->ticket_no }} · {{ $ticket->created_at->format('d M, Y') }}
                                </div>
                            </div>
                            <div class="pj-card-actions">
                                @if(in_array('support_add', $roleArray) || in_array('All', $roleArray))
                                <button type="button" class="btn kb-action-btn open-support-modal"
                                    data-url="/manage-support?id={{ $ticket->id }}">
                                    <i class="bx bx-pencil"></i>
                                </button>
                                @endif
                            </div>
                        </div>

                        <div class="pj-card-info mt-2">
                            <div class="pj-info-row">
                                <i class="bx bx-building"></i>
                                <span>{{ $ticket->company->name ?? 'N/A' }}</span>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-flag"></i>
                                <span
                                    class="badge @if($ticket->priority == 'High') bg-danger @elseif($ticket->priority == 'Medium') bg-warning text-dark @else bg-info @endif"
                                    style="font-size:0.6rem; padding: 2px 6px;">
                                    {{ $ticket->priority }} Priority
                                </span>
                            </div>
                        </div>

                        <p class="text-muted small mt-2 mb-0 text-truncate">
                            {{ $ticket->description }}
                        </p>

                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            @if($ticket->status == 0)
                                <span class="pv-badge pv-badge-warning">Open</span>
                            @elseif($ticket->status == 1)
                                <span class="pv-badge pv-badge-info">In Progress</span>
                            @else
                                <span class="pv-badge pv-badge-success">Resolved</span>
                            @endif
                            
                            @if(in_array('support_delete', $roleArray) || in_array('All', $roleArray))
                            <button class="btn btn-sm text-danger border-0 p-0 delete-support-btn" data-id="{{ $ticket->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="pj-empty" style="grid-column:1/-1;">
                        <i class="bx bx-support"></i>
                        <p>No support tickets found.</p>
                    </div>
                @endforelse
            </div>

            {{-- ── Table View ── --}}
            <div id="tableView" class="dash-card mb-4"
                style="background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ticket Details</th>
                                <th>Company</th>
                                <th>Priority</th>
                                <th class="text-center">Status</th>
                                <th class="text-center position-sticky end-0 bg-white mw60">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $k => $ticket)
                                <tr class="pointer-cursor ticket-card-wrapper open-support-modal"
                                    data-url="/manage-support?id={{ $ticket->id }}" 
                                    data-status="{{ $ticket->status }}"
                                    data-company-id="{{ $ticket->cid }}"
                                    data-search="{{ strtolower($ticket->ticket_no . ' ' . $ticket->subject . ' ' . ($ticket->company->name ?? '')) }}">
                                    <td class="small fw-bold text-muted">{{ $k + 1 }}</td>
                                    <td>
                                        <div class="fw-600 mb-0" style="font-size:0.85rem;">{{ $ticket->subject }}</div>
                                        <div class="small text-muted">{{ $ticket->ticket_no }} ·
                                            {{ $ticket->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-500">{{ $ticket->company->name ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge @if($ticket->priority == 'High') bg-danger @elseif($ticket->priority == 'Medium') bg-warning text-dark @else bg-info @endif"
                                            style="font-size:0.65rem;">
                                            {{ $ticket->priority }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($ticket->status == 0)
                                            <span class="pv-badge pv-badge-warning">Open</span>
                                        @elseif($ticket->status == 1)
                                            <span class="pv-badge pv-badge-info">Processing</span>
                                        @else
                                            <span class="pv-badge pv-badge-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @if(in_array('support_add', $roleArray) || in_array('All', $roleArray))
                                            <button type="button" class="btn kb-action-btn open-support-modal"
                                                data-url="/manage-support?id={{ $ticket->id }}" title="Edit">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            @endif
                                            @if(in_array('support_delete', $roleArray) || in_array('All', $roleArray))
                                            <button class="btn kb-action-btn kb-action-del delete-support-btn"
                                                data-id="{{ $ticket->id }}" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    {{-- Support Ticket Modal --}}
    <div class="modal fade" id="supportModal" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none;" id="supportModalContent">
                <!-- Content injected via AJAX -->
            </div>
        </div>
    </div>

    <style>
        /* Reusing established design tokens */
        .pj-stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        @media (max-width: 768px) {
            .pj-stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .pj-stat-row {
                grid-template-columns: 1fr;
            }
        }

        .pj-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pj-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .pj-stat-num {
            font-size: 1.15rem;
            font-weight: 700;
            color: #202124;
            line-height: 1;
        }

        .pj-stat-label {
            font-size: 0.7rem;
            color: #80868b;
            font-weight: 500;
            margin-top: 4px;
        }

        .pj-view-toggle {
            display: flex;
            gap: 3px;
            background: #f1f3f4;
            border-radius: 20px;
            padding: 3px;
        }

        .pj-view-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: transparent;
            border-radius: 17px;
            cursor: pointer;
            color: #80868b;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }

        .pj-view-btn.active {
            background: #fff;
            color: #163f7a;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
        }

        .pj-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }

        .pj-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            padding: 0 16px 16px;
        }

        .pj-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transform: translateY(-3px);
        }

        .pj-card-accent {
            height: 4px;
            margin: 0 -16px 14px;
        }

        .pj-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pj-card-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            color: #fff;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pj-card-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #202124;
        }

        .pj-card-id {
            font-size: 0.65rem;
            color: #80868b;
        }

        .pj-info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            color: #5f6368;
            margin-top: 6px;
        }

        .pj-info-row i {
            font-size: 0.9rem;
            color: #163f7a;
        }

        .pv-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .pv-badge-success {
            background: rgba(52, 168, 83, 0.1);
            color: #163f7a;
        }

        .pv-badge-info {
            background: rgba(26, 115, 232, 0.1);
            color: #1a73e8;
        }

        .pv-badge-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .pj-empty {
            text-align: center;
            padding: 50px 20px;
            color: #9aa0a6;
        }

        .pj-empty i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 10px;
            color: #dadce0;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pref = localStorage.getItem('support_view_pref') || 'table';
            setView(pref);

            // Filtering
            const searchInput = document.getElementById('ticketSearch');
            const statusFilter = document.getElementById('statusFilter');
            const companyFilter = document.getElementById('companyFilter');

            function applyFilters() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const statusTerm = statusFilter.value;
                const companyTerm = companyFilter ? companyFilter.value : 'all';
                const items = document.querySelectorAll('.ticket-card-wrapper');

                items.forEach(item => {
                    const searchData = item.dataset.search;
                    const statusData = item.dataset.status;
                    const companyData = item.dataset.companyId;

                    const matchesSearch = searchData.includes(searchTerm);
                    const matchesStatus = statusTerm === 'all' || statusData === statusTerm;
                    const matchesCompany = companyTerm === 'all' || companyData === companyTerm;

                    const isVisible = matchesSearch && matchesStatus && matchesCompany;

                    item.style.display = isVisible ? '' : 'none';
                    if (item.tagName === 'TR') {
                        item.style.display = isVisible ? 'table-row' : 'none';
                    }
                });
            }

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (companyFilter) companyFilter.addEventListener('change', applyFilters);

            // Modal & Delete Delegate
            document.addEventListener('click', function (e) {
                // Handle delete button FIRST
                const delBtn = e.target.closest('.delete-support-btn');
                if (delBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    Swal.fire({
                        title: 'Delete Ticket?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d93025',
                        cancelButtonColor: '#5f6368',
                        confirmButtonText: 'Yes, delete it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/delete-support?id=' + delBtn.dataset.id;
                        }
                    });
                    return;
                }

                // Skip modal open for close button clicks
                if (e.target.closest('.btn-close')) return;

                // Handle modal open
                const trigger = e.target.closest('.open-support-modal');
                if (trigger) {
                    e.preventDefault();
                    loadSupportModal(trigger.dataset.url);
                }
            });
        });

        function setView(view) {
            const cardView = document.getElementById('cardView');
            const tableView = document.getElementById('tableView');
            const cardBtn = document.getElementById('cardViewBtn');
            const tableBtn = document.getElementById('tableViewBtn');

            if (view === 'card') {
                cardView.style.display = 'grid';
                tableView.style.display = 'none';
                cardBtn.classList.add('active');
                tableBtn.classList.remove('active');
                localStorage.setItem('support_view_pref', 'card');
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
                cardBtn.classList.remove('active');
                tableBtn.classList.add('active');
                localStorage.setItem('support_view_pref', 'table');
            }
        }

        function loadSupportModal(url) {
            const content = document.getElementById('supportModalContent');
            const modalEl = document.getElementById('supportModal');
            content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#163f7a;"></i></div>';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            fetch(url).then(res => res.text()).then(html => { content.innerHTML = html; });
        }
    </script>
@endsection
