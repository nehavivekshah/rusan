@extends('layout')
@section('title','Leads - Rusan')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    
    <style>
        .summary-box {
            background: #f8f9fa;
            padding: 5px 15px;
            border-radius: 8px;
            font-weight: 600;
        }

        .summary-box span {
            font-weight: 400;
        }

        .add-item-btn {
            background-color: #e3f2fd;
            border: none;
            color: #007bff;
        }

        .add-item-btn:hover {
            background-color: #d6e9fc;
        }
        
        .form-section input, select {
            border: 1px solid #dee2e6 !important;
            border-left: 1px solid #dee2e6 !important;
        }
        
        .section-title {
            text-align: center;
            font-weight: 700;
            font-size: 19px;
            text-transform: uppercase;
        }
        .searchGroup{
            max-height: 36px;
        }
    </style>
    
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Leads
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                
                <div class="btn-group">
                    @if(in_array('leads_export',$roleArray) || in_array('All',$roleArray))
                    <a href="/export-leads-file" class="btn btn-info btn-sm" target="_blank" download="leads.csv" title="Download Leads CSV File"><i class="bx bx-download"></i> <span>Export</span></a>
                    @endif
                    
                    @if(in_array('leads_import',$roleArray) || in_array('All',$roleArray))
                    <a href="javascript:void(0)" class="btn btn-warning btn-sm" id="importFile"><i class="bx bx-upload"></i> <span>Import</span></a>
                    <a href="{{ asset('assets/leads.csv') }}" class="btn btn-danger btn-sm" target="_blank" download="leads.csv" title="Download CSV Sample File"><i class="bx bx-download"></i> <span>Sample File</span></a>
                    @endif
                    
                    @if(in_array('leads_add',$roleArray) || in_array('All',$roleArray))
                    <a href="/manage-lead" class="btn btn-primary bg-primary text-white btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
                    @endif
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-1">

                    <!-- LEFT AREA: Row Count + Bulk + Assign User -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2 mb-sm-0">
                
                        <!-- Rows Per Page -->
                        <select class="form-select bg-white border" id="selectrowcount" style="width:110px;">
                            <option value="" @if(($_GET['rowcount'] ?? '') == 'all') selected @endif>All</option>
                            <option value="10" @if(($_GET['rowcount'] ?? '') == '10') selected @endif>10</option>
                            <option value="25" @if(($_GET['rowcount'] ?? '') == '25') selected @endif>25</option>
                            <option value="50" @if((empty($_GET['rowcount'])) || (($_GET['rowcount'] ?? '') == '50')) selected @endif>50</option>
                            <option value="100" @if(($_GET['rowcount'] ?? '') == '100') selected @endif>100</option>
                        </select>
                
                        <!-- Bulk Actions -->
                        <div class="bulk-actions d-flex align-items-center">
                            <!-- Assign User Filter -->
                            <select class="form-select bg-white border-0" id="assignUser" style="min-width:160px;border-right: 1px solid #cccccca6 !important;border-top-right-radius: 0px;border-bottom-right-radius: 0px;">
                                <option value="">Assign Leads</option>
                                @foreach($getUsers as $getUser)
                                    <option value="{{ $getUser->id }}">{{ $getUser->name }}</option>
                                @endforeach
                            </select>
                            <!--<span class="bulk-actions__label mr-2" data-toggle="modal" data-target="bulk_actions_popup">-->
                            <!--    Bulk Actions-->
                            <!--</span>-->
                
                            <a href="/leads" class="bulk-actions__btn text-dark" id="bulkRefresh" title="Refresh">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </div>
                
                    <!-- RIGHT AREA: Search + Status Filter -->
                    <div class="data_search d-flex gap-2">
                
                        <form id="searchForm" method="GET" action="/leads" class="d-flex gap-2">
                
                            <!-- Search Box -->
                            <div class="input-group border-0 rounded">
                                <input type="text" name="search" class="form-control bg-white searchGroup"
                                       value="{{ request('search') }}" placeholder="Search..." id="searchInput">
                                <button type="button" class="btn btn-primary searchGroup" id="searchLead"><i class="bx bx-search"></i></button>
                            </div>
                
                            <!-- Status Filter -->
                            <select name="status" class="form-select border rounded" id="statusFilter" style="max-width: 130px;">
                                <option value="">Status</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Fresh</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Follow Up</option>
                                <option value="9" {{ request('status') === '9' ? 'selected' : '' }}>Loss</option>
                            </select>
                
                            <!-- Hidden rowcount/user filters so they remain applied in GET -->
                            <input type="hidden" name="rowcount" id="rowcountHidden" value="{{ request('rowcount') }}">
                            <input type="hidden" name="assign_user" id="assignUserHidden" value="{{ request('assign_user') }}">
                
                        </form>
                
                    </div>
                </div>

                    <table id="leadslists" class="table table-condensed m-table leads" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th class="checkbox-column pl-2"><input type="checkbox" id="checkall" value="all"></th>
                                <th>Name</th>
                                <th class="m-none">Company</th>
                                <th class="m-none mw80">Mobile No.</th>
                                <th class="m-none mw60">Status</th>
                                <th class="m-none mw80">Since</th>
                                <th class="m-none mw80">Purpose</th>
                                <th class="m-none mw60">Value</th>
                                <th class="m-none mw70">Last Talk</th>
                                <th class="m-none mw150">Next Move</th>
                                <th class="m-none mw60">POC</th>
                                <th class="position-sticky end-0" width="60px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leads as $lead)
                            @php
                            
                                $reminderTimes = strtotime($lead->next_date) * 1000;
                                
                                $colorStatus = ($lead->status == '5') ? 'table-success' : 
                                    (($lead->status == '9') ? 'table-danger' : 
                                    (($lead->status == '1' && date('Y-m-d', strtotime($lead->next_date)) < date('Y-m-d')) ? 'table-alert bg-alert' : 
                                    (($lead->status == '1' && date('Y-m-d', strtotime($lead->next_date)) == date('Y-m-d') && date('His', strtotime($lead->next_date)) < date('His')) ? 'table-alert bg-alert' : 
                                    ($lead->status == '1' ? 'table-warning' : 'table-white'))));


                                $status = ($lead->status == '5') ? "converted" : 
                                          (($lead->status == '9') ? "Lose" :
                                          (($lead->status == '1') ? "Follow Up" : "Fresh"));
                            @endphp
                            <!-- Add rows with your data here -->
                            <tr class="lead-row-{{ $reminderTimes }} {{ $colorStatus ?? '' }} view selectrow" id="{{ $lead->id ?? '' }}">
                                <td class="checkbox-column pr-0"><input type="checkbox" name="checkleads[]" class="checklead" value="{{ $lead->id ?? '' }}"></td>
                                <td style="max-width:150px;overflow: hidden!important;">{{ $lead->name ?? '' }}<span class="small d-none">{{ $lead->company ?? '' }}</span></td>
                                <td class="m-none">{{ substr(($lead->company ?? ''),0,20).".." }}</td>
                                <td class="m-none mw80">{{ $lead->mob ?? '' }}</td>
                                <td class="m-none mw60"><span class="badge @if(($lead->status ?? '') == '1') bg-warning text-dark @elseif(($lead->status ?? '') == '5') bg-success @elseif(($lead->status ?? '') == '9') bg-danger @else bg-light border text-dark @endif">{{ $status ?? '' }}</span></td>
                                <td class="m-none mw80">{{ date_format(date_create($lead->created_at ?? ''), 'd M, Y') }}</td>
                                <td class="m-none mw80">{{ $lead->purpose ?? '' }}</td>
                                <td class="m-none mw60">Rs. {{ $lead->values ?? '' }}</td>
                                <td class="m-none mw70 tm" data-bs-toggle="tooltip" title="{{ $lead->msg ?? '' }}">
                                    @if(!empty($lead->last_talk))
                                        @if(date('Y-m-d') == date('Y-m-d', strtotime($lead->last_talk)))
                                            Today
                                        @else
                                            {{ date_format(date_create($lead->last_talk), 'd M, Y') }}
                                        @endif
                                    @else
                                        --
                                    @endif
                                </td>
                                <td class="m-none mw150">
                                    @if(!empty($lead->next_date))
                                        @if(date('Y-m-d') == date('Y-m-d', strtotime($lead->next_date)))
                                            Today {{ date_format(date_create($lead->next_date), '/ h:i A') }}
                                        @else
                                            {{ date_format(date_create($lead->next_date), 'd M, Y / h:i A') }}
                                        @endif
                                    @else
                                        --
                                    @endif
                                </td>
                                <td class="m-none mw60">{{ $lead->poc ?? '' }}</td>
                                <td class="position-sticky end-0 bg-default" width="60px">
                                    <div class="table-btn m-none">
                                        @if(!empty($lead->whatsapp))<a href="https://api.whatsapp.com/send/?phone={{ $lead->whatsapp }}&text=Hi&type=phone_number&app_absent=0" target="_blank" class="btn btn-success bg-success text-white btn-sm" title="whatsapp"><i class="bx bxl-whatsapp"></i></a>@endif
                                        @if(!empty($lead->email))<a href="mailto:{{ $lead->email }}" class="btn btn-warning bg-warning text-dark btn-sm" title="Email"><i class="bx bx-envelope"></i></a>@endif
                                        @if(!empty($lead->mob))<a href="tel:+{{ $lead->mob }}" class="btn btn-primary bg-primary text-white btn-sm" title="Call"><i class="bx bx-phone"></i></a>@endif
                                    </div>
                                    <div class="table-btn d-none">
                                        @if(!empty($lead->whatsapp))<a href="https://api.whatsapp.com/send/?phone={{ $lead->whatsapp }}&text=Hi&type=phone_number&app_absent=0" target="_blank" class="btn btn-success bg-success text-white btn-sm" title="whatsapp"><i class="bx bxl-whatsapp"></i></a>@endif
                                        @if(!empty($lead->email))<a href="mailto:{{ $lead->email }}" class="btn btn-warning bg-warning text-dark btn-sm" title="Email"><i class="bx bx-envelope"></i></a>@endif
                                        @if(!empty($lead->mob))<a href="tel:+{{ $lead->mob }}" class="btn btn-primary bg-primary text-white btn-sm" title="Call"><i class="bx bx-phone"></i></a>@endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <!-- Repeat rows as needed -->
                        </tbody>
                    </table>
                    <div class="d-flex flex-column flex-sm-row justify-content-between">
                        @php
                            // If there are no leads at all, handle that edge case
                            if($totalLeads === 0) {
                                $startItem = 0;
                                $endItem   = 0;
                            } else {
                                // Calculate start/end based on the current page and per page
                                $startItem = ($currentPage - 1) * $perPage + 1;
                                $endItem   = ($currentPage - 1) * $perPage + $leads->count();
                            }
                        @endphp
                        
                        <p>
                            Showing <strong>{{ $startItem }}</strong> to 
                            <strong>{{ $endItem }}</strong> 
                            of <strong>{{ $totalLeads }}</strong> entries
                        </p>
                    
                        <!-- Pagination Controls -->
                        <nav aria-label="Page navigation example">
                            <ul class="pagination pagination-sm justify-content-center align-items-center justify-content-sm-end mb-0">
                                {{-- Previous Page Link --}}
                                @if($currentPage > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">&laquo;</span>
                                    </li>
                                @endif
                        
                                {{-- Page Numbers --}}
                                @php
                                    $start = max(1, $currentPage - 2);
                                    $end = min($totalPages, $currentPage + 2);
                                @endphp
                        
                                {{-- Show first page if not in range --}}
                                @if($start > 1)
                                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a></li>
                                    @if($start > 2)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endif
                        
                                {{-- Display pages within range --}}
                                @for($page = $start; $page <= $end; $page++)
                                    <li class="page-item {{ $currentPage == $page ? 'active' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}">{{ $page }}</a>
                                    </li>
                                @endfor
                        
                                {{-- Show last page if not in range --}}
                                @if($end < $totalPages)
                                    @if($end < $totalPages - 1)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                    <li class="page-item"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}">{{ $totalPages }}</a></li>
                                @endif
                        
                                {{-- Next Page Link --}}
                                @if($currentPage < $totalPages)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">&raquo;</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <form id="leadsubmit" action="/import-leads-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impLeadFile" id="impLeadFile" accept=".csv, .xls" style="display:none;" />
    </form>
    
    <!-- Modal Structure -->
    <div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadModalLabel">Lead Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tab navigation with tabs and auto-adjusting width -->
                    <ul class="nav nav-tabs mb-3" id="leadModalTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="lead-details-tab" data-bs-toggle="pill" data-bs-target="#lead-details" type="button" role="tab" aria-controls="lead-details" aria-selected="true">Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="porposal-tab" data-bs-toggle="pill" data-bs-target="#porposal" type="button" role="tab" aria-controls="comments" aria-selected="false">Porposal</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="comments-tab" data-bs-toggle="pill" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false">Conversations</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="new-comment-tab" data-bs-toggle="pill" data-bs-target="#new-comment" type="button" role="tab" aria-controls="new-comment" aria-selected="false">Reminder</button>
                        </li>
                    </ul>
    
                    <!-- Tab content -->
                    <div class="tab-content" id="leadModalTabContent">
                        <!-- Lead Details Tab -->
                        <div class="tab-pane fade show active" id="lead-details" role="tabpanel" aria-labelledby="lead-details-tab">
                            <form action="manage-lead?page={{$_GET['page'] ?? 1}}" method="post" class="row gx-3">
                                @csrf
                                <div class="col-12 text-left">
                                    <h4 class="h5 font-weight-bold divider">Lead Information</h4>
                                    <span class="div-line mb-0"></span>
                                    <input type="hidden" id="id" name="id" value="{{ $_GET['id'] ?? '' }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="name">Name*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name*" required>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="email">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Id">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="mobile">Mobile Number*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                        <input type="text" class="form-control" id="mob" name="mob" placeholder="Enter Mobile Number*" value="91" required>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="whatsapp">Whatsapp</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                        <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="Enter Whatsapp Number" value="91">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="company">Company</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                        <input type="text" class="form-control" id="company" name="company" placeholder="Enter Company">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="position">Position</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" class="form-control" id="position" name="position" placeholder="Enter Position">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="industry">Industry</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-building'></i></span>
                                        <input type="text" class="form-control" id="industry" name="industry" placeholder="Enter Industry">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="industry">GST No.</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-building'></i></span>
                                        <input type="text" class="form-control" id="gstno" name="gstno" placeholder="Enter GST No.">
                                    </div>
                                </div>
                                <div class="col-12 text-left pt-3">
                                    <h4 class="h5 font-weight-bold divider">Address Details</h4>
                                    <span class="div-line mb-0"></span>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="address">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-home'></i></span>
                                        <input type="text" class="form-control" id="address" name="address[]" placeholder="Enter Address">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="city">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map'></i></span>
                                        <input type="text" class="form-control" id="city" name="address[]" placeholder="Enter City">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="state">State</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                        <input type="text" class="form-control" id="state" name="address[]" placeholder="Enter State">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="country">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                        <input type="text" class="form-control" id="country" name="address[]" placeholder="Enter Country">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="zip">Zip/Postal Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                        <input type="text" class="form-control" id="zip" name="address[]" placeholder="Enter Zip/Postal Code">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="website">Website</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-link'></i></span>
                                        <input type="url" class="form-control" id="website" name="website" placeholder="Enter Website Link">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="source">Language</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-world'></i></span>
                                        <input type="text" class="form-control" id="language" name="language" placeholder="Enter Language">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="source">Purpose</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-target-lock'></i></span>
                                        <input type="text" class="form-control" id="purpose" name="purpose" placeholder="Enter Purpose">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="values">Lead Value</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                        <input type="number" class="form-control" id="value" name="value" placeholder="Enter Values">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="source">Assigned</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-share-alt'></i></span>
                                        <input type="text" class="form-control" id="assigned" name="assigned" placeholder="Enter Assigned User Name">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="source">POC</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                        <input type="text" class="form-control" id="poc" name="poc" placeholder="Enter Point of Contact">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="source">Tags</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                        <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter Tags (Search Keywords, K2)">
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Status:</label><br>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-list-check'></i></span>
                                        <select class="form-control" id="status" name="status"></select>
                                    </div>
                                </div>
                                @if(in_array('leads_edit',$roleArray) || in_array('All',$roleArray))
                                <div class="form-group col-md-12 mt-3 d-flex justify-content-between">
                                    <button type="button" class="btn btn-danger border px-4 leadDelete" id="leadDelete" data-page="leadDelete">Delete</button>
                                    <div>
                                        <img src="{{ asset('logo.png') }}" style="height:60px;margin:auto;" />
                                        <button type="reset" class="btn btn-light ml-auto border px-4">Reset</button>
                                        <button type="submit" class="btn btn-success bg-success text-white px-4">Save</button>
                                    </div>
                                </div>
                                @endif
                            </form>
                        </div>
    
                        <!-- Proposals Tab -->
                        <div class="tab-pane fade" id="porposal" role="tabpanel" aria-labelledby="porposal-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">Proposals</h5>
                                        <a href="/manage-proposal" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus mr-1"></i> New Proposal
                                        </a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped rounded mb-0" style="width: 100%;border: 1px solid #ccc; border-radius: 5px !important; overflow: hidden;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="110px" class="m-none">#</th>
                                                    <th>Subject</th>
                                                    <th>Total</th>
                                                    <th width="110px" class="m-none">Date</th>
                                                    <th width="110px" class="m-none">Open Till</th>
                                                    <th class="m-none">Status</th>
                                                    <th width="110px" class="m-none text-right">Created Date</th>
                                                </tr>
                                            </thead>
                                            <tbody id="Proposals"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <!-- Add New Proposal Tab -->
                        <div class="tab-pane fade" id="managePorposal" role="tabpanel" aria-labelledby="managePorposal-tab">
                            <!-- Proposal Form -->
                            <form id="proposalForm" action="/manage-proposal" method="post" class="row gx-3">
                                @csrf
                                <div class="col-12 text-left">
                                    <h4 class="h5 font-weight-bold divider">Proposal Details</h4>
                                    <span class="div-line mb-0"></span>
                                </div>
                                <!-- Proposal Information -->
                                <div class="col-md-4 form-group">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-rename'></i></span>
                                        <input type="hidden" name="lead_id" id="lead_id">
                                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter Subject" required>
                                    </div>
                                    <div class="form-text">e.g. Website Redesign Proposal</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="date" class="form-label">Proposal Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                                        <input type="date" name="proposal_date" id="proposalDate" class="form-control" required>
                                    </div>
                                    <div class="form-text">The date the proposal was created</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="openTill" class="form-label">Valid Till</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-calendar-event'></i></span>
                                        <input type="date" name="open_till" id="openTill" class="form-control">
                                    </div>
                                    <div class="form-text">Date till which the proposal is valid</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-money'></i></span>
                                        <select name="currency" id="currency" class="form-control" required>
                                            <option selected value="INR">₹ INR</option>
                                            <option value="USD">$ USD</option>
                                            <option value="EUR">€ EUR</option>
                                            <option value="GBP">£ GBP</option>
                                        </select>
                                    </div>
                                    <div class="form-text">Currency for this proposal</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="discountType" class="form-label">Discount Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-purchase-tag'></i></span>
                                        <select name="discount_type" id="discountType" class="form-control">
                                            <option value="none" selected>No discount</option>
                                            <option value="before-tax">Before Tax</option>
                                            <option value="after-tax">After Tax</option>
                                        </select>
                                    </div>
                                    <div class="form-text">Type of Discount to apply</div>
                                </div>
                                <div class="col-12 form-group">
                                    <label for="notes" class="form-label">Proposal Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Add proposal notes here..."></textarea>
                                </div>
                            
                                <!-- Client Details Section -->
                                <div class="col-12 text-left pt-3">
                                    <h4 class="h5 font-weight-bold divider">Client Details</h4>
                                    <span class="div-line mb-0"></span>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientName" class="form-label">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" name="client_name" id="clientName" class="form-control" placeholder="Enter Client Name" required>
                                    </div>
                                    <div class="form-text">Client's Name</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                        <input type="email" name="client_email" id="clientEmail" class="form-control" placeholder="Enter Client Email" required>
                                    </div>
                                    <div class="form-text">Client's Email Address</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientPhone" class="form-label">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                        <input type="tel" name="client_phone" id="clientPhone" class="form-control" placeholder="Enter Client Phone">
                                    </div>
                                    <div class="form-text">Client's Contact Number</div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientAddress" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-home'></i></span>
                                        <input type="text" name="client_address" id="clientAddress" class="form-control" placeholder="Enter Client Address">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientCity" class="form-label">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map'></i></span>
                                        <input type="text" name="client_city" id="clientCity" class="form-control" placeholder="Enter Client City">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientState" class="form-label">State/Province</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                        <input type="text" name="client_state" id="clientState" class="form-control" placeholder="Enter State/Province">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientZip" class="form-label">Zip/Postal Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                        <input type="text" name="client_zip" id="clientZip" class="form-control" placeholder="Enter Zip/Postal Code">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="clientCountry" class="form-label">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map-globe'></i></span>
                                        <input type="text" name="client_country" id="clientCountry" class="form-control" placeholder="Enter Country">
                                    </div>
                                </div>
                            
                                <!-- Items Section -->
                                <div class="col-12 text-left pt-3">
                                    <h4 class="h5 font-weight-bold divider">Items</h4>
                                    <span class="div-line mb-0"></span>
                                </div>
                                <div class="col-md-12">
                                    <div class="table-responsive rounded border border-dark mb-3">
                                        <table class="table table-bordered text-center mb-0" id="items-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Description</th>
                                                    <th>Qty</th>
                                                    <th>Rate</th>
                                                    <th>Tax</th>
                                                    <th>Amount</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr data-item-row="0">
                                                    <td><input type="text" class="form-control item-name" name="proposal_items[0][item_name]" placeholder="Item Name" value="Item 1"></td>
                                                    <td><input type="text" class="form-control item-description" name="proposal_items[0][description]" placeholder="Description" value="Description of Item 1"></td>
                                                    <td><input type="number" class="form-control item-qty" name="proposal_items[0][quantity]" value="1" min="1"></td>
                                                    <td><input type="number" class="form-control item-rate" name="proposal_items[0][rate]" placeholder="Rate" value="100.00"></td>
                                                    <td>
                                                        <select class="form-control item-tax" name="proposal_items[0][tax_percentage]">
                                                            <option value="0">No Tax</option>
                                                            <option value="0.05">5%</option>
                                                            <option value="0.10">10%</option>
                                                            <option value="0.15">15%</option>
                                                        </select>
                                                    </td>
                                                    <td class="item-amount">₹100.00</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-item-btn"><i class='bx bx-trash'></i></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn add-item-btn btn-sm">+ Add Item</button>
                                </div>
                                
                                <!-- Summary Section -->
                                <div class="col-md-5"></div>
                                <div class="col-md-7">
                                    <div class="summary-box mb-2 d-flex justify-content-between align-items-center"> <!-- Use align-items-center -->
                                        <span>Sub Total:</span>
                                        <span id="sub-total">₹0.00</span>
                                        <input type="hidden" name="sub_total" id="sub-total1">
                                    </div>
                                
                                    <!-- ****** MODIFIED DISCOUNT ROW ****** -->
                                    <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                                        <span>Discount (<span id="discount-type-display">None</span>):</span>
                                        <div class="input-group input-group-sm w-50"> <!-- Wrap input and display -->
                                             <input type="number" class="form-control form-control-sm text-end" name="discount_percentage" id="discountValue" value="0" placeholder="Enter %" step="0.01" min="0">
                                             <span class="input-group-text" id="discount-total">₹0.00</span> <!-- Display calculated amount -->
                                             <input type="hidden" name="discount_amount_calculated" id="discount-total1">
                                        </div>
                                    </div>
                                    <!-- *********************************** -->
                                
                                     <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                                        <span>Tax:</span>
                                        <span id="tax-total">₹0.00</span>
                                        <input type="hidden" name="tax_total" id="tax-total1">
                                     </div>
                                    <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                                        <span>Adjustment:</span>
                                         <!-- Added align-items-center to parent, adjusted width if needed -->
                                        <input type="number" class="form-control form-control-sm text-end w-50" name="adjustment_amount" id="adjustment" value="0" step="0.01">
                                    </div>
                                    <hr/> <!-- Optional: Add a visual separator -->
                                    <div class="summary-box d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong id="total">₹0.00</strong>
                                        <input type="hidden" name="grand_total" id="total1">
                                    </div>
                                </div>
                            
                                <!-- Actions Section -->
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-success text-white bg-success text-white border me-2">Save</button>
                                    <input type="submit" class="btn btn-primary bg-primary text-white" name="submit" value="Save & Send">
                                </div>
                            </form>
                        </div>
    
                        <!-- Comments Tab -->
                        <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                            <div id="leadcomments" class="mt-3"></div>
                        </div>
    
                        <!-- New Comment Tab -->
                        <div class="tab-pane fade" id="new-comment" role="tabpanel" aria-labelledby="new-comment-tab">
                            <div class="cmtArea mt-3">
                                <form action="manage-lead-comment?page={{$_GET['page'] ?? 1}}" method="post" class="cmt-form">
                                    @csrf
                                    <input type="hidden" name="lead_id" id="commentLeadId">
                                    <div class="form-group">
                                        <label for="message" class="form-label">Message*:</label>
                                        <textarea class="form-control" rows="5" id="message" name="message" placeholder="Write Here..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="nxtDate" class="form-label">Next Date*:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                                            <input type="datetime-local" class="form-control" id="nxtDate" name="nxtDate" required>
                                        </div>
                                    </div>
                                    <div class="form-group text-center pt-2">
                                        <button type="submit" class="btn btn-success bg-success text-white">Submit</button>
                                        <button type="reset" class="btn btn-light border">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Comment Modal -->
    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
      <div class="modal-dialog shadow">
        <div class="modal-content">
          
          <div class="modal-header">
            <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body">
            <form id="editCommentForm">
              <input type="hidden" id="editCommentId" name="commentId">
              
              <div class="mb-3">
                <label for="editCommentMsg" class="form-label">Comment</label>
                <textarea class="form-control" id="editCommentMsg" name="msg" rows="4" placeholder="Enter comment"></textarea>
              </div>
              
              <div class="mb-3">
                <label for="editCommentNextDate" class="form-label">Next Date</label>
                <input type="datetime-local" class="form-control" id="editCommentNextDate" name="next_date">
              </div>
            </form>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveCommentBtn">Save Changes</button>
          </div>
          
        </div>
      </div>
    </div>

    
    <!-- 3.  THE MODAL -->
