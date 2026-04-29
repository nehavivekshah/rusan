@extends('layout')
@section('title', 'Dashboard - Rusan')

@section('content')
    @php
        $company = session('companies');
        $roles = session('roles');
        $roleArray = array_filter(explode(',', ($roles->features ?? '')));
        $hour = (int) date('G');
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

        // Lead status counts for funnel
        $leadStatusMap = [
            1 => ['label' => 'New', 'color' => '#1a73e8'],
            2 => ['label' => 'In Progress', 'color' => '#163f7a'],
            3 => ['label' => 'Follow-up', 'color' => '#fbbc04'],
            4 => ['label' => 'Negotiation', 'color' => '#ff6d00'],
            5 => ['label' => 'Converted', 'color' => '#163f7a'],
            6 => ['label' => 'Rejected', 'color' => '#ea4335'],
        ];
        $leadByStatus = [];
        foreach ($leads as $lead) {
            $s = (int) ($lead->status ?? 1);
            $leadByStatus[$s] = ($leadByStatus[$s] ?? 0) + 1;
        }
        $totalLeadsForDonut = array_sum($leadByStatus) ?: 1;
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Dashboard'])

        <div class="db-wrap">

            {{-- ═══════════════════════ USER-FRIENDLY HERO BANNER ═══════════════════════ --}}
            <div class="db-hero-premium">
                <div class="db-hero-content">
                    <div class="db-hero-welcome">
                        <div class="db-hero-text">
                            <h1 class="db-hero-title">Welcome back, {{ explode(' ', Auth::user()->name ?? 'User')[0] }}! 👋</h1>
                            <p class="db-hero-subtitle">
                                @if(Auth::user()->role == 'master')
                                    System Administrator Panel | {{ date('l, F jS') }}
                                @else
                                    You have <strong>{{ $myPendingTasks }}</strong> tasks to follow up on today. Let's make it a productive day!
                                @endif
                            </p>
                        </div>
                        <div class="db-hero-search-wrap">
                            <i class="bx bx-search db-hero-search-icon"></i>
                            <input type="text" class="db-hero-search" placeholder="Search leads, customers or deals..." onkeyup="heroSearch(this.value)">
                        </div>
                    </div>
                    
                    @if(Auth::user()->role != 'master')
                    <div class="db-hero-glance">
                        <div class="db-glance-card">
                            <span class="db-glance-val">{{ count($leads) }}</span>
                            <span class="db-glance-label">Total Leads</span>
                        </div>
                        <div class="db-glance-card">
                            <span class="db-glance-val">{{ count($clients) }}</span>
                            <span class="db-glance-label">Customers</span>
                        </div>
                        <div class="db-glance-card">
                            <span class="db-glance-val text-warning">{{ $pendingProposals }}</span>
                            <span class="db-glance-label">Proposals</span>
                        </div>
                    </div>
                    @endif
                </div>
                
                {{-- Dynamic Greeting Background Elements --}}
                <div class="db-hero-decor">
                    <div class="decor-circle-1"></div>
                    <div class="decor-circle-2"></div>
                </div>
            </div>

            @if(Auth::user()->role != 'master')
                {{-- ═══════════════════════ KPI CARDS ═══════════════════════ --}}
                <div class="db-kpi-row">

                    <a href="/invoices" class="db-kpi-card" style="--kc:#163f7a;">
                        <div class="db-kpi-icon"><i class="bx bx-receipt"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">₹{{ number_format($outstandingInvoices, 0) }}</div>
                            <div class="db-kpi-label">Outstanding Invoices</div>
                            <div class="db-kpi-meta"><i class="bx bx-time-five"></i> Pending Payment</div>
                        </div>
                        <div class="db-kpi-glow"></div>
                    </a>

                    <a href="/leads" class="db-kpi-card" style="--kc:#163f7a;">
                        <div class="db-kpi-icon"><i class="bx bx-trending-up"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">{{ count($leads) }}</div>
                            <div class="db-kpi-label">Total Leads</div>
                            <div class="db-kpi-meta"><i class="bx bx-up-arrow-alt"></i> Sales Pipeline</div>
                        </div>
                        <div class="db-kpi-glow"></div>
                    </a>

                    <a href="/proposals" class="db-kpi-card" style="--kc:#ea4335;">
                        <div class="db-kpi-icon"><i class="bx bx-file-blank"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">{{ $pendingProposals }}</div>
                            <div class="db-kpi-label">Pending Proposals</div>
                            <div class="db-kpi-meta"><i class="bx bx-error-circle"></i> Awaiting Action</div>
                        </div>
                        <div class="db-kpi-glow"></div>
                    </a>

                    <a href="/task" class="db-kpi-card" style="--kc:#fbbc04;">
                        <div class="db-kpi-icon"><i class="bx bx-task"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">{{ $myPendingTasks }}</div>
                            <div class="db-kpi-label">My Tasks</div>
                            <div class="db-kpi-meta"><i class="bx bx-list-ul"></i> Your Queue</div>
                        </div>
                        <div class="db-kpi-glow"></div>
                    </a>

                    <a href="/clients" class="db-kpi-card" style="--kc:#1a73e8;">
                        <div class="db-kpi-icon"><i class="bx bx-user-check"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">{{ count($clients) }}</div>
                            <div class="db-kpi-label">Customers</div>
                            <div class="db-kpi-meta"><i class="bx bx-group"></i> Active Base</div>
                        </div>
                        <div class="db-kpi-glow"></div>
                    </a>

                </div>

                {{-- ═══════════════════════ QUICK ACTIONS | REVENUE | LEAD PIPELINE ═══════════════════════ --}}
                <div class="db-grid-3 mb-28">

                    {{-- Quick Actions --}}
                    <div class="db-card">
                        <div class="db-card-head">
                            <span class="db-card-icon" style="color:#8b5cf6; background:rgba(139,92,246,.08);"><i
                                    class="bx bx-bolt-circle"></i></span>
                            <span class="db-card-title">Quick Actions</span>
                        </div>
                        <div class="db-qa-grid">
                            @if(in_array('leads', $roleArray) || in_array('All', $roleArray) || Auth::user()->role == '0')
                                <a href="/manage-lead" class="db-qa-item">
                                    <div class="db-qai-icon qai-blue"><i class="bx bx-user-plus"></i></div>
                                    <span class="db-qai-text">New Lead</span>
                                </a>
                            @endif
                            @if(in_array('clients', $roleArray) || in_array('All', $roleArray))
                                <a href="/manage-client" class="db-qa-item">
                                    <div class="db-qai-icon qai-indigo"><i class="bx bx-group"></i></div>
                                    <span class="db-qai-text">Add Customer</span>
                                </a>
                            @endif
                            @if(in_array('proposals', $roleArray) || in_array('All', $roleArray))
                                <a href="/manage-proposal" class="db-qa-item">
                                    <div class="db-qai-icon qai-red"><i class="bx bx-file-blank"></i></div>
                                    <span class="db-qai-text">New Proposal</span>
                                </a>
                            @endif
                            @if(in_array('invoice', $roleArray) || in_array('All', $roleArray))
                                <a href="/manage-invoice" class="db-qa-item">
                                    <div class="db-qai-icon qai-cyan"><i class="bx bx-receipt"></i></div>
                                    <span class="db-qai-text">New Invoice</span>
                                </a>
                            @endif
                            @if(in_array('tasks', $roleArray) || in_array('All', $roleArray))
                                <a href="/task" class="db-qa-item">
                                    <div class="db-qai-icon qai-yellow"><i class="bx bx-task"></i></div>
                                    <span class="db-qai-text">My Tasks</span>
                                </a>
                            @endif
                            @if(in_array('attendances', $roleArray) || in_array('All', $roleArray))
                                <a href="/attendances" class="db-qa-item">
                                    <div class="db-qai-icon qai-navy"><i class="bx bx-calendar-check"></i></div>
                                    <span class="db-qai-text">Attendance</span>
                                </a>
                            @endif
                            @if(in_array('reports', $roleArray) || in_array('All', $roleArray))
                                <a href="/reports" class="db-qa-item">
                                    <div class="db-qai-icon qai-gray"><i class="bx bx-line-chart"></i></div>
                                    <span class="db-qai-text">Reports</span>
                                </a>
                            @endif
                            <a href="/support" class="db-qa-item">
                                <div class="db-qai-icon qai-purple"><i class="bx bx-help-circle"></i></div>
                                <span class="db-qai-text">Support</span>
                            </a>
                        </div>
                    </div>

                    {{-- Revenue Line Chart --}}
                    <div class="db-card">
                        <div class="db-card-head">
                            <span class="db-card-icon" style="color:#163f7a; background:rgba(52,168,83,.08);"><i class="bx bx-line-chart"></i></span>
                            <span class="db-card-title">Revenue Growth</span>
                            <span class="db-card-badge" style="background:#e8f5e9; color:#2e7d32;">{{ date('Y') }}</span>
                            <span class="ms-auto db-card-sub">Monthly revenue</span>
                            <button class="db-zoom-btn" onclick="dbZoom('revenue')" title="Expand"><i class="bx bx-expand-alt"></i></button>
                        </div>
                        <div class="db-chart-wrap">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    {{-- Lead Pipeline Donut --}}
                    <div class="db-card">
                        <div class="db-card-head">
                            <span class="db-card-icon" style="color:#163f7a; background:rgba(22, 63, 122,.08);"><i
                                    class="bx bx-pie-chart-alt-2"></i></span>
                            <span class="db-card-title">Lead Pipeline</span>
                            <span class="db-card-badge" style="background:#e6f4f4; color:#163f7a;">{{ count($leads) }}
                                Total</span>
                        </div>
                        <div class="db-donut-wrap">
                            <div class="db-donut-canvas-area">
                                <canvas id="leadsDonutChart"></canvas>
                                <div class="db-donut-center">
                                    <div class="db-donut-num">{{ count($leads) }}</div>
                                    <div class="db-donut-sub">Leads</div>
                                </div>
                            </div>
                            <div class="db-donut-legend">
                                @foreach($leadStatusMap as $stat => $meta)
                                    @php $cnt = $leadByStatus[$stat] ?? 0; @endphp
                                    <div class="db-legend-item">
                                        <span class="db-legend-dot" style="background:{{ $meta['color'] }};"></span>
                                        <span class="db-legend-label">{{ $meta['label'] }}</span>
                                        <span class="db-legend-val">{{ $cnt }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ═══════════════════════ ACTIVITY | ALERTS | LIVE FEED ═══════════════════════ --}}
                <div class="db-grid-3 mb-28">

                    {{-- Activity Bar Chart --}}
                    <div class="db-card">
                        <div class="db-card-head">
                            <span class="db-card-icon" style="color:#1a73e8; background:rgba(26,115,232,.08);"><i class="bx bx-bar-chart-alt-2"></i></span>
                            <span class="db-card-title">Activity Monitor</span>
                            <select id="activityDateRange" class="db-select ms-auto">
                                <option value="7" {{ $selectedActivityDays == 7 ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="30" {{ $selectedActivityDays == 30 ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="90" {{ $selectedActivityDays == 90 ? 'selected' : '' }}>Last 90 Days</option>
                            </select>
                            <button class="db-zoom-btn" onclick="dbZoom('activity')" title="Expand"><i class="bx bx-expand-alt"></i></button>
                        </div>
                        <div class="db-chart-wrap">
                            <canvas id="activityFlowChart"></canvas>
                        </div>
                    </div>

                    {{-- CRM Alerts --}}
                    <div class="db-card" style="display:flex; flex-direction:column;">
                        <div class="db-card-head">
                            <span class="db-card-icon" style="color:#ea4335; background:rgba(234,67,53,.08);"><i class="bx bxs-zap"></i></span>
                            <span class="db-card-title">CRM Alerts</span>
                            @if(count($overdueLeadsList) + count($expiringProposals) > 0)
                                <span class="db-card-badge" style="background:#fdecea; color:#ea4335;">{{ count($overdueLeadsList) + count($expiringProposals) }} Alerts</span>
                            @endif
                            <button class="db-zoom-btn ms-auto" onclick="dbZoom('alerts')" title="Expand"><i class="bx bx-expand-alt"></i></button>
                        </div>
                        <div id="alertsBody" class="db-alerts-body" style="flex:1; overflow-y:auto; max-height:260px;">
                            @foreach($overdueLeadsList as $ol)
                                <a href="/manage-lead?id={{ $ol->id }}" class="db-alert-row db-alert-red">
                                    <div class="db-alert-dot"></div>
                                    <div class="db-alert-text">
                                        <strong>{{ $ol->name }}</strong>
                                        <small>Overdue · {{ \Carbon\Carbon::parse($ol->next_date)->diffForHumans() }}</small>
                                    </div>
                                    <i class="bx bx-chevron-right"></i>
                                </a>
                            @endforeach
                            @foreach($expiringProposals as $ep)
                                <a href="/manage-proposal?id={{ $ep->id }}" class="db-alert-row db-alert-yellow">
                                    <div class="db-alert-dot"></div>
                                    <div class="db-alert-text">
                                        <strong>{{ Str::limit($ep->subject, 28) }}</strong>
                                        <small>Expires · {{ \Carbon\Carbon::parse($ep->open_till)->diffForHumans() }}</small>
                                    </div>
                                    <i class="bx bx-chevron-right"></i>
                                </a>
                            @endforeach
                            @if(count($overdueLeadsList) == 0 && count($expiringProposals) == 0)
                                <div class="db-empty-state">
                                    <i class="bx bx-check-shield" style="font-size:2.2rem; color:#163f7a;"></i>
                                    <p>All clear!</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Live Activity Feed --}}
                    <div class="db-card" style="display:flex; flex-direction:column;">
                        <div class="db-card-head">
                            <span class="db-card-icon" style="color:#ea4335; background:rgba(234,67,53,.08);"><i class="bx bx-pulse"></i></span>
                            <span class="db-card-title">Live Feed</span>
                            <span class="db-live-dot"></span>
                            <span class="db-card-badge ms-1" style="background:#fdecea; color:#ea4335; font-size:0.58rem; letter-spacing:.5px;">LIVE</span>
                            <button class="db-zoom-btn ms-auto" onclick="dbZoom('feed')" title="Expand"><i class="bx bx-expand-alt"></i></button>
                        </div>
                        <div id="feedBody" class="db-feed-wrap" style="flex:1; overflow-y:auto; max-height:260px;">
                            @forelse(collect($activities ?? [])->take(10) as $act)
                                <div class="db-feed-item">
                                    <div class="db-feed-avatar">{{ strtoupper(substr($act->user_name ?? 'S', 0, 1)) }}</div>
                                    <div class="db-feed-body">
                                        <div class="db-feed-user">{{ $act->user_name ?? 'System' }}</div>
                                        <div class="db-feed-desc">{{ $act->type }} — {{ Str::limit($act->description ?? 'Action recorded', 40) }}</div>
                                        <div class="db-feed-time">{{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="db-empty-state">
                                    <i class="bx bx-news" style="font-size:2rem; color:#dadce0;"></i>
                                    <p>No recent activity</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            @else
                {{-- ════════ MASTER VIEW ════════ --}}
                <div class="db-kpi-row">
                    <a href="/companies" class="db-kpi-card">
                        <div class="db-kpi-icon qai-blue"><i class="bx bx-building"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">Companies</div>
                            <div class="db-kpi-label">Active Portals</div>
                            <div class="db-kpi-meta"><i class="bx bx-chevron-right"></i> Manage</div>
                        </div>
                    </a>
                    <a href="/subscriptions" class="db-kpi-card">
                        <div class="db-kpi-icon qai-yellow"><i class="bx bx-crown"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">Subscriptions</div>
                            <div class="db-kpi-label">Plans & Billing</div>
                            <div class="db-kpi-meta"><i class="bx bx-chevron-right"></i> Manage</div>
                        </div>
                    </a>
                    <a href="/enquiries" class="db-kpi-card">
                        <div class="db-kpi-icon qai-indigo"><i class="bx bx-mail-send"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">Enquiries</div>
                            <div class="db-kpi-label">Landing Page Leads</div>
                            <div class="db-kpi-meta"><i class="bx bx-chevron-right"></i> Manage</div>
                        </div>
                    </a>
                    <a href="/licensing" class="db-kpi-card">
                        <div class="db-kpi-icon qai-red"><i class="bx bx-file"></i></div>
                        <div class="db-kpi-body">
                            <div class="db-kpi-val">Licensing</div>
                            <div class="db-kpi-label">Product Keys</div>
                            <div class="db-kpi-meta"><i class="bx bx-chevron-right"></i> Manage</div>
                        </div>
                    </a>
                </div>
            @endif

        </div>{{-- end .db-wrap --}}

        {{-- ═══════════ ZOOM MODAL ═══════════ --}}
        <div id="dbZoomModal" class="dbzm-overlay" onclick="if(event.target===this)dbZoomClose()">
            <div class="dbzm-dialog">
                <div class="dbzm-header">
                    <span id="dbzmTitle" class="dbzm-title"></span>
                    <button class="dbzm-close" onclick="dbZoomClose()"><i class="bx bx-x"></i></button>
                </div>
                <div id="dbzmBody" class="dbzm-body"></div>
            </div>
        </div>

    </section>

    <style>
        /* ═══════════ DASHBOARD SHELL ═══════════ */
        .db-wrap {
            padding: 20px 24px 36px;
        }

        /* ── User Friendly Hero Banner ── */
        .db-hero-premium {
            background: linear-gradient(135deg, #163f7a 0%, #1e40af 100%);
            border-radius: 24px;
            padding: 32px 40px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(22, 63, 122, 0.2);
            color: #ffffff;
        }

        .db-hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .db-hero-welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            flex-wrap: wrap;
        }

        .db-hero-text { flex: 1; min-width: 300px; }

        .db-hero-title {
            font-size: 2.1rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.8px;
        }

        .db-hero-subtitle {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 6px;
            max-width: 480px;
            line-height: 1.5;
        }

        /* Hero Search Bar */
        .db-hero-search-wrap {
            position: relative;
            flex: 0 0 350px;
            min-width: 250px;
        }

        .db-hero-search {
            width: 100%;
            padding: 12px 18px 12px 48px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            color: #fff;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .db-hero-search::placeholder { color: rgba(255, 255, 255, 0.6); }

        .db-hero-search:focus {
            background: #fff;
            color: #1e293b;
            outline: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .db-hero-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.7);
            pointer-events: none;
        }

        .db-hero-search:focus + .db-hero-search-icon { color: #163f7a; }

        /* Glance Cards */
        .db-hero-glance {
            display: flex;
            gap: 16px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .db-glance-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 14px 24px;
            display: flex;
            flex-direction: column;
            min-width: 140px;
            transition: transform 0.3s ease;
        }

        .db-glance-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .db-glance-val {
            font-size: 1.35rem;
            font-weight: 800;
        }

        .db-glance-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 2px;
        }

        /* Decoration */
        .db-hero-decor { position: absolute; inset: 0; pointer-events: none; }
        .decor-circle-1 {
            position: absolute; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            top: -100px; right: -50px; border-radius: 50%;
        }
        .decor-circle-2 {
            position: absolute; width: 200px; height: 200px;
            background: rgba(255,255,255,0.03);
            bottom: -50px; left: 10%; border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.05);
        }

        @media (max-width: 992px) {
            .db-hero-welcome { flex-direction: column; align-items: flex-start; gap: 20px; }
            .db-hero-search-wrap { flex: 1; width: 100%; }
        }

        /* ── KPI Cards ── */
        .db-kpi-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .db-kpi-card {
            position: relative;
            background: #fff;
            border-radius: 20px;
            border: 1px solid #eef0f2;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .db-kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .db-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(22, 63, 122, 0.08);
            color: #163f7a;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .db-kpi-card:hover .db-kpi-icon {
            background: #163f7a;
            color: #fff;
        }

        .db-kpi-body {
            min-width: 0;
        }

        .db-kpi-val {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
            letter-spacing: -1px;
        }

        .db-kpi-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
        }

        .db-kpi-meta {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #163f7a;
            background: rgba(22, 63, 122, 0.08);
            border-radius: 6px;
            padding: 4px 10px;
            margin-top: 12px;
            width: fit-content;
        }

        /* ── Generic Card ── */
        .db-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 18px;
            overflow: hidden;
            transition: box-shadow .18s;
        }

        .db-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
        }

        .db-card-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 18px 12px;
            border-bottom: 1px solid #f1f3f4;
            flex-wrap: wrap;
        }

        .db-card-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            flex-shrink: 0;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .db-card-title {
            font-size: 0.83rem;
            font-weight: 700;
            color: #202124;
        }

        .db-card-badge {
            font-size: 0.64rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .db-card-sub {
            font-size: 0.65rem;
            color: #9aa0a6;
        }

        .db-select {
            font-size: 0.70rem;
            border: 1px solid #dadce0;
            border-radius: 20px;
            padding: 3px 10px;
            color: #3c4043;
            outline: none;
            background: #fff;
            cursor: pointer;
        }

        /* ── Chart wrappers ── */
        .db-chart-wrap {
            padding: 16px 18px;
            height: 220px;
            position: relative;
        }

        .db-chart-wrap canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* ── Donut ── */
        .db-donut-wrap {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 16px 18px;
        }

        .db-donut-canvas-area {
            position: relative;
            width: 160px;
            height: 160px;
            flex-shrink: 0;
        }

        .db-donut-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .db-donut-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: #202124;
        }

        .db-donut-sub {
            font-size: 0.65rem;
            color: #9aa0a6;
        }

        .db-donut-legend {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .db-legend-item {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .db-legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .db-legend-label {
            font-size: 0.73rem;
            color: #5f6368;
            flex: 1;
        }

        .db-legend-val {
            font-size: 0.73rem;
            font-weight: 700;
            color: #202124;
        }

        /* ── Grid Layouts ── */
        .db-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .db-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }

        .db-grid-2-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
        }

        .db-grid-1-2 {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 16px;
        }

        .db-grid-3-2 {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 16px;
        }

        .mb-28 {
            margin-bottom: 22px;
        }

        /* ── Quick Actions Grid ── */
        .db-qa-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            padding: 18px;
        }

        .db-qa-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 8px;
            border-radius: 16px;
            text-decoration: none;
            transition: all 0.2s ease;
            background: #f8fafc;
            border: 1px solid transparent;
        }

        .db-qa-item:hover {
            background: #fff;
            border-color: #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }

        .db-qai-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            transition: transform 0.2s ease;
        }

        .db-qa-item:hover .db-qai-icon {
            transform: scale(1.1);
        }

        .db-qai-text {
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
            text-align: center;
        }

        .qai-blue { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .qai-indigo { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .qai-red { background: rgba(220, 38, 38, 0.1); color: #dc2626; }
        .qai-cyan { background: rgba(8, 145, 178, 0.1); color: #0891b2; }
        .qai-yellow { background: rgba(202, 138, 4, 0.1); color: #ca8a04; }
        .qai-navy { background: #163f7a15; color: #163f7a; }
        .qai-gray { background: rgba(71, 85, 105, 0.1); color: #475569; }
        .qai-purple { background: rgba(147, 51, 234, 0.1); color: #9333ea; }

        /* ── Horizontal Live Feed ── */
        .db-feed-horiz {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1px;
            background: #f1f3f4;
            max-height: 120px;
            overflow: hidden;
        }

        .db-feed-horiz .db-feed-item {
            background: #fff;
            padding: 12px 16px;
            border-bottom: none;
        }

        .db-alerts-body {
            padding: 6px 0;
        }

        .db-alert-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            text-decoration: none;
            border-bottom: 1px solid #f8f9fa;
            transition: background .12s;
        }

        .db-alert-row:hover {
            background: #f8f9fa;
        }

        .db-alert-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .db-alert-red .db-alert-dot {
            background: #ea4335;
        }

        .db-alert-yellow .db-alert-dot {
            background: #fbbc04;
        }

        .db-alert-text {
            flex: 1;
            min-width: 0;
        }

        .db-alert-text strong {
            display: block;
            font-size: 0.78rem;
            color: #202124;
        }

        .db-alert-text small {
            font-size: 0.68rem;
            color: #9aa0a6;
        }

        .db-alert-row .bx-chevron-right {
            color: #dadce0;
            flex-shrink: 0;
        }

        /* ── Quick Actions ── */
        .db-qa-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding: 14px 18px;
        }

        .db-qa-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 14px 8px;
            border-radius: 12px;
            text-decoration: none;
            background: color-mix(in srgb, var(--qa) 7%, #fff);
            border: 1.5px solid color-mix(in srgb, var(--qa) 15%, transparent);
            color: var(--qa);
            transition: all .15s;
            font-size: 0.68rem;
            font-weight: 700;
            text-align: center;
        }

        .db-qa-btn i {
            font-size: 1.4rem;
        }

        .db-qa-btn:hover {
            background: color-mix(in srgb, var(--qa) 15%, #fff);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        /* ── Live Feed ── */
        .db-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #ea4335;
            animation: db-blink 1.2s infinite;
            flex-shrink: 0;
        }

        @keyframes db-blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .3;
            }
        }

        .db-feed-wrap {
            padding: 6px 0;
        }

        .db-feed-item {
            display: flex;
            gap: 10px;
            padding: 10px 18px;
            border-bottom: 1px solid #f8f9fa;
        }

        .db-feed-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            flex-shrink: 0;
            background: linear-gradient(135deg, #163f7a, #163f7a);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .db-feed-body {
            min-width: 0;
            flex: 1;
        }

        .db-feed-user {
            font-size: 0.75rem;
            font-weight: 700;
            color: #202124;
        }

        .db-feed-desc {
            font-size: 0.68rem;
            color: #5f6368;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .db-feed-time {
            font-size: 0.63rem;
            color: #9aa0a6;
            margin-top: 2px;
        }

        /* ── Empty State ── */
        .db-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 32px 0;
            color: #9aa0a6;
            font-size: 0.78rem;
            text-align: center;
        }

        /* ── Responsive ── */
        @media (max-width: 1200px) {
            .db-kpi-row {
                grid-template-columns: repeat(3, 1fr);
            }

            .db-grid-2-1,
            .db-grid-1-2,
            .db-grid-3-2 {
                grid-template-columns: 1fr;
            }

            .db-grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 900px) {
            .db-kpi-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .db-grid-2,
            .db-grid-3 {
                grid-template-columns: 1fr;
            }

            .db-qa-grid,
            .db-qa-grid-2col {
                grid-template-columns: repeat(3, 1fr) !important;
            }

            .db-hero {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .db-hero-right {
                align-self: stretch;
                text-align: left;
            }
        }

        @media (max-width: 600px) {
            .db-wrap {
                padding: 12px 14px 28px;
            }

            .db-kpi-row {
                grid-template-columns: 1fr 1fr;
            }

            .db-hero {
                padding: 20px;
            }

            .db-clock {
                font-size: 1.8rem;
            }
        }
    </style>

    <style>
        /* ── Zoom Button ── */
        .db-zoom-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 8px;
            border: 1px solid #e8eaed; background: #f8f9fa;
            color: #5f6368; cursor: pointer; font-size: 1.05rem;
            transition: all .15s; flex-shrink: 0;
        }
        .db-zoom-btn:hover { background: #e8f0fe; color: #1a73e8; border-color: #c5d8fb; }

        /* ── Zoom Modal Overlay ── */
        .dbzm-overlay {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,.55);
            backdrop-filter: blur(6px);
            align-items: center; justify-content: center;
            animation: dbzm-in .22s ease;
        }
        .dbzm-overlay.active { display: flex; }
        @keyframes dbzm-in { from { opacity:0; } to { opacity:1; } }

        .dbzm-dialog {
            background: #fff;
            border-radius: 20px;
            width: min(92vw, 960px);
            max-height: 88vh;
            display: flex; flex-direction: column;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,.22);
            animation: dbzm-rise .25s cubic-bezier(.34,1.44,.64,1);
        }
        @keyframes dbzm-rise { from { transform: scale(.94) translateY(16px); opacity:0; } to { transform:none; opacity:1; } }

        .dbzm-header {
            display: flex; align-items: center; gap: 10px;
            padding: 16px 22px 14px;
            border-bottom: 1px solid #f1f3f4;
            flex-shrink: 0;
        }
        .dbzm-title { font-size: 1rem; font-weight: 700; color: #202124; flex: 1; }
        .dbzm-close {
            width: 34px; height: 34px; border-radius: 50%;
            border: none; background: #f1f3f4;
            font-size: 1.3rem; color: #5f6368; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .12s;
        }
        .dbzm-close:hover { background: #fdecea; color: #ea4335; }

        .dbzm-body {
            flex: 1; overflow-y: auto; padding: 20px 22px;
        }
        .dbzm-body .db-chart-wrap { height: 400px !important; }
        .dbzm-body .db-alerts-body { max-height: none !important; }
        .dbzm-body .db-feed-wrap  { max-height: none !important; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ── Live Clock ──
        (function tickClock() {
            const el = document.getElementById('dbClock');
            if (!el) return;
            const now = new Date();
            el.textContent = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });
            setTimeout(tickClock, 1000);
        })();

        // ── KPI card entrance animation ──
        document.querySelectorAll('.db-kpi-card').forEach((card, i) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(12px)';
            setTimeout(() => {
                card.style.transition = 'opacity .35s ease, transform .35s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 80 + i * 60);
        });

        // Chart configs storage (declared before the role check so zoom modal can always access it)
        const _dbChartConfigs = {};

        @if(Auth::user()->role != 'master')
            // ── Revenue Line Chart ──
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const monthlyRevenue = {!! json_encode($monthlyRevenue) !!};
            const revGrad = revenueCtx.createLinearGradient(0, 0, 0, 200);
            revGrad.addColorStop(0, 'rgba(22, 63, 122, 0.2)');
            revGrad.addColorStop(1, 'rgba(22, 63, 122, 0)');
            const _revCfg = {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: monthlyRevenue,
                        borderColor: '#163f7a',
                        backgroundColor: revGrad,
                        fill: true, tension: 0.45,
                        pointBackgroundColor: '#163f7a', pointRadius: 4, pointHoverRadius: 7, borderWidth: 3
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => '₹ ' + ctx.raw.toLocaleString('en-IN') } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            beginAtZero: true, grid: { color: '#f1f3f4' },
                            ticks: { callback: v => v >= 1000 ? '₹' + (v / 1000).toFixed(0) + 'K' : '₹' + v, font: { size: 11 } }
                        }
                    }
                }
            };
            new Chart(revenueCtx, _revCfg);
            _dbChartConfigs.revenue = _revCfg;

            // ── Lead Pipeline Donut Chart ──
            const donutCtx = document.getElementById('leadsDonutChart').getContext('2d');
            const donutData = {!! json_encode(array_values(array_map(fn($s) => $leadByStatus[$s] ?? 0, array_keys($leadStatusMap)))) !!};
            const donutColors = ['#1a73e8', '#163f7a', '#fbbc04', '#ff6d00', '#163f7a', '#ea4335'];
            const donutLabels = {!! json_encode(array_values(array_map(fn($m) => $m['label'], $leadStatusMap))) !!};
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{ data: donutData, backgroundColor: donutColors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw } }
                    }
                }
            });

            // ── Activity Bar Chart ──
            const actCtx = document.getElementById('activityFlowChart').getContext('2d');
            const actLabels = {!! json_encode($activityChartLabels) !!};
            const actDatasets = {!! json_encode($activityChartDatasets) !!};
            const palette = ['rgba(22, 63, 122, 0.7)', 'rgba(37, 99, 235, 0.7)', 'rgba(79, 70, 229, 0.7)',
                'rgba(14, 165, 233, 0.7)', 'rgba(234, 67, 53, 0.7)', 'rgba(11, 31, 61, 0.7)'];
            const _actCfg = {
                type: 'bar',
                data: {
                    labels: actLabels,
                    datasets: actDatasets.map((ds, i) => ({
                        label: ds.label, data: ds.data,
                        backgroundColor: palette[i % palette.length],
                        borderRadius: 4
                    }))
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } },
                        tooltip: {
                            mode: 'index', intersect: false,
                            callbacks: { footer: items => 'Total: ' + items.reduce((s, i) => s + i.raw, 0) }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: {
                            stacked: true, beginAtZero: true, grid: { color: '#f1f3f4' },
                            ticks: { stepSize: 1, precision: 0, font: { size: 10 } }
                        }
                    }
                }
            };
            new Chart(actCtx, _actCfg);
            _dbChartConfigs.activity = _actCfg;

            // \u2500\u2500 Activity date range selector \u2500\u2500
            document.getElementById('activityDateRange')?.addEventListener('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('activity_days', this.value);
                window.location.href = url.toString();
            });
        @endif

        // \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 ZOOM MODAL \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550
        // NOTE: _dbChartConfigs already declared above; zoom works for all roles
        let _dbZoomChart = null;

        function dbZoom(type) {
            const modal = document.getElementById('dbZoomModal');
            const body  = document.getElementById('dbzmBody');
            const title = document.getElementById('dbzmTitle');
            body.innerHTML = '';
            if (_dbZoomChart) { _dbZoomChart.destroy(); _dbZoomChart = null; }

            if (type === 'revenue') {
                title.textContent = '📈 Revenue Growth — ' + new Date().getFullYear();
                body.innerHTML = '<div class="db-chart-wrap"><canvas id="zmRevenueChart"></canvas></div>';
                modal.classList.add('active');
                requestAnimationFrame(() => {
                    const cfg = (typeof _dbChartConfigs !== 'undefined') ? _dbChartConfigs.revenue : null;
                    if (cfg) _dbZoomChart = new Chart(document.getElementById('zmRevenueChart'), JSON.parse(JSON.stringify(cfg)));
                });
            } else if (type === 'activity') {
                title.textContent = '📊 Activity Monitor';
                body.innerHTML = '<div class="db-chart-wrap"><canvas id="zmActivityChart"></canvas></div>';
                modal.classList.add('active');
                requestAnimationFrame(() => {
                    const cfg = (typeof _dbChartConfigs !== 'undefined') ? _dbChartConfigs.activity : null;
                    if (cfg) _dbZoomChart = new Chart(document.getElementById('zmActivityChart'), JSON.parse(JSON.stringify(cfg)));
                });
            } else if (type === 'alerts') {
                title.textContent = '⚡ CRM Alerts';
                const src = document.getElementById('alertsBody');
                if (src) body.appendChild(src.cloneNode(true));
                modal.classList.add('active');
            } else if (type === 'feed') {
                title.textContent = '🔴 Live Activity Feed';
                const src = document.getElementById('feedBody');
                if (src) body.appendChild(src.cloneNode(true));
                modal.classList.add('active');
            }
        }

        function dbZoomClose() {
            document.getElementById('dbZoomModal').classList.remove('active');
            if (_dbZoomChart) { _dbZoomChart.destroy(); _dbZoomChart = null; }
        }

        // Close on Escape key
        document.addEventListener('keydown', e => { if (e.key === 'Escape') dbZoomClose(); });

        // ── Hero Quick Search ──
        window.heroSearch = function(val) {
            const query = val.toLowerCase().trim();
            // Implement search logic here
        };
    </script>

    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js"></script>

@endsection
