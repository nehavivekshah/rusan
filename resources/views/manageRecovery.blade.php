@extends('layout')
@section('title', 'Manage Recovery - eseCRM')

@section('content')
    @php
        $sessionroles = session('roles');
        $roleArray = explode(',', ($sessionroles->permissions ?? ''));
        $isEdit = !empty($_GET['id']);
        $title = $isEdit ? 'Edit Recovery Details' : 'Add New Recovery';
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Project Management', 'subtitle' => $title])

        <div class="dash-container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <div class="ml-card">
                        <div class="ml-card-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ $previous_url ?? '/recoveries' }}" class="btn btn-light btn-sm rounded-circle">
                                    <i class="bx bx-arrow-back"></i>
                                </a>
                                <h5 class="mb-0">{{ $title }}</h5>
                            </div>
                            @if($isEdit)
                                <span class="badge bg-soft-primary text-primary">ID: #REC-{{ str_pad($_GET['id'], 4, '0', STR_PAD_LEFT) }}</span>
                            @endif
                        </div>

                        <div class="ml-card-body p-4">
                            <form action="manage-recovery" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">
                                <input type="hidden" name="previous_url" value="{{ $previous_url ?? url()->previous() }}">

                                <div class="row g-4">
                                    {{-- Primary Context --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Customer Context</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text bg-light"><i class='bx bx-user-pin'></i></span>
                                            <select class="form-select selectpicker" name="clientId" id="clientId" data-live-search="true" required>
                                                <option value="">Select a Customer...</option>
                                                <option value="new" data-content="<span class='text-primary fw-bold'>+ New Customer</span>">+ New Customer</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" @if($recoveries && $client->id == ($recoveries->client_id ?? '')) selected @endif>
                                                        {{ $client->name }} {{ $client->company ? "({$client->company})" : "" }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Project Context</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text bg-light"><i class='bx bx-briefcase'></i></span>
                                            <select class="form-select selectpicker" name="projectId" id="projectId" data-live-search="true" required>
                                                <option value="">Select a Project...</option>
                                                <option value="new" data-content="<span class='text-primary fw-bold'>+ New Project</span>">+ New Project</option>
                                                @if(!empty($projects))
                                                    @foreach($projects as $project)
                                                        <option value="{{ $project->id }}" @if($recoveries && $project->id == ($recoveries->project_id ?? '')) selected @endif>
                                                            {{ $project->name }} - ₹{{ number_format($project->amount, 0) }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <hr class="my-2 opacity-50">

                                    {{-- Basic Information --}}
                                    <div class="col-md-4">
                                        <label class="form-label">Batch No.*</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class='bx bx-barcode-reader'></i></span>
                                            <input type="text" class="form-control" id="btno" name="btno" placeholder="Batch Number" value="{{ $recoveries->batchNo ?? '' }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Client Name*</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" value="{{ $recoveries->client_name ?? '' }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Company*</label>
                                        <input type="text" class="form-control" id="company" name="company" placeholder="Company Name" value="{{ $recoveries->client_company ?? '' }}" required>
                                    </div>

                                    <div class="col-md-6" id="pDiv" style="display: {{ (empty($recoveries->project_id) && !empty($recoveries->project_name)) ? 'block' : 'none' }}">
                                        <label class="form-label">Manual Project Name*</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class='bx bx-box'></i></span>
                                            <input type="text" class="form-control border-primary" id="project" name="project" placeholder="Enter custom project name" value="{{ $recoveries->project_name ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Total Amount*</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">₹</span>
                                            <input type="number" class="form-control fw-bold" name="amount" placeholder="0.00" value="{{ $recoveries->project_amount ?? '' }}" required>
                                        </div>
                                    </div>

                                    @if(!$isEdit)
                                        <div class="col-md-3">
                                            <label class="form-label">Received Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-soft-success text-success">₹</span>
                                                <input type="number" class="form-control border-success" name="received" placeholder="0.00" value="{{ $recoveries->paid ?? '0' }}">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Next Reminder</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bx bx-bell"></i></span>
                                                <input type="datetime-local" class="form-control" name="reminder" value="{{ !empty($recoveries->reminder) ? \Carbon\Carbon::parse($recoveries->reminder)->format('Y-m-d\TH:i') : '' }}">
                                            </div>
                                        </div>
                                    @endif

                                    <hr class="my-2 opacity-50">

                                    {{-- Contact & Details --}}
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label">Mobile Number*</label>
                                        <input type="tel" class="form-control" name="phone" placeholder="+91" value="{{ $recoveries->client_mob ?? '91' }}" required>
                                    </div>

                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label">WhatsApp*</label>
                                        <input type="tel" class="form-control" name="whatsapp" placeholder="+91" value="{{ $recoveries->client_whatsapp ?? '91' }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" placeholder="client@example.com" value="{{ $recoveries->client_email ?? '' }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Executive (POC)</label>
                                        <input type="text" class="form-control" name="executive" value="{{ $recoveries->client_poc ?? '' }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Industry</label>
                                        <input type="text" class="form-control" name="industry" value="{{ $recoveries->client_industry ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Website URL</label>
                                        <input type="url" class="form-control" name="website" placeholder="https://..." value="{{ $recoveries->website ?? '' }}">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Notes / Message</label>
                                        <textarea class="form-control" name="note" rows="3" placeholder="Add any collection notes here...">{{ $recoveries->recovery_note ?? '' }}</textarea>
                                    </div>

                                    <div class="col-12 mt-4 pt-2 border-top d-flex gap-2 justify-content-end">
                                        <button type="reset" class="btn btn-light px-4">Reset Form</button>
                                        <button type="submit" name="submit" class="btn btn-primary px-5 fw-bold">
                                            <i class="bx bx-check-double"></i> {{ $isEdit ? 'Save Changes' : 'Record Recovery' }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {
            // Handle project selection logic
            $('#projectId').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#pDiv').slideDown();
                    $('#project').attr('required', true).focus();
                } else {
                    $('#pDiv').slideUp();
                    $('#project').attr('required', false);
                }
            });

            // Handle client change context if needed
            $('#clientId').on('change', function() {
                if ($(this).val() === 'new') {
                    // Could clear form or toggle fields
                }
            });
        });
    </script>

    <style>
        .form-label { font-size: 0.85rem; font-weight: 500; color: #5f6368; margin-bottom: 6px; }
        .form-control:focus, .form-select:focus { border-color: #006666; box-shadow: 0 0 0 0.2rem rgba(0, 102, 102, 0.15); }
        .input-group-text { border-right: none; color: #5f6368; }
        .input-group > .form-control { border-left: none; }
        .bg-soft-success { background-color: rgba(52, 168, 83, 0.1); }
        .bg-soft-primary { background-color: rgba(26, 115, 232, 0.1); }
    </style>
@endsection

