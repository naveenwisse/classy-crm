<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\Tasks\StoreTask;
use App\Notifications\NewClientTask;
use App\Notifications\NewTask;
use App\Notifications\TaskCompleted;
use App\Notifications\TaskUpdated;
use App\Notifications\TaskUpdatedClient;
use App\Project;
use App\SubTask;
use App\Task;
use App\TaskboardColumn;
use App\TaskCategory;
use App\Traits\ProjectProgress;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ManageTasksController extends AdminBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = __('app.menu.projects');
        $this->middleware(function ($request, $next) {
            if (!in_array('tasks', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTask $request)
    {
        $task = new Task();
        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();

        $task->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        $task->user_id = $request->user_id;
        $task->project_id = $request->project_id;
        $task->priority = $request->priority;
        $task->board_column_id = $taskBoardColumn->id;
        $task->task_category_id = $request->category_id;

        if ($request->milestone_id != '') {
            $task->milestone_id = $request->milestone_id;
        }

        $task->save();

        // Send notification to user
        $notifyUser = User::withoutGlobalScope('active')->findOrFail($request->user_id);
        $notifyUser->notify(new NewTask($task));

        if ($task->project_id != null) {
            if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->project->client_id);
                $notifyUser->notify(new NewClientTask($task));
            }
        }

        $this->logProjectActivity($request->project_id, __('messages.newTaskAddedToTheProject'));

        $this->project = Project::findOrFail($task->project_id);
        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

        //log search
        $this->logSearchEntry($task->id, 'Task: ' . $task->heading, 'admin.all-tasks.edit');

        return Reply::successWithData(__('messages.taskCreatedSuccessfully'), ['html' => $view]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->project = Project::findOrFail($id);
        $this->categories = TaskCategory::all();
        return view('admin.projects.tasks.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->task = Task::findOrFail($id);
        $this->taskBoardColumns = TaskboardColumn::all();
        $this->categories = TaskCategory::all();
        $view = view('admin.projects.tasks.edit', $this->data)->render();
        return Reply::dataOnly(['html' => $view]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTask $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $task->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        $task->user_id = $request->user_id;
        $task->priority = $request->priority;
        $task->task_category_id = $request->category_id;
        $task->board_column_id = $request->status;

        $taskBoardColumn = TaskboardColumn::findOrFail($request->status);
        if ($taskBoardColumn->slug == 'completed') {
            $task->completed_on = Carbon::now();
        } else {
            $task->completed_on = null;
        }

        if ($request->milestone_id != '') {
            $task->milestone_id = $request->milestone_id;
        }

        $task->save();

        //  Send notification to user
        $notifyUser = User::findOrFail($request->user_id);
        $notifyUser->notify(new TaskUpdated($task));

        if ($task->project_id != null) {
            if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable') {
                $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->project->client_id);
                $notifyUser->notify(new TaskUpdatedClient($task));
            }
        }

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

        $this->project = Project::findOrFail($task->project_id);

        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.taskUpdatedSuccessfully'), ['html' => $view]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $taskId = $request->taskId;
        $status = $request->status;
        $taskBoardColumn = TaskboardColumn::where('slug', $status)->first();
        $task = Task::with('project')->findOrFail($taskId);
        $task->board_column_id = $taskBoardColumn->id;
        //        $task->status = $status;

        if ($taskBoardColumn->slug == 'completed') {
            $task->completed_on = Carbon::today()->format('Y-m-d');
            $task->save();

            // send task complete notification
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->user_id);
            $notifyUser->notify(new TaskCompleted($task));

            if ($task->project_id != null) {
                if ($task->project->client_id != null  && $task->project->allow_client_notification == 'enable') {
                    $notifyClient = User::findOrFail($task->project->client_id);
                    $notifyClient->notify(new TaskCompleted($task));
                }
            }

            $admins = User::allAdmins($task->user_id);

            Notification::send($admins, new TaskCompleted($task));
        } else {
            $task->completed_on = null;
        }


        $task->save();
        
        if ($task->project_id != null) {
            if ($task->project->calculate_task_progress == "true") {
                //calculate project progress if enabled
                $this->calculateProjectProgress($task->project_id);
            }
            $this->project = Project::find($task->project_id);
            $this->project->tasks = Task::whereProjectId($this->project->id)->orderBy($request->sortBy, 'desc')->get();   
        }

        $this->task = $task;

        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.taskUpdatedSuccessfully'), ['html' => $view, 'textColor' => $task->board_column->label_color, 'column' => $task->board_column->column_name]);
    }

    public function sort(Request $request)
    {
        $projectId = $request->projectId;
        $this->sortBy = $request->sortBy;
        $taskBoardColumn = TaskboardColumn::where('slug', 'completed')->first();
        $this->project = Project::findOrFail($projectId);
        if ($request->sortBy == 'due_date') {
            $order = "asc";
        } else {
            $order = "desc";
        }

        $tasks = Task::whereProjectId($projectId)->orderBy($request->sortBy, $order);

        if ($request->hideCompleted == '1') {
            $tasks->where('board_column_id', '!=', $taskBoardColumn->id);
        }

        $this->project->tasks = $tasks->get();

        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        return Reply::dataOnly(['html' => $view]);
    }

    public function checkTask($taskID)
    {
        $task = Task::findOrFail($taskID);
        $subTask = SubTask::where(['task_id' => $taskID, 'status' => 'incomplete'])->count();

        return Reply::dataOnly(['taskCount' => $subTask, 'lastStatus' => $task->board_column->slug]);
    }

    public function data(Request $request, $projectId = null) {

        $tasks = Task::leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->leftJoin('users as client', 'client.id', '=', 'projects.client_id')
            ->join('users', 'users.id', '=', 'tasks.user_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
            ->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
            ->select('tasks.id', 'projects.project_name', 'tasks.heading', 'users.name','client.name as clientName','creator_user.name as created_by','creator_user.image as created_image', 'users.image', 'tasks.due_date', 'taskboard_columns.column_name', 'taskboard_columns.label_color', 'tasks.project_id')
            ->where('projects.id', $projectId);

        $tasks->get();

        return DataTables::of($tasks)
            ->addColumn('action', function($row){
                return '<a href="javascript:;" class="btn btn-info btn-circle edit-task"
                      data-toggle="tooltip" data-task-id="'.$row->id.'" data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        &nbsp;&nbsp;<a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-task-id="'.$row->id.'" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
            })
            ->editColumn('due_date', function($row){
                if($row->due_date->isPast()) {
                    return '<span class="text-danger">'.$row->due_date->format($this->global->date_format).'</span>';
                }
                return '<span class="text-success">'.$row->due_date->format($this->global->date_format).'</span>';
            })
            ->editColumn('name', function($row){
                return ($row->image) ? : '<img src="'.asset('img/default-profile-2.png').'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->name);
            })
            ->editColumn('clientName', function($row){
                return ($row->clientName) ? ucwords($row->clientName) : '-';
            })
            ->editColumn('created_by', function($row){
                if(!is_null($row->created_by)){
                    return ($row->created_image) ? '<img src="'.asset_url('avatar/'.$row->created_image).'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->created_by) : '<img src="'.asset('img/default-profile-2.png').'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->created_by);
                }
                return '-';
            })
            ->editColumn('heading', function($row){
                return '<a href="javascript:;" data-task-id="'.$row->id.'" class="show-task-detail">'.ucfirst($row->heading).'</a>';
            })
            ->editColumn('column_name', function($row){
                return '<label class="label" style="background-color: '.$row->label_color.'">'.$row->column_name.'</label>';
            })
            ->rawColumns(['column_name', 'action', 'clientName', 'due_date', 'name', 'created_by', 'heading'])
            ->removeColumn('project_id')
            ->removeColumn('image')
            ->removeColumn('created_image')
            ->removeColumn('label_color')
            ->make(true);
    }

    /**
     * @param $projectId
     */
    public function export($projectId) {

        $tasks = Task::leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->join('users', 'users.id', '=', 'tasks.user_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
            ->select('tasks.id', 'projects.project_name', 'tasks.heading', 'users.name', 'users.image', 'taskboard_columns.column_name', 'tasks.due_date')
            ->where('projects.id', $projectId);

        $attributes =  ['image', 'due_date'];

        $tasks = $tasks->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Project','Title','Assigned TO', 'Status','Due Date'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($tasks as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('task', function($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Task');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('task file');

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