<div class="modal fade" id="bulk_actions_popup" tabindex="-1"
     role="dialog" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 rounded-lg">

      <!-- Header ----------------------------------------------------->
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="bulkActionsModalLabel">
          Bulk Actions
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body ------------------------------------------------------->
      <div class="modal-body pt-2">

        <!-- Mass Delete -->
        <div class="custom-control custom-checkbox mb-3">
          <input type="checkbox" class="custom-control-input" id="massDelete">
          <label class="custom-control-label" for="massDelete">Mass Delete</label>
        </div>

        <hr class="my-3">

        <!-- Mark as lost -->
        <div class="custom-control custom-checkbox mb-4">
          <input type="checkbox" class="custom-control-input" id="markLost">
          <label class="custom-control-label" for="markLost">Mark as lost</label>
        </div>

        <!-- Change Status -->
        <div class="form-group">
          <label for="changeStatus">Change Status</label>
          <select class="custom-select" id="changeStatus">
            <option selected>Nothing selected</option>
            <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="qualified">Qualified</option>
          </select>
        </div>

        <!-- Lead Source -->
        <div class="form-group">
          <label for="leadSource">Lead Source</label>
          <select class="custom-select" id="leadSource">
            <option selected>Nothing selected</option>
            <option value="web">Website</option>
            <option value="referral">Referral</option>
            <option value="event">Event</option>
          </select>
        </div>

        <!-- Last Contact -->
        <div class="form-group">
          <label for="lastContact">Last Contact</label>
          <div class="input-group">
            <input type="text" class="form-control" id="lastContact" placeholder="">
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="bi-calendar"></i>
              </span>
            </div>
          </div>
        </div>

        <!-- Assigned -->
        <div class="form-group">
          <label for="assigned">Assigned</label>
          <select class="custom-select" id="assigned">
            <option selected>Nothing selected</option>
            <option value="user1">User 1</option>
            <option value="user2">User 2</option>
          </select>
        </div>

        <!-- Tags -->
        <div class="form-group">
          <label><i class="bi-tag-fill"></i> Tags:</label>
          <input type="text" class="form-control" placeholder="Tag">
        </div>

        <hr class="my-4">

        <!-- Visibility -->
        <div class="form-group mb-0">
          <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="publicRadio" name="visibility"
                   class="custom-control-input" checked>
            <label class="custom-control-label" for="publicRadio">Public</label>
          </div>
          <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="privateRadio" name="visibility"
                   class="custom-control-input">
            <label class="custom-control-label" for="privateRadio">Private</label>
          </div>
        </div>

      </div><!-- /.modal-body -->

      <!-- Footer ----------------------------------------------------->
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary"
                data-dismiss="modal">Close</button>
        <button type="button" class="btn text-white"
                style="background:#c62828">
          Confirm
        </button>
      </div>

    </div><!-- /.modal-content -->
  </div>
