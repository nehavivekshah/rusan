@extends('layout')
@section('title', 'Manage Customer - Rusan')

@section('content')

    @php
        $location = json_decode(($clients->location ?? '[]'), true);
        $isEdit   = !empty($_GET['id']);
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => $isEdit ? 'Edit Customer' : 'New Customer'])

        <div class="dash-container">

            {{-- Page Top Bar --}}
            <div class="ml-page-topbar mb-4">
                <div class="ml-page-topbar-left">
                    <a href="/clients" class="ml-back-btn" title="Back to Customers">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                    <div>
                        <h1 class="ml-page-title">
                            {{ $isEdit ? 'Edit Customer Details' : 'Add New Customer' }}
                        </h1>
                        <p class="ml-page-subtitle">
                            {{ $isEdit ? 'Update the customer information below.' : 'Fill in the details to create a new customer.' }}
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($isEdit)
                        <a href="/manage-proposal?id=&lead_id={{ $clients->id }}&related=2" class="lb-btn lb-btn-ghost">
                            <i class="bx bx-file-blank"></i>
                            <span class="d-none d-sm-inline">Proposal</span>
                        </a>
                        <a href="/manage-invoice?id=&client_id={{ $clients->id }}" class="lb-btn lb-btn-ghost" style="color:#34a853;">
                            <i class="bx bx-receipt"></i>
                            <span class="d-none d-sm-inline">Invoice</span>
                        </a>
                        <a href="/task" class="lb-btn lb-btn-ghost" style="color:#f29900;">
                            <i class="bx bx-task"></i>
                            <span class="d-none d-sm-inline">Add Task</span>
                        </a>
                        <div class="ml-lead-badge">
                            <i class="bx bx-edit-alt"></i> Editing
                        </div>
                        <button type="submit" form="profileForm" class="lb-btn lb-btn-primary ms-2 shadow-sm">
                            <i class="bx bx-check-circle"></i> Update
                        </button>
                    @else
                        <div class="ml-lead-badge ml-lead-badge-new">
                            <i class="bx bx-plus-circle"></i> New Entry
                        </div>
                        <button type="submit" form="profileForm" class="lb-btn lb-btn-primary ms-2 shadow-sm">
                            <i class="bx bx-check-circle"></i> Save Customer
                        </button>
                    @endif
                </div>
            </div>

            {{-- Tab Nav (Edit mode only) --}}
            @if($isEdit)
                <!-- <div class="d-flex justify-content-center justify-content-lg-start">
                    <ul class="nav nav-pills custom-dash-tabs mb-4" id="clientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#profile" type="button">
                            <i class="bx bx-user me-1"></i> Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#interactions" type="button">
                            <i class="bx bx-history me-1"></i> Interactions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#journey" type="button">
                            <i class="bx bx-map-alt me-1"></i> Journey
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#documents" type="button">
                            <i class="bx bx-file me-1"></i> Documents
                        </button>
                    </li>
                    </ul>
                </div> -->
            @endif

            <div class="tab-content" id="clientTabsContent">

                @if ($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            if (typeof Swal !== 'undefined') {
                                let errorMessages = {!! json_encode(implode('<br>', $errors->all())) !!};
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.onmouseenter = Swal.stopTimer;
                                        toast.onmouseleave = Swal.resumeTimer;
                                    }
                                });
                                Toast.fire({
                                    icon: 'warning',
                                    title: "Validation Error",
                                    html: errorMessages
                                });
                            }
                        });
                    </script>
                @endif

                {{-- ── PROFILE TAB ── --}}
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <form action="manage-client" method="post" class="row g-4" id="profileForm">
                        @csrf
                        <input type="hidden" name="id" value="{{ $clients->id ?? '' }}">
                        <input type="hidden" name="previous_url" value="{{ $previous_url ?? url()->previous() }}">

                        {{-- Primary Information --}}
                        <div class="col-lg-6">
                            <div class="ml-card h-100">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                        <i class="bx bx-user"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Primary Information</h6>
                                        <span class="ml-card-sub">Contact identity & numbers</span>
                                    </div>
                                </div>
                                <div class="ml-card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="ml-label">Full Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    placeholder="Full Name" value="{{ $clients->name ?? '' }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Email Address <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    placeholder="email@example.com" value="{{ $clients->email ?? '' }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Mobile Number <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                                <input type="text" class="form-control" id="mob" name="mob"
                                                    placeholder="91XXXXXXXXXX" value="{{ $clients->mob ?? '91' }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Alternative Mobile</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                                <input type="text" class="form-control" id="alterMob" name="alterMob"
                                                    placeholder="91XXXXXXXXXX" value="{{ $clients->alterMob ?? '91' }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="ml-label">WhatsApp</label>
                                            <div class="input-group">
                                                <span class="input-group-text" style="color:#25d366; background: #eafeea; border-color: #c3e6cb;"><i class="bx bxl-whatsapp"></i></span>
                                                <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                                    placeholder="91XXXXXXXXXX" value="{{ $clients->whatsapp ?? '91' }}">
                                                <button type="button" class="btn btn-outline-success px-2" id="copyToWhatsapp" title="Copy from Mobile" data-bs-toggle="tooltip">
                                                    <i class='bx bx-copy'></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Business Details --}}
                        <div class="col-lg-6">
                            <div class="ml-card h-100">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                                        <i class="bx bx-briefcase"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Business Details</h6>
                                        <span class="ml-card-sub">Company, industry & lifecycle</span>
                                    </div>
                                </div>
                                <div class="ml-card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="ml-label">Company Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                                <input type="text" class="form-control" id="company" name="company"
                                                    placeholder="Company Name" value="{{ $clients->company ?? '' }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">GST No.</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                                <input type="text" class="form-control" id="gst" name="gst"
                                                    placeholder="GSTIN" value="{{ $clients->gstno ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Lifecycle Stage</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-pie-chart-alt-2"></i></span>
                                                <select class="form-select" id="lifecycle_stage" name="lifecycle_stage">
                                                    <option value="" {{ empty($clients->lifecycle_stage) ? 'selected' : '' }}>Select Stage</option>
                                                    @foreach(['Lead','Marketing Qualified','Sales Qualified','Customer','Evangelist'] as $stage)
                                                        <option value="{{ $stage }}" {{ ($clients->lifecycle_stage ?? '') == $stage ? 'selected' : '' }}>{{ $stage }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Position / Role</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-user-pin"></i></span>
                                                <input type="text" class="form-control" id="position" name="position"
                                                    placeholder="Job Title" value="{{ $clients->position ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Industry</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-cog"></i></span>
                                                <input type="text" class="form-control" id="industry" name="industry"
                                                    placeholder="e.g. IT, Manufacturing" value="{{ $clients->industry ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="ml-label">Website</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                                <input type="url" class="form-control" name="website"
                                                    placeholder="https://example.com" value="{{ $clients->website ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Marketing & Additional --}}
                        <div class="col-lg-6">
                            <div class="ml-card h-100">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                                        <i class="bx bx-target-lock"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Marketing & Additional</h6>
                                        <span class="ml-card-sub">Source, values & tagging</span>
                                    </div>
                                </div>
                                <div class="ml-card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="ml-label">Primary POC</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-user-voice"></i></span>
                                                <input type="text" class="form-control" name="poc"
                                                    placeholder="Main Contact Name" value="{{ $clients->poc ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Acquisition Source</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-download"></i></span>
                                                <input type="text" class="form-control" name="source"
                                                    placeholder="e.g. Organic, Referral" value="{{ $clients->source ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Relationship Purpose</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-bullseye"></i></span>
                                                <input type="text" class="form-control" name="purpose"
                                                    placeholder="e.g. Reseller, Enterprise" value="{{ $clients->purpose ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Est. Value/Score</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-line-chart"></i></span>
                                                <input type="text" class="form-control" name="values"
                                                    placeholder="e.g. High, 100k" value="{{ $clients->values ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Language</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-message-rounded-dots"></i></span>
                                                <input type="text" class="form-control" name="language"
                                                    placeholder="e.g. English" value="{{ $clients->language ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Tags (Comma separated)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-tag"></i></span>
                                                <input type="text" class="form-control" name="tags"
                                                    placeholder="e.g. VIP, Tech" value="{{ $clients->tags ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Location Details --}}
                        <div class="col-lg-6">
                            <div class="ml-card h-100">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(251,188,4,0.10);color:#f29900;">
                                        <i class="bx bx-map"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Location Details</h6>
                                        <span class="ml-card-sub">Address & region</span>
                                    </div>
                                </div>
                                <div class="ml-card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="ml-label">Full Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-home"></i></span>
                                                <input type="text" class="form-control" id="address" name="address[address]"
                                                    placeholder="Street, Building" value="{{ $location['address'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">City</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-map-alt"></i></span>
                                                <input type="text" class="form-control" id="city" name="address[city]"
                                                    placeholder="City" value="{{ $location['city'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">State</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                                                <input type="text" class="form-control" id="state" name="address[state]"
                                                    placeholder="State" value="{{ $location['state'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Country</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-globe-alt"></i></span>
                                                <input type="text" class="form-control" name="address[country]"
                                                    placeholder="Country" value="{{ $location['country'] ?? 'India' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Zip / Postal Code</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-pin"></i></span>
                                                <input type="text" class="form-control" name="address[zip]"
                                                    placeholder="Zip Code" value="{{ $location['zip'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Departments & Branches --}}
                        <div class="col-12">
                            <div class="ml-card">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(147,52,233,0.10);color:#9334e9;">
                                        <i class="bx bx-git-branch"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="ml-card-title">Departments & Branches</h6>
                                        <span class="ml-card-sub">Teams & points of contact</span>
                                    </div>
                                    <button type="button" class="lb-btn lb-btn-ghost" id="addDepartment" style="padding:4px 10px;font-size:0.75rem;">
                                        <i class="bx bx-plus"></i> Add
                                    </button>
                                </div>
                                <div class="ml-card-body p-0">
                                    <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                                        <table class="table table-sm table-hover mb-0" id="departmentTable">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th class="border-0 ps-3">Dept Name</th>
                                                    <th class="border-0">Branch</th>
                                                    <th class="border-0">POC</th>
                                                    <th class="border-0 text-center" style="width:50px;">Del</th>
                                                </tr>
                                            </thead>
                                            <tbody id="departmentBody">
                                                @if(isset($clients->departments) && count($clients->departments) > 0)
                                                    @foreach($clients->departments as $index => $dept)
                                                        <tr>
                                                            <td class="ps-3">
                                                                <input type="text" name="departments[{{$index}}][name]" class="form-control form-control-sm" value="{{$dept->name}}" placeholder="Sales">
                                                                <input type="hidden" name="departments[{{$index}}][id]" value="{{$dept->id}}">
                                                            </td>
                                                            <td><input type="text" name="departments[{{$index}}][location]" class="form-control form-control-sm" value="{{$dept->location}}" placeholder="Location"></td>
                                                            <td><input type="text" name="departments[{{$index}}][poc]" class="form-control form-control-sm" value="{{$dept->poc}}" placeholder="POC"></td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn kb-action-btn kb-action-del remove-dept"><i class="bx bx-trash"></i></button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td class="ps-3"><input type="text" name="departments[0][name]" class="form-control form-control-sm" placeholder="Sales"></td>
                                                        <td><input type="text" name="departments[0][location]" class="form-control form-control-sm" placeholder="Location"></td>
                                                        <td><input type="text" name="departments[0][poc]" class="form-control form-control-sm" placeholder="POC"></td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn kb-action-btn kb-action-del remove-dept"><i class="bx bx-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Footer --}}
                        <div class="col-12 mb-5">
                            <div class="ml-form-footer">
                                <a href="/clients" class="lb-btn lb-btn-ghost">
                                    <i class="bx bx-x"></i> Cancel
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="reset" class="lb-btn lb-btn-ghost">
                                        <i class="bx bx-reset"></i> Reset
                                    </button>
                                    <button type="submit" class="lb-btn lb-btn-primary">
                                        <i class="bx bx-check-circle"></i>
                                        {{ $isEdit ? 'Update Customer' : 'Save Customer' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>{{-- /profile tab --}}

                {{-- ── INTERACTIONS TAB ── --}}
                @if($isEdit)
                    <div class="tab-pane fade" id="interactions" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="ml-card">
                                    <div class="ml-card-header">
                                        <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;"><i class="bx bx-plus-circle"></i></div>
                                        <div><h6 class="ml-card-title">Add Interaction</h6><span class="ml-card-sub">Log a new activity</span></div>
                                    </div>
                                    <div class="ml-card-body">
                                        <form action="{{ route('clients.interaction') }}" method="post" enctype="multipart/form-data" class="row g-3">
                                            @csrf
                                            <input type="hidden" name="client_id" value="{{ $_GET['id'] }}">
                                            <div class="col-12">
                                                <label class="ml-label">Interaction Type</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-category"></i></span>
                                                    <select name="type" class="form-select" required>
                                                        <option value="Note">Note</option>
                                                        <option value="Call">Call Log</option>
                                                        <option value="Meeting">Meeting</option>
                                                        <option value="Email">Email Sent</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="ml-label">Notes / Content</label>
                                                <textarea name="content" class="form-control" rows="4" required placeholder="Details..."></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="lb-btn lb-btn-primary w-100">
                                                    <i class="bx bx-check-circle"></i> Save Interaction
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="ml-card">
                                    <div class="ml-card-header">
                                        <div class="ml-card-icon" style="background:rgba(0,102,102,0.10);color:#006666;"><i class="bx bx-history"></i></div>
                                        <div><h6 class="ml-card-title">Interaction History</h6><span class="ml-card-sub">All logged activities</span></div>
                                    </div>
                                    <div class="ml-card-body" style="max-height:500px;overflow-y:auto;">
                                        @if(isset($interactions) && $interactions->where('type','!=','Document')->count() > 0)
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($interactions->where('type','!=','Document') as $interaction)
                                                    <div class="mc-interaction-item">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="mc-interaction-type">
                                                                @if($interaction->type == 'Note') <i class="bx bx-notepad"></i>
                                                                @elseif($interaction->type == 'Call') <i class="bx bx-phone-call"></i>
                                                                @elseif($interaction->type == 'Meeting') <i class="bx bx-group"></i>
                                                                @else <i class="bx bx-envelope"></i> @endif
                                                                {{ $interaction->type }}
                                                            </span>
                                                            <small class="text-muted">{{ $interaction->created_at->format('d M Y, H:i') }}</small>
                                                        </div>
                                                        <p class="mb-0 small text-dark">{{ $interaction->content }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="kb-empty-col">
                                                <i class="bx bx-info-circle"></i>
                                                <span>No interactions logged yet.</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── JOURNEY TAB ── --}}
                    <div class="tab-pane fade" id="journey" role="tabpanel">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(0,102,102,0.10);color:#006666;"><i class="bx bx-git-commit"></i></div>
                                <div><h6 class="ml-card-title">Full Lifecycle Journey</h6><span class="ml-card-sub">Lead to customer path</span></div>
                            </div>
                            <div class="ml-card-body">
                                <div class="mc-timeline">
                                    <div class="mc-timeline-item mc-tl-blue">
                                        <div class="mc-tl-dot"></div>
                                        <div class="mc-tl-content">
                                            <h6 class="mc-tl-title">Lead Acquired</h6>
                                            @if($leadOrigin)
                                                <p class="text-muted small mb-1">Lead #{{ $leadOrigin->id }} · Created {{ $leadOrigin->created_at->format('d M Y') }}</p>
                                                <span class="kb-card-source"><i class="bx bx-user-plus"></i> {{ $leadOrigin->source ?? 'Direct' }}</span>
                                            @else
                                                <p class="text-muted small mb-0">Created: {{ $clients->created_at->format('d M Y') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if(count($proposals) > 0)
                                        <div class="mc-timeline-item mc-tl-purple">
                                            <div class="mc-tl-dot"></div>
                                            <div class="mc-tl-content">
                                                <h6 class="mc-tl-title">Proposals ({{ count($proposals) }})</h6>
                                                @foreach($proposals as $prop)
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="small"><i class="bx bx-file me-1"></i>{{ $prop->subject }}</span>
                                                        <span class="leads-status-badge {{ $prop->status == 'Accepted' ? 'leads-status-fresh' : 'leads-status-default' }}">{{ $prop->status }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(count($projects) > 0)
                                        <div class="mc-timeline-item mc-tl-teal">
                                            <div class="mc-tl-dot"></div>
                                            <div class="mc-tl-content">
                                                <h6 class="mc-tl-title">Projects ({{ count($projects) }})</h6>
                                                @foreach($projects as $proj)
                                                    <div class="d-flex justify-content-between align-items-center mb-1 p-2 bg-light rounded">
                                                        <span class="small fw-500">{{ $proj->name }}</span>
                                                        <span class="small text-muted">₹{{ number_format($proj->amount, 0) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(count($invoices) > 0)
                                        <div class="mc-timeline-item mc-tl-amber">
                                            <div class="mc-tl-dot"></div>
                                            <div class="mc-tl-content">
                                                <h6 class="mc-tl-title">Billing & Recovery</h6>
                                                <div class="row g-2 mt-1">
                                                    @foreach($invoices as $inv)
                                                        <div class="col-md-6">
                                                            <div class="mc-inv-card">
                                                                <div class="d-flex justify-content-between">
                                                                    <small class="fw-bold text-primary">INV#{{ str_pad($inv->id,5,'0',STR_PAD_LEFT) }}</small>
                                                                    <small class="text-muted">{{ $inv->created_at->format('d M') }}</small>
                                                                </div>
                                                                <div class="fw-bold mt-1">₹{{ number_format($inv->grand_total, 0) }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="mc-timeline-item mc-tl-green mc-tl-last">
                                        <div class="mc-tl-dot mc-tl-dot-success"></div>
                                        <div class="mc-tl-content">
                                            <h6 class="mc-tl-title" style="color:#34a853;">Customer Relationship Established</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── DOCUMENTS TAB ── --}}
                    <div class="tab-pane fade" id="documents" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="ml-card">
                                    <div class="ml-card-header">
                                        <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;"><i class="bx bx-upload"></i></div>
                                        <div><h6 class="ml-card-title">Upload Document</h6><span class="ml-card-sub">Attach files to this customer</span></div>
                                    </div>
                                    <div class="ml-card-body">
                                        <form action="{{ route('clients.interaction') }}" method="post" enctype="multipart/form-data" class="row g-3">
                                            @csrf
                                            <input type="hidden" name="client_id" value="{{ $_GET['id'] }}">
                                            <input type="hidden" name="type" value="Document">
                                            <div class="col-12">
                                                <label class="ml-label">Description / Title</label>
                                                <input type="text" name="content" class="form-control" placeholder="e.g. Signed Contract" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="ml-label">File Attachment</label>
                                                <input type="file" name="attachment" class="form-control" required>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="lb-btn lb-btn-primary w-100">
                                                    <i class="bx bx-upload"></i> Upload Document
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="ml-card">
                                    <div class="ml-card-header">
                                        <div class="ml-card-icon" style="background:rgba(0,102,102,0.10);color:#006666;"><i class="bx bx-folder-open"></i></div>
                                        <div><h6 class="ml-card-title">Customer Documents</h6><span class="ml-card-sub">All uploaded files</span></div>
                                    </div>
                                    <div class="ml-card-body p-0">
                                        <div class="table-responsive">
                                            <table class="leads-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Uploaded On</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($interactions) && $interactions->where('type','Document')->count() > 0)
                                                        @foreach($interactions->where('type','Document') as $doc)
                                                            <tr>
                                                                <td><i class="bx bx-file text-primary me-2"></i>{{ $doc->content }}</td>
                                                                <td>{{ $doc->created_at->format('d M Y') }}</td>
                                                                <td class="text-center">
                                                                    <a href="{{ asset('storage/'.$doc->attachment_path) }}" target="_blank"
                                                                       class="btn kb-action-btn kb-action-call" title="Download">
                                                                        <i class="bx bx-download"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr><td colspan="3"><div class="kb-empty-col"><i class="bx bx-folder"></i><span>No documents uploaded.</span></div></td></tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>{{-- /tab-content --}}
        </div>{{-- /dash-container --}}
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Dynamic Department Add
            let deptIndex = {{ isset($clients->departments) ? count($clients->departments) : 1 }};
            document.getElementById('addDepartment').addEventListener('click', function () {
                const tbody = document.getElementById('departmentBody');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-3"><input type="text" name="departments[${deptIndex}][name]" class="form-control form-control-sm" placeholder="Sales"></td>
                    <td><input type="text" name="departments[${deptIndex}][location]" class="form-control form-control-sm" placeholder="Location"></td>
                    <td><input type="text" name="departments[${deptIndex}][poc]" class="form-control form-control-sm" placeholder="POC"></td>
                    <td class="text-center align-middle"><button type="button" class="btn kb-action-btn kb-action-del remove-dept"><i class="bx bx-trash"></i></button></td>`;
                tbody.appendChild(tr);
                deptIndex++;
            });
            document.getElementById('departmentBody').addEventListener('click', function (e) {
                if (e.target.closest('.remove-dept')) e.target.closest('tr').remove();
            });

            // Sync Mobile to Whatsapp Action
            const btnCopyWa = document.getElementById('copyToWhatsapp');
            if(btnCopyWa) {
                btnCopyWa.addEventListener('click', function() {
                    const mobVal = document.getElementById('mob').value;
                    if(mobVal) {
                        document.getElementById('whatsapp').value = mobVal;
                        // Flash green background temporarily to show success
                        const waInput = document.getElementById('whatsapp');
                        const oldBg = waInput.style.backgroundColor;
                        waInput.style.backgroundColor = '#e8f5e9';
                        setTimeout(() => { waInput.style.backgroundColor = oldBg; }, 400);
                    }
                });
            }
        });
    </script>

    <style>
        /* â”€â”€ Premium Dashboard Tabs UI â”€â”€ */
        .custom-dash-tabs {
            background: #ffffff;
            padding: 8px;
            border-radius: 16px;
            display: inline-flex;
            gap: 6px;
            border: 1px solid #e8eaed;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            flex-wrap: wrap; /* Ensure tabs wrap nicely on mobile screens */
        }
        .custom-dash-tabs .nav-link {
            border-radius: 12px;
            color: #5f6368;
            font-weight: 500;
            padding: 10px 22px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            background: transparent;
            display: flex;
            align-items: center;
        }
        .custom-dash-tabs .nav-link:hover {
            color: #1a73e8;
            background: rgba(26,115,232,0.06);
            transform: translateY(-1px);
        }
        .custom-dash-tabs .nav-link.active {
            background: #1a73e8;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(26,115,232,0.3);
            transform: translateY(-1px);
        }
        .custom-dash-tabs .nav-link.active i {
            color: #ffffff !important;
        }
        .custom-dash-tabs .nav-link i {
            font-size: 1.15rem;
            color: #9aa0a6;
            transition: color 0.25s;
        }

        /* Fix input-group button border radii */
        button#copyToWhatsapp {
            border: 1px solid #dee2e6;
            border-top-left-radius: 0px !important;
            border-bottom-left-radius: 0px !important;
        }
    </style>

@endsection
