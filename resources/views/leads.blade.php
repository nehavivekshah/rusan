@extends('layout')
@section('title', 'Leads Management - Rusan')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = is_array($roles->permissions ?? '') ? $roles->permissions : explode(',', (string) ($roles->permissions ?? ''));
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/css/lead-panel.css') }}">

    <style>
        .bg-alert {
            background-color: #fff1f1 !important;
            border-left: 5px solid #dc3545 !important;
        }

        #leadslists tbody tr {
            cursor: pointer;
        }

        .section-divider {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #888;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin: 15px 0;
        }

        .form-label {
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .timeline-box {
            max-height: 450px;
            overflow-y: auto;
            border-left: 2px solid #eee;
            padding-left: 20px;
        }

        /* ─── Bulk Select ─── */
        .lead-cb {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #006666;
        }

        #leadslists tbody tr.selected-row {
            background: rgba(0, 102, 102, 0.06) !important;
        }

        /* ─── Floating Bulk Action Bar ─── */
        #bulkActionBar {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            opacity: 0;
            transition: transform 0.28s cubic-bezier(.4, 0, .2, 1), opacity 0.22s;
            z-index: 9999;
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.16);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 420px;
            pointer-events: none;
        }

        #bulkActionBar.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: all;
        }

        #bulkSelCount {
            font-size: 0.82rem;
            font-weight: 700;
            color: #006666;
            background: #e6f4f0;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        #bulkSalesSelect {
            flex: 1;
            font-size: 0.82rem;
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 6px 10px;
            color: #202124;
            outline: none;
        }

        #bulkAssignBtn {
            background: #006666;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 18px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.18s;
        }

        #bulkAssignBtn:hover {
            background: #004d4d;
        }

        #bulkClearBtn {
            background: none;
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.80rem;
            color: #5f6368;
            cursor: pointer;
        }

        @media (max-width:767px) {
            .mob-style {
                flex-wrap: wrap;
                gap: 13px !important;
            }

            .input-group {
                min-width: 100% !important;
            }

            #leadslists_previous {
                display: none;
            }

            #bulkActionBar {
                min-width: 90vw;
                flex-wrap: wrap;
                bottom: 14px;
            }
        }
    </style>

    <section class="task__section">
        @include('inc.header', ['title' => 'Leads Board'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                {{-- Left: Filters --}}
                <div class="leads-toolbar-left">
                    @if(in_array('All', $roleArray))
                        <select id="ajaxSalesRep" class="lb-select">
                            <option value="">All Sales Reps</option>
                            @foreach($getUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select id="ajaxStatus" class="lb-select">
                        <option value="">All Status</option>
                        <option value="0">🟢 Fresh</option>
                        <option value="1">🟡 Follow Up</option>
                        <option value="2">🟣 Qualified</option>
                        <option value="3">🟠 Proposal Sent</option>
                        <option value="5">🔵 Closed (Won)</option>
                        <option value="9">🔴 Loss</option>
                    </select>
                    <input type="text" id="ajaxTags" class="lb-select" placeholder="Filter by Tags"
                        style="width: auto; max-width: 120px; padding: 4px 8px;">
                    <select id="ajaxIndustry" class="lb-select">
                        <option value="">All Industries</option>
                    </select>
                    <button class="lb-icon-btn" id="refreshBtn" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>

                {{-- Right: Actions --}}
                <div class="leads-toolbar-right">
                    @if(in_array('leads_import', $roleArray) || in_array('All', $roleArray))
                        <a href="javascript:void(0)" class="lb-btn lb-btn-secondary" id="importFile">
                            <i class="bx bx-upload"></i>
                            <span class="d-none d-sm-inline">Import</span>
                        </a>
                        <a href="{{ route('exportLeads') }}" class="lb-btn lb-btn-ghost" title="Export Leads to CSV">
                            <i class="bx bx-export"></i>
                            <span class="d-none d-sm-inline">Export</span>
                        </a>
                        <a href="{{ asset('assets/leads.csv') }}" class="lb-btn lb-btn-ghost" target="_blank"
                            download="leads.csv" title="Download CSV Sample">
                            <i class="bx bx-download"></i>
                            <span class="d-none d-sm-inline">Sample</span>
                        </a>
                    @endif
                    <a href="{{ route('leads.kanban') }}" class="lb-btn lb-btn-ghost" title="Switch to Kanban View">
                        <i class="bx bx-layout"></i>
                        <span class="d-none d-sm-inline">Kanban</span>
                    </a>
                    @if(in_array('leads_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-lead" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span>Add Lead</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Leads Table --}}
            <div class="dash-card">
                <div class="table-responsive">
                    <table id="leadslists" class="leads-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="p-0" style="width:36px;"><input type="checkbox" class="lead-cb"
                                        id="selectAllLeads" title="Select all"></th>
                                <th>Name</th>
                                <th class="m-none">Company</th>
                                <th class="m-none mw80">Mobile</th>
                                <th class="m-none mw60">Status</th>
                                <th class="m-none mw80">Since</th>
                                <th class="m-none mw80">Purpose</th>
                                <th class="m-none mw60">Value</th>
                                <th class="m-none mw70">Last Talk</th>
                                <th class="m-none mw150">Next Move</th>
                                @if(in_array('All', $roleArray))
                                    <th class="m-none mw60">Assigned</th>
                                @else
                                    <th class="m-none mw60">POC</th>
                                @endif
                                <th class="text-center" width="60px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ─── Floating Bulk Action Bar ─── --}}
        <div id="bulkActionBar">
            <span id="bulkSelCount">0 selected</span>
            <select id="bulkSalesSelect">
                <option value="">— Assign to Salesperson —</option>
                @foreach($getUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <button id="bulkAssignBtn"><i class="bx bx-user-check"></i> Assign</button>
            <button id="bulkClearBtn">✕ Clear</button>
        </div>

    </section>

    <!-- ═══════════════════════════════════════════════════════════
                                         LEAD DETAILS MODAL — Contract-Style Popup
                                    ════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width:900px;">
            <div class="modal-content" style="border-radius:16px; border:none; overflow:hidden;">

                <!-- ── Header (matching cf-modal-header) ── -->
                <div class="ld-header">
                    <div class="ld-header-content">
                        <div class="d-flex align-items-center gap-3 flex-1 min-w-0">
                            <div class="ld-avatar" id="leadAvatarBadge">L</div>
                            <div class="min-w-0">
                                <p class="ld-name mb-0" id="ld_display_name">Lead Details</p>
                                <p class="ld-company mb-0" id="ld_display_company">—</p>
                                <p class="ld-company mb-0" style="margin-top:2px;">
                                    <i class="bx bx-calendar-plus me-1" style="font-size:0.7rem;"></i> Added on <span
                                        id="ld_display_since">—</span>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="ld-status-chip" id="ld_status_chip">Fresh</span>
                            <a class="ld-quick-btn" id="ld_btn_call" href="#" title="Call">
                                <i class="bx bx-phone"></i>
                            </a>
                            <a class="ld-quick-btn" id="ld_btn_wa" href="#" title="WhatsApp"
                                style="background:rgba(37,211,102,0.2);color:#25D366 !important;border-color:rgba(37,211,102,0.3);"
                                onclick="event.preventDefault(); directSendLeadWa();">
                                <i class="bx bxl-whatsapp"></i>
                            </a>
                            <a class="ld-quick-btn" id="ld_btn_mail" href="#" title="Email">
                                <i class="bx bx-envelope"></i>
                            </a>
                            <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal"
                                aria-label="Close" style="opacity:.8;"></button>
                        </div>
                    </div>
                </div>

                <!-- ── Segmented Control Tabs ── -->
                <div class="ld-tab-nav">
                    <button class="ld-tab active" onclick="ldShowTab('tab-profile', this)">
                        <i class="bx bx-user-circle"></i> Profile
                    </button>
                    <button class="ld-tab" onclick="ldShowTab('tab-comments', this)">
                        <i class="bx bx-message-detail"></i> Timeline
                    </button>
                    <button class="ld-tab" onclick="ldShowTab('tab-porposal', this)">
                        <i class="bx bx-file"></i> Proposals
                    </button>
                    <button class="ld-tab" onclick="ldShowTab('tab-assign', this)">
                        <i class="bx bx-user-plus"></i> Assign
                    </button>
                    <button class="ld-tab" onclick="ldShowTab('tab-wp-template', this)">
                        <i class="bx bxl-whatsapp"></i> Wp Template
                    </button>
                </div>

                <!-- ── Tab Content ── -->
                <div class="modal-body ld-body">
                    <div class="tab-content h-100">

                        <!-- ══ PROFILE TAB ══ -->
                        <div class="ld-tab-pane" id="tab-profile" style="display:block;">
                            <div class="ld-scroll">

                                <!-- View Mode -->
                                <div id="ld-view-mode">
                                    <!-- Info Cards Grid -->
                                    <div class="ld-info-grid">
                                        <!-- Contact Card -->
                                        <div class="ld-info-card">
                                            <div class="ld-info-card-header"><i class="bx bx-phone-call"></i> Contact
                                                Details</div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-mobile-alt"></i> Mobile</span>
                                                <span class="ld-info-val" id="v_mob">—</span>
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bxl-whatsapp"></i> WhatsApp</span>
                                                <span class="ld-info-val" id="v_whatsapp">—</span>
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-envelope"></i> Email</span>
                                                <span class="ld-info-val" id="v_email">—</span>
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-world"></i> Language</span>
                                                <span class="ld-info-val" id="v_language">—</span>
                                            </div>
                                        </div>

                                        <!-- Business Card -->
                                        <div class="ld-info-card">
                                            <div class="ld-info-card-header"><i class="bx bx-buildings"></i> Business Info
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-briefcase"></i> Company</span>
                                                <span class="ld-info-val" id="v_company">—</span>
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-user-pin"></i> Position</span>
                                                <span class="ld-info-val" id="v_position">—</span>
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-trending-up"></i>
                                                    Industry</span>
                                                <span class="ld-info-val" id="v_industry">—</span>
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-id-card"></i> GST No.</span>
                                                <span class="ld-info-val" id="v_gstno">—</span>
                                            </div>
                                            <div class="ld-info-row">
                                                <span class="ld-info-label"><i class="bx bx-globe"></i> Website</span>
                                                <span class="ld-info-val" id="v_website">—</span>
                                            </div>
                                        </div>

                                        <!-- Intelligence Card (Full Width) -->
                                        <div class="ld-info-card" style="grid-column: 1 / -1;">
                                            <div class="ld-info-card-header"><i class="bx bx-brain"></i> CRM Intelligence
                                            </div>
                                            <div class="row g-0">
                                                <div class="col-md-6 pe-md-3" style="border-right:1.5px solid #f1f3f4;">
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-target-lock"></i>
                                                            Purpose</span>
                                                        <span class="ld-info-val" id="v_purpose">—</span>
                                                    </div>
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-rupee"></i> Lead
                                                            Value</span>
                                                        <span class="ld-info-val" id="v_value"
                                                            style="color:#34a853;font-weight:700;">—</span>
                                                    </div>
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-user-check"></i>
                                                            POC</span>
                                                        <span class="ld-info-val" id="v_poc">—</span>
                                                    </div>
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-star"></i> Lead
                                                            Score</span>
                                                        <span class="ld-info-val" id="v_score">—</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 ps-md-3">
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-user-pin"></i> Assigned
                                                            To</span>
                                                        <span class="ld-info-val" id="v_assigned"
                                                            style="color:#006666;font-weight:700;">—</span>
                                                    </div>
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-error"></i> Potential
                                                            Duplicate</span>
                                                        <span class="ld-info-val" id="v_duplicate">—</span>
                                                    </div>
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-purchase-tag-alt"></i>
                                                            Tags</span>
                                                        <span class="ld-info-val" id="v_tags">—</span>
                                                    </div>
                                                    <div class="ld-info-row">
                                                        <span class="ld-info-label"><i class="bx bx-map-pin"></i>
                                                            Location</span>
                                                        <span class="ld-info-val" id="v_address_full"
                                                            style="font-size:0.78rem;color:#5f6368;">—</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Bar -->
                                    @if(in_array('leads_edit', $roleArray) || in_array('leads_delete', $roleArray) || in_array('All', $roleArray))
                                        <div class="ld-action-bar">
                                            @if(in_array('leads_delete', $roleArray) || in_array('All', $roleArray))
                                                <button type="button" class="ld-btn ld-btn-danger leadDelete" id="leadDelete">
                                                    <i class="bx bx-trash"></i> Delete
                                                </button>
                                            @endif
                                            @if(in_array('leads_edit', $roleArray) || in_array('All', $roleArray))
                                                <button type="button" class="ld-btn ld-btn-primary" id="ld_edit_toggle">
                                                    <i class="bx bx-edit"></i> Edit Lead
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Edit Mode (hidden by default) -->
                                <div id="ld-edit-mode" style="display:none;">
                                    <div class="ld-edit-banner">
                                        <i class="bx bx-edit-alt"></i> Editing Lead
                                        <button type="button" class="ms-auto ld-btn ld-btn-ghost" id="ld_edit_cancel"
                                            style="padding:5px 12px;font-size:.78rem;">
                                            <i class="bx bx-x"></i> Cancel
                                        </button>
                                    </div>
                                    <form id="editLeadForm">
                                        @csrf
                                        <input type="hidden" id="m_id" name="id">
                                        <div class="row g-3 p-3" style="background:#f4fbfb;">

                                            <div class="ld-section-label col-12">Contact Information</div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Full Name <span
                                                        style="color:#ea4335;">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                                                    <input type="text" class="form-control" id="m_name" name="name"
                                                        placeholder="Full Name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                                    <input type="email" class="form-control" id="m_email" name="email"
                                                        placeholder="Email">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Mobile <span style="color:#ea4335;">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                                    <input type="text" class="form-control" id="m_mob" name="mob"
                                                        placeholder="91XXXXXXXXXX" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">WhatsApp</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                                    <input type="text" class="form-control" id="m_whatsapp" name="whatsapp"
                                                        placeholder="91XXXXXXXXXX">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Language</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-world"></i></span>
                                                    <input type="text" class="form-control" id="m_language" name="language"
                                                        placeholder="EN / HI">
                                                </div>
                                            </div>

                                            <div class="ld-section-label col-12">Business Details</div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Company</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-briefcase"></i></span>
                                                    <input type="text" class="form-control" id="m_company" name="company"
                                                        placeholder="Company">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Position</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-user-pin"></i></span>
                                                    <input type="text" class="form-control" id="m_position" name="position"
                                                        placeholder="e.g. Manager">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Industry</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-building"></i></span>
                                                    <input type="text" class="form-control" id="m_industry" name="industry"
                                                        placeholder="e.g. IT">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">GST No.</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                                    <input type="text" class="form-control" id="m_gstno" name="gstno"
                                                        placeholder="GSTIN">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Website</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-link"></i></span>
                                                    <input type="url" class="form-control" id="m_website" name="website"
                                                        placeholder="https://">
                                                </div>
                                            </div>

                                            <div class="ld-section-label col-12">Address</div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Street</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-home"></i></span>
                                                    <input type="text" class="form-control" id="m_address"
                                                        name="address[address]" placeholder="Street">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">City</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-map-alt"></i></span>
                                                    <input type="text" class="form-control" id="m_city" name="address[city]"
                                                        placeholder="City">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">State</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                                                    <input type="text" class="form-control" id="m_state"
                                                        name="address[state]" placeholder="State">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Country</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                                    <input type="text" class="form-control" id="m_country"
                                                        name="address[country]" placeholder="Country">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">ZIP</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-pin"></i></span>
                                                    <input type="text" class="form-control" id="m_zip" name="address[zip]"
                                                        placeholder="ZIP Code">
                                                </div>
                                            </div>

                                            <div class="ld-section-label col-12">CRM Intelligence</div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Purpose</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-target-lock"></i></span>
                                                    <input type="text" class="form-control" id="m_purpose" name="purpose"
                                                        placeholder="e.g. Sales">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Lead Value (₹)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-rupee"></i></span>
                                                    <input type="number" class="form-control" id="m_value" name="values"
                                                        placeholder="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">POC</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-user-check"></i></span>
                                                    <input type="text" class="form-control" id="m_poc" name="poc"
                                                        placeholder="Point of Contact">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Assign Salesperson</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-share-alt"></i></span>
                                                    <select class="form-select" id="m_assigned" name="assigned">
                                                        <option value="">— Select —</option>
                                                        @foreach($getUsers as $u)
                                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Tags</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i
                                                            class="bx bx-purchase-tag-alt"></i></span>
                                                    <input type="text" class="form-control" id="m_tags" name="tags"
                                                        placeholder="K2, Hot, VIP">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="ld-label">Status</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-list-check"></i></span>
                                                    <select name="status" id="m_status" class="form-select">
                                                        <option value="0">🔵 New / Fresh</option>
                                                        <option value="1">🟠 Contacted / Follow Up</option>
                                                        <option value="2">🟣 Qualified</option>
                                                        <option value="3">🟢 Proposal Sent</option>
                                                        <option value="5">✅ Closed (Won)</option>
                                                        <option value="9">❌ Lost</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Edit Footer (matching cf-modal-footer) -->
                                            <div class="col-12">
                                                <div class="ld-edit-footer">
                                                    <button type="reset" class="ld-btn ld-btn-ghost">
                                                        <i class="bx bx-reset"></i> Reset
                                                    </button>
                                                    <button type="submit" class="ld-btn ld-btn-primary">
                                                        <i class="bx bx-check"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ══ CONVERSATIONS TAB ══ -->
                        <div class="ld-tab-pane" id="tab-comments" style="display:none;">
                            <div class="ld-convo-wrap">
                                <!-- Timeline -->
                                <div class="ld-timeline-col">
                                    <div class="ld-timeline-head">
                                        <i class="bx bx-history"></i> History
                                    </div>
                                    <div id="commentHistory" class="ld-timeline"></div>
                                </div>
                                <!-- Add Note Form -->
                                <div class="ld-note-col">
                                    <div class="ld-note-head"><i class="bx bx-plus-circle"></i> Add Note</div>
                                    <form id="addCommentForm" class="ld-note-form">
                                        @csrf
                                        <input type="hidden" name="lead_id" id="c_lead_id">
                                        <div class="ld-note-field">
                                            <label class="ld-label">Message <span style="color:#ea4335;">*</span></label>
                                            <textarea name="msg" id="c_msg" rows="5"
                                                placeholder="Write a note about this conversation…" required></textarea>
                                        </div>
                                        <div class="ld-note-field">
                                            <label class="ld-label"><i class="bx bx-alarm"></i> Next Reminder <small
                                                    style="color:#9aa0a6;">(optional)</small></label>
                                            <input type="datetime-local" name="next_date" id="c_next_date">
                                        </div>
                                        <button type="submit" class="ld-btn ld-btn-primary w-100">
                                            <i class="bx bx-save"></i> Save Note
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ══ PROPOSALS TAB ══ -->
                        <div class="ld-tab-pane" id="tab-porposal" style="display:none;">
                            <div style="padding:20px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="ld-section-label" style="margin:0;border:none;padding:0;">Proposals</div>
                                    <a href="/manage-proposal" class="ld-btn ld-btn-primary"
                                        style="font-size:0.78rem;padding:6px 14px;">
                                        <i class="bx bx-plus"></i> New Proposal
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0"
                                        style="font-size:0.82rem;border-radius:10px;overflow:hidden;">
                                        <thead style="background:linear-gradient(135deg,#005757,#007e7e);">
                                            <tr>
                                                <th
                                                    style="color:#fff !important;font-weight:600;border:none;padding:10px 12px;">
                                                    #ID</th>
                                                <th
                                                    style="color:#fff !important;font-weight:600;border:none;padding:10px 12px;">
                                                    Subject</th>
                                                <th
                                                    style="color:#fff !important;font-weight:600;border:none;padding:10px 12px;">
                                                    Total</th>
                                                <th
                                                    style="color:#fff !important;font-weight:600;border:none;padding:10px 12px;">
                                                    Date</th>
                                                <th
                                                    style="color:#fff !important;font-weight:600;border:none;padding:10px 12px;">
                                                    Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="Proposals"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ══ ASSIGN TAB ══ -->
                        <div class="ld-tab-pane" id="tab-assign" style="display:none;">
                            <div style="padding:28px 20px;">
                                <div class="ld-assign-card">
                                    <div class="ld-assign-icon"><i class="bx bx-user-plus"></i></div>
                                    <h6 class="mb-1" style="font-weight:700;color:#202124;font-size:0.95rem;">Assign
                                        Salesperson</h6>
                                    <p style="font-size:0.78rem;color:#5f6368;margin-bottom:18px;">Re-assign this lead to a
                                        different salesperson instantly.</p>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                                        <select class="form-select" id="quick_assign_user">
                                            <option value="">— Select Salesperson —</option>
                                            @foreach($getUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" class="ld-btn ld-btn-primary w-100" id="quickAssignBtn">
                                        <i class="bx bx-check-circle"></i> Assign Now
                                    </button>
                                    <div id="quickAssignMsg" class="mt-2 text-center" style="font-size:0.82rem;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- ══ WP TEMPLATE TAB ══ -->
                        <div class="ld-tab-pane" id="tab-wp-template" style="display:none;">
                            <div style="padding:28px 20px;">
                                <div class="ld-assign-card">
                                    <div class="ld-assign-icon" style="background:rgba(37,211,102,0.1);color:#25d366;"><i
                                            class="bx bxl-whatsapp"></i></div>
                                    <h6 class="mb-1" style="font-weight:700;color:#202124;font-size:0.95rem;">WhatsApp
                                        Template</h6>
                                    <p style="font-size:0.78rem;color:#5f6368;margin-bottom:18px;">Customize the WhatsApp
                                        message for this lead.</p>

                                    <div class="form-group mb-3">
                                        <textarea id="waMessageTextTabbed" class="form-control" rows="6"
                                            placeholder="Hi, I wanted to follow up about..."
                                            style="border-radius:8px;border:1px solid #dadce0;padding:12px;font-size:0.93rem;resize:none;height:auto!important;"></textarea>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="ld-btn ld-btn-primary w-100" id="saveWpTemplateBtn">
                                            <i class="bx bx-bookmark"></i> Save Template
                                        </button>
                                        <button type="button" class="ld-btn ld-btn-primary w-100" id="sendWpTemplateBtn"
                                            style="background:#25d366;border-color:#25d366;">
                                            <i class="bx bx-send"></i> Open WhatsApp
                                        </button>
                                    </div>
                                    <div id="wpTemplateStatusMsg" class="mt-2 text-center"
                                        style="font-size:0.82rem;font-weight:600;color:#25D366;display:none;">
                                        <i class="bx bx-check-circle"></i> Template Saved!
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}
                </div>{{-- /modal-body --}}
            </div>{{-- /modal-content --}}
        </div>{{-- /modal-dialog --}}
    </div>{{-- /modal --}}

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // User ID → Name map (from server)
        var userMap = {!! json_encode($getUsers->pluck('name', 'id')) !!};

        $(document).ready(function () {
            // 1. Init DataTable
            var table = $('#leadslists').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,

                ajax: {
                    url: "{{ route('leads.index') }}",
                    data: function (d) {
                        d.status = $('#ajaxStatus').val();
                        d.assign_user = $('#ajaxSalesRep').val();
                        d.tags = $('#ajaxTags').val();
                        d.industry = $('#ajaxIndustry').val();
                    }
                },

                columns: [
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (id) {
                            return '<input type="checkbox" class="lead-cb lead-row-cb" data-id="' + id + '">';
                        }
                    },
                    { data: 'name' },
                    { data: 'company' },
                    { data: 'mobile' },
                    { data: 'status' },
                    { data: 'since' },
                    { data: 'purpose' },
                    { data: 'value' },
                    { data: 'last_talk' },
                    { data: 'next_move' },
                    { data: 'assigned' },
                    { data: 'action', orderable: false, searchable: false }
                ],

                columnDefs: [
                    { targets: 0, className: 'text-center', width: '36px' },
                    { targets: 1, className: 'mw150' },
                    { targets: 2, className: 'm-none' },
                    { targets: 3, className: 'm-none mw80' },
                    { targets: 4, className: 'm-none mw60' },
                    { targets: 5, className: 'm-none mw80' },
                    { targets: 6, className: 'm-none mw80' },
                    { targets: 7, className: 'm-none mw60' },
                    { targets: 8, className: 'm-none mw70 tm' },
                    { targets: 9, className: 'm-none mw150' },
                    { targets: 10, className: 'm-none mw60' },
                    { targets: 11, className: 'position-sticky end-0 bg-default mw60' }
                ],

                createdRow: function (row, data, dataIndex) {
                    if (data.row_class) $(row).addClass(data.row_class);
                    $(row).attr('data-id', data.id);
                }
            });

            // ─── Bulk Selection Logic ───────────────────────────────────────
            function getSelectedIds() {
                return $('.lead-row-cb:checked').map(function () {
                    return $(this).data('id');
                }).get();
            }

            function updateBulkBar() {
                var ids = getSelectedIds();
                var bar = $('#bulkActionBar');
                if (ids.length > 0) {
                    $('#bulkSelCount').text(ids.length + ' selected');
                    bar.addClass('show');
                } else {
                    bar.removeClass('show');
                }
            }

            // Select-all header checkbox
            $(document).on('change', '#selectAllLeads', function () {
                var checked = $(this).prop('checked');
                $('.lead-row-cb').prop('checked', checked);
                $('#leadslists tbody tr').toggleClass('selected-row', checked);
                updateBulkBar();
            });

            // Individual row checkboxes
            $(document).on('change', '.lead-row-cb', function () {
                $(this).closest('tr').toggleClass('selected-row', $(this).prop('checked'));
                var total = $('.lead-row-cb').length;
                var checked = $('.lead-row-cb:checked').length;
                $('#selectAllLeads').prop('indeterminate', checked > 0 && checked < total)
                    .prop('checked', checked === total && total > 0);
                updateBulkBar();
            });

            // Reset on table redraw
            table.on('draw', function () {
                $('#selectAllLeads').prop('checked', false).prop('indeterminate', false);
                updateBulkBar();
            });

            // Clear selection
            $('#bulkClearBtn').on('click', function () {
                $('.lead-row-cb, #selectAllLeads').prop('checked', false).prop('indeterminate', false);
                $('#leadslists tbody tr').removeClass('selected-row');
                updateBulkBar();
            });

            // Bulk Assign
            $('#bulkAssignBtn').on('click', function () {
                var ids = getSelectedIds();
                var salesId = $('#bulkSalesSelect').val();
                if (!ids.length) { return; }
                if (!salesId) { alert('Please select a salesperson first.'); return; }

                $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Assigning...');

                $.ajax({
                    url: "{{ route('leads.bulkAssign') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        lead_ids: ids,
                        assigned_to: salesId
                    },
                    success: function (res) {
                        // Toast-style feedback
                        var msg = res.message || 'Assigned successfully!';
                        alert(msg);
                        $('.lead-row-cb, #selectAllLeads').prop('checked', false).prop('indeterminate', false);
                        $('#leadslists tbody tr').removeClass('selected-row');
                        updateBulkBar();
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
                    },
                    complete: function () {
                        $('#bulkAssignBtn').prop('disabled', false).html('<i class="bx bx-user-check"></i> Assign');
                    }
                });
            });


            // 2. Filters & Refresh
            $('#importFile').click(function () { $('#impLeadFile').click(); });
            $('#impLeadFile').change(function () { if (this.value) $('#leadsubmit').submit(); });

            $('#ajaxSearch').keyup(function () { table.search($(this).val()).draw(); });
            $('#ajaxTags').keyup(function () { table.draw(); });
            $('#ajaxSalesRep, #ajaxStatus, #ajaxIndustry').on('change', function () { table.draw(); });
            $('#refreshBtn').click(function () { table.draw(); });

            // Industry Loader
            function loadIndustryOptions() {
                $.get('/get-lead-industries', function (res) {
                    var sel = $('#ajaxIndustry');
                    sel.find('option:not(:first)').remove();
                    (res.industries || []).forEach(function (ind) {
                        if (ind) sel.append('<option value="' + ind + '">' + ind + '</option>');
                    });
                });
            }
            loadIndustryOptions();



            // 4. Modal Open Function
            function openLeadModal(id) {
                if (!id) return;

                // Always reset to view mode on open
                $('#ld-view-mode').show();
                $('#ld-edit-mode').hide();

                $('#m_id').val(id); $('#c_lead_id').val(id);
                window._activeLeadId = id; // expose for WA message template

                $.get("/get-lead-details/" + id, function (data) {
                    var l = data.lead;
                    var location = {};
                    try { location = JSON.parse(l.location) || {}; } catch (e) { }

                    // ── Header Banner ──
                    var initials = (l.name || 'L').charAt(0).toUpperCase();
                    $('#leadAvatarBadge').text(initials);
                    $('#ld_display_name').text(l.name || '—');
                    $('#ld_display_company').text(l.company || '—');

                    // Added on date
                    var addDate = l.created_at ? new Date(l.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
                    $('#ld_display_since').text(addDate);

                    var statusLabels = { '0': 'Fresh', '1': 'Follow Up', '2': 'Qualified', '3': 'Proposal Sent', '5': 'Converted', '9': 'Loss' };
                    var statusColors = { '0': '#5f6368', '1': '#f9ab00', '2': '#673ab7', '3': '#00bcd4', '5': '#1e8e3e', '9': '#d93025' };
                    var sl = statusLabels[l.status] || 'Fresh';
                    var sc = statusColors[l.status] || '#5f6368';
                    $('#ld_status_chip').text(sl).css({ 'background': sc, 'color': '#ffffff', 'border-color': sc });

                    $('#ld_btn_call').attr('href', l.mob ? 'tel:+' + l.mob : '#');
                    $('#ld_btn_wa').attr('href', l.whatsapp ? 'https://wa.me/' + l.whatsapp : '#');
                    $('#ld_btn_mail').attr('href', l.email ? 'mailto:' + l.email : '#');

                    // ── View Mode Cards ──
                    $('#v_mob').text(l.mob ? '+' + l.mob : '—');
                    $('#v_whatsapp').text(l.whatsapp ? '+' + l.whatsapp : '—');
                    $('#v_email').text(l.email || '—');
                    $('#v_language').text(l.language || '—');
                    $('#v_company').text(l.company || '—');
                    $('#v_position').text(l.position || '—');
                    $('#v_industry').text(l.industry || '—');
                    $('#v_gstno').text(l.gstno || '—');
                    $('#v_website').html(l.website ? '<a href="' + l.website + '" target="_blank">' + l.website + '</a>' : '—');
                    $('#v_address_full').text([location.address, location.city, location.state, location.zip, location.country].filter(Boolean).join(', ') || '—');
                    $('#v_purpose').text(l.purpose || '—');
                    $('#v_value').text(l.values ? '₹' + Number(l.values).toLocaleString('en-IN') : '—');
                    $('#v_poc').text(l.poc || '—');
                    $('#v_assigned').text(userMap[l.assigned] || l.assigned || '—');
                    $('#v_tags').text(l.tags || '—');

                    // Score and Duplicate mapping
                    let scoreHtml = '—';
                    if (l.score !== null && l.score !== undefined && l.score !== '') {
                        let scoreColor = l.score >= 70 ? 'success' : (l.score >= 40 ? 'warning' : 'danger');
                        scoreHtml = '<span class="badge bg-' + scoreColor + ' text-white"><i class="bx bxs-star me-1"></i>' + l.score + '</span>';
                    }
                    $('#v_score').html(scoreHtml);

                    let isDup = (l.is_duplicate == 1 || String(l.is_duplicate) === 'true' || String(l.is_duplicate) === '1');
                    $('#v_duplicate').html(isDup ? '<span class="badge bg-danger text-white"><i class="bx bx-error me-1"></i>Yes</span>' : '<span class="badge bg-success text-white"><i class="bx bx-check me-1"></i>No</span>');

                    // ── Edit Form pre-fill ──
                    $('#m_name').val(l.name);
                    $('#m_email').val(l.email);
                    $('#m_mob').val(l.mob);
                    $('#m_whatsapp').val(l.whatsapp);
                    $('#m_company').val(l.company);
                    $('#m_position').val(l.position);
                    $('#m_industry').val(l.industry);
                    $('#m_gstno').val(l.gstno);
                    $('#m_address').val(location['address'] || '');
                    $('#m_city').val(location['city'] || '');
                    $('#m_state').val(location['state'] || '');
                    $('#m_country').val(location['country'] || '');
                    $('#m_zip').val(location['zip'] || '');
                    $('#m_website').val(l.website);
                    $('#m_language').val(l.language);
                    $('#m_purpose').val(l.purpose);
                    $('#m_value').val(l.values);
                    $('#m_assigned').val(l.assigned); // set dropdown by ID
                    $('#m_poc').val(l.poc);
                    $('#m_status').val(l.status);
                    $('#m_tags').val(l.tags);

                    // Pre-select quick assign tab
                    $('#quick_assign_user').val(l.assigned || '');
                    $('#quickAssignMsg').text('');

                    // ── Conversation Timeline ──
                    var html = '';
                    data.comments.forEach(function (c) {
                        html += '<div class="ld-timeline-item">'
                            + '<div class="ld-tl-dot"></div>'
                            + '<div class="ld-tl-body">'
                            + '<div class="ld-tl-meta">' + (c.next_date ? c.next_date : c.created_at) + '</div>'
                            + '<p class="ld-tl-msg">' + c.msg + '</p>'
                            + '</div></div>';
                    });
                    $('#commentHistory').html(html || '<p class="text-muted text-center p-4" style="font-size:0.82rem">No conversations yet.</p>');

                    // ── WhatsApp Template Auto-load ──
                    window._activeLeadId = l.id;
                    var defaultMsg = '🚀 *Grow Your Business with Our Digital Solutions*\n\n✅ Website Design & Development\n✅ ERP & CRM Solutions\n✅ Mobile App Development\n✅ SEO & Digital Growth Services\n\n🎁 *FREE with Our Services (Limited-Time Value Add):*\n🔹 SMS Pilot – Reach your customers instantly with promotional & transactional SMS\n🔹 Digital Visiting Card – Share your professional profile anytime, anywhere with one click\n🔹 Sales Lead Management – Track, manage, and convert leads more efficiently\n\n📞 *Call / WhatsApp:*\n+91 95945 45556 | +91 96197 75533\n\n🌐 *Learn more:*\nhttps://webbrella.com/website-design-and-development';
                    var savedMsg = localStorage.getItem('wa_msg_lead_' + l.id) || defaultMsg;
                    $('#waMessageTextTabbed').val(savedMsg);

                    // Reset save button state
                    $('#saveWpTemplateBtn').html('<i class="bx bx-bookmark"></i> Save Template').prop('disabled', false);
                    $('#wpTemplateStatusMsg').hide();

                    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('leadModal'));
                    modal.show();
                });
            }

            // Handle URL param ?id=X to auto-open modal
            const urlParams = new URLSearchParams(window.location.search);
            const urlLeadId = urlParams.get('id');
            if (urlLeadId) {
                openLeadModal(urlLeadId);
            }

            // Row Click
            $(document).on('click', '#leadslists tbody tr', function (e) {
                // Prevent modal opening when clicking the checkbox or bulk-action elements
                if ($(e.target).closest('input.lead-row-cb, input.lead-cb, a, button, select').length) return;
                var id = $(this).attr('data-id');
                openLeadModal(id);
            });



            // ── Edit / Cancel Toggle ──
            $(document).on('click', '#ld_edit_toggle', function () {
                $('#ld-view-mode').hide();
                $('#ld-edit-mode').show();
            });
            $(document).on('click', '#ld_edit_cancel', function () {
                $('#ld-edit-mode').hide();
                $('#ld-view-mode').show();
            });

            // ── Quick Assign (Assign Tab) ──
            $(document).on('click', '#quickAssignBtn', function () {
                var leadId = $('#m_id').val();
                var userId = $('#quick_assign_user').val();
                if (!userId) { $('#quickAssignMsg').html('<span class="text-danger">Please select a salesperson.</span>'); return; }

                $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Assigning...');

                $.ajax({
                    url: "{{ route('leads.bulkAssign') }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', lead_ids: [leadId], assigned_to: userId },
                    success: function (res) {
                        $('#quickAssignMsg').html('<span class="text-success"><i class="bx bx-check"></i> ' + res.message + '</span>');
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        $('#quickAssignMsg').html('<span class="text-danger">' + (xhr.responseJSON?.message || 'Error') + '</span>');
                    },
                    complete: function () {
                        $('#quickAssignBtn').prop('disabled', false).html('<i class="bx bx-check-circle"></i> Assign Now');
                    }
                });
            });

            // 6. Submit Forms
            $('#editLeadForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('leads.update') }}", $(this).serialize(), function (res) {
                    alert(res.message || 'Profile Updated');
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('leadModal')).hide();
                    table.ajax.reload(null, false);
                }).fail(function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
                });
            });

            $('#addCommentForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('leads.storeComment') }}", $(this).serialize(), function () {
                    alert('Comment Saved');
                    $('#addCommentForm')[0].reset();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('leadModal')).hide();
                    table.ajax.reload(null, false);
                });
            });

            $('#leadDelete').on('click', function () {
                if (confirm('Are you sure you want to delete this lead?')) {
                    let id = $('#m_id').val();

                    $.post("/delete-lead", {
                        _token: "{{ csrf_token() }}",
                        id: id
                    }, function (res) {
                        alert('Lead deleted successfully');
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('leadModal')).hide();
                        $('#leadslists').DataTable().ajax.reload(null, false);
                    }).fail(function (xhr) {
                        alert('Error: ' + xhr.statusText);
                    });
                }
            });
        });
        /* ── Tab Switcher for Lead Details Panel ────────────────── */
        function ldShowTab(tabId, btnEl) {
            // Hide all panes
            document.querySelectorAll('#leadModal .ld-tab-pane').forEach(function (p) {
                p.style.display = 'none';
            });
            // Show target pane
            var pane = document.getElementById(tabId);
            if (pane) pane.style.display = 'block';

            // Update active tab button
            document.querySelectorAll('#leadModal .ld-tab').forEach(function (b) {
                b.classList.remove('active');
            });
            if (btnEl) btnEl.classList.add('active');
        }

        // Reset to Profile tab every time the modal opens
        document.getElementById('leadModal').addEventListener('show.bs.modal', function () {
            var firstBtn = this.querySelector('.ld-tab-nav .ld-tab');
            ldShowTab('tab-profile', firstBtn);
            // Also reset edit mode back to view mode
            var viewMode = document.getElementById('ld-view-mode');
            var editMode = document.getElementById('ld-edit-mode');
            if (viewMode) viewMode.style.display = '';
            if (editMode) editMode.style.display = 'none';
        });

        // ── Direct WhatsApp Send from Leads List Modal (no popup) ──
        var _ldDefaultTemplate = '🚀 *Grow Your Business with Our Digital Solutions*\n\n✅ Website Design & Development\n✅ ERP & CRM Solutions\n✅ Mobile App Development\n✅ SEO & Digital Growth Services\n\n🎁 *FREE with Our Services (Limited-Time Value Add):*\n🔹 SMS Pilot – Reach your customers instantly with promotional & transactional SMS\n🔹 Digital Visiting Card – Share your professional profile anytime, anywhere with one click\n🔹 Sales Lead Management – Track, manage, and convert leads more efficiently\n\n📞 *Call / WhatsApp:*\n+91 95945 45556 | +91 96197 75533\n\n🌐 *Learn more:*\nhttps://webbrella.com/website-design-and-development';

        function directSendLeadWa() {
            var waHref = $('#ld_btn_wa').attr('href') || '';
            var number = '';
            if (waHref && waHref.includes('wa.me')) {
                try { number = new URL(waHref).pathname.replace('/', ''); } catch(_) { number = waHref.split('wa.me/')[1] || ''; }
            }
            number = String(number).replace(/\D/g, '');
            if (!number) { alert('No valid WhatsApp number found for this lead.'); return; }

            var leadId = window._activeLeadId || $('#m_id').val() || $('#c_lead_id').val() || '';
            var text = (leadId ? localStorage.getItem('wa_msg_lead_' + leadId) : null) || _ldDefaultTemplate;
            var url = 'https://wa.me/' + number + '?text=' + encodeURIComponent(text);
            window.open(url, '_blank');
        }
    </script>

    {{-- Hidden form for CSV import (required by #importFile button handler) --}}
    <form id="leadsubmit" action="/import-leads-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impLeadFile" id="impLeadFile" accept=".csv, .xls" style="display:none;" />
    </form>

@endsection
