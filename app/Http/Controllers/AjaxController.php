<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

use App\Models\Companies;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Proposals;
use App\Models\Proposal_items;
use App\Models\Task;
use App\Models\Recoveries;
use App\Models\Projects;
use App\Models\Invoices;
use App\Models\Contracts;
use App\Models\Enquiry;
use App\Models\Attendances;

class AjaxController extends Controller
{
    public function task()
    {
        return view('task');
    }

    public function tasksubmit(Request $request)
    {
        $tasks = Task::find($request->taskid);
        $tasks->name = $request->tasktitle;
        
        $tasks->update();
        
        //return redirect()->back()->with('status','Student Updated Successfully');

        return response(['success' => 'Updated.']);
    }
    
    public function ajaxSend(Request $request)
    {
        // Get the row ID from the request, or set it as an empty string if not present
        $id = $request->rowid ?? '';
    
        // Check if the userDelete action is requested
        if (($request->userDelete ?? '') == 'userDelete') {
            // Find the user by ID
            $user = User::find($id);
    
            // Check if the user exists
            if ($user) {
                // Delete the user
                $user->delete();
                return response()->json(['success' => 'User deleted successfully.']);
            } else {
                return response()->json(['error' => 'User not found.'], 404);
            }
        }elseif (($request->leadDelete ?? '') == 'leadDelete') {
            // Find the user by ID
            $leads = Leads::find($id);
    
            // Check if the user exists
            if ($leads) {
                $leads->delete();
                return response()->json(['success' => 'Leads deleted successfully.']);
            } else {
                return response()->json(['error' => 'Leads not found.'], 404);
            }
        }elseif (($request->contractDelete ?? '') == 'contractDelete') {
            // Find the user by ID
            $contracts = Contracts::find($id);
    
            // Check if the user exists
            if ($contracts) {
                $contracts->delete();
                return response()->json(['success' => 'Contract deleted successfully.']);
            } else {
                return response()->json(['error' => 'Contract not found.'], 404);
            }
        }elseif (($request->clientDelete ?? '') == 'clientDelete') {
            // Find the user by ID
            $client = Clients::find($id);
    
            // Check if the user exists
            if ($client) {
                
                Projects::where('client_id','=',$id)->delete();
                Recoveries::where('client_id','=',$id)->delete();
                // Delete the company
                $client->delete();
                
                return response()->json(['success' => 'Client deleted successfully.']);
            } else {
                return response()->json(['error' => 'Client not found.'], 404);
            }
        }elseif (($request->companyDelete ?? '') == 'companyDelete') {
            // Find the user by ID
            $company = Companies::find($id);
    
            // Check if the user exists
            if ($company) {
                User::where('cid','=',$id)->delete();
                // Delete the company
                $company->delete();
                return response()->json(['success' => 'Company deleted successfully.']);
            } else {
                return response()->json(['error' => 'Company not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'companyDeactivate') {
            // Find the user by ID
            $company = Companies::find($id);
            
            $company->status = 0;
            $company->update();
            
            // Check if the user exists
            if ($company) {
                return response()->json(['success' => 'Company Deactivated successfully.']);
            } else {
                return response()->json(['error' => 'Company not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'companyActivate') {
            // Find the user by ID
            $company = Companies::find($id);
            $company->status = 1;
            $company->update();
            
            // Check if the user exists
            if ($company) {
                return response()->json(['success' => 'Company Activated successfully.']);
            } else {
                return response()->json(['error' => 'Company not found.'], 404);
            }
        }elseif (($request->licenseDelete ?? '') == 'licenseDelete') {
            // Find the user by ID
            $eselicenses = Eselicenses::find($id);
    
            // Check if the user exists
            if ($eselicenses) {
                // Delete the company
                $eselicenses->delete();
                return response()->json(['success' => 'License deleted successfully.']);
            } else {
                return response()->json(['error' => 'License not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'licenseDeactivate') {
            // Find the user by ID
            $eselicenses = Eselicenses::find($id);
            
            $eselicenses->status = 'blocked';
            $eselicenses->update();
            
            // Check if the user exists
            if ($eselicenses) {
                return response()->json(['success' => 'License Deactivated successfully.']);
            } else {
                return response()->json(['error' => 'License not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'licenseActivate') {
            // Find the user by ID
            $eselicenses = Eselicenses::find($id);
            $eselicenses->status = 'active';
            $eselicenses->update();
            
            // Check if the user exists
            if ($eselicenses) {
                return response()->json(['success' => 'License Activated successfully.']);
            } else {
                return response()->json(['error' => 'License not found.'], 404);
            }
        }elseif (($request->proposalDelete ?? '') == 'proposalDelete') {
            // Find the proposal by ID
            $proposal = Proposals::find($id);
    
            // Check if the proposal exists
            if ($proposal) {
                // Delete all related items first
                // If the relationship is defined, you can do:
                Proposal_items::where('proposal_id',$id)->delete();
    
                // Now, delete the proposal
                $proposal->delete();
                
                return response()->json(['success' => 'Proposal and its items were deleted successfully.']);
            } else {
                return response()->json(['error' => 'Proposal not found.'], 404);
            }
        }elseif (($request->projectDelete ?? '') == 'projectDelete') {
            // Find the project by ID
            $project = Projects::find($id);
    
            // Check if the project exists
            if ($project) {
                // Delete related records
                \App\Models\CrmTask::where('project_id', $id)->delete();
                \App\Models\Task::where('project_id', $id)->delete();
                \App\Models\Invoices::where('project_id', $id)->delete();
                \App\Models\Recoveries::where('project_id', $id)->delete();
                
                // Delete the project
                $project->delete();
                
                return response()->json(['success' => 'Project deleted successfully.']);
            } else {
                return response()->json(['error' => 'Project not found.'], 404);
            }
        }elseif (($request->invoiceDelete ?? '') == 'invoiceDelete') {
            // Find the invoice by ID
            $invoice = Invoices::find($id);
    
            // Check if the invoice exists
            if ($invoice) {
                // Delete related items — bypass TenantScope for items with NULL cid
                \App\Models\Invoice_items::withoutGlobalScope(\App\Scopes\TenantScope::class)
                    ->where('invoice_id', $id)->delete();
                // Delete the invoice
                $invoice->delete();
                
                return response()->json(['success' => 'Invoice deleted successfully.']);
            } else {
                return response()->json(['error' => 'Invoice not found.'], 404);
            }
        }elseif (($request->recoveryProjectDelete ?? '') == 'recoveryProjectDelete') {
            // Delete ALL recovery records for a given project
            $deleted = Recoveries::where('project_id', $id)->delete();
            if ($deleted) {
                return response()->json(['success' => 'All recovery records for this project deleted successfully.']);
            } else {
                return response()->json(['error' => 'No recovery records found for this project.'], 404);
            }
        }elseif (($request->recoveryAmountDelete ?? '') == 'recoveryAmountDelete') {
            // Delete a single recovery payment record
            $recovery = Recoveries::find($id);
    
            if ($recovery) {
                if($recovery->delete()){
                    return response()->json(['success' => 'Recovery record deleted successfully.']);
                }else{
                    return response()->json(['error' => 'Something went wrong.'], 500);
                }
            } else {
                return response()->json(['error' => 'Recovery not found.'], 404);
            }
        } elseif (($request->attendanceDelete ?? '') == 'attendanceDelete') {
            $attendance = Attendances::find($id);
            if ($attendance) {
                $attendance->delete();
                return response()->json(['success' => 'Attendance record deleted successfully.']);
            } else {
                return response()->json(['error' => 'Attendance record not found.'], 404);
            }
        } else {
            // Handle other operations here, if needed
            // For example, you can add more actions based on request parameters.
            // Update user, change status, etc.
    
            // Placeholder response for no delete action
            return response()->json(['success' => 'No delete action performed, but request processed successfully.']);
        }
    }
    
    public function taskSearch(Request $request)
    {
        $task = Task::leftjoin('users','tasks.uid','=','users.id')
        ->select('users.name','tasks.*')
        ->where('tasks.title','LIKE',($request->updatedPositions ?? '').'%')->get();
        
        $output = '';
        foreach($task as $taskData){
        $output .= '<li><a href="/edit-task?id='.$taskData->id.'">'.$taskData->title.'<span>'.$taskData->name.'</span></a></li>';
        }
        
        return response()->json(['result' => $output]);
    }

    /**
     * Global Search — AJAX endpoint for Ctrl+K universal CRM search.
     * Searches Leads, Clients, Proposals, and Projects within the authenticated tenant.
     */
    public function globalSearch(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['leads' => [], 'clients' => [], 'proposals' => [], 'projects' => []]);
        }

        $cid  = Auth::user()->cid;
        $like = '%' . $q . '%';

        // Search Leads
        $leads = Leads::where(function($query) use ($like) {
            $query->where('name', 'like', $like)
                  ->orWhere('company', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('mob', 'like', $like);
        })
            ->limit(5)
            ->get(['id', 'name', 'company', 'email', 'mob'])
            ->map(fn($l) => [
                'title' => $l->name ?: '(No Name)',
                'sub'   => implode(' · ', array_filter([$l->company, $l->email ?: $l->mob])),
                'url'   => '/newleads?id=' . $l->id,
            ]);

        // Search Clients
        $clients = Clients::where(function($query) use ($like) {
            $query->where('name', 'like', $like)
                  ->orWhere('company', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('mob', 'like', $like);
        })
            ->limit(5)
            ->get(['id', 'name', 'company', 'email', 'mob'])
            ->map(fn($c) => [
                'title' => $c->name ?: '(No Name)',
                'sub'   => implode(' · ', array_filter([$c->company, $c->email ?: $c->mob])),
                'url'   => '/manage-client?id=' . $c->id,
            ]);

        // Search Proposals
        $proposals = Proposals::where(function($query) use ($like) {
            $query->where('subject', 'like', $like)
                  ->orWhere('client_name', 'like', $like)
                  ->orWhere('client_email', 'like', $like);
        })
            ->limit(5)
            ->get(['id', 'subject', 'client_name', 'status', 'grand_total'])
            ->map(fn($p) => [
                'title' => $p->subject ?: '(No Subject)',
                'sub'   => implode(' · ', array_filter([$p->client_name, $p->status])),
                'url'   => '/manage-proposal?id=' . $p->id,
            ]);

        // Search Projects
        $projects = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->where(function($query) use ($like) {
                $query->where('projects.name', 'like', $like)
                      ->orWhere('clients.name', 'like', $like)
                      ->orWhere('clients.company', 'like', $like);
            })
            ->limit(5)
            ->get(['projects.id', 'projects.name', 'projects.type',
                   'clients.name as client_name', 'clients.company as client_company'])
            ->map(fn($p) => [
                'title' => $p->name ?: '(No Name)',
                'sub'   => implode(' · ', array_filter([$p->client_name, $p->client_company, $p->type])),
                'url'   => '/project/view/' . $p->id,
            ]);

        return response()->json([
            'leads'     => $leads->values(),
            'clients'   => $clients->values(),
            'proposals' => $proposals->values(),
            'projects'  => $projects->values(),
        ]);
    }

    /**
     * Public Enquiry Submission — Form capturing from Landing Page.
     * Allowed to be called as Guest (must be registered in web.php without auth).
     */
    public function storeEnquiry(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'mob'     => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $enquiry = Enquiry::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Your enquiry has been submitted successfully!']);
        }

        return redirect()->back()->with('success', 'Your enquiry has been submitted successfully!');
    }
}
