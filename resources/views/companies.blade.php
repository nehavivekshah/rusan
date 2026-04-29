@extends('layout')
@section('title', 'Companies - Rusan')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        // Aggregate stats
        $totalCompanies = $companies->count();
        $activeCompanies = $companies->where('status', '1')->count();
        $inactiveCompanies = $companies->where('status', '0')->count();
        $gstCompanies = $companies->whereNotNull('gst')->where('gst', '!=', '')->count();
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Companies'])

        <div class="dash-container">

            {{-- ── Stat Cards Row ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(22, 63, 122,0.1);color:#163f7a;">
                        <i class="bx bx-building"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $totalCompanies }}</div>
                        <div class="pj-stat-label">Total Companies</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52,168,83,0.1);color:#163f7a;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#163f7a;">{{ $activeCompanies }}</div>
                        <div class="pj-stat-label">Active Companies</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                        <i class="bx bx-minus-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#ea4335;">{{ $inactiveCompanies }}</div>
                        <div class="pj-stat-label">Inactive</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-file"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#1a73e8;">{{ $gstCompanies }}</div>
                        <div class="pj-stat-label">GST Registered</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/companies" method="GET" id="companyFilterForm" class="d-flex align-items-center gap-2">
                        <select name="status" id="companyStatusFilter" class="form-select" onchange="this.form.submit()"
                            style="width: auto; min-width: 140px;">
                            <option value="">All Status</option>
                            <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </form>

                    <span class="lb-page-count">
                        {{ $totalCompanies }} {{ $totalCompanies == 1 ? 'Company' : 'Companies' }}
                    </span>
                </div>
                <div class="leads-toolbar-right gap-2">
                    {{-- View Toggle --}}
                    <div class="pj-view-toggle">
                        <button class="pj-view-btn" id="cardViewBtn" title="Card View" onclick="setView('card')">
                            <i class="bx bx-grid-alt"></i>
                        </button>
                        <button class="pj-view-btn active" id="tableViewBtn" title="Table View" onclick="setView('table')">
                            <i class="bx bx-list-ul"></i>
                        </button>
                    </div>
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('All', $roleArray))
                        <button type="button" class="lb-btn lb-btn-primary open-company-modal" data-url="/manage-company?ajax=1">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Company</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ════════════════════════════════
            CARD VIEW
            ════════════════════════════════ --}}
            <div id="cardView" class="pj-card-grid mb-4" style="display:none;">
                @forelse($companies as $company)
                    <div class="pj-card open-company-modal" data-url="/view-company?id={{ $company->id }}&ajax=1">
                        {{-- Top accent --}}
                        <div class="pj-card-accent" style="background: linear-gradient(90deg, #163f7a, #0f2d57);"></div>

                        {{-- Header --}}
                        <div class="pj-card-header">
                            <div class="pj-card-avatar">
                                @if($company->logo)
                                    <img src="{{ asset('assets/images/company/logos/' . $company->logo) }}" alt="" style="width:100%; height:100%; border-radius:12px; object-fit:contain;">
                                @else
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="pj-card-meta">
                                <div class="pj-card-name">{{ $company->name }}</div>
                                <div class="pj-card-id">
                                    {{ $company->industry ?? 'Global Company' }}
                                </div>
                            </div>
                            <div class="pj-card-actions">
                                <button type="button" class="btn kb-action-btn open-company-modal" data-url="/manage-company?id={{ $company->id }}&ajax=1" title="Edit"
                                    style="background:rgba(22, 63, 122,0.08);color:#163f7a; border:none;">
                                    <i class="bx bx-pencil"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="pj-card-info mt-2">
                            <div class="pj-info-row">
                                <i class="bx bx-envelope"></i>
                                <span>{{ $company->email ?? 'No email' }}</span>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-phone"></i>
                                <span>{{ $company->mob ?? 'No phone' }}</span>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-map"></i>
                                <span>{{ $company->city }}{{ $company->state ? ', '.$company->state : '' }}</span>
                            </div>
                        </div>

                        {{-- Footer Badge --}}
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            @if($company->gst)
                                <span class="badge bg-light text-primary border" style="font-size:0.65rem;">GST: {{ $company->gst }}</span>
                            @endif
                            @if($company->plan)
                                @php $planInfo = $plans->where('name', 'ilike', $company->plan)->first(); @endphp
                                <span class="badge bg-light text-success border" style="font-size:0.65rem; text-transform:capitalize;">
                                    Plan: {{ $company->plan }} {{ $planInfo ? '(₹'.number_format($planInfo->price, 0).')' : '' }}
                                </span>
                            @endif
                            @if($company->status == 1)
                                <span class="pv-badge pv-badge-success">Active</span>
                            @else
                                <span class="pv-badge pv-badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="pj-empty" style="grid-column:1/-1;">
                        <i class="bx bx-building"></i>
                        <p>No companies found.</p>
                        @if(in_array('All', $roleArray))
                            <a href="/manage-company" class="lb-btn lb-btn-primary mt-2"><i class="bx bx-plus"></i> Add Company</a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- ════════════════════════════════
            TABLE VIEW
            ════════════════════════════════ --}}
            <div id="tableView" class="dash-card mb-4"
                style="background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" id="lists" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company Details</th>
                                <th class="m-none">Contact</th>
                                <th class="m-none">Tax / GST</th>
                                <th class="m-none">Plan</th>
                                <th class="m-none">Location</th>
                                <th class="text-center">Status</th>
                                <th class="text-center position-sticky end-0 mw60" data-orderable="false"
                                    style="z-index:1;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $k=>$company)
                                <tr class="pointer-cursor selectrow open-company-modal"
                                    data-url="/view-company?id={{ $company->id }}&ajax=1">
                                    <td class="fw-bold text-muted" style="font-size:0.75rem;">
                                        {{ $k+1 }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm"
                                                style="background:linear-gradient(135deg,#163f7a,#0f2d57);color:#fff; overflow:hidden;">
                                                @if($company->logo)
                                                    <img src="{{ asset('assets/images/company/logos/' . $company->logo) }}" alt="" style="width:100%; height:100%; object-fit:contain; background:#fff;">
                                                @else
                                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-600 text-truncate" style="max-width:200px;">{{ $company->name }}</div>
                                                <div class="small text-muted text-truncate" style="max-width:200px;">{{ $company->industry ?? 'Business Services' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <div class="small fw-500">{{ $company->email }}</div>
                                        <div class="small text-muted">{{ $company->mob }}</div>
                                    </td>
                                    <td class="m-none">
                                        @if($company->gst)
                                            <div class="badge bg-light text-dark border fw-normal" style="font-size:0.7rem;">{{ $company->gst }}</div>
                                        @else
                                            <span class="text-muted small">Not provided</span>
                                        @endif
                                    </td>
                                    <td class="m-none">
                                        @if($company->plan)
                                            @php $planInfo = $plans->where('name', 'ilike', $company->plan)->first(); @endphp
                                            <div class="pv-badge pv-badge-info" style="text-transform: capitalize;">{{ $company->plan }}</div>
                                            @if($planInfo)
                                                <div class="small text-muted mt-1">₹{{ number_format($planInfo->price, 0) }}/mo</div>
                                            @endif
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="m-none">
                                        <div class="small">{{ $company->city }}</div>
                                        <div class="small text-muted">{{ $company->state }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($company->status == 1)
                                            <span class="pv-badge pv-badge-success accountstatus" id="{{ $company->id }}" data-page="companyDeactivate">Active</span>
                                        @else
                                            <span class="pv-badge pv-badge-danger accountstatus" id="{{ $company->id }}" data-page="companyActivate">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn kb-action-btn kb-action-edit open-company-modal" 
                                                data-url="/manage-company?id={{ $company->id }}&ajax=1" title="Edit">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete"
                                                id="{{ $company->id }}" date-page="companyDelete" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </a>
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

    {{-- Manage Company Modal --}}
    <div class="modal fade" id="manageCompanyModal" aria-labelledby="manageCompanyModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;" id="manageCompanyModalContent">
                <!-- Content injected via AJAX -->
            </div>
        </div>
    </div>

    <style>
        /* ── Project Stat Cards (Reused) ── */
        .pj-stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        @media (max-width: 768px) {
            .pj-stat-row {  grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 480px) {
            .pj-stat-row { grid-template-columns: 1fr; }
        }

        .pj-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow 0.18s;
        }

        .pj-stat-card:hover {  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); }

        .pj-stat-icon {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; flex-shrink: 0;
        }

        .pj-stat-num { font-size: 1.2rem; font-weight: 700; color: #202124; line-height: 1.2; }
        .pj-stat-label { font-size: 0.72rem; color: #80868b; font-weight: 500; margin-top: 2px; }

        /* ── View Toggle ── */
        .pj-view-toggle {
            display: flex; gap: 3px; background: #f1f3f4; border-radius: 20px; padding: 3px;
        }

        .pj-view-btn {
            width: 30px; height: 30px; border: none; background: transparent;
            border-radius: 17px; cursor: pointer; color: #80868b;
            font-size: 1rem; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }

        .pj-view-btn.active { background: #fff; color: #163f7a; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12); }

        /* ── Card Grid ── */
        .pj-card-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;
        }

        .pj-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 16px;
            overflow: hidden; cursor: pointer; transition: box-shadow 0.2s, transform 0.18s;
            position: relative; padding: 0 16px 16px;
        }

        .pj-card:hover { box-shadow: 0 8px 28px rgba(0, 0, 0, 0.10); transform: translateY(-2px); }
        .pj-card-accent { height: 4px; margin: 0 -16px 14px; }

        .pj-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .pj-card-avatar {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, #163f7a, #0f2d57);
            color: #fff; font-size: 1.1rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        .pj-card-meta { flex: 1; min-width: 0; }
        .pj-card-name { font-size: 0.9rem; font-weight: 700; color: #202124; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pj-card-id { font-size: 0.68rem; color: #80868b; }

        .pj-card-info { display: flex; flex-direction: column; gap: 6px; }
        .pj-info-row { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #5f6368; }
        .pj-info-row i { font-size: 0.95rem; color: #163f7a; }

        .pj-empty { text-align: center; padding: 60px 20px; color: #9aa0a6; }
        .pj-empty i { font-size: 3rem; display: block; margin-bottom: 12px; color: #dadce0; }
        .pj-empty p { font-size: 0.85rem; margin: 0; }

        .pv-badge {
            padding: 3px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 600;
        }
        .pv-badge-success { background: rgba(52,168,83,0.1); color: #163f7a; }
        .pv-badge-danger { background: rgba(234,67,53,0.1); color: #ea4335; }
        .pv-badge-info { background: rgba(26,115,232,0.1); color: #1a73e8; }
    </style>

    <script>
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
                localStorage.setItem('company_view_pref', 'card');
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
                cardBtn.classList.remove('active');
                tableBtn.classList.add('active');
                localStorage.setItem('company_view_pref', 'table');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pref = localStorage.getItem('company_view_pref');
            if (pref === 'card') {
                setView('card');
            }

            // AJAX Modal trigger logic (Reused from contracts module)
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

            // Event delegation for dynamically added rows/cards
            document.addEventListener('click', function(e) {
                // If it's a delete or status change, let those specific handlers run (they bubble or we stop props)
                if (e.target.closest('.delete') || e.target.closest('.accountstatus') || e.target.closest('.btn-close')) {
                    return; 
                }

                const trigger = e.target.closest('.open-company-modal');
                if (trigger) {
                    e.preventDefault();
                    // No stopPropagation here so we don't break other things, 
                    // but we've already returned if it was a non-modal action.
                    
                    const url = trigger.dataset.url;
                    const content = document.getElementById('manageCompanyModalContent');
                    const modalEl = document.getElementById('manageCompanyModal');

                    content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#163f7a;"></i><p class="mt-2 text-muted">Loading form...</p></div>';

                    bootstrap.Modal.getOrCreateInstance(modalEl).show();

                    fetch(url)
                        .then(r => r.text())
                        .then(html => {
                            content.innerHTML = html;
                            execScripts(content);
                        })
                        .catch(() => {
                            content.innerHTML = '<div class="p-5 text-center text-danger"><i class="bx bx-error" style="font-size:2rem;"></i><p>Could not load form. Please try again.</p></div>';
                        });
                }
            });
        });
    </script>
@endsection
