@extends('layout')
@section('title', 'Manage Lead - Rusan')

@section('content')

    @php
        $isEdit = !empty($_GET['id']);
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

                    {{-- LEAD INFORMATION --}}
                    <div class="col-12">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(26,115,232,0.10); color:#1a73e8;">
                                    <i class="bx bx-user"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Lead Information</h6>
                                    <span class="ml-card-sub">Basic identity & lead categorization</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="ml-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="first_name" placeholder="First Name" value="{{ $leads?->first_name ?? '' }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name" placeholder="Middle Name" value="{{ $leads?->middle_name ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" placeholder="Last Name" value="{{ $leads?->last_name ?? '' }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="ml-label">Gender</label>
                                        <select class="form-select" name="gender">
                                            <option value="">Select...</option>
                                            <option value="Male" {{ ($leads?->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ ($leads?->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ ($leads?->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Date of Birth</label>
                                        <input type="date" class="form-control" name="dob" value="{{ $leads?->dob ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Progress</label>
                                        <select class="form-select" name="progress">
                                            <option value="">Select...</option>
                                            <option value="Call Back" {{ ($leads?->progress ?? '') == 'Call Back' ? 'selected' : '' }}>Call Back</option>
                                            <option value="Interested" {{ ($leads?->progress ?? '') == 'Interested' ? 'selected' : '' }}>Interested</option>
                                            <option value="Not Interested" {{ ($leads?->progress ?? '') == 'Not Interested' ? 'selected' : '' }}>Not Interested</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="ml-label">Company Name</label>
                                        <input type="text" class="form-control" name="company" placeholder="Company Name" value="{{ $leads?->company ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Industry</label>
                                        <select class="form-select" name="industry">
                                            <option value="">Select...</option>
                                            <option value="IT" {{ ($leads?->industry ?? '') == 'IT' ? 'selected' : '' }}>IT</option>
                                            <option value="Healthcare" {{ ($leads?->industry ?? '') == 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                                            <option value="Finance" {{ ($leads?->industry ?? '') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                            <option value="Other" {{ ($leads?->industry ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Interested Product</label>
                                        <select class="form-select" name="product">
                                            <option value="">Select...</option>
                                            <option value="Product A" {{ ($leads?->interested_product ?? '') == 'Product A' ? 'selected' : '' }}>Product A</option>
                                            <option value="Product B" {{ ($leads?->interested_product ?? '') == 'Product B' ? 'selected' : '' }}>Product B</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="ml-label">Lead Source</label>
                                        <select class="form-select" name="source">
                                            <option value="">Select...</option>
                                            <option value="Web" {{ ($leads?->source ?? '') == 'Web' ? 'selected' : '' }}>Web</option>
                                            <option value="Referral" {{ ($leads?->source ?? '') == 'Referral' ? 'selected' : '' }}>Referral</option>
                                            <option value="Ad" {{ ($leads?->source ?? '') == 'Ad' ? 'selected' : '' }}>Ad</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Email opt out</label>
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="email_opt_out" id="email_opt_out" {{ ($leads?->email_opt_out ?? 0) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">SMS opt out</label>
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="sms_opt_out" id="sms_opt_out" {{ ($leads?->sms_opt_out ?? 0) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CONTACT INFORMATION --}}
                    <div class="col-12">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(52,168,83,0.10); color:#163f7a;">
                                    <i class="bx bx-phone"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Contact Information</h6>
                                    <span class="ml-card-sub">Phone, Email & Address Details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="ml-label">Phone <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                            <input type="text" class="form-control" name="mob" placeholder="Phone" value="{{ $leads?->mob ?? '' }}" required>
                                            <button class="btn btn-outline-primary" type="button"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                            <input type="email" class="form-control" name="email" placeholder="Email" value="{{ $leads?->email ?? '' }}">
                                            <button class="btn btn-outline-primary" type="button"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Website</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                            <input type="text" class="form-control" name="website" placeholder="Website" value="{{ $leads?->website ?? '' }}">
                                            <button class="btn btn-outline-primary" type="button"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="ml-label">Address</label>
                                        <input type="text" class="form-control" name="address" placeholder="Address" value="{{ $leads?->address ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">City</label>
                                        <select class="form-select" name="city">
                                            <option value="">Select...</option>
                                            <option value="Mumbai" {{ ($leads?->city ?? '') == 'Mumbai' ? 'selected' : '' }}>Mumbai</option>
                                            <option value="Delhi" {{ ($leads?->city ?? '') == 'Delhi' ? 'selected' : '' }}>Delhi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">State</label>
                                        <select class="form-select" name="state">
                                            <option value="">Select...</option>
                                            <option value="Maharashtra" {{ ($leads?->state ?? '') == 'Maharashtra' ? 'selected' : '' }}>Maharashtra</option>
                                            <option value="Delhi" {{ ($leads?->state ?? '') == 'Delhi' ? 'selected' : '' }}>Delhi</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="ml-label">Country</label>
                                        <select class="form-select" name="country">
                                            <option value="">Select...</option>
                                            <option value="India" {{ ($leads?->country ?? 'India') == 'India' ? 'selected' : '' }}>India</option>
                                            <option value="USA" {{ ($leads?->country ?? '') == 'USA' ? 'selected' : '' }}>USA</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Pin Code</label>
                                        <input type="text" class="form-control" name="pin_code" placeholder="Pin Code" value="{{ $leads?->pin_code ?? '' }}">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button class="btn btn-primary w-100"><i class="bx bx-plus"></i> Add More</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ADDITIONAL INFORMATION --}}
                    <div class="col-12">
                        <div class="ml-card">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(251,188,4,0.10); color:#f29900;">
                                    <i class="bx bx-info-circle"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Additional Information</h6>
                                    <span class="ml-card-sub">Medical history & follow-up details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="ml-label">Lead Score</label>
                                        <input type="text" class="form-control" name="score" placeholder="D" value="{{ $leads?->score ?? 'D' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Lead State</label>
                                        <select class="form-select" name="lead_state">
                                            <option value="">Select...</option>
                                            <option value="New" {{ ($leads?->lead_state ?? '') == 'New' ? 'selected' : '' }}>New</option>
                                            <option value="Contacted" {{ ($leads?->lead_state ?? '') == 'Contacted' ? 'selected' : '' }}>Contacted</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Last Call Feedback</label>
                                        <select class="form-select" name="last_call_feedback">
                                            <option value="">Select...</option>
                                            <option value="Positive" {{ ($leads?->last_call_feedback ?? '') == 'Positive' ? 'selected' : '' }}>Positive</option>
                                            <option value="Negative" {{ ($leads?->last_call_feedback ?? '') == 'Negative' ? 'selected' : '' }}>Negative</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="ml-label">Last Call Comment</label>
                                        <textarea class="form-control" name="last_call_comment" rows="3" placeholder="Last Call Comment">{{ $leads?->last_call_comment ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="ml-label">Next Call Date</label>
                                                <input type="datetime-local" class="form-control" name="nxtDate" value="{{ ($leads?->next_call_date) ? date('Y-m-d\TH:i', strtotime($leads?->next_call_date)) : '' }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="ml-label">Marketing Source</label>
                                                <select class="form-select" name="marketing_source">
                                                    <option value="">Select...</option>
                                                    <option value="Facebook" {{ ($leads?->marketing_source ?? '') == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                                    <option value="Google" {{ ($leads?->marketing_source ?? '') == 'Google' ? 'selected' : '' }}>Google</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="ml-label">Age</label>
                                        <input type="number" class="form-control" name="age" placeholder="Age" value="{{ $leads?->age ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Number of consumption years</label>
                                        <input type="number" class="form-control" name="consumption_years" placeholder="Number of consumption years" value="{{ $leads?->consumption_years ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Number of Times Tobacco consume</label>
                                        <input type="text" class="form-control" name="tobacco_frequency" placeholder="Number of Times Tobacco consume" value="{{ $leads?->tobacco_frequency ?? '' }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="ml-label">Craving for Smoking</label>
                                        <input type="text" class="form-control" name="craving_for_smoking" placeholder="Craving for Smoking" value="{{ $leads?->craving_for_smoking ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Problem Smoking</label>
                                        <input type="text" class="form-control" name="problem_smoking" placeholder="Problem Smoking" value="{{ $leads?->problem_smoking ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Experience Intense Craving</label>
                                        <input type="text" class="form-control" name="experience_intense_craving" placeholder="Experience Intense Craving" value="{{ $leads?->experience_intense_craving ?? '' }}">
                                    </div>
                                    
                                    {{-- Status & Assigned (Added for functionality) --}}
                                    <div class="col-md-6">
                                        <label class="ml-label">Assign Salesperson</label>
                                        <select class="form-select" name="assigned">
                                            <option value="">— Select Salesperson —</option>
                                            @foreach($salesUsers ?? [] as $u)
                                                <option value="{{ $u->id }}" {{ ($leads?->assigned ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="0" {{ ($leads?->status ?? '') == '0' ? 'selected' : '' }}>🔵 New</option>
                                            <option value="1" {{ ($leads?->status ?? '') == '1' ? 'selected' : '' }}>🟠 Contacted</option>
                                            <option value="2" {{ ($leads?->status ?? '') == '2' ? 'selected' : '' }}>🟣 Qualified</option>
                                            <option value="3" {{ ($leads?->status ?? '') == '3' ? 'selected' : '' }}>🟢 Proposal Sent</option>
                                            <option value="5" {{ ($leads?->status ?? '') == '5' ? 'selected' : '' }}>✅ Closed (Won)</option>
                                            <option value="9" {{ ($leads?->status ?? '') == '9' ? 'selected' : '' }}>❌ Lost</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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
