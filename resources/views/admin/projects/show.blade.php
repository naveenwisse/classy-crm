@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }} #{{ $project->id }} - <span class="font-bold">{{ ucwords($project->project_name) }}</span></h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('admin.projects.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('modules.projects.overview')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/icheck/skins/all.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/multiselect/css/multi-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<style>
    #section-line-1 .col-in{
        padding:0 10px;
    }

    #section-line-1 .col-in h3{
        font-size: 15px;
    }
</style>
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <section>
                <div class="sttabs tabs-style-line">
{{--                    <div class="white-box">--}}
{{--                        <nav>--}}
{{--                            <ul>--}}
{{--                                <li class="tab-current"><a href="{{ route('admin.projects.show', $project->id) }}"><span>@lang('modules.projects.overview')</span></a>--}}
{{--                                </li>--}}

{{--                                @if(in_array('tasks',$modules))--}}
{{--                                <li><a href="{{ route('admin.tasks.show', $project->id) }}"><span>@lang('app.menu.tasks')</span></a></li>--}}
{{--                                @endif--}}
{{--                            </ul>--}}
{{--                        </nav>--}}
{{--                    </div>--}}
                    <div class="content-wrap">
                        <section id="section-line-1" class="show">
                            <div class="row">

                                <div  class="col-md-12">
                                    <div class="white-box">
                                        <h3 class="b-b">Project #{{ $project->id }} - <span
                                                    class="font-bold">{{ ucwords($project->project_name) }}</span>
                                        </h3>
                                    </div>
                                </div>


                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
{{--                                        client details--}}
                                        <div class="col-md-6">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">@lang('modules.projects.projectInfo')</div>
                                                <div class="panel-wrapper collapse in">
                                                    <div class="panel-body">
                                                        <dl>
                                                            <dt>@lang('modules.projects.projectName')</dt>
                                                            <dd class="m-b-10">{{ $project->project_name }}</dd>
                                                            <dt>@lang('modules.projects.address')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->address) }}</dd>
                                                            <dt>@lang('modules.projects.city')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->city) }}</dd>
                                                            <dt>@lang('modules.projects.state')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->state) }}</dd>
                                                            <dt>@lang('modules.projects.zip')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->zip) }}</dd>
                                                            <dt>@lang('modules.projects.contact')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->contact) }}</dd>
                                                            <dt>@lang('modules.projects.phone')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->phone) }}</dd>
                                                            <dt>@lang('modules.projects.cell')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->cell) }}</dd>
                                                            <dt>@lang('modules.projects.fax')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->fax) }}</dd>
                                                            <dt>@lang('modules.projects.email')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->email) }}</dd>
                                                            <dt>@lang('app.status')</dt>
                                                            <dd class="m-b-10">{{ ucwords($project->status) }}</dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

{{--                                         project members --}}
                                        <div class="col-md-6">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">@lang('modules.projects.members')</div>
                                                <div class="panel-wrapper collapse in">
                                                    <div class="panel-body">
                                                        <div class="message-center">
                                                            @if($project->client_id)
                                                                <dt>@lang('modules.projects.clientInfo')</dt>
                                                                <a href="#">
                                                                    <div class="mail-contnet">
                                                                        <h5>{{ ucwords($project->client->full_name) }}</h5>
                                                                        <span class="mail-desc">{{ $project->client->email }}</span>
                                                                    </div>
                                                                </a>
                                                            @endif
                                                            @if($project->user_id)
                                                                <dt>@lang('modules.projects.designerInfo')</dt>
                                                                <a href="#">
                                                                    <div class="mail-contnet">
                                                                        <h5>{{ ucwords($project->designer->name) }}</h5>
                                                                        <span class="mail-desc">{{ $project->designer->email }}</span>
                                                                    </div>
                                                                </a>
                                                            @endif
                                                            @if(!$project->client_id && !$project->user_id)
                                                                @lang('messages.noMemberAddedToProject')
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- project members --}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="panel panel-default">--}}
{{--                                                <div class="panel-heading">@lang('modules.projects.openTasks')</div>--}}
{{--                                                <div class="panel-wrapper collapse in">--}}
{{--                                                    <div class="panel-body">--}}
{{--                                                        <ul class="list-task list-group" data-role="tasklist">--}}
{{--                                                            @forelse($openTasks as $key=>$task)--}}
{{--                                                            <li class="list-group-item" data-role="task">--}}
{{--                                                                {{ ($key+1).'. '.ucfirst($task->heading) }} <label--}}
{{--                                                                        class="label label-success pull-right">{{ $task->due_date->format('d M') }}</label>--}}
{{--                                                            </li>--}}
{{--                                                            @empty--}}
{{--                                                                <li class="list-group-item" data-role="task">--}}
{{--                                                                    @lang('modules.projects.noOpenTasks')--}}
{{--                                                                </li>--}}
{{--                                                            @endforelse--}}
{{--                                                        </ul>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div><!-- /content -->
                </div><!-- /tabs -->
            </section>
        </div>


    </div>
    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('js/cbpFWTabs.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
//    (function () {
//
//        [].slice.call(document.querySelectorAll('.sttabs')).forEach(function (el) {
//            new CBPFWTabs(el);
//        });
//
//    })();

    $('#timer-list').on('click', '.stop-timer', function () {
       var id = $(this).data('time-id');
        var url = '{{route("admin.all-time-logs.stopTimer", ":id")}}';
        url = url.replace(':id', id);
        var token = '{{ csrf_token() }}'
        $.easyAjax({
            url: url,
            type: "POST",
            data: {timeId: id, _token: token},
            success: function (data) {
                $('#timer-list').html(data.html);
            }
        })

    });

</script>
@endpush
