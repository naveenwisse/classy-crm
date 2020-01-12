<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Project;
use App\Lead;
use App\User;
use App\EventStatus;
use App\Event;
use App\EventAttendee;
use App\EventType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class EventReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.appointmentReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();
        $this->event_status = EventStatus::all();
        return view('admin.reports.events.index', $this->data);
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

    public function data($startDate = null, $endDate = null, $type = null) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $events = Event::leftJoin('event_types', 'event_types.id', 'events.event_type')
                ->leftJoin('event_status', 'event_status.id', 'events.status_id')
                ->leftJoin('leads', 'leads.id', 'events.lead_id')
                ->leftJoin('projects', 'projects.id', 'events.project_id')
                ->leftJoin('event_attendees', 'event_attendees.event_id', 'events.id')
                ->select('events.id', 'events.event_name','event_types.type as event_type', 'event_status.name as event_status', 'events.status_id', 'events.start_date_time', 'events.end_date_time', 'events.created_at', 'event_attendees.user_id as user_id');


        if (!is_null($startDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '<=', $endDate);
        }

        if (!is_null($type) && $type !== 'all') {
            $events->where('events.status_id', '=', $type);
        }

        $events->orderBy('events.created_at', 'ASC');
        $events->get();

        return DataTables::of($events)
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('event_status', function ($row) {
                if ($row->event_status == 'scheduled') {
                    $status = '<label class="label label-info">' . $row->event_status . '</label>';
                } else if ($row->event_status == 'completed') {
                    $status = '<label class="label label-success">' . $row->event_status . '</label>';
                } else {
                    $status = '<label class="label label-info">' . $row->event_status . '</label>';
                }
                return $status;
            })
            ->editColumn('start_date_time', function ($row) {
                return $row->start_date_time->format($this->global->date_format. ' H:i:s');
            })
            ->editColumn('end_date_time', function ($row) {
                return $row->end_date_time->format($this->global->date_format. ' H:i:s');
            })
            ->addColumn('designer', function($row){
                $user = User::findorFail($row->user_id);
                return $user->name ?? '';
            })
            ->rawColumns(['created_at', 'event_status', 'start_date_time', 'end_date_time'])
            ->removeColumn('status_id')
            ->make(true);

    }

    public function export($startDate = null, $endDate = null, $type=null) {
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $events = Event::leftJoin('event_types', 'event_types.id', 'events.event_type')
            ->leftJoin('event_status', 'event_status.id', 'events.status_id')
            ->leftJoin('leads', 'leads.id', 'events.lead_id')
            ->leftJoin('projects', 'projects.id', 'events.project_id')
            ->leftJoin('event_attendees', 'event_attendees.event_id', 'events.id')
            ->select('events.id', 'events.event_name','event_types.type as event_type', 'events.status_id', 'events.start_date_time', 'events.end_date_time', 'events.created_at', 'event_status.name as event_status', 'event_attendees.user_id as user_id');


        if (!is_null($startDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $events->where(DB::raw('DATE(events.`created_at`)'), '<=', $endDate);
        }

        if (!is_null($type) && $type !== 'all') {
            $events->where('events.status_id', '=', $type);
        }

        $events->orderBy('events.created_at', 'ASC');
        $events->get();

        $title = 'Appointment Report';
        $attributes = ['status_id', 'user_id'];

        $events = $events->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Appointment Name', 'Appointment Type', 'Designer', 'Starts On', 'Ends On', 'Created On', 'Status'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($events as $row) {
            $data = $row->toArray();
            $user = User::findorFail($row->user_id);
            $insert = ['designer' => $user->name];
            array_splice($data, 3, 0, $insert);
            $data['start_date_time'] = $row->start_date_time->format($this->global->date_format. ' H:i:s');
            $data['end_date_time'] = $row->end_date_time->format($this->global->date_format. ' H:i:s');
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
