@extends('layout')
@section('title', 'Manage Lead - Rusan')

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

            <form action="manage-lead" method="post" id="manageLeadForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">
                <input type="hidden" name="from" value="{{ request('from') }}">

                <div class="row g-4">

                    {{-- ═══════════════════════════════════════ --}}
                    {{-- SECTION 1: LEAD INFORMATION            --}}
                    {{-- ═══════════════════════════════════════ --}}
                    <div class="col-12">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(26,115,232,0.10); color:#1a73e8;">
                                    <i class="bx bx-user"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Lead Information</h6>
                                    <span class="ml-card-sub">Identity, demographics & business details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">

                                    {{-- Row 1: First / Middle / Last Name --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="first_name"
                                            placeholder="First Name"
                                            value="{{ $leads->first_name ?? '' }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name"
                                            placeholder="Middle Name"
                                            value="{{ $leads->middle_name ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name"
                                            placeholder="Last Name"
                                            value="{{ $leads->last_name ?? '' }}">
                                    </div>

                                    {{-- Row 2: Gender / DOB / Progress --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Gender</label>
                                        <select class="form-select" name="gender">
                                            <option value="">Select...</option>
                                            <option value="Male" {{ ($leads->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ ($leads->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ ($leads->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Date of Birth</label>
                                        <input type="date" class="form-control" name="dob"
                                            value="{{ $leads->dob ? $leads->dob->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Progress</label>
                                        <input type="text" class="form-control" name="progress"
                                            placeholder="Call Back"
                                            value="{{ $leads->progress ?? '' }}">
                                    </div>

                                    {{-- Row 3: Company / Industry / Interested Product --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Company Name</label>
                                        <input type="text" class="form-control" name="company"
                                            placeholder="Company Name"
                                            value="{{ $leads->company ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Industry</label>
                                        <input type="text" class="form-control" name="industry"
                                            placeholder="Retail"
                                            value="{{ $leads->industry ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Interested Product</label>
                                        <select class="form-select" name="interested_product">
                                            <option value="">Select...</option>
                                            @foreach($products ?? [] as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ ($leads->interested_product ?? '') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Row 4: Lead Source / First Call / SMS Opt --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Lead Source</label>
                                        <select class="form-select" name="source">
                                            <option value="">Select...</option>
                                            <option value="Web" {{ strcasecmp($leads->source ?? '', 'web') === 0 ? 'selected' : '' }}>Web Form</option>
                                            <option value="Referral" {{ strcasecmp($leads->source ?? '', 'referral') === 0 ? 'selected' : '' }}>Referral</option>
                                            <option value="Ad" {{ strcasecmp($leads->source ?? '', 'ad') === 0 ? 'selected' : '' }}>Advertisement</option>
                                            <option value="Event" {{ strcasecmp($leads->source ?? '', 'event') === 0 ? 'selected' : '' }}>Event / Expo</option>
                                            <option value="Cold Call" {{ strcasecmp($leads->source ?? '', 'cold call') === 0 ? 'selected' : '' }}>Cold Call</option>
                                            <option value="Other" {{ strcasecmp($leads->source ?? '', 'other') === 0 ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end gap-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="first_call" name="first_call" value="1"
                                                {{ ($leads->first_call ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label ml-label" for="first_call">First Call</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="sms_opt" name="sms_opt" value="1"
                                                {{ ($leads->sms_opt ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label ml-label" for="sms_opt">SMS Opt</label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════ --}}
                    {{-- SECTION 2: CONTACT INFORMATION         --}}
                    {{-- ═══════════════════════════════════════ --}}
                    <div class="col-12">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(52,168,83,0.10); color:#34a853;">
                                    <i class="bx bx-phone"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Contact Information</h6>
                                    <span class="ml-card-sub">Phone, email, website & address</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">

                                    {{-- Row 1: Phone / Email / Website --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Phone <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-select" name="phone_code" style="max-width:80px;">
                                                <option value="+91" selected>+91</option>
                                                <option value="+1">+1</option>
                                                <option value="+44">+44</option>
                                                <option value="+971">+971</option>
                                                <option value="+61">+61</option>
                                            </select>
                                            <input type="text" class="form-control" name="mob"
                                                placeholder="Phone"
                                                value="{{ $leads->mob ?? '' }}" required>
                                            <button type="button" class="btn btn-primary btn-sm" title="Add Phone"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Email</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control" name="email"
                                                placeholder="Email"
                                                value="{{ $leads->email ?? '' }}">
                                            <button type="button" class="btn btn-primary btn-sm" title="Add Email"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Website</label>
                                        <div class="input-group">
                                            <input type="url" class="form-control" name="website"
                                                placeholder="Website"
                                                value="{{ $leads->website ?? '' }}">
                                            <button type="button" class="btn btn-primary btn-sm" title="Add Website"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </div>

                                    {{-- Row 2: Address / City / State --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Address</label>
                                        <input type="text" class="form-control" name="address[address]"
                                            placeholder="Address"
                                            value="{{ $location['address'] ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">City</label>
                                        <select class="form-select" name="address[city]">
                                            <option value="">Select...</option>
                                            @php $cityVal = $location['city'] ?? ''; @endphp
                                            @if($cityVal)
                                                <option value="{{ $cityVal }}" selected>{{ $cityVal }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">State</label>
                                        <select class="form-select" name="address[state]">
                                            <option value="">Select...</option>
                                            @php $stateVal = $location['state'] ?? ''; @endphp
                                            @if($stateVal)
                                                <option value="{{ $stateVal }}" selected>{{ $stateVal }}</option>
                                            @endif
                                        </select>
                                    </div>

                                    {{-- Row 3: Country / Pin Code --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Country</label>
                                        <select class="form-select" name="address[country]">
                                            <option value="">Select...</option>
                                            <option value="India" {{ ($location['country'] ?? 'India') == 'India' ? 'selected' : '' }}>India</option>
                                            <option value="USA" {{ ($location['country'] ?? '') == 'USA' ? 'selected' : '' }}>USA</option>
                                            <option value="UK" {{ ($location['country'] ?? '') == 'UK' ? 'selected' : '' }}>UK</option>
                                            <option value="UAE" {{ ($location['country'] ?? '') == 'UAE' ? 'selected' : '' }}>UAE</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Pin Code</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="address[zip]"
                                                placeholder="Pin Code"
                                                value="{{ $location['zip'] ?? '' }}">
                                            <button type="button" class="btn btn-primary btn-sm" title="Add Pin Code"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════ --}}
                    {{-- SECTION 3: ADDITIONAL INFORMATION       --}}
                    {{-- ═══════════════════════════════════════ --}}
                    <div class="col-12">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(251,188,4,0.10); color:#f29900;">
                                    <i class="bx bx-info-circle"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Additional Information</h6>
                                    <span class="ml-card-sub">Lead scoring, call tracking & health assessment</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">

                                    {{-- Row 1: Lead Score / Lead State / Last Call Feedback --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Lead Score</label>
                                        <input type="number" class="form-control" name="score"
                                            placeholder="ID"
                                            value="{{ $leads->score ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Lead State</label>
                                        <select class="form-select" name="lead_state">
                                            <option value="">Select...</option>
                                            <option value="New" {{ ($leads->lead_state ?? '') == 'New' ? 'selected' : '' }}>New</option>
                                            <option value="Contacted" {{ ($leads->lead_state ?? '') == 'Contacted' ? 'selected' : '' }}>Contacted</option>
                                            <option value="Qualified" {{ ($leads->lead_state ?? '') == 'Qualified' ? 'selected' : '' }}>Qualified</option>
                                            <option value="Converted" {{ ($leads->lead_state ?? '') == 'Converted' ? 'selected' : '' }}>Converted</option>
                                            <option value="Lost" {{ ($leads->lead_state ?? '') == 'Lost' ? 'selected' : '' }}>Lost</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Last Call Feedback</label>
                                        <select class="form-select" name="last_call_feedback">
                                            <option value="">Select...</option>
                                            <option value="Interested" {{ ($leads->last_call_feedback ?? '') == 'Interested' ? 'selected' : '' }}>Interested</option>
                                            <option value="Not Interested" {{ ($leads->last_call_feedback ?? '') == 'Not Interested' ? 'selected' : '' }}>Not Interested</option>
                                            <option value="Call Back" {{ ($leads->last_call_feedback ?? '') == 'Call Back' ? 'selected' : '' }}>Call Back</option>
                                            <option value="Not Reachable" {{ ($leads->last_call_feedback ?? '') == 'Not Reachable' ? 'selected' : '' }}>Not Reachable</option>
                                            <option value="Wrong Number" {{ ($leads->last_call_feedback ?? '') == 'Wrong Number' ? 'selected' : '' }}>Wrong Number</option>
                                        </select>
                                    </div>

                                    {{-- Row 2: Last Call Comment / Next Call Date / Marketing Source --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Last Call Comment</label>
                                        <textarea class="form-control" name="last_call_comment" rows="3"
                                            placeholder="Last Call Comment">{{ $leads->last_call_comment ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Next Call Date</label>
                                        <input type="datetime-local" class="form-control" name="next_call_date"
                                            value="{{ $leads->next_call_date ? $leads->next_call_date->format('Y-m-d\TH:i') : '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Marketing Source</label>
                                        <select class="form-select" name="marketing_source">
                                            <option value="">Select...</option>
                                            <option value="Google Ads" {{ ($leads->marketing_source ?? '') == 'Google Ads' ? 'selected' : '' }}>Google Ads</option>
                                            <option value="Facebook" {{ ($leads->marketing_source ?? '') == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                            <option value="Instagram" {{ ($leads->marketing_source ?? '') == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                            <option value="LinkedIn" {{ ($leads->marketing_source ?? '') == 'LinkedIn' ? 'selected' : '' }}>LinkedIn</option>
                                            <option value="Email Campaign" {{ ($leads->marketing_source ?? '') == 'Email Campaign' ? 'selected' : '' }}>Email Campaign</option>
                                            <option value="Direct" {{ ($leads->marketing_source ?? '') == 'Direct' ? 'selected' : '' }}>Direct</option>
                                            <option value="Other" {{ ($leads->marketing_source ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>

                                    {{-- Row 3: Age / Consumption Years / Tobacco Frequency --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Age</label>
                                        <input type="number" class="form-control" name="age"
                                            placeholder="Age"
                                            value="{{ $leads->age ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Number of Consumption Years</label>
                                        <input type="number" class="form-control" name="consumption_years"
                                            placeholder="Number of consumption years"
                                            value="{{ $leads->consumption_years ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Number of Times Tobacco Consume</label>
                                        <input type="number" class="form-control" name="tobacco_frequency"
                                            placeholder="Number of Times Tobacco consume"
                                            value="{{ $leads->tobacco_frequency ?? '' }}">
                                    </div>

                                    {{-- Row 4: Craving / Problem Smoking / Experience Intense Craving --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Craving for Smoking</label>
                                        <input type="text" class="form-control" name="craving_for_smoking"
                                            placeholder="Craving For Smoking"
                                            value="{{ $leads->craving_for_smoking ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Problem Smoking</label>
                                        <input type="text" class="form-control" name="problem_smoking"
                                            placeholder="Problem Smoking"
                                            value="{{ $leads->problem_smoking ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Experience Intense Craving</label>
                                        <input type="text" class="form-control" name="experience_intense_craving"
                                            placeholder="Experience Intense Craving"
                                            value="{{ $leads->experience_intense_craving ?? '' }}">
                                    </div>

                                    {{-- Row 5: Attachment --}}
                                    <div class="col-md-4">
                                        <label class="ml-label">Attachment</label>
                                        <input type="file" class="form-control" name="attachment">
                                        @if($leads->attachment ?? false)
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . $leads->attachment) }}" target="_blank" class="text-primary d-flex align-items-center gap-1">
                                                    <i class="bx bx-paperclip"></i> View Current Attachment
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════ --}}
                    {{-- HIDDEN LEGACY FIELDS                    --}}
                    {{-- ═══════════════════════════════════════ --}}
                    <input type="hidden" name="status" value="{{ $leads->status ?? '0' }}">

                    {{-- NEXT ACTION — New Leads Only --}}
                    @if(!$isEdit)
                        <div class="col-12">
                            <div class="ml-card ml-card-accent">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(22, 63, 122,0.10); color:#163f7a;">
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
