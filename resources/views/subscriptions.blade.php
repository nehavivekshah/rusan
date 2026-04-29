@extends('layout')
@section('title', 'Subscriptions - Rusan')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Subscription Management'])

        <div class="dash-container">
            
            {{-- ── Plan Analytics ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(22, 63, 122,0.1);color:#163f7a;">
                        <i class="bx bx-building"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['total'] }}</div>
                        <div class="pj-stat-label">Subscribed Companies</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(212, 175, 55, 0.1);color:#d4af37;">
                        <i class="bx bx-crown"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $plans->count() }}</div>
                        <div class="pj-stat-label">Active Tiers</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52, 168, 83, 0.1);color:#163f7a;">
                        <i class="bx bx-trending-up"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num text-success">₹{{ number_format($plans->avg('price'), 2) }}</div>
                        <div class="pj-stat-label">Avg. Price Point</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(22, 63, 122, 0.1);color:#163f7a;">
                        <i class="bx bx-shield-quarter"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['pro'] + $stats['premium'] }}</div>
                        <div class="pj-stat-label">Premium Users</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        {{ $plans->count() }} {{ $plans->count() == 1 ? 'Subscription Tier' : 'Subscription Tiers' }}
                    </span>
                    <div class="ms-3 d-none d-md-block">
                        <div class="cf-input-box" style="height: 34px; width: 220px; border-radius: 20px;">
                            <span class="cf-icon" style="width: 32px;"><i class="bx bx-search" style="font-size: 0.9rem;"></i></span>
                            <input type="text" id="planSearch" placeholder="Search tiers..." style="font-size: 0.75rem;">
                        </div>
                    </div>
                </div>
                <div class="leads-toolbar-right gap-2">
                    {{-- View Toggle --}}
                    <div class="pj-view-toggle">
                        <button class="pj-view-btn active" id="cardViewBtn" title="Grid View" onclick="setView('card')">
                            <i class="bx bx-grid-alt"></i>
                        </button>
                        <button class="pj-view-btn" id="tableViewBtn" title="List View" onclick="setView('table')">
                            <i class="bx bx-list-ul"></i>
                        </button>
                    </div>
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button type="button" class="lb-btn lb-btn-primary open-plan-modal" data-url="/manage-plan?ajax=1">
                        <i class="bx bx-plus"></i>
                        <span class="d-none d-sm-inline">Add Tier</span>
                    </button>
                </div>
            </div>

            {{-- ════════════════════════════════
            CARD VIEW (GRID)
            ════════════════════════════════ --}}
            <div id="cardView" class="pj-card-grid mb-4">
                @foreach($plans as $plan)
                    <div class="pj-card plan-item" data-name="{{ strtolower($plan->name) }}">
                        <div class="pj-card-accent" style="background: linear-gradient(90deg, #d4af37, #b8860b);"></div>
                        
                        <div class="pj-card-header">
                            <div class="pj-card-avatar" style="background: rgba(22, 63, 122,0.08); color: #163f7a;">
                                <i class="bx {{ strtolower($plan->name) == 'pro' ? 'bx-crown' : 'bx-badge' }}"></i>
                            </div>
                            <div class="pj-card-meta">
                                <div class="pj-card-name">{{ $plan->name }}</div>
                                <div class="pj-card-id font-bold text-indigo" style="color:#163f7a;">₹{{ number_format($plan->price, 2) }}/mo</div>
                            </div>
                            <div class="pj-card-actions">
                                <div class="dropdown">
                                    <button class="btn kb-action-btn" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item py-2 open-plan-modal" href="javascript:void(0)" data-url="/manage-plan?item={{ $plan->id }}&id={{ $plan->id }}"><i class="bx bx-edit-alt me-2 text-warning"></i> Edit</a></li>
                                        <li><a class="dropdown-item py-2 text-danger" href="/delete-plan?id={{ $plan->id }}" onclick="return confirm('Archive this tier?')"><i class="bx bx-trash me-2"></i> Archive</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="pj-card-info mt-2">
                             <p class="small text-muted mb-3" style="font-size: 0.78rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 38px;">
                                {{ $plan->description }}
                             </p>
                             
                             @if($plan->features)
                                <div class="plan-features-preview border-top pt-2">
                                    @foreach(array_slice($plan->features, 0, 3) as $feat)
                                        <div class="d-flex align-items-center gap-2 mb-1" style="font-size: 0.72rem; color: #5f6368;">
                                            <i class="bx bx-check text-success"></i> {{ $feat }}
                                        </div>
                                    @endforeach
                                </div>
                             @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ════════════════════════════════
            TABLE VIEW (LIST)
            ════════════════════════════════ --}}
            <div id="tableView" class="dash-card mb-4" style="display:none; background: #fff; border: 1.5px solid #e8eaed; border-radius: 20px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tier Details</th>
                                <th>Price</th>
                                <th>Included Features</th>
                                <th class="text-center">Status</th>
                                <th class="text-center position-sticky end-0 mw60" style="z-index:1;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $k=>$plan)
                                <tr class="plan-item" data-name="{{ strtolower($plan->name) }}">
                                    <td class="fw-bold text-muted">{{ $k+1 }}</td>
                                    <td>
                                        <div class="fw-600 text-dark">{{ $plan->name }}</div>
                                        <div class="small text-muted truncate-1" style="max-width:300px;">{{ $plan->description }}</div>
                                    </td>
                                    <td>
                                        <div class="badge bg-soft-primary px-3 py-1 rounded-pill text-indigo" style="font-size:0.75rem; background:rgba(22, 63, 122,0.08); color:#163f7a;">
                                            ₹{{ number_format($plan->price, 2) }}/mo
                                        </div>
                                    </td>
                                    <td>
                                        @if($plan->features)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach(array_slice($plan->features, 0, 2) as $feat)
                                                    <span class="small border rounded px-2 py-0" style="font-size: 0.65rem; color: #5f6368;"><i class="bx bx-check text-success me-1"></i>{{ $feat }}</span>
                                                @endforeach
                                                @if(count($plan->features) > 2)
                                                    <span class="text-indigo small" style="font-size: 0.65rem;">+{{ count($plan->features)-2 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="pv-badge pv-badge-success">Active</span>
                                    </td>
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn kb-action-btn kb-action-edit open-plan-modal" 
                                                data-url="/manage-plan?id={{ $plan->id }}&ajax=1" title="Edit">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            <a href="/delete-plan?id={{ $plan->id }}" class="btn kb-action-btn kb-action-del" title="Delete" onclick="return confirm('Delete tier?')">
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
        </div>
    </section>

    {{-- Modals Shell --}}
    <div class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius:16px; border:none;" id="companyModalContent"></div>
        </div>
    </div>

    <div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="border-radius:20px; border:none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);" id="planModalContent"></div>
        </div>
    </div>

    <style>
        .dash-container { padding: 0 20px; }
        .bg-teal-premium { background-color: #163f7a !important; color: #fff !important; }
        
        /* ── Project/Company Standard Stats ── */
        .pj-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        @media (max-width: 992px) { .pj-stat-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .pj-stat-row { grid-template-columns: 1fr; } }

        .pj-stat-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 14px;
            padding: 16px 18px; display: flex; align-items: center; gap: 14px;
            transition: box-shadow 0.2s;
        }
        .pj-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .pj-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .pj-stat-num { font-size: 1.15rem; font-weight: 700; color: #202124; }
        .pj-stat-label { font-size: 0.72rem; color: #80868b; font-weight: 500; }

        /* ── View Toggle ── */
        .pj-view-toggle { display: flex; gap: 3px; background: #f1f3f4; border-radius: 20px; padding: 3px; }
        .pj-view-btn {
            width: 30px; height: 30px; border: none; background: transparent;
            border-radius: 17px; cursor: pointer; color: #80868b;
            font-size: 1rem; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .pj-view-btn.active { background: #fff; color: #163f7a; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }

        /* ── Card Grid ── */
        .pj-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
        .pj-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 16px;
            overflow: hidden; cursor: default; transition: box-shadow 0.2s, transform 0.18s;
            position: relative; padding: 0 16px 16px;
        }
        .pj-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: translateY(-2px); }
        .pj-card-accent { height: 4px; margin: 0 -16px 14px; }
        .pj-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .pj-card-avatar { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .pj-card-meta { flex: 1; min-width: 0; }
        .pj-card-name { font-size: 0.9rem; font-weight: 700; color: #202124; }
        .pj-card-id { font-size: 0.68rem; color: #80868b; }
        .pv-badge { padding: 3px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
        .pv-badge-success { background: rgba(52,168,83,0.1); color: #163f7a; }

        .truncate-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
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
                localStorage.setItem('subscription_view_pref', 'card');
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
                cardBtn.classList.remove('active');
                tableBtn.classList.add('active');
                localStorage.setItem('subscription_view_pref', 'table');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Restore View Preference
            const pref = localStorage.getItem('subscription_view_pref');
            if (pref === 'table') {
                setView('table');
            }

            // Search Functionality
            const searchInput = document.getElementById('planSearch');
            if(searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const term = e.target.value.toLowerCase();
                    document.querySelectorAll('.plan-item').forEach(item => {
                        const name = item.dataset.name || '';
                        item.style.display = name.includes(term) ? '' : 'none';
                    });
                });
            }

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

            // Global Modal Handler
            function handleAjaxModal(triggerClass, modalId, contentId, loaderText) {
                document.addEventListener('click', function(e) {
                    const trigger = e.target.closest(triggerClass);
                    if (trigger) {
                        e.preventDefault();
                        const url = trigger.dataset.url;
                        const content = document.getElementById(contentId);
                        const modalEl = document.getElementById(modalId);

                        content.innerHTML = `<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#163f7a;"></i><p class="mt-2 text-muted small">${loaderText}</p></div>`;
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();

                        fetch(url)
                            .then(r => r.text())
                            .then(html => {
                                content.innerHTML = html;
                                execScripts(content);
                            })
                            .catch(() => {
                                content.innerHTML = '<div class="p-5 text-center text-danger small"><i class="bx bx-error-circle"></i> Error loading resource.</div>';
                            });
                    }
                });
            }

            handleAjaxModal('.open-company-modal', 'companyModal', 'companyModalContent', 'Syncing company parameters...');
            handleAjaxModal('.open-plan-modal', 'planModal', 'planModalContent', 'Opening tier configuration...');
        });
    </script>
@endsection
