<?php

namespace App\Http\Controllers\Designer;

use App\Helper\Reply;
use App\Http\Requests\CommonRequest;
use App\Http\Requests\Lead\StoreRequest;
use App\Http\Requests\Lead\UpdateRequest;
use App\Lead;
use App\LeadFollowUp;
use App\LeadSource;
use App\LeadStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Yajra\DataTables\Facades\DataTables;

class DesignerLeadController extends DesignerBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'user-follow';
        $this->pageTitle = 'leads';
        $this->middleware(function ($request, $next) {
            if(!in_array('leads',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $this->totalLeads = Lead::all();
        $this->totalClientConverted = $this->totalLeads->filter(function ($value, $key) {
            return $value->client_id != null;
        });
        $this->totalLeads = Lead::all()->count();
        $this->totalClientConverted = $this->totalClientConverted->count();

        return view('designer.lead.index', $this->data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id) {
        $this->lead = Lead::findOrFail($id);
        $this->states = Config::get('constants.states');
        return view('designer.lead.show', $this->data);
    }

    /**
     * @param CommonRequest $request
     * @param null $id
     * @return mixed
     */
    public function data(CommonRequest $request, $id = null) {
        $currentDate = Carbon::today()->format('Y-m-d');
        $this->userDetail = auth()->user();
        $lead = Lead::select('leads.id','leads.client_id','first_name', 'last_name', 'cell', 'zip','lead_status.type as statusName','status_id', 'leads.created_at', 'lead_sources.name as source')
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id');

//        if ($request->client != 'all' && $request->client != '') {
//            if ($request->client == 'lead') {
//                $lead = $lead->whereNull('client_id');
//            } else {
//                $lead = $lead->whereNotNull('client_id');
//            }
//        }


        $lead = $lead->where('leads.user_id', '=', $this->userDetail->id);
        $lead = $lead->GroupBy('leads.id')->get();

        return DataTables::of($lead)
            ->addColumn('action', function($row){
                $action = '<a href="' . route('designer.leads.show', [$row->id]) . '" class="btn btn-success btn-circle"
                      data-toggle="tooltip" data-original-title="View Lead Details"><i class="fa fa-search" aria-hidden="true"></i></a>';
                return $action;
            })
            ->addColumn('status', function ($row) {
                $status = LeadStatus::all();
                $statusLi = '';
                foreach ($status as $st) {
                    if ($row->status_id == $st->id) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }
                    $statusLi .= '<option ' . $selected . ' value="' . $st->id . '">' . $st->type . '</option>';
                }

                $action = '<select class="form-control" name="statusChange" onchange="changeStatus( ' . $row->id . ', this.value)">
                    ' . $statusLi . '
                </select>';


                return $action;
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->removeColumn('status_id')
            ->removeColumn('client_id')
            ->removeColumn('source')
            ->removeColumn('statusName')
            ->rawColumns(['status','action','client_name'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(403);
        $this->sources = LeadSource::all();
        $this->status = LeadStatus::all();
        $this->states = Config::get('constants.states');
        return view('designer.lead.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        abort(403);
        $lead = new Lead();
        $lead->company_name = $request->company_name;
        $lead->website = $request->website;
        $lead->address = $request->address;
        $lead->client_name = $request->client_name;
        $lead->client_email = $request->client_email;
        $lead->mobile = $request->mobile;
        $lead->note = $request->note;
        $lead->next_follow_up = $request->next_follow_up;
        $lead->save();

        return Reply::redirect(route('designer.leads.index'), __('messages.LeadAddedUpdated'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort(403);
        $this->lead = Lead::findOrFail($id);
        $this->sources = LeadSource::all();
        $this->status = LeadStatus::all();
        $this->states = Config::get('constants.states');
        return view('designer.lead.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        abort(403);
        $lead = Lead::findOrFail($id);
        $lead->company_name = $request->company_name;
        $lead->website = $request->website;
        $lead->address = $request->address;
        $lead->client_name = $request->client_name;
        $lead->client_email = $request->client_email;
        $lead->mobile = $request->mobile;
        $lead->note = $request->note;
        $lead->status_id = $request->status;
        $lead->source_id = $request->source;
        $lead->next_follow_up = $request->next_follow_up;
        $lead->save();

        return Reply::redirect(route('designer.leads.index'), __('messages.LeadUpdated'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(403);
        Lead::destroy($id);
        return Reply::success(__('messages.LeadDeleted'));
    }

    /**
     * @param CommonRequest $request
     * @return array
     */
    public function changeStatus(CommonRequest $request)
    {
        $lead = Lead::findOrFail($request->leadID);
        $lead->status_id = $request->statusID;
        $lead->save();

        return Reply::success(__('messages.leadStatusChangeSuccess'));
    }

    /**
     * @param $leadID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followUpCreate($leadID){
        $this->leadID = $leadID;
        return view('designer.lead.follow_up', $this->data);
    }

    /**
     * @param CommonRequest $request
     * @return array
     */
    public function followUpStore(\App\Http\Requests\FollowUp\StoreRequest $request){

        $followUp = new LeadFollowUp();
        $followUp->lead_id = $request->lead_id;
        $followUp->next_follow_up_date = Carbon::createFromFormat($this->global->date_format, $request->next_follow_up_date)->format('Y-m-d');;
        $followUp->remark = $request->remark;
        $followUp->save();
        $this->lead = Lead::findOrFail($request->lead_id);

        $view = view('designer.lead.followup.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.leadFollowUpAddedSuccess'), ['html' => $view]);
    }

    /**
     * @param $leadID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followUpShow($leadID){

        $this->leadID = $leadID;
        $this->lead = Lead::findOrFail($leadID);
        return view('designer.lead.followup.show', $this->data);
    }

    public function editFollow($id)
    {
        if(!$this->user->can('edit_lead')){
            abort(403);
        }

        $this->follow = LeadFollowUp::findOrFail($id);
        $view = view('designer.lead.followup.edit', $this->data)->render();
        return Reply::dataOnly(['html' => $view]);
    }

    /**
     * @param \App\Http\Requests\FollowUp\StoreRequest $request
     * @return array
     * @throws \Throwable
     */
    public function UpdateFollow(\App\Http\Requests\FollowUp\StoreRequest $request)
    {

        $followUp = LeadFollowUp::findOrFail($request->id);
        $followUp->lead_id = $request->lead_id;
        $followUp->next_follow_up_date = Carbon::createFromFormat($this->global->date_format, $request->next_follow_up_date)->format('Y-m-d');;
        $followUp->remark = $request->remark;
        $followUp->save();

        $this->lead = Lead::findOrFail($request->lead_id);

        $view = view('designer.lead.followup.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.leadFollowUpUpdatedSuccess'), ['html' => $view]);
    }

    /**
     * @param CommonRequest $request
     * @return array
     * @throws \Throwable
     */
    public function followUpSort(CommonRequest $request)
    {

        $leadId = $request->leadId;
        $this->sortBy = $request->sortBy;

        $this->lead = Lead::findOrFail($leadId);
        if($request->sortBy == 'next_follow_up_date'){
            $order = "asc";
        }
        else{
            $order = "desc";
        }

        $follow = LeadFollowUp::where('lead_id', $leadId)->orderBy($request->sortBy, $order);


        $this->lead->follow = $follow->get();

        $view = view('designer.lead.followup.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.followUpFilter'), ['html' => $view]);
    }
}
