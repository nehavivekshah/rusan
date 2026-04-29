@extends('layout')
@section('title', 'Licensing - Rusan')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Product Licensing'])

        <div class="dash-container">

            {{-- ── Stat Cards Row ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(22, 63, 122,0.1);color:#163f7a;">
                        <i class="bx bx-key"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['total'] }}</div>
                        <div class="pj-stat-label">Total Licenses</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52,168,83,0.1);color:#163f7a;">
                        <i class="bx bx-check-shield"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#163f7a;">{{ $stats['active'] }}</div>
                        <div class="pj-stat-label">Active Keys</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(251,188,4,0.1);color:#fbbc04;">
                        <i class="bx bx-time-five"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#fbbc04;">{{ $stats['expiring_soon'] }}</div>
                        <div class="pj-stat-label">Expiring Soon</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                        <i class="bx bx-error-alt"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#ea4335;">{{ $stats['expired'] }}</div>
                        <div class="pj-stat-label">Expired / Blocked</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <div class="d-flex align-items-center gap-2">
                        <select id="expiryFilter" class="form-select" style="width: auto; min-width: 150px;">
                            <option value="all">All Licenses</option>
                            <option value="active">Active Only</option>
                            <option value="expiring">Expiring (30d)</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <span class="lb-page-count ms-2">
                        {{ $stats['total'] }} Records found
                    </span>
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
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('All', $roleArray))
                        <button type="button" class="lb-btn lb-btn-primary open-license-modal" data-url="/manage-license?ajax=1">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">New License</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ════════════════════════════════
            CARD VIEW
            ════════════════════════════════ --}}
            <div id="cardView" class="pj-card-grid mb-4" style="display:none;">
                @forelse($licenses as $license)
                    @php
                        $isExpired = \Carbon\Carbon::parse($license->expiry_date)->isPast();
                        $isExpiring = !$isExpired && \Carbon\Carbon::parse($license->expiry_date)->diffInDays(now()) <= 30;
                    @endphp
                    <div class="pj-card">
                        <div class="pj-card-accent" style="background: {{ $isExpired ? '#ea4335' : ($isExpiring ? '#fbbc04' : '#163f7a') }};"></div>
                        
                        <div class="pj-card-header">
                            <div class="pj-card-avatar" style="background: {{ $isExpired ? 'rgba(234,67,53,0.1)' : 'rgba(22, 63, 122,0.1)' }}; color: {{ $isExpired ? '#ea4335' : '#163f7a' }};">
                                <i class="bx {{ $isExpired ? 'bx-error' : 'bx-badge-check' }}"></i>
                            </div>
                            <div class="pj-card-meta">
                                <div class="pj-card-name">{{ $license->client_name }}</div>
                                <div class="pj-card-id">{{ $license->project_name }}</div>
                            </div>
                            <div class="pj-card-actions">
                                <button type="button" class="btn kb-action-btn open-license-modal" data-url="/manage-license?id={{ $license->id }}&ajax=1" title="Edit">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pj-card-info mt-3">
                            <div class="pj-info-row">
                                <i class="bx bx-link-external"></i>
                                <span class="text-truncate">{{ $license->deployment_url ?? 'No URL' }}</span>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-key"></i>
                                <code class="small fw-bold text-dark">{{ $license->eselicense_key }}</code>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-calendar-event"></i>
                                <span class="{{ $isExpired ? 'text-danger fw-bold' : '' }}">
                                    Expires: {{ \Carbon\Carbon::parse($license->expiry_date)->format('M d, Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <span class="badge" style="background:rgba(22, 63, 122,0.06); color:#163f7a; font-size:0.65rem;">
                                {{ $license->technology_stack ?? 'Web App' }}
                            </span>
                            <div class="d-flex gap-2">
                                <button class="badge bg-light text-dark border dbbackup" data-domain="{{ $license->deployment_url }}" data-key="{{ $license->eselicense_key }}" title="Backup DB">
                                    <i class="bx bx-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="pj-empty" style="grid-column: 1/-1;">
                        <i class="bx bx-key"></i>
                        <p>No licenses issued yet.</p>
                    </div>
                @endforelse
            </div>

            {{-- ════════════════════════════════
            TABLE VIEW
            ════════════════════════════════ --}}
            <div id="tableView" class="dash-card mb-4" style="background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" id="lists" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client & Project</th>
                                <th>License Key</th>
                                <th class="m-none">Tech Stack</th>
                                <th class="m-none">Expiry Date</th>
                                <th class="text-center">Integrations</th>
                                <th class="text-center position-sticky end-0 mw60" data-orderable="false" style="z-index: 1;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($licenses as $k=>$license)
                                @php
                                    $isExpired = \Carbon\Carbon::parse($license->expiry_date)->isPast();
                                @endphp
                                <tr>
                                    <td class="text-muted fw-bold" style="font-size:0.75rem;">{{ $k+1 }}</td>
                                    <td>
                                        <div class="fw-600 text-dark">{{ $license->client_name }}</div>
                                        <div class="small text-muted">{{ $license->project_name }}</div>
                                        <div class="small text-primary"><i class="bx bx-link-external me-1"></i>{{ $license->deployment_url }}</div>
                                    </td>
                                    <td>
                                        <code class="px-2 py-1 bg-light rounded text-dark fw-bold" style="font-size:0.8rem; letter-spacing:0.5px;">{{ $license->eselicense_key }}</code>
                                    </td>
                                    <td class="m-none">
                                        <span class="badge" style="background:rgba(22, 63, 122,0.08); color:#163f7a; font-weight:500;">
                                            {{ $license->technology_stack ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="m-none">
                                        @if($isExpired)
                                            <div class="text-danger fw-600 small"><i class="bx bx-error-circle me-1"></i>{{ \Carbon\Carbon::parse($license->expiry_date)->format('d M, Y') }}</div>
                                        @else
                                            <div class="text-dark small fw-500">{{ \Carbon\Carbon::parse($license->expiry_date)->format('d M, Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-light border dbbackup" data-domain="{{ $license->deployment_url }}" data-key="{{ $license->eselicense_key }}" title="Download Database">
                                                <i class="bx bx-data"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn kb-action-btn kb-action-edit open-license-modal" 
                                                data-url="/manage-license?id={{ $license->id }}&ajax=1" title="Edit License">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @if(Auth::user()->role == 'master')
                                            <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete"
                                                id="{{ $license->id }}" date-page="licenseDelete" title="Revoke/Delete">
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

    {{-- License Modal --}}
    <div class="modal fade" id="licenseModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;" id="licenseModalContent">
                <!-- Content injected via AJAX -->
            </div>
        </div>
    </div>

    <style>
        /* Shared Styles (Stat Cards, View Toggles - Reused from Projects) */
        .dash-container { padding: 0 20px; }
        .pj-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        @media (max-width: 992px) { .pj-stat-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .pj-stat-row { grid-template-columns: 1fr; } }

        .pj-stat-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 14px;
            padding: 16px 18px; display: flex; align-items: center; gap: 14px;
            transition: box-shadow 0.2s;
        }
        .pj-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .pj-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
        .pj-stat-num { font-size: 1.25rem; font-weight: 700; color: #202124; line-height: 1.2; }
        .pj-stat-label { font-size: 0.72rem; color: #80868b; font-weight: 500; margin-top: 2px; }

        .pj-view-toggle { display: flex; gap: 3px; background: #f1f3f4; border-radius: 20px; padding: 3px; }
        .pj-view-btn { width: 32px; height: 32px; border: none; background: transparent; border-radius: 50%; cursor: pointer; color: #80868b; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .pj-view-btn.active { background: #fff; color: #163f7a; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }

        .pj-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .pj-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 16px; position: relative;
            padding: 16px; transition: transform 0.2s, box-shadow 0.2s; overflow: hidden;
        }
        .pj-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .pj-card-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; }
        
        .pj-card-header { display: flex; align-items: center; gap: 12px; }
        .pj-card-avatar { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .pj-card-meta { flex: 1; min-width: 0; }
        .pj-card-name { font-size: 0.88rem; font-weight: 700; color: #202124; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pj-card-id { font-size: 0.7rem; color: #80868b; }

        .pj-card-info { display: flex; flex-direction: column; gap: 8px; }
        .pj-info-row { display: flex; align-items: center; gap: 10px; font-size: 0.78rem; color: #5f6368; }
        .pj-info-row i { font-size: 1rem; color: #163f7a; opacity: 0.7; }
        .pj-info-row code { color: #202124; }

        .pj-empty { text-align: center; padding: 60px 20px; color: #dadce0; grid-column: 1/-1; }
        .pj-empty i { font-size: 3rem; display: block; margin-bottom: 12px; }
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
                localStorage.setItem('license_view_pref', 'card');
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
                cardBtn.classList.remove('active');
                tableBtn.classList.add('active');
                localStorage.setItem('license_view_pref', 'table');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Restore View Preference
            const pref = localStorage.getItem('license_view_pref') || 'table';
            setView(pref);

            // Script Execution Utility
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

            // AJAX Modal Logic
            document.addEventListener('click', function(e) {
                const trigger = e.target.closest('.open-license-modal');
                if (trigger) {
                    e.preventDefault();
                    const url = trigger.dataset.url;
                    const content = document.getElementById('licenseModalContent');
                    const modalEl = document.getElementById('licenseModal');

                    content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#163f7a;"></i><p class="mt-2 text-muted">Loading license data...</p></div>';
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();

                    fetch(url)
                        .then(r => r.text())
                        .then(html => {
                            content.innerHTML = html;
                            execScripts(content);
                        })
                        .catch(() => {
                            content.innerHTML = '<div class="p-5 text-center text-danger"><i class="bx bx-error" style="font-size:2rem;"></i><p>Error loading license form.</p></div>';
                        });
                }
            });
        });
    </script>
@endsection
