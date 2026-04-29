@extends('layout')
@section('title', 'Sales Pipeline - eseCRM')

@section('content')
<section class="task__section">
    @include('inc.header', ['title' => 'Sales Pipeline'])

    <div class="dash-container">

        {{-- ── Toolbar ── --}}
        <div class="leads-toolbar mb-3">
            <div class="leads-toolbar-left gap-3">
                <span class="lb-page-count">
                    <i class="bx bx-trending-up"></i> Opportunities Pipeline
                </span>
                {{-- Pipeline total --}}
                <div class="ok-pipeline-total" id="pipelineTotal">
                    <i class="bx bx-rupee"></i>
                    <span id="pipelineTotalVal">0</span>
                    <span class="ok-pipeline-label">Total Pipeline</span>
                </div>
            </div>
            <div class="leads-toolbar-right">
                <button class="lb-btn lb-btn-primary"
                        data-bs-toggle="offcanvas" data-bs-target="#addDealOffcanvas">
                    <i class="bx bx-plus"></i> New Deal
                </button>
            </div>
        </div>

        {{-- ── Stage summary strip ── --}}
        <div class="ok-stage-strip" id="stageSummaryStrip">
            @php
            $stages = [
                'New'         => ['#1a73e8', 'bx bx-star'],
                'Qualified'   => ['#9334e9', 'bx bx-check-shield'],
                'Proposal'    => ['#f29900', 'bx bx-file'],
                'Negotiation' => ['#006666', 'bx bx-transfer'],
                'Closed Won'  => ['#34a853', 'bx bx-trophy'],
                'Closed Lost' => ['#ea4335', 'bx bx-x-circle'],
            ];
            @endphp
            @foreach($stages as $stage => $cfg)
                @php $sid = str_replace(' ', '-', $stage); @endphp
                <div class="ok-stage-chip" style="border-bottom:2px solid {{ $cfg[0] }};">
                    <i class="{{ $cfg[1] }}" style="color:{{ $cfg[0] }};"></i>
                    <span class="ok-stage-chip-label">{{ $stage }}</span>
                    <span class="ok-stage-chip-count badge" id="count-{{ $sid }}"
                          style="background:{{ $cfg[0] }}20;color:{{ $cfg[0] }};">0</span>
                    <span class="ok-stage-chip-val" id="val-{{ $sid }}">₹0</span>
                </div>
            @endforeach
        </div>

        {{-- ── Kanban Board ── --}}
        <div class="ok-board" id="kanbanBoard">
            @foreach($stages as $stage => $cfg)
                @php $sid = str_replace(' ', '-', $stage); @endphp
                <div class="ok-col" data-stage="{{ $stage }}"
                     ondrop="drop(event)" ondragover="allowDrop(event)">

                    {{-- Column header --}}
                    <div class="ok-col-header" style="border-bottom:3px solid {{ $cfg[0] }};">
                        <div class="d-flex align-items-center gap-2">
                            <div class="ok-col-icon" style="background:{{ $cfg[0] }}15;color:{{ $cfg[0] }};">
                                <i class="{{ $cfg[1] }}"></i>
                            </div>
                            <span class="ok-col-title">{{ $stage }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="ok-col-count" id="colcount-{{ $sid }}"
                                  style="background:{{ $cfg[0] }}15;color:{{ $cfg[0] }};">0</span>
                        </div>
                    </div>

                    {{-- Column value total --}}
                    <div class="ok-col-total">
                        <i class="bx bx-rupee"></i>
                        <span id="colval-{{ $sid }}">0</span>
                    </div>

                    {{-- Cards container --}}
                    <div class="ok-cards" id="col-{{ $sid }}">
                        {{-- populated by JS --}}
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ── ADD DEAL OFFCANVAS ── --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="addDealOffcanvas"
     style="width:440px; max-width:100vw;">
    <div class="offcanvas-header" style="background:linear-gradient(135deg,#006666,#008080); color:#fff;">
        <div class="d-flex align-items-center gap-3">
            <div style="width:36px;height:36px;background:rgba(255,255,255,0.15);border-radius:10px;
                        display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                <i class="bx bx-trending-up"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">New Deal</h6>
                <small style="opacity:0.75;">Add an opportunity to the pipeline</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ route('opportunities.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="ml-label">Deal Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-pencil"></i></span>
                    <input type="text" name="name" class="form-control"
                           placeholder="e.g. Website Redesign" required>
                </div>
            </div>
            <div class="col-12">
                <label class="ml-label">Customer <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-building"></i></span>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Select Customer…</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company ?? $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <label class="ml-label">Deal Value (₹)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-rupee"></i></span>
                    <input type="number" name="amount" class="form-control" placeholder="50000">
                </div>
            </div>
            <div class="col-6">
                <label class="ml-label">Stage</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-git-branch"></i></span>
                    <select name="stage" class="form-select" required>
                        <option value="New">New</option>
                        <option value="Qualified">Qualified</option>
                        <option value="Proposal">Proposal</option>
                        <option value="Negotiation">Negotiation</option>
                        <option value="Closed Won">Closed Won</option>
                        <option value="Closed Lost">Closed Lost</option>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <label class="ml-label">Expected Close Date</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                    <input type="date" name="expected_close_date" class="form-control">
                </div>
            </div>
            <div class="col-12 mt-2">
                <button type="submit" class="lb-btn lb-btn-primary w-100">
                    <i class="bx bx-check-circle"></i> Create Deal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const STAGE_COLORS = {
    'New':         '#1a73e8',
    'Qualified':   '#9334e9',
    'Proposal':    '#f29900',
    'Negotiation': '#006666',
    'Closed Won':  '#34a853',
    'Closed Lost': '#ea4335',
};

function formatCurrency(val) {
    return '₹' + Number(val).toLocaleString('en-IN');
}

function stageId(s) { return s.replace(/\s+/g, '-'); }

function loadKanban() {
    $.get("{{ route('opportunities.kanban_data') }}", function (res) {
        $('.ok-cards').html('<div class="ok-empty"><i class="bx bx-package"></i><span>No deals</span></div>');

        let counts = {}, values = {};
        Object.keys(STAGE_COLORS).forEach(s => { counts[s] = 0; values[s] = 0; });

        res.data.forEach(function (opp) {
            let stage = STAGE_COLORS.hasOwnProperty(opp.stage) ? opp.stage : 'New';
            counts[stage]++;
            values[stage] += Number(opp.amount || 0);

            let sid      = stageId(stage);
            let color    = STAGE_COLORS[stage];
            let amount   = opp.amount ? formatCurrency(opp.amount) : '—';
            let closeDate = opp.expected_close_date || '';
            let isOverdue = closeDate && new Date(closeDate) < new Date() && stage !== 'Closed Won' && stage !== 'Closed Lost';
            let dateLabel = closeDate
                ? `<span class="ok-card-date ${isOverdue ? 'ok-date-overdue' : ''}">
                       <i class="bx bx-calendar"></i> ${closeDate}
                   </span>` : '';

            let client = opp.company_name || opp.client_name || 'Unknown';
            // Remove empty state if present
            let $col = $('#col-' + sid);
            $col.find('.ok-empty').remove();

            let cardHtml = `
            <div class="ok-card" id="opp-${opp.id}" draggable="true"
                 ondragstart="drag(event)" data-id="${opp.id}"
                 style="border-left-color:${color};">
                <div class="ok-card-title">${opp.name}</div>
                <div class="ok-card-company">
                    <i class="bx bx-building" style="color:${color};"></i>
                    <span>${client}</span>
                </div>
                <div class="ok-card-footer">
                    <span class="ok-card-amount" style="color:${color};">
                        <i class="bx bx-rupee"></i>${Number(opp.amount || 0).toLocaleString('en-IN')}
                    </span>
                    ${dateLabel}
                </div>
            </div>`;
            $col.append(cardHtml);
        });

        // Update stage strip + col headers
        let totalPipeline = 0;
        Object.keys(STAGE_COLORS).forEach(s => {
            let sid = stageId(s);
            $('#count-' + sid).text(counts[s]);
            $('#colcount-' + sid).text(counts[s]);
            let fv = formatCurrency(values[s]);
            $('#val-' + sid).text(fv);
            $('#colval-' + sid).text(values[s].toLocaleString('en-IN'));
            if (s !== 'Closed Lost') totalPipeline += values[s];
        });
        $('#pipelineTotalVal').text(totalPipeline.toLocaleString('en-IN'));
    });
}

$(document).ready(function () { loadKanban(); });

// Drag and Drop
function allowDrop(ev) {
    ev.preventDefault();
    $(ev.currentTarget).addClass('ok-drag-over');
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    ev.dataTransfer.setData("oppId", $(ev.target).data('id'));
}

$(document).on('dragleave drop', '.ok-col', function (e) {
    $(this).removeClass('ok-drag-over');
});

function drop(ev) {
    ev.preventDefault();
    $(ev.currentTarget).removeClass('ok-drag-over');

    let data   = ev.dataTransfer.getData("text");
    let oppId  = ev.dataTransfer.getData("oppId");
    let container = $(ev.currentTarget).find('.ok-cards')[0];
    container.appendChild(document.getElementById(data));

    let newStage = $(ev.currentTarget).data('stage');
    let reason   = '';
    if (newStage === 'Closed Lost') {
        reason = prompt("Reason for losing this deal (optional):");
    }
    updateOppStage(oppId, newStage, reason);
}

function updateOppStage(oppId, newStage, reason) {
    $.post("{{ route('opportunities.update_stage') }}", {
        _token: "{{ csrf_token() }}", id: oppId, stage: newStage, reason: reason
    }, function () { loadKanban(); }).fail(function () {
        alert('Error updating deal stage.');
        loadKanban();
    });
}
</script>
@endsection
