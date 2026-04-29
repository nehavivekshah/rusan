@extends('layout')
@section('title', 'Leads Pipeline (Kanban) - eseCRM')

<style>
    /* Kanban Filter Bar */
    .kb-filter-bar {
        background: #fff;
        border: 1px solid #e8eaed;
        border-radius: 12px;
        padding: 12px 16px;
        margin: 0 0px 12px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .06)
    }

    .kb-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-end
    }

    .kb-filter-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1 1 150px;
        min-width: 120px
    }

    .kb-filter-label {
        font-size: .72rem;
        font-weight: 600;
        color: #5f6368;
        text-transform: uppercase;
        letter-spacing: .3px;
        display: flex;
        align-items: center;
        gap: 4px
    }

    .kb-filter-input,
    .kb-filter-select {
        height: 36px;
        border: 1.5px solid #dadce0;
        border-radius: 8px;
        padding: 0 10px;
        font-size: .82rem;
        color: #202124;
        background: #f8f9fa;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        width: 100%
    }

    .kb-filter-input:focus,
    .kb-filter-select:focus {
        border-color: #006666;
        box-shadow: 0 0 0 3px rgba(0, 102, 102, .10);
        background: #fff
    }

    .kb-filter-actions {
        flex: 0 0 auto;
        flex-direction: row !important;
        align-items: flex-end;
        gap: 6px;
        min-width: auto
    }

    .kb-filter-active-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #f29900;
        border-radius: 50%;
        margin-left: 4px;
        vertical-align: middle;
        animation: kbDotPulse 1.5s ease-in-out infinite
    }

    @keyframes kbDotPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 1
        }

        50% {
            transform: scale(1.4);
            opacity: .7
        }
    }

    @media(max-width:576px) {
        .kb-filter-bar {
            margin: 0 8px 10px;
            padding: 10px 12px
        }

        .kb-filter-field {
            flex: 1 1 100%
        }

        .kb-filter-actions {
            width: 100%
        }
    }
</style>

