@extends('layout')
@section('title', 'Customers - Rusan')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        $totalClients = $clients->count();
        $activeClients = $clients->where('status', '1')->count();
        $inactiveClients = $clients->where('status', '0')->count();
        $newThisMonth = $clients->filter(
            fn($c) =>
            !empty($c->created_at) &&
            \Carbon\Carbon::parse($c->created_at)->isCurrentMonth()
        )->count();
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/css/lead-panel.css') }}">

    <section class="task__section">
        @include('inc.header', ['title' => 'Customers'])

        <div class="dash-container">

            {{-- â”€â”€ Stat Cards â”€â”€ --}}
            <div class="cl-stat-row mb-4">
                <div class="cl-stat-card">
                    <div class="cl-stat-icon" style="background:rgba(22, 63, 122,0.10);color:#163f7a;">
                        <i class="bx bx-group"></i>
                    </div>
                    <div>
                        <div class="cl-stat-num">{{ $totalClients }}</div>
                        <div class="cl-stat-label">Total Customers</div>
                    </div>
                </div>
                <div class="cl-stat-card">
                    <div class="cl-stat-icon" style="background:rgba(52,168,83,0.10);color:#163f7a;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="cl-stat-num" style="color:#163f7a;">{{ $activeClients }}</div>
                        <div class="cl-stat-label">Active</div>
                    </div>
                </div>
                <div class="cl-stat-card">
                    <div class="cl-stat-icon" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                        <i class="bx bx-minus-circle"></i>
                    </div>
                    <div>
                        <div class="cl-stat-num" style="color:#ea4335;">{{ $inactiveClients }}</div>
                        <div class="cl-stat-label">Inactive</div>
                    </div>
                </div>
                <div class="cl-stat-card">
                    <div class="cl-stat-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                        <i class="bx bx-user-plus"></i>
                    </div>
                    <div>
                        <div class="cl-stat-num" style="color:#1a73e8;">{{ $newThisMonth }}</div>
                        <div class="cl-stat-label">New This Month</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/clients" method="GET" id="clientFilterForm" class="d-flex align-items-center gap-2">
                        <select name="status" id="clientStatusFilter" class="form-select" onchange="this.form.submit()"
                            style="width: auto; min-width: 130px;">
                            <option value="">All Status</option>
                            <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($status ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <select name="industry" id="clientIndustryFilter" class="form-select" onchange="this.form.submit()"
                            style="width: auto; min-width: 140px;">
                            <option value="">All Industries</option>
                            @if(isset($availableIndustries))
                                @foreach($availableIndustries as $ind)
                                    <option value="{{ $ind }}" {{ ($industry ?? '') == $ind ? 'selected' : '' }}>{{ $ind }}</option>
                                @endforeach
                            @endif
                        </select>
                        <select name="lifecycle_stage" id="clientStageFilter" class="form-select"
                            onchange="this.form.submit()" style="width: auto; min-width: 140px;">
                            <option value="">All Stages</option>
                            @foreach(['Lead', 'Marketing Qualified', 'Sales Qualified', 'Customer', 'Evangelist'] as $stage)
                                <option value="{{ $stage }}" {{ ($lifecycle_stage ?? '') == $stage ? 'selected' : '' }}>
                                    {{ $stage }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="leads-toolbar-right">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('clients_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-client" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Customer</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Table Card --}}
            <div class="dash-card mb-4">
                <div class="table-responsive">
                    <table id="lists" class="leads-table clients" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="m-none">Company</th>
                                <th class="m-none">Email</th>
                                <th class="m-none mw80">Mobile</th>
                                <th class="m-none mw135 text-center">Created</th>
                                <th class="m-none mw80">Status</th>
                                <th class="text-center position-sticky end-0 bg-default mw60">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                                <tr class="view selectrow" id="{{ $client->id ?? '' }}">
                                    {{-- Name --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm"
                                                style="background:linear-gradient(135deg,#163f7a,#0f2d57);color:#fff;">
                                                {{ strtoupper(substr($client->name ?? 'C', 0, 1)) }}
                                            </div>
                                             <div>
                                                <div class="fw-500"><a href="/customer-360/client/{{ $client->id }}" class="text-decoration-none text-dark">{{ $client->name ?? '' }}</a></div>
                                                <div class="text-muted small d-none">{{ $client->company ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Company --}}
                                    <td class="m-none text-muted">
                                        {{ substr(($client->company ?? ''), 0, 22) }}
                                    </td>
                                    {{-- Email --}}
                                    <td class="m-none text-muted">
                                        {{ substr(($client->email ?? ''), 0, 22) }}
                                    </td>
                                    {{-- Mobile --}}
                                    <td class="m-none">{{ $client->mob ?? '' }}</td>
                                    {{-- Created --}}
                                    <td class="m-none text-center">
                                        {!! date_format(date_create($client->created_at ?? ''), 'd M, Y') !!}
                                    </td>
                                    {{-- Status --}}
                                    <td>
                                        @if($client->status == '1')
                                            <span class="inv-status-pill" style="background:rgba(52,168,83,0.10);color:#163f7a;">
                                                <i class="bx bx-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="inv-status-pill" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                                                <i class="bx bx-minus-circle"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Actions --}}
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @if(!empty($client->whatsapp))
                                                <a href="https://api.whatsapp.com/send/?phone={{ $client->whatsapp }}&text=Hi&type=phone_number&app_absent=0"
                                                    target="_blank" class="btn kb-action-btn kb-action-wa" title="WhatsApp">
                                                    <i class="bx bxl-whatsapp"></i>
                                                </a>
                                            @endif
                                            @if(!empty($client->email))
                                                <a href="mailto:{{ $client->email }}" class="btn kb-action-btn kb-action-email"
                                                    title="Email">
                                                    <i class="bx bx-envelope"></i>
                                                </a>
                                            @endif
                                            @if(!empty($client->mob))
                                                <a href="tel:{{ $client->mob }}" class="btn kb-action-btn kb-action-call"
                                                    title="Call">
                                                    <i class="bx bx-phone"></i>
                                                </a>
                                            @endif
                                            @if(in_array('client_edit', $roleArray) || in_array('All', $roleArray))
                                                {{-- Active / Inactive Toggle --}}
                                                <button class="btn kb-action-btn cl-toggle-status
                                                        {{ $client->status == '1' ? 'cl-toggle-active' : 'cl-toggle-inactive' }}"
                                                    data-id="{{ $client->id }}"
                                                    data-status="{{ $client->status }}"
                                                    title="{{ $client->status == '1' ? 'Click to Deactivate' : 'Click to Activate' }}"
                                                    onclick="event.stopPropagation()">
                                                    <i class="bx {{ $client->status == '1' ? 'bx-toggle-right' : 'bx-toggle-left' }}"></i>
                                                </button>
                                                <a href="/manage-client?id={{ $client->id ?? '' }}"
                                                    class="btn kb-action-btn kb-action-edit" title="Edit">
                                                    <i class="bx bx-pencil"></i>
                                                </a>
                                            @endif
                                            @if(in_array('client_delete', $roleArray) || in_array('All', $roleArray))
                                                <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete"
                                                    id="{{ $client->id }}" date-page="clientDelete" title="Delete">
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
            </div>

        </div>
    </section>

    <style>
        /* â”€â”€ Client Stat Row â”€â”€ */
        .cl-stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        @media (max-width: 768px) {
            .cl-stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .cl-stat-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        .cl-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow 0.15s;
        }

        .cl-stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .cl-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .cl-stat-num {
            font-size: 1.35rem;
            font-weight: 800;
            color: #202124;
            line-height: 1;
        }

        .cl-stat-label {
            font-size: 0.72rem;
            color: #80868b;
            margin-top: 3px;
            font-weight: 500;
        }

        /* â”€â”€ Active / Inactive Toggle Button â”€â”€ */
        .cl-toggle-status {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }
        .cl-toggle-active {
            background: rgba(52, 168, 83, 0.12);
            color: #163f7a;
        }
        .cl-toggle-active:hover {
            background: rgba(52, 168, 83, 0.22);
            transform: scale(1.08);
        }
        .cl-toggle-inactive {
            background: rgba(234, 67, 53, 0.10);
            color: #ea4335;
        }
        .cl-toggle-inactive:hover {
            background: rgba(234, 67, 53, 0.20);
            transform: scale(1.08);
        }
        .cl-toggle-status.cl-toggling {
            opacity: 0.45;
            pointer-events: none;
        }

        /* Reuse inv-status-pill from invoices page */
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
    </style>

    {{-- Hidden import form --}}
    <form id="Clientsubmit" action="/import-client-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impClientFile" id="impClientFile" accept=".csv, .xls" style="display:none;" />
    </form>

    @include('inc.client-modal')
</div>


    <script>
    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        document.querySelectorAll('.cl-toggle-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id; const button = this;
                button.classList.add('cl-toggling');
                fetch('/clients/toggle-status', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: JSON.stringify({ id }) })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error('Toggle failed');
                    const isActive = data.status == '1';
                    button.dataset.status = data.status;
                    button.title = isActive ? 'Click to Deactivate' : 'Click to Activate';
                    button.className = 'btn kb-action-btn cl-toggle-status ' + (isActive ? 'cl-toggle-active' : 'cl-toggle-inactive');
                    button.innerHTML = isActive ? '<i class="bx bx-toggle-right"></i>' : '<i class="bx bx-toggle-left"></i>';
                    const row = button.closest('tr'); const pill = row?.querySelector('.inv-status-pill');
                    if (pill) {
                        if (isActive) { pill.style.background = 'rgba(52,168,83,0.10)'; pill.style.color = '#163f7a'; pill.innerHTML = '<i class="bx bx-check-circle"></i> Active'; }
                        else          { pill.style.background = 'rgba(234,67,53,0.10)';  pill.style.color = '#ea4335'; pill.innerHTML = '<i class="bx bx-minus-circle"></i> Inactive'; }
                    }
                    let active = 0, inactive = 0; document.querySelectorAll('.cl-toggle-status').forEach(b => { if (b.dataset.status == '1') active++; else inactive++; });
                    const nums = document.querySelectorAll('.cl-stat-num');
                    if (nums[1]) nums[1].textContent = active; if (nums[2]) nums[2].textContent = inactive;
                    row?.classList.add('table-success'); setTimeout(() => row?.classList.remove('table-success'), 800);
                })
                .catch(() => alert('Could not update status. Please try again.'))
                .finally(() => button.classList.remove('cl-toggling'));
            });
        });
    })();
    </script>
@endsection
