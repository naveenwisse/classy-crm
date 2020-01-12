<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\CommonRequest;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportSettingsController extends AdminBaseController
{

    public function __construct() {
        parent:: __construct();
        $this->pageTitle = __('app.menu.reportSettings');
        $this->pageIcon = 'icon-settings';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.report-settings.index', $this->data);
    }

    public function leadSetting(){
        $this->fields = DB::getSchemaBuilder()->getColumnListing('leads');

        return view('admin.report-settings.lead', $this->data);
    }

    public function projectSetting(){
        $this->fields = DB::getSchemaBuilder()->getColumnListing('leads');

        return view('admin.report-settings.project', $this->data);
    }

    public function appointmentSetting(){
        $this->roles = Role::all();
        $this->fields = DB::getSchemaBuilder()->getColumnListing('leads');

        return view('admin.report-settings.appointment', $this->data);
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
    public function store(CommonRequest $request)
    {
        $company = $this->global;

        $company->task_self = ($request->has('self_task')) ? 'yes' : 'no';
        $company->save();

        return Reply::redirect( route('admin.task-settings.index'), __('messages.logTimeUpdateSuccess'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

}
