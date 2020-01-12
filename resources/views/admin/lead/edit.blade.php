@extends('layouts.app')

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
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('admin.leads.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.edit')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>

@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.lead.updateTitle')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'updateLead','class'=>'ajax-form','method'=>'PUT']) !!}
                        <input type="hidden" id="note_lead_id" value="{{$lead->id}}">
                        <div class="form-body">
                            <h3 class="box-title">@lang('modules.lead.lead_contract_info')</h3>
                            <hr>
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
                                        <label class="control-label">@lang('app.address') 1</label>
                                        <input type="text" name="address1"  id="address1" class="form-control" value="{{ $lead->address1 ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.address') 2</label>
                                        <input type="text" name="address2"  id="address2" class="form-control" value="{{ $lead->address2 ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.lead.city')</label>
                                        <input type="text" name="city"  id="city" class="form-control" value="{{ $lead->city ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-6 no-padding-left">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.state')</label>
                                                <select class="form-control selectpicker" id="state" name="state" data-style="form-control select-with-transition">
                                                    @forelse($states as $key => $value)
                                                        <option @if($lead->state == $key) selected
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
                                    <div class="col-md-12 text-center">
                                        <div class="form-group">
                                            <button type="button" id="btn_google_map" class="btn btn-primary"> <i class="fa fa-globe" aria-hidden="true"></i> Google Map</button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('app.status')</label>
                                        <select name="status" id="status" class="form-control selectpicker" data-style="form-control">
                                            @forelse($status as $sts)
                                                <option @if($lead->status_id == $sts->id) selected
                                                        @endif value="{{ $sts->id }}"> {{ $sts->type }}</option>
                                            @empty

                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.source')
                                            <a href="javascript:;"
                                               id="createLeadSource"
                                               class="btn btn-sm btn-outline btn-success">
                                                <i class="fa fa-plus"></i> @lang('modules.lead.addNewLeadSource')
                                            </a>
                                        </label>
                                        <select name="source" id="source" class="form-control selectpicker" data-style="form-control">

                                            @forelse($sources as $source)
                                                <option @if($lead->source_id == $source->id) selected @endif value="{{ $source->id }}"> {{ $source->name }}</option>
                                            @empty
                                                <option value="">@lang('messages.noLeadSourceAdded')</option>
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.interest_area.area_of_interest')
                                            <a href="javascript:;"
                                               id="createInterestArea"
                                               class="btn btn-sm btn-outline btn-success">
                                                <i class="fa fa-plus"></i> @lang('modules.interest_area.addNewInterestArea')
                                            </a>
                                        </label>
                                        <select class="select2 select2-multiple" data-placeholder="@lang('modules.interest_area.chooseArea')" name="interest_area[]" id="interest_area_id[]" multiple="multiple">
                                            <option value=""></option>
                                            <?php if($lead->interest_areas != null )
                                                    $selected_areas = explode(',', $lead->interest_areas);
                                                  else{
                                                      $selected_areas = [];
                                                  }
                                            ?>
                                            @foreach($areas as $area)
                                                <option @if(in_array($area->id, $selected_areas)) selected @endif value="{{ $area->id }}">{{ ucwords($area->type) }}</option>
                                            @endforeach
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
                        <h3 class="box-title m-t-30">@lang('modules.lead.projectInfo')</h3>
                        <hr>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label class="control-label">@lang('app.address') 1</label>
                                    <input type="text" name="pl_address1"  id="pl_address1" class="form-control" value="{{ $lead->project_location->address1 ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">@lang('app.address') 2</label>
                                    <input type="text" name="pl_address2"  id="pl_address2" class="form-control" value="{{ $lead->project_location->address2 ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.lead.city')</label>
                                    <input type="text" name="pl_city"  id="pl_city" class="form-control" value="{{ $lead->project_location->city ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <div class="col-md-6 no-padding-left">
                                        <div class="form-group">
                                            <label class="control-label">@lang('modules.lead.state')</label>
                                            <select class="form-control selectpicker" id="pl_state" name="pl_state" data-style="form-control">
                                                @foreach($states as $key => $value)
                                                    <option @if(isset($lead->project_location->state) && $lead->project_location->state == $key) selected @endif value="{{ $key }}">{{ucfirst($value)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 no-padding">
                                        <div class="form-group">
                                            <label class="control-label">@lang('modules.lead.zip')</label>
                                            <input type="text" name="pl_zip"  id="pl_zip" class="form-control" value="{{ $lead->project_location->zip ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-center">
                                    <div class="form-group">
                                        <button type="button" id="btn_pl_google_map" class="btn btn-primary"> <i class="fa fa-globe" aria-hidden="true"></i> Google Map</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group text-center m-t-30">
                                    <div class="checkbox checkbox-info ">
                                        <input id="same_as_above" name="same_as_above" value="true"
                                               type="checkbox">
                                        <label for="same_as_above">@lang('modules.lead.same_as_above')</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h3 class="box-title m-t-30">@lang('modules.lead.additionalInfo')</h3>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.tasks.assignTo')</label>
                                    <select class="select2 form-control" data-placeholder="@lang('modules.tasks.chooseAssignee')" name="user_id" id="user_id" >
                                        <option value=""></option>
                                        @foreach($designers as $designer)
                                            <option @if($lead->user_id == $designer->id) selected @endif
                                            value="{{ $designer->id }}">{{ ucwords($designer->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="button" id="btn_appointment" class="btn btn-primary"> <i class="fa fa-calendar"></i> @lang('modules.lead.appointment_schedule')</button>
                                    <button type="button" id="btn_note" class="btn btn-primary"> <i class="fa fa-sticky-note"></i> @lang('modules.lead.notes_history')</button>
                                    <a href="{{ route('admin.all-tasks.create') }}" class="btn btn-primary"><i class="ti-layout-list-thumb"></i> @lang('modules.tasks.newTask')</a>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="save-form" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.update')</button>
                            <a href="{{ route('admin.leads.index') }}" class="btn btn-default">@lang('app.back')</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- .row -->
    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="leadSourceModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        <!-- /.modal-dialog -->.
    </div>
    {{--Ajax Modal Ends--}}

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="interestAreaModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        <!-- /.modal-dialog -->.
    </div>
    {{--Ajax Modal Ends--}}

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="noteModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        <!-- /.modal-dialog -->.
    </div>
    {{--Ajax Modal Ends--}}
@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/jquery.inputmask.min.js') }}"></script>
<script>
    $(".date-picker").datepicker({
        todayHighlight: true,
        autoclose: true
    });
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });
    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route("admin.leads.update", [$lead->id])}}',
            container: '#updateLead',
            type: "POST",
            redirect: true,
            data: $('#updateLead').serialize()
        })
    });

    $("#same_as_above").change(function() {
        if(this.checked) {
            $('#pl_address1').val($('#address1').val());
            $('#pl_address2').val($('#address2').val());
            $('#pl_city').val($('#city').val());
            $('#pl_state').val($('#state').val());
            $('#pl_zip').val($('#zip').val());
        }
        else {
            $('#pl_address1').val('');
            $('#pl_address2').val('');
            $('#pl_city').val('');
            $('#pl_state').val('');
            $('#pl_zip').val('');
        }
        $('#pl_state').selectpicker('refresh');
    });

    $('#createLeadSource').click(function(){
        var url = '{{ route('admin.leadSource.create-src')}}';
        $('#modelHeading').html("@lang('modules.lead.leadSource')");
        $.ajaxModal('#leadSourceModal', url);
    });

    $('#btn_note').click(function(){
        var url = '{{ route('admin.note.create-lead-note', [$lead->id])}}';
        $('#modelHeading').html("@lang('modules.note.note')");
        $.ajaxModal('#noteModal', url);
    });

    $('#createInterestArea').click(function(){
        var url = '{{ route('admin.interest-area.create-area')}}';
        $('#modelHeading').html("@lang('modules.interest-area.area_of_interest')");
        $.ajaxModal('#interestAreaModal', url);
    });

    $(function(){
       if($('#pl_address1').val() == $('#address1').val() && $('#pl_address2').val() == $('#address2').val() && $('#pl_city').val() == $('#city').val()
        && $('#pl_state').val() == $('#state').val() && $('#pl_zip').val() == $('#zip').val()){
            $('#same_as_above').prop('checked', true);
       }
    });

    $('#btn_appointment').click(function(){
        window.location.href= '{{ route("admin.events.lead", [$lead->id]) }}';
    });

    $('#btn_google_map').click(function(){
        if(!$('#address1').val() && !$('#city').val()){
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
            let address = $('#address1').val();
            
            if($('#address2').val()){
                address += ',' + $('#address2').val();
            }
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

    $('#btn_pl_google_map').click(function(){
        if(!$('#pl_address1').val() && !$('#pl_city').val()){
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
            let address = $('#pl_address1').val();
            
            if($('#address2').val()){
                address += ',' + $('pl_#address2').val();
            }
            if($('#city').val()){
                address += ',' + $('#pl_city').val();
            }
            if($('#state').val()){
                address += ',' + $('#pl_state').val();
            }
            if($('#zip').val()){
                address += ',' + $('#pl_zip').val();
            }
            
            url = encodeURI(address)
            window.open('https://www.google.com/maps/search/?api=1&query=' + url, '_blank');
        }
        
    });

    $(document).ready(function(){
        $('#phone').inputmask("999-999-9999");
        $('#cell').inputmask("999-999-9999");
        $('#fax').inputmask("999-999-9999");
    });
</script>
@endpush