<?php

namespace App\Http\Controllers\Designer;

use App\Helper\Reply;
use App\Http\Requests\Project\StoreProject;
use App\Project;
use App\ProjectActivity;
use App\ProjectCategory;
use App\ProjectFile;
use App\ProjectMember;
use App\ProjectTemplate;
use App\ProjectTimeLog;
use App\Task;
use App\Traits\ProjectProgress;
use App\User;
use App\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Helper\Files;


/**
 * class DesignerProjectsController
 * @package App\Http\Controllers\Designer
 */
class DesignerProjectsController extends DesignerBaseController
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if ($this->user->can('view_projects')) {
            $this->totalProjects = Project::all()->count();
            $this->finishedProjects = Project::completed()->count();
            $this->inProcessProjects = Project::inProcess()->count();
            $this->onHoldProjects = Project::onHold()->count();
            $this->canceledProjects = Project::canceled()->count();
            $this->notStartedProjects = Project::notStarted()->count();
            $this->overdueProjects = Project::overdue()->count();
        } else {
            $this->totalProjects = Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.user_id', $this->user->id)->count();
            $this->finishedProjects = Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.user_id', $this->user->id)->completed()->count();
            $this->inProcessProjects = Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.user_id', $this->user->id)->inProcess()->count();
            $this->onHoldProjects = Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.user_id', $this->user->id)->onHold()->count();
            $this->canceledProjects = Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.user_id', $this->user->id)->canceled()->count();
            $this->notStartedProjects = Project::join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.user_id', $this->user->id)->notStarted()->count();
        }


        $this->clients = Client::all();
        return view('designer.projects.index', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort(403);
        $this->project = Project::findOrFail($id)->withCustomFields();

        if (!$this->project->isProjectAdmin && !$this->user->can('edit_projects')) {
            abort(403);
        }

        $this->clients = Client::all();
        $this->categories = ProjectCategory::all();
        $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;

        return view('designer.projects.edit', $this->data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $this->userDetail = auth()->user();

        $this->project = Project::findOrFail($id);

        $isMember = Project::checkIsMember($id);

        // Check authorised user

        if ($isMember) {
            $this->activeTimers = ProjectTimeLog::projectActiveTimers($this->project->id);

            $this->openTasks = Task::projectOpenTasks($this->project->id, $this->userDetail->id);

            return view('designer.projects.show', $this->data);
        } else {
            // If not authorised user
            abort(403);
        }


    }

    public function data(Request $request)
    {
        $this->userDetail = auth()->user();
        $projects = Project::select('projects.id', 'projects.project_name', 'projects.client_id','projects.created_at', 'projects.updated_at', 'projects.status');

//        if (!$this->user->can('view_projects')) {
//            $projects = $projects->join('project_members', 'project_members.project_id', '=', 'projects.id');
//            $projects = $projects->where('project_members.user_id', '=', $this->userDetail->id);
//        }

//        if (!is_null($request->status) && $request->status != 'all') {
//            if ($request->status == 'incomplete') {
//                $projects->where('completion_percent', '<', '100');
//            } elseif ($request->status == 'complete') {
//                $projects->where('completion_percent', '=', '100');
//            }
//        }

        $projects = $projects->where('projects.user_id', '=', $this->userDetail->id);
        if (!is_null($request->client_id) && $request->client_id != 'all') {
            $projects->where('client_id', $request->client_id);
        }

        $projects->get();

        return DataTables::of($projects)
            ->addColumn('action', function ($row) {
                $action = '<a href="' . route('designer.projects.show', [$row->id]) . '" class="btn btn-success btn-circle"
                      data-toggle="tooltip" data-original-title="View Project Details"><i class="fa fa-search" aria-hidden="true"></i></a>';
                return $action;
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

                if ($this->user->can('add_projects')) {
                    $members .= '<br><br><a class="font-12" href="' . route('designer.project-members.show', $row->id) . '"><i class="fa fa-plus"></i> ' . __('modules.projects.addMemberTitle') . '</a>';
                }
                return $members;
            })

            ->editColumn('project_name', function ($row) {
                return '<a href="' . route('designer.projects.show', $row->id) . '">' . ucfirst($row->project_name) . '</a>';
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
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('client_id', function ($row) {
                return '<a href="' . route('designer.clients.show', $row->id) . '">' . $row->client->full_name . '</a>';
            })
            ->rawColumns(['project_name', 'action', 'client_id', 'status'])
            ->make(true);
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
        abort(403);
        $project = Project::findOrFail($id);
        $project->project_name = $request->project_name;
        if ($request->project_summary != '') {
            $project->project_summary = $request->project_summary;
        }
        
        $project->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        if (!$request->has('without_deadline')) {
            $project->deadline = Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d');
        }
        if ($request->notes != '') {
            $project->notes = $request->notes;
        }
        if ($request->category_id != '') {
            $project->category_id = $request->category_id;
        }

        $project->client_id = ($request->client_id == 'null' || $request->client_id == '') ? null : $request->client_id;
        $project->feedback = $request->feedback;

        if ($request->calculate_task_progress) {
            $project->calculate_task_progress = $request->calculate_task_progress;
            $project->completion_percent = $this->calculateProjectProgress($id);
        } else {
            $project->calculate_task_progress = "false";
            $project->completion_percent = $request->completion_percent;
        }

        if ($request->client_view_task) {
            $project->client_view_task = 'enable';
        } else {
            $project->client_view_task = "disable";
        }
        if (($request->client_view_task) && ($request->client_task_notification)) {
            $project->allow_client_notification = 'enable';
        } else {
            $project->allow_client_notification = "disable";
        }

        if ($request->manual_timelog) {
            $project->manual_timelog = 'enable';
        } else {
            $project->manual_timelog = "disable";
        }
        $project->status = $request->status;

        $project->save();

        $this->logProjectActivity($project->id, ucwords($project->project_name) . __('modules.projects.projectUpdated'));
        return Reply::redirect(route('designer.projects.index'), __('messages.projectUpdated'));
    }

    public function create()
    {
        if (!$this->user->can('add_projects')) {
            abort(403);
        }
        abort(403);
        $this->clients = Client::all();
        $this->categories = ProjectCategory::all();
        $this->templates = ProjectTemplate::all();

        $project = new Project();
        $this->fields = $project->getCustomFieldGroupsWithFields()->fields;
        return view('designer.projects.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProject $request) {
        abort(403);
        $project = new Project();
        $project->project_name = $request->project_name;
        if ($request->project_summary != '') {
            $project->project_summary = $request->project_summary;
        }
        $project->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');

        if (!$request->has('without_deadline')) {
            $project->deadline = Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d');
        } else {
            $project->deadline = null;
        }

        if ($request->notes != '') {
            $project->notes = $request->notes;
        }
        if ($request->category_id != '') {
            $project->category_id = $request->category_id;
        }
        $project->client_id = $request->client_id;

        if ($request->client_view_task) {
            $project->client_view_task = 'enable';
        } else {
            $project->client_view_task = "disable";
        }
        if (($request->client_view_task) && ($request->client_task_notification)) {
            $project->allow_client_notification = 'enable';
        } else {
            $project->allow_client_notification = "disable";
        }

        if ($request->manual_timelog) {
            $project->manual_timelog = 'enable';
        } else {
            $project->manual_timelog = "disable";
        }

        $project->status = $request->status;


        $project->save();

        if ($request->template_id) {
            $template = ProjectTemplate::findOrFail($request->template_id);
            foreach ($template->members as $member) {
                $projectMember = new ProjectMember();

                $projectMember->user_id    = $member->user_id;
                $projectMember->project_id = $project->id;
                $projectMember->save();
            }
            foreach ($template->tasks as $task) {
                $projectTask = new Task();

                $projectTask->user_id     = $task->user_id;
                $projectTask->project_id  = $project->id;
                $projectTask->heading     = $task->heading;
                $projectTask->description = $task->description;
                $projectTask->due_date    = Carbon::now()->addDay()->format('Y-m-d');
                $projectTask->status      = 'incomplete';
                $projectTask->save();
            }
        }

        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $project->updateCustomFieldData($request->get('custom_fields_data'));
        }

        $this->logSearchEntry($project->id, 'Project: '.$project->project_name, 'admin.projects.show');

        $this->logProjectActivity($project->id, ucwords($project->project_name) . ' '. __("messages.addedAsNewProject"));
        return Reply::redirect(route('designer.projects.index'), __('modules.projects.projectUpdated'));
    }

    public function destroy($id)
    {
        abort(403);
        $project = Project::withTrashed()->findOrFail($id);

        //delete project files
        Files::deleteDirectory('project-files/' . $id);

        $project->forceDelete();

        return Reply::success(__('messages.projectDeleted'));
    }

}
