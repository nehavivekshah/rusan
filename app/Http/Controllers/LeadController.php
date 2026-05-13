<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\CustomMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\AuthController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

use Carbon\Carbon;
use App\Models\SmtpSettings;
use App\Models\Companies;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Lead_comments;
use App\Models\Proposals;
use App\Models\Proposal_items;
use App\Models\Proposal_signatures;
use App\Models\Projects;
use Exception;
use DateTime;

use App\Services\LeadService;
use App\Services\ProposalService;
use App\Traits\ActivityLogger;

class LeadController extends Controller
{
    use ActivityLogger;

    protected $leadService;
    protected $proposalService;

    public function __construct(LeadService $leadService, ProposalService $proposalService)
    {
        $this->leadService = $leadService;
        $this->proposalService = $proposalService;
    }
    /*public function leads(Request $request)
    {
        // Build the base query
        $query = Leads::leftJoin('lead_comments', function($join) {
                $join->on('leads.id', '=', 'lead_comments.lead_id')
                    ->whereIn('lead_comments.next_date', function ($query) {
                        $query->select(DB::raw('MAX(next_date)'))
                            ->from('lead_comments')
                            ->whereColumn('lead_comments.lead_id', 'leads.id');
                    });
            })
            ->select(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.gstno', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.msg',
                DB::raw('MAX(lead_comments.next_date) as next_date'),
                DB::raw('MAX(lead_comments.created_at) as last_talk')
            )
            ->groupBy(
                'leads.id', 
                'leads.cid', 
                'leads.name', 
                'leads.company', 
                'leads.email', 
                'leads.mob', 
                'leads.gstno', 
                'leads.whatsapp', 
                'leads.location', 
                'leads.purpose', 
                'leads.assigned',   
                'leads.values',
                'leads.poc', 
                'leads.status', 
                'leads.created_at', 
                'leads.updated_at',
                'lead_comments.msg'
            );

        // Filter by CID if not master
        if (Auth::user()->role != 'master') {
            $query->where('leads.cid', '=', Auth::user()->cid);
        }

        // Apply search
        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('leads.name', 'like', "%{$search}%")
                    ->orWhere('leads.company', 'like', "%{$search}%")
                    ->orWhere('leads.email', 'like', "%{$search}%")
                    ->orWhere('leads.mob', 'like', "%{$search}%")
                    ->orWhere('leads.whatsapp', 'like', "%{$search}%")
                    ->orWhere('leads.location', 'like', "%{$search}%")
                    ->orWhere('leads.purpose', 'like', "%{$search}%")
                    ->orWhere('leads.assigned', 'like', "%{$search}%")
                    ->orWhere('leads.values', 'like', "%{$search}%")
                    ->orWhere('leads.poc', 'like', "%{$search}%")
                    ->orWhere('lead_comments.msg', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        $status = $request->input('status');
        if ($status !== null) {
            $query->where(function ($q) use ($status) {
                $q->where('leads.status', '=', $status);
            });
        }

        // Get all leads
        $allLeads = $query->get();

        // Today's date
        $today = Carbon::today();

        // Sort the leads by your custom logic
        $allLeads = $allLeads->sortBy(function ($lead) use ($today) {
            $nextDate = $lead->next_date ? Carbon::parse($lead->next_date) : null;

            // Priority logic
            if ($lead->status == 1 && $nextDate && $nextDate->lte($today)) {
                $priority = 0;
            } elseif ($lead->status == 0) {
                $priority = 1;
            } elseif ($lead->status == 1 && $nextDate && $nextDate->gt($today)) {
                $priority = 2;
            } else {
                $priority = 3;
            }

            // Secondary sort: by next_date (closest first)
            $timestamp = $nextDate ? $nextDate->timestamp : PHP_INT_MAX;

            return [$priority, $timestamp];
        })->values();

        // Prepare reminder timestamps
        $reminderTimes = $allLeads->map(function ($lead) {
            return $lead->next_date ? Carbon::parse($lead->next_date)->timestamp * 1000 : null;
        });

        // Pagination manually
        $perPage = 50;
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $totalLeads = $allLeads->count();
        $leads = $allLeads->slice($offset, $perPage);
        $totalPages = (int) ceil($totalLeads / $perPage);

        // Active users of the same company
        $getUsers = User::where('status', '=', '1')->get();

        return view('leads', [
            'leads'         => $leads,
            'reminderTimes' => $reminderTimes,
            'currentPage'   => $currentPage,
            'totalPages'    => $totalPages,
            'totalLeads'    => $totalLeads,
            'perPage'       => $perPage,
            'search'        => $search,
            'getUsers'      => $getUsers
        ]);
    }*/

    public function leads(Request $request)
    {
        $perPage = $request->rowcount ?? 50;
        $search = $request->input('search');
        $status = $request->input('status');

        $leads = $this->leadService->getPaginatedLeads($search, $status, $perPage);

        return view('leads', [
            'leads' => $leads,
            'reminderTimes' => $leads->map(fn($lead) => $lead->next_date ? Carbon::parse($lead->next_date)->timestamp * 1000 : null),
            'currentPage' => $leads->currentPage(),
            'totalPages' => $leads->lastPage(),
            'totalLeads' => $leads->total(),
            'perPage' => $perPage,
            'search' => $search,
            'getUsers' => User::where('status', '1')->get()
        ]);
    }

    public function leadList(Request $request)
    {
        $leads = Leads::select('id', 'name', 'company', 'email', 'mob', 'location')->where('name', '!=', '')->orderBy('name', 'ASC')->get();

        return json_encode(['leads' => $leads]);
    }

    public function singleLeadsGet(Request $request)
    {
        $id = ($request->id ?? '');
        $page = ($request->pagename ?? '');
        if ($page == 'leads') {

            $leads = Leads::where('id', '=', $request->id)->first();
            $leadComments = Lead_comments::where('lead_id', '=', ($leads->id ?? ''))->get();
            $receivedEmails = \App\Models\ReceivedEmail::where('lead_id', '=', ($leads->id ?? ''))
                ->orderBy('received_at', 'DESC')
                ->get();

            $proposals = Proposals::leftJoin('leads', 'proposals.lead_id', '=', 'leads.id')
                ->select('leads.name as lead_name', 'proposals.*')
                ->where('leads.id', '=', $id)
                ->orderBy('proposals.proposal_date', 'DESC')
                ->orderBy('proposals.id', 'DESC')->get();

            return json_encode([
                'leads' => $leads, 
                'leadComments' => $leadComments, 
                'receivedEmails' => $receivedEmails,
                'proposals' => $proposals
            ]);
        }
    }

