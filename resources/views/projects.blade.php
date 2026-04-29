@extends('layout')
@section('title', 'Projects - Rusan')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        // Aggregate stats
        $totalProjects = $projects->count();
        $totalValue = $projects->sum('amount');
        $totalRecovered = $projects->sum(fn($p) => $p->total_paid ?? 0);
        $totalPending = $totalValue - $totalRecovered;

        // Status type counts
        $typeGroups = $projects->groupBy('type');
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Projects'])

        <div class="dash-container">

            {{-- ── Stat Cards Row ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                        <i class="bx bx-layer"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $totalProjects }}</div>
                        <div class="pj-stat-label">Total Projects</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-rupee"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">₹{{ number_format($totalValue, 0) }}</div>
                        <div class="pj-stat-label">Total Contract Value</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#34a853;">₹{{ number_format($totalRecovered, 0) }}</div>
                        <div class="pj-stat-label">Total Recovered</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                        <i class="bx bx-time-five"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#ea4335;">₹{{ number_format($totalPending, 0) }}</div>
                        <div class="pj-stat-label">Pending Balance</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/projects" method="GET" id="projectFilterForm" class="d-flex align-items-center gap-2">
                        <select name="status" id="projectStatusFilter" class="form-select" onchange="this.form.submit()"
                            style="width: auto; min-width: 140px;">
                            <option value="">All Status</option>
                            <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($status ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </form>

                    <span class="lb-page-count">
                        {{ $totalProjects }} {{ $totalProjects == 1 ? 'Project' : 'Projects' }}
                        @if(!empty($search)) found @endif
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
                    @if(in_array('projects_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-project" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Project</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- ════════════════════════════════
            CARD VIEW
            ════════════════════════════════ --}}
            <div id="cardView" class="pj-card-grid mb-4" style="display:none;">
                @forelse($projects as $project)
                    @php
                        $paid = $project->total_paid ?? 0;
                        $amount = $project->amount ?? 0;
                        $pct = $amount > 0 ? min(100, round(($paid / $amount) * 100)) : 0;
                        $pctColor = $pct >= 80 ? '#34a853' : ($pct >= 40 ? '#fbbc04' : '#ea4335');
                        $remaining = $amount - $paid;
                    @endphp
                    <div class="pj-card project-row-click" data-url="/project/view/{{ $project->id }}">
                        {{-- Top accent --}}
                        <div class="pj-card-accent" style="background: linear-gradient(90deg, #006666, #009688);"></div>

                        {{-- Header --}}
                        <div class="pj-card-header">
                            <div class="pj-card-avatar">{{ strtoupper(substr($project->name, 0, 1)) }}</div>
                            <div class="pj-card-meta">
                                <div class="pj-card-name">{{ $project->name }}</div>
                                <div class="pj-card-id">
                                    @if($project->project_id_custom)
                                        <span class="badge bg-light text-dark border-0 fw-bold"
                                            style="font-size:0.6rem;">{{ $project->project_id_custom }}</span>
                                    @else
                                        #PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}
                                    @endif
                                    @if($project->batchNo)
                                        <span class="badge bg-info text-white border-0 ms-1"
                                            style="font-size:0.55rem; background: #006666 !important;">{{ $project->batchNo }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="pj-card-actions">
                                @php
                                    $waRaw = !empty($project->client_whatsapp) && $project->client_whatsapp !== '-' ? $project->client_whatsapp : $project->client_mob;
                                    $waNum = preg_replace('/[^0-9]/', '', $waRaw ?? '');
                                    if (strlen($waNum) == 10) { $waNum = '91' . $waNum; }
                                @endphp
                                @if(!empty($waNum))
                                    <a href="https://wa.me/{{ $waNum }}" target="_blank" class="btn kb-action-btn" title="WhatsApp" 
                                       style="background:rgba(37,211,102,0.08);color:#25D366;"
                                       onclick="event.stopPropagation();">
                                        <i class="bx bxl-whatsapp"></i>
                                    </a>
                                @endif
                                @if($project->deployment_url)
                                    <a href="{{ $project->deployment_url }}" target="_blank" class="btn kb-action-btn"
                                        title="Visit Site" style="background:rgba(26,115,232,0.08);color:#1a73e8;"
                                        onclick="event.stopPropagation();">
                                        <i class="bx bx-link-external"></i>
                                    </a>
                                @endif
                                <a href="/manage-project?id={{ $project->id }}" class="btn kb-action-btn" title="Edit"
                                    style="background:rgba(0,102,102,0.08);color:#006666;"
                                    onclick="event.stopPropagation();">
                                    <i class="bx bx-pencil"></i>
                                </a>
                                @if(in_array('projects_delete', $roleArray) || in_array('All', $roleArray))
                                    <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete"
                                        id="{{ $project->id }}" data-page="projectDelete" title="Delete" style="background:rgba(234,67,53,0.08);color:#ea4335;">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Client --}}
                        <div class="pj-card-client">
                            <i class="bx bx-user-circle"></i>
                            <span>{{ $project->client_name ?? '—' }}</span>
                            @if($project->client_company)
                                <span class="pj-dot">·</span>
                                <span class="text-muted">{{ Str::limit($project->client_company, 20) }}</span>
                            @endif
                        </div>

                        {{-- Sales / Manager --}}
                        @if($project->salesperson_name)
                            <div class="pj-card-client mt-1" title="Closed by">
                                <i class="bx bx-badge-check" style="color:#1a73e8;"></i>
                                <span class="text-muted" style="font-size:0.7rem;">Sales: {{ $project->salesperson_name }}</span>
                            </div>
                        @endif

                        {{-- Type badge --}}
                        @if($project->type)
                            <div class="mb-2">
                                <span class="pj-type-pill">{{ $project->type }}</span>
                            </div>
                        @endif

                        {{-- Financial Summary --}}
                        <div class="pj-card-finance">
                            <div class="pj-finance-row">
                                <span>Contract Value</span>
                                <span class="pj-finance-val">₹{{ number_format($amount, 0) }}</span>
                            </div>
                            <div class="pj-finance-row">
                                <span>Recovered</span>
                                <span class="pj-finance-val" style="color:#34a853;">₹{{ number_format($paid, 0) }}</span>
                            </div>
                            <div class="pj-finance-row">
                                <span>Pending</span>
                                <span class="pj-finance-val" style="color:{{ $remaining > 0 ? '#ea4335' : '#34a853' }};">
                                    ₹{{ number_format($remaining, 0) }}
                                </span>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="pj-progress-wrap">
                            <div class="pj-progress-bar">
                                <div class="pj-progress-fill" style="width:{{ $pct }}%; background:{{ $pctColor }};"></div>
                            </div>
                            <span class="pj-progress-pct" style="color:{{ $pctColor }};">{{ $pct }}%</span>
                        </div>
                        <div class="pj-progress-label">Recovery Progress</div>
                    </div>
                @empty
                    <div class="pj-empty" style="grid-column:1/-1;">
                        <i class="bx bx-layer"></i>
                        <p>No projects found. Create your first project!</p>
                        @if(in_array('projects_add', $roleArray) || in_array('All', $roleArray))
                            <a href="/manage-project" class="lb-btn lb-btn-primary mt-2"><i class="bx bx-plus"></i> Add Project</a>
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
                                <th>Project ID</th>
                                <th>Batch No.</th>
                                <th>Project Details</th>
                                <th class="m-none">Service Category</th>
                                <th class="m-none">Project Timeline</th>
                                <th>Recovery Status</th>
                                <th class="m-none">Closed By</th>
                                <th class="text-center">Status</th>
                                <th class="m-none">Tags</th>
                                <th class="text-center position-sticky end-0 mw60" data-orderable="false"
                                    style="z-index:1;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                @php
                                    $paid = $project->total_paid ?? 0;
                                    $amt = $project->amount ?? 0;
                                    $pct = $amt > 0 ? min(100, round(($paid / $amt) * 100)) : 0;
                                    $pctColor = $pct >= 80 ? '#34a853' : ($pct >= 40 ? '#fbbc04' : '#ea4335');
                                    $isDone = ($project->status == 0);
                                @endphp
                                <tr class="pointer-cursor selectrow project-row-click" data-url="/project/view/{{ $project->id }}">
                                    <td class="fw-bold text-muted" style="font-size:0.75rem;">
                                        @if($project->project_id_custom)
                                            <span class="text-primary">{{ $project->project_id_custom }}</span>
                                        @else
                                            #PROJ-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}
                                        @endif
                                    </td>
                                    <td class="fw-600" style="font-size:0.8rem; color:#006666;">
                                        {{ $project->batchNo ?? '—' }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm"
                                                style="background:linear-gradient(135deg,#006666,#009688);color:#fff;">
                                                {{ strtoupper(substr($project->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-600 text-truncate" style="max-width:180px;">{{ $project->name }}
                                                </div>
                                                <div class="small text-muted d-flex align-items-center gap-1">
                                                    {{ $project->client_name ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <span class="pj-type-pill">{{ $project->type ?? 'General' }}</span>
                                    </td>
                                    <td class="m-none">
                                        <div class="small">
                                            <span class="text-muted">S:</span>
                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M') : '—' }}
                                        </div>
                                        <div
                                            class="small fw-600 {{ \Carbon\Carbon::parse($project->deadline)->isPast() && !$isDone ? 'text-danger' : 'text-muted' }}">
                                            <span class="text-muted">D:</span>
                                            {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d M, Y') : '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="color:#006666;">₹{{ number_format($project->amount, 0) }}
                                        </div>
                                        <div class="d-flex align-items-center gap-1 mt-1" style="font-size:0.65rem;">
                                            <div
                                                style="width:40px;height:4px;background:#f0f0f0;border-radius:2px;overflow:hidden;">
                                                <div style="width:{{ $pct }}%;height:100%;background:{{ $pctColor }};"></div>
                                            </div>
                                            <span style="color:{{ $pctColor }};">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <div class="small text-muted">
                                            @if($project->salesperson_name)
                                                <i class="bx bx-user-check"></i> {{ $project->salesperson_name }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($project->status == 1)
                                            <span class="pv-badge pv-badge-success">Active</span>
                                        @else
                                            <span class="pv-badge pv-badge-info">Closed</span>
                                        @endif
                                    </td>
                                    <td class="m-none">
                                        @if($project->tags)
                                            @foreach(explode(',', $project->tags) as $tag)
                                                <span class="badge bg-light text-dark border fw-normal"
                                                    style="font-size:0.65rem;">{{ trim($tag) }}</span>
                                            @endforeach
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @php
                                                $waRaw = !empty($project->client_whatsapp) && $project->client_whatsapp !== '-' ? $project->client_whatsapp : $project->client_mob;
                                                $waNum = preg_replace('/[^0-9]/', '', $waRaw ?? '');
                                                if (strlen($waNum) == 10) { $waNum = '91' . $waNum; }
                                            @endphp
                                            @if(!empty($waNum))
                                                <a href="https://wa.me/{{ $waNum }}" target="_blank" class="btn kb-action-btn" title="WhatsApp" 
                                                   style="color:#25D366; background:rgba(37,211,102,0.08);"
                                                   onclick="event.stopPropagation();">
                                                    <i class="bx bxl-whatsapp"></i>
                                                </a>
                                            @endif
                                            <a href="/manage-project?id={{ $project->id }}"
                                                class="btn kb-action-btn kb-action-edit" title="Edit"
                                                onclick="event.stopPropagation();">
                                                <i class="bx bx-pencil"></i>
                                            </a>
                                            @if(in_array('projects_delete', $roleArray) || in_array('All', $roleArray))
                                                <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete"
                                                    id="{{ $project->id }}" data-page="projectDelete" title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="kb-empty-col" style="padding:40px 0;">
                                            <i class="bx bx-layer" style="font-size:2.5rem;"></i>
                                            <span>No projects found.</span>
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

    <style>
        /* ── Project Stat Cards ── */
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

        /* ── Search Clear Button ── */
        .pj-search-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #dadce0;
            color: #5f6368;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.15s;
            z-index: 2;
        }

        .pj-search-clear:hover {
            background: #ea4335;
            color: #fff;
        }

        /* ── Active Filter Badge ── */
        .pj-active-filter {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(0, 102, 102, 0.08);
            border: 1px solid rgba(0, 102, 102, 0.2);
            color: #006666;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .pj-active-filter i {
            font-size: 0.85rem;
        }

        .pj-filter-clear {
            color: #ea4335;
            font-weight: 800;
            font-size: 1rem;
            text-decoration: none;
            line-height: 1;
            margin-left: 2px;
        }

        .pj-filter-clear:hover {
            color: #c62828;
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

        .pj-stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .pj-stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .pj-stat-num {
            font-size: 1.2rem;
            font-weight: 700;
            color: #202124;
            line-height: 1.2;
        }

        .pj-stat-label {
            font-size: 0.72rem;
            color: #80868b;
            font-weight: 500;
            margin-top: 2px;
        }

        /* ── View Toggle ── */
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
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }

        .pj-view-btn.active {
            background: #fff;
            color: #006666;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
        }

        /* ── Card Grid ── */
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
            transition: box-shadow 0.2s, transform 0.18s;
            position: relative;
            padding: 0 16px 16px;
        }

        .pj-card:hover {
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.10);
            transform: translateY(-2px);
        }

        .pj-card-accent {
            height: 4px;
            margin: 0 -16px 14px;
        }

        .pj-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .pj-card-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #006666, #009688);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pj-card-meta {
            flex: 1;
            min-width: 0;
        }

        .pj-card-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #202124;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pj-card-id {
            font-size: 0.68rem;
            color: #80868b;
        }

        .pj-card-actions {
            display: flex;
            gap: 5px;
            flex-shrink: 0;
        }

        .pj-card-client {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.78rem;
            color: #5f6368;
            margin-bottom: 8px;
        }

        .pj-card-client i {
            color: #006666;
            font-size: 0.9rem;
        }

        .pj-dot {
            color: #dadce0;
        }

        .pj-type-pill {
            display: inline-block;
            background: rgba(0, 102, 102, 0.08);
            color: #006666;
            font-size: 0.68rem;
            font-weight: 600;
            border-radius: 20px;
            padding: 2px 10px;
        }

        .pj-card-finance {
            background: #f8fafb;
            border-radius: 10px;
            padding: 10px 12px;
            margin: 10px 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .pj-finance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
        }

        .pj-finance-row span:first-child {
            color: #80868b;
        }

        .pj-finance-val {
            font-weight: 700;
            font-size: 0.78rem;
            color: #202124;
        }

        .pj-progress-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
        }

        .pj-progress-bar {
            flex: 1;
            height: 7px;
            background: #f0f0f0;
            border-radius: 99px;
            overflow: hidden;
        }

        .pj-progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.4s ease;
        }

        .pj-progress-pct {
            font-size: 0.70rem;
            font-weight: 700;
            min-width: 28px;
            text-align: right;
        }

        .pj-progress-label {
            font-size: 0.65rem;
            color: #9aa0a6;
            margin-top: 2px;
        }

        .pj-empty {
            text-align: center;
            padding: 60px 20px;
            color: #9aa0a6;
        }

        .pj-empty i {
            font-size: 3rem;
            display: block;
            margin-bottom: 12px;
            color: #dadce0;
        }

        .pj-empty p {
            font-size: 0.85rem;
            margin: 0;
        }

        /* ── DataTable Overrides ── */
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e8eaed !important;
            border-radius: 8px !important;
            padding: 4px 8px !important;
            font-size: 0.85rem !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e8eaed !important;
            border-radius: 20px !important;
            padding: 6px 16px !important;
            font-size: 0.85rem !important;
            margin-left: 10px !important;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 0.8rem !important;
            color: #5f6368 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: 0.85rem !important;
        }
    </style>

@endsection
