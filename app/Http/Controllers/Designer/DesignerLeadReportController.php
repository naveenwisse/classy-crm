<?php

namespace App\Http\Controllers\Designer;

use App\Helper\Reply;
use App\LeadSource;
use App\LeadStatus;
use App\Project;
use App\Lead;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DesignerLeadReportController extends DesignerBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.leadReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();

        return view('designer.reports.leads.index', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) {
        $this->userDetail = auth()->user();
        $startDate = null; $endDate = null;
        $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }

        if($request->has('status_id')){
            $status_id = $request->get('status_id');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->select('leads.id','leads.first_name', 'leads.last_name', 'leads.city', 'leads.zip','lead_status.type as status', 'leads.created_at', 'lead_sources.name as source');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($status_id)){
            $leads->where('leads.status_id', $status_id);
        }

        if(!is_null($source_id)){
            $leads->where('leads.source_id', $source_id);
        }

        if(!is_null($city)){
            $leads->where('leads.city', $city);
        }

        $leads->where('leads.user_id', '=', $this->userDetail->id);
        $leads->orderBy('leads.created_at', 'ASC');
        $leads->get();

        return DataTables::of($leads)
            ->addColumn('date', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->addColumn('full_name', function ($row) {
                return $row->full_name;
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'Pending Lead') {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                } else if ($row->status == 'In Design Process') {
                    $status = '<label class="label label-warning">' . $row->status . '</label>';
                } else if ($row->status == 'Converted Sale') {
                    $status = '<label class="label label-success">' . $row->status . '</label>';
                } else if ($row->status == 'Dead') {
                    $status = '<label class="label label-danger">' . $row->status . '</label>';
                } else {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                }
                return $status;
            })
            ->rawColumns(['full_name', 'date', 'status'])
            ->removeColumn('first_name', 'last_name', 'created_at')
            ->make(true);

    }

    public function export(Request $request) {

        $this->userDetail = auth()->user();
        $startDate = null; $endDate = null;
        $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }

        if($request->has('status_id')){
            $status_id = $request->get('status_id');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->select('leads.id', 'leads.created_at', 'leads.first_name', 'leads.last_name', 'lead_sources.name as source', 'leads.city', 'leads.zip', 'lead_status.type as status');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($status_id)){
            $leads->where('leads.status_id', $status_id);
        }

        if(!is_null($source_id)){
            $leads->where('leads.source_id', $source_id);
        }

        if(!is_null($city)){
            $leads->where('leads.city', $city);
        }

        $leads->where('leads.user_id', '=', $this->userDetail->id);
        $leads->orderBy('leads.created_at', 'ASC');
        $leads->get();

        $title = 'Lead Report';
        $attributes = ['first_name', 'last_name'];

        $leads = $leads->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Date', 'Full Name', 'Source', 'City', 'Zip', 'Status'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($leads as $row) {
            $data = $row->toArray();
            $insert = ['full_name' => $row->full_name];
            array_splice($data, 2, 0, $insert);
            $data['created_at'] = $row->created_at->format($this->global->date_format);
            $exportArray[] = $data;
        }

        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription('Lead Report file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));

                });

            });

        })->download('xlsx');
    }

}