    public function manageLead(Request $request)
    {

        $leads = Leads::where('id', '=', $request->id)->first();

        $leadComments = Lead_comments::where('lead_id', '=', ($leads->id ?? ''))->get();

        $salesUsers = User::where('status', '1')
            ->orderBy('name')
            ->get();

        return view('manageLead', [
            'leads'       => $leads,
            'leadComments'=> $leadComments,
            'salesUsers'  => $salesUsers,
        ]);

    }



    public function manageLeadPost(Request $request)
    {

        $location = json_encode($request->address ?? []);

        $currentPage = $request->page ?? 1;
        $from        = $request->from;  // 'kanban' or null
        $redirectTo  = ($from === 'kanban') ? 'leads/kanban' : ('leads?page=' . $currentPage);

        if (empty($request->id)) {
            $leadSingle = new Leads();

            $leadSingle->name = trim(($request->first_name ?? '') . ' ' . ($request->middle_name ?? '') . ' ' . ($request->last_name ?? ''));
            $leadSingle->first_name = ($request->first_name ?? '');
            $leadSingle->middle_name = ($request->middle_name ?? '');
            $leadSingle->last_name = ($request->last_name ?? '');
            $leadSingle->gender = ($request->gender ?? '');
            $leadSingle->dob = ($request->dob ?? null);
            $leadSingle->progress = ($request->progress ?? '');
            
            $leadSingle->email = ($request->email ?? '');
            $leadSingle->mob = ($request->mob ?? '');
            $leadSingle->whatsapp = ($request->whatsapp ?? '');
            
            $leadSingle->company = ($request->company ?? '');
            $leadSingle->industry = ($request->industry ?? '');
            $leadSingle->interested_product = ($request->product ?? '');
            $leadSingle->source = ($request->source ?? '');
            $leadSingle->email_opt_out = $request->has('email_opt_out') ? 1 : 0;
            $leadSingle->sms_opt_out = $request->has('sms_opt_out') ? 1 : 0;

            $leadSingle->website = ($request->website ?? '');
            $leadSingle->address = ($request->address ?? '');
            $leadSingle->city = ($request->city ?? '');
            $leadSingle->state = ($request->state ?? '');
            $leadSingle->country = ($request->country ?? '');
            $leadSingle->pin_code = ($request->pin_code ?? '');
            $leadSingle->location = json_encode([
                'address' => $request->address ?? '',
                'city' => $request->city ?? '',
                'state' => $request->state ?? '',
                'country' => $request->country ?? '',
                'zip' => $request->pin_code ?? ''
            ]);

            $leadSingle->lead_state = ($request->lead_state ?? '');
            $leadSingle->last_call_feedback = ($request->last_call_feedback ?? '');
            $leadSingle->last_call_comment = ($request->last_call_comment ?? '');
            $leadSingle->next_call_date = ($request->nxtDate ?? null);
            $leadSingle->marketing_source = ($request->marketing_source ?? '');
            
            $leadSingle->age = ($request->age ?? null);
            $leadSingle->consumption_years = ($request->consumption_years ?? null);
            $leadSingle->tobacco_frequency = ($request->tobacco_frequency ?? '');
            $leadSingle->craving_for_smoking = ($request->craving_for_smoking ?? '');
            $leadSingle->problem_smoking = ($request->problem_smoking ?? '');
            $leadSingle->experience_intense_craving = ($request->experience_intense_craving ?? '');

            $leadSingle->cid = (Auth::user()->cid ?? '');
            
            // Auto-Assignment Logic: If current user is Sales, assign to self
            $roles = session('roles'); 
            $isSales = $roles && str_contains(strtolower($roles->title), 'sales');

            if ($isSales) {
                $leadSingle->assigned = Auth::id();
            } else {
                $leadSingle->assigned = $request->assigned ?: $this->leadService->getLeastLoadedUser(Auth::user()->cid);
            }

            // Lead Scoring Algorithm
            $leadSingle->score = $this->leadService->calculateScore($request->all());

            // Duplicate Detection
            $leadSingle->is_duplicate = $this->leadService->isDuplicate($request->email, $request->mob, Auth::user()->cid) ? 1 : 0;

            if ((!empty($request->nxtDate) && (new DateTime($request->nxtDate) > new DateTime())) || !empty($request->last_call_comment)) {
                $leadSingle->status = ($request->status ?? '1');
            } else {
                $leadSingle->status = ($request->status ?? '0');
            }

            if ($leadSingle->save()) {
                // CRM Lifecycle Hook: Auto-create follow-up task
                $this->leadService->createFollowUpTask($leadSingle);

                if ((!empty($request->nxtDate) && (new DateTime($request->nxtDate) > new DateTime())) || !empty($request->last_call_comment)) {
                    $leadComment = new Lead_comments();
                    $leadComment->lead_id = ($leadSingle->id ?? '');
                    $leadComment->msg = ($request->last_call_comment ?? 'Call back at next date');
                    $leadComment->next_date = ($request->nxtDate ?? null);
                    $leadComment->save();
                }

                $this->logActivity('Lead Created', 'leads', $leadSingle->id, $leadSingle->name, "Added new lead: {$leadSingle->name}");

                return redirect($redirectTo)->with('success', 'This Lead was successfully added to the Leads Table.');
            } else {
                return redirect($redirectTo)->with('error', 'Failed to add this lead to the leads table.');
            }

        } else {

            // Updating an existing lead
            $id = $request->id ?? '';

            if (($request->status ?? '') == '5') {

                $leadSingle = Leads::find($id);

                // Creating a new client/customer
                $client = new Clients();

                $client->cid = (Auth::user()->cid ?? '');
                $client->commentLeadID = ($id ?? '');
                $client->name = trim(($request->first_name ?? '') . ' ' . ($request->middle_name ?? '') . ' ' . ($request->last_name ?? ''));
                $client->email = ($request->email ?? '');
                $client->mob = ($request->mob ?? '');
                $client->whatsapp = ($request->whatsapp ?? '');
                $client->company = ($request->company ?? '');
                $client->industry = ($request->industry ?? '');
                $client->location = json_encode([
                    'address' => $request->address ?? '',
                    'city' => $request->city ?? '',
                    'state' => $request->state ?? '',
                    'country' => $request->country ?? '',
                    'zip' => $request->pin_code ?? ''
                ]);
                $client->website = ($request->website ?? '');
                $client->status = '0';

                if ($client->save()) {

                    $proposal = Proposals::where('lead_id', $id)->first(); 

                    if ($proposal) {
                        $proposal->lead_id = $client->id;
                        $proposal->related = 2;
                        $proposal->save();
                    }

                    $leadSingle->status = '5'; // Converted
                    $leadSingle->update();


                    $this->logActivity('Lead Converted', 'leads', (int)$id, $client->name, "Lead converted to customer: {$client->name}");

                    return redirect($redirectTo)->with('success', "Successfully converted leads moved to client list.");
                } else {
                    return redirect($redirectTo)->with('error', 'Failed to add this lead to the client list.');
                }

            } else {

                $leadSingle = Leads::find($id);

                if (!$leadSingle) {
                    return back()->with('error', 'Lead not found.');
                }

                $leadSingle->cid = (Auth::user()->cid ?? '');
                $leadSingle->name = trim(($request->first_name ?? '') . ' ' . ($request->middle_name ?? '') . ' ' . ($request->last_name ?? ''));
                $leadSingle->first_name = ($request->first_name ?? '');
                $leadSingle->middle_name = ($request->middle_name ?? '');
                $leadSingle->last_name = ($request->last_name ?? '');
                $leadSingle->gender = ($request->gender ?? '');
                $leadSingle->dob = ($request->dob ?? null);
                $leadSingle->progress = ($request->progress ?? '');
                
                $leadSingle->email = ($request->email ?? '');
                $leadSingle->mob = ($request->mob ?? '');
                $leadSingle->whatsapp = ($request->whatsapp ?? '');
                
                $leadSingle->company = ($request->company ?? '');
                $leadSingle->industry = ($request->industry ?? '');
                $leadSingle->interested_product = ($request->product ?? '');
                $leadSingle->source = ($request->source ?? '');
                $leadSingle->email_opt_out = $request->has('email_opt_out') ? 1 : 0;
                $leadSingle->sms_opt_out = $request->has('sms_opt_out') ? 1 : 0;

                $leadSingle->website = ($request->website ?? '');
                $leadSingle->address = ($request->address ?? '');
                $leadSingle->city = ($request->city ?? '');
                $leadSingle->state = ($request->state ?? '');
                $leadSingle->country = ($request->country ?? '');
                $leadSingle->pin_code = ($request->pin_code ?? '');
                $leadSingle->location = json_encode([
                    'address' => $request->address ?? '',
                    'city' => $request->city ?? '',
                    'state' => $request->state ?? '',
                    'country' => $request->country ?? '',
                    'zip' => $request->pin_code ?? ''
                ]);

                $leadSingle->lead_state = ($request->lead_state ?? '');
                $leadSingle->last_call_feedback = ($request->last_call_feedback ?? '');
                $leadSingle->last_call_comment = ($request->last_call_comment ?? '');
                $leadSingle->next_call_date = ($request->nxtDate ?? null);
                $leadSingle->marketing_source = ($request->marketing_source ?? '');
                
                $leadSingle->age = ($request->age ?? null);
                $leadSingle->consumption_years = ($request->consumption_years ?? null);
                $leadSingle->tobacco_frequency = ($request->tobacco_frequency ?? '');
                $leadSingle->craving_for_smoking = ($request->craving_for_smoking ?? '');
                $leadSingle->problem_smoking = ($request->problem_smoking ?? '');
                $leadSingle->experience_intense_craving = ($request->experience_intense_craving ?? '');

                $leadSingle->assigned = ($request->assigned ?? '');
                $leadSingle->status = ($request->status ?? '10');

                if ($leadSingle->update()) {
                    $this->logActivity('Lead Updated', 'leads', (int)$id, $leadSingle->name, "Updated lead: {$leadSingle->name}");

                    return redirect($redirectTo)->with('success', 'This Lead was successfully updated in the Leads Table.');
                } else {
                    return redirect($redirectTo)->with('error', 'Failed to update this lead in the leads table.');
                }

            }
        }
    }

