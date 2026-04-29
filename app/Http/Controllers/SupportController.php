<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\Companies;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company_session = session('companies');

        $companies = collect();
        if ($user->isMaster()) {
            $companies = Companies::select('id', 'name')->orderBy('name', 'asc')->get();
            $tickets = SupportTicket::with('company')->orderBy('created_at', 'desc')->get();
        } else {
            $tickets = SupportTicket::orderBy('created_at', 'desc')->get();
        }

        $stats = [
            'total' => $tickets->count(),
            'open' => $tickets->where('status', 0)->count(),
            'processed' => $tickets->where('status', 1)->count(),
            'closed' => $tickets->where('status', 2)->count(),
        ];

        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
        if($user->isMaster()) {
            $roleArray[] = 'All';
        }

        return view('support', compact('tickets', 'stats', 'companies', 'roleArray'));
    }

    public function manageSupport(Request $request)
    {
        $ticket = null;
        if ($request->id) {
            $ticket = SupportTicket::with('company')->find($request->id);
        }

        return view('manageSupportForm', compact('ticket'));
    }

    public function storeSupport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $user = Auth::user();
        $company_session = session('companies');

        if ($request->id) {
            $ticket = SupportTicket::find($request->id);
            if (!$ticket) {
                return response()->json(['success' => false, 'message' => 'Ticket not found.']);
            }
            
            // Allow status update for master
            if ($user->isMaster() && isset($request->status)) {
                $ticket->status = $request->status;
            }

            $ticket->subject = $request->subject;
            $ticket->description = $request->description;
            $ticket->priority = $request->priority;
            $ticket->save();

            return response()->json(['success' => true, 'message' => 'Ticket updated successfully!']);
        } else {
            $ticket_no = 'TKT-' . strtoupper(substr(uniqid(), -6));
            
            SupportTicket::create([
                'ticket_no' => $ticket_no,
                'cid' => $company_session->id,
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 0, // Open
            ]);

            return response()->json(['success' => true, 'message' => 'Support ticket raised successfully!']);
        }
    }

    public function deleteSupport(Request $request)
    {
        $ticket = SupportTicket::find($request->id);
        if ($ticket) {
            $ticket->delete();
            return redirect()->back()->with('success', 'Ticket deleted successfully.');
        }
        return redirect()->back()->with('error', 'Ticket not found.');
    }
}
