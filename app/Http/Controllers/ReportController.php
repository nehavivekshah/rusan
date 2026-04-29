<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // 1. General Metrics
        $totalPipelineValue = Opportunity::whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount');
        $totalWonValue = Opportunity::where('stage', 'Closed Won')->sum('amount');

        $wonCount = Opportunity::where('stage', 'Closed Won')->count();
        $lostCount = Opportunity::where('stage', 'Closed Lost')->count();
        $totalClosed = $wonCount + $lostCount;
        $winRate = $totalClosed > 0 ? round(($wonCount / $totalClosed) * 100, 2) : 0;

        // 2. Agent Performance
        $agentPerformance = DB::table('opportunities')
            ->leftJoin('users', 'opportunities.user_id', '=', 'users.id')
            ->select(
                'users.name as agent_name',
                DB::raw('COUNT(opportunities.id) as total_deals'),
                DB::raw('SUM(CASE WHEN opportunities.stage = "Closed Won" THEN 1 ELSE 0 END) as won_deals'),
                DB::raw('SUM(CASE WHEN opportunities.stage = "Closed Won" THEN opportunities.amount ELSE 0 END) as revenue_generated')
            )
            ->groupBy('users.id', 'users.name')
            ->get();

        // 3. Sales Forecasting (Phase 7 algorithm)
        // expected_revenue = amount * probability based on stage
        $forecasts = Opportunity::whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->whereNotNull('expected_close_date')
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->expected_close_date)->format('Y-M');
            });

        $probabilityMap = [
            'New' => 0.1,
            'Qualified' => 0.3,
            'Proposal' => 0.6,
            'Negotiation' => 0.9
        ];

        $monthlyForecast = [];
        $forecastLabels = [];
        $forecastData = [];

        foreach ($forecasts as $month => $opps) {
            $expectedRevenue = 0;
            foreach ($opps as $opp) {
                $prob = $probabilityMap[$opp->stage] ?? 0;
                $expectedRevenue += ($opp->amount * $prob);
            }
            $monthlyForecast[$month] = $expectedRevenue;
        }

        ksort($monthlyForecast);

        foreach ($monthlyForecast as $month => $val) {
            $forecastLabels[] = $month;
            $forecastData[] = $val;
        }

        return view('reports', compact(
            'totalPipelineValue',
            'totalWonValue',
            'winRate',
            'agentPerformance',
            'forecastLabels',
            'forecastData'
        ));
    }

    public function emailTracking()
    {
        $emails = \App\Models\TrackedEmail::orderBy('created_at', 'DESC')->limit(100)->get();
        return view('reports.email-tracking', compact('emails'));
    }
}
