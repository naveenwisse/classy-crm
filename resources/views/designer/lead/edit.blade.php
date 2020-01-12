@extends('layouts.designer-app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('designer.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('designer.leads.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.edit')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.lead.updateTitle')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'updateLead','class'=>'ajax-form','method'=>'PUT']) !!}
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.lead.companyName')</label>
                                        <input type="text" id="company_name" name="company_name" class="form-control" value="{{ $lead->company_name ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-6 no-padding-left">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.first_name')</label>
                                                <input type="text" id="first_name" name="first_name" class="form-control" value="{{ $lead->first_name ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 no-padding">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.last_name')</label>
                                                <input type="text" id="last_name" name="last_name" class="form-control" value="{{ $lead->last_name ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.address')</label>
                                        <input type="text" name="address"  id="address" class="form-control" value="{{ $lead->address ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.lead.city')</label>
                                        <input type="text" name="city"  id="city" class="form-control" value="{{ $lead->city ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-6 no-padding-left">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.state')</label>
                                                <select class="form-control" id="state" name="state">
                                                    @forelse($states as $key => $value)
                                                        <option @if($lead->states == $key) selected
                                                                @endif value="{{ $key }}"> {{ $value }}</option>
                                                    @empty

                                                    @endforelse
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 no-padding">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.zip')</label>
                                                <input type="text" name="zip"  id="zip" class="form-control" value="{{ $lead->zip ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('app.status')</label>
                                        <select name="status" id="status" class="form-control">
                                            @forelse($status as $sts)
                                                <option @if($lead->status_id == $sts->id) selected
                                                        @endif value="{{ $sts->id }}"> {{ $sts->type }}</option>
                                            @empty

                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <div class="col-md-8 no-padding-left">
                                            <div class="form-group">
                                                <label>@lang('modules.lead.phone')</label>
                                                <input type="tel" name="phone" id="phone" class="form-control" value="{{ $lead->phone ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4 no-padding">
                                            <div class="form-group">
                                                <label>@lang('modules.lead.ext')</label>
                                                <input type="text" name="ext" id="ext" class="form-control" value="{{ $lead->ext ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.cell')</label>
                                        <input type="tel" name="cell" id="cell" class="form-control" value="{{ $lead->cell ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.fax')</label>
                                        <input type="tel" name="fax" id="fax" class="form-control" value="{{ $lead->fax ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.email')</label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ $lead->email ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.ref')</label>
                                        <input type="text" name="ref" id="ref" class="form-control" value="{{ $lead->ref ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="save-form" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.update')</button>
                            <a href="{{ route('designer.leads.index') }}" class="btn btn-default">@lang('app.back')</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script>
    $(".date-picker").datepicker({
        todayHighlight: true,
        autoclose: true
    });
    
    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('designer.leads.update', [$lead->id])}}',
            container: '#updateLead',
            type: "POST",
            redirect: true,
            data: $('#updateLead').serialize()
        })
    });
</script>
@endpush