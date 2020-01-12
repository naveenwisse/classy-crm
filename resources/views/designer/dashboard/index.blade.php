@extends('layouts.designer-app')

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
                <li><a href="{{ route('designer.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
    <style>
        .col-in {
            padding: 0 20px !important;

        }

        .fc-event{
            font-size: 10px !important;
        }

        @media (min-width: 769px) {
            #wrapper .panel-wrapper{
                height: 500px;
                overflow-y: auto;
            }
        }

    </style>
@endpush

@section('content')

    <h1>This page will be on development stage!!!</h1>

    {{--<div class="row dashboard-stats">--}}
        {{--@if(in_array('projects',$modules))--}}
        {{--<div class="col-md-3 col-sm-6">--}}
            {{--<a href="{{ route('designer.projects.index') }}">--}}
                {{--<div class="white-box">--}}
                    {{--<div class="row">--}}
                        {{--<div class="col-xs-3">--}}
                            {{--<div>--}}
                                {{--<span class="bg-info-gradient"><i class="icon-layers"></i></span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="col-xs-9 text-right">--}}
                            {{--<span class="widget-title"> @lang('modules.dashboard.totalProjects')</span><br>--}}
                            {{--<span class="counter">{{ $totalProjects }}</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</a>--}}
        {{--</div>--}}
        {{--@endif--}}

        {{--@if(in_array('timelogs',$modules))--}}
        {{--<div class="col-md-3 col-sm-6">--}}
            {{--<a href="{{ route('designer.all-time-logs.index') }}">--}}
                {{--<div class="white-box">--}}
                    {{--<div class="row">--}}
                        {{--<div class="col-xs-3">--}}
                            {{--<div>--}}
                                {{--<span class="bg-warning-gradient"><i class="icon-clock"></i></span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="col-xs-9 text-right">--}}
                            {{--<span class="widget-title"> @lang('modules.dashboard.totalHoursLogged')</span><br>--}}
                            {{--<span class="counter">{{ $counts->totalHoursLogged }}</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</a>--}}
        {{--</div>--}}
        {{--@endif--}}

        {{--@if(in_array('tasks',$modules))--}}
        {{--<div class="col-md-3 col-sm-6">--}}
            {{--<a href="{{ route('designer.all-tasks.index') }}">--}}
                {{--<div class="white-box">--}}
                    {{--<div class="row">--}}
                        {{--<div class="col-xs-3">--}}
                            {{--<div>--}}
                                {{--<span class="bg-danger-gradient"><i class="ti-alert"></i></span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="col-xs-9 text-right">--}}
                            {{--<span class="widget-title"> @lang('modules.dashboard.totalPendingTasks')</span><br>--}}
                            {{--<span class="counter">{{ $counts->totalPendingTasks }}</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</a>--}}
        {{--</div>--}}

        {{--<div class="col-md-3 col-sm-6">--}}
            {{--<a href="{{ route('designer.all-tasks.index') }}">--}}
                {{--<div class="white-box">--}}
                    {{--<div class="row">--}}
                        {{--<div class="col-xs-3">--}}
                            {{--<div>--}}
                                {{--<span class="bg-success-gradient"><i class="ti-check-box"></i></span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="col-xs-9 text-right">--}}
                            {{--<span class="widget-title"> @lang('modules.dashboard.totalCompletedTasks')</span><br>--}}
                            {{--<span class="counter">{{ $counts->totalCompletedTasks }}</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</a>--}}
        {{--</div>--}}
        {{--@endif--}}

    {{--</div>--}}
    {{--<!-- .row -->--}}

    {{--<div class="row">--}}

        {{--@if(in_array('attendance',$modules))--}}
        {{--<div class="col-md-6">--}}
            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-heading">@lang('app.menu.attendance')</div>--}}
                {{--<div class="panel-wrapper collapse in">--}}
                    {{--<div class="panel-body">--}}
                        {{--@if (!isset($noClockIn))--}}
                            {{----}}
                            {{--@if(!$checkTodayHoliday)--}}
                                {{--@if($todayTotalClockin < $maxAttandenceInDay)--}}
                                    {{--<div class="col-xs-6">--}}
                                        {{--<h3>@lang('modules.attendance.clock_in')</h3>--}}
                                    {{--</div>--}}
                                    {{--<div class="col-xs-6">--}}
                                        {{--<h3>@lang('modules.attendance.clock_in') IP</h3>--}}
                                    {{--</div>--}}
                                    {{--<div class="col-xs-6">--}}
                                        {{--@if(is_null($currenntClockIn))--}}
                                            {{--{{ \Carbon\Carbon::now()->timezone($global->timezone)->format($global->time_format) }}--}}
                                        {{--@else--}}
                                            {{--{{ $currenntClockIn->clock_in_time->timezone($global->timezone)->format($global->time_format) }}--}}
                                        {{--@endif--}}
                                    {{--</div>--}}
                                    {{--<div class="col-xs-6">--}}
                                        {{--{{ $currenntClockIn->clock_in_ip ?? request()->ip() }}--}}
                                    {{--</div>--}}

                                    {{--@if(!is_null($currenntClockIn) && !is_null($currenntClockIn->clock_out_time))--}}
                                        {{--<div class="col-xs-6 m-t-20">--}}
                                            {{--<label for="">@lang('modules.attendance.clock_out')</label>--}}
                                            {{--<br>{{ $currenntClockIn->clock_out_time->timezone($global->timezone)->format($global->time_format) }}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-xs-6 m-t-20">--}}
                                            {{--<label for="">@lang('modules.attendance.clock_out') IP</label>--}}
                                            {{--<br>{{ $currenntClockIn->clock_out_ip }}--}}
                                        {{--</div>--}}
                                    {{--@endif--}}

                                    {{--<div class="col-xs-8 m-t-20">--}}
                                        {{--<label for="">@lang('modules.attendance.working_from')</label>--}}
                                        {{--@if(is_null($currenntClockIn))--}}
                                            {{--<input type="text" class="form-control" id="working_from" name="working_from">--}}
                                        {{--@else--}}
                                            {{--<br> {{ $currenntClockIn->working_from }}--}}
                                        {{--@endif--}}
                                    {{--</div>--}}

                                    {{--<div class="col-xs-4 m-t-20">--}}
                                        {{--<label class="m-t-30">&nbsp;</label>--}}
                                        {{--@if(is_null($currenntClockIn))--}}
                                            {{--<button class="btn btn-success btn-sm" id="clock-in">@lang('modules.attendance.clock_in')</button>--}}
                                        {{--@endif--}}
                                        {{--@if(!is_null($currenntClockIn) && is_null($currenntClockIn->clock_out_time))--}}
                                            {{--<button class="btn btn-danger btn-sm" id="clock-out">@lang('modules.attendance.clock_out')</button>--}}
                                        {{--@endif--}}
                                    {{--</div>--}}
                                {{--@else--}}
                                    {{--<div class="col-xs-12">--}}
                                        {{--<div class="alert alert-info">@lang('modules.attendance.maxColckIn')</div>--}}
                                    {{--</div>--}}
                                {{--@endif--}}
                            {{--@else--}}
                                {{--<div class="col-xs-12">--}}
                                    {{--<div class="alert alert-info alert-dismissable">--}}
                                        {{--<b>@lang('modules.dashboard.holidayCheck') {{ ucwords($checkTodayHoliday->occassion) }}.</b> </div>--}}
                                {{--</div>--}}
                            {{--@endif--}}
                        {{--@else--}}
                            {{--<div class="col-xs-12 text-center">--}}
                                {{--<h4><i class="ti-alert text-danger"></i></h4>--}}
                                {{--<h4>@lang('messages.officeTimeOver')</h4>--}}
                            {{--</div>--}}
                        {{--@endif--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--@endif--}}

        {{--@if(in_array('tasks',$modules))--}}
        {{--<div class="col-md-6">--}}
            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-heading">@lang('modules.dashboard.overdueTasks')</div>--}}
                {{--<div class="panel-wrapper collapse in">--}}
                    {{--<div class="panel-body">--}}
                        {{--<ul class="list-task list-group" data-role="tasklist">--}}
                            {{--<li class="list-group-item" data-role="task">--}}
                                {{--<strong>@lang('app.title')</strong> <span--}}
                                        {{--class="pull-right"><strong>@lang('app.dueDate')</strong></span>--}}
                            {{--</li>--}}
                            {{--@forelse($pendingTasks as $key=>$task)--}}
                                {{--<li class="list-group-item row" data-role="task">--}}
                                    {{--<div class="col-xs-8">--}}
                                        {{--{!! ($key+1).'. <a href="javascript:;" data-task-id="'.$task->id.'" class="show-task-detail">'.ucfirst($task->heading).'</a>' !!}--}}
                                        {{--@if(!is_null($task->project_id) && !is_null($task->project))--}}
                                            {{--<a href="{{ route('designer.projects.show', $task->project_id) }}"--}}
                                                {{--class="text-danger">{{ ucwords($task->project->project_name) }}</a>--}}
                                        {{--@endif--}}
                                    {{--</div>--}}
                                    {{--<label class="label label-danger pull-right col-xs-4">{{ $task->due_date->format($global->date_format) }}</label>--}}
                                {{--</li>--}}
                            {{--@empty--}}
                                {{--<li class="list-group-item" data-role="task">--}}
                                    {{--@lang('messages.noOpenTasks')--}}
                                {{--</li>--}}
                            {{--@endforelse--}}
                        {{--</ul>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--@endif--}}

    {{--</div>--}}

    {{--<div class="row" >--}}

        {{--@if(in_array('projects',$modules))--}}
        {{--<div class="col-md-6" id="project-timeline">--}}
            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-heading">@lang('modules.dashboard.projectActivityTimeline')</div>--}}
                {{--<div class="panel-wrapper collapse in">--}}
                    {{--<div class="panel-body">--}}
                        {{--<div class="steamline">--}}
                            {{--@foreach($projectActivities as $activity)--}}
                                {{--<div class="sl-item">--}}
                                    {{--<div class="sl-left"><i class="fa fa-circle text-info"></i>--}}
                                    {{--</div>--}}
                                    {{--<div class="sl-right">--}}
                                        {{--<div><h6><a href="{{ route('designer.projects.show', $activity->project_id) }}" class="text-danger">{{ ucwords($activity->project_name) }}:</a> {{ $activity->activity }}</h6> <span class="sl-date">{{ $activity->created_at->timezone($global->timezone)->diffForHumans() }}</span></div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--@endforeach--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--@endif--}}

        {{--@if(in_array('notices',$modules) && $user->can('view_notice'))--}}
        {{--<div class="col-md-6" id="notices-timeline">--}}
            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-heading">@lang('modules.module.noticeBoard')</div>--}}
                {{--<div class="panel-wrapper collapse in">--}}
                    {{--<div class="panel-body">--}}
                        {{--<div class="steamline">--}}
                            {{--@foreach($notices as $notice)--}}
                                {{--<div class="sl-item">--}}
                                    {{--<div class="sl-left"><i class="fa fa-circle text-info"></i>--}}
                                    {{--</div>--}}
                                    {{--<div class="sl-right">--}}
                                        {{--<div>--}}
                                            {{--<h6>--}}
                                                {{--<a href="javascript:showNoticeModal({{ $notice->id }});" class="text-danger">--}}
                                                    {{--{{ ucwords($notice->heading) }}--}}
                                                {{--</a>--}}
                                            {{--</h6>--}}
                                            {{--<span class="sl-date">--}}
                                                {{--{{ $notice->created_at->timezone($global->timezone)->diffForHumans() }}--}}
                                            {{--</span>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--@endforeach--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--@endif--}}

        {{--@if(in_array('employees',$modules))--}}
        {{--<div class="col-md-6">--}}
            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-heading">@lang('modules.dashboard.userActivityTimeline')</div>--}}
                {{--<div class="panel-wrapper collapse in">--}}
                    {{--<div class="panel-body">--}}
                        {{--<div class="steamline">--}}
                            {{--@forelse($userActivities as $key=>$activity)--}}
                                {{--<div class="sl-item">--}}
                                    {{--<div class="sl-left">--}}
                                        {{--{!!  ($activity->user->image) ? '<img src="'.asset_url('avatar/'.$activity->user->image).'"--}}
                                                                    {{--alt="user" class="img-circle">' : '<img src="'.asset('img/default-profile-2.png').'"--}}
                                                                    {{--alt="user" class="img-circle">' !!}--}}
                                    {{--</div>--}}
                                    {{--<div class="sl-right">--}}
                                        {{--<div class="m-l-40">--}}
                                            {{--@if($user->can('view_employees'))--}}
                                                {{--<a href="{{ route('designer.employees.show', $activity->user_id) }}" class="text-success">{{ ucwords($activity->user->name) }}</a>--}}
                                            {{--@else--}}
                                                {{--{{ ucwords($activity->user->name) }}--}}
                                            {{--@endif--}}
                                            {{--<span  class="sl-date">{{ $activity->created_at->timezone($global->timezone)->diffForHumans() }}</span>--}}
                                            {{--<p>{!! ucfirst($activity->activity) !!}</p>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--@if(count($userActivities) > ($key+1))--}}
                                    {{--<hr>--}}
                                {{--@endif--}}
                            {{--@empty--}}
                                {{--<div>@lang('messages.noActivityByThisUser')</div>--}}
                            {{--@endforelse--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--@endif--}}



    {{--</div>--}}

@endsection

@push('footer-script')
<script>
    $('#clock-in').click(function () {
        var workingFrom = $('#working_from').val();

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: '{{route('designer.attendances.store')}}',
            type: "POST",
            data: {
                working_from: workingFrom,
                _token: token
            },
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    })

    @if(!is_null($currenntClockIn))
    $('#clock-out').click(function () {

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: '{{route('designer.attendances.update', $currenntClockIn->id)}}',
            type: "PUT",
            data: {
                _token: token
            },
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    })
    @endif

    function showNoticeModal(id) {
        var url = '{{ route('designer.notices.show', ':id') }}';
        url = url.replace(':id', id);
        $.ajaxModal('#projectTimerModal', url);
    }

    $('.show-task-detail').click(function () {
            $(".right-sidebar").slideDown(50).addClass("shw-rside");

            var id = $(this).data('task-id');
            var url = "{{ route('designer.all-tasks.show',':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'GET',
                url: url,
                success: function (response) {
                    if (response.status == "success") {
                        $('#right-sidebar-content').html(response.view);
                    }
                }
            });
        })

</script>
@endpush
