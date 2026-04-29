<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Lead_comments;
use App\Models\Opportunity;
use App\Models\Proposals;
use App\Models\Invoices;
use App\Models\CrmTask;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class Customer360Controller extends Controller
{
    public function view($type, $id)
    {
        if ($type === 'lead') {
            $customer = Leads::findOrFail($id);
            $comments = Lead_comments::where('lead_id', $id)->latest()->get();
            $opportunities = Opportunity::where('customer_id', $id)->latest()->get();
            $proposals = Proposals::where('lead_id', $id)->where('related', 1)->latest()->get();
            $invoices = collect(); // Leads usually don't have invoices until they become clients
        } else {
            $customer = Clients::findOrFail($id);
            // Link back to lead comments if available
            $comments = Lead_comments::where('lead_id', $customer->commentLeadID)->latest()->get();
            $opportunities = Opportunity::where('customer_id', $customer->id)->latest()->get();
            $proposals = Proposals::where('lead_id', $customer->id)->where('related', 2)->latest()->get();
            $invoices = Invoices::where('client_id', $customer->id)->latest()->get();
        }

        $activities = Activity::where('causer_id', $id)->latest()->take(20)->get();
        $tasks = CrmTask::where('lead_id', $id)->latest()->get();

        return view('customer360.view', compact('customer', 'type', 'comments', 'opportunities', 'proposals', 'invoices', 'activities', 'tasks'));
    }
}