    public function manageLeadCommentPost(Request $request)
    {

        $currentPage = $request->page ?? 1;

        if (empty($request->id)) {

            $leadId = $request->lead_id ?? null;
            $clientId = $request->client_id ?? null;

            // Creating a new lead comment
            $leadComment = new Lead_comments();

            $leadComment->lead_id = $leadId;
            $leadComment->msg = $request->message;
            $leadComment->next_date = $request->nxtDate ?? null;

            if ($leadComment->save()) {

                if (!empty($leadId)) {

                    $leadSingle = Leads::find($leadId);

                    $leadSingle->status = '1';
                    $leadSingle->update();

                    $this->logActivity('Lead Comment Added', 'leads', (int)$leadId, null, "Added follow-up comment on lead #{$leadId}");

                    return redirect('leads?page=' . $currentPage)->with('success', 'This comment was successfully added to the Leads Table.');

                } else {
                    return redirect('clients?page=' . $currentPage)->with('success', 'This comment was successfully added to the client Table.');
                }
            } else {
                if (!empty($leadId)) {
                    return redirect('leads?page=' . $currentPage)->with('error', 'Failed to add this comment to the leads table.');
                } else {
                    return redirect('clients?page=' . $currentPage)->with('error', 'Failed to add this comment to the clients table.');
                }
            }

        } else {
            // Updating an existing lead comment
            $id = $request->id ?? '';

            $leadComment = Lead_comments::find($id);

            if (!$leadComment) {
                return redirect('leads?page=' . $currentPage)->with('error', 'Lead comment not found.');
            }

            $leadComment->msg = $request->message;
            $leadComment->next_date = $request->nxtDate ?? null;


            $leadId = $request->lead_id ?? $leadComment->lead_id;

            if ($leadComment->update()) {

                $leadSingle = Leads::find($leadId);

                $leadSingle->status = '1';
                $leadSingle->update();

                return redirect('leads?page=' . $currentPage)->with('success', 'This lead comment was successfully updated in the Leads Table.');
            } else {
                return redirect('leads?page=' . $currentPage)->with('error', 'Failed to update this lead comment in the leads table.');
            }
        }
    }

