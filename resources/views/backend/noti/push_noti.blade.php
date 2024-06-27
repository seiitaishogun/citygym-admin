@extends('backend.layouts.app')

@section('title', __('acp.noti_title'))

@section('content')
    <form action="{{ route('admin.noti.send') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('acp.noti_title')}}</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title-input">@lang('acp.title') <span style="color: red">*</span></label>
                            <input class="form-control" id="title-input" type="text" name="title"  placeholder="@lang('news.vn_title_holder')" >
                        </div>

                        <div class="form-group">
                            <label for="file-input">@lang('acp.app_group')</label>
                            <select class="form-control"  name="app_group">
                                @foreach($groups as $item)
                                <option value="{{$item}}">{{__('acp.'.$item)}}</option>
                                @endforeach

                            </select>
                        </div>

                        <div class="form-group">
                            <label for="textarea-input">@lang('acp.noti_content') <span style="color: red">*</span></label>
                            <textarea class="form-control"  name="content" rows="9" placeholder="@lang('acp.noti_content_holder')"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="textarea-input">@lang('acp.noti_sentType')</label>
                            <select class="form-control" id="sentType" name="scheduled">
                                <option value="1">Now</option>
                                <option value="2">Schedule</option>
                            </select>
                        </div>

                        <div class="form-group" style="display: none" id="sltTime" >
                            <label for="textarea-input">@lang('acp.noti_schedule_time') </label>
                            <input class="form-control" id="datepicker" type="text" name="date-input" placeholder="date">
                        </div>
                    </div>
                    <div class="card-footer">
                        <input type="hidden" name="app_type" value="{{$app_type}}" >
                        <button class="btn btn-sm btn-primary" type="submit"> Send</button>
                        <button class="btn btn-sm btn-danger" type="reset"> Reset</button>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection

@push('after-scripts')
    <script src="{{url('/')}}/js/backend/modules/notification.js"></script>
@endpush
