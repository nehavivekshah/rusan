@extends('layout')
@section('title', 'Email Tracking Report - eseCRM')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Email Tracking Analytics'])

        <div class="dash-container">
            {{-- Stats Overview --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="dash-card p-3 text-center">
                        <div class="text-muted small fw-500 mb-1">Total Emails Sent</div>
                        <div class="h3 mb-0">{{ $emails->count() }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dash-card p-3 text-center">
                        <div class="text-muted small fw-500 mb-1">Total Opens</div>
                        <div class="h3 mb-0 text-primary">{{ $emails->sum('opens') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dash-card p-3 text-center">
                        <div class="text-muted small fw-500 mb-1">Total Clicks</div>
                        <div class="h3 mb-0 text-success">{{ $emails->sum('clicks') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dash-card p-3 text-center">
                        <div class="text-muted small fw-500 mb-1">Avg Open Rate</div>
                        <div class="h3 mb-0 text-info">
                            @php
                                $sent = $emails->count();
                                $opened = $emails->where('opens', '>', 0)->count();
                                echo $sent > 0 ? round(($opened / $sent) * 100, 1) . '%' : '0%';
                            @endphp
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Emails Table --}}
            <div class="dash-card">
                <div class="dash-card-header d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h5 class="mb-0">Recent Tracked Emails</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sent Date</th>
                                <th>Recipient</th>
                                <th>Subject</th>
                                <th class="text-center">Opens</th>
                                <th class="text-center">Clicks</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emails as $email)
                                <tr>
                                    <td class="small">{{ $email->created_at->format('d M, H:i') }}</td>
                                    <td>{{ $email->recipient }}</td>
                                    <td class="fw-500 text-truncate" style="max-width: 250px;">{{ $email->subject }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $email->opens > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ $email->opens }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $email->clicks > 0 ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $email->clicks }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        @if($email->last_open)
                                            Opened: {{ $email->last_open->diffForHumans() }}
                                        @elseif($email->last_click)
                                            Clicked: {{ $email->last_click->diffForHumans() }}
                                        @else
                                            No Activity
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No emails tracked yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
