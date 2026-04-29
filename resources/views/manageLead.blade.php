@extends('layout')
@section('title', 'Manage Lead - eseCRM')

@section('content')

    @php
        $location = json_decode(($leads->location ?? '[]'), true);
        $isEdit   = !empty($_GET['id']);
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => $isEdit ? 'Edit Lead' : 'New Lead'])

        <div class="dash-container">

            {{-- Page Title Bar --}}
            <div class="ml-page-topbar mb-4">
                <div class="ml-page-topbar-left">
                    <a href="{{ request('from') === 'kanban' ? '/leads/kanban' : '/leads' }}" class="ml-back-btn" title="Back">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                    <div>
                        <h1 class="ml-page-title">
                            {{ $isEdit ? 'Edit Lead Details' : 'Add New Lead' }}
                        </h1>
                        <p class="ml-page-subtitle">
                            {{ $isEdit ? 'Update the lead information below.' : 'Fill in the details to create a new lead.' }}
                        </p>
                    </div>
                </div>
                @if($isEdit)
                    <div class="ml-lead-badge">
                        <i class="bx bx-edit-alt"></i> Editing
                    </div>
                @else
                    <div class="ml-lead-badge ml-lead-badge-new">
                        <i class="bx bx-plus-circle"></i> New Entry
                    </div>
                @endif
            </div>

            <form action="manage-lead" method="post" id="manageLeadForm">
                @csrf
                <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">
                <input type="hidden" name="from" value="{{ request('from') }}">

                <div class="row g-4">

                    {{-- PRIMARY INFORMATION --}}
                    <div class="col-lg-6">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(26,115,232,0.10); color:#1a73e8;">
                                    <i class="bx bx-user"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Primary Information</h6>
                                    <span class="ml-card-sub">Contact identity & details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="ml-label">Full Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Enter Full Name"
                                                value="{{ $leads->name ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="email@example.com"
                                                value="{{ $leads->email ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Mobile Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                            <input type="text" class="form-control" id="mob" name="mob"
                                                placeholder="91XXXXXXXXXX"
                                                value="{{ $leads->mob ?? '91' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">WhatsApp</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="color:#25d366;"><i class="bx bxl-whatsapp"></i></span>
                                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                                placeholder="91XXXXXXXXXX"
                                                value="{{ $leads->whatsapp ?? '91' }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="ml-label">GST Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                            <input type="text" class="form-control" id="gstno" name="gstno"
                                                placeholder="GSTIN Number"
                                                value="{{ $leads->gstno ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BUSINESS DETAILS --}}
                    <div class="col-lg-6">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(52,168,83,0.10); color:#34a853;">
                                    <i class="bx bx-briefcase"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Business Details</h6>
                                    <span class="ml-card-sub">Company & role information</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="ml-label">Company Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                                            <input type="text" class="form-control" id="company" name="company"
                                                placeholder="Enter Company"
                                                value="{{ $leads->company ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Position / Job Title</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user-pin"></i></span>
                                            <input type="text" class="form-control" id="position" name="position"
                                                placeholder="e.g. Manager"
                                                value="{{ $leads->position ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Industry</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-cog"></i></span>
                                            <input type="text" class="form-control" id="industry" name="industry"
                                                placeholder="e.g. IT, Healthcare"
                                                value="{{ $leads->industry ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="ml-label">Website</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                            <input type="url" class="form-control" name="website"
                                                placeholder="https://example.com"
                                                value="{{ $leads->website ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LOCATION DETAILS --}}
                    <div class="col-lg-6">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(251,188,4,0.10); color:#f29900;">
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
                                                placeholder="Street, Building"
                                                value="{{ $location['address'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">City</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-map-alt"></i></span>
                                            <input type="text" class="form-control" id="city" name="address[city]"
                                                placeholder="City"
                                                value="{{ $location['city'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">State</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                                            <input type="text" class="form-control" id="state" name="address[state]"
                                                placeholder="State"
                                                value="{{ $location['state'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Country</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-globe-alt"></i></span>
                                            <input type="text" class="form-control" name="address[country]"
                                                placeholder="Country"
                                                value="{{ $location['country'] ?? 'India' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Zip / Postal Code</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-pin"></i></span>
                                            <input type="text" class="form-control" name="address[zip]"
                                                placeholder="ZIP Code"
                                                value="{{ $location['zip'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LEAD INTELLIGENCE --}}
                    <div class="col-lg-6">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(234,67,53,0.10); color:#ea4335;">
                                    <i class="bx bx-brain"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Lead Intelligence</h6>
                                    <span class="ml-card-sub">Assignment, value & tags</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="ml-label">Assign Salesperson</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user-plus"></i></span>
                                            <select class="form-select" name="assigned">
                                                <option value="">— Select Salesperson —</option>
                                                @foreach($salesUsers ?? [] as $u)
                                                    <option value="{{ $u->id }}"
                                                        {{ ($leads->assigned ?? '') == $u->id ? 'selected' : '' }}>
                                                        {{ $u->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="ml-label">Purpose</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-target-lock"></i></span>
                                            <input type="text" class="form-control" name="purpose"
                                                placeholder="e.g. Sales, Query"
                                                value="{{ $leads->purpose ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Lead Value (₹)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-rupee"></i></span>
                                            <input type="number" class="form-control" name="value"
                                                placeholder="0.00"
                                                value="{{ $leads->values ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Language</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-world"></i></span>
                                            <input type="text" class="form-control" name="language"
                                                placeholder="EN / HI"
                                                value="{{ $leads->language ?? 'EN' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Point of Contact (POC)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user-check"></i></span>
                                            <input type="text" class="form-control" name="poc"
                                                placeholder="SPOK Name"
                                                value="{{ $leads->poc ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Keywords / Tags</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-purchase-tag-alt"></i></span>
                                            <input type="text" class="form-control" name="tags"
                                                placeholder="e.g. K2, Hot, VIP"
                                                value="{{ $leads->tags ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-list-check"></i></span>
                                            <select class="form-select" name="status">
                                                <option value="0" {{ ($leads->status ?? '') == '0' ? 'selected' : '' }}>🔵 New</option>
                                                <option value="1" {{ ($leads->status ?? '') == '1' ? 'selected' : '' }}>🟠 Contacted</option>
                                                <option value="2" {{ ($leads->status ?? '') == '2' ? 'selected' : '' }}>🟣 Qualified</option>
                                                <option value="3" {{ ($leads->status ?? '') == '3' ? 'selected' : '' }}>🟢 Proposal Sent</option>
                                                <option value="5" {{ ($leads->status ?? '') == '5' ? 'selected' : '' }}>✅ Closed (Won)</option>
                                                <option value="9" {{ ($leads->status ?? '') == '9' ? 'selected' : '' }}>❌ Lost</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- NEXT ACTION — New Leads Only --}}
                    @if(!$isEdit)
                        <div class="col-12">
                            <div class="ml-card ml-card-accent">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(0,102,102,0.10); color:#006666;">
                                        <i class="bx bx-calendar-event"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Next Action & Follow-up</h6>
                                        <span class="ml-card-sub">Schedule the first reminder & note</span>
                                    </div>
                                </div>
                                <div class="ml-card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="ml-label">Reminder Date & Time</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-time"></i></span>
                                                <input type="datetime-local" class="form-control" id="nxtDate" name="nxtDate">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="ml-label">Opening Note / Message</label>
                                            <textarea class="form-control" rows="3" id="message" name="message"
                                                placeholder="Summary of the first conversation or intent..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- FORM ACTION FOOTER --}}
                    <div class="col-12 mb-5">
                        <div class="ml-form-footer">
                            <a href="{{ request('from') === 'kanban' ? '/leads/kanban' : '/leads' }}" class="lb-btn lb-btn-ghost">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                            <div class="d-flex gap-2">
                                <button type="reset" class="lb-btn lb-btn-ghost">
                                    <i class="bx bx-reset"></i> Reset
                                </button>
                                <button type="submit" class="lb-btn lb-btn-primary">
                                    <i class="bx bx-check-circle"></i>
                                    {{ $isEdit ? 'Update Lead' : 'Save New Lead' }}
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </section>

@endsection
