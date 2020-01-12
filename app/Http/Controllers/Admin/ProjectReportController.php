<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Project;
use App\Lead;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ProjectReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.projectReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();

        return view('admin.reports.projects.index', $this->data);
    }

    public function store(Request $request){
        $taskBoardColumn = TaskboardColumn::all();
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();

        $incompletedTaskColumn = $taskBoardColumn->filter(function ($value, $key) {
            return $value->slug == 'incomplete';
        })->first();

        $completedTaskColumn = $taskBoardColumn->filter(function ($value, $key) {
            return $value->slug == 'completed';
        })->first();

        $totalTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $endDate);

        if (!is_null($request->projectId)) {
            $totalTasks->where('project_id', $request->projectId);
        }

        if (!is_null($request->employeeId)) {
            $totalTasks->where('user_id', $request->employeeId);
        }

        $totalTasks = $totalTasks->count();

        $completedTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $endDate);

        if (!is_null($request->projectId)) {
            $completedTasks->where('project_id', $request->projectId);
        }

        if (!is_null($request->employeeId)) {
            $completedTasks->where('user_id', $request->employeeId);
        }
        $completedTasks = $completedTasks->where('tasks.board_column_id', $completedTaskColumn->id)->count();

        $pendingTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $endDate);

        if (!is_null($request->projectId)) {
            $pendingTasks->where('project_id', $request->projectId);
        }

        if (!is_null($request->employeeId)) {
            $pendingTasks->where('user_id', $request->employeeId);
        }

        $pendingTasks = $pendingTasks->where('tasks.board_column_id', '<>', $completedTaskColumn->id)->count();

        return Reply::successWithData(__('messages.reportGenerated'),
            ['pendingTasks' => $pendingTasks, 'completedTasks' => $completedTasks, 'totalTasks' => $totalTasks]
        );
    }

    public function data($startDate = null, $endDate = null) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $projects = Project::leftJoin('users', 'users.id', 'projects.user_id')
                ->leftJoin('clients', 'clients.id', 'projects.client_id')
                ->select('projects.id', 'projects.project_name','clients.first_name as client_first_name', 'clients.last_name as client_last_name', 'users.name as designer', 'projects.sales_price','projects.created_at', 'projects.status');

        if (!is_null($startDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '<=', $endDate);
        }

        $projects->orderBy('projects.created_at', 'ASC');
        $projects->get();

        return DataTables::of($projects)
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'canceled') {
                    $status = '<label class="label label-danger">' . $row->status . '</label>';
                } else if ($row->status == 'completed') {
                    $status = '<label class="label label-success">' . $row->status . '</label>';
                } else if ($row->status == 'in progress') {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                } else if ($row->status == 'not started') {
                    $status = '<label class="label label-inverse">' . $row->status . '</label>';
                } else if ($row->status == 'on hold') {
                    $status = '<label class="label label-warning">' . $row->status . '</label>';
                } else {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                }
                return $status;
            })
            ->addColumn('client', function($row){
                return ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
            })
            ->rawColumns(['created_at', 'client', 'status', 'project_name'])
            ->removeColumn('client_first_name', 'client_last_name')
            ->make(true);

    }

    public function export($startDate = null, $endDate = null) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $projects = Project::leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('projects.id', 'projects.project_name','clients.first_name as client_first_name', 'clients.last_name as client_last_name', 'users.name as designer', 'projects.sales_price', 'projects.created_at', 'projects.status');

        if (!is_null($startDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '<=', $endDate);
        }

        $projects->orderBy('projects.created_at', 'ASC');
        $projects->get();

        $title = 'Project Report';
        $attributes = ['client_first_name', 'client_last_name'];

        $events = $projects->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Project Name', 'Client', 'Designer', 'Sales Price','Created On', 'Status'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($events as $row) {
            $data = $row->toArray();
            $insert = ['client' => ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name)];
            array_splice($data, 2, 0, $insert);
            $data['created_at'] = $row->created_at->format($this->global->date_format);
            $exportArray[] = $data;
        }

        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription('Appointment Report file');

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
