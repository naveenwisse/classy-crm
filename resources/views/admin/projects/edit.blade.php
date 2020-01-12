@extends('layouts.app') 
@section('page-title')
<div class="row bg-title">
    <!-- .page title -->
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
    </div>
    <!-- /.page title -->
    <!-- .breadcrumb -->
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
            <li><a href="{{ route('admin.projects.index') }}">{{ $pageTitle }}</a></li>
            <li class="active">@lang('app.edit')</li>
        </ol>
    </div>
    <!-- /.breadcrumb -->
</div>
@endsection
 @push('head-script')
<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">

<link rel="stylesheet" href="{{ asset('plugins/bower_components/ion-rangeslider/css/ion.rangeSlider.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/ion-rangeslider/css/ion.rangeSlider.skinModern.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<style>
    .panel-black .panel-heading a,
    .panel-inverse .panel-heading a {
        color: unset!important;
    }
</style>




@endpush 
@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="panel panel-inverse">
            <div class="panel-heading"> @lang('modules.projects.updateTitle')</div>
            <div class="panel-wrapper collapse in" aria-expanded="true">
                <div class="panel-body">
                    {!! Form::open(['id'=>'updateProject','class'=>'ajax-form','method'=>'PUT']) !!}

                    <div class="form-body">
                        <h3 class="box-title m-b-30">@lang('modules.projects.projectInfo') - #{{$project->id}}</h3>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.projects.projectName')</label>
                                    <input type="text" id="project_name" name="project_name" class="form-control" value="{{ $project->project_name ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">@lang('app.address') 1</label>
                                    <input type="text" name="address1"  id="address1" class="form-control" value="{{ $project->address1 ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">@lang('app.address') 2</label>
                                    <input type="text" name="address2"  id="address2" class="form-control" value="{{ $project->address2 ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.lead.city')</label>
                                    <input type="text" name="city"  id="city" class="form-control" value="{{ $project->city ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <div class="col-md-6 no-padding-left">
                                        <div class="form-group">
                                            <label class="control-label">@lang('modules.lead.state')</label>
                                            <select class="form-control" id="state" name="state">
                                                @forelse($states as $key => $value)
                                                    <option @if($project->states == $key) selected
                                                            @endif value="{{ $key }}"> {{ $value }}</option>
                                                @empty

                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 no-padding">
                                        <div class="form-group">
                                            <label class="control-label">@lang('modules.lead.zip')</label>
                                            <input type="text" name="zip"  id="zip" class="form-control" value="{{ $project->zip ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-center">
                                    <div class="form-group">
                                        <button type="button" id="btn_google_map" class="btn btn-primary"> <i class="fa fa-globe" aria-hidden="true"></i> Google Map</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <div class="col-md-8 no-padding-left">
                                        <div class="form-group">
                                            <label>@lang('modules.lead.phone')</label>
                                            <input type="tel" name="phone" id="phone" class="form-control" value="{{ $project->phone ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4 no-padding">
                                        <div class="form-group">
                                            <label>@lang('modules.lead.ext')</label>
                                            <input type="text" name="ext" id="ext" class="form-control" value="{{ $project->ext ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>@lang('modules.lead.cell')</label>
                                    <input type="tel" name="cell" id="cell" class="form-control" value="{{ $project->cell ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>@lang('modules.lead.fax')</label>
                                    <input type="tel" name="fax" id="fax" class="form-control" value="{{ $project->fax ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>@lang('modules.lead.email')</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ $project->email ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.projects.installStartDate')</label>
                                    <input type="text" name="install_start_date" id="install_start_date" class="form-control" autocomplete="off" value="{{ $project->install_start_date ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.projects.installEndDate')</label>
                                    <input type="text" name="install_end_date"  id="install_end_date" class="form-control" autocomplete="off" value="{{ $project->install_end_date ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label class="control-label">@lang('modules.projects.contact')</label>
                                <input type="text" name="contact"  id="contact" class="form-control" value="{{$project->contact}}">
                            </div>
                        </div>

                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label class="control-label">@lang('app.project') @lang('app.status')</label>
                                <select name="status" id="" class="form-control">
                                    <option
                                            @if($project->status == 'not started') selected @endif
                                    value="not started">@lang('app.notStarted')
                                    </option>
                                    <option
                                            @if($project->status == 'in progress') selected @endif
                                    value="in progress">@lang('app.inProgress')
                                    </option>
                                    <option
                                            @if($project->status == 'on hold') selected @endif
                                    value="on hold">@lang('app.onHold')
                                    </option>
                                    <option
                                            @if($project->status == 'canceled') selected @endif
                                    value="canceled">@lang('app.canceled')
                                    </option>
                                    <option
                                            @if($project->status == 'completed') selected @endif
                                    value="completed">@lang('app.completed')
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h3 class="box-title m-b-10">@lang('modules.projects.salesInfo')</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">@lang('modules.projects.salesPrice')</label>
                                <div class="col-md-12 input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-usd" aria-hidden="true"></i>
                                    </div>
                                    <input type="number" min="0.01" step="0.01" class="form-control" name="sales_price" value="{{ $project->sales_price }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">@lang('modules.projects.soldDate')</label>
                                <input type="text" name="sold_date" id="sold_date" class="form-control" autocomplete="off" value="{{ $project->sold_date ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <h3 class="box-title m-b-30">@lang('modules.projects.clientInfo')</h3>
                    <div class="row">
                        <div class="col-xs-12 ">
                            <div class="form-group">
                                <label class="control-label">@lang('modules.projects.selectClient')</label>
                                <select class="select2 form-control" name="client_id" id="client_id" data-placeholder="@lang('modules.projects.selectClient')"
                                        data-style="form-control">
                                    <option value=""></option>
                                    @foreach($clients as $client)
                                        <option @if($project->client_id == $client->id) selected @endif value="{{ $client->id }}">{{ ucwords($client->first_name). ' ' . ucwords($client->last_name) }}
                                            @if($client->company_name != '') {{ '('.$client->company_name.')' }} @endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <h3 class="box-title">@lang('modules.projects.designerInfo')</h3>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('modules.projects.selectDesigner')</label>
                                <select class="select2 form-control" data-placeholder="@lang('modules.projects.selectDesigner')" name="user_id" id="user_id" >
                                    <option value=""></option>
                                    @foreach($designers as $designer)
                                        <option @if($project->user_id == $designer->id) selected @endif
                                        value="{{ $designer->id }}">{{ ucwords($designer->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                                <div class="form-group">
                                    <button type="button" id="btn_appointment" class="btn btn-primary"> <i class="fa fa-calendar"></i> @lang('modules.lead.appointment_schedule')</button>
                                    <a href="{{ route('admin.all-tasks.create') }}" class="btn btn-primary"><i class="ti-layout-list-thumb"></i> @lang('modules.tasks.newTask')</a>
                                </div>
                            </div>
                    </div>
                    <div class="form-actions m-t-15">
                        <button type="submit" id="save-form" class="btn btn-success"><i
                                            class="fa fa-check"></i> @lang('app.update')</button>
                        <button type="reset" class="btn btn-default">@lang('app.reset')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- .row -->

{{--Ajax Modal--}}
<div class="modal fade bs-modal-md in" id="projectCategoryModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" id="modal-data-application">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <span class="caption-subject font-red-sunglo bold uppercase" id="modelHeading"></span>
            </div>
            <div class="modal-body">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
                <button type="button" class="btn blue">Save changes</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
{{--Ajax Modal Ends--}}
@endsection
 @push('footer-script')
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>

<script src="{{ asset('plugins/bower_components/ion-rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js') }}"></script>

<script>
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });
    checkTask();
    function checkTask()
    {
        var chVal = $('#client_view_task').is(":checked") ? true : false;
        if(chVal == true){
            $('#clientNotification').show();
        }
        else{
            $('#clientNotification').hide();
        }

    }
    @if($project->deadline == null)
        $('#deadlineBox').hide();
    @endif
    $('#without_deadline').click(function () {
        var check = $('#without_deadline').is(":checked") ? true : false;
        if(check == true){
            $('#deadlineBox').hide();
        }
        else{
            $('#deadlineBox').show();
        }
    });

    $("#start_date").datepicker({
        format: '{{ $global->date_picker_format }}',
        todayHighlight: true,
        autoclose: true,
    }).on('changeDate', function (selected) {
        $('#deadline').datepicker({
            format: '{{ $global->date_picker_format }}',
            autoclose: true,
            todayHighlight: true,
            startDate: minDate
        });
        var minDate = new Date(selected.date.valueOf());
        $('#deadline').datepicker("update", minDate);
        $('#deadline').datepicker('setStartDate', minDate);
    });

    $("#deadline").datepicker({
        format: '{{ $global->date_picker_format }}',
        autoclose: true
    }).on('changeDate', function (selected) {
        var maxDate = new Date(selected.date.valueOf());
        $('#start_date').datepicker('setEndDate', maxDate);
    });

    $("#install_start_date").datepicker({
        format: '{{ $global->date_picker_format }}',
        todayHighlight: true,
        autoclose: true,
    }).on('changeDate', function (selected) {
        $('#install_end_date').datepicker({
            format: '{{ $global->date_picker_format }}',
            autoclose: true,
            todayHighlight: true
        });
        var minDate = new Date(selected.date.valueOf());
        $('#install_end_date').datepicker("update", minDate);
        $('#install_end_date').datepicker('setStartDate', minDate);
    });

    $("#install_end_date").datepicker({
        format: '{{ $global->date_picker_format }}',
        autoclose: true
    }).on('changeDate', function (selected) {
        var maxDate = new Date(selected.date.valueOf());
        $('#install_start_date').datepicker('setEndDate', maxDate);
    });

    $("#sold_date").datepicker({
        format: '{{ $global->date_picker_format }}',
        todayHighlight: true,
        autoclose: true,
    });

    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('admin.projects.update', [$project->id])}}',
            container: '#updateProject',
            type: "POST",
            redirect: true,
            data: $('#updateProject').serialize()
        })
    });

    $('.summernote').summernote({
        height: 200,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false                 // set focus to editable area after initializing summernote
    });

    var completion = $('#completion_percent').val();

    $("#range_01").ionRangeSlider({
        grid: true,
        min: 0,
        max: 100,
        from: parseInt(completion),
        postfix: "%",
        onFinish: saveRangeData
    });

    var slider = $("#range_01").data("ionRangeSlider");

    $('#calculate-task-progress').change(function () {
        if($(this).is(':checked')){
            slider.update({"disable": true});
        }
        else{
            slider.update({"disable": false});
        }
    })

    function saveRangeData(data) {
        var percent = data.from;
        $('#completion_percent').val(percent);
    }

    $(':reset').on('click', function(evt) {
        evt.preventDefault()
        $form = $(evt.target).closest('form')
        $form[0].reset()
        $form.find('select').select2()
    });

    @if($project->calculate_task_progress == "true")
        slider.update({"disable": true});
    @endif

    $('#btn_appointment').click(function(){
        window.location.href= '{{ route("admin.events.project", [$project->id]) }}';
    });

    $('#btn_google_map').click(function(){
        if(!$('#address').val() && !$('#city').val()){
             $.toast({
                heading: 'Error',
                text: "Please input address fields.",
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'error',
                hideAfter: 3500
            });
            return false;
        }
        else{
            let address = $('#address').val();
            
            if($('#city').val()){
                address += ',' + $('#city').val();
            }
            if($('#state').val()){
                address += ',' + $('#state').val();
            }
            if($('#zip').val()){
                address += ',' + $('#zip').val();
            }
            
            url = encodeURI(address)
            window.open('https://www.google.com/maps/search/?api=1&query=' + url, '_blank');
        }
        
    });

</script>

<script>
    $('#updateProject').on('click', '#addProjectCategory', function () {
        var url = '{{ route('admin.projectCategory.create-cat')}}';
        $('#modelHeading').html('Manage Project Category');
        $.ajaxModal('#projectCategoryModal', url);
    })

</script>




@endpush