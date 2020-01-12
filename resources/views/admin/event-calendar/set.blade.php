@extends('layouts.app')
<style>
    .datepicker-days table td.disabled, .datepicker-days table td.disabled:hover {
        background: rgba(255, 255, 255, 0.6) !important;
        color: #eeeeee !important;
        cursor: not-allowed !important;
    }
</style>
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
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<!-- <link rel="stylesheet" href="{{ asset('plugins/bower_components/calendar/dist/fullcalendar.css') }}"> -->
<link rel="stylesheet" href="{{ asset('plugins/full-calendar/packages/core/main.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/full-calendar/packages/daygrid/main.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/full-calendar/packages/timegrid/main.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/context-menu/jquery.contextMenu.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.css') }}">

<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/multiselect/css/multi-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <h3 class="box-title col-md-3">@lang('app.menu.appointments')</h3>
                </div>
                <div class="col-md-12">
                    <div class="col-md-4 col-sm-6" >
                        <label class="control-label">Select Date</label>
                        <div class="col-md-12 input-group date" data-provide="datepicker"  id="select_datepicker">
                            <input type="text" class="form-control">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-4 col-sm-6" >
                        <label class="control-label">Scheduled Date</label>
                        <div class="col-md-12 input-group date" data-provide="datepicker"  id="scheduled_datepicker">
                            <input type="text" class="form-control">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="white-box">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    <!-- .row -->

    <!-- BEGIN MODAL -->
    <div class="modal fade bs-modal-md in" id="my-event" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-data-application">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="icon-plus"></i> @lang('modules.appointments.addAppointment')</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open(['id'=>'createEvent','class'=>'ajax-form','method'=>'POST']) !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6 ">
                                <div class="form-group">
                                    <label>@lang('modules.appointments.appointmentName')</label>
                                    <input type="text" name="event_name" id="event_name" class="form-control" value="{{ $apptName }}">
                                </div>
                            </div>

                            <div class="col-md-2 ">
                                <div class="form-group">
                                    <label>@lang('modules.sticky.colors')</label>
                                    <select id="colorselector" name="label_color">
                                        <option value="bg-info" data-color="#5475ed">Blue</option>
                                        <option value="bg-purple" data-color="#ab8ce4">Purple</option>
                                        <option value="bg-inverse" data-color="#4c5667">Grey</option>
                                        <option value="bg-warning" data-color="#f1c411">Yellow</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 ">
                                <div class="form-group">
                                    <label>@lang('app.description')</label>
                                    <textarea name="description" id="description" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-3 ">
                                <div class="form-group">
                                    <label>@lang('modules.appointments.startOn')</label>
                                    <input type="text" name="start_date" id="start_date" class="form-control" autocomplete="none">
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="input-group bootstrap-timepicker timepicker">
                                    <label>&nbsp;</label>
                                    <input type="text" name="start_time" id="start_time"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>@lang('modules.appointments.endOn')</label>
                                    <input type="text" name="end_date" id="end_date" class="form-control" autocomplete="none">
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="input-group bootstrap-timepicker timepicker">
                                    <label>&nbsp;</label>
                                    <input type="text" name="end_time" id="end_time"
                                           class="form-control">
                                </div>
                            </div>
                        </div>
                        @if(isset($project) && $project)
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                        <label class="control-label">@lang('modules.module.projects')</label>
                                        <select class="select2 form-control" data-placeholder="@lang('modules.timeLogs.selectProject')" name="project_id" id="project_id" >
                                            <option value="{{ $project->id }}" selected>{{ ucwords($project->project_name) }}</option>
                                        </select>
                                    </div>
                            </div>
                        </div>

                        <div class="row" id="project_detail">
                            <h5 class="box-title m-l-10">@lang('modules.events.projectInfo')</h5>
                            <div class="col-xs-12">
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.lead.clientName')</label>
                                        <input type="text" name="project_client_name" id="project_client_name" class="form-control" autocomplete="none" value="{{ isset($project->client->full_name) ? $project->client->full_name : ''}}" disabled="disabled">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.projects.phone')</label>
                                        <input type="text" name="project_phone" id="project_phone" class="form-control" autocomplete="none" value="{{ $project->phone }}" disabled="disabled">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.projects.email')</label>
                                        <input type="text" name="project_email" id="project_email" class="form-control" autocomplete="none" value="{{ $project->email }}" disabled="disabled">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.tasks.assignTo')</label>
                                    <select class="select2 form-control"
                                            data-placeholder="@lang('modules.tasks.chooseAssignee')" name="user_id" id="user_id">
                                        @foreach($designers as $designer)
                                            <option @if ($project->user_id == $designer->id) selected @endif value="{{ $designer->id }}">{{ ucwords($designer->name) }} @if($designer->id == $user->id)
                                                    (YOU) @endif</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>
                        @endif
                        @if(isset($lead) && $lead)
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.module.leads')</label>
                                    <select class="select2 form-control" data-placeholder="@lang('modules.events.selectLead')" name="lead_id" id="lead_id" >
                                        <option value="{{ $lead->id }}" selected>{{ $lead->full_name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" id="lead_detail">
                            <h5 class="box-title m-l-10">@lang('modules.events.leadInfo')</h5>
                            <div class="col-xs-12">
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.lead.clientName')</label>
                                        <input type="text" name="lead_client_name" id="lead_client_name" class="form-control" autocomplete="none" value="{{ isset($lead->client->full_name) ? $lead->client->full_name : ''}}" disabled="disabled">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.lead.phone')</label>
                                        <input type="text" name="lead_phone" id="lead_phone" class="form-control" autocomplete="none" value="{{ $lead->phone }}" disabled="disabled">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.lead.email')</label>
                                        <input type="text" name="lead_email" id="lead_email" class="form-control" autocomplete="none" value="{{ $lead->email }}" disabled="disabled">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.tasks.assignTo')</label>
                                    <select class="select2 form-control"
                                            data-placeholder="@lang('modules.tasks.chooseAssignee')" name="user_id" id="user_id">
                                        @foreach($designers as $designer)
                                            <option @if ($lead->user_id == $designer->id) selected @endif value="{{ $designer->id }}">{{ ucwords($designer->name) }} @if($designer->id == $user->id)
                                                    (YOU) @endif</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <div class="checkbox checkbox-info">
                                        <input id="send_reminder" name="send_reminder" value="yes"
                                                type="checkbox">
                                        <label for="send_reminder">@lang('modules.tasks.reminder')</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="reminder-fields" style="display: none;">
                            <div class="col-xs-6 col-md-3">
                                <div class="form-group">
                                    <label>@lang('modules.appointments.remindBefore')</label>
                                    <input type="number" min="1" value="1" name="remind_time" class="form-control">
                                </div>
                            </div>
                            <div class="col-xs-6 col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <select name="remind_type" id="" class="form-control">
                                        <option value="day">@lang('app.day')</option>
                                        <option value="hour">@lang('app.hour')</option>
                                        <option value="minute">@lang('app.minute')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="event_type" id="event_type">
                    {!! Form::close() !!}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">@lang('app.close')</button>
                    <button type="button" class="btn btn-success save-event waves-effect waves-light">@lang('app.submit')</button>
                </div>
            </div>
        </div>
    </div>

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="eventDetailModal" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-data-application">
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

@if(isset($lead) && $lead)
<script type="text/javascript"> var apptType = 'lead'; </script>
@else
<script type="text/javascript"> var apptType = 'project'; </script>
@endif

<script>
    var resources = [
        @foreach($designers as $designer)
        {
            id: '{{ ucfirst($designer->id) }}',
            title: '{{ ucfirst($designer->name) }}'
        },
        @endforeach
    ];
    var taskEvents = [
        @foreach($events as $event)
        {
            id: '{{ ucfirst($event->id) }}',
            resourceId: '{{ $event->attendee->user_id }}',
            title: '{{ ucfirst($event->event_name) }}',
            start: '{{ $event->start_date_time }}',
            end:  '{{ $event->end_date_time }}',
            className: '{{ $event->label_color }}'
        },
        @endforeach
    ];

    var enableDays = [];
    let schedule_day = '';
    @foreach($events as $event)
        schedule_day = '{{ date('m/d/Y', strtotime($event->start_date_time)) }}';
        if(enableDays.indexOf(schedule_day) < 0){
            enableDays.push(schedule_day);
        }
    @endforeach


    var getEventDetail = function (id) {
        var url = '{{ route('admin.events.show', ':id')}}';
        url = url.replace(':id', id);

        $('#modelHeading').html('Appointment');
        $.ajaxModal('#eventDetailModal', url);
    }

    var getEventEdit = function (id) {
        var url = '{{ route('admin.events.edit', ':id')}}';
        url = url.replace(':id', id);

        $('#modelHeading').html('Appointment');
        $.ajaxModal('#eventDetailModal', url);
    }

    var calendarLocale = '{{ $global->locale }}';
</script>

<script src="{{ asset('plugins/bower_components/calendar/jquery-ui.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
<script src="{{ asset('plugins/context-menu/jquery.ui.position.min.js') }}"></script>
<script src="{{ asset('plugins/context-menu/jquery.contextMenu.min.js') }}"></script>
<script src="{{ asset('plugins/full-calendar/packages/core/main.js') }}"></script>
<script src="{{ asset('plugins/full-calendar/packages/interaction/main.js') }}"></script>
<script src="{{ asset('plugins/full-calendar/packages/daygrid/main.js') }}"></script>
<script src="{{ asset('plugins/full-calendar/packages/timegrid/main.js') }}"></script>
<script src="{{ asset('plugins/full-calendar/packages-premium/resource-common/main.js') }}"></script>
<script src="{{ asset('plugins/full-calendar/packages-premium/resource-daygrid/main.js') }}"></script>
<script src="{{ asset('plugins/full-calendar/packages-premium/resource-timegrid/main.js') }}"></script>
<!-- <script src="{{ asset('plugins/bower_components/calendar/dist/fullcalendar.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/calendar/dist/jquery.fullcalendar.js') }}"></script> -->
<!-- <script src="{{ asset('plugins/bower_components/calendar/dist/locale-all.js') }}"></script> -->
<script src="{{ asset('js/event-calendar.js') }}"></script>

<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js') }}"></script>

<script src="{{ asset('js/cbpFWTabs.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.js') }}"></script>

<script>
    jQuery('#start_date, #end_date').datepicker({
        autoclose: true,
        todayHighlight: true
    });

    function formatDate(d) {
        let day = String(d.getDate());
        if (day.length === 1)
            day = '0' + day;
        let month = String((d.getMonth()+1));
        if (month.length === 1)
            month = '0' + month;
        return month + "/" + day + "/" + d.getFullYear()
    }

    $('#colorselector').colorselector();
    $('#select_datepicker').datepicker("setDate", new Date());
    $('#select_datepicker').datepicker().on('changeDate', function (e) {
        let dateStr = getDateStr(e.date);
        $.CalendarApp.$calendarObj.changeView('resourceTimeGridDay', dateStr);
    });

    $("#scheduled_datepicker").datepicker({
        beforeShowDay: function(date){
            if (enableDays.indexOf(formatDate(date)) < 0) {
                console.log(formatDate(date));
                return {
                    enabled: false
                }
            }
            else{
                return {
                    enabled: true
                }
            }

        },
        todayHighlight: false,
        format: "dd-mm-yyyy",
        autoclose: true
    });

    $('#scheduled_datepicker').datepicker().on('changeDate', function (e) {
        let dateStr = getDateStr(e.date);
        $('#select_datepicker').datepicker("setDate", e.date);
        $.CalendarApp.$calendarObj.changeView('resourceTimeGridDay', dateStr);
    });
    
    $('#start_time, #end_time').timepicker();

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    function getDateStr(date){
        let curr_date = date.getDate();
        if(curr_date < 10){
            curr_date = '0'+curr_date;
        }
        let curr_month = date.getMonth();
        curr_month = curr_month+1;
        if(curr_month < 10){
            curr_month = '0'+curr_month;
        }
        let curr_year = date.getFullYear();
        return curr_year + '-' + curr_month + '-' + curr_date;
    }

    function formatAMPM(date) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        let ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;
        let strTime = hours + ':' + minutes + ' ' + ampm;
        return strTime;
    }

    function addEventModal(start, end, allDay, resource_id, type){
        $('#createEvent div.has-error').removeClass('has-error');
        $('#createEvent div.help-block').remove();
        if(start){
            var curr_date = start.getDate();
            if(curr_date < 10){
                curr_date = '0'+curr_date;
            }
            var curr_month = start.getMonth();
            curr_month = curr_month+1;
            if(curr_month < 10){
                curr_month = '0'+curr_month;
            }
            var curr_year = start.getFullYear();
            
            $('#start_date').val(curr_month+'/'+curr_date+'/'+curr_year);
            $('#start_time').val(formatAMPM(start));

            var curr_date = end.getDate();
            if(curr_date < 10){
                curr_date = '0'+curr_date;
            }
            var curr_month = end.getMonth();
            curr_month = curr_month+1;
            if(curr_month < 10){
                curr_month = '0'+curr_month;
            }
            var curr_year = end.getFullYear();
            $('#end_date').val(curr_month+'/'+curr_date+'/'+curr_year);
            $('#end_time').val(formatAMPM(end));

            $('#start_date, #end_date').datepicker('destroy');
            jQuery('#start_date, #end_date').datepicker({
                autoclose: true,
                todayHighlight: true
            })
        }

        if(resource_id){
            $('#user_id').val(resource_id).trigger('change');
            $('#user_id').select2("readonly", true);
        }
        if(type){
            $('#event_type').val(type);
            if(type === 1){
                $('#colorselector').colorselector('setValue','bg-info');
                $('#project_id').parent().parent().parent().hide();
                $('#event_name').parent().parent().show();
                $('#lead_id').parent().parent().parent().show();
                $('#lead_detail').show();
                $('#project_detail').hide();
                $('#user_id').parent().parent().parent().show();
                $('#send_reminder').parent().parent().parent().parent().show();
            }
            else if(type === 2){
                $('#colorselector').colorselector('setValue','bg-purple');
                $('#lead_id').parent().parent().parent().hide();
                $('#event_name').parent().parent().show();
                $('#project_id').parent().parent().parent().show();
                $('#lead_detail').hide();
                $('#project_detail').show();
                $('#user_id').parent().parent().parent().show();
                $('#send_reminder').parent().parent().parent().parent().show();
            }
            else if(type === 3){
                $('#colorselector').colorselector('setValue','bg-inverse');
                $('#event_name').parent().parent().hide();
                $('#project_id').parent().parent().parent().hide();
                $('#lead_id').parent().parent().parent().hide();
                $('#lead_detail').hide();
                $('#project_detail').hide();
                $('#user_id').parent().parent().parent().hide();
                $('#send_reminder').parent().parent().parent().parent().hide();
            }
            else if(type === 4){
                $('#colorselector').colorselector('setValue','bg-warning');
                $('#event_name').parent().parent().hide();
                $('#project_id').parent().parent().parent().hide();
                $('#lead_id').parent().parent().parent().hide();
                $('#lead_detail').hide();
                $('#project_detail').hide();
                $('#user_id').parent().parent().parent().hide();
                $('#send_reminder').parent().parent().parent().parent().hide();
            }
            // $('#colorselector').colorselector('setReadonly', true);
            $('.btn-colorselector').parent().prop('disabled', true);
        }

        $('#my-event').modal('show');

    }

    function createPDF(id){
        var url = '{{route("admin.events.create-pdf", ":id")}}';
        url = url.replace(':id', id);
        window.location.href = url;
    }

    $('.save-event').click(function () {
        $.easyAjax({
            url: '{{route("admin.events.store")}}',
            container: '#modal-data-application',
            type: "POST",
            data: $('#createEvent').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.href = '{{ route('admin.events.index') }}';
                }
            }
        })
    })

    $('#lead_id').change(function () {
        let lead_id = $(this).val();
        var url = '{{route("admin.leads.getdetail", ":id")}}';
        url = url.replace(':id', lead_id);
        var token = "{{ csrf_token() }}";
        $.easyAjax({
            url: url,
            container: '#modal-data-application',
            type: "POST",
            data: { _token : token },
            success: function (response) {
                if(response.status == 'success'){
                    $('#lead_client_name').val(response.data.client_name);
                    $('#lead_phone').val(response.data.phone);
                    $('#lead_email').val(response.data.email);
                }
            }
        })
    });

    $('#project_id').change(function () {
        let project_id = $(this).val();
        var url = '{{route("admin.projects.getdetail", ":id")}}';
        url = url.replace(':id', project_id);
        var token = "{{ csrf_token() }}";
        $.easyAjax({
            url: url,
            container: '#modal-data-application',
            type: "POST",
            data: { _token : token },
            success: function (response) {
                if(response.status == 'success'){
                    $('#project_client_name').val(response.data.client_name);
                    $('#project_phone').val(response.data.phone);
                    $('#project_email').val(response.data.email);
                }
            }
        })
    });


    $('#repeat-event').change(function () {
        if($(this).is(':checked')){
            $('#repeat-fields').show();
        }
        else{
            $('#repeat-fields').hide();
        }
    });

    $('#send_reminder').change(function () {
        if($(this).is(':checked')){
            $('#reminder-fields').show();
        }
        else{
            $('#reminder-fields').hide();
        }
    });

    $(document).ready(function(){
        $.contextMenu({
            // define which elements trigger this menu
            selector: "a.fc-event",
            // define the elements of the menu
            items: {
                edit: {
                    name: "Edit", 
                    callback: function(key, opt){ 
                        let event_id = $(this).data('event-id');
                        getEventEdit(event_id); 
                    }
                },
                download : {
                    name : "Download",
                    visible: function(key, opt){
                        // Hide this item if the menu was triggered on a div
                        let event_type = $(this).data('event-type');
                        if(event_type == 1 || event_type == 2){
                            return true;
                        }
                        return false;
                    },
                    callback: function(key, opt){
                        let event_id = $(this).data('event-id');
                        createPDF(event_id);
                    }
                },
                delete: {
                    name: "Delete", 
                    callback: function(key, opt){ 
                        var event_id = $(this).data('event-id');
                        swal({
                            title: "Are you sure?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes",
                            cancelButtonText: "Cancel",
                            closeOnConfirm: true,
                            closeOnCancel: true
                        }, function(isConfirm){
                            if (isConfirm) {
                                var url = "/events/:id";
                                url = url.replace(':id', event_id);
                                var token = $('#createEvent [name="_token"]').val();

                                $.easyAjax({
                                    type: 'POST',
                                    url: url,
                                    data: {'_token': token, '_method': 'DELETE'},
                                    success: function (response) {
                                        if (response.status == "success") {
                                            window.location.reload();
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
            }
            // there's more, have a look at the demos and docs...
        });

        if(apptType === 'lead'){
            $.contextMenu({
                selector: ".fc-resourceTimeGridDay-view .fc-highlight",
                items: {
                    first: {
                        name: "Schedule First Appt", 
                        callback: function(key, opt){
                            addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 1);
                        }
                    },
                    blocked: {
                        name: "Schedule Blocked Time", 
                        callback: function(key, opt){ 
                            addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 3); 
                        }
                    },
                    personal: {
                        name: "Schedule Personal Time Off", 
                        callback: function(key, opt){ 
                            addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 4); 
                        }
                    }
                }
                // there's more, have a look at the demos and docs...
            });
        }
        else{
            $.contextMenu({
                selector: ".fc-resourceTimeGridDay-view .fc-highlight",
                items: {
                    follow_up: {
                        name: "Schedule Follow-up Appt", 
                        callback: function(key, opt){
                            addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 2);
                        }
                    },
                    blocked: {
                        name: "Schedule Blocked Time", 
                        callback: function(key, opt){ 
                            addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 3); 
                        }
                    },
                    personal: {
                        name: "Schedule Personal Time Off", 
                        callback: function(key, opt){ 
                            addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 4); 
                        }
                    }
                }
                // there's more, have a look at the demos and docs...
            });
        }
        
    });

</script>

@endpush
