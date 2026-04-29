@php
    $user = Auth::user();
@endphp

<div class="modal-header border-0 p-4 pb-0">
    <h5 class="modal-title fw-bold" style="color: #163f7a;">
        <i class="bx @if($ticket) bx-edit @else bx-plus-circle @endif me-2"></i>
        {{ $ticket ? 'Ticket Details' : 'Raise New Ticket' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form id="supportForm" class="p-4 pt-3">
    @csrf
    @if($ticket)
        <input type="hidden" name="id" value="{{ $ticket->id }}">
    @endif

    <div class="mb-3">
        <label class="form-label small fw-bold text-muted">Ticket Subject</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bx bx-tag text-muted"></i></span>
            <input type="text" name="subject" class="form-control border-start-0" 
                placeholder="e.g., Unable to generate invoice" value="{{ $ticket->subject ?? '' }}" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-6">
            <label class="form-label small fw-bold text-muted">Priority</label>
            <select name="priority" class="form-select">
                <option value="Low" {{ ($ticket && $ticket->priority == 'Low') ? 'selected' : '' }}>Low</option>
                <option value="Medium" {{ (!$ticket || $ticket->priority == 'Medium') ? 'selected' : '' }}>Medium</option>
                <option value="High" {{ ($ticket && $ticket->priority == 'High') ? 'selected' : '' }}>High</option>
            </select>
        </div>
        <div class="col-6">
            <label class="form-label small fw-bold text-muted">Current Status</label>
            @if($user->role == 'master')
                <select name="status" class="form-select @if($ticket && $ticket->status == 2) border-success @endif">
                    <option value="0" {{ ($ticket && $ticket->status == 0) ? 'selected' : '' }}>Open</option>
                    <option value="1" {{ ($ticket && $ticket->status == 1) ? 'selected' : '' }}>Processing</option>
                    <option value="2" {{ ($ticket && $ticket->status == 2) ? 'selected' : '' }}>Resolved</option>
                </select>
            @else
                <div class="form-control bg-light">
                    @if(!$ticket || $ticket->status == 0) Open
                    @elseif($ticket->status == 1) In Progress
                    @else Resolved @endif
                </div>
            @endif
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label small fw-bold text-muted">Issue Description</label>
        <textarea name="description" class="form-control" rows="4" 
            placeholder="Please provide details about the issue...">{{ $ticket->description ?? '' }}</textarea>
    </div>

    @if($ticket)
        <div class="p-3 mb-4 rounded-3 border bg-light" style="border-style: dashed !important;">
            <div class="row g-2">
                <div class="col-6">
                    <div class="small text-muted">Ticket ID</div>
                    <div class="fw-bold">{{ $ticket->ticket_no }}</div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Created At</div>
                    <div class="fw-bold">{{ $ticket->created_at->format('d M, Y H:i') }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
        <button type="submit" id="submitBtn" class="btn btn-teal flex-grow-1" style="background:#163f7a; color:white; border-radius:10px;">
            <i class="bx bx-check-circle me-1"></i> {{ $ticket ? 'Update Ticket' : 'Raise Ticket' }}
        </button>
    </div>
</form>

<script>
    $('#supportForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#submitBtn');
        const originalText = btn.html();
        
        btn.html('<i class="bx bx-loader-alt bx-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: '/manage-support',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message);
                    btn.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                btn.html(originalText).prop('disabled', false);
            }
        });
    });
</script>
