@if(!request()->has('ajax'))
@extends('layout')
@section('title', 'Contract - eseCRM')

@section('content')
@endif

@php
    $sessionroles = session('roles');
    $roleArray    = explode(',', ($sessionroles->permissions ?? ''));
    $showCustom = old('contract_type', $contract->contract_type ?? '') === 'new';
@endphp

@if(!request()->has('ajax'))
<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i>
        Manage Contract
        <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
    </div>

    <div class="container-fluid">
        <div class="board-title board-title-flex mb-2">
            <a href="/contracts" class="btn btn-light btn-sm back-btn rounded-pill border">
                <i class="bx bx-arrow-back"></i>
            </a>
            @if(!empty($_GET['id']))
                <h1>Edit Contract</h1>
            @else
                <h1>Add New Contract</h1>
            @endif
        </div>
@else
    <div class="modal-header" style="border-bottom:1px solid #f0f0f0; padding:16px 20px;">
        <h5 class="modal-title" style="font-size:1.1rem; font-weight:700; color:#202124;">
            @if(!empty($_GET['id']) || !empty($contract->id)) Edit Contract @else Add New Contract @endif
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body px-4 py-3">
@endif

        <div class="row g-0">
            <div class="col-md-12">
                <form action="/manage-contract" method="post" class="row g-3">
                    @csrf

                    @if(!empty($_GET['id']))
                        <input type="hidden" name="id" value="{{ $_GET['id'] }}">
                    @elseif(!empty($contract->id))
                        <input type="hidden" name="id" value="{{ $contract->id }}">
                    @endif
                    <input type="hidden" name="previous_url" value="{{ $previous_url ?? url()->previous() }}">

                    <!-- Client -->
                    <div class="col-md-6">
                        <label for="client_id" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Select Client <span class="text-danger">*</span></label>
                        <select class="selectpicker form-select d-block w-100" id="client_id" name="client_id" data-live-search="true" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @if($client->id == ($contract->client_id ?? '')) selected @endif>
                                    {{ $client->name ?? 'Unnamed Client' }} {{ $client->company ? '('.$client->company.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Subject -->
                    <div class="col-md-6">
                        <label for="subject" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject"
                               value="{{ old('subject', $contract->subject ?? '') }}" required>
                        @error('subject') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Contract Type -->
                    <div class="col-md-6">
                        <label for="contract_type" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Contract Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="contract_type" name="contract_type" required>
                            <option value="">Select Type</option>
                            <option value="domain" {{ old('contract_type', $contract->contract_type ?? '') === 'domain' ? 'selected' : '' }}>Domain Renewal</option>
                            <option value="hosting" {{ old('contract_type', $contract->contract_type ?? '') === 'hosting' ? 'selected' : '' }}>Hosting Renewal</option>
                            <option value="domain-hosting" {{ old('contract_type', $contract->contract_type ?? '') === 'domain-hosting' ? 'selected' : '' }}>Domain + Hosting Renewal</option>
                            <option value="hosting-email" {{ old('contract_type', $contract->contract_type ?? '') === 'hosting-email' ? 'selected' : '' }}>Hosting + Email Renewal</option>
                            <option value="hosting-webmail" {{ old('contract_type', $contract->contract_type ?? '') === 'hosting-webmail' ? 'selected' : '' }}>Hosting + webmail Renewal</option>
                            <option value="domain-hosting-email" {{ old('contract_type', $contract->contract_type ?? '') === 'domain-hosting-email' ? 'selected' : '' }}>Domain + Hosting + Email Renewal</option>
                            <option value="seo" {{ old('contract_type', $contract->contract_type ?? '') === 'seo' ? 'selected' : '' }}>SEO</option>
                            <option value="new" {{ old('contract_type', $contract->contract_type ?? '') === 'new' ? 'selected' : '' }}>New...</option>
                        </select>
                        @error('contract_type') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Custom Contract Type -->
                    <div class="col-md-6" id="custom_contract_type_container" style="{{ $showCustom ? '' : 'display: none;' }}">
                        <label for="custom_contract_type" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Enter New Contract Type</label>
                        <input type="text" class="form-control" id="custom_contract_type" name="custom_contract_type"
                               value="{{ old('custom_contract_type', '') }}">
                        @error('custom_contract_type') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Value -->
                    <div class="col-md-6">
                        <label for="value" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Contract Value (₹)</label>
                        <input type="number" step="0.01" class="form-control" id="value" name="value"
                               value="{{ old('value', $contract->value ?? '') }}">
                        @error('value') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Start Date -->
                    <div class="col-md-6">
                        <label for="start_date" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="{{ old('start_date', !empty($contract->start_date) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : '') }}" required>
                        @error('start_date') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- End Date -->
                    <div class="col-md-6">
                        <label for="end_date" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="{{ old('end_date', !empty($contract->end_date) ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '') }}">
                        @error('end_date') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label for="description" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Description / Scope</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $contract->des ?? '') }}</textarea>
                        @error('description') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-12 mt-4 pt-3 border-top text-end">
                        @if(request()->has('ajax'))
                            <button type="button" class="btn btn-light rounded-pill border px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                        @else
                            <a href="/contracts" class="btn btn-light rounded-pill border px-4 me-2">Cancel</a>
                        @endif
                        <button type="submit" class="btn btn-primary rounded-pill px-4" style="background:#006666; border:none;">
                            {{ isset($contract) && $contract->exists ? 'Update Contract' : 'Save Contract' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

@if(request()->has('ajax'))
    </div>

    {{-- Script execution when inserted via AJAX --}}
    <script>
        setTimeout(() => {
            // Re-bind bootstrap selectpicker for dropdowns
            if($.fn.selectpicker){
                $('#client_id').selectpicker();
            }
            
            const typeDropdown = document.getElementById('contract_type');
            const customTypeContainer = document.getElementById('custom_contract_type_container');
            const customTypeInput = document.getElementById('custom_contract_type');

            function toggleCustomField() {
                if (!typeDropdown || !customTypeContainer || !customTypeInput) return;
                
                if (typeDropdown.value === 'new') {
                    customTypeContainer.style.display = 'block';
                    customTypeInput.required = true;
                } else {
                    customTypeContainer.style.display = 'none';
                    customTypeInput.required = false;
                }
            }

            if(typeDropdown){
                toggleCustomField(); // Initialize on load
                typeDropdown.addEventListener('change', toggleCustomField);
            }
        }, 500);
    </script>
@else
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if($.fn.select2){
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select',
                allowClear: true
            });
        }

        const typeDropdown = document.getElementById('contract_type');
        const customTypeContainer = document.getElementById('custom_contract_type_container');
        const customTypeInput = document.getElementById('custom_contract_type');

        function toggleCustomField() {
            if (!typeDropdown || !customTypeContainer || !customTypeInput) return;

            if (typeDropdown.value === 'new') {
                customTypeContainer.style.display = 'block';
                customTypeInput.required = true;
            } else {
                customTypeContainer.style.display = 'none';
                customTypeInput.required = false;
            }
        }

        if(typeDropdown){
            toggleCustomField(); // Initialize on load
            typeDropdown.addEventListener('change', toggleCustomField);
        }
    });
</script>

@endsection
@endif
