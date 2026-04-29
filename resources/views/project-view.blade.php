@extends('layout')
@section('title', 'Project Details - Rusan')

@section('content')
    @php
        $totalPaid = $recoveries->sum('paid');
        $totalAmount = $project->amount ?? 0;
        $pendingAmount = max(0, $totalAmount - $totalPaid);
        $recoveryPct = $totalAmount > 0 ? min(100, round(($totalPaid / $totalAmount) * 100)) : 0;
        $pctColor = $recoveryPct >= 80 ? '#34a853' : ($recoveryPct >= 40 ? '#fbbc04' : '#ea4335');

        $paidInvoices = $invoices->where('status', 'paid')->count();
        $unpaidInvoices = $invoices->where('status', '!=', 'paid')->count();
        $pendingTasks = $tasks->where('status', '!=', 'Completed')->count();

        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Project Details'])

        <div class="dash-container">

            {{-- ══ Hero Banner ══ --}}
            <div class="pv-hero mb-4">
                <div class="pv-hero-body">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="pv-hero-avatar">{{ strtoupper(substr($project->name, 0, 1)) }}</div>
                        <div class="pv-hero-info">
                            <h1 class="pv-hero-title">{{ $project->name }}</h1>
                            <div class="pv-hero-meta">
                                <span><i class="bx bx-building"></i> {{ $project->client_name }}</span>
                                @if($project->client_company)
                                    <span class="pv-sep">·</span>
                                    <span>{{ $project->client_company }}</span>
                                @endif
                                <span class="pv-sep">·</span>
                                <span><i class="bx bx-calendar"></i>
                                    {{ \Carbon\Carbon::parse($project->created_at)->format('d M, Y') }}</span>
                                <span class="pv-sep">·</span>
                                <span class="pv-id">
                                    @if($project->project_id_custom)
                                        <span
                                            class="badge bg-white text-primary border fw-bold">{{ $project->project_id_custom }}</span>
                                    @else
                                        #PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pv-hero-actions">
                        @if($project->client_mob)
                            <a href="tel:{{ $project->client_mob }}" class="pv-action-btn" title="Call">
                                <i class="bx bx-phone"></i>
                            </a>
                        @endif
                        @if($project->client_whatsapp || $project->client_mob)
                            @php 
                                $waRaw = $project->client_whatsapp ?? $project->client_mob; 
                                $waNum = preg_replace('/[^0-9]/', '', $waRaw ?? '');
                                if (strlen($waNum) == 10) { $waNum = '91' . $waNum; }
                            @endphp
                            @if(!empty($waNum))
                                <a href="https://wa.me/{{ $waNum }}?text=Regarding Project: {{ urlencode($project->name) }}"
                                    target="_blank" class="pv-action-btn pv-wa" title="WhatsApp">
                                    <i class="bx bxl-whatsapp"></i>
                                </a>
                            @endif
                        @endif
                        @if($project->client_email)
                            <a href="mailto:{{ $project->client_email }}" class="pv-action-btn" title="Email">
                                <i class="bx bx-envelope"></i>
                            </a>
                        @endif
                        @if($project->deployment_url)
                            <a href="{{ $project->deployment_url }}" target="_blank" class="pv-action-btn" title="Live Site">
                                <i class="bx bx-globe"></i>
                            </a>
                        @endif
                        @if(in_array('projects_edit', $roleArray) || in_array('All', $roleArray))
                            <a href="/manage-project?id={{ $project->id }}" class="pv-edit-btn">
                                <i class="bx bx-edit"></i> Edit Project
                            </a>
                        @endif
                        <a href="{{ url('/projects') }}" class="pv-back-btn">
                            <i class="bx bx-arrow-back"></i> Back
                        </a>
                    </div>
                </div>

                {{-- Recovery progress strip --}}
                <div class="pv-progress-strip">
                    <div class="pv-progress-fill" style="width:{{ $recoveryPct }}%; background:{{ $pctColor }};"></div>
                </div>
            </div>

            {{-- ══ KPI Cards ══ --}}
            <div class="pv-kpi-row mb-4">
                <div class="pv-kpi">
                    <div class="pv-kpi-label">Contract Value</div>
                    <div class="pv-kpi-val">₹{{ number_format($totalAmount, 0) }}</div>
                </div>
                <div class="pv-kpi" style="border-color:#34a85340;">
                    <div class="pv-kpi-label" style="color:#34a853;">Recovered</div>
                    <div class="pv-kpi-val" style="color:#34a853;">₹{{ number_format($totalPaid, 0) }}</div>
                </div>
                <div class="pv-kpi" style="border-color:#ea433540;">
                    <div class="pv-kpi-label" style="color:#ea4335;">Pending</div>
                    <div class="pv-kpi-val" style="color:#ea4335;">₹{{ number_format($pendingAmount, 0) }}</div>
                </div>
                <div class="pv-kpi">
                    <div class="pv-kpi-label">Recovery Rate</div>
                    <div class="pv-kpi-val" style="color:{{ $pctColor }};">{{ $recoveryPct }}%</div>
                    <div class="pv-kpi-sub">
                        <div style="height:4px;background:#f0f0f0;border-radius:99px;margin-top:6px;overflow:hidden;">
                            <div
                                style="width:{{ $recoveryPct }}%;height:100%;background:{{ $pctColor }};border-radius:99px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pv-kpi">
                    <div class="pv-kpi-label">Invoices</div>
                    <div class="pv-kpi-val">{{ $invoices->count() }}</div>
                    <div class="pv-kpi-sub">{{ $paidInvoices }} paid · {{ $unpaidInvoices }} unpaid</div>
                </div>
                <div class="pv-kpi">
                    <div class="pv-kpi-label">Tasks</div>
                    <div class="pv-kpi-val">{{ $tasks->count() }}</div>
                    <div class="pv-kpi-sub">{{ $pendingTasks }} pending</div>
                </div>
            </div>

            {{-- ══ Tabs ══ --}}
            <div class="ld-tab-nav mb-3" role="tablist">
                <button class="ld-tab active" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                    <i class="bx bx-info-circle"></i> Overview
                </button>
                <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#billing" type="button">
                    <i class="bx bx-receipt"></i> Billing
                    @if($recoveries->count())
                        <span class="pv-tab-badge">{{ $recoveries->count() }}</span>
                    @endif
                </button>
                <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button">
                    <i class="bx bx-file"></i> Invoices
                    @if($invoices->count())
                        <span class="pv-tab-badge">{{ $invoices->count() }}</span>
                    @endif
                </button>
                <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button">
                    <i class="bx bx-task"></i> Tasks
                    @if($pendingTasks)
                        <span class="pv-tab-badge" style="background:#ea4335;">{{ $pendingTasks }}</span>
                    @endif
                </button>
                <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#license" type="button">
                    <i class="bx bx-key"></i> License
                </button>
                <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#proposals" type="button">
                    <i class="bx bx-paper-plane"></i> Proposals
                    @if($proposals->count())
                        <span class="pv-tab-badge">{{ $proposals->count() }}</span>
                    @endif
                </button>
            </div>

            {{-- ══ Tab Content ══ --}}
            <div class="tab-content pv-tab-body" id="pvTabContent">

                {{-- ─ OVERVIEW ─ --}}
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row g-4">
                        {{-- Project Details --}}
                        <div class="col-md-7">
                            <div class="pv-section-card">
                                <div class="pv-section-head">
                                    <i class="bx bx-detail"></i> Project Specifications
                                </div>
                                <div class="pv-info-grid">
                                    <div class="pv-info-item">
                                        <div class="pv-info-label"><i class="bx bx-category"></i> Type</div>
                                        <div class="pv-info-val">
                                            <span class="pj-type-pill">{{ $project->type ?: 'General' }}</span>
                                        </div>
                                    </div>
                                    <div class="pv-info-item">
                                        <div class="pv-info-label"><i class="bx bx-hash"></i> Batch No.</div>
                                        <div class="pv-info-val fw-bold" style="color:#006666;">
                                            {{ $project->batchNo ?: '—' }}
                                        </div>
                                    </div>
                                    <div class="pv-info-item">
                                        <div class="pv-info-label"><i class="bx bx-money"></i> Budget</div>
                                        <div class="pv-info-val fw-bold" style="color:#006666;font-size:1.05rem;">
                                            ₹{{ number_format($totalAmount, 2) }}
                                        </div>
                                    </div>
                                    <div class="pv-info-item">
                                        <div class="pv-info-label"><i class="bx bx-calendar-star"></i> Start Date</div>
                                        <div class="pv-info-val">
                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M, Y') : '—' }}
                                        </div>
                                    </div>
                                    <div class="pv-info-item">
                                        <div class="pv-info-label"><i class="bx bx-calendar-check"></i> Deadline</div>
                                        <div
                                            class="pv-info-val {{ \Carbon\Carbon::parse($project->deadline)->isPast() && $project->status == 1 ? 'text-danger fw-bold' : '' }}">
                                            {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d M, Y') : '—' }}
                                        </div>
                                    </div>
                                    <div class="pv-info-item">
                                        <div class="pv-info-label"><i class="bx bx-user-check"></i> Closed By</div>
                                        <div class="pv-info-val fw-bold" style="color:#1a73e8;">
                                            {{ $project->salesperson_name ?: '—' }}
                                        </div>
                                    </div>
                                    @if($project->deployment_url)
                                        <div class="pv-info-item" style="grid-column:span 2;">
                                            <div class="pv-info-label"><i class="bx bx-globe"></i> Deployment URL</div>
                                            <div class="pv-info-val">
                                                <a href="{{ $project->deployment_url }}" target="_blank"
                                                    class="pv-link">{{ $project->deployment_url }}</a>
                                            </div>
                                        </div>
                                    @endif
                                    @if($project->note)
                                        <div class="pv-info-item" style="grid-column:span 2;">
                                            <div class="pv-info-label"><i class="bx bx-note"></i> Notes</div>
                                            <div class="pv-info-val text-muted">{{ $project->note }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Client Info --}}
                        <div class="col-md-5">
                            <div class="pv-section-card">
                                <div class="pv-section-head">
                                    <i class="bx bx-user"></i> Client Information
                                </div>
                                <div class="pv-client-block">
                                    <div class="pv-client-avatar">
                                        {{ strtoupper(substr($project->client_name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="pv-client-name">{{ $project->client_name }}</div>
                                        @if($project->client_company)
                                            <div class="pv-client-company">{{ $project->client_company }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="pv-contact-list">
                                    @if($project->client_email)
                                        <a href="mailto:{{ $project->client_email }}" class="pv-contact-row">
                                            <span class="pv-contact-icon"><i class="bx bx-envelope"></i></span>
                                            <span>{{ $project->client_email }}</span>
                                        </a>
                                    @endif
                                    @if($project->client_mob)
                                        <a href="tel:{{ $project->client_mob }}" class="pv-contact-row">
                                            <span class="pv-contact-icon"><i class="bx bx-phone"></i></span>
                                            <span>{{ $project->client_mob }}</span>
                                        </a>
                                    @endif
                                    @if($project->client_location)
                                        @php
                                            $locObj = json_decode($project->client_location, true);
                                            $locStr = is_array($locObj) ? implode(', ', array_filter([$locObj['address'] ?? '', $locObj['city'] ?? '', $locObj['state'] ?? '', $locObj['zip'] ?? '', $locObj['country'] ?? ''])) : $project->client_location;
                                        @endphp
                                        @if(trim($locStr))
                                            <div class="pv-contact-row">
                                                <span class="pv-contact-icon"><i class="bx bx-map"></i></span>
                                                <span>{{ $locStr }}</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Recovery Timeline --}}
                        @if($recoveries->count())
                            <div class="col-12">
                                <div class="pv-section-card">
                                    <div class="pv-section-head d-flex justify-content-between">
                                        <span><i class="bx bx-history"></i> Recent Recoveries</span>
                                        <a href="#" class="pv-see-all" data-bs-toggle="tab" data-bs-target="#billing">
                                            View All <i class="bx bx-chevron-right"></i>
                                        </a>
                                    </div>
                                    <div class="pv-timeline">
                                        @foreach($recoveries->take(3) as $rec)
                                            <div class="pv-timeline-item">
                                                <div class="pv-tl-dot {{ $rec->status == '1' ? 'pv-tl-paid' : 'pv-tl-pending' }}">
                                                </div>
                                                <div class="pv-tl-body">
                                                    <div class="pv-tl-title">
                                                        ₹{{ number_format($rec->paid, 2) }}
                                                        <span
                                                            class="pv-badge {{ $rec->status == '1' ? 'pv-badge-success' : 'pv-badge-warn' }}">
                                                            {{ $rec->status == '1' ? 'Paid' : 'Pending' }}
                                                        </span>
                                                    </div>
                                                    <div class="pv-tl-sub">
                                                        {{ \Carbon\Carbon::parse($rec->created_at)->format('d M, Y') }}
                                                        @if($rec->note) · {{ $rec->note }} @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ─ BILLING / RECOVERIES ─ --}}
                <div class="tab-pane fade" id="billing" role="tabpanel">
                    <div class="pv-tab-toolbar">
                        <h2 class="pv-tab-title"><i class="bx bx-receipt"></i> Recovery History</h2>
                        @if(in_array('recoveries_add', $roleArray) || in_array('All', $roleArray))
                            <a href="/manage-recovery?project_id={{ $project->id }}&previous_url={{ urlencode(url()->current()) }}" class="pv-add-btn">
                                <i class="bx bx-plus"></i> Add Recovery
                            </a>
                        @endif
                    </div>

                    {{-- Summary strip --}}
                    <div class="pv-billing-summary mb-3">
                        <div class="pv-bs-item">
                            <span>Total</span>
                            <strong>₹{{ number_format($totalAmount, 2) }}</strong>
                        </div>
                        <div class="pv-bs-sep"></div>
                        <div class="pv-bs-item" style="color:#34a853;">
                            <span>Recovered</span>
                            <strong>₹{{ number_format($totalPaid, 2) }}</strong>
                        </div>
                        <div class="pv-bs-sep"></div>
                        <div class="pv-bs-item" style="color:#ea4335;">
                            <span>Pending</span>
                            <strong>₹{{ number_format($pendingAmount, 2) }}</strong>
                        </div>
                        <div class="pv-bs-sep"></div>
                        <div class="pv-bs-item" style="color:{{ $pctColor }};">
                            <span>Progress</span>
                            <strong>{{ $recoveryPct }}%</strong>
                        </div>
                    </div>

                    @forelse($recoveries as $rec)
                        <div class="pv-rec-card">
                            <div class="pv-rec-icon {{ $rec->status == '1' ? 'pv-rec-paid' : 'pv-rec-pend' }}">
                                <i class="bx {{ $rec->status == '1' ? 'bx-check-circle' : 'bx-time' }}"></i>
                            </div>
                            <div class="pv-rec-body">
                                <div class="pv-rec-amount">₹{{ number_format($rec->paid, 2) }}</div>
                                <div class="pv-rec-meta">
                                    {{ \Carbon\Carbon::parse($rec->created_at)->format('d M, Y · h:i A') }}
                                    @if($rec->note) <span class="pv-sep">·</span> {{ $rec->note }} @endif
                                </div>
                            </div>
                            <span class="pv-badge {{ $rec->status == '1' ? 'pv-badge-success' : 'pv-badge-warn' }}">
                                {{ $rec->status == '1' ? 'Paid' : 'Pending' }}
                            </span>
                            @if(in_array('recoveries_edit', $roleArray) || in_array('All', $roleArray))
                                <div class="pv-rec-actions ms-2">
                                    <a href="/manage-recovery?id={{ $rec->id }}&previous_url={{ urlencode(url()->current()) }}" 
                                       class="btn btn-sm btn-light border" title="Edit Recovery">
                                        <i class="bx bx-edit-alt text-muted"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="pv-empty-state">
                            <i class="bx bx-receipt"></i>
                            <p>No recovery records yet.</p>
                            @if(in_array('recoveries_add', $roleArray) || in_array('All', $roleArray))
                                <a href="/manage-recovery?project_id={{ $project->id }}&previous_url={{ urlencode(url()->current()) }}" class="pv-add-btn">Add First Recovery</a>
                            @endif
                        </div>
                    @endforelse
                </div>

                {{-- ─ INVOICES ─ --}}
                <div class="tab-pane fade" id="invoices" role="tabpanel">
                    <div class="pv-tab-toolbar">
                        <h2 class="pv-tab-title"><i class="bx bx-file"></i> Client Invoices</h2>
                        @if(in_array('invoice_add', $roleArray) || in_array('All', $roleArray))
                            <a href="/manage-invoice?project_id={{ $project->id }}" class="pv-add-btn"><i
                                    class="bx bx-plus"></i> Create Invoice</a>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="leads-table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $inv)
                                    @php
                                        $overdue = \Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->status != 'paid';
                                    @endphp
                                    <tr>
                                        <td><span class="fw-bold" style="color:#006666;">{{ $inv->invoice_number }}</span></td>
                                        <td>{{ \Carbon\Carbon::parse($inv->date)->format('d M, Y') }}</td>
                                        <td>
                                            <span class="{{ $overdue ? 'text-danger fw-bold' : '' }}">
                                                {{ \Carbon\Carbon::parse($inv->due_date)->format('d M, Y') }}
                                                @if($overdue) <i class="bx bx-error-circle ms-1"></i> @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if($inv->status == 'paid')
                                                <span class="pv-badge pv-badge-success">Paid</span>
                                            @elseif($inv->status == 'partial')
                                                <span class="pv-badge pv-badge-info">Partial</span>
                                            @else
                                                <span class="pv-badge pv-badge-danger">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('invoicePreview', $inv->id) }}" class="btn kb-action-btn"
                                                    title="View">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('invoicePdfPreview', $inv->id) }}" class="btn kb-action-btn"
                                                    title="PDF" style="color:#ea4335;">
                                                    <i class="bx bxs-file-pdf"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="pv-empty-state">
                                                <i class="bx bx-file"></i>
                                                <p>No invoices found.</p>
                                                @if(in_array('invoice_add', $roleArray) || in_array('All', $roleArray))
                                                    <a href="/manage-invoice?project_id={{ $project->id }}"
                                                        class="pv-add-btn">Create Invoice</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ─ TASKS ─ --}}
                <div class="tab-pane fade" id="tasks" role="tabpanel">
                    <div class="pv-tab-toolbar">
                        <h2 class="pv-tab-title"><i class="bx bx-task"></i> Project Tasks</h2>

                        @if(in_array('tasks_add', $roleArray) || in_array('All', $roleArray))
                            @if($tasks->count() > 0)
                                <a href="/task?id={{ $tasks->first()->id }}&project_id={{ $project->id }}" class="pv-add-btn">
                                    <i class="bx bx-edit-alt"></i> Manage Tasks
                                </a>
                            @else
                                <a href="/task?action=add&project_id={{ $project->id }}" class="pv-add-btn">
                                    <i class="bx bx-plus"></i> Manage Tasks
                                </a>
                            @endif
                        @endif
                    </div>
                    @forelse($tasks as $t)
                        @php
                            $isDone = ($t->status == '4' || $t->status == '5');
                            $taskOverdue = $t->due_date && \Carbon\Carbon::parse($t->due_date)->isPast() && !$isDone;

                            $statusMap = [
                                '1' => ['Urgent', 'pv-badge-danger', 'pv-rec-danger'],
                                '2' => ['Pending', 'pv-badge-warn', 'pv-rec-pend'],
                                '3' => ['In Progress', 'pv-badge-info', 'pv-rec-pend'],
                                '4' => ['Done', 'pv-badge-success', 'pv-rec-paid'],
                                '5' => ['Closed', 'pv-badge-secondary', 'pv-rec-paid'],
                                '6' => ['New', 'pv-badge-info', 'pv-rec-pend'],
                            ];
                            $stInfo = $statusMap[$t->status] ?? ['Open', 'pv-badge-info', 'pv-rec-pend'];
                        @endphp
                        <div class="pv-rec-card task-item-row" id="task-row-{{ $t->id }}">
                            <div class="pv-rec-icon {{ $stInfo[2] }}" id="task-icon-{{ $t->id }}">
                                <i class="bx {{ $isDone ? 'bx-check-double' : 'bx-task' }}"></i>
                            </div>
                            <div class="pv-rec-body">
                                <div class="pv-rec-amount task-name {{ $isDone ? 'pvt-name-done' : '' }}"
                                    id="task-name-{{ $t->id }}" style="font-size:0.9rem;">{{ $t->title }}</div>
                                <div class="pv-rec-meta">
                                    @if($t->label_name) <span class="pv-badge pv-badge-info me-1">{{ $t->label_name }}</span>
                                    @endif
                                    @if($t->due_date)
                                        Due: <span
                                            class="task-due-date {{ $taskOverdue ? 'text-danger fw-bold' : '' }}">{{ \Carbon\Carbon::parse($t->due_date)->format('d M, Y') }}</span>
                                    @else
                                        <span class="text-muted small">No due date</span>
                                    @endif
                                    @if(in_array('tasks_add', $roleArray) || in_array('All', $roleArray))
                                        <a href="/task?project_id={{ $project->id }}&parent_id={{ $t->id }}"
                                            class="ms-2 text-primary small" title="Add Subtask">
                                            <i class="bx bx-plus-circle"></i> Subtask
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="task-status-badge-container">
                                <span class="pv-badge {{ $stInfo[1] }}">{{ $stInfo[0] }}</span>
                                @if($taskOverdue && !$isDone)
                                    <span class="pv-badge pv-badge-danger ms-1">Overdue</span>
                                @endif
                            </div>
                        </div>

                        {{-- --- Subtasks --- --}}
                        @foreach($t->subtasks as $st)
                            @php
                                $stdone = ($st->status == '4' || $st->status == '5');
                                $stOverdue = $st->due_date && \Carbon\Carbon::parse($st->due_date)->isPast() && !$stdone;
                                $stInfo = $statusMap[$st->status] ?? ['Open', 'pv-badge-info', 'pv-rec-pend'];
                            @endphp
                            <div class="pv-rec-card task-item-row ms-5 border-start" id="task-row-{{ $st->id }}"
                                style="background: rgba(0,0,0,0.02);">
                                <div class="pv-rec-icon {{ $stInfo[2] }}" id="task-icon-{{ $st->id }}"
                                    style="transform: scale(0.85);">
                                    <i class="bx {{ $stdone ? 'bx-check-double' : 'bx-task' }}"></i>
                                </div>
                                <div class="pv-rec-body">
                                    <div class="pv-rec-amount task-name {{ $stdone ? 'pvt-name-done' : '' }}"
                                        id="task-name-{{ $st->id }}" style="font-size:0.85rem;">{{ $st->title }}</div>
                                    <div class="pv-rec-meta" style="font-size:0.75rem;">
                                        @if($st->due_date)
                                            Due: <span
                                                class="task-due-date {{ $stOverdue ? 'text-danger fw-bold' : '' }}">{{ \Carbon\Carbon::parse($st->due_date)->format('d M, Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="task-status-badge-container">
                                    <span class="pv-badge {{ $stInfo[1] }}" style="font-size:0.65rem;">{{ $stInfo[0] }}</span>
                                </div>
                            </div>
                        @endforeach
                    @empty
                        <div class="pv-empty-state">
                            <i class="bx bx-task"></i>
                            <p>No tasks assigned to this project.</p>
                        </div>
                    @endforelse
                </div>

                {{-- ─ LICENSE ─ --}}
                <div class="tab-pane fade" id="license" role="tabpanel">
                    <div class="pv-tab-toolbar">
                        <h2 class="pv-tab-title"><i class="bx bx-key"></i> License Details</h2>
                        @if($license)
                            <a href="/manage-license?id={{ $license->id }}" class="pv-add-btn pv-btn-outline">
                                <i class="bx bx-edit"></i> Edit License
                            </a>
                        @else
                            <a href="/manage-license?project_id={{ $project->id }}" class="pv-add-btn"><i
                                    class="bx bx-plus"></i> Add License</a>
                        @endif
                    </div>

                    @if($license)
                        @php
                            $licExpired = \Carbon\Carbon::parse($license->expiry_date)->isPast();
                            $licDaysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($license->expiry_date), false);
                        @endphp
                        <div class="pv-license-card">
                            {{-- Key Display --}}
                            <div class="pv-lic-key-box">
                                <div class="pv-lic-key-label"><i class="bx bx-key"></i> License Key</div>
                                <div class="pv-lic-key-row">
                                    <code id="licKey">{{ $license->eselicense_key }}</code>
                                    <button class="pv-copy-btn" onclick="copyKey()" title="Copy">
                                        <i class="bx bx-copy"></i> Copy
                                    </button>
                                </div>
                            </div>

                            <div class="pv-lic-grid">
                                <div class="pv-lic-item">
                                    <div class="pv-info-label"><i class="bx bx-code-alt"></i> Technology Stack</div>
                                    <div class="pv-info-val fw-bold">{{ $license->technology_stack ?: 'N/A' }}</div>
                                </div>
                                <div class="pv-lic-item">
                                    <div class="pv-info-label"><i class="bx bx-calendar-event"></i> Expiry Date</div>
                                    <div class="pv-info-val fw-bold {{ $licExpired ? 'text-danger' : 'text-success' }}">
                                        {{ \Carbon\Carbon::parse($license->expiry_date)->format('d F, Y') }}
                                    </div>
                                </div>
                                <div class="pv-lic-item" style="grid-column:span 2;">
                                    @if($licExpired)
                                        <div class="pv-lic-status-bar pv-lic-expired">
                                            <i class="bx bx-error"></i>
                                            License Expired {{ \Carbon\Carbon::parse($license->expiry_date)->diffForHumans() }}
                                        </div>
                                    @elseif($licDaysLeft <= 30)
                                        <div class="pv-lic-status-bar pv-lic-warn">
                                            <i class="bx bx-alarm"></i>
                                            Expires in {{ $licDaysLeft }} days — Consider renewal soon
                                        </div>
                                    @else
                                        <div class="pv-lic-status-bar pv-lic-ok">
                                            <i class="bx bx-shield-quarter"></i>
                                            Active · {{ $licDaysLeft }} days remaining
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="pv-empty-state">
                            <i class="bx bx-key"></i>
                            <p>No license associated with this project.</p>
                            <a href="/manage-license?project_id={{ $project->id }}" class="pv-add-btn">Add License</a>
                        </div>
                    @endif
                </div>

                {{-- ─ PROPOSALS ─ --}}
                <div class="tab-pane fade" id="proposals" role="tabpanel">
                    <div class="pv-tab-toolbar">
                        <h2 class="pv-tab-title"><i class="bx bx-paper-plane"></i> Client Proposals</h2>
                        <a href="/manage-proposal?project_id={{ $project->id }}" class="pv-add-btn"><i
                                class="bx bx-plus"></i> Create Proposal</a>
                    </div>
                    <div class="table-responsive">
                        <table class="leads-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th class="m-none">Date Sent</th>
                                    <th class="m-none">Open Till</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proposals as $prop)
                                    <tr>
                                        <td class="fw-600">{{ Str::limit($prop->subject, 35) }}</td>
                                        <td class="m-none text-muted">
                                            {{ \Carbon\Carbon::parse($prop->proposal_date)->format('d M, Y') }}
                                        </td>
                                        <td class="m-none">
                                            @php $propExpired = \Carbon\Carbon::parse($prop->open_till)->isPast() && !in_array($prop->status, ['Accepted', 'Declined']); @endphp
                                            <span class="{{ $propExpired ? 'text-danger fw-bold' : 'text-muted' }}">
                                                {{ \Carbon\Carbon::parse($prop->open_till)->format('d M, Y') }}
                                            </span>
                                        </td>
                                        <td class="fw-bold" style="color:#006666;">₹{{ number_format($prop->grand_total, 0) }}
                                        </td>
                                        <td>
                                            @if($prop->status == 'Accepted')
                                                <span class="pv-badge pv-badge-success">Accepted</span>
                                            @elseif($prop->status == 'Declined')
                                                <span class="pv-badge pv-badge-danger">Declined</span>
                                            @else
                                                <span class="pv-badge pv-badge-info">{{ $prop->status ?: 'Sent' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="pv-empty-state">
                                                <i class="bx bx-paper-plane"></i>
                                                <p>No proposals linked to this project.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- /tab-content --}}
        </div>{{-- /dash-container --}}
    </section>

    <style>
        /* Task Interactive States */
        .pvt-check-wrap {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .pvt-check-circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid currentColor;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            background: transparent;
        }

        .pvt-check-circle i {
            font-size: 14px;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s;
        }

        .pvt-checked {
            background: currentColor !important;
        }

        .pvt-checked i {
            opacity: 1;
            transform: scale(1);
            color: #fff !important;
        }

        .pvt-name-done {
            text-decoration: line-through;
            opacity: 0.6;
        }
    </style>

    <script>
        function copyKey() {
            const key = document.getElementById('licKey').textContent.trim();
            navigator.clipboard.writeText(key).then(() => {
                const btn = document.querySelector('.pv-copy-btn');
                btn.innerHTML = '<i class="bx bx-check"></i> Copied!';
                btn.style.background = '#34a853';
                setTimeout(() => {
                    btn.innerHTML = '<i class="bx bx-copy"></i> Copy';
                    btn.style.background = '';
                }, 2000);
            });
        }

        $(document).ready(function () {
            // 1. Animate progress bar on load
            const fills = document.querySelectorAll('.pv-progress-fill');
            fills.forEach(f => {
                const w = f.style.width;
                f.style.width = '0';
                setTimeout(() => { f.style.width = w; }, 100);
            });

            // 2. Handle task status toggle
            $('.task-status-check').on('change', function () {
                const $check = $(this);
                const taskId = $check.data('id');
                const isChecked = $check.is(':checked');
                const $row = $('#task-row-' + taskId);
                const $icon = $('#task-icon-' + taskId);
                const $circle = $check.next('.pvt-check-circle');
                const $name = $('#task-name-' + taskId);
                const $badgeContainer = $row.find('.task-status-badge-container');

                // Immediate visual feedback
                if (isChecked) {
                    $circle.addClass('pvt-checked');
                    $name.addClass('pvt-name-done');
                    $icon.removeClass('pv-rec-pend pv-rec-danger').addClass('pv-rec-paid');
                    $badgeContainer.html('<span class="pv-badge pv-badge-success">Done</span>');
                } else {
                    $circle.removeClass('pvt-checked');
                    $name.removeClass('pvt-name-done');
                    // Assuming it goes back to 'Pending' visually; server state will determine if it stays overdue
                    $icon.removeClass('pv-rec-paid').addClass('pv-rec-pend');
                    $badgeContainer.html('<span class="pv-badge pv-badge-warn">Pending</span>');
                }

                $.ajax({
                    url: "{{ route('crm_tasks.update_status') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: taskId,
                        status: isChecked ? 'Completed' : 'Pending'
                    },
                    error: function () {
                        alert('Connection error while updating task status');
                    }
                });
            });
        });
    </script>
@endsection
