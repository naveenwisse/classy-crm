<?php

namespace App\Http\Controllers\Admin;

use App\Expense;
use App\Helper\Reply;
use App\Http\Requests\Project\StoreProject;
use App\ProjectActivity;
use App\ProjectCategory;
use App\ProjectFile;
use App\ProjectMember;
use App\ProjectTemplate;
use App\ProjectTimeLog;
use App\Task;
use App\Client;
use App\Lead;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Project;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\ProjectProgress;
use App\Currency;
use App\Helper\Files;
use App\ProjectMilestone;
use App\Payment;

class ManageProjectsController extends AdminBaseController
{

    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.projects');
        $this->pageIcon = 'icon-layers';
        $this->middleware(function ($request, $next) {
            if (!in_array('projects', $this->user->modules)) {
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
        $this->totalProjects = Project::all()->count();
        $this->finishedProjects = Project::completed()->count();
        $this->inProcessProjects = Project::inProcess()->count();
        $this->onHoldProjects = Project::onHold()->count();
        $this->canceledProjects = Project::canceled()->count();
        $this->notStartedProjects = Project::notStarted()->count();

        $this->clients = Client::all();
        $this->categories = ProjectCategory::all();


        return view('admin.projects.index', $this->data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function archive()
    {
        $this->totalProjects = Project::onlyTrashed()->count();
        $this->clients = Client::all();
        return view('admin.projects.archive', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->currencies = Currency::all();
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesigners();
        $this->clients = Client::all();
        return view('admin.projects.create', $this->data);
    }

    public function createFromLead($id)
    {
        $this->leadDetail = Lead::findOrFail($id);
        if(empty($this->leadDetail->client_id)){
            abort(403);
        }
        $this->currencies = Currency::all();
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesigners();
        $this->clients = Client::all();

        return view('admin.projects.create', $this->data);
    }

    public function createFromClient($id)
    {
        $this->currencies = Currency::all();
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesigners();
        $this->clients = Client::all();
        $this->clientDetail = CLient::findOrFail($id);
        return view('admin.projects.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProject $request)
    {
        $memberExistsInTemplate = false;

        $project = new Project();
        $project->project_name = $request->project_name;
        $project->address1 = $request->address1;
        $project->address2 = $request->address2;
        $project->city = $request->city;
        $project->state = $request->state;
        $project->zip = $request->zip;
        $project->contact = $request->contact;
        $project->phone = $request->phone;
        $project->ext = $request->ext;
        $project->cell = $request->cell;
        $project->fax = $request->fax;
        $project->email = $request->email;
        $project->install_start_date = Carbon::createFromFormat($this->global->date_format, $request->install_start_date)->format('Y-m-d');
        $project->install_end_date = Carbon::createFromFormat($this->global->date_format, $request->install_end_date)->format('Y-m-d');
        $project->status = $request->status;
        $project->sales_price = $request->sales_price;
        $project->sold_date = Carbon::createFromFormat($this->global->date_format, $request->sold_date)->format('Y-m-d');
        $project->user_id = $request->user_id;
        $project->client_id = $request->client_id;
        $project->save();

        $this->logSearchEntry($project->id, 'Project: ' . $project->project_name, 'admin.projects.show');

        $this->logProjectActivity($project->id, ucwords($project->project_name) . ' ' . __("messages.addedAsNewProject"));


        if ($request->has('leadDetail')) {
            $lead = Lead::findOrFail($request->leadDetail);
            $lead->project_id = $project->id;
            $lead->save();

            return Reply::redirect(route('admin.leads.index'), __('messages.leadProjectChangeSuccess'));
        }

        return Reply::redirect(route('admin.projects.index'), __('modules.projects.projectUpdated'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->project = Project::with('members', 'members.user')->findOrFail($id)->withCustomFields();
        $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;

        $this->activeTimers = ProjectTimeLog::projectActiveTimers($this->project->id);
        $this->openTasks = Task::projectOpenTasks($this->project->id);
        $this->openTasksPercent = (count($this->openTasks) == 0 ? "0" : (count($this->openTasks) / count($this->project->tasks)) * 100);

        $this->daysLeft = 0;
        $this->daysLeftFromStartDate = 0;
        $this->daysLeftPercent = 0;

        if (is_null($this->project->deadline)) {
            $this->daysLeft = 0;
        } else {
            if ($this->project->deadline->isPast()) {
                $this->daysLeft = 0;
            } else {
                $this->daysLeft = $this->project->deadline->diff(Carbon::now())->format('%d') + ($this->project->deadline->diff(Carbon::now())->format('%m') * 30) + ($this->project->deadline->diff(Carbon::now())->format('%y') * 12);
            }
            $this->daysLeftFromStartDate = $this->project->deadline->diff($this->project->start_date)->format('%d') + ($this->project->deadline->diff($this->project->start_date)->format('%m') * 30) + ($this->project->deadline->diff($this->project->start_date)->format('%y') * 12);
            $this->daysLeftPercent = ($this->daysLeftFromStartDate == 0 ? "0" : (($this->daysLeft / $this->daysLeftFromStartDate) * 100));
        }
        

        $this->hoursLogged = ProjectTimeLog::projectTotalMinuts($this->project->id);

        $hour = intdiv($this->hoursLogged, 60);

        if (($this->hoursLogged % 60) > 0) {
            $minute = ($this->hoursLogged % 60);
            $this->hoursLogged = $hour . 'hrs ' . $minute . ' mins';
        } else {
            $this->hoursLogged = $hour;
        }

        $this->recentFiles = ProjectFile::where('project_id', $this->project->id)
            ->orderBy('id', 'desc')->limit(10)->get();
        $this->activities = ProjectActivity::getProjectActivities($id, 10);
        //        $this->completedTasks = Task::projectCompletedTasks($this->project->id);
        $this->milestones = ProjectMilestone::with('currency')->where('project_id', $id)->get();
        $this->earnings = Payment::where('status', 'complete')
            ->where('project_id', $id)
            ->sum('amount');
        $this->expenses = Expense::where(['project_id' => $id, 'status' => 'approved'])->sum('price');

        return view('admin.projects.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->project = Project::findOrFail($id);
        if(!is_null($this->project->sold_date))
            $this->project->sold_date = Carbon::createFromFormat('Y-m-d', $this->project->sold_date)->format($this->global->date_format);
        if(!is_null($this->project->install_start_date))
            $this->project->install_start_date = Carbon::createFromFormat('Y-m-d', $this->project->install_start_date)->format($this->global->date_format);
        if(!is_null($this->project->install_end_date))
            $this->project->install_end_date = Carbon::createFromFormat('Y-m-d', $this->project->install_end_date)->format($this->global->date_format);
        $this->currencies = Currency::all();
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesigners();
        $this->clients = Client::all();
        return view('admin.projects.edit', $this->data);
    }


    public function getDetail($id)
    {
        $project = Project::findOrFail($id);
        if(!is_null($project->sold_date))
            $project->sold_date = Carbon::createFromFormat('Y-m-d', $this->project->sold_date)->format($this->global->date_format);

        if($project){
            $data = $project->toArray();
            if($project->client_id){
                $data['client_name'] = $project->client->full_name;
            }
            else{
                $data['client_name'] ='';
            }
            return Reply::dataOnly(['status'  => 'success', 'data' => $data ]);
        }
        return [];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProject $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->project_name = $request->project_name;
        $project->address1 = $request->address1;
        $project->address2 = $request->address2;
        $project->city = $request->city;
        $project->state = $request->state;
        $project->zip = $request->zip;
        $project->contact = $request->contact;
        $project->phone = $request->phone;
        $project->ext = $request->ext;
        $project->cell = $request->cell;
        $project->fax = $request->fax;
        $project->email = $request->email;
        $project->install_start_date = Carbon::createFromFormat($this->global->date_format, $request->install_start_date)->format('Y-m-d');
        $project->install_end_date = Carbon::createFromFormat($this->global->date_format, $request->install_end_date)->format('Y-m-d');
        $project->status = $request->status;
        $project->sales_price = $request->sales_price;
        $project->sold_date = Carbon::createFromFormat($this->global->date_format, $request->sold_date)->format('Y-m-d');
        $project->user_id = $request->user_id;
        $project->client_id = $request->client_id;
        $project->save();

        $this->logProjectActivity($project->id, ucwords($project->project_name) . __('modules.projects.projectUpdated'));
        return Reply::redirect(route('admin.projects.index'), __('messages.projectUpdated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    
        $project = Project::withTrashed()->findOrFail($id);

        //delete project files
        // Files::deleteDirectory('project-files/' . $id);

        $project->forceDelete();
        $leads = Lead::where('project_id', $id)->get();
        foreach ($leads as $lead){
            $lead->project_id = NULL;
            $lead->save();
        }

        return Reply::success(__('messages.projectDeleted'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function archiveDestroy($id)
    {

        Project::destroy($id);

        return Reply::success(__('messages.projectArchiveSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function archiveRestore($id)
    {

        $project = Project::withTrashed()->findOrFail($id);
        $project->restore();

        return Reply::success(__('messages.projectRevertSuccessfully'));
    }

    public function data(Request $request)
    {

        $projects = Project::select('id', 'project_name', 'status', 'created_at', 'client_id');

        if (!is_null($request->status) && $request->status != 'all') {
            $projects->where('status', $request->status);
        }

//        if (!is_null($request->client_id) && $request->client_id != 'all') {
//            $projects->where('client_id', $request->client_id);
//        }

        if (!is_null($request->category_id) && $request->category_id != 'all') {
            $projects->where('category_id', $request->category_id);
        }

        $projects->get();

        return DataTables::of($projects)
            ->addColumn('action', function ($row) {
                $action = '<div class="btn-group m-r-10">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-info btn-outline  dropdown-toggle waves-effect waves-light" type="button">'.__('modules.lead.action').'  <span class="caret"></span></button>
                <ul role="menu" class="dropdown-menu">
                    <li><a href="'.route('admin.projects.show', $row->id).'"><i class="fa fa-search"></i> '.__('modules.lead.view').'</a></li>
                    <li><a href="'.route('admin.projects.edit', $row->id).'"><i class="fa fa-edit"></i> '.__('modules.lead.edit').'</a></li>
                    <li><a href="javascript:;" class="sa-params" data-user-id="'.$row->id.'"><i class="fa fa-trash "></i> '.__('app.delete').'</a></li>  
                </ul>
              </div>';
                return $action;
            })
            ->editColumn('project_name', function ($row) {
                return ucfirst($row->project_name);
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('status', function ($row) {

                if ($row->status == 'in progress') {
                    $status = '<label class="label label-info">' . __('app.inProgress') . '</label>';
                } else if ($row->status == 'on hold') {
                    $status = '<label class="label label-warning">' . __('app.onHold') . '</label>';
                } else if ($row->status == 'not started') {
                    $status = '<label class="label label-warning">' . __('app.notStarted') . '</label>';
                } else if ($row->status == 'canceled') {
                    $status = '<label class="label label-danger">' . __('app.canceled') . '</label>';
                } else if ($row->status == 'completed') {
                    $status = '<label class="label label-success">' . __('app.completed') . '</label>';
                }
                return $status;
            })
            ->addColumn('client_name', function ($row) {
                return $row->client->full_name ?? '';
            })
            ->rawColumns(['project_name', 'action', 'created_at', 'status', 'client_name'])
            ->removeColumn('project_summary')
            ->removeColumn('notes')
            ->removeColumn('category_id')
            ->removeColumn('feedback')
            ->removeColumn('start_date')
            ->make(true);
    }

    public function archiveData(Request $request)
    {
        $projects = Project::with('members', 'members.user', 'client')->select('id', 'project_name', 'start_date', 'deadline', 'client_id', 'completion_percent');

        if (!is_null($request->status) && $request->status != 'all') {
            if ($request->status == 'incomplete') {
                $projects->where('completion_percent', '<', '100');
            } elseif ($request->status == 'complete') {
                $projects->where('completion_percent', '=', '100');
            }
        }

        if (!is_null($request->client_id) && $request->client_id != 'all') {
            $projects->where('client_id', $request->client_id);
        }

        $projects->onlyTrashed()->get();

        return DataTables::of($projects)
            ->addColumn('action', function ($row) {
                return '
                      <a href="javascript:;" class="btn btn-info btn-circle revert"
                      data-toggle="tooltip" data-user-id="' . $row->id . '" data-original-title="Restore"><i class="fa fa-undo" aria-hidden="true"></i></a>
                       <a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-user-id="' . $row->id . '" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
            })
            ->addColumn('members', function ($row) {
                $members = '';

                if (count($row->members) > 0) {
                    foreach ($row->members as $member) {
                        $members .= ($member->user->image) ? '<img data-toggle="tooltip" data-original-title="' . ucwords($member->user->name) . '" src="' . asset_url('avatar/' . $member->user->image) . '"
                        alt="user" class="img-circle" width="30"> ' : '<img data-toggle="tooltip" data-original-title="' . ucwords($member->user->name) . '" src="' . asset('img/default-profile-2.png') . '"
                        alt="user" class="img-circle" width="30"> ';
                    }
                } else {
                    $members .= __('messages.noMemberAddedToProject');
                }
                return $members;
            })
            ->editColumn('project_name', function ($row) {
                return ucfirst($row->project_name);
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d M, Y');
            })
            ->editColumn('status', function ($row) {
                if (is_null($row->status)) {
                    return "";
                }
                return ucwords($row->status);
            })
            ->rawColumns(['project_name', 'action'])
            ->removeColumn('project_summary')
            ->removeColumn('notes')
            ->removeColumn('category_id')
            ->removeColumn('feedback')
            ->removeColumn('start_date')
            ->make(true);
    }

    public function export($status = null, $clientID = null)
    {
        $projects = Project::leftJoin('users', 'users.id', '=', 'projects.client_id')
            ->leftJoin('project_category', 'project_category.id', '=', 'projects.category_id')
            ->select(
                'projects.id',
                'projects.project_name',
                'users.name',
                'project_category.category_name',
                'projects.start_date',
                'projects.deadline',
                'projects.completion_percent',
                'projects.created_at'
            );
        if (!is_null($status) && $status != 'all') {
            if ($status == 'incomplete') {
                $projects = $projects->where('completion_percent', '<', '100');
            } elseif ($status == 'complete') {
                $projects = $projects->where('completion_percent', '=', '100');
            }
        }

        if (!is_null($clientID) && $clientID != 'all') {
            $projects = $projects->where('client_id', $clientID);
        }

        $projects = $projects->get();

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Project Name', 'Client Name', 'Category', 'Start Date', 'Deadline', 'Completion Percent', 'Created at'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($projects as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('Projects', function ($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Projects');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('Projects file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function ($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function ($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));
                });
            });
        })->download('xlsx');
    }

    public function gantt()
    {

        $data = array();
        $links = array();

        $projects = Project::select('id', 'project_name', 'start_date', 'deadline', 'completion_percent')->get();

        $id = 0; //count for gantt ids
        foreach ($projects as $project) {
            $id = $id + 1;
            $projectId = $id;

            // TODO::ProjectDeadline to do
            $projectDuration = 0;
            if ($project->deadline) {
                $projectDuration = $project->deadline->diffInDays($project->start_date);
            }

            $data[] = [
                'id' => $projectId,
                'text' => ucwords($project->project_name),
                'start_date' => $project->start_date->format('Y-m-d H:i:s'),
                'duration' => $projectDuration,
                'progress' => $project->completion_percent/100
            ];

            $tasks = Task::projectOpenTasks($project->id);

            foreach ($tasks as $key => $task) {
                $id = $id + 1;

                $taskDuration = $task->due_date->diffInDays($task->start_date);
                $data[] = [
                    'id' => $id,
                    'text' => ucfirst($task->heading),
                    'start_date' => (!is_null($task->start_date)) ? $task->start_date->format('Y-m-d H:i:s') : $task->due_date->format('Y-m-d H:i:s'),
                    'duration' => $taskDuration,
                    'parent' => $projectId,
                    'users' => [
                        ucwords($task->user->name)
                    ]
                ];

                $links[] = [
                    'id' => $id,
                    'source' => $project->id,
                    'target' => $task->id,
                    'type' => 1
                ];
            }

            $ganttData = [
                'data' => $data,
                'links' => $links
            ];
        }
        //        return $ganttData;
        return view('admin.projects.gantt', $this->data);
    }

    public function ganttData()
    {

        $data = array();
        $links = array();

        $projects = Project::select('id', 'project_name', 'start_date', 'deadline', 'completion_percent')->get();

        $id = 0; //count for gantt ids
        foreach ($projects as $project) {
            $id = $id + 1;
            $projectId = $id;

            // TODO::ProjectDeadline to do
            $projectDuration = 0;
            if ($project->deadline) {
                $projectDuration = $project->deadline->diffInDays($project->start_date);
            }

            $data[] = [
                'id' => $projectId,
                'text' => ucwords($project->project_name),
                'start_date' => $project->start_date->format('Y-m-d H:i:s'),
                'duration' => $projectDuration,
                'progress' => $project->completion_percent/100,
                'project_id' => $project->id
            ];

            $tasks = Task::projectOpenTasks($project->id);

            foreach ($tasks as $key => $task) {
                $id = $id + 1;

                $taskDuration = $task->due_date->diffInDays($task->start_date);
                $taskDuration = $taskDuration > 1 ? $taskDuration +1 : $taskDuration;

                $data[] = [
                    'id' => $id,
                    'text' => ucfirst($task->heading),
                    'start_date' => (!is_null($task->start_date)) ? $task->start_date->format('Y-m-d'): $task->due_date->format('Y-m-d'),
                    'duration' => $taskDuration,
                    'parent' => $projectId,
                    'users' => [
                        ucwords($task->user->name)
                    ],
                    'taskid' => $task->id
                ];

                $links[] = [
                    'id' => $id,
                    'source' => $projectId,
                    'target' => $id,
                    'type' => 1
                ];
            }

            $ganttData = [
                'data' => $data,
                'links' => $links
            ];
        }

        return response()->json($ganttData);
    }

    public function updateTaskDuration(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d');
        $task->save();

        return Reply::success('messages.taskUpdatedSuccessfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $project = Project::find($id)
            ->update([
                'status' => $request->status
            ]);

        return Reply::dataOnly(['status' => 'success']);
    }

    public function ajaxCreate(Request $request, $projectId)
    {
        $this->projectId = $projectId;
        $this->projects = Project::all();
        $this->employees = ProjectMember::byProject($projectId);
        $this->pageName = 'ganttChart';
        $this->parentGanttId = $request->parent_gantt_id;
        return view('admin.tasks.ajax_create', $this->data);
    }
}
