<?php

namespace App\Http\Controllers\Admin;

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

class LeadReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.leadReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();

        return view('admin.reports.leads.index', $this->data);
    }

    public function conversionClient() {
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();

        return view('admin.reports.leads.conversion-client', $this->data);
    }

    public function conversionProject() {
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();

        return view('admin.reports.leads.conversion-project', $this->data);
    }

    public function designerPerformance() {
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();

        return view('admin.reports.leads.designer-performance', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) { //$startDate = null, $endDate = null, $client = null, $project = null
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }

        if($request->has('client')){
            $client = $request->get('client');
        }

        if($request->has('project')){
            $project = $request->get('project');
        }

        if($request->has('designer_id')){
            $designer_id = $request->get('designer_id');
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

        if(is_null($client) && is_null($project)){
            $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
                ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
                ->leftJoin('users', 'users.id', 'leads.user_id')
                ->select('leads.id','leads.first_name', 'leads.last_name', 'leads.city', 'leads.zip','lead_status.type as status', 'leads.created_at', 'lead_sources.name as source', 'users.name as designer');
        }

        if(!is_null($client) && $client){
            $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
                ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
                ->leftJoin('users', 'users.id', 'leads.user_id')
                ->leftJoin('clients', 'clients.id', 'leads.client_id')
                ->select('leads.id', 'clients.first_name as first_name', 'clients.last_name as last_name', 'leads.city', 'leads.zip','lead_status.type as status', 'leads.created_at', 'lead_sources.name as source', 'users.name as designer');
            $leads->whereNotNull('leads.client_id');
        }

        if(!is_null($project) && $project){
            $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
                ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
                ->leftJoin('users', 'users.id', 'leads.user_id')
                ->leftJoin('projects', 'projects.id', 'leads.project_id')
                ->select('leads.id', 'projects.project_name as project', 'projects.sales_price as sales_price', 'leads.city', 'leads.zip','lead_status.type as status','leads.status_id', 'leads.created_at', 'lead_sources.name as source', 'users.name as designer');
            $leads->whereNotNull('leads.project_id');
        }

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $leads->where('leads.user_id', $designer_id);
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

        $leads->orderBy('leads.created_at', 'ASC');
        $leads->get();

        if(is_null($client) && is_null($project)){
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
                ->removeColumn('first_name', 'last_name', 'created_at', 'status_id','user_id', 'status_id')
                ->make(true);
        }

        if(!is_null($client) && $client){
            return DataTables::of($leads)
                ->addColumn('date', function ($row) {
                    return $row->created_at->format($this->global->date_format);
                })
                ->addColumn('client', function ($row) {
                    return ucfirst($row->first_name) . ' ' . ucfirst($row->last_name);
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
                ->rawColumns(['date', 'status'])
                ->removeColumn('first_name', 'last_name', 'created_at', 'status_id')
                ->make(true);
        }

        if(!is_null($project) && $project){
            return DataTables::of($leads)
                ->addColumn('date', function ($row) {
                    return $row->created_at->format($this->global->date_format);
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
                ->removeColumn('created_at', 'status_id')
                ->make(true);
        }
    }

    public function performanceData(Request $request) { //$startDate = null, $endDate = null, $client = null, $project = null
        $startDate = null; $endDate = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }
        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
        }
        $status = LeadStatus::all();
        $pending = 0; $process = 0; $converted = 0; $dead = 0;
        foreach ($status as $st){
            if ($st->type == 'Pending Lead') {
                $pending = $st->id;
            } else if ($st->type == 'In Design Process') {
                $process = $st->id;
            } else if ($st->type == 'Converted Sale') {
                $converted = $st->id;
            } else if ($st->type == 'Dead') {
                $dead = $st->id;
            }
        }
        $designers = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->with(['leads' => function ($query) use ($startDate, $endDate) {
                if (!is_null($startDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
                }
                if (!is_null($endDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
                }
            }])
            ->select('users.id', 'users.name')
            ->where('roles.name', 'designer')
            ->orderBy('users.id', 'ASC');

        $designers->get();

        return DataTables::of($designers)
            ->addColumn('received', function ($row) {
                return count($row->leads);
            })
            ->addColumn('pending', function ($row) use ($pending) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $pending){
                        $count++;
                    }
                }
                return $count;
            })
            ->addColumn('design_process', function ($row) use ($process) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $process){
                        $count++;
                    }
                }
                return $count;
            })
            ->addColumn('converted', function ($row) use ($converted) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $converted){
                        $count++;
                    }
                }
                return $count;
            })
            ->addColumn('dead', function ($row) use ($dead) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $dead){
                        $count++;
                    }
                }
                return $count;
            })
            ->removeColumn('unreadNotifications', 'image_url')
            ->make(true);
    }

    public function performanceExport(Request $request) {
        $startDate = null; $endDate = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }
        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
        }
        $status = LeadStatus::all();
        $pending = 0; $process = 0; $converted = 0; $dead = 0;
        foreach ($status as $st){
            if ($st->type == 'Pending Lead') {
                $pending = $st->id;
            } else if ($st->type == 'In Design Process') {
                $process = $st->id;
            } else if ($st->type == 'Converted Sale') {
                $converted = $st->id;
            } else if ($st->type == 'Dead') {
                $dead = $st->id;
            }
        }
        $designers = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->with(['leads' => function ($query) use ($startDate, $endDate) {
                if (!is_null($startDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
                }
                if (!is_null($endDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
                }
            }])
            ->select('users.id', 'users.name')
            ->where('roles.name', 'designer')
            ->orderBy('users.id', 'ASC');

        $designers = $designers->get();

        $title = 'Designer Performance Report';

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Designer Name', 'Received', 'Pending', 'In Design Process', 'Converted', 'Dead'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($designers as $designer) {
            $data = [];
            $data['id'] = $designer->id;
            $data['name'] = $designer->name;
            $data['received'] = count($designer->leads);
            $data['pending'] = 0;
            $data['process'] = 0;
            $data['converted'] = 0;
            $data['dead'] = 0;
            foreach ($designer->leads as $lead){
                if($lead->status_id == $pending){
                    $data['pending']++;
                }
                if($lead->status_id == $process){
                    $data['process']++;
                }
                if($lead->status_id == $converted){
                    $data['converted']++;
                }
                if($lead->status_id == $dead){
                    $data['dead']++;
                }
            }
            $exportArray[] = $data;
        }

        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription($title);

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', true, false);
                $sheet->row(1, function($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));
                });

            });

        })->download('xlsx');
    }

    public function export(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }

        if($request->has('client')){
            $client = $request->get('client');
        }

        if($request->has('project')){
            $project = $request->get('project');
        }

        if($request->has('designer_id')){
            $designer_id = $request->get('designer_id');
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

        if(is_null($client) && is_null($project)){
            $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
                ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
                ->leftJoin('users', 'users.id', 'leads.user_id')
                ->select('leads.id', 'leads.created_at', 'leads.first_name', 'leads.last_name', 'lead_sources.name as source', 'users.name as designer', 'leads.city', 'leads.zip', 'lead_status.type as status');
        }

        if(!is_null($client) && $client){
            $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
                ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
                ->leftJoin('users', 'users.id', 'leads.user_id')
                ->leftJoin('clients', 'clients.id', 'leads.client_id')
                ->select('leads.id', 'leads.created_at', 'clients.first_name as first_name', 'clients.last_name as last_name', 'lead_sources.name as source', 'users.name as designer', 'leads.city', 'leads.zip', 'lead_status.type as status');
            $leads->whereNotNull('leads.client_id');
        }

        if(!is_null($project) && $project){
            $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
                ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
                ->leftJoin('users', 'users.id', 'leads.user_id')
                ->leftJoin('projects', 'projects.id', 'leads.project_id')
                ->select('leads.id', 'leads.created_at', 'projects.project_name as project', 'projects.sales_price as sales_price', 'lead_sources.name as source', 'users.name as designer', 'leads.city', 'leads.zip', 'lead_status.type as status');
            $leads->whereNotNull('leads.project_id');
        }

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $leads->where('leads.user_id', $designer_id);
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

        $leads->orderBy('leads.created_at', 'ASC');
        $leads->get();

        $title = '';
        if(is_null($client) && is_null($project)) {
            $title = 'Lead Report';
            $attributes = ['first_name', 'last_name'];

            $leads = $leads->get()->makeHidden($attributes);

            // Initialize the array which will be passed into the Excel
            // generator.
            $exportArray = [];

            // Define the Excel spreadsheet headers
            $exportArray[] = ['ID', 'Date', 'Full Name', 'Source', 'Designer', 'City', 'Zip', 'Status'];

            // Convert each member of the returned collection into an array,
            // and append it to the payments array.
            foreach ($leads as $row) {
                $data = $row->toArray();
                $insert = ['full_name' => $row->full_name];
                array_splice($data, 2, 0, $insert);
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                $exportArray[] = $data;
            }
        }

        if(!is_null($client) && $client){
            $title = 'Lead Conversion To Client Report';
            $attributes = ['first_name', 'last_name'];

            $leads = $leads->get()->makeHidden($attributes);

            // Initialize the array which will be passed into the Excel
            // generator.
            $exportArray = [];

            // Define the Excel spreadsheet headers
            $exportArray[] = ['ID', 'Date', 'Client', 'Source', 'Designer', 'City', 'Zip', 'Status'];

            // Convert each member of the returned collection into an array,
            // and append it to the payments array.
            foreach ($leads as $row) {
                $data = $row->toArray();
                $insert = ['client' => $row->full_name];
                array_splice($data, 2, 0, $insert);
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                $exportArray[] = $data;
            }
        }

        if(!is_null($project) && $project){
            $title = 'Lead Conversion To Project Report';
            $leads = $leads->get();
            // Initialize the array which will be passed into the Excel
            // generator.
            $exportArray = [];

            // Define the Excel spreadsheet headers
            $exportArray[] = ['ID', 'Date', 'Project Name', 'Sales Price','Source', 'Designer', 'City', 'Zip', 'Status'];

            // Convert each member of the returned collection into an array,
            // and append it to the payments array.
            foreach ($leads as $row) {
                $data = $row->toArray();
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                $exportArray[] = $data;
            }
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
