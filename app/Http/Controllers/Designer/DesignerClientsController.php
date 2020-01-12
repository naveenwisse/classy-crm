<?php

namespace App\Http\Controllers\Designer;

use App\ClientDetails;
use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientRequest;
use App\Http\Requests\Admin\Client\UpdateClientRequest;
use App\Invoice;
use App\Notifications\NewUser;
use App\User;
use App\Client;
use App\Project;
use App\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Config;

class DesignerClientsController extends DesignerBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.clients');
        $this->pageIcon = 'icon-people';

        $this->middleware(function ($request, $next) {
            if (!in_array('clients', $this->user->modules)) {
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
        $this->clients = Client::all();

        return view('designer.clients.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(403);
        $client = new ClientDetails();
        $this->fields = $client->getCustomFieldGroupsWithFields()->fields;
        return view('designer.clients.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClientRequest $request)
    {
        abort(403);
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->mobile = $request->input('mobile');
        $user->save();

        if ($user->id) {
            $client = new ClientDetails();
            $client->user_id = $user->id;
            $client->company_name = $request->company_name;
            $client->address = $request->address;
            $client->website = $request->website;
            $client->note = $request->note;
            $client->skype = $request->skype;
            $client->facebook = $request->facebook;
            $client->twitter = $request->twitter;
            $client->linkedin = $request->linkedin;
            $client->gst_number = $request->gst_number;
            $client->save();
        }

        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $client->updateCustomFieldData($request->get('custom_fields_data'));
        }

        $user->attachRole(3);


        if ($this->emailSetting[0]->send_email == 'yes') {
            //send welcome email notification
            $user->notify(new NewUser($request->input('password')));
        }

        //log search
        $this->logSearchEntry($user->id, $user->name, 'admin.clients.edit');
        $this->logSearchEntry($user->id, $user->email, 'admin.clients.edit');
        if (!is_null($client->company_name)) {
            $this->logSearchEntry($user->id, $client->company_name, 'admin.clients.edit');
        }

        return Reply::redirect(route('designer.clients.index'),__('messages.clientAdded'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->client = Client::findOrFail($id);
        $this->states = Config::get('constants.states');
        return view('designer.clients.show', $this->data);
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
        $this->userDetail = User::withoutGlobalScope('active')->findOrFail($id);
        $this->clientDetail = ClientDetails::where('user_id', '=', $this->userDetail->id)->first();

        if (!is_null($this->clientDetail)) {
            $this->clientDetail = $this->clientDetail->withCustomFields();
            $this->fields = $this->clientDetail->getCustomFieldGroupsWithFields()->fields;
        }

        return view('designer.clients.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, $id)
    {
        abort(403);
        $user = User::withoutGlobalScope('active')->findOrFail($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if ($request->password != '') {
            $user->password = Hash::make($request->input('password'));
        }
        $user->mobile = $request->input('mobile');
        $user->save();

        $client = ClientDetails::where('user_id', '=', $user->id)->first();
        if (empty($client)) {
            $client = new ClientDetails();
            $client->user_id = $user->id;
        }
        $client->company_name = $request->company_name;
        $client->address = $request->address;
        $client->website = $request->website;
        $client->note = $request->note;
        $client->skype = $request->skype;
        $client->facebook = $request->facebook;
        $client->twitter = $request->twitter;
        $client->linkedin = $request->linkedin;
        $client->gst_number = $request->gst_number;
        $client->save();

        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $client->updateCustomFieldData($request->get('custom_fields_data'));
        }

        return Reply::redirect(route('designer.clients.index'), __('messages.clientUpdated'));

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
        User::destroy($id);
        return Reply::success(__('messages.clientDeleted'));
    }

    public function data(Request $request)
    {
        $this->userDetail = auth()->user();
        $clients = Client::select('id', 'first_name', 'last_name', 'email', 'cell', 'zip', 'created_at');
        $projects = Project::where('projects.user_id', '=', $this->userDetail->id)->select('id', 'client_id')->pluck('client_id')->toArray();
        
        $leads = Lead::where('leads.user_id', '=', $this->userDetail->id)->select('id', 'client_id')->pluck('client_id')->toArray();
        $client_ids = array_merge($projects, $leads);
        

        // if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
        //     $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
        //     $clients = $clients->where(DB::raw('DATE(`created_at`)'), '>=', $startDate);
        // }

        // if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
        //     $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
        //     $clients = $clients->where(DB::raw('DATE(`created_at`)'), '<=', $endDate);
        // }

        // if ($request->client != 'all' && $request->client != '') {
        //     $clients = $clients->where('id', $request->client);
        // }

        $clients = $clients->whereIn('id', $client_ids)->get();

        return DataTables::of($clients)
            ->addColumn('action', function($row){
                $action = '<a href="' . route('designer.clients.show', [$row->id]) . '" class="btn btn-success btn-circle"
                      data-toggle="tooltip" data-original-title="View Client Details"><i class="fa fa-search" aria-hidden="true"></i></a>';
                return $action;
            })
//            ->editColumn(
//                'name',
//                function ($row) {
//                    return '<a href="'.route('admin.clients.projects', $row->id).'">'.ucfirst($row->first_name).' '.ucfirst($row->last_name).'</a>';
//                }
//            )
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->format($this->global->date_format);
                }
            )
            ->rawColumns(['name', 'action'])
            ->make(true);
    }

    public function showProjects($id) 
    {
        $this->client = User::withoutGlobalScope('active')->findOrFail($id);
        return view('designer.clients.projects', $this->data);
    }

    public function showInvoices($id)
    {
        if (!$this->user->can('view_invoices')) {
            abort(403);
        }
        $this->client = User::withoutGlobalScope('active')->findOrFail($id);
        $this->invoices = Invoice::join('projects', 'projects.id', '=', 'invoices.project_id')
            ->join('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->join('users', 'users.id', '=', 'projects.client_id')
            ->select('invoices.invoice_number', 'invoices.total', 'currencies.currency_symbol', 'invoices.issue_date', 'invoices.id')
            ->where('projects.client_id', $id)
            ->get();

        return view('designer.clients.invoices', $this->data);
    }

    public function export()
    {
        $rows = User::leftJoin('client_details', 'users.id', '=', 'client_details.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.mobile',
                'client_details.company_name',
                'client_details.address',
                'client_details.website',
                'users.created_at'
            )
            ->get();

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Name', 'Email', 'Mobile', 'Company Name', 'Address', 'Website', 'Created at'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($rows as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('clients', function ($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Clients');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('clients file');

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
}