    public function proposals()
    {
        // Fetch proposals with dynamic joins and select based on 'related' field
        $query = Proposals::leftJoin('users', 'proposals.uid', '=', 'users.id')
            ->leftJoin('leads', 'proposals.lead_id', '=', 'leads.id')
            ->leftJoin('clients', 'proposals.lead_id', '=', 'clients.id');

        // Conditional select based on 'related' status
        $query->selectRaw("
            CASE 
                WHEN proposals.related = 1 THEN leads.name
                ELSE clients.name
            END AS lead_name,
            CASE 
                WHEN proposals.related = 1 THEN NULL
                ELSE clients.company
            END AS company,
            proposals.*
        ");

        $query->orderBy('proposals.proposal_date', 'DESC')
            ->orderBy('proposals.id', 'DESC');

        // Get results
        $proposals = $query->get();

        return view('proposals', ['proposals' => $proposals]);
    }

    public function manageProposal(Request $request)
    {
        $id = $request->id ?? null; // or just $request->id

        // If there's an ID, load one proposal
        if ($id) {
            // `first()` returns a single model or null (not a collection).
            $proposal = Proposals::where('id', $id)->first();
            // Alternatively: $proposal = Proposals::find($id);

            // Get items for that single invoice
            $proposalItems = Proposal_items::where('proposal_id', $id)->get();
        } else {
            // No ID means we're creating a NEW invoice
            // You can create a blank model or set $invoice = null
            $proposal = null;
            // No items for a new invoice
            $proposalItems = collect();
        }

        $leads = Leads::where('name', '!=', '')->orderBy('name', 'ASC')->get();

        $clients = Clients::where('name', '!=', '')->orderBy('name', 'ASC')->get();

        $companies = Companies::where('id', '=', Auth::User()->cid)->first();

        $preloadProject = null;
        $projectId = $request->project_id;
        if ($projectId) {
            $preloadProject = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
                ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company', 'clients.email as client_email', 'clients.mob as client_mob', 'clients.whatsapp as client_whatsapp', 'clients.industry as client_industry', 'clients.poc as client_poc', 'clients.location as client_location')
                ->where('projects.id', $projectId)
                ->first();
        }

        return view('manageProposal', [
            'proposal' => $proposal,
            'proposalItems' => $proposalItems,
            'leads' => $leads,
            'clients' => $clients,
            'companies' => $companies,
            'preloadProject' => $preloadProject,
            'previous_url' => $request->input('previous_url', url()->previous())
        ]);
    }

    public function manageProposalPost(Request $request)
    {
        // 1) Validate main proposal fields
        $validatedData = $request->validate([
            'lead_id' => 'required|integer',
            'subject' => 'required|string|max:255',
            'related' => 'nullable|integer',
            'proposal_date' => 'required|date',
            'open_till' => 'nullable|date',
            'currency' => 'nullable|string|max:10',
            'discount_type' => 'nullable|in:none,before-tax,after-tax',
            'discount_percentage' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string',
            'client_city' => 'nullable|string|max:100',
            'client_state' => 'nullable|string|max:100',
            'client_zip' => 'nullable|string|max:20',
            'client_country' => 'nullable|string|max:100',
            'sub_total' => 'nullable|string',
            'discount_amount_calculated' => 'nullable|string',
            'cgst_total' => 'nullable|string',
            'sgst_total' => 'nullable|string',
            'igst_total' => 'nullable|string',
            'vat_total' => 'nullable|string',
            'adjustment_amount' => 'nullable|numeric',
            'grand_total' => 'nullable|string',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected',
            'id' => 'nullable|integer|exists:proposals,id'
        ]);

        // 2) Convert string amounts to numeric values
        $subTotal = $this->proposalService->convertCurrencyStringToNumber($validatedData['sub_total'] ?? '0');
        $discountAmountCalculated = $this->proposalService->convertCurrencyStringToNumber($validatedData['discount_amount_calculated'] ?? '0');
        $cgst_total = $this->proposalService->convertCurrencyStringToNumber($validatedData['cgst_total'] ?? '0');
        $sgst_total = $this->proposalService->convertCurrencyStringToNumber($validatedData['sgst_total'] ?? '0');
        $igst_total = $this->proposalService->convertCurrencyStringToNumber($validatedData['igst_total'] ?? '0');
        $vat_total = $this->proposalService->convertCurrencyStringToNumber($validatedData['vat_total'] ?? '0');
        $grandTotal = $this->proposalService->convertCurrencyStringToNumber($validatedData['grand_total'] ?? '0');

        // 3) Determine if this is an update or create action
        $proposal = !empty($validatedData['id']) ? Proposals::findOrFail($validatedData['id']) : new Proposals();
        if (!$proposal->exists) {
            $proposal->uid = Auth::User()->id ?? null;
        }

        // 4) Assign values
        $proposal->fill(array_merge($validatedData, [
            'sub_total' => $subTotal,
            'discount_amount_calculated' => $discountAmountCalculated,
            'cgst_total' => $cgst_total,
            'sgst_total' => $sgst_total,
            'igst_total' => $igst_total,
            'vat_total' => $vat_total,
            'grand_total' => $grandTotal,
            'status' => ($request->submit == 'Save & Send') ? ($validatedData['status'] ?? 'Sent') : ($validatedData['status'] ?? 'draft')
        ]));

        if ($proposal->save()) {
            // CRM Lifecycle Hook: Auto-task on Proposal Sent
            if ($proposal->status == 'Sent') {
                $this->proposalService->createProposalFollowUpTask($proposal);
            }

            // 5) Handle proposal items via service
            if ($request->has('proposal_items')) {
                $this->proposalService->processItems($proposal, $request->input('proposal_items', []));
            }

            if ($request->submit == 'Save & Send') {
                $to = $validatedData['client_email'] ?? '';
                $subject = 'Business Proposal #000' . ($proposal->id ?? '') . ' Received: ' . ($validatedData['subject']);
                $message = "We have also attached our business proposal for your kind perusal.<br><br>
                            <b>Proposal ID:</b> #000" . ($proposal->id ?? '') . "<br>
                            <b>Valid Until:</b> " . (date_format(date_create($proposal->open_till ?? null), 'd M, Y')) . "<br>
                            You can view the full proposal at the following link: <a href='https://Rusan.com/proposal/" . ($proposal->id ?? '') . "/" . md5($proposal->client_email ?? '') . "'>View Proposal</a><br><br>
                            If you have any questions or comments, feel free to reach out or comment online. We are here to assist you.<br><br>
                            Thank you once again for your interest and trust.<br><br>";

                $viewData = [
                    "name" => ($validatedData['client_name'] ?? 'Sir/Mam'),
                    "messages" => $message,
                    "company" => (session('companies')->name ?? ''),
                    "signature" => nl2br(Auth::User()->esign) ?? "Regards<br>Webbrella Global"
                ];

                try {
                    $this->leadService->sendMail($to, $subject, 'emails.proposal', $viewData);
                    $this->logActivity('Proposal Sent', 'proposals', $proposal->id, $proposal->subject, "Sent proposal: {$proposal->subject}");
                    $redirectUrl = $request->input('previous_url') ?: '/proposals';
                    return redirect($redirectUrl)->with('success', 'Proposal sent successfully!');
                } catch (\Exception $e) {
                    \Log::error("Failed to send proposal mail: " . $e->getMessage());
                    $redirectUrl = $request->input('previous_url') ?: '/proposals';
                    return redirect($redirectUrl)->with('success', 'Proposal saved, but email could not be sent. Please check your SMTP settings.')->with('error_detail', $e->getMessage());
                }
            }

            if ($request->submit == 'Save & Send') {
                $this->logActivity('Proposal Sent', 'proposals', $proposal->id, $proposal->subject, "Sent proposal: {$proposal->subject}");
            } else {
                $this->logActivity('Proposal Saved', 'proposals', $proposal->id, $proposal->subject, "Saved proposal: {$proposal->subject}");
            }
            $redirectUrl = $request->input('previous_url') ?: '/proposals';
            return redirect($redirectUrl)->with('success', 'Proposal saved successfully!');
        }

        return back()->with('error', 'Failed to save proposal.');
    }

    // Helper function to convert currency strings like "₹100.00" to float
    private function convertCurrencyStringToNumber($currencyString)
    {
        // Remove the currency symbol (₹) and convert to float
        $currencyString = preg_replace('/[^0-9.]+/', '', $currencyString); // Remove non-numeric chars
        return (float) $currencyString;
    }

    public function proposal($id, $token)
    {
        $proposal = Proposals::leftJoin('users', 'proposals.uid', '=', 'users.id')
            ->leftJoin('companies', 'users.cid', '=', 'companies.id')
            ->leftJoin('leads', 'proposals.lead_id', '=', 'leads.id')
            ->select(
                'leads.name as lead_name',
                'companies.name as companyName',
                'companies.email as companyEmail',
                'companies.mob as companyMob',
                'companies.gst as gst',
                'companies.vat as vat',
                'companies.img as companyImg',
                'companies.address as companyAddress',
                'companies.city as companyCity',
                'companies.state as companyState',
                'companies.zipcode as companyZipCode',
                'companies.country as companyCountry',
                'proposals.*'
            )
            ->where('proposals.id', $id)
            ->first();

        $proposalItems = Proposal_items::where('proposal_id', ($proposal->id ?? ''))->get();

        if (md5($proposal->client_email) !== $token) {
            abort(403, 'Unauthorized or invalid token.');
        }

        return view('viewProposal', ['proposal' => $proposal, 'proposalItems' => $proposalItems]);

    }

    public function downloadPdf($id, $token)
    {
        try {
            $proposal = Proposals::leftJoin('users', 'proposals.uid', '=', 'users.id')
                ->leftJoin('companies', 'users.cid', '=', 'companies.id')
                ->leftJoin('leads', 'proposals.lead_id', '=', 'leads.id')
                ->select(
                    'leads.name as lead_name',
                    'companies.name as companyName',
                    'companies.email as companyEmail',
                    'companies.mob as companyMob',
                    'companies.gst as gst',
                    'companies.vat as vat',
                    'companies.img as companyImg',
                    'companies.address as companyAddress',
                    'companies.city as companyCity',
                    'companies.state as companyState',
                    'companies.zipcode as companyZipCode',
                    'companies.country as companyCountry',
                    'proposals.*'
                )
                ->where('proposals.id', $id)
                ->firstOrFail();

            $proposalItems = Proposal_items::where('proposal_id', $proposal->id)->get();

            if (md5($proposal->client_email) !== $token) {
                abort(403, 'Unauthorized or invalid token.');
            }

            // Create PDF with the same template used for preview
            $pdf = Pdf::loadView('proposals.pdf_template', [
                'proposal' => $proposal,
                'proposalItems' => $proposalItems,
            ])->setPaper('a4', 'portrait');

            $filename = "Proposal-{$proposal->id}-" . Str::slug($proposal->subject ?? 'proposal') . ".pdf";

            return $pdf->download($filename);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Proposal not found.');
        } catch (\Exception $e) {
            abort(500, 'Could not generate PDF.');
        }
    }

    public function declineProposal($id, $tocken)
    {
        $proposal = Proposals::findOrFail($id);

        $user = User::where('id', ($proposal->uid ?? ''))->first();

        $to = $user->email ?? '';
        $subject = 'Business Proposal #000' . $proposal->id . ' Declined: ' . ($proposal->subject ?? '');
        $clientName = $proposal->client_name ?? 'Sir/Mam';

        $message = "
            We have also attached our business proposal for your kind perusal.<br><br>
            <b>Proposal ID:</b> #000{$proposal->id}<br>
            <b>Valid Until:</b> " . ($proposal->open_till ? date('d M, Y', strtotime($proposal->open_till)) : '-') . "<br>
            You can view the full proposal at the following link: 
            <a href='https://Rusan.com/proposal/{$proposal->id}/" . md5($proposal->client_email) . "'>View Proposal</a><br><br>
            If you have any questions or comments, feel free to reach out or comment online. We are here to assist you.<br><br>
            Thank you once again for your interest and trust.<br><br>
        ";

        $viewName = 'emails.proposal';
        $company = session('companies');
        $signature = nl2br($user->esign ?? "Regards<br>Webbrella Global");

        $viewData = [
            "name" => $clientName,
            "messages" => $message,
            "company" => $company->name ?? '',
            "signature" => $signature
        ];

        $this->leadService->sendMail($to, $subject, $viewName, $viewData, $user->id, $user->cid);

        $proposal->status = 'Declined';
        $proposal->save();

        return back()->with('success', 'Proposal declined and email sent successfully!');
    }

    public function acceptProposal(Request $request, $id, $token)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'signature_data' => 'required|string',
        ]);

        // Decode base64 image
        $signatureData = $request->input('signature_data');
        $image = str_replace('data:image/png;base64,', '', $signatureData);
        $image = str_replace(' ', '+', $image);
        $fileName = 'signature_' . time() . '.png';

        // Save to /public/assets/images/signs/
        $path = public_path("assets/images/signs/{$fileName}");
        file_put_contents($path, base64_decode($image));

        // Save to database
        Proposal_signatures::create([
            'proposal_id' => $id,
            'token' => $token,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'signature_path' => "assets/images/signs/{$fileName}", // for later public use
        ]);

        $proposal = Proposals::findOrFail($id);

        $user = User::where('id', ($proposal->uid ?? ''))->first();

        $to = $user->email ?? '';
        $subject = 'Business Proposal #000' . $proposal->id . ' Accepted: ' . ($proposal->subject ?? '');
        $clientName = $proposal->client_name ?? 'Sir/Mam';

        $message = "
            We are pleased to inform you that your business proposal has been accepted.<br><br>
            <b>Proposal ID:</b> #000{$proposal->id}<br>
            <b>Valid Until:</b> " . ($proposal->open_till ? date('d M, Y', strtotime($proposal->open_till)) : '-') . "<br>
            You can view the full proposal at the following link: 
            <a href='https://Rusan.com/proposal/{$proposal->id}/" . md5($proposal->client_email) . "'>View Proposal</a><br><br>
            We look forward to working together and building a successful collaboration.<br><br>
            If you have any questions or suggestions, feel free to reach out to us.<br><br>
            Thank you for your trust and confidence in our company.<br><br>
        ";

        $viewName = 'emails.proposal';
        $company = session('companies');
        $signature = nl2br($user->esign ?? "Regards<br>Webbrella Global");

        $viewData = [
            "name" => $clientName,
            "messages" => $message,
            "company" => $company->name ?? '',
            "signature" => $signature
        ];

        $this->leadService->sendMail($to, $subject, $viewName, $viewData, $user->id, $user->cid);

        $proposal->status = 'Accepted';
        $proposal->save();

        // --- CRM Lifecycle Hook: Auto-convert Lead to Client ---
        if ($proposal->related == 1 && !empty($proposal->lead_id)) {
            $lead = Leads::find($proposal->lead_id);
            if ($lead) {
                // Check if already converted to avoid duplicates
                $existingClient = Clients::where('commentLeadID', $lead->id)->first();
                if (!$existingClient) {
                    $client = new Clients();
                    $client->cid = $lead->cid;
                    $client->commentLeadID = $lead->id;
                    $client->name = $lead->name;
                    $client->email = $lead->email;
                    $client->mob = $lead->mob;
                    $client->gstno = $lead->gstno ?? $lead->gst_no;
                    $client->whatsapp = $lead->whatsapp;
                    $client->company = $lead->company;
                    $client->position = $lead->position;
                    $client->industry = $lead->industry;
                    $client->location = $lead->location;
                    $client->website = $lead->website;
                    $client->purpose = $lead->purpose;
                    $client->values = $lead->values;
                    $client->language = $lead->language;
                    $client->poc = $lead->poc;
                    $client->tags = $lead->tags;
                    $client->status = '0'; // Active Customer

                    if ($client->save()) {
                        // Update proposal to link to new client
                        $proposal->lead_id = $client->id;
                        $proposal->related = 2; // Linked to Client
                        $proposal->save();

                        // Update Lead status
                        $lead->status = '5'; // Converted
                        $lead->update();
                    }
                }
            }
        }
        // -------------------------------------------------------

        return redirect()->back()->with('success', 'Signature submitted successfully.');
    }

    /*Lead Assign Controller*/
    public function leadsPost(Request $request)
    {
        $leads = Leads::leftJoin('lead_comments', function ($join) {
            $join->on('leads.id', '=', 'lead_comments.lead_id')
                ->whereIn('lead_comments.next_date', function ($query) {
                    $query->select(DB::raw('MAX(next_date)'))
                        ->from('lead_comments')
                        ->whereColumn('lead_comments.lead_id', 'leads.id');
                });
        })
            ->select(
                'leads.id',
                'leads.cid',
                'leads.name',
                'leads.company',
                'leads.email',
                'leads.mob',
                'leads.whatsapp',
                'leads.location',
                'leads.purpose',
                'leads.assigned',
                'leads.values',
                'leads.poc',
                'leads.status',
                'leads.created_at',
                'leads.updated_at',
                DB::raw('MAX(lead_comments.next_date) as next_date'),
                'lead_comments.msg'
            )
            ->groupBy(
                'leads.id',
                'leads.cid',
                'leads.name',
                'leads.company',
                'leads.email',
                'leads.mob',
                'leads.whatsapp',
                'leads.location',
                'leads.purpose',
                'leads.assigned',
                'leads.values',
                'leads.poc',
                'leads.status',
                'leads.created_at',
                'leads.updated_at',
                'lead_comments.msg'
            )
            ->orderByRaw('
            CASE 
                WHEN DATE(lead_comments.next_date) <= CURDATE() THEN 0
                ELSE 1
            END ASC
        ')
            ->orderBy('leads.status', 'ASC')
            ->orderBy('leads.created_at', 'DESC')
            ->get();

        return view('leads', ['leads' => $leads]);
    }

    public function reminderScript()
    {
        try {
            // Fetch leads with reminders
            $leads = DB::table('leads')
                ->leftJoin('lead_comments', function ($join) {
                    $join->on('leads.id', '=', 'lead_comments.lead_id')
                        ->whereIn('lead_comments.next_date', function ($query) {
                            $query->select(DB::raw('MAX(next_date)'))
                                ->from('lead_comments')
                                ->whereColumn('lead_comments.lead_id', 'leads.id');
                        });
                })
                ->select(
                    'leads.id',
                    'leads.cid',
                    'leads.name',
                    'leads.company',
                    'leads.email',
                    'leads.mob',
                    'leads.whatsapp',
                    'leads.location',
                    'leads.purpose',
                    'leads.assigned',
                    'leads.values',
                    'leads.poc',
                    'leads.status',
                    'leads.created_at',
                    'leads.updated_at',
                    'lead_comments.msg',
                    DB::raw('MAX(lead_comments.next_date) as next_date'),
                    DB::raw('MAX(lead_comments.created_at) as last_talk')
                )
                ->groupBy(
                    'leads.id',
                    'leads.cid',
                    'leads.name',
                    'leads.company',
                    'leads.email',
                    'leads.mob',
                    'leads.whatsapp',
                    'leads.location',
                    'leads.purpose',
                    'leads.assigned',
                    'leads.values',
                    'leads.poc',
                    'leads.status',
                    'leads.created_at',
                    'leads.updated_at',
                    'lead_comments.msg'
                )
                ->orderByRaw('
                    CASE 
                        WHEN leads.status = 1 AND DATE(MAX(lead_comments.next_date)) <= CURDATE() THEN 0
                        ELSE 1
                    END ASC
                ')
                ->orderBy('leads.status', 'ASC')
                ->orderBy('leads.created_at', 'DESC')
                ->get();

            foreach ($leads as $lead) {
                if ($lead->next_date && Carbon::parse($lead->next_date)->isToday()) {
                    // Prepare the notification message with a clickable link
                    $notificationLink = "https://Rusan.com/leads"; // Replace with your actual lead detail URL  Click here to view details: {$notificationLink}
                    $message = "Reminder for Lead: {$lead->name}. Message: {$lead->msg}.";

                    // Prepare the API URL
                    $url = "https://Rusan.com/api/v1/send-notification?" . http_build_query([
                        'title' => 'Rusan',
                        'msg' => $message,
                        'url' => $notificationLink,
                        'mono' => "msetah@gmail.com",
                    ]);

                    // Send the notification
                    $this->sendNotification($url);

                    // Log the result
                    Log::info("Notification sent to {$lead->email}");
                }
            }

            // --- CRM Lifecycle Hook: Auto-reminder for overdue invoices ---
            $overdueInvoices = \App\Models\Invoices::where('status', '!=', 'Paid')
                ->where('due_date', '<', now())
                ->get();

            foreach ($overdueInvoices as $invoice) {
                $client = \App\Models\Clients::find($invoice->client_id);
                if ($client) {
                    $message = "Overdue Invoice #{$invoice->invoice_number}: Balance pending from {$client->name}. Please follow up.";
                    $notificationLink = "https://Rusan.com/manage-invoice?id={$invoice->id}";

                    $url = "https://Rusan.com/api/v1/send-notification?" . http_build_query([
                        'title' => 'INVOICE OVERDUE',
                        'msg' => $message,
                        'url' => $notificationLink,
                        'mono' => "msetah@gmail.com",
                    ]);

                    $this->sendNotification($url);
                    Log::info("Invoice reminder sent for INV#{$invoice->id}");
                }
            }
            // --------------------------------------------------------------

        } catch (\Exception $e) {
            // Handle exceptions
            Log::error('Error in reminder script: ' . $e->getMessage());
        }
    }

    protected function sendNotification($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = "cURL Error: " . curl_error($curl);
            curl_close($curl);
            throw new \Exception($error);
        }

        curl_close($curl);

        return $response;
    }

    public function importLeads(Request $request)
    {
        // Counters and trackers for success/fail
        $uploadedCount = 0;
        $notUploadedCount = 0;
        $notUploadedRows = [];

        try {
            $roles = session('roles');
            $isSales = $roles && str_contains(strtolower($roles->title), 'sales');

            // Open the file and read its contents
            if (($handle = fopen($request->file('impLeadFile')->getRealPath(), 'r')) !== FALSE) {
                // Skip the header row
                fgetcsv($handle);

                $rowIndex = 1;  // Keep track of row index (start from 1 after header)

                // Loop through the CSV rows
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    /*
                     |------------------------------------------------------------------------------
                     | 1. Check required fields (name, mob, status)
                     |    Determine the "status" from data[14] if set:
                     |     - 'converted' => special handling
                     |     - 'lost' => 9
                     |     - else => 0
                     |------------------------------------------------------------------------------
                     */
                    // CSV Mapping aligned with Export format:
                    // 0: CID (skip), 1: Name, 2: Email, 3: Mobile, 4: WhatsApp, 5: Company, 6: GST No, 
                    // 7: Position, 8: Industry, 9: Location, 10: Website, 11: Assigned, 12: Purpose, 
                    // 13: Values, 14: Language, 15: POC, 16: Status, 17: Last Talk, 18: Created At, 
                    // 19: Reminder, 20: Note

                    $name = $data[1] ?? null;
                    $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                    $name = mb_substr($name, 0, 230);
                    $email = $data[2] ?? null;
                    $mob = $data[3] ?? null;
                    $whatsapp = $data[4] ?? null;
                    $company = $data[5] ?? null;
                    $company = mb_convert_encoding($company, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                    $gst_no = $data[6] ?? null;

                    $position = $data[7] ?? null;
                    $industry = $data[8] ?? null;
                    $location = json_encode(explode(',', ($data[9] ?? '')));
                    $website = $data[10] ?? null;
                    $assigned = $isSales ? Auth::id() : ($data[11] ?? null);
                    $purpose = $data[12] ?? null;
                    $values = $data[13] ?? null;
                    $language = $data[14] ?? null;
                    $poc = $data[15] ?? null;
                    $statusStr = $data[16] ?? null;

                    $last_talk_idx = 17;
                    $created_at_idx = 18;
                    $reminder_idx = 19;
                    $note_idx = 20;

                    $status = 0;
                    if (!empty($statusStr)) {
                        if ($statusStr === 'lost')
                            $status = 9;
                        elseif ($statusStr === 'converted')
                            $status = 0; // handled logic
                        else
                            $status = 0;
                    }

                    // If required fields are missing, skip row
                    // Here, we interpret "status" as we derived above, 
                    // but the presence of a name and mob is strictly required
                    if (empty($name) || empty($mob)) {
                        $notUploadedCount++;
                        $notUploadedRows[] = $rowIndex;
                        $rowIndex++;
                        continue;
                    }

                    // Parse date fields using Carbon (optional fields)
                    // We'll safely check if index is set or not
                    try {
                        $last_talk = isset($data[$last_talk_idx]) ? Carbon::parse($data[$last_talk_idx])->format('Y-m-d H:i:s') : now();
                        $created_at = isset($data[$created_at_idx]) ? Carbon::parse($data[$created_at_idx])->format('Y-m-d H:i:s') : now();
                        $reminder = isset($data[$reminder_idx]) ? Carbon::parse($data[$reminder_idx])->format('Y-m-d H:i:s') : now();
                    } catch (\Exception $e) {
                        // Fallback if date parsing fails
                        $last_talk = now();
                        $created_at = now();
                        $reminder = now();
                    }

                    /*
                     |------------------------------------------------------------------------------
                     | 2. Handle "converted" => Insert into `clients` table
                     |    Otherwise, insert/update in `leads` table
                     |------------------------------------------------------------------------------
                     */
                    if (!empty($statusStr) && $statusStr === 'converted') {
                        // 2A. If lead is converted, insert/update in 'clients'
                        $existingClient = DB::table('clients')
                            ->where('email', '=', $email)
                            ->orWhere('mob', '=', $mob)
                            ->first();

                        if ($existingClient) {
                            // Update existing client
                            DB::table('clients')
                                ->where('id', $existingClient->id)
                                ->update([
                                    'name' => $name,
                                    'email' => $email,
                                    'mob' => '+91' . $mob,
                                    'whatsapp' => $whatsapp,
                                    'company' => $company,
                                    'gstno' => $gst_no, // New Field
                                    'position' => $position,
                                    'industry' => $industry,
                                    'location' => $location,
                                    'website' => $website,
                                    'assigned' => $assigned,
                                    'purpose' => $purpose,
                                    'values' => $values,
                                    'language' => $language,
                                    'poc' => $poc,
                                    'status' => '0', // up to you how you define "converted"
                                    'updated_at' => now(),
                                ]);
                        } else {
                            // Insert new client
                            DB::table('clients')->insert([
                                'cid' => Auth::user()->cid ?? '',
                                'commentLeadID' => 0,
                                'name' => $name,
                                'email' => $email,
                                'mob' => $mob,
                                'whatsapp' => $whatsapp,
                                'company' => $company,
                                'gstno' => $gst_no, // New Field
                                'position' => $position,
                                'industry' => $industry,
                                'location' => $location,
                                'website' => $website,
                                'assigned' => $assigned,
                                'purpose' => $purpose,
                                'values' => $values,
                                'language' => $language,
                                'poc' => $poc,
                                'status' => '0',
                                'created_at' => $created_at,
                            ]);
                        }
                    } else {
                        // 2B. Insert/Update "leads" table
                        $checkLeads = Leads::where('mob', '=', $mob)->first();

                        if ($checkLeads) {
                            // Update the existing lead
                            $checkLeads->update([
                                'cid' => Auth::user()->cid ?? '',
                                'name' => $name,
                                'email' => $email,
                                'mob' => $mob,
                                'whatsapp' => $whatsapp,
                                'company' => $company,
                                'gst_no' => $gst_no, // New Field
                                'position' => $position,
                                'industry' => $industry,
                                'location' => $location,
                                'website' => $website,
                                'assigned' => $assigned,
                                'purpose' => $purpose,
                                'values' => $values,
                                'language' => $language,
                                'poc' => $poc,
                                'status' => $status,
                                'updated_at' => now(),
                            ]);

                            // Insert comment if last_talk is provided
                            if (!empty($data[$note_idx])) {
                                DB::table('lead_comments')->insert([
                                    'lead_id' => $checkLeads->id,
                                    'msg' => $data[$note_idx] ?? 'Updated Data',
                                    'next_date' => $reminder,
                                    'created_at' => $last_talk,
                                ]);
                            }
                        } else {
                            // Insert new lead
                            $lead_id = DB::table('leads')->insertGetId([
                                'cid' => Auth::user()->cid ?? '',
                                'name' => $name,
                                'email' => $email,
                                'mob' => $mob,
                                'whatsapp' => $whatsapp,
                                'company' => $company,
                                'gst_no' => $gst_no, // New Field
                                'position' => $position,
                                'industry' => $industry,
                                'location' => $location,
                                'website' => $website,
                                'assigned' => $assigned,
                                'purpose' => $purpose,
                                'values' => $values,
                                'language' => $language,
                                'poc' => $poc,
                                'status' => $status,
                                'created_at' => $created_at,
                            ]);

                            // Add initial comment if last_talk is provided
                            if (!empty($data[$note_idx])) {
                                DB::table('lead_comments')->insert([
                                    'lead_id' => $lead_id,
                                    'msg' => $data[$note_idx] ?? 'Import Data',
                                    'next_date' => $reminder,
                                    'created_at' => $last_talk,
                                ]);
                            }
                        }
                    }

                    // Successfully processed this row
                    $uploadedCount++;
                    $rowIndex++;
                }

                // Close the file after reading all rows
                fclose($handle);

                // Build a message with summary
                $summaryMessage = 'Data imported successfully! '
                    . 'Successfully Uploaded: ' . $uploadedCount . ' | '
                    . 'Not Uploaded: ' . $notUploadedCount;

                // Optionally show which row indexes failed
                if ($notUploadedCount > 0) {
                    $summaryMessage .= ' | Rows skipped: ' . implode(', ', $notUploadedRows);
                }

                return back()->with('success', $summaryMessage);
            } else {
                return back()->with('error', 'Could not open the file.');
            }
        } catch (Exception $e) {
            // Handle errors gracefully
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }

    public function exportLeads()
    {

        // Check if the user is logged in
        if (!Auth::check()) {
            // Redirect to the login page with an error message
            return redirect()->route('login')->with('error', 'Oops, something went wrong. Kindly log in to your account first, then export your file.');
        }

        // Set the headers for the CSV file download
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=leads.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        // Callback function to write the CSV content
        $callback = function () {
            // Open output buffer for writing the CSV data
            $file = fopen('php://output', 'w');

            // Write the column headers in the first row (matching the columns in the import)
            fputcsv($file, ['CID', 'Name', 'Email', 'Mobile', 'WhatsApp', 'Company', 'GST No', 'Position', 'Industry', 'Location', 'Website', 'assigned', 'Purpose', 'Values', 'Language', 'POC', 'Status', 'Last Talk Date', 'Created Date', 'Next Rimder Date', 'Note']);

            // Fetch data from the database
            $leads = DB::table('leads')
                ->select('leads.*', 'lc.msg', 'lc.next_date', 'lc.last_talk')
                ->leftJoin(DB::raw('(SELECT lead_id, msg, next_date, created_at as last_talk
                                    FROM lead_comments
                                    WHERE id IN (SELECT MAX(id) FROM lead_comments GROUP BY lead_id)
                                ) as lc'), 'leads.id', '=', 'lc.lead_id')
                ->where('leads.cid', '=', Auth::user()->cid)
                ->where('leads.status', '!=', '9')
                ->get();

            // Loop through the leads and write each row into the CSV
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->cid,
                    $lead->name,
                    $lead->email,
                    $lead->mob,
                    $lead->whatsapp,
                    $lead->company,
                    $lead->gst_no ?? $lead->gstno, // GST No
                    $lead->position,
                    $lead->industry,
                    $lead->location,
                    $lead->website,
                    $lead->assigned,
                    $lead->purpose,
                    $lead->values,
                    $lead->language,
                    $lead->poc,
                    $lead->status,
                    $lead->last_talk,
                    $lead->created_at,
                    $lead->next_date,
                    $lead->msg,
                ]);
            }

            // Close the file
            fclose($file);
        };

        // Return the response with headers and content generated from the callback
        return response()->stream($callback, 200, $headers);
    }

    public function exportAllLeads()
    {

        // Set the headers for the CSV file download
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=leads.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        // Callback function to write the CSV content
        $callback = function () {
            // Open output buffer for writing the CSV data
            $file = fopen('php://output', 'w');

            // Write the column headers in the first row (matching the columns in the import)
            fputcsv($file, ['CID', 'Name', 'Email', 'Mobile', 'WhatsApp', 'Company', 'GST No', 'Position', 'Industry', 'Location', 'Website', 'Assigned', 'Purpose', 'Values', 'Language', 'POC', 'Status']);

            // Fetch data from the database
            $leads = DB::table('leads')->get();

            // Loop through the leads and write each row into the CSV
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->cid,
                    $lead->name,
                    $lead->email,
                    $lead->mob,
                    $lead->whatsapp,
                    $lead->company,
                    $lead->gst_no ?? $lead->gstno, // GST No
                    $lead->position,
                    $lead->industry,
                    $lead->location,
                    $lead->website,
                    $lead->assigned,
                    $lead->purpose,
                    $lead->values,
                    $lead->language,
                    $lead->poc,
                    $lead->status,
                ]);
            }

            // Close the file
            fclose($file);
        };

        // Return the response with headers and content generated from the callback
        return response()->stream($callback, 200, $headers);
    }
    public function sendProposalWhatsApp($id)
    {
        $proposal = Proposals::findOrFail($id);
        
        $phone = $proposal->client_phone;
        if (empty($phone)) {
            $lead = Leads::find($proposal->lead_id);
            $phone = $lead->whatsapp ?? $lead->mob ?? '';
        }

        if (empty($phone)) {
            return back()->with('error', 'No WhatsApp number found for this client.');
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10) { $phone = '91' . $phone; }

        $wa = new \App\Services\WhatsAppService();
        $pdfUrl = url("/proposal/download/{$proposal->id}/" . md5($proposal->client_email));
        
        $result = $wa->sendDocument(
            $phone, 
            $pdfUrl, 
            "Proposal-" . str_pad($proposal->id, 4, '0', STR_PAD_LEFT) . ".pdf", 
            "Hello " . ($proposal->client_name ?: 'there') . ", please find our proposal for " . $proposal->subject
        );

        if ($result['success']) {
            $this->logActivity('Proposal Shared (WA)', 'proposals', $proposal->id, $proposal->subject, "Shared proposal via WhatsApp to {$phone}");
            return back()->with('success', 'Proposal sent successfully via WhatsApp!');
        } else {
            return back()->with('error', 'WhatsApp Failed: ' . ($result['message'] ?? 'Unknown error'));
        }
    }
}
