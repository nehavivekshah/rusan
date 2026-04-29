@extends('layout')
@section('title', 'Customer 360 View - eseCRM')

@section('content')
<section class="task__section">
    @include('inc.header', ['title' => 'Customer 360 View'])

    <div class="dash-container">
        <!-- Profile Header -->
        <div class="dash-card mb-4 overflow-hidden border-0 shadow-sm" style="background: linear-gradient(135deg, #163f7a 0%, #0f2d57 100%);">
            <div class="p-4 d-flex align-items-center gap-4 text-white">
                <div class="lb-avatar-lg bg-white text-primary fs-1 fw-bold d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 20px;">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <h2 class="mb-1 fw-bold">{{ $customer->name }}</h2>
                    <div class="d-flex align-items-center gap-3 opacity-75">
                        <span><i class="bx bx-building me-1"></i> {{ $customer->company ?? 'No Company' }}</span>
                        <span><i class="bx bx-envelope me-1"></i> {{ $customer->email ?? 'No Email' }}</span>
                        <span><i class="bx bx-phone me-1"></i> +{{ $customer->mob }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light text-primary fw-bold rounded-pill px-4" onclick="triggerCall('+{{ $customer->mob }}')">
                        <i class="bx bx-phone me-1"></i> Call
                    </button>
                    <button class="btn btn-success fw-bold rounded-pill px-4" onclick="sendWhatsapp('+{{ $customer->whatsapp ?? $customer->mob }}')">
                        <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Details & History -->
            <div class="col-lg-8">
                <!-- Nav Tabs -->
                <ul class="nav nav-pills mb-3 gap-2" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab">Timeline & History</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="pills-deals-tab" data-bs-toggle="pill" data-bs-target="#pills-deals" type="button" role="tab">Deals & Opportunities</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="pills-docs-tab" data-bs-toggle="pill" data-bs-target="#pills-docs" type="button" role="tab">Proposals & Invoices</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- History Tab -->
                    <div class="tab-pane fade show active" id="pills-history" role="tabpanel">
                        <div class="dash-card p-4">
                            <h5 class="fw-bold mb-4">Interaction Timeline</h5>
                            <div class="timeline">
                                @forelse($comments as $comment)
                                <div class="timeline-item d-flex gap-3 mb-4">
                                    <div class="timeline-icon bg-light text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="bx bx-message-square-detail"></i>
                                    </div>
                                    <div class="timeline-content bg-light p-3 rounded-3 flex-grow-1 border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-primary">Comment / Update</span>
                                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-2 text-dark">{{ $comment->msg }}</p>
                                        @if($comment->next_date)
                                        <div class="badge bg-warning text-dark"><i class="bx bx-calendar-event me-1"></i> Next Follow-up: {{ date('d M, h:i A', strtotime($comment->next_date)) }}</div>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5 text-muted">No interactions recorded yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Deals Tab -->
                    <div class="tab-pane fade" id="pills-deals" role="tabpanel">
                        <div class="dash-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold m-0">Active Deals</h5>
                                <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addDealModal"><i class="bx bx-plus"></i> New Deal</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Deal Name</th>
                                            <th>Value</th>
                                            <th>Stage</th>
                                            <th>Expected Close</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($opportunities as $deal)
                                        <tr>
                                            <td class="fw-bold">{{ $deal->name }}</td>
                                            <td class="text-success fw-bold">₹{{ number_format($deal->amount, 2) }}</td>
                                            <td><span class="badge bg-info text-dark">{{ $deal->stage }}</span></td>
                                            <td>{{ $deal->expected_close_date ? date('d M, Y', strtotime($deal->expected_close_date)) : 'N/A' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No deals found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Docs Tab -->
                    <div class="tab-pane fade" id="pills-docs" role="tabpanel">
                        <div class="dash-card p-4 mb-4">
                            <h5 class="fw-bold mb-3">Proposals</h5>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Proposal #</th>
                                            <th>Status</th>
                                            <th>Date Sent</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($proposals as $prop)
                                        <tr>
                                            <td class="fw-bold">#{{ $prop->proposal_id }}</td>
                                            <td>
                                                @php
                                                    $pStatusMap = [0 => 'Draft', 1 => 'Sent', 2 => 'Accepted', 3 => 'Declined'];
                                                    $pStatusClass = [0 => 'bg-secondary', 1 => 'bg-primary', 2 => 'bg-success', 3 => 'bg-danger'];
                                                @endphp
                                                <span class="badge {{ $pStatusClass[$prop->status] ?? 'bg-secondary' }}">{{ $pStatusMap[$prop->status] ?? 'Unknown' }}</span>
                                            </td>
                                            <td>{{ $prop->created_at->format('d M, Y') }}</td>
                                            <td class="text-center">
                                                <a href="/quotation/{{ $prop->id }}/{{ md5($prop->email) }}" target="_blank" class="btn btn-sm btn-light text-primary border"><i class="bx bx-show"></i></a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-3 text-muted">No proposals found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($type === 'client')
                        <div class="dash-card p-4">
                            <h5 class="fw-bold mb-3">Invoices</h5>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoices as $inv)
                                        <tr>
                                            <td class="fw-bold">{{ $inv->invoice_id }}</td>
                                            <td class="fw-bold">₹{{ number_format($inv->amount, 2) }}</td>
                                            <td><span class="badge {{ $inv->status == 1 ? 'bg-success' : 'bg-warning text-dark' }}">{{ $inv->status == 1 ? 'Paid' : 'Unpaid' }}</span></td>
                                            <td>{{ $inv->created_at->format('d M, Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-3 text-muted">No invoices found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar info -->
            <div class="col-lg-4">
                <div class="dash-card p-4 mb-4">
                    <h5 class="fw-bold mb-3">Customer Info</h5>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Industry</label>
                        <span class="fw-bold">{{ $customer->industry ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Location</label>
                        @php $loc = json_decode($customer->location); @endphp
                        <span class="fw-bold">{{ $loc->address ?? ($customer->location ?: 'N/A') }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Source</label>
                        <span class="badge bg-light text-dark border">{{ $customer->source ?? 'Direct' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Lead Score</label>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $customer->score ?? 0 }}%"></div>
                        </div>
                        <span class="small fw-bold text-success">{{ $customer->score ?? 0 }}/100</span>
                    </div>
                </div>

                <div class="dash-card p-4">
                    <h5 class="fw-bold mb-3">Upcoming Tasks</h5>
                    @forelse($tasks as $task)
                    <div class="task-item border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold" style="font-size: 0.85rem;">{{ $task->title }}</span>
                            <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">{{ $task->due_date }}</span>
                        </div>
                        <p class="small text-muted mb-0">{{ Str::limit($task->description, 60) }}</p>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted small">No upcoming tasks.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.timeline-item { position: relative; }
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 45px;
    bottom: -15px;
    width: 2px;
    background: #e8eaed;
}
.nav-link.active { background-color: #163f7a !important; color: #fff !important; }
.nav-link { color: #5f6368; font-weight: 600; border: 1px solid #e8eaed; background: #fff; }
</style>

<script>
function triggerCall(phone) {
    Swal.fire({
        title: 'Initiate Call?',
        text: "Connecting to " + phone + " via Exotel...",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#163f7a',
        confirmButtonText: 'Yes, Call Now'
    }).then((result) => {
        if (result.isConfirmed) {
            // Placeholder for Exotel API integration
            Swal.fire('Calling...', 'Request sent to Exotel Server.', 'success');
        }
    });
}

function sendWhatsapp(phone) {
    window.open('https://api.whatsapp.com/send/?phone=' + phone.replace('+', '') + '&text=Hello, this is regarding your request on eseCRM.', '_blank');
}
</script>
@endsection
