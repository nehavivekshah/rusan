@extends('layout')
@section('title', 'Reports & Forecasting - Rusan')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Sales Analytics & Forecasting'])

        <div class="dash-container">
            {{-- ── Stat Cards ── --}}
            <div class="rv-stat-row mb-4">
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-line-chart"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num">₹{{ number_format($totalPipelineValue) }}</div>
                        <div class="rv-stat-label">Total Pipeline</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(52,168,83,0.1);color:#163f7a;">
                        <i class="bx bx-check-shield"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#163f7a;">₹{{ number_format($totalWonValue) }}</div>
                        <div class="rv-stat-label">Revenue Won</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(251,188,4,0.1);color:#f9a825;">
                        <i class="bx bx-target-lock"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#f9a825;">{{ $winRate }}%</div>
                        <div class="rv-stat-label">Avg. Win Rate</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                        <i class="bx bx-trending-up"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#ea4335;">+12%</div>
                        <div class="rv-stat-label">Monthly Growth</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-4">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-bar-chart-alt-2"></i>
                        Live Analytics Data
                    </span>
                </div>
                <div class="leads-toolbar-right">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh Analytics">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button class="lb-btn" onclick="window.print()" style="background:#fff; border:1.5px solid #e8eaed; color:#5f6368; border-radius:10px; gap:6px;">
                        <i class="bx bx-download"></i>
                        <span class="d-none d-sm-inline">Export PDF</span>
                    </button>
                </div>
            </div>

            <div class="row g-4 mb-4">
                {{-- ── Forecast Chart ── --}}
                <div class="col-lg-12">
                    <div class="dash-card">
                        <div class="p-4">
                            <h5 class="fw-800 mb-2" style="color:#163f7a;">Expected Revenue Forecast</h5>
                            <p class="text-muted small mb-4">AI-driven forecast using weighted probability multipliers across sales stages.</p>
                            <div style="height: 350px;">
                                <canvas id="forecastChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Agent Performance Table ── --}}
                <div class="col-lg-12">
                    <div class="dash-card">
                        <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-800 m-0" style="color:#163f7a;">Agent Sales Performance</h5>
                            <span class="badge bg-light text-muted fw-bold px-3">Real-time Ranking</span>
                        </div>
                        <div class="table-responsive">
                            <table class="leads-table" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Agent Name</th>
                                        <th>Total Deals</th>
                                        <th style="width:120px;">Won</th>
                                        <th style="width:250px;">Win Ratio</th>
                                        <th class="text-end">Revenue Generated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($agentPerformance as $agent)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="lb-avatar-sm" style="background:rgba(26,115,232,0.1); color:#1a73e8; border:none; font-weight:800;">
                                                        {{ substr($agent->agent_name ?? 'U', 0, 1) }}
                                                    </div>
                                                    <div class="fw-600 text-dark">{{ $agent->agent_name ?? 'Unassigned' }}</div>
                                                </div>
                                            </td>
                                            <td><span class="fw-600">{{ $agent->total_deals }}</span> Deals</td>
                                            <td>
                                                <span class="rv-status-pill" style="background:#163f7a15; color:#163f7a;">
                                                    <i class="bx bx-check"></i> {{ $agent->won_deals }} Won
                                                </span>
                                            </td>
                                            <td>
                                                @php $pct = $agent->total_deals > 0 ? ($agent->won_deals / $agent->total_deals) * 100 : 0; @endphp
                                                <div class="d-flex flex-column gap-1">
                                                    <div class="progress" style="height: 6px; background:#f1f3f4; border-radius:10px;">
                                                        <div class="progress-bar" style="width: {{ $pct }}%; background:#163f7a; border-radius:10px;"></div>
                                                    </div>
                                                    <div class="text-muted" style="font-size:0.7rem; font-weight:600;">{{ round($pct, 1) }}% Conversion</div>
                                                </div>
                                            </td>
                                            <td class="text-end fw-800 text-success">₹{{ number_format($agent->revenue_generated) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No conversion data found for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        /* ── Page Layout ── */
        .dash-container { padding: 24px 24px 24px; }
        .rv-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        @media (max-width: 991px) { .rv-stat-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px) { .rv-stat-row { grid-template-columns: repeat(1, 1fr); } }

        .rv-stat-card { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; padding: 18px; display: flex; align-items: center; gap: 14px; transition: all 0.2s; }
        .rv-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #d2d4d7; }
        .rv-stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .rv-stat-num { font-size: 1.4rem; font-weight: 800; color: #202124; line-height: 1; }
        .rv-stat-label { font-size: 0.72rem; color: #80868b; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }

        /* ── Status Pills ── */
        .rv-status-pill { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 4px 12px; font-size: 0.75rem; font-weight: 700; }
        .rv-status-pill i { font-size: 0.9rem; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('forecastChart').getContext('2d');
            const forecastLabels = {!! json_encode($forecastLabels) !!};
            const forecastData = {!! json_encode($forecastData) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: forecastLabels,
                    datasets: [{
                        label: 'Expected Revenue',
                        data: forecastData,
                        backgroundColor: 'rgba(26, 115, 232, 0.7)',
                        hoverBackgroundColor: 'rgba(26, 115, 232, 1)',
                        borderRadius: 6,
                        barThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f3f4' },
                            ticks: {
                                color: '#9aa0a6',
                                font: { weight: 500 },
                                callback: function (value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#202124',
                                font: { weight: 600 }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#202124',
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 },
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return '₹' + context.raw.toLocaleString('en-IN') + ' Expected';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