@section('content')

    <section class="task__section">
        @include('inc.header', ['title' => 'Leads Pipeline'])

        <div class="dash-container pb-0">

            {{-- Kanban Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <div class="kb-stage-legend">
                        <span class="kb-dot" style="background:#1a73e8;"></span> New
                        <span class="kb-dot ms-2" style="background:#f29900;"></span> Contacted
                        <span class="kb-dot ms-2" style="background:#7c3aed;"></span> Qualified
                        <span class="kb-dot ms-2" style="background:#006666;"></span> Proposal
                        <span class="kb-dot ms-2" style="background:#34a853;"></span> Won
                        <span class="kb-dot ms-2" style="background:#ea4335;"></span> Lost
                    </div>
                    <span class="kb-total-badge" id="kbTotalBadge">Loading...</span>
                </div>
                <div class="leads-toolbar-right">
                    <button class="lb-icon-btn" id="kbRefreshBtn" title="Refresh Board">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <a href="/leads" class="lb-btn lb-btn-ghost">
                        <i class="bx bx-list-ul"></i>
                        <span class="d-none d-sm-inline">List View</span>
                    </a>
                    <a href="/manage-lead" class="lb-btn lb-btn-primary">
                        <i class="bx bx-plus"></i>
                        <span class="d-none d-sm-inline">Add Lead</span>
                    </a>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="kb-filter-bar" id="kbFilterBar">
                <div class="kb-filter-row">
                    {{-- Search --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-search"></i> Search</label>
                        <input type="text" id="kbSearch" class="kb-filter-input" placeholder="Name, company, mobile…">
                    </div>
                    {{-- Assigned To --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-user"></i> Assigned To</label>
                        <select id="kbAssigned" class="kb-filter-select">
                            <option value="">— All Salespersons —</option>
                            @foreach($getUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Date From --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-calendar"></i> Date From</label>
                        <input type="date" id="kbDateFrom" class="kb-filter-input">
                    </div>
                    {{-- Date To --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-calendar-check"></i> Date To</label>
                        <input type="date" id="kbDateTo" class="kb-filter-input">
                    </div>
                    {{-- Industry --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-building"></i> Industry</label>
                        <select id="kbIndustry" class="kb-filter-select">
                            <option value="">— All Industries —</option>
                        </select>
                    </div>
                    {{-- Actions --}}
                    <div class="kb-filter-field kb-filter-actions">
                        <button class="lb-btn lb-btn-primary" id="kbApplyFilter">
                            <i class="bx bx-filter-alt"></i> Apply
                        </button>
                        <button class="lb-btn lb-btn-ghost" id="kbResetFilter">
                            <i class="bx bx-x"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- Kanban Board --}}
        <div class="kb-board" id="kanbanBoard">

            @php
                $stages = [
                    ['key' => 'New', 'status' => 0, 'label' => 'New Leads', 'cls' => 'new', 'color' => '#1a73e8'],
                    ['key' => 'Contacted', 'status' => 1, 'label' => 'Contacted', 'cls' => 'contacted', 'color' => '#f29900'],
                    ['key' => 'Qualified', 'status' => 2, 'label' => 'Qualified', 'cls' => 'qualified', 'color' => '#7c3aed'],
                    ['key' => 'Proposal', 'status' => 3, 'label' => 'Proposal Sent', 'cls' => 'proposal', 'color' => '#006666'],
                    ['key' => 'Closed', 'status' => 5, 'label' => 'Closed (Won)', 'cls' => 'closed', 'color' => '#34a853'],
                    ['key' => 'Lost', 'status' => 9, 'label' => 'Lost', 'cls' => 'lost', 'color' => '#ea4335'],
                ]
            @endphp

            @foreach($stages as $stage)
                <div class="kb-col" data-stage="{{ $stage['key'] }}" data-status="{{ $stage['status'] }}" data-page="1"
                    ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">

                    <div class="kb-col-header kb-col-header-{{ $stage['cls'] }}">
                        <div class="kb-col-title">
                            <span class="kb-col-dot" style="background:{{ $stage['color'] }};"></span>
                            {{ $stage['label'] }}
                        </div>
                        <span class="kb-count" id="count-{{ $stage['key'] }}">
                            <span class="kb-spinner-sm"></span>
                        </span>
                    </div>

                    {{-- skeleton placeholder --}}
                    <div class="kb-items" id="col-{{ $stage['key'] }}">
                        <div class="kb-skeleton"></div>
                        <div class="kb-skeleton" style="height:60px;"></div>
                        <div class="kb-skeleton" style="height:70px;"></div>
                    </div>

                    <div class="kb-load-more-wrap" id="more-{{ $stage['key'] }}" style="display:none;">
                        <button class="kb-load-more-btn"
                            onclick="loadStage('{{ $stage['key'] }}', {{ $stage['status'] }}, true)">
                            <i class="bx bx-chevron-down"></i>
                            <span class="kb-load-more-label" id="more-label-{{ $stage['key'] }}">Load more</span>
                        </button>
                    </div>

                </div>
            @endforeach

        </div>

    </section>

    {{-- Lead Details Popup Modal --}}
    <div class="modal fade" id="kbLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width:900px;">
            <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none;">

                {{-- Header Banner --}}
                <div class="ld-header">
                    <div class="ld-header-content">
                        <div class="d-flex align-items-center gap-3 flex-1 min-w-0">
                            <div class="ld-avatar" id="kb_leadAvatar">L</div>
                            <div class="min-w-0">
                                <h5 class="ld-name" id="kb_leadName">Lead Details</h5>
                                <span class="ld-company" id="kb_leadCompany">—</span>
                                <div class="mt-2 text-white-50" style="font-size:0.75rem;">
                                    <i class="bx bx-calendar-plus me-1"></i> Added on <span id="kb_leadSince">—</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="ld-status-chip" id="kb_leadStatus"
                                style="background:#f29900 !important; color:#fff !important;">Fresh</span>
                            <a class="ld-quick-btn" id="kb_btnCall" href="#" title="Call">
                                <i class="bx bx-phone"></i>
                            </a>
                            <a class="ld-quick-btn" id="kb_btnWa"
                                style="background:rgba(37,211,102,0.2) !important; color:#25D366 !important;" href="#"
                                title="WhatsApp" onclick="event.preventDefault(); directSendKbWaFromModal();">
                                <i class="bx bxl-whatsapp"></i>
                            </a>
                            <a class="ld-quick-btn" id="kb_btnMail" href="#" title="Email">
                                <i class="bx bx-envelope"></i>
                            </a>
                            <button type="button" class="btn text-white ps-2 pe-0" data-bs-dismiss="modal"
                                aria-label="Close" style="box-shadow:none;">
                                <i class="bx bx-x" style="font-size:1.8rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-0">
                    <input type="hidden" id="kb_card_id">

                    {{-- Tabs Navigation --}}
                    <div class="ld-tab-nav">
                        <button class="ld-tab active" onclick="kbTab(this,'kb-tab-info')">
                            <i class="bx bx-user-circle"></i> Profile
                        </button>
                        <button class="ld-tab" onclick="kbTab(this,'kb-tab-conv')">
                            <i class="bx bx-message-detail"></i> Timeline
                        </button>
                        <button class="ld-tab" onclick="kbTab(this,'kb-tab-props')">
                            <i class="bx bx-file"></i> Proposals
                        </button>
                        <button class="ld-tab" onclick="kbTab(this,'kb-tab-assign')">
                            <i class="bx bx-user-plus"></i> Assign
                        </button>
                        <button class="ld-tab" onclick="kbTab(this,'kb-tab-wp-template')">
                            <i class="bx bxl-whatsapp"></i> Wp Template
                        </button>
                    </div>

                    <div id="kb-tab-info" style="padding:16px;">
                        <div class="ld-info-grid" id="kb_infoGrid">
                            {{-- Contact Card --}}
                            <div class="ld-info-card">
                                <div class="ld-info-card-header"><i class="bx bx-phone-call"></i> Contact Details</div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label"><i class="bx bx-mobile-alt"></i> Mobile</span>
                                    <span class="ld-info-val" id="kb_mob">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label"><i class="bx bxl-whatsapp"></i> WhatsApp</span>
                                    <span class="ld-info-val" id="kb_wa">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label"><i class="bx bx-envelope"></i> Email</span>
                                    <span class="ld-info-val" id="kb_email">—</span>
                                </div>
                            </div>

                            {{-- Business Card --}}
                            <div class="ld-info-card">
                                <div class="ld-info-card-header"><i class="bx bx-buildings"></i> Business Info</div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label"><i class="bx bx-briefcase"></i> Company</span>
                                    <span class="ld-info-val" id="kb_company_val">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label"><i class="bx bx-user-pin"></i> Position</span>
                                    <span class="ld-info-val" id="kb_position">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="ld-info-label"><i class="bx bx-trending-up"></i> Industry</span>
                                    <span class="ld-info-val" id="kb_industry">—</span>
                                </div>
                            </div>

                            {{-- CRM Card --}}
                            <div class="ld-info-card" style="grid-column:1/-1;">
                                <div class="ld-info-card-header"><i class="bx bx-brain"></i> CRM Intelligence</div>
                                <div class="row g-0">
                                    <div class="col-md-6 pe-md-3" style="border-right:1.5px solid #f1f3f4;">
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-target-lock"></i> Purpose</span>
                                            <span class="ld-info-val" id="kb_purpose">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-rupee"></i> Lead Value</span>
                                            <span class="ld-info-val fw-bold text-success" id="kb_value">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-user-check"></i> POC</span>
                                            <span class="ld-info-val" id="kb_poc">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-star"></i> Lead Score</span>
                                            <span class="ld-info-val" id="kb_score">—</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 ps-md-3">
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-user-pin"></i> Assigned To</span>
                                            <span class="ld-info-val text-primary fw-bold" id="kb_assigned">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-error"></i> Potential Duplicate</span>
                                            <span class="ld-info-val" id="kb_duplicate">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-purchase-tag-alt"></i> Tags</span>
                                            <span class="ld-info-val" id="kb_tags">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-map-pin"></i> Location</span>
                                            <span class="ld-info-val text-muted" id="kb_location_val" style="font-size:0.75rem;">—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ld-action-bar">
                            <a href="#" id="kb_editBtn" class="ld-btn ld-btn-primary"><i class="bx bx-edit-alt"></i> Edit
                                Lead</a>
                        </div>
                    </div>

                    {{-- Timeline Tab --}}
                    <div id="kb-tab-conv" style="display:none; padding:16px;">
                        <div class="ld-timeline-head mb-3">
                            <i class="bx bx-history"></i> Conversation History
                        </div>
                        <div id="kb_timeline">
                            <p class="text-muted text-center" style="font-size:0.82rem;">Loading…</p>
                        </div>
                    </div>

                    {{-- Proposals Tab --}}
                    <div id="kb-tab-props" style="display:none; padding:16px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="ld-timeline-head mb-0">
                                <i class="bx bx-file"></i> Lead Proposals
                            </div>
                            <a href="/manage-proposal" class="ld-btn ld-btn-primary py-1 px-3" style="font-size:0.75rem;">
                                <i class="bx bx-plus"></i> New
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:0.80rem;">
                                <thead style="background:#006666 !important;">
                                    <tr>
                                        <th style="color:#ffffff !important;">#ID</th>
                                        <th style="color:#ffffff !important;">Subject</th>
                                        <th style="color:#ffffff !important;">Total</th>
                                        <th style="color:#ffffff !important;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="kb_proposals"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Assign Tab --}}
                    <div id="kb-tab-assign" style="display:none; padding:30px 20px;">
                        <div class="text-center mb-4">
                            <div class="ld-avatar mx-auto mb-3"
                                style="background:#e6f4ea; color:#006666; width:64px; height:64px; border:none;">
                                <i class="bx bx-user-plus" style="font-size:2rem;"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Assign Salesperson</h6>
                            <p class="text-muted small">Update the ownership of this lead instantly.</p>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <select class="form-select" id="kb_quick_assign">
                                <option value="">— Select —</option>
                                @foreach($getUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="ld-btn ld-btn-primary w-100 py-2" id="kb_quickAssignBtn">
                            <i class="bx bx-check-circle"></i> Assign Lead
                        </button>
                        <div id="kb_assignMsg" class="mt-3 text-center small"></div>
                    </div>

                    {{-- Wp Template Tab --}}
                    <div id="kb-tab-wp-template" style="display:none; padding:28px 20px;">
                        <div class="ld-assign-card">
                            <div class="ld-assign-icon" style="background:rgba(37,211,102,0.1);color:#25d366;"><i
                                    class="bx bxl-whatsapp"></i></div>
                            <h6 class="mb-1" style="font-weight:700;color:#202124;font-size:0.95rem;">WhatsApp
                                Template</h6>
                            <p style="font-size:0.78rem;color:#5f6368;margin-bottom:18px;">Customize the WhatsApp
                                message for this lead.</p>

                            <div class="form-group mb-3">
                                <textarea id="kb_waMessageTextTabbed" class="form-control" rows="6"
                                    placeholder="Hi, I wanted to follow up about..."
                                    style="border-radius:8px;border:1px solid #dadce0;padding:12px;font-size:0.93rem;resize:none;height:auto!important;"></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="ld-btn ld-btn-primary w-100" id="kb_saveWpTemplateBtn">
                                    <i class="bx bx-bookmark"></i> Save Template
                                </button>
                                <button type="button" class="ld-btn ld-btn-primary w-100" id="kb_sendWpTemplateBtn"
                                    style="background:#25d366;border-color:#25d366;">
                                    <i class="bx bx-send"></i> Open WhatsApp
                                </button>
                            </div>
                            <div id="kb_wpTemplateStatusMsg" class="mt-2 text-center"
                                style="font-size:0.82rem;font-weight:600;color:#25D366;display:none;">
                                <i class="bx bx-check-circle"></i> Template Saved!
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const KANBAN_URL = "{{ route('leads.kanban_data') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const LIMIT = 15;

        const stageColors = {
            'New': { border: '#1a73e8', bg: 'rgba(26,115,232,0.08)' },
            'Contacted': { border: '#f29900', bg: 'rgba(242,153,0,0.08)' },
            'Qualified': { border: '#7c3aed', bg: 'rgba(124,58,237,0.10)' },
            'Proposal': { border: '#006666', bg: 'rgba(0,102,102,0.08)' },
            'Closed': { border: '#34a853', bg: 'rgba(52,168,83,0.08)' },
            'Lost': { border: '#ea4335', bg: 'rgba(234,67,53,0.08)' },
        };

        // State: pages loaded per stage
        const colPages = {
            'New': 1, 'Contacted': 1, 'Qualified': 1,
            'Proposal': 1, 'Closed': 1, 'Lost': 1
        };

        const stageStatusMap = {
            'New': 0, 'Contacted': 1, 'Qualified': 2,
            'Proposal': 3, 'Closed': 5, 'Lost': 9
        };

        // Build a card's HTML
        function buildCard(lead, stage) {
            const color = stageColors[stage];
            const initial = (lead.name || lead.company || 'L').charAt(0).toUpperCase();

            // Header: name + company — always shown separately
            const nameHtml = `<div class="kb-card-name">${escHtml(lead.name || '—')}</div>`;
            const companyHtml = lead.company
                ? `<div class="kb-card-company"><i class="bx bx-buildings"></i> ${escHtml(lead.company)}</div>` : '';

            // Source badge
            const sourceHtml = lead.source
                ? `<span class="kb-card-source"><i class="bx bx-user-plus"></i> ${escHtml(lead.source)}</span>` : '';

            // Lead value chip
            const valueHtml = lead.values
                ? `<span class="kb-card-value-chip"><i class="bx bx-rupee"></i>${escHtml(String(lead.values))}</span>` : '';

            // Contact action buttons — always visible
            const assignedName = kbUserMap[lead.assigned] || lead.assigned || 'Unassigned';
            const assignedHtml = `<div class="kb-card-poc mt-2"><i class="bx bx-user-circle"></i> ${escHtml(assignedName)}</div>`;

            const waBtn = lead.whatsapp
                ? `<a href="javascript:void(0)" class="btn kb-action-btn kb-action-wa kb-card-wa-btn" data-wa="${escHtml(lead.whatsapp)}" data-lead-id="${lead.id}" title="WhatsApp" onclick="event.stopPropagation(); directSendKbWa(this);"><i class="bx bxl-whatsapp"></i></a>` : '';
            const callBtn = lead.mob
                ? `<a href="tel:+${encodeURIComponent(lead.mob)}" class="btn kb-action-btn kb-action-call" title="Call ${escHtml(lead.mob)}" onclick="event.stopPropagation();"><i class="bx bx-phone"></i></a>` : '';
            const emailBtn = lead.email
                ? `<a href="mailto:${escHtml(lead.email)}" class="btn kb-action-btn kb-action-email" title="Email ${escHtml(lead.email)}" onclick="event.stopPropagation();"><i class="bx bx-envelope"></i></a>` : '';
            const editBtn = `<a href="/manage-lead?id=${lead.id}&from=kanban" class="btn kb-action-btn kb-action-edit" title="Edit Lead" onclick="event.stopPropagation();"><i class="bx bx-edit-alt"></i></a>`;

            return `
                    <div class="kb-card" id="lead-${lead.id}" draggable="true"
                         ondragstart="drag(event)" data-id="${lead.id}"
                         style="border-left-color:${color.border};">

                        {{-- Top: avatar + name + company --}}
                        <div class="kb-card-header">
                            <div class="kb-card-avatar" style="background:${color.bg}; color:${color.border};">${initial}</div>
                            <div class="kb-card-name-block">
                                ${nameHtml}
                                ${companyHtml}
                            </div>
                        </div>

                        {{-- Meta row: source + value --}}
                        <div class="kb-card-meta-row">
                            ${sourceHtml}
                            ${valueHtml}
                        </div>

                        {{-- Assigned To --}}
                        ${assignedHtml}

                        {{-- Action buttons — always visible --}}
                        <div class="kb-card-actions kb-card-actions-visible">
                            ${waBtn}${callBtn}${emailBtn}
                            <span class="kb-action-spacer"></span>
                            ${editBtn}
                        </div>
                    </div>`;
        }


        function escHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ── Get current filter values ──
        function getFilters() {
            return {
                search: $('#kbSearch').val().trim(),
                assigned: $('#kbAssigned').val(),
                date_from: $('#kbDateFrom').val(),
                date_to: $('#kbDateTo').val(),
                industry: $('#kbIndustry').val(),
            };
        }

        // Populate Industry dropdown from unique lead values
        function loadIndustryOptions() {
            $.get('/get-lead-industries', function(res) {
                var sel = $('#kbIndustry');
                sel.find('option:not(:first)').remove();
                (res.industries || []).forEach(function(ind) {
                    if (ind) sel.append('<option value="' + ind + '">' + ind + '</option>');
                });
            });
        }

        // Load (or append) cards for one stage
        function loadStage(stage, statusInt, append) {
            const page = append ? (colPages[stage] + 1) : 1;
            const colEl = $('#col-' + stage);
            const moreEl = $('#more-' + stage);
            const cntEl = $('#count-' + stage);

            if (!append) {
                colEl.html('<div class="kb-skeleton"></div><div class="kb-skeleton" style="height:60px;"></div>');
                moreEl.hide();
            } else {
                $(`#more-label-${stage}`).html('<i class="bx bx-loader-alt bx-spin"></i> Loading...');
            }

            var params = Object.assign({ stage: statusInt, page: page, limit: LIMIT }, getFilters());

            $.get(KANBAN_URL, params, function (res) {
                if (!append) colEl.empty();

                res.data.forEach(function (lead) {
                    colEl.append(buildCard(lead, stage));
                });

                colPages[stage] = page;

                // Update count badge
                cntEl.text(res.total);

                // Load More button
                if (res.has_more) {
                    const remaining = res.total - (page * LIMIT);
                    $(`#more-label-${stage}`).html(
                        `<i class="bx bx-chevron-down"></i> Load ${Math.min(remaining, LIMIT)} more of ${remaining}`
                    );
                    moreEl.show();
                } else {
                    moreEl.hide();
                }
            }).fail(function () {
                if (!append) colEl.html('<div class="kb-empty-col"><i class="bx bx-error"></i> Failed to load</div>');
            });
        }

        // Load SUMMARY counts first (cheap), then load each column
        function initBoard() {
            let totalAll = 0;
            var filters = getFilters();

            $.get(KANBAN_URL, filters, function (res) {
                if (res.counts) {
                    for (let key in res.counts) {
                        totalAll += res.counts[key];
                    }
                }
                // Show active filter indicator if any filter is set
                var hasFilter = filters.search || filters.assigned || filters.date_from || filters.date_to;
                var badge = totalAll + ' lead' + (totalAll === 1 ? '' : 's');
                if (hasFilter) badge += ' <span class="kb-filter-active-dot" title="Filters active"></span>';
                $('#kbTotalBadge').html(badge);
            });

            // Load each column independently (parallel)
            const stages = [
                { key: 'New', status: 0 },
                { key: 'Contacted', status: 1 },
                { key: 'Qualified', status: 2 },
                { key: 'Proposal', status: 3 },
                { key: 'Closed', status: 5 },
                { key: 'Lost', status: 9 },
            ];
            stages.forEach(function (s) {
                colPages[s.key] = 1;
                loadStage(s.key, s.status, false);
            });
        }

        $(document).ready(function () {
            loadIndustryOptions();
            initBoard();

            // Refresh button
            $('#kbRefreshBtn').on('click', function () {
                $(this).find('i').addClass('bx-spin');
                setTimeout(() => $(this).find('i').removeClass('bx-spin'), 1200);
                initBoard();
            });

            // Apply filters
            $('#kbApplyFilter').on('click', function () { initBoard(); });
            $('#kbSearch').on('keydown', function (e) {
                if (e.key === 'Enter') initBoard();
            });

            // Reset filters
            $('#kbResetFilter').on('click', function () {
                $('#kbSearch').val('');
                $('#kbAssigned').val('');
                $('#kbDateFrom').val('');
                $('#kbDateTo').val('');
                $('#kbIndustry').val('');
                initBoard();
            });
        });

        /* ── Kanban Card Double-Click → Lead Details Popup ── */
        var kbUserMap = {!! json_encode($getUsers->pluck('name', 'id')) !!};

        var kbStatusLabels = { 0: 'New', 1: 'Contacted', 2: 'Qualified', 3: 'Proposal Sent', 5: 'Closed (Won)', 9: 'Lost' };
        var kbStatusColors = { 0: '#5f6368', 1: '#f29900', 2: '#7c3aed', 3: '#006666', 5: '#34a853', 9: '#ea4335' };

        $(document).on('dblclick', '.kb-card', function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            if (!id) return;

            // Reset to Info tab
            kbTab($('.ld-tab').first()[0], 'kb-tab-info');
            $('#kb_timeline').html('<p class="text-muted text-center" style="font-size:0.82rem;">Loading…</p>');

            // Open modal immediately
            var modal = new bootstrap.Modal(document.getElementById('kbLeadModal'));
            modal.show();

            $.get('/get-lead-details/' + id, function (data) {
                $('#kb_card_id').val(id);
                window._activeLeadId = id; // expose for WA message template
                var l = data.lead;
                var loc = {};
                try { loc = JSON.parse(l.location) || {}; } catch (e) { }

                // Header
                $('#kb_leadAvatar').text((l.name || 'L').charAt(0).toUpperCase());
                $('#kb_leadName').text(l.name || '—');
                $('#kb_leadCompany').text(l.company || '—');

                // Added on date
                var addDate = l.created_at ? new Date(l.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
                $('#kb_leadSince').text(addDate);

                var sl = kbStatusLabels[l.status] || 'New';
                var sc = kbStatusColors[l.status] || '#5f6368';
                $('#kb_leadStatus').text(sl).css({ 'background': sc, 'color': '#ffffff', 'border-color': sc });

                $('#kb_btnCall').attr('href', l.mob ? 'tel:+' + l.mob : '#');
                $('#kb_btnWa').attr('href', l.whatsapp ? 'https://wa.me/' + l.whatsapp : '#');
                $('#kb_btnMail').attr('href', l.email ? 'mailto:' + l.email : '#');

                // Info cards
                $('#kb_mob').text(l.mob ? '+' + l.mob : '—');
                $('#kb_wa').text(l.whatsapp ? '+' + l.whatsapp : '—');
                $('#kb_email').text(l.email || '—');
                $('#kb_company_val').text(l.company || '—');
                $('#kb_position').text(l.position || '—');
                $('#kb_industry').text(l.industry || '—');

                $('#kb_purpose').text(l.purpose || '—');
                $('#kb_value').text(l.values ? '₹' + Number(l.values).toLocaleString('en-IN') : '—');
                $('#kb_poc').text(l.poc || '—');
                $('#kb_assigned').text(kbUserMap[l.assigned] || l.assigned || '—');
                $('#kb_tags').text(l.tags || '—');

                // Score and Duplicate mapping
                let scoreHtml = '—';
                if (l.score !== null && l.score !== undefined && l.score !== '') {
                    let scoreColor = l.score >= 70 ? 'success' : (l.score >= 40 ? 'warning' : 'danger');
                    scoreHtml = '<span class="badge bg-' + scoreColor + ' text-white"><i class="bx bxs-star me-1"></i>' + l.score + '</span>';
                }
                $('#kb_score').html(scoreHtml);

                let isDup = (l.is_duplicate == 1 || String(l.is_duplicate) === 'true' || String(l.is_duplicate) === '1');
                $('#kb_duplicate').html(isDup ? '<span class="badge bg-danger text-white"><i class="bx bx-error me-1"></i>Yes</span>' : '<span class="badge bg-success text-white"><i class="bx bx-check me-1"></i>No</span>');

                // Location combined
                $('#kb_location_val').text([loc.address, loc.city, loc.state, loc.zip, loc.country].filter(Boolean).join(', ') || '—');

                // Edit button
                $('#kb_editBtn').attr('href', '/manage-lead?id=' + id + '&from=kanban');

                // Pre-select quick assign
                $('#kb_quick_assign').val(l.assigned || '');
                $('#kb_assignMsg').html('');

                // Proposals
                var propHtml = '';
                (l.proposals || []).forEach(function (p) {
                    propHtml += `<tr>
                            <td>#${p.id}</td>
                            <td>${p.subject || '—'}</td>
                            <td>₹${Number(p.total).toLocaleString('en-IN')}</td>
                            <td><span class="badge bg-info">${p.status || 'Draft'}</span></td>
                        </tr>`;
                });
                $('#kb_proposals').html(propHtml || '<tr><td colspan="4" class="text-center text-muted py-3">No proposals found.</td></tr>');

                // Conversations timeline
                var html = '';
                (data.comments || []).forEach(function (c) {
                    html += '<div class="ld-timeline-item">'
                        + '<div class="ld-tl-dot"></div>'
                        + '<div class="ld-tl-body">'
                        + '<div class="ld-tl-meta">' + (c.next_date || c.created_at) + '</div>'
                        + '<p class="ld-tl-msg">' + c.msg + '</p>'
                        + '</div></div>';
                });
                $('#kb_timeline').html(html || '<p class="text-muted text-center py-3" style="font-size:0.82rem;">No conversations yet.</p>');

                // ── WhatsApp Template Auto-load ──
                var defaultMsg = '🚀 *Grow Your Business with Our Digital Solutions*\n\n✅ Website Design & Development\n✅ ERP & CRM Solutions\n✅ Mobile App Development\n✅ SEO & Digital Growth Services\n\n🎁 *FREE with Our Services (Limited-Time Value Add):*\n🔹 SMS Pilot – Reach your customers instantly with promotional & transactional SMS\n🔹 Digital Visiting Card – Share your professional profile anytime, anywhere with one click\n🔹 Sales Lead Management – Track, manage, and convert leads more efficiently\n\n📞 *Call / WhatsApp:*\n+91 95945 45556 | +91 96197 75533\n\n🌐 *Learn more:*\nhttps://webbrella.com/website-design-and-development';
                var savedMsg = localStorage.getItem('wa_msg_lead_' + l.id) || defaultMsg;
                $('#kb_waMessageTextTabbed').val(savedMsg);

                // Reset save button state
                $('#kb_saveWpTemplateBtn').html('<i class="bx bx-bookmark"></i> Save Template').prop('disabled', false);
                $('#kb_wpTemplateStatusMsg').hide();
            });
        });

        // Quick Assign in Kanban Modal
        $(document).on('click', '#kb_quickAssignBtn', function () {
            var leadId = $('#kb_card_id').val(); // I'll need to set this ID when modal opens
            var salesId = $('#kb_quick_assign').val();
            if (!salesId) { $('#kb_assignMsg').html('<span class="text-danger">Select a salesperson</span>'); return; }

            $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Assigning...');

            $.post("{{ route('leads.bulkAssign') }}", {
                _token: CSRF_TOKEN,
                lead_ids: [leadId],
                assigned_to: salesId
            }, function (res) {
                $('#kb_assignMsg').html('<span class="text-success"><i class="bx bx-check"></i> ' + (res.message || 'Assigned!') + '</span>');
                initBoard(); // reload columns
            }).always(() => {
                $(this).prop('disabled', false).html('<i class="bx bx-check-circle"></i> Assign Lead');
            });
        });

        function kbTab(btn, tabId) {
            $('.ld-tab').removeClass('active');
            $(btn).addClass('active');
            $('#kb-tab-info, #kb-tab-conv, #kb-tab-props, #kb-tab-assign, #kb-tab-wp-template').hide();
            $('#' + tabId).show();
        }

        // ── WhatsApp Template Save (Kanban) ──
        $(document).on('click', '#kb_saveWpTemplateBtn', function() {
            let leadId = window._activeLeadId || $('#kb_card_id').val();
            let text   = $('#kb_waMessageTextTabbed').val().trim();
            if (!leadId) {
                alert('Cannot save — lead ID not found.'); return;
            }
            if (text) {
                localStorage.setItem('wa_msg_lead_' + leadId, text);
                $('#kb_wpTemplateStatusMsg').show();
                $(this).html('<i class="bx bx-check"></i> Saved!').prop('disabled', true);
                setTimeout(() => {
                    $('#kb_wpTemplateStatusMsg').hide();
                    $('#kb_saveWpTemplateBtn').html('<i class="bx bx-bookmark"></i> Save Template').prop('disabled', false);
                }, 2000);
            } else {
                localStorage.removeItem('wa_msg_lead_' + leadId);
                $('#kb_wpTemplateStatusMsg').hide();
            }
        });

        // ── WhatsApp Template Send (Kanban) ──
        $(document).on('click', '#kb_sendWpTemplateBtn', function() {
            let leadId = window._activeLeadId || $('#kb_card_id').val();
            // Try to get number from the header WA button href, or use stored lead data
            let waHref = $('#kb_btnWa').attr('href') || '';
            let number = '';
            if (waHref && waHref.includes('wa.me')) {
                try { number = new URL(waHref).pathname.replace('/', ''); } catch(_) { number = waHref.split('wa.me/')[1] || ''; }
            }
            let text = $('#kb_waMessageTextTabbed').val().trim();
            if (number) {
                number = number.replace(/\D/g, '');
                let url = 'https://wa.me/' + number + (text ? '?text=' + encodeURIComponent(text) : '');
                window.open(url, '_blank');
            } else {
                alert('No valid WhatsApp number found for this lead.');
            }
        });

        // ── Direct WhatsApp Send from Kanban Card Button (no popup) ──
        var _kbDefaultTemplate = '🚀 *Grow Your Business with Our Digital Solutions*\n\n✅ Website Design & Development\n✅ ERP & CRM Solutions\n✅ Mobile App Development\n✅ SEO & Digital Growth Services\n\n🎁 *FREE with Our Services (Limited-Time Value Add):*\n🔹 SMS Pilot – Reach your customers instantly with promotional & transactional SMS\n🔹 Digital Visiting Card – Share your professional profile anytime, anywhere with one click\n🔹 Sales Lead Management – Track, manage, and convert leads more efficiently\n\n📞 *Call / WhatsApp:*\n+91 95945 45556 | +91 96197 75533\n\n🌐 *Learn more:*\nhttps://webbrella.com/website-design-and-development';

        function directSendKbWa(el) {
            let number = String($(el).data('wa') || '').replace(/\D/g, '');
            let leadId = $(el).data('lead-id') || '';
            if (!number) { alert('No valid WhatsApp number found for this lead.'); return; }

            // Load saved template or default
            let text = (leadId ? localStorage.getItem('wa_msg_lead_' + leadId) : null) || _kbDefaultTemplate;
            let url = 'https://wa.me/' + number + '?text=' + encodeURIComponent(text);
            window.open(url, '_blank');
        }

        // ── Direct WhatsApp Send from Kanban Modal Header Button ──
        function directSendKbWaFromModal() {
            let waHref = $('#kb_btnWa').attr('href') || '';
            let number = '';
            if (waHref && waHref.includes('wa.me')) {
                try { number = new URL(waHref).pathname.replace('/', ''); } catch(_) { number = waHref.split('wa.me/')[1] || ''; }
            }
            number = String(number).replace(/\D/g, '');
            if (!number) { alert('No valid WhatsApp number found for this lead.'); return; }

            let leadId = window._activeLeadId || $('#kb_card_id').val() || '';
            let text = (leadId ? localStorage.getItem('wa_msg_lead_' + leadId) : null) || _kbDefaultTemplate;
            let url = 'https://wa.me/' + number + '?text=' + encodeURIComponent(text);
            window.open(url, '_blank');
        }

        /* ── Drag & Drop ─────────────────────────────────────────── */
        function allowDrop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).addClass('kb-drag-over');
        }
        function dragLeave(ev) {
            $(ev.currentTarget).removeClass('kb-drag-over');
        }
        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.dataTransfer.setData("leadId", $(ev.target).data('id'));
        }
        function drop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).removeClass('kb-drag-over');

            const elemId = ev.dataTransfer.getData("text");
            const leadId = ev.dataTransfer.getData("leadId");
            const newStage = $(ev.currentTarget).data('stage');

            const container = $(ev.currentTarget).find('.kb-items')[0];
            const elem = document.getElementById(elemId);
            if (!elem || !container) return;
            container.appendChild(elem);

            const revMap = { 'New': 0, 'Contacted': 1, 'Qualified': 2, 'Proposal': 3, 'Closed': 5, 'Lost': 9 };
            updateLeadStage(leadId, revMap[newStage], newStage, elem);
        }
        function updateLeadStage(leadId, newStatus, newStage, elem) {
            // Optimistic: update card border color immediately
            const color = stageColors[newStage];
            if (color) $(elem).css('border-left-color', color.border);

            $.post("{{ route('leads.update_status') }}", {
                _token: CSRF_TOKEN,
                id: leadId,
                status: newStatus
            }).fail(function () {
                alert('Error updating lead status. Refreshing...');
                initBoard();
            });
        }
    </script>

@endsection