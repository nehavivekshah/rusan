@extends('layout')
@section('title', 'Manage Proposal - eseCRM')

@section('content')
@php $taxes = !empty($companies->tax) ? explode(',', $companies->tax) : []; @endphp

<section class="task__section">
    @include('inc.header', ['title' => empty($proposal->id) ? 'Create Proposal' : 'Edit Proposal'])

    <div class="dash-container">

        {{-- ── Page heading bar ── --}}
        <div class="leads-toolbar mb-3">
            <div class="leads-toolbar-left gap-3">
                <a href="{{ $previous_url ?? '/proposals' }}" class="btn kb-action-btn" title="Back to Proposals"
                   style="width:34px;height:34px;background:#f1f3f4;color:#5f6368;">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <div>
                    @if(!empty($proposal->id))
                        <span class="lb-page-count"><i class="bx bx-edit"></i> Edit Proposal</span>
                        <span class="pr-id-badge ms-2">PRO-{{ str_pad($proposal->id, 4, '0', STR_PAD_LEFT) }}</span>
                    @else
                        <span class="lb-page-count"><i class="bx bx-plus-circle"></i> Create New Proposal</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Progress steps ── --}}
        <div class="mp-steps mb-4">
            <div class="mp-step mp-step-done">
                <span class="mp-step-num"><i class="bx bx-check" style="font-size:0.8rem;"></i></span>
                <span class="mp-step-label">Proposal Info</span>
            </div>
            <div class="mp-step-line mp-line-done"></div>
            <div class="mp-step mp-step-done">
                <span class="mp-step-num"><i class="bx bx-check" style="font-size:0.8rem;"></i></span>
                <span class="mp-step-label">Client Details</span>
            </div>
            <div class="mp-step-line"></div>
            <div class="mp-step mp-step-active">
                <span class="mp-step-num">3</span>
                <span class="mp-step-label">Items &amp; Summary</span>
            </div>
        </div>

        <form id="proposalForm" action="/manage-proposal" method="post">
            @csrf
            <input type="hidden" name="id" id="id" value="{{ $proposal->id ?? '' }}">
            <input type="hidden" name="previous_url" value="{{ $previous_url ?? url()->previous() }}">



            <div class="row g-4 align-items-start">

                {{-- ══ LEFT — Form sections ══ --}}
                <div class="col-lg-12">

                    {{-- ── Proposal Information ── --}}
                    <div class="ml-card mb-4">
                        <div class="ml-card-header">
                            <div class="ml-card-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                                <i class="bx bx-file"></i>
                            </div>
                            <div>
                                <h6 class="ml-card-title">Proposal Information</h6>
                                <span class="ml-card-sub">Subject, dates, currency & discount</span>
                            </div>
                        </div>
                        <div class="ml-card-body">
                            <div class="row g-3">
                                {{-- Subject --}}
                                <div class="col-md-6">
                                    <label class="ml-label">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-rename"></i></span>
                                        <input type="text" name="subject" id="subject" class="form-control"
                                               placeholder="e.g. Website Redesign Proposal"
                                               value="{{ $proposal->subject ?? '' }}" required>
                                    </div>
                                    <div class="form-text">Short descriptive subject line</div>
                                </div>

                                {{-- Related type --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Related To</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-link"></i></span>
                                        <select name="related" id="related" class="form-select" required>
                                            <option value="1" {{ ( ($proposal->related ?? '') == '1' || (isset($preloadProject)) ) ? '' : 'selected' }}>Lead</option>
                                            <option value="2" {{ ( ($proposal->related ?? '') == '2' || (isset($preloadProject)) ) ? 'selected' : '' }}>Client</option>
                                        </select>
                                    </div>
                                    <div class="form-text">Link proposal to a lead or client</div>
                                </div>

                                {{-- Related list --}}
                                <div class="col-md-3">
                                    <label class="ml-label" id="proposalType">{{ (isset($preloadProject) || ($proposal->related ?? '') == '2') ? 'Clients List' : 'Leads List' }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-list-ul"></i></span>
                                        @if(isset($preloadProject) || ($proposal->related ?? '') == '2')
                                            <select name="lead_id" id="relatedList" class="form-select selectpicker" data-live-search="true">
                                                <option value="">Select…</option>
                                                @foreach($clients as $lead)
                                                    <option value="{{ $lead->id ?? '' }}"
                                                        data-name="{{ $lead->name ?? '' }}"
                                                        data-company="{{ $lead->company ?? '' }}"
                                                        data-email="{{ $lead->email ?? '' }}"
                                                        data-mob="{{ $lead->mob ?? '' }}"
                                                        data-loc-raw="{{ $lead->location ?? '' }}"
                                                        {{ ( ($proposal->lead_id ?? '') == ($lead->id ?? '') || (isset($preloadProject) && $preloadProject->client_id == ($lead->id ?? '')) ) ? 'selected' : '' }}>
                                                        {{ $lead->name ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <select name="lead_id" id="relatedList"
                                                    class="selectpicker form-select" data-live-search="true">
                                                <option value="">Select…</option>
                                                @foreach($leads as $lead)
                                                    <option value="{{ $lead->id ?? '' }}"
                                                        data-name="{{ $lead->name ?? '' }}"
                                                        data-company="{{ $lead->company ?? '' }}"
                                                        data-email="{{ $lead->email ?? '' }}"
                                                        data-mob="{{ $lead->mob ?? '' }}"
                                                        data-loc-raw="{{ $lead->location ?? '' }}"
                                                        @if(($proposal->lead_id ?? '') == ($lead->id ?? '')) selected @endif>
                                                        {{ $lead->name ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div class="form-text">Auto-fills client details below</div>
                                </div>

                                {{-- Proposal date --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Proposal Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                        <input type="date" name="proposal_date" id="proposalDate" class="form-control"
                                               value="{{ $proposal->proposal_date ?? date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                {{-- Valid till --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Valid Till</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                        <input type="date" name="open_till" id="openTill" class="form-control"
                                               value="{{ $proposal->open_till ?? \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}">
                                    </div>
                                </div>

                                {{-- Currency --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Currency <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-money"></i></span>
                                        <select name="currency" id="currency" class="form-select" required>
                                            <option value="INR" @if(($proposal->currency ?? '') == 'INR') selected @endif>₹ INR</option>
                                            <option value="USD" @if(($proposal->currency ?? '') == 'USD') selected @endif>$ USD</option>
                                            <option value="EUR" @if(($proposal->currency ?? '') == 'EUR') selected @endif>€ EUR</option>
                                            <option value="GBP" @if(($proposal->currency ?? '') == 'GBP') selected @endif>£ GBP</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Discount type --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Discount Applied On</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-purchase-tag"></i></span>
                                        <select name="discount_type" id="discountType" class="form-select">
                                            <option value="none" @if(($proposal->discount_type ?? '') == 'none') selected @endif>No Discount</option>
                                            <option value="before-tax" @if(($proposal->discount_type ?? '') == 'before-tax') selected @endif>Before Tax</option>
                                            <option value="after-tax" @if(($proposal->discount_type ?? '') == 'after-tax') selected @endif>After Tax</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Tags --}}
                                <div class="col-md-6">
                                    <label class="ml-label">Tags</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-tag"></i></span>
                                        <input type="text" name="tags" id="tags" class="form-control"
                                               placeholder="Enter Tags (comma separated)"
                                               value="{{ $proposal->tags ?? '' }}">
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div class="col-12">
                                    <label class="ml-label">Proposal Notes</label>
                                    <textarea name="notes" id="editor" class="form-control" rows="3"
                                              placeholder="Add internal notes…">{{ $proposal->notes ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Client Details (collapsible) ── --}}
                    <div class="ml-card mb-4">
                        <div class="ml-card-header" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#clientDetailsBody">
                            <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                <i class="bx bx-user-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="ml-card-title">Client Details</h6>
                                <span class="ml-card-sub">Auto-filled when you pick a lead/client above</span>
                            </div>
                            <i class="bx bx-chevron-down" style="color:#9aa0a6;font-size:1.1rem;"></i>
                        </div>
                        @php
                            $p_addr = ''; $p_city = ''; $p_state = ''; $p_country = ''; $p_zip = '';
                            if(isset($preloadProject) && !empty($preloadProject->client_location)) {
                                try {
                                    $loc = json_decode($preloadProject->client_location, true);
                                    if(is_array($loc)) {
                                        if(isset($loc['address'])) {
                                            $p_addr = $loc['address']; $p_city = $loc['city'] ?? ''; $p_state = $loc['state'] ?? ''; $p_country = $loc['country'] ?? ''; $p_zip = $loc['zip'] ?? '';
                                        } else {
                                            $p_addr = $loc[0] ?? ''; $p_city = $loc[1] ?? ''; $p_state = $loc[2] ?? ''; $p_country = $loc[3] ?? ''; $p_zip = $loc[4] ?? '';
                                        }
                                    }
                                } catch(\Exception $e) { $p_addr = $preloadProject->client_location; }
                            }
                        @endphp
                        <div class="collapse {{ (isset($preloadProject) || !empty($proposal->id)) ? 'show' : '' }} ml-card-body" id="clientDetailsBody">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="ml-label">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                                        <input type="text" name="client_name" id="clientName" class="form-control"
                                               placeholder="Client Name"
                                               value="{{ $proposal->client_name ?? ($preloadProject->client_name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="ml-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                        <input type="email" name="client_email" id="clientEmail" class="form-control"
                                               placeholder="client@example.com"
                                               value="{{ $proposal->client_email ?? ($preloadProject->client_email ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="ml-label">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                        <input type="tel" name="client_phone" id="clientPhone" class="form-control"
                                               placeholder="+91 XXXXX XXXXX"
                                               value="{{ $proposal->client_phone ?? ($preloadProject->client_mob ?? '91') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="ml-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-home"></i></span>
                                        <input type="text" name="client_address" id="clientAddress" class="form-control"
                                               placeholder="Street address"
                                               value="{{ $proposal->client_address ?? ($p_addr ?: '') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="ml-label">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-map"></i></span>
                                        <input type="text" name="client_city" id="clientCity" class="form-control"
                                               placeholder="City"
                                               value="{{ $proposal->client_city ?? ($p_city ?: '') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="ml-label">State / Province</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                                        <input type="text" name="client_state" id="clientState" class="form-control"
                                               placeholder="State"
                                               value="{{ $proposal->client_state ?? ($p_state ?: '') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="ml-label">Zip / Postal Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-pin"></i></span>
                                        <input type="text" name="client_zip" id="clientZip" class="form-control"
                                               placeholder="Postal code"
                                               value="{{ $proposal->client_zip ?? ($p_zip ?: '') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="ml-label">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                        <input type="text" name="client_country" id="clientCountry" class="form-control"
                                               placeholder="Country"
                                               value="{{ $proposal->client_country ?? ($p_country ?: '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Line Items ── --}}
                    <div class="ml-card mb-4">
                        <div class="ml-card-header">
                            <div class="ml-card-icon" style="background:rgba(242,153,0,0.10);color:#f29900;">
                                <i class="bx bx-list-ul"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="ml-card-title">Items &amp; Summary</h6>
                                <span class="ml-card-sub">Add line items — totals update automatically</span>
                            </div>
                            <button type="button" class="lb-btn add-item-btn"
                                    style="background:rgba(0,102,102,0.08);color:#006666;">
                                <i class="bx bx-plus"></i> Add Item
                            </button>
                        </div>
                        <div class="ml-card-body p-3">
                            {{-- Items container --}}
                            <div id="items-card-container">
                                @if(count($proposalItems) > 0)
                                    @foreach($proposalItems as $k => $proposalItem)
                                    <div class="mp-item-row" data-item-row="{{ $k }}">
                                        <div class="mp-item-row-header">
                                            <span class="mp-item-num">{{ $k + 1 }}</span>
                                            <span class="mp-item-row-title">Item {{ $k + 1 }}</span>
                                            <button type="button" class="btn kb-action-btn kb-action-del remove-item-btn ms-auto"
                                                    style="width:28px;height:28px;" title="Remove item">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                        <div class="mp-item-row-body">
                                            <div class="mp-item-field mp-item-name-field">
                                                <label class="mp-item-label">Item Name</label>
                                                <textarea class="form-control form-control-sm item-name mp-autoresize"
                                                          name="proposal_items[{{ $k }}][item_name]"
                                                          placeholder="e.g. Web Development"
                                                          rows="1">{{ $proposalItem->item_name ?? '' }}</textarea>
                                            </div>
                                            <div class="mp-item-field mp-item-desc-field">
                                                <label class="mp-item-label">Description</label>
                                                <textarea class="form-control form-control-sm item-description mp-autoresize"
                                                          name="proposal_items[{{ $k }}][description]"
                                                          placeholder="Optional details…"
                                                          rows="1">{{ $proposalItem->description ?? '' }}</textarea>
                                            </div>
                                            <div class="mp-item-field mp-item-qty-field">
                                                <label class="mp-item-label">Qty</label>
                                                <input type="number" class="form-control form-control-sm item-qty text-center"
                                                       name="proposal_items[{{ $k }}][quantity]"
                                                       value="{{ $proposalItem->quantity ?? 1 }}" min="1">
                                            </div>
                                            <div class="mp-item-field mp-item-rate-field">
                                                <label class="mp-item-label">Rate (₹)</label>
                                                <input type="number" class="form-control form-control-sm item-rate text-end"
                                                       name="proposal_items[{{ $k }}][rate]"
                                                       placeholder="0.00"
                                                       value="{{ $proposalItem->rate ?? '' }}">
                                            </div>
                                            <div class="mp-item-field mp-item-tax-field">
                                                <label class="mp-item-label">Tax</label>
                                                <select class="selectpicker form-control form-control-sm item-tax" multiple
                                                        data-selected-text-format="count > 2"
                                                        data-container="body"
                                                        name="proposal_items[{{ $k }}][tax_percentage][]" title="No Tax">
                                                    @foreach($taxes as $index => $tax)
                                                        @php $calTax = ($tax ?? 0) / 100; @endphp
                                                        @if($index == 0)
                                                            <option value="{{ $index . ':' . $calTax }}"
                                                                @if(($proposalItem->cgst_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                CGST {{ $tax ?? 0 }} %</option>
                                                        @elseif($index == 1)
                                                            <option value="{{ $index . ':' . $calTax }}"
                                                                @if(($proposalItem->sgst_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                SGST {{ $tax ?? 0 }} %</option>
                                                        @elseif($index == 2)
                                                            <option value="{{ $index . ':' . $calTax }}"
                                                                @if(($proposalItem->igst_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                IGST {{ $tax ?? 0 }} %</option>
                                                        @elseif($index == 3)
                                                            <option value="{{ $index . ':' . $calTax }}"
                                                                @if(($proposalItem->vat_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                VAT {{ $tax ?? 0 }} %</option>
                                                        @else
                                                            <option value="{{ $index . ':' . $calTax }}">{{ $tax ?? 0 }} %</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mp-item-field mp-item-amount-field">
                                                <label class="mp-item-label">Amount</label>
                                                <span class="item-amount mp-item-amount-val">
                                                    ₹{{ number_format(($proposalItem->rate ?? 0) * ($proposalItem->quantity ?? 0), 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="mp-item-row" data-item-row="0">
                                        <div class="mp-item-row-header">
                                            <span class="mp-item-num">1</span>
                                            <span class="mp-item-row-title">Item 1</span>
                                            <button type="button" class="btn kb-action-btn kb-action-del remove-item-btn ms-auto"
                                                    style="width:28px;height:28px;" title="Remove item">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                        <div class="mp-item-row-body">
                                            <div class="mp-item-field mp-item-name-field">
                                                <label class="mp-item-label">Item Name</label>
                                                <textarea class="form-control form-control-sm item-name mp-autoresize"
                                                          name="proposal_items[0][item_name]"
                                                          placeholder="e.g. Web Development"
                                                          rows="1" required></textarea>
                                            </div>
                                            <div class="mp-item-field mp-item-desc-field">
                                                <label class="mp-item-label">Description</label>
                                                <textarea class="form-control form-control-sm item-description mp-autoresize"
                                                          name="proposal_items[0][description]"
                                                          placeholder="Optional details…"
                                                          rows="1"></textarea>
                                            </div>
                                            <div class="mp-item-field mp-item-qty-field">
                                                <label class="mp-item-label">Qty</label>
                                                <input type="number" class="form-control form-control-sm item-qty text-center"
                                                       name="proposal_items[0][quantity]"
                                                       value="1" min="1">
                                            </div>
                                            <div class="mp-item-field mp-item-rate-field">
                                                <label class="mp-item-label">Rate (₹)</label>
                                                <input type="number" class="form-control form-control-sm item-rate text-end"
                                                       name="proposal_items[0][rate]"
                                                       placeholder="0.00" required>
                                            </div>
                                            <div class="mp-item-field mp-item-tax-field">
                                                <label class="mp-item-label">Tax</label>
                                                <select class="selectpicker form-control form-control-sm item-tax" multiple
                                                        data-selected-text-format="count > 2"
                                                        data-container="body"
                                                        name="proposal_items[0][tax_percentage][]" title="No Tax">
                                                    @foreach($taxes as $index => $tax)
                                                        @php $calTax = ($tax ?? 0) / 100; @endphp
                                                        <option value="{{ $index . ':' . $calTax }}">{{ $tax ?? 0 }} %</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mp-item-field mp-item-amount-field">
                                                <label class="mp-item-label">Amount</label>
                                                <span class="item-amount mp-item-amount-val">₹0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" class="mp-add-item-btn add-item-btn mt-3">
                                <i class="bx bx-plus"></i> Add Another Item
                            </button>
                        </div>
                    </div>

                </div>{{-- /col-lg-8 --}}

                {{-- ══ RIGHT — Sticky Summary + Actions ══ --}}
                <div class="col-lg-4 offset-lg-8">
                    <div class="mp-sidebar-sticky">

                        {{-- Summary card --}}
                        <div class="ml-card mb-3">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                                    <i class="bx bx-calculator"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Summary</h6>
                                    <span class="ml-card-sub">Live totals as you type</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="mp-summary-row">
                                    <span class="mp-summary-label">Sub Total</span>
                                    <span id="sub-total" class="mp-summary-val">&#x20B9;{{ $proposal->sub_total ?? '0.00' }}</span>
                                    <input type="hidden" name="sub_total" id="sub-total1"
                                           value="{{ $proposal->sub_total ?? 0.00 }}">
                                </div>
                                <div class="mp-summary-row">
                                    <span class="mp-summary-label">
                                        Discount (<span id="discount-type-display" class="fst-italic">None</span>)
                                    </span>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" class="form-control form-control-sm text-end mp-disc-input"
                                               name="discount_percentage" id="discountValue"
                                               value="{{ $proposal->discount_percentage ?? 0 }}"
                                               placeholder="%" step="0.01" min="0">
                                        <span id="discount-total" class="mp-summary-val text-danger">
                                            &#x20B9;{{ $proposal->discount_amount_calculated ?? '0.00' }}
                                        </span>
                                        <input type="hidden" name="discount_amount_calculated" id="discount-total1"
                                               value="{{ $proposal->discount_amount_calculated ?? 0.00 }}">
                                    </div>
                                </div>
                                <div class="mp-summary-row mp-tax-row">
                                    <span class="mp-summary-label text-muted">CGST</span>
                                    <span id="cgst-total" class="mp-summary-val text-muted">&#x20B9;{{ $proposal->cgst_total ?? '0.00' }}</span>
                                    <input type="hidden" name="cgst_total" id="cgst-total1" value="{{ $proposal->cgst_total ?? 0.00 }}">
                                </div>
                                <div class="mp-summary-row mp-tax-row">
                                    <span class="mp-summary-label text-muted">SGST</span>
                                    <span id="sgst-total" class="mp-summary-val text-muted">&#x20B9;{{ $proposal->sgst_total ?? '0.00' }}</span>
                                    <input type="hidden" name="sgst_total" id="sgst-total1" value="{{ $proposal->sgst_total ?? 0.00 }}">
                                </div>
                                <div class="mp-summary-row mp-tax-row">
                                    <span class="mp-summary-label text-muted">IGST</span>
                                    <span id="igst-total" class="mp-summary-val text-muted">&#x20B9;{{ $proposal->igst_total ?? '0.00' }}</span>
                                    <input type="hidden" name="igst_total" id="igst-total1" value="{{ $proposal->igst_total ?? 0.00 }}">
                                </div>
                                <div class="mp-summary-row mp-tax-row">
                                    <span class="mp-summary-label text-muted">VAT</span>
                                    <span id="vat-total" class="mp-summary-val text-muted">&#x20B9;{{ $proposal->vat_total ?? '0.00' }}</span>
                                    <input type="hidden" name="vat_total" id="vat-total1" value="{{ $proposal->vat_total ?? 0.00 }}">
                                </div>
                                <div class="mp-summary-row">
                                    <span class="mp-summary-label">Adjustment</span>
                                    <input type="number" class="form-control form-control-sm text-end mp-disc-input"
                                           name="adjustment_amount" id="adjustment"
                                           value="{{ $proposal->adjustment_amount ?? 0 }}" step="0.01">
                                </div>
                                <div class="mp-summary-total">
                                    <span>Grand Total</span>
                                    <strong id="total" class="mp-grand-total-val">
                                        &#x20B9;{{ $proposal->grand_total ?? '0.00' }}
                                    </strong>
                                    <input type="hidden" name="grand_total" id="total1"
                                           value="{{ $proposal->grand_total ?? 0.00 }}">
                                </div>
                            </div>
                        </div>

                        {{-- Action buttons card --}}
                        <div class="ml-card">
                            <div class="ml-card-body">
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit" form="proposalForm" name="submit" value="Save & Send"
                                            class="lb-btn lb-btn-primary w-100 justify-content-center">
                                        <i class="bx bx-send"></i> Save &amp; Send
                                    </button>
                                    <button type="submit" form="proposalForm"
                                            class="lb-btn w-100 justify-content-center"
                                            style="background:#f1f3f4;color:#202124;">
                                        <i class="bx bx-save"></i> Save as Draft
                                    </button>
                                    @if(!empty($proposal->id))
                                    <a href="/quotation/{{ $proposal->id }}/{{ md5($proposal->client_email ?? '') }}"
                                       class="lb-btn w-100 justify-content-center" target="_blank"
                                       style="background:rgba(26,115,232,0.08);color:#1a73e8;">
                                        <i class="bx bx-show"></i> View Proposal
                                    </a>
                                    @endif
                                    <a href="{{ $previous_url ?? '/proposals' }}" class="lb-btn w-100 justify-content-center"
                                       style="background:transparent;color:#9aa0a6;border:1px solid #e8eaed;">
                                        <i class="bx bx-x"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>{{-- /col-lg-4 sticky sidebar --}}

            </div>{{-- /row --}}
        </form>

    </div>
</section>

<script>
    // ── Auto-resize textareas ──
    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('mp-autoresize')) autoResize(e.target);
    });
    document.querySelectorAll('.mp-autoresize').forEach(autoResize);

    // ── Lead/Client autofill ──
    const leadList = document.getElementById('relatedList');
    if (leadList) {
        leadList.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            document.getElementById('clientName').value    = opt.getAttribute('data-name')    || '';
            document.getElementById('clientEmail').value   = opt.getAttribute('data-email')   || '';
            document.getElementById('clientPhone').value   = opt.getAttribute('data-mob')     || '';
            
            // Robust Address Parsing
            const rawLoc = opt.getAttribute('data-loc-raw') || '';
            let address = '', city = '', state = '', zip = '', country = '';
            try {
                const loc = JSON.parse(rawLoc);
                if (Array.isArray(loc)) {
                    address = loc[0] || '';
                    city    = loc[1] || '';
                    state   = loc[2] || '';
                    country = loc[3] || '';
                    zip     = loc[4] || '';
                } else if (typeof loc === 'object' && loc !== null) {
                    address = loc.address || '';
                    city    = loc.city    || '';
                    state   = loc.state   || '';
                    country = loc.country || '';
                    zip     = loc.zip     || '';
                }
            } catch (e) {
                address = rawLoc;
            }

            document.getElementById('clientAddress').value = address;
            document.getElementById('clientCity').value    = city;
            document.getElementById('clientState').value   = state;
            document.getElementById('clientZip').value     = zip;
            document.getElementById('clientCountry').value = country;
            
            // Expand client details if collapsed
            const body = document.getElementById('clientDetailsBody');
            if (body && !body.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(body) || new bootstrap.Collapse(body, { show: false });
                bsCollapse.show();
            }
        });
    }

    // ── Auto-populate from Project Context ──
    @if(request()->has('project_id') && !empty($preloadProject))
        window.addEventListener('load', function() {
            console.log("Auto-populating proposal details from project context...");
            
            const subjectField = document.getElementById('subject');
            if(subjectField && !subjectField.value) {
                subjectField.value = "Proposal for " + @json($preloadProject->name);
            }

            const relatedSelect = document.getElementById('related');
            if(relatedSelect) {
                relatedSelect.value = "2"; // Set to 'Client'
                
                // Fetch the client list first
                updateRelatedList("2", function() {
                    // After list is updated, select the specific client
                    const clientSelect = document.getElementById('relatedList');
                    if(clientSelect) {
                        $(clientSelect).val(@json($preloadProject->client_id));
                        $(clientSelect).trigger('change');
                        if($(clientSelect).hasClass('selectpicker')) {
                            $(clientSelect).selectpicker('refresh');
                        }
                    }

                    // Pre-fill Items & Summary
                    const itemNameField = document.querySelector('textarea[name="proposal_items[0][item_name]"]');
                    const itemRateField = document.querySelector('input[name="proposal_items[0][rate]"]');
                    
                    if (itemNameField && !itemNameField.value) {
                        itemNameField.value = @json($preloadProject->name);
                    }
                    if (itemRateField && !itemRateField.value) {
                        itemRateField.value = @json($preloadProject->amount);
                    }

                    // Trigger calculations by firing input event on rate field
                    if (itemRateField) {
                        const inputEvent = new Event('input', { bubbles: true });
                        itemRateField.dispatchEvent(inputEvent);
                    }
                });
            }
        });
    @endif

    function updateRelatedList(relatedValue, callback) {
        const $related = $('#relatedList').empty().append('<option value="">Select\u2026</option>');
        const map = {
            '1': { text: 'Leads List',   url: '/leads-list',   key: 'leads'   },
            '2': { text: 'Clients List', url: '/clients-list', key: 'clients' }
        };
        const cfg = map[relatedValue];
        if (!cfg) return;
        $('#proposalType').text(cfg.text);
        $.get(cfg.url).done(resp => {
            let items;
            try { items = JSON.parse(resp)[cfg.key] ?? []; if (!Array.isArray(items)) throw 0; }
            catch (e) { $related.append('<option value="">Error loading data</option>'); return; }
            items.forEach(item => {
                $('<option>', {
                    value: item.id, text: item.name,
                    'data-name': item.name, 'data-company': item.company,
                    'data-email': item.email, 'data-mob': item.mob,
                    'data-loc-raw': item.location || ''
                }).appendTo($related);
            });
            $related.selectpicker('refresh');
            if (typeof callback === 'function') callback();
        }).fail(() => { 
            $related.append('<option value="">Error loading data</option>'); 
            $related.selectpicker('refresh'); 
            if (typeof callback === 'function') callback();
        });
    }
    document.getElementById('related').addEventListener('change', function () { updateRelatedList(this.value); });
    window.onload = function () { updateRelatedList(document.getElementById('related').value); };

    document.addEventListener('DOMContentLoaded', function () {
        const container       = document.getElementById('items-card-container');
        const currencySelect  = document.getElementById('currency');
        const adjustmentInput = document.getElementById('adjustment');
        const discountTypeSelect  = document.getElementById('discountType');
        const discountValueInput  = document.getElementById('discountValue');
        const discountTypeDisplay = document.getElementById('discount-type-display');
        const discountTotalDisplay  = document.getElementById('discount-total');
        const discountTotalDisplay1 = document.getElementById('discount-total1');

        function formatCurrency(amount, code = 'INR') {
            try {
                return new Intl.NumberFormat(code === 'INR' ? 'en-IN' : undefined, { style: 'currency', currency: code }).format(amount);
            } catch (e) {
                return ({ INR: '₹', USD: '$', EUR: '€', GBP: '£' }[code] || '') + amount.toFixed(2);
            }
        }

        function renumberItems() {
            container.querySelectorAll('.mp-item-row').forEach((row, i) => {
                const badge = row.querySelector('.mp-item-num');
                const title = row.querySelector('.mp-item-row-title');
                if (badge) badge.textContent = i + 1;
                if (title) title.textContent = 'Item ' + (i + 1);
                row.querySelectorAll('input, textarea, select').forEach(el => {
                    if (!el.name) return;
                    el.name = el.name.replace(/proposal_items\[\d+\]/, `proposal_items[${i}]`);
                });
            });
        }

        function calculateTotals() {
            try {
                const code      = currencySelect.value || 'INR';
                const adj       = parseFloat(adjustmentInput.value) || 0;
                const discType  = discountTypeSelect.value;
                const discPct   = parseFloat(discountValueInput.value) || 0;
                let subTotal = 0, cgst = 0, sgst = 0, igst = 0, vat = 0;

                container.querySelectorAll('.mp-item-row').forEach(row => {
                    const qtyEl = row.querySelector('.item-qty');
                    const rateEl = row.querySelector('.item-rate');
                    if (!qtyEl || !rateEl) return;

                    const qty  = parseFloat(qtyEl.value)  || 0;
                    const rate = parseFloat(rateEl.value) || 0;
                    const line = qty * rate;
                    subTotal += line;

                    const amtEl = row.querySelector('.item-amount');
                    if (amtEl) amtEl.textContent = formatCurrency(line, code);

                    const taxSel = row.querySelector('.item-tax');
                    if (taxSel) {
                        // Use jQuery val() for robust multi-select retrieval
                        const selectedValues = $(taxSel).val() || [];
                        selectedValues.forEach(val => {
                            if (!val.includes(':')) return;
                            const [idx, pct] = val.split(':');
                            const t = line * (parseFloat(pct) || 0);
                            switch (+idx) { 
                                case 0: cgst += t; break; 
                                case 1: sgst += t; break; 
                                case 2: igst += t; break; 
                                case 3: vat += t; break; 
                            }
                        });
                    }
                });

                const taxTotal  = cgst + sgst + igst + vat;
                let   discountAmt = 0;
                if (discPct > 0) {
                    const base = discType === 'before-tax' ? subTotal : subTotal + taxTotal;
                    discountAmt = base * discPct / 100;
                }
                const grand = subTotal + taxTotal - discountAmt + adj;

                // Robust UI Updates
                const setSafeText = (id, text) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = text;
                };
                const setSafeValue = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val;
                };

                setSafeText('sub-total', formatCurrency(subTotal, code));
                setSafeValue('sub-total1', subTotal.toFixed(2));
                
                if (discountTypeDisplay && discountTypeSelect.selectedOptions.length > 0) {
                    discountTypeDisplay.textContent = discountTypeSelect.selectedOptions[0].text;
                }
                
                setSafeText('discount-total', '- ' + formatCurrency(discountAmt, code));
                setSafeValue('discount-total1', discountAmt.toFixed(2));
                
                setSafeText('cgst-total', formatCurrency(cgst, code));
                setSafeValue('cgst-total1', cgst.toFixed(2));
                setSafeText('sgst-total', formatCurrency(sgst, code));
                setSafeValue('sgst-total1', sgst.toFixed(2));
                setSafeText('igst-total', formatCurrency(igst, code));
                setSafeValue('igst-total1', igst.toFixed(2));
                setSafeText('vat-total', formatCurrency(vat, code));
                setSafeValue('vat-total1', vat.toFixed(2));
                
                setSafeText('total', formatCurrency(grand, code));
                setSafeValue('total1', grand.toFixed(2));
            } catch (err) {
                console.error("Calculation Error:", err);
            }
        }

        // ── Add Item ──
        document.querySelectorAll('.add-item-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const rows  = container.querySelectorAll('.mp-item-row');
                const last  = rows[rows.length - 1];
                const idx   = rows.length;
                const clone = last.cloneNode(true);
                clone.querySelector('.mp-item-num').textContent   = idx + 1;
                clone.querySelector('.mp-item-row-title').textContent = 'Item ' + (idx + 1);
                clone.querySelectorAll('input, textarea').forEach(el => {
                    el.value = el.classList.contains('item-qty') ? 1 : '';
                    if (el.classList.contains('mp-autoresize')) { el.style.height = 'auto'; }
                    if (el.name) el.name = el.name.replace(/proposal_items\[\d+\]/, `proposal_items[${idx}]`);
                });
                clone.querySelectorAll('select').forEach(sel => {
                    Array.from(sel.options).forEach(o => o.selected = false);
                    if (sel.name) sel.name = sel.name.replace(/proposal_items\[\d+\]/, `proposal_items[${idx}]`);
                });
                const amtEl = clone.querySelector('.item-amount');
                if (amtEl) amtEl.textContent = formatCurrency(0, currencySelect.value);
                // Aggressive Fix: Completely reconstruct the tax select field to avoid duplication
                const $taxField = $(clone).find('.mp-item-tax-field');
                const optionsHtml = $taxField.find('select').html(); // Preserve the <option> tags
                $taxField.empty().append('<label class="mp-item-label">Tax</label>')
                         .append($('<select>', {
                            class: 'selectpicker form-control form-control-sm item-tax',
                            multiple: true,
                            'data-selected-text-format': 'count > 2',
                            'data-container': 'body',
                            name: `proposal_items[${idx}][tax_percentage][]`,
                            title: 'No Tax'
                         }).append(optionsHtml));

                container.appendChild(clone);
                
                // Final Robust Init: Ensure only one selectpicker instance
                const $newSelects = $(clone).find('.selectpicker');
                $newSelects.each(function() {
                    $(this).selectpicker('destroy').selectpicker();
                });
                
                clone.querySelectorAll('.mp-autoresize').forEach(autoResize);
                clone.querySelector('.item-name')?.focus();
                calculateTotals();
            });
        });

        // ── Remove Item ──
        container.addEventListener('click', function (e) {
            if (e.target.closest('.remove-item-btn')) {
                const rows = container.querySelectorAll('.mp-item-row');
                if (rows.length > 1) {
                    e.target.closest('.mp-item-row').remove();
                    renumberItems();
                    calculateTotals();
                } else {
                    alert('You must have at least one item.');
                }
            }
        });

        // ── Live calculation triggers ──
        // Using jQuery delegation for robust plugin event capturing
        $(container).on('input', '.item-qty, .item-rate', calculateTotals);
        $(container).on('change', '.item-tax', calculateTotals);
        
        $(currencySelect).on('change', calculateTotals);
        $(adjustmentInput).on('input', calculateTotals);
        $(discountTypeSelect).on('change', calculateTotals);
        $(discountValueInput).on('input', calculateTotals);

        calculateTotals();
    });
</script>

<script>
    tinymce.init({
        selector: '#editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    });
</script>
@endsection