</div>
    <script>
        /*function submitAssignLeads() {
            // Collect all checkboxes with the class 'checklead' that are checked
            const checkboxes = document.querySelectorAll('.checklead:checked');
        
            // Extract the values of the checked checkboxes
            const selectedLeads = Array.from(checkboxes).map(checkbox => checkbox.value);
        
            if (selectedLeads.length === 0) {
              alert('Please select at least one lead.');
              return;
            }
        
            // Log the selected values (for debugging purposes)
            console.log('Selected Leads:', selectedLeads);
        
            // Prepare the data to be sent
            const data = { leads: selectedLeads };
        
            // Send the data to the server via an AJAX request
            fetch('/submit-assignLeads', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Add if using Laravel
              },
              body: JSON.stringify(data)
            })
              .then(response => response.json())
              .then(result => {
                console.log('Response:', result);
                alert('Leads submitted successfully!');
              })
              .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting leads.');
              });
        }
        
        // Function to update the summary
        function updateSummary() {
            let subTotal = 0;
            let taxTotal = 0;
            const currencySymbol = document.getElementById('currency').value.charAt(0);
        
            itemsTable.querySelectorAll('tbody tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
        
                const amount = qty * rate;
                const taxAmount = amount * taxRate;
                row.querySelector('.item-amount').textContent = `${currencySymbol}${amount.toFixed(2)}`;
        
                subTotal += amount;
                taxTotal += taxAmount;
        
            });
            const adjustment = parseFloat(adjustmentInput.value) || 0;
            const total = subTotal + taxTotal + adjustment;
        
        
            document.getElementById('sub-total').textContent = `${currencySymbol}${subTotal.toFixed(2)}`;
            document.getElementById('tax-total').textContent = `${currencySymbol}${taxTotal.toFixed(2)}`;
            document.getElementById('total').textContent = `${currencySymbol}${total.toFixed(2)}`;
        }
        
        // Event listener to add a new item
        addItemBtn.addEventListener('click', addItemRow);
        
        // Function to add a new item row
        function addItemRow() {
            const rowCount = itemsTable.rows.length;
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-item-row', rowCount);
            const currencySymbol = document.getElementById('currency').value.charAt(0);
            newRow.innerHTML = `
              <td><input type="text" class="form-control item-name" placeholder="Item Name"></td>
              <td><input type="text" class="form-control item-description" placeholder="Description"></td>
              <td><input type="number" class="form-control item-qty" value="1" min="1"></td>
              <td><input type="number" class="form-control item-rate" placeholder="Rate"></td>
              <td>
                <select class="form-control item-tax">
                    <option value="0">No Tax</option>
                    <option value="0.05">5%</option>
                    <option value="0.10">10%</option>
                     <option value="0.15">15%</option>
                </select>
               </td>
              <td class="item-amount">${currencySymbol}0.00</td>
              <td>
                  <button type="button" class="btn btn-sm btn-danger remove-item-btn"><i class='bx bx-trash'></i></button>
              </td>
          `;
            itemsTable.querySelector('tbody').appendChild(newRow);
            updateSummary();
        }
        
        // Event listener for currency changes
        document.getElementById('currency').addEventListener('change', updateSummary);
        
        // Initial summary update
        updateSummary();*/
    </script>
@endsection
