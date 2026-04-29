@extends('layout')
@section('title', 'Manage Invoice - Rusan')

@section('content')
    <style>
        /* ── Existing layout styles ── */
        .mp-sidebar-sticky { position: sticky; top: 84px; z-index: 10; }
        .mp-item-row { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; padding: 1.25rem; margin-bottom: 1.25rem; position: relative; transition: all 0.2s; }
        .mp-item-row:hover { border-color: #163f7a; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .mp-item-row-header { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid #f1f3f4; padding-bottom: 10px; }
        .mp-item-num { width: 24px; height: 24px; background: #163f7a; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; }
        .mp-item-row-title { font-size: 0.88rem; font-weight: 700; color: #202124; }
        .mp-item-row-body { display: grid; grid-template-columns: 2fr 2fr 0.8fr 1.2fr 1.5fr 1.2fr; gap: 12px; }
        .mp-item-label { display: block; font-size: 0.68rem; font-weight: 700; color: #80868b; text-transform: uppercase; margin-bottom: 4px; }
        .mp-item-amount-val { font-size: 0.9rem; font-weight: 700; color: #202124; display: block; margin-top: 6px; }
        .mp-add-item-btn { width: 100%; padding: 12px; background: #fff; border: 1px dashed #ced4da; border-radius: 12px; color: #5f6368; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .mp-add-item-btn:hover { background: #f8f9fa; border-color: #163f7a; color: #163f7a; }
        .mp-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 0.85rem; }
        .mp-summary-label { color: #80868b; font-weight: 500; }
        .mp-summary-val { color: #202124; font-weight: 700; }
        .mp-summary-total { margin-top: 15px; padding-top: 15px; border-top: 2px solid #f1f3f4; display: flex; justify-content: space-between; align-items: center; }
        .mp-grand-total-val { font-size: 1.4rem; font-weight: 800; color: #163f7a; }
        .ml-card { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .ml-card-header { padding: 16px 20px; border-bottom: 1px solid #f1f3f4; display: flex; align-items: center; gap: 12px; }
        .ml-card-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .ml-card-title { font-size: 1rem; font-weight: 700; color: #202124; margin: 0; }
        .ml-card-sub { font-size: 0.72rem; color: #80868b; display: block; margin-top: 1px; font-weight: 400; }
        .ml-card-body { padding: 20px; }
        .ml-label { display: block; font-size: 0.75rem; font-weight: 400; color: #5f6368; margin-bottom: 6px; }
        .mp-steps { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 30px; }
        .mp-step { display: flex; align-items: center; gap: 8px; color: #dadce0; }
        .mp-step-active { color: #163f7a; }
        .mp-step-done { color: #163f7a; }
        .mp-step-num { width: 24px; height: 24px; border-radius: 50%; border: 2px solid currentColor; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
        .mp-step-label { font-size: 0.85rem; font-weight: 600; }
        .mp-step-line { height: 2px; width: 40px; background: #f1f3f4; border-radius: 2px; }
        .mp-line-done { background: #163f7a; }
        .mp-autoresize { resize: none; overflow: hidden; }
        .inv-status-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .bank-info-bg { background: #f8fdfd; border: 1.5px dashed rgba(22, 63, 122,.25); border-radius: 12px; padding: 16px; }
        @media (max-width: 1200px) { .mp-item-row-body { grid-template-columns: 1.5fr 1.5fr 0.8fr 1fr 1fr 1fr; } }
        @media (max-width: 991px) { .mp-sidebar-sticky { position: static; } .mp-item-row-body { grid-template-columns: 1fr 1fr; gap: 15px; } }
        @media (max-width: 575px) { .mp-item-row-body { grid-template-columns: 1fr; } }

        /* ── cf- Design System (matching Recovery & Contract forms) ── */
        .cf-input-box {
            display: flex; align-items: center;
            border: 1.5px solid #d1d5db; border-radius: 8px;
            background: #fff; overflow: hidden; height: 42px;
            transition: border-color .15s, box-shadow .15s;
        }
        .cf-input-box:focus-within { border-color: #163f7a; box-shadow: 0 0 0 3px rgba(22, 63, 122,.08); }
        .cf-icon {
            display: flex; align-items: center; justify-content: center;
            width: 38px; min-width: 38px; height: 100%;
            color: #163f7a; font-size: 1.05rem;
            border-right: 1.5px solid #e8eaed; background: #f8fdfd; flex-shrink: 0;
        }
        .cf-input-box input,
        .cf-input-box select {
            flex: 1; border: none !important; outline: none !important;
            box-shadow: none !important; background: transparent;
            font-size: .875rem; color: #202124; padding: 0 10px; height: 100%;
            appearance: none; -webkit-appearance: none; width: 100%; min-width: 0;
        }
        .cf-input-box select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
        }
        .cf-input-box input::placeholder { color: #9aa0a6; }
        .cf-input-box.cf-textarea-box { height: auto; align-items: flex-start; }
        .cf-input-box.cf-textarea-box .cf-icon { height: 42px; align-self: flex-start; }
        .cf-input-box.cf-textarea-box textarea {
            flex: 1; border: none !important; outline: none !important;
            box-shadow: none !important; background: transparent;
            font-size: .875rem; color: #202124; padding: 10px;
            resize: none; width: 100%;
        }
        /* client search wrapper keeps the + button */
        .cf-client-wrap {
            display: flex; align-items: center;
            border: 1.5px solid #d1d5db; border-radius: 8px;
            background: #fff; transition: border-color .15s, box-shadow .15s;
            min-height: 42px; position: relative;
            overflow: hidden;
        }
        .cf-client-wrap:focus-within { border-color: #163f7a; box-shadow: 0 0 0 3px rgba(22, 63, 122,.08); }
        .cf-client-wrap .cf-icon { border-right: 1.5px solid #e8eaed; height: 42px; flex-shrink: 0; }
        /* Native select fallback */
        .cf-client-wrap select { flex: 1; border: none !important; outline: none !important; box-shadow: none !important; background: transparent; font-size: .875rem; color: #202124; padding: 0 10px; height: 42px; }
        /* Bootstrap-Select (selectpicker) overrides */
        .cf-client-wrap .bootstrap-select { flex: 1; min-width: 0; border: none !important; }
        .cf-client-wrap .bootstrap-select > .dropdown-toggle {
            border: none !important; background: transparent !important;
            box-shadow: none !important; outline: none !important;
            font-size: .875rem; color: #202124 !important;
            height: 42px !important; width: 100%; text-align: left;
            padding: 0 10px; border-radius: 0 !important;
        }
        .cf-client-wrap .bootstrap-select > .dropdown-toggle:focus { outline: none !important; box-shadow: none !important; }
        .cf-client-wrap .bootstrap-select > .dropdown-toggle::after { margin-left: auto; }
        .cf-client-wrap .btn-add-client {
            height: 42px; padding: 0 14px; background: #163f7a;
            color: #fff; border: none; font-size: .9rem; cursor: pointer;
            display: flex; align-items: center; transition: background .15s;
            border-radius: 0 7px 7px 0; flex-shrink: 0;
        }
        .cf-client-wrap .btn-add-client:hover { background: #004e4e; }
        /* bank detail small inputs */
        .cf-bank-input { border: 1.5px solid #d1d5db !important; border-radius: 7px !important; font-size: .82rem !important; padding: 7px 10px !important; color: #202124; transition: border-color .15s, box-shadow .15s; height: 42px !important; }
        .cf-bank-input:focus { border-color: #163f7a !important; box-shadow: 0 0 0 3px rgba(22, 63, 122,.07) !important; outline: none; }
        /* summary small inputs */
        .cf-summary-select { border: 1.5px solid #d1d5db !important; border-radius: 7px !important; font-size: .82rem !important; padding: 6px 10px !important; background: #fff; }
        .cf-summary-select:focus { border-color: #163f7a !important; outline: none !important; }
        /* Contract-style modal header & footer */
        .cf-modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px;
            background: linear-gradient(135deg, #005757, #163f7a);
            border-radius: 16px 16px 0 0;
        }
        .cf-modal-header-title { font-size: .975rem; font-weight: 700; color: #fff; margin: 0; }
        .cf-modal-header-sub   { font-size: .73rem; color: rgba(255,255,255,.72); margin: 0; }
        .cf-modal-header .btn-close { filter: invert(1); opacity: .8; }
        .cf-modal-footer {
            padding: 12px 20px; border-top: 1px solid #e8eaed;
            display: flex; justify-content: flex-end; gap: 8px;
            background: #fff; border-radius: 0 0 16px 16px;
        }
        .cf-btn-cancel {
            font-size: .85rem; padding: 8px 20px; border-radius: 8px;
            border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
            cursor: pointer; transition: background .15s;
        }
        .cf-btn-cancel:hover { background: #f5f5f5; }
        .cf-btn-save {
            font-size: .85rem; font-weight: 600; padding: 8px 22px; border-radius: 8px;
            border: none; background: #163f7a; color: #fff;
            cursor: pointer; transition: background .15s;
            display: flex; align-items: center; gap: 5px;
        }
        .cf-btn-save:hover { background: #004e4e; }
        /* Action bar buttons */
        .inv-btn-draft {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; border-radius: 9px; font-size: .85rem; font-weight: 600;
            border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
            cursor: pointer; transition: all .15s; text-decoration: none;
        }
        .inv-btn-draft:hover { background: #f5f5f5; border-color: #bbb; color: #202124; }
        .inv-btn-save {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; border-radius: 9px; font-size: .85rem; font-weight: 600;
            border: none; background: #163f7a; color: #fff;
            cursor: pointer; transition: background .15s;
        }
        .inv-btn-save:hover { background: #004e4e; }
        .inv-btn-send {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; border-radius: 9px; font-size: .85rem; font-weight: 700;
            border: none; background: linear-gradient(135deg,#163f7a,#00a3a3); color: #fff;
            cursor: pointer; transition: filter .15s; box-shadow: 0 3px 10px rgba(22, 63, 122,.25);
        }
        .inv-btn-send:hover { filter: brightness(1.08); }
    </style>

    <section class="task__section">
        @include('inc.header', ['title' => !empty($invoice->id) ? 'Edit Invoice' : 'Create Invoice'])

        <div class="dash-container">
            <form id="invoiceForm" action="/manage-invoice" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $invoice->id ?? '' }}">
                <input type="hidden" name="previous_url" value="{{ $previous_url ?? url()->previous() }}">
                <input type="hidden" name="_action" id="invoiceAction" value="save">


            {{-- ── Page heading bar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left gap-3">
                    <a href="/invoices" class="btn kb-action-btn" title="Back to Invoices"
                       style="width:34px;height:34px;background:#f1f3f4;color:#5f6368;">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                    <div>
                        @if(!empty($invoice->id))
                            <span class="lb-page-count"><i class="bx bx-edit"></i> Edit Invoice</span>
                            <span class="inv-status-pill ms-2" style="background:#163f7a15;color:#163f7a;">
                                INV-{{ $invoice->invoice_number }}
                            </span>
                        @else
                            <span class="lb-page-count"><i class="bx bx-plus-circle"></i> Create New Invoice</span>
                        @endif
                    </div>
                </div>
                <div class="leads-toolbar-right gap-2">
                    {{-- Live Preview --}}
                    <button type="button" class="inv-btn-draft" id="previewInvoiceBtn" title="Preview Invoice PDF"
                            style="color:#1a73e8;border-color:rgba(26,115,232,0.3);background:rgba(26,115,232,0.05);">
                        <i class="bx bx-show"></i> Live Preview
                    </button>
                    {{-- Save Draft --}}
                    <button type="button" class="inv-btn-draft" id="btnSaveDraft" title="Save as draft — client won't be notified">
                        <i class="bx bx-file-blank"></i> Save Draft
                    </button>
                    {{-- Save Invoice --}}
                    <button type="button" class="inv-btn-save" id="btnSaveInvoice">
                        <i class="bx bx-save"></i> Save Invoice
                    </button>
                    {{-- Save & Send --}}
                    <button type="button" class="inv-btn-send" id="btnSaveAndSend" title="Save and email invoice to client">
                        <i class="bx bx-send"></i> Save &amp; Send
                    </button>
                </div>
            </div>

            {{-- ── Progress steps ── --}}
            <div class="mp-steps mb-4">
                <div class="mp-step mp-step-done">
                    <span class="mp-step-num"><i class="bx bx-check" style="font-size:0.8rem;"></i></span>
                    <span class="mp-step-label">Invoice Info</span>
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

                <div class="row">
                    {{-- ── Left Column: Main Form & Items ── --}}
                    <div class="col-lg-12">
                        {{-- ── Invoice Information ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(22, 63, 122,0.10);color:#163f7a;">
                                    <i class="bx bx-file"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Invoice Information</h6>
                                    <span class="ml-card-sub">Number, dates, type & status</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="ml-label">Invoice Number <span class="text-danger">*</span></label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-hash"></i></span>
                                            <input type="text" name="invoice_number" id="invoice_number"
                                                   value="{{ old('invoice_number', $invoice->invoice_number ?? '') }}" required placeholder="e.g. INV-001">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Type <span class="text-danger">*</span></label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-category"></i></span>
                                            <select name="invoice_type" required>
                                                <option value="invoice" @if(old('invoice_type', $invoice->invoice ?? '') == 'invoice') selected @endif>Invoice</option>
                                                <option value="proforma" @if(old('invoice_type', $invoice->invoice ?? '') == 'proforma') selected @endif>Proforma</option>
                                                <option value="tax" @if(old('invoice_type', $invoice->invoice ?? '') == 'tax') selected @endif>Tax Invoice</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Status</label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-info-circle"></i></span>
                                            <select name="status">
                                                <option value="unpaid" @if(old('status', $invoice->status ?? 'unpaid') == 'unpaid') selected @endif>Unpaid</option>
                                                <option value="paid" @if(old('status', $invoice->status ?? '') == 'paid') selected @endif>Paid</option>
                                                <option value="partial" @if(old('status', $invoice->status ?? '') == 'partial') selected @endif>Partial</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Invoice Date <span class="text-danger">*</span></label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-calendar"></i></span>
                                            <input type="date" name="date"
                                                   value="{{ old('date', $invoice && $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Due Date</label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-calendar-check"></i></span>
                                            <input type="date" name="due_date"
                                                   value="{{ old('due_date', $invoice && $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Reference / PO #</label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-note"></i></span>
                                            <input type="text" name="reference" id="invoiceReference"
                                                   placeholder="Order or Reference Number"
                                                   value="{{ old('reference', $invoice->reference ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Client & Billing ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                    <i class="bx bx-user-pin"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Client & Billing</h6>
                                    <span class="ml-card-sub">Select client and manage address details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="ml-label">Select Client <span class="text-danger">*</span></label>
                                        <div class="cf-client-wrap">
                                            <span class="cf-icon"><i class="bx bx-user-pin"></i></span>
                                            <select class="selectpicker" id="client_id" name="client_id"
                                                    data-live-search="true" data-width="100%"
                                                    data-container="body" required>
                                                <option value="">Search for a client...</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}"
                                                            data-name="{{ $client->name ?? '' }}"
                                                            data-company="{{ $client->company ?? '' }}"
                                                            data-email="{{ $client->email ?? '' }}"
                                                            data-phone="{{ $client->mob ?? '' }}"
                                                            data-gstno="{{ $client->gstno ?? '' }}"
                                                            data-loc-raw="{{ $client->location ?? '' }}"
                                                            @if(
                                                                old('client_id', $invoice->client_id ?? '') == $client->id
                                                                || (!empty($preloadClient) && $preloadClient->id == $client->id)
                                                            ) selected @endif>
                                                        {{ $client->name }} ({{ $client->company ?? '—' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn-add-client" data-bs-toggle="modal"
                                                    data-bs-target="#addClientModal" title="Add New Client">
                                                <i class='bx bx-plus'></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Linked Project</label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-folder-open"></i></span>
                                            <select name="project_id" id="projectDropdown" style="color:#5f6368;">
                                                @if(!empty($invoice->project_id))
                                                    <option value="{{ $invoice->project_id }}" selected>Loading…</option>
                                                @elseif(!empty($preloadProject))
                                                    {{-- Pre-populated from URL project_id param --}}
                                                    <option value="{{ $preloadProject->project_id }}" selected
                                                            data-amount="{{ $preloadProject->project_amount ?? '' }}">
                                                        {{ $preloadProject->project_name }}
                                                    </option>
                                                @else
                                                    <option value="">— Select Client First —</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div id="projectValueBadge" class="mt-1"
                                             style="display:{{ !empty($preloadProject->project_amount) ? 'block' : 'none' }};">
                                            <span style="font-size:0.72rem;color:#163f7a;font-weight:600;">
                                                <i class="bx bx-rupee"></i>
                                                <span id="projectValueText">{{ !empty($preloadProject->project_amount) ? number_format($preloadProject->project_amount, 0) : '0' }}</span> — Contract Value
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">GST No.</label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-id-card"></i></span>
                                            <input type="text" name="client_gst" id="client_gst"
                                                   value="{{ old('client_gst', $invoice->client_gstno ?? '') }}" placeholder="GST Number">
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center">
                                        <div class="form-check mt-3">
                                            <input type="checkbox" class="form-check-input" id="recurring_invoice"
                                                   name="recurring_invoice" value="1" @if(old('recurring_invoice', $invoice->recurring_invoice ?? false)) checked @endif>
                                            <label class="form-check-label fw-600 text-muted" for="recurring_invoice" style="font-size:0.85rem;">Enable Recurring Invoice</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Billing Address</label>
                                        <div class="cf-input-box cf-textarea-box">
                                            <!-- <span class="cf-icon"><i class="bx bx-map"></i></span> -->
                                            <textarea name="billing_address" id="billing_address" rows="2"
                                                      placeholder="Standard billing address" class="mp-autoresize">{{ old('billing_address', $invoice->billing_address ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Shipping Address</label>
                                        <div class="cf-input-box cf-textarea-box">
                                            <!-- <span class="cf-icon"><i class="bx bx-map-pin"></i></span> -->
                                            <textarea name="shipping_address" id="shipping_address" rows="2"
                                                      placeholder="Standard shipping address" class="mp-autoresize">{{ old('shipping_address', $invoice->shipping_address ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Payment & Bank Details ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(242,153,0,0.10);color:#f29900;">
                                    <i class="bx bx-credit-card"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Payment & Bank Details</h6>
                                    <span class="ml-card-sub">Mode, currency & organization details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-12 mt-2">
                                        <div class="bank-info-bg">
                                            <h6 class="fw-700 mb-3 text-dark small text-uppercase" style="letter-spacing:0.5px;">Organization Bank Details</h6>
                                            @php
                                                $company = session('companies');
                                                $bank = json_decode($invoice->bank_details ?? $company->bank_details ?? '["","","","",""]', true);
                                                if (!is_array($bank)) $bank = ["","","","",""];
                                            @endphp
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="ml-label">Payment Mode</label>
                                                    <div class="cf-input-box">
                                                        <span class="cf-icon"><i class="bx bx-wallet"></i></span>
                                                        <select name="payment_mode">
                                                            <option value="">Select Mode...</option>
                                                            <option value="cash" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'cash') selected @endif>Cash</option>
                                                            <option value="card" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'card') selected @endif>Credit/Debit Card</option>
                                                            <option value="bank" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'bank') selected @endif>Bank Transfer</option>
                                                            <option value="paypal" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'paypal') selected @endif>PayPal</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="ml-label" style="font-size:0.68rem;">Bank Name</label>
                                                    <input type="text" class="form-control cf-bank-input" name="bank_details[]" value="{{ $bank[0] ?? '' }}" placeholder="e.g. HDFC Bank">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="ml-label" style="font-size:0.68rem;">Account Holder</label>
                                                    <input type="text" class="form-control cf-bank-input" name="bank_details[]" value="{{ $bank[1] ?? '' }}" placeholder="Account Holder Name">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="ml-label" style="font-size:0.68rem;">Account Number</label>
                                                    <input type="text" class="form-control cf-bank-input" name="bank_details[]" value="{{ $bank[2] ?? '' }}" placeholder="XXXX XXXX XXXX">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="ml-label" style="font-size:0.68rem;">IFSC / SWIFT</label>
                                                    <input type="text" class="form-control cf-bank-input" name="bank_details[]" value="{{ $bank[3] ?? '' }}" placeholder="e.g. HDFC0001234">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="ml-label" style="font-size:0.68rem;">UPI ID</label>
                                                    <input type="text" class="form-control cf-bank-input" name="bank_details[]" value="{{ $bank[4] ?? '' }}" placeholder="name@upi">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="ml-label">Currency</label>
                                                    <div class="cf-input-box">
                                                        <span class="cf-icon"><i class="bx bx-globe"></i></span>
                                                        <select id="currency" name="currency">
                                                            <option value="INR" @if(old('currency', $invoice->currency ?? 'INR') == 'INR') selected @endif>₹ INR</option>
                                                            <option value="USD" @if(old('currency', $invoice->currency ?? '') == 'USD') selected @endif>$ USD</option>
                                                            <option value="EUR" @if(old('currency', $invoice->currency ?? '') == 'EUR') selected @endif>€ EUR</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
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
                                <button type="button" class="lb-btn" id="addItemButton"
                                        style="background:rgba(22, 63, 122,0.08);color:#163f7a;">
                                    <i class="bx bx-plus"></i> Add Item
                                </button>
                            </div>
                            <div class="ml-card-body p-3">
                                <div id="invoiceItemsBody">
                                    @php
                                        $taxes = !empty($companies->tax) ? explode(',', $companies->tax) : [];
                                    @endphp

                                    @foreach($invoiceItems as $k => $item)
                                        <div class="mp-item-row" data-item-row="{{ $k }}">
                                            <div class="mp-item-row-header">
                                                <span class="mp-item-num">{{ $k + 1 }}</span>
                                                <span class="mp-item-row-title">Item {{ $k + 1 }}</span>
                                                <button type="button" class="btn kb-action-btn kb-action-del removeRowButton ms-auto"
                                                        style="width:28px;height:28px;" title="Remove item">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div   >
                                            <div class="mp-item-row-body">
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Item Name</label>
                                                    <textarea class="form-control form-control-sm item-name mp-autoresize"
                                                              name="invoice_items[{{ $k }}][short_description]"
                                                              placeholder="e.g. Web Development" rows="1">{{ $item->short_description ?? '' }}</textarea>
                                                </div>
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Description</label>
                                                    <textarea class="form-control form-control-sm item-longdesc mp-autoresize"
                                                              name="invoice_items[{{ $k }}][long_description]"
                                                              placeholder="Optional details…" rows="1">{{ $item->long_description ?? '' }}</textarea>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">SAC/HSN</label>
                                                    <input type="text" class="form-control form-control-sm item-sac"
                                                           name="invoice_items[{{ $k }}][sac_code]" value="{{ $item->sac_code ?? '' }}">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Qty</label>
                                                    <input type="number" class="form-control form-control-sm item-qty text-center"
                                                           name="invoice_items[{{ $k }}][quantity]" value="{{ $item->quantity ?? 1 }}" min="1">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Rate (₹)</label>
                                                    <input type="number" class="form-control form-control-sm item-price text-end"
                                                           name="invoice_items[{{ $k }}][price]" placeholder="0.00" value="{{ $item->price ?? '' }}">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Tax</label>
                                                    <select class="selectpicker form-control form-control-sm item-tax" multiple
                                                            data-selected-text-format="count > 2" data-container="body"
                                                            name="invoice_items[{{ $k }}][tax_rate][]" title="No Tax">
                                                        @foreach($taxes as $index => $tax)
                                                            @php $calTax = ($tax ?? 0) / 100; @endphp
                                                            <option value="{{ $index . ':' . $calTax }}"
                                                                @php
                                                                    $isSel = false;
                                                                    if($index == 0 && $item->cgst_percent > 0) $isSel = true;
                                                                    elseif($index == 1 && $item->sgst_percent > 0) $isSel = true;
                                                                    elseif($index == 2 && $item->igst_percent > 0) $isSel = true;
                                                                    elseif($index == 3 && $item->vat_percent > 0) $isSel = true;
                                                                @endphp
                                                                @if($isSel) selected @endif>
                                                                {{ ['CGST','SGST','IGST','VAT'][$index] ?? 'Tax' }} {{ $tax }}%
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Amount</label>
                                                    <span class="line-total mp-item-amount-val">
                                                        ₹{{ number_format(($item->price ?? 0) * ($item->quantity ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if(count($invoiceItems) == 0)
                                        <div class="mp-item-row" data-item-row="0">
                                            <div class="mp-item-row-header">
                                                <span class="mp-item-num">1</span>
                                                <span class="mp-item-row-title">Item 1</span>
                                                <button type="button" class="btn kb-action-btn kb-action-del removeRowButton ms-auto"
                                                        style="width:28px;height:28px;" title="Remove item">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                            <div class="mp-item-row-body">
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Item Name</label>
                                                    <textarea class="form-control form-control-sm item-name mp-autoresize"
                                                              name="invoice_items[0][short_description]"
                                                              placeholder="e.g. Web Development" rows="1" required></textarea>
                                                </div>
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Description</label>
                                                    <textarea class="form-control form-control-sm item-longdesc mp-autoresize"
                                                              name="invoice_items[0][long_description]"
                                                              placeholder="Optional details…" rows="1"></textarea>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">SAC/HSN</label>
                                                    <input type="text" class="form-control form-control-sm item-sac"
                                                           name="invoice_items[0][sac_code]" value="998314">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Qty</label>
                                                    <input type="number" class="form-control form-control-sm item-qty text-center"
                                                           name="invoice_items[0][quantity]" value="1" min="1">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Rate (₹)</label>
                                                    <input type="number" class="form-control form-control-sm item-price text-end"
                                                           name="invoice_items[0][price]" placeholder="0.00" required>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Tax</label>
                                                    <select class="selectpicker form-control form-control-sm item-tax" multiple
                                                            data-selected-text-format="count > 2" data-container="body"
                                                            name="invoice_items[0][tax_rate][]" title="No Tax">
                                                        @foreach($taxes as $index => $tax)
                                                            @php $calTax = ($tax ?? 0) / 100; @endphp
                                                            <option value="{{ $index . ':' . $calTax }}">
                                                                {{ ['CGST','SGST','IGST','VAT'][$index] ?? 'Tax' }} {{ $tax }}%
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Amount</label>
                                                    <span class="line-total mp-item-amount-val">₹0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <button type="button" class="mp-add-item-btn mt-3" id="addItemButtonSecondary">
                                    <i class="bx bx-plus"></i> Add Another Item
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- End col-lg-8 --}}
                    <div class="col-lg-8">
                        {{-- ── Notes ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                    <i class="bx bx-edit-alt"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Notes &amp; Terms</h6>
                                    <span class="ml-card-sub">Internal notes & legal terms</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="ml-label">Admin Note (Internal)</label>
                                        <div class="cf-input-box cf-textarea-box">
                                            <!-- <span class="cf-icon"><i class="bx bx-lock-alt"></i></span> -->
                                            <textarea name="admin_note" rows="2" class="mp-autoresize"
                                                      placeholder="Not visible to client">{{ old('admin_note', $invoice->admin_note ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="ml-label">Client Note (on PDF)</label>
                                        <div class="cf-input-box cf-textarea-box">
                                            <!-- <span class="cf-icon"><i class="bx bx-message-square-dots"></i></span> -->
                                            <textarea name="client_note" rows="2" class="mp-autoresize"
                                                      placeholder="Visible on PDF">{{ old('client_note', $invoice->client_note ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="ml-label">Terms &amp; Conditions</label>
                                        <div class="cf-input-box cf-textarea-box">
                                            <!-- <span class="cf-icon"><i class="bx bx-file-blank"></i></span> -->
                                            <textarea name="terms" rows="2" class="mp-autoresize"
                                                      placeholder="Terms and conditions">{{ old('terms', $invoice->terms ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        {{-- ══ RIGHT — Sticky Summary + Actions ══ --}}
                        <div class="mp-sidebar-sticky">
                            {{-- Summary card --}}
                            <div class="ml-card mb-3">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(52,168,83,0.10);color:#163f7a;">
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
                                        <span id="subTotal" class="mp-summary-val">₹0.00</span>
                                    </div>

                                    <div class="divider my-2 border-top border-dashed"></div>

                                    <div class="mb-3">
                                        <label class="ml-label">Apply Discount</label>
                                        <div class="d-flex gap-2">
                                            <select name="discount_type" id="discountApplicationType" class="cf-summary-select form-select" style="flex:2;">
                                                <option value="none" @if(($invoice->discount_type ?? '') == 'none') selected @endif>None</option>
                                                <option value="before-tax" @if(($invoice->discount_type ?? '') == 'before-tax') selected @endif>Before Tax</option>
                                                <option value="after-tax" @if(($invoice->discount_type ?? '') == 'after-tax') selected @endif>After Tax</option>
                                            </select>
                                            <select name="discount_mode" id="discountValueType" class="cf-summary-select form-select" style="flex:0 0 70px;">
                                                <option value="flat" @if(($invoice->discount_mode ?? '') == 'flat') selected @endif>FLAT</option>
                                                <option value="percentage" @if(($invoice->discount_mode ?? '') == 'percentage') selected @endif>%</option>
                                            </select>
                                            <input type="number" class="cf-bank-input form-control text-end" name="discount"
                                                   id="discountValue" value="{{ $invoice->discount ?? 0 }}" step="0.01" style="flex:1;">
                                        </div>
                                    </div>

                                    <div class="mp-summary-row text-success small" id="discountAmountRow" style="display:none !important;">
                                        <span class="mp-summary-label text-success">Net Discount</span>
                                        <span id="discountAmountCalculated" class="mp-summary-val text-success">(-₹0.00)</span>
                                    </div>

                                    <div class="divider my-2 border-top border-dashed"></div>

                                    <div id="tax-summary-rows">
                                        {{-- Tax rows populated by JS --}}
                                        <div class="mp-summary-row">
                                            <span class="mp-summary-label">Total Tax</span>
                                            <span id="totalTax" class="mp-summary-val">₹0.00</span>
                                        </div>
                                    </div>

                                    <div class="divider my-2 border-top border-dashed"></div>

                                    <div class="mb-3">
                                        <label class="ml-label">Adjustment / Setup Fee</label>
                                        <div class="cf-input-box">
                                            <span class="cf-icon"><i class="bx bx-math"></i></span>
                                            <input type="number" class="text-end" name="adjustment"
                                                   id="adjustment" value="{{ $invoice->adjustment ?? 0 }}" step="0.01">
                                        </div>
                                    </div>

                                    <div class="mp-summary-total">
                                        <span>Grand Total</span>
                                        <strong id="grandTotal" class="mp-grand-total-val">₹0.00</strong>
                                    </div>
                                    <input type="hidden" name="gtAmount" id="gtAmount" value="0" />
                                </div>
                            </div>

                            {{-- Action buttons card --}}
                            <div class="ml-card">
                                <div class="ml-card-body">
                                    <div class="d-flex flex-column gap-2">

                                        {{-- Save Draft --}}
                                        <button type="button" class="inv-btn-draft w-100 justify-content-center" id="btnSaveDraftSidebar" title="Save as draft — client won't be notified">
                                            <i class="bx bx-file-blank"></i> Save Draft
                                        </button>

                                        {{-- Save Invoice --}}
                                        <button type="button" class="inv-btn-save w-100 justify-content-center" id="btnSaveInvoiceSidebar">
                                            <i class="bx bx-save"></i> Save Invoice
                                        </button>

                                        {{-- Save & Send --}}
                                        <button type="button" class="inv-btn-send w-100 justify-content-center" id="btnSaveAndSendSidebar" title="Save and email invoice to client">
                                            <i class="bx bx-send"></i> Save &amp; Send
                                        </button>

                                        <div style="border-top:1px solid #f1f3f4;margin:4px 0;"></div>

                                        {{-- Live Preview --}}
                                        <button type="button" class="inv-btn-draft w-100 justify-content-center" id="previewInvoiceBtnSidebar"
                                                style="color:#1a73e8;border-color:rgba(26,115,232,0.3);background:rgba(26,115,232,0.05);">
                                            <i class="bx bx-show"></i> Live Preview
                                        </button>

                                        {{-- Cancel --}}
                                        <a href="/invoices" class="lb-btn w-100 justify-content-center"
                                           style="background:transparent;color:#9aa0a6;border:1px solid #e8eaed;">
                                            <i class="bx bx-x"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- End col-lg-4 --}}

                </div>
                {{-- End row g-4 --}}
            </form>
        </div>
    </section>

    <!-- Modal for New Client -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="/manage-invoice-client" method="POST" id="addClientForm">
                @csrf
                <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">

                    {{-- Teal gradient header (matches contract/recovery style) --}}
                    <div class="cf-modal-header">
                        <div>
                            <p class="cf-modal-header-title">
                                <i class="bx bx-user-plus me-1"></i> Add New Client
                            </p>
                            <p class="cf-modal-header-sub">Fill in the details to add a new client to the system</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);opacity:.8;"></button>
                    </div>

                    {{-- Body --}}
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="ml-label">Full Name <span class="text-danger">*</span></label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-user"></i></span>
                                    <input type="text" name="name" required placeholder="John Doe">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="ml-label">Email Address <span class="text-danger">*</span></label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-envelope"></i></span>
                                    <input type="email" name="email" required placeholder="john@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="ml-label">Mobile Number <span class="text-danger">*</span></label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-phone"></i></span>
                                    <input type="text" name="mob" required value="91">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="ml-label">Company Name <span class="text-danger">*</span></label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-buildings"></i></span>
                                    <input type="text" name="company" required placeholder="Acme Corp">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="ml-label">Address / Location</label>
                                <div class="cf-input-box cf-textarea-box">
                                    <span class="cf-icon"><i class="bx bx-map"></i></span>
                                    <textarea name="address[]" rows="2" placeholder="Street, City, State, ZIP"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="ml-label">GST Number</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-id-card"></i></span>
                                    <input type="text" name="gstno" placeholder="Optional">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="cf-modal-footer">
                        <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="cf-btn-save">
                            <i class="bx bx-save"></i> Save Client
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        // Defer execution until jQuery is available (jQuery loads after @@yield in layout.blade.php)
        function invoiceScriptInit() {
            if (typeof $ === 'undefined') {
                return setTimeout(invoiceScriptInit, 50);
            }

        const availableTaxes = @json($taxes);
        const taxNames = ['CGST','SGST','IGST','VAT'];

        function formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function recalculateTotals() {
            try {
                const discountAppType = $('#discountApplicationType').val();
                const discountValueType = $('#discountValueType').val();
                const discountValue = parseFloat($('#discountValue').val()) || 0;
                const adjustment = parseFloat($('#adjustment').val()) || 0;

                let initialSubTotal = 0;
                let totalTax = 0;
                let taxBreakdown = {};

                const $rows = $('.mp-item-row');
                $rows.each(function() {
                    const $row = $(this);
                    const qty = parseFloat($row.find('.item-qty').val()) || 0;
                    const price = parseFloat($row.find('.item-price').val()) || 0;
                    const lineSubTotal = qty * price;
                    
                    let lineTaxAmount = 0;
                    const $taxSelect = $row.find('select.item-tax');
                    const selectedTaxValues = $taxSelect.val();
                    if (selectedTaxValues) {
                        selectedTaxValues.forEach(val => {
                            const rate = parseFloat(val.split(':')[1]);
                            const amt = lineSubTotal * rate;
                            lineTaxAmount += amt;
                            const label = $row.find(`select.item-tax option[value="${val}"]`).text().trim();
                            taxBreakdown[label] = (taxBreakdown[label] || 0) + amt;
                        });
                    }

                    $row.data('initialSubtotal', lineSubTotal);
                    $row.data('initialTax', lineTaxAmount);
                    initialSubTotal += lineSubTotal;

                    $row.find('.line-total').text('₹ ' + formatCurrency(lineSubTotal + lineTaxAmount));
                });

                // Calculate Net Discount
                let calculatedDiscountAmount = 0;
                if (discountValue > 0) {
                    let discountBase = (discountAppType === 'before-tax') ? initialSubTotal : 
                                       (initialSubTotal + $rows.toArray().reduce((sum, el) => sum + parseFloat($(el).data('initialTax') || 0), 0));
                    
                    calculatedDiscountAmount = (discountValueType === 'percentage') ? (discountBase * (discountValue / 100)) : discountValue;
                    calculatedDiscountAmount = Math.min(calculatedDiscountAmount, discountBase);
                }

                // Final Taxes & Subtotal
                let finalSubTotal = initialSubTotal;
                totalTax = 0;

                if (discountAppType === 'before-tax') {
                    finalSubTotal = initialSubTotal - calculatedDiscountAmount;
                    $rows.each(function() {
                        const rowSub = parseFloat($(this).data('initialSubtotal') || 0);
                        const rowRatio = initialSubTotal > 0 ? (rowSub / initialSubTotal) : 0;
                        const rowDiscount = calculatedDiscountAmount * rowRatio;
                        const rowFinalSub = rowSub - rowDiscount;
                        
                        const selectedTares = $(this).find('select.item-tax').val();
                        if (selectedTares) {
                            selectedTares.forEach(val => {
                                const rate = parseFloat(val.split(':')[1]);
                                totalTax += rowFinalSub * rate;
                            });
                        }
                    });
                } else {
                    $rows.each(function() { totalTax += parseFloat($(this).data('initialTax') || 0); });
                }

                const grandTotal = Math.max(0, (discountAppType === 'before-tax') ? (finalSubTotal + totalTax - adjustment) : 
                                   (discountAppType === 'after-tax') ? (initialSubTotal + totalTax - calculatedDiscountAmount - adjustment) : 
                                   (initialSubTotal + totalTax - adjustment));

                $('#subTotal').text('₹ ' + formatCurrency(initialSubTotal));
                $('#totalTax').text('₹ ' + formatCurrency(totalTax));
                $('#grandTotal').text('₹ ' + formatCurrency(grandTotal));
                $('#gtAmount').val(grandTotal.toFixed(2));

                $('#discountAmountRow').toggle(calculatedDiscountAmount > 0).css('display', calculatedDiscountAmount > 0 ? 'flex' : 'none');
                $('#discountAmountCalculated').text(`(-₹ ${formatCurrency(calculatedDiscountAmount)})`);

                // Tax breakdown for summary
                let taxHtml = '';
                for (let label in taxBreakdown) {
                    taxHtml += `<div class="mp-summary-row small"><span class="mp-summary-label">${label}</span><span class="mp-summary-val">₹ ${formatCurrency(taxBreakdown[label])}</span></div>`;
                }
                if (taxHtml) {
                    $('#tax-summary-rows').html(taxHtml + `<div class="divider my-1 border-top border-dashed"></div><div class="mp-summary-row"><span class="mp-summary-label">Total Tax</span><span class="mp-summary-val">₹ ${formatCurrency(totalTax)}</span></div>`);
                }

            } catch (e) { console.error("Calc Error:", e); }
        }

        $(document).ready(function() {
        $(document).on('click', '#addItemButton, #addItemButtonSecondary', function() {
                const index = $('.mp-item-row').length;
                let taxOptions = '';
                if (Array.isArray(availableTaxes)) {
                    availableTaxes.forEach((val, i) => {
                        const rate = (val || 0) / 100;
                        taxOptions += `<option value="${i}:${rate}">${taxNames[i] || 'Tax'} ${val}%</option>`;
                    });
                }

                const html = `
                    <div class="mp-item-row shadow-sm animate__animated animate__fadeInUp" data-item-row="${index}">
                        <div class="mp-item-row-header">
                            <span class="mp-item-num">${index + 1}</span>
                            <span class="mp-item-row-title">Item ${index + 1}</span>
                            <button type="button" class="btn kb-action-btn kb-action-del removeRowButton ms-auto" style="width:28px;height:28px;" title="Remove item"><i class="bx bx-trash"></i></button>
                        </div>
                        <div class="mp-item-row-body">
                            <div class="mp-item-field" style="grid-column: span 2;">
                                <label class="mp-item-label">Item Name</label>
                                <textarea class="form-control form-control-sm item-name mp-autoresize" name="invoice_items[${index}][short_description]" rows="1" required></textarea>
                            </div>
                            <div class="mp-item-field" style="grid-column: span 2;">
                                <label class="mp-item-label">Description</label>
                                <textarea class="form-control form-control-sm item-longdesc mp-autoresize" name="invoice_items[${index}][long_description]" rows="1"></textarea>
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">SAC/HSN</label>
                                <input type="text" class="form-control form-control-sm item-sac" name="invoice_items[${index}][sac_code]">
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Qty</label>
                                <input type="number" class="form-control form-control-sm item-qty text-center" name="invoice_items[${index}][quantity]" value="1" min="1">
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Rate (₹)</label>
                                <input type="number" class="form-control form-control-sm item-price text-end" name="invoice_items[${index}][price]" placeholder="0.00" required>
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Tax</label>
                                <select class="selectpicker form-control form-control-sm item-tax" multiple data-container="body" name="invoice_items[${index}][tax_rate][]" title="No Tax">
                                    ${taxOptions}
                                </select>
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Amount</label>
                                <span class="line-total mp-item-amount-val">₹ 0.00</span>
                            </div>
                        </div>
                    </div>`;

                $('#invoiceItemsBody').append(html);

                // Safely initialize selectpicker on the new row
                const $newRow = $('#invoiceItemsBody .mp-item-row').last();
                if (typeof $.fn.selectpicker !== 'undefined') {
                    $newRow.find('.selectpicker').selectpicker();
                }

                recalculateTotals();
            });

            // Remove Item Logic
            $(document).on('click', '.removeRowButton', function() {
                if ($('.mp-item-row').length > 1) {
                    $(this).closest('.mp-item-row').fadeOut(300, function() {
                        $(this).remove();
                        // Update numbering
                        $('.mp-item-row').each((i, el) => {
                            $(el).find('.mp-item-num').text(i + 1);
                            $(el).find('.mp-item-row-title').text('Item ' + (i + 1));
                        });
                        recalculateTotals();
                    });
                } else {
                    swal("Warning", "At least one item is required.", "warning");
                }
            });

            // Live Preview button
            $(document).on('click', '#previewInvoiceBtn, #previewInvoiceBtnSidebar', function () {
                @if(!empty($invoice->id))
                    window.open('/invoices/preview/{{ $invoice->id }}', '_blank');
                @else
                    swal({
                        title: "Save First",
                        text: "Please save the invoice before previewing it.",
                        icon: "info",
                        button: "OK"
                    });
                @endif
            });

            // ── Action bar buttons ──

            // Save Draft
            $(document).on('click', '#btnSaveDraft, #btnSaveDraftSidebar', function () {
                $('#invoiceAction').val('draft');
                $('#invoiceForm').trigger('submit');
            });

            // Save Invoice
            $(document).on('click', '#btnSaveInvoice, #btnSaveInvoiceSidebar', function () {
                $('#invoiceAction').val('save');
                $('#invoiceForm').trigger('submit');
            });

            // Save & Send — confirm before sending email to client
            $(document).on('click', '#btnSaveAndSend, #btnSaveAndSendSidebar', function () {
                swal({
                    title: "Save & Send Invoice?",
                    text: "The invoice will be saved and emailed to the client immediately.",
                    icon: "info",
                    buttons: ["Cancel", "Yes, Send It"],
                }).then((isConfirm) => {
                    if (isConfirm) {
                        $('#invoiceAction').val('send');
                        $('#invoiceForm').trigger('submit');
                    }
                });
            });

            // Client Auto-fill + Project Loader
            $('#client_id').on('change', function() {
                const opt = $(this).find(':selected');
                const clientId = opt.val();
                if (clientId) {
                    // Optimized Address Parsing Logic (Handles Array and Object formats)
                    let addr = '';
                    const rawLoc = opt.attr('data-loc-raw') || '';
                    try {
                        const loc = JSON.parse(rawLoc);
                        if (Array.isArray(loc)) {
                            // Format: ["Level 5","Hubtown Viva",...]
                            addr = loc.filter(Boolean).join("\n");
                        } else if (typeof loc === 'object' && loc !== null) {
                            // Format: {"address":"...","city":"...","state":"...","country":"...","zip":"..."}
                            const parts = [
                                loc.address,
                                loc.city,
                                loc.state,
                                loc.zip,
                                loc.country
                            ].filter(Boolean);
                            addr = parts.join(", ");
                        } else {
                            addr = rawLoc;
                        }
                    } catch (e) {
                        addr = rawLoc; // Fallback to raw string if not JSON
                    }

                    $('#billing_address').val(addr);
                    $('#shipping_address').val(addr);
                    $('#client_gst').val(opt.attr('data-gstno') || '');

                    // Load projects for this client
                    $('#projectDropdown').html('<option value="">Loading projects…</option>').prop('disabled', true);
                    $.getJSON('/get-projects/' + clientId, function(data) {
                        let opts = '<option value="">— No Project (General Invoice) —</option>';
                        if (data.projects && data.projects.length > 0) {
                            data.projects.forEach(function(p) {
                                opts += `<option value="${p.id}" data-amount="${p.amount}">${p.name}</option>`;
                            });
                        } else {
                            opts += '<option disabled>No projects found for this client</option>';
                        }
                        $('#projectDropdown').html(opts).prop('disabled', false);
                        $('#projectValueBadge').hide();
                    }).fail(function() {
                        $('#projectDropdown').html('<option value="">— Select Project —</option>').prop('disabled', false);
                    });
                } else {
                    $('#projectDropdown').html('<option value="">— Select Client First —</option>');
                    $('#projectValueBadge').hide();
                }
            });

            // Project auto-fill on selection
            $(document).on('change', '#projectDropdown', function() {
                const opt = $(this).find(':selected');
                const amount = opt.data('amount');
                const name = opt.text();
                if (opt.val()) {
                    // Pre-fill Reference with project name
                    if (!$('#invoiceReference').val()) {
                        $('#invoiceReference').val(name);
                    }
                    // Show contract value badge
                    if (amount) {
                        $('#projectValueText').text(parseFloat(amount).toLocaleString('en-IN'));
                        $('#projectValueBadge').show();
                    } else {
                        $('#projectValueBadge').hide();
                    }
                } else {
                    $('#projectValueBadge').hide();
                }
            });

            // Event Listeners for calculations
            $(document).on('input', '.item-qty, .item-price, #discountValue, #adjustment', recalculateTotals);
            $(document).on('change', 'select.item-tax, #discountApplicationType, #discountValueType', recalculateTotals);

            // ─── Initial Setup ───────────────────────────────────────────────────
            setTimeout(() => {
                $('.selectpicker').selectpicker('refresh');
                $('#client_id').next('.bootstrap-select').css({ 'flex': '1', 'min-width': '0', 'border': 'none' });
                recalculateTotals();

                const existingClientId  = $('#client_id').val();
                const existingProjectId = '{{ $invoice->project_id ?? "" }}';
                @php $preloadProjId = $preloadProject->project_id ?? ''; @endphp
                const preloadProjectId  = '{{ $preloadProjId }}';

                // ── CASE 1: Editing an existing invoice ──────────────────────
                // Re-load all projects for that client and re-select the saved project
                if (existingClientId && existingProjectId) {
                    $.getJSON('/get-projects/' + existingClientId, function(data) {
                        let opts = '<option value="">— No Project (General Invoice) —</option>';
                        if (data.projects && data.projects.length > 0) {
                            data.projects.forEach(function(p) {
                                const sel = (String(p.id) === String(existingProjectId)) ? ' selected' : '';
                                opts += `<option value="${p.id}" data-amount="${p.amount}"${sel}>${p.name}</option>`;
                            });
                        }
                        $('#projectDropdown').html(opts).prop('disabled', false);
                        const preselected = $('#projectDropdown').find(':selected');
                        const preAmount   = preselected.data('amount');
                        if (preselected.val() && preAmount) {
                            $('#projectValueText').text(parseFloat(preAmount).toLocaleString('en-IN'));
                            $('#projectValueBadge').show();
                        }
                    });

                // ── CASE 2: New invoice pre-loaded from project_id URL param ──
                // Auto-fill address, GST, reference; load full project list and select the right one
                } else if (existingClientId && preloadProjectId) {
                    // 1. Fill address from the selected client option
                    const opt    = $('#client_id').find('option:selected');
                    const rawLoc = opt.attr('data-loc-raw') || '';
                    let addr = '';
                    try {
                        const loc = JSON.parse(rawLoc);
                        if (Array.isArray(loc)) {
                            addr = loc.filter(Boolean).join(', ');
                        } else if (typeof loc === 'object' && loc !== null) {
                            addr = [loc.address, loc.city, loc.state, loc.zip, loc.country].filter(Boolean).join(', ');
                        } else { addr = rawLoc; }
                    } catch (e) { addr = rawLoc; }
                    if (addr) {
                        $('#billing_address').val(addr);
                        $('#shipping_address').val(addr);
                    }

                    // 2. Fill GST
                    const gst = opt.attr('data-gstno') || '';
                    if (gst) $('#client_gst').val(gst);

                    // 3. Fill reference with project name (pre-rendered by server)
                    @if(!empty($preloadProject))
                    if (!$('#invoiceReference').val()) {
                        $('#invoiceReference').val('{{ addslashes($preloadProject->project_name ?? '') }}');
                    }
                    @endif

                    // 4. Load all projects for this client, then select the preloaded one
                    $.getJSON('/get-projects/' + existingClientId, function(data) {
                        let opts = '<option value="">— No Project (General Invoice) —</option>';
                        if (data.projects && data.projects.length > 0) {
                            data.projects.forEach(function(p) {
                                const sel = (String(p.id) === String(preloadProjectId)) ? ' selected' : '';
                                opts += `<option value="${p.id}" data-amount="${p.amount}"${sel}>${p.name}</option>`;
                            });
                        }
                        $('#projectDropdown').html(opts).prop('disabled', false);
                        const preselected = $('#projectDropdown').find(':selected');
                        const preAmount   = preselected.data('amount');
                        if (preselected.val() && preAmount) {
                            $('#projectValueText').text(parseFloat(preAmount).toLocaleString('en-IN'));
                            $('#projectValueBadge').show();
                        }
                    });
                }
            }, 350);
        });

        } // end invoiceScriptInit
        invoiceScriptInit();
    </script>
@endsection
